var selected_date = null;
var selected_date_end = null;
var timer = null;
var $clock_in = null;
var $clock_in_time = null;
var $clock_in_date = null;
var $d = null;
var range_selection = false;
var current_week_hrs_worked;
var current_day_hrs_worked;

function myunload() {
  if ($("#btn_clock_in")[0].disabled == true) {
    return "YOU ARE CLOCKED IN.  HIT CANCEL TO RETURN TO THE PAGE AND CLOCK OUT BEFORE CLOSING THIS WINDOW!!!!!";
  }
  if (document.getElementById("process_image")) {
    return "PLEASE LET ALL REQUESTS FINISH BEFORE LEAVING THIS PAGE!";
  }
}

$(function () {
  setInterval(function () {
    //keep the session alive indefinitely
    $.get("ajax/keepalive.php");
  }, 1000 * 60 * 5);
  $("#inline").datepicker({
    showOtherMonths: true,
    selectOtherMonths: true,
    beforeShowDay: function (date) {
      if (range_selection) {
        try {
          var date1 = $.datepicker.parseDate(
            $.datepicker._defaults.dateFormat,
            selected_date
          );
          var date2 = $.datepicker.parseDate(
            $.datepicker._defaults.dateFormat,
            selected_date_end
          );
        } catch (e) {}
      }
      return [
        true,
        date1 &&
        (date.getTime() == date1.getTime() ||
          (date2 && date >= date1 && date <= date2))
          ? "dp-highlight"
          : "",
      ];
    },
    onSelect: function (dateText, inst) {
      if (range_selection) {
        var date1 = $.datepicker.parseDate(
          $.datepicker._defaults.dateFormat,
          selected_date
        );
        var date2 = $.datepicker.parseDate(
          $.datepicker._defaults.dateFormat,
          selected_date_end
        );
        if (!date1 || date2) {
          selected_date = dateText;
          selected_date_end = "";
          $(this).datepicker("option", "minDate", dateText);
        } else {
          selected_date_end = dateText;
          $(this).datepicker("option", "minDate", null);
        }
      } else {
        selected_date = dateText;
        selected_date_end = dateText;
      }
      rebuild_list_ajax();
    },
  });

  $("#progress").click(function () {
    $.get(
      "ajax/total_over_under.php?work_week=" +
        $("#slctCompany option:selected").attr("wkhrs"),
      function (str) {
        alert(str);
        return;
      }
    );
  });

  //attach click event to the clock in button
  $("#btn_clock_in").click(function (e) {
    current_week_hrs_worked = 0;
    refresh_current_week_hours_worked();
    current_day_hrs_worked = 0;
    refresh_current_day_hours_worked();
    $clock_in = null;
    $("#txtClockOut").val("");
    $("#btn_clock_out").attr("disabled", false);
    $(this).attr("disabled", true);
    $("#txtClockIn").attr("disabled", true);
    $("#slctCompany").attr("disabled", true);
    var d = new Date();
    d = d.formatDate("Y-m-d H:i:s");
    $("#txtClockIn").val(d);
    intvl = setInterval("update_running_tot()", 100);
    return false;
  });

  //attach click event to the clock out button
  $("#btn_clock_out").click(function (e) {
    clearInterval(intvl);

    var d = new Date();
    d = d.formatDate("Y-m-d H:i:s");
    $("#btn_clock_in").attr("disabled", false);
    $(this).attr("disabled", true);
    $("#txtClockOut").val(d);
    //make an ajax call to record the info
    $.post(
      "ajax/record_punch.php",
      {
        clock_in: $("#txtClockIn").val(),
        clock_out: $("#txtClockOut").val(),
        company: $("#slctCompany").val(),
      },
      function (str) {
        rebuild_list_ajax();
      }
    );

    $("#slctCompany").attr("disabled", false);
    return false;
  });

  //attach click event to the "add times" button
  $("#btnAdd").click(function () {
    $("#add_dialog").dialog("open");
    return false;
  });

  //attach click event to add subtract hours button
  $("#btnAddSubtract").click(function () {
    $("#add_subtract_dialog").dialog("open");
  });

  //attach click event to the delete link in the edit dialog box
  $(".delete_link").click(function (e) {
    var key = $("#edit_time_key").val();
    var d = $("#edit_time_in").val().substring(0, 10);
    if (confirm("Are you sure you wish to delete this time?")) {
      $.post(
        "ajax/delete_time.php",
        {
          time_key: key,
          date_in: d,
          company: $("#slctCompany").val(),
        },
        function (str) {
          refresh_current_week_hours_worked();
          rebuild_list_ajax();
        }
      );
      $("#edit_dialog").dialog("close");
    }
    return false;
  });

  //attach change event to the company drop down list
  $("#slctCompany").change(function () {
    $.cookie("cook1", $(this).val(), { path: "/", expires: 300 });
    rebuild_list_ajax();
  });

  //attach click event to the link that toggles allowing "ranges" in the datepicker
  $("#range_selector").click(function () {
    if ($(this).html() == "Turn Range On") {
      range_selection = true;
      $(this).html("Turn Range Off");
    } else {
      range_selection = false;
      $(this).html("Turn Range On");
    }
  });

  //attach click event to the "unbilled activity" button
  $("#unbilled_btn").click(function () {
    selected_date = "unbilled";
    selected_date_end = "unbilled";
    rebuild_list_ajax();
  });

  //attach click event to the "all unbilled activity" button
  //this appears to be no longer in use since not button with this id exists
  $("#all_activity_btn").click(function () {
    selected_date = "all_activity";
    selected_date_end = "all_activity";
    rebuild_list_ajax();
  });

  $("#averages").click(function () {
    //window.open("averages.php?company="+$("#slctCompany").val());
    $("#averages_dialog").dialog("open");
  });

  $("#reactivate_controls").click(function () {
    $clock_in = null;
    if ($("#txtClockIn").attr("disabled")) {
      $("#txtClockIn").attr("disabled", false);
      $("#slctCompany").attr("disabled", false);
    } else {
      $("#txtClockIn").attr("disabled", true);
      $("#slctCompany").attr("disabled", true);
    }
  });

  $("#add_time_in").blur(function () {
    var t = format_time($(this).val());
    $("#add_time_in").val(t);
  });

  $("#add_time_out").blur(function () {
    var t = format_time($(this).val());
    $("#add_time_out").val(t);
  });

  $("#editCompanies").click(function () {
    $("#edit_companies_dialog").dialog("open");
  });

  $("#averages_dialog").dialog({
    autoOpen: false,
    modal: false,
    height: 400,
    width: 750,
    open: function () {
      var that = this;
      $(this).html("Loading...");
      $.get(
        "ajax/create_averages.php?company=" + $("#slctCompany").val(),
        function (str) {
          $(that).html(str);
        }
      );
    },
  });

  //create the "add time" dialog box
  $("#add_dialog").dialog({
    autoOpen: false,
    modal: true,
    open: function () {
      $("#add_date").val(selected_date);
      $("#add_time_in").val("");
      $("#add_time_out").val("");
      $("#add_time_in").focus();
    },
    buttons: [
      {
        text: "Ok",
        click: function () {
          var add_d = $("#add_date").val();
          if (!valid_date(add_d)) {
            alert("Invalid Date");
            return;
          }
          var add_t_in = $("#add_time_in").val();
          if (validate_time(add_t_in) == false) {
            alert("Invalid Time In");
            return false;
          }
          var add_t_out = $("#add_time_out").val();
          if (validate_time(add_t_out) == false) {
            alert("Invalid Time Out");
            return false;
          } else {
            $("#add_time_in").val(add_t_in);
          }
          $.post(
            "ajax/add_time.php",
            {
              date_in: add_d,
              time_in: add_t_in,
              time_out: add_t_out,
              company: $("#slctCompany").val(),
            },
            function (str) {
              refresh_current_week_hours_worked();
              rebuild_list_ajax();
            }
          );
          $(this).dialog("close");
        },
      },
      {
        text: "Cancel",
        click: function () {
          $(this).dialog("close");
        },
      },
    ],
  });

  //create the "edit time" dialog box
  $("#edit_dialog").dialog({
    autoOpen: false,
    modal: true,
    buttons: [
      {
        text: "Ok",
        click: function () {
          var edit_t_in = $("#edit_time_in").val();
          if (!valid_datetime(edit_t_in)) {
            alert("Invalid Time In Date/Time");
            return;
          }
          var edit_t_out = $("#edit_time_out").val();
          if (!valid_datetime(edit_t_out)) {
            alert("Invalid Time Out Date/Time");
            return;
          }
          var edit_t_key = $("#edit_time_key").val();
          $.post(
            "ajax/edit_time.php",
            {
              time_key: edit_t_key,
              time_in: edit_t_in,
              time_out: edit_t_out,
              company: $("#slctCompany").val(),
            },
            function (str) {
              rebuild_list_ajax();
            }
          );
          $(this).dialog("close");
        },
      },
      {
        text: "Cancel",
        click: function () {
          $(this).dialog("close");
        },
      },
    ],
  });

  //create the "add/subtract time" dialog box
  $("#add_subtract_dialog").dialog({
    height: 200,
    modal: true,
    autoOpen: false,
    open: function () {
      $("#add_subtract_hours").val("");
      $("#add_subtract_mins").val("");
      $("#add_subtract_notes").val("");
      $("#add_subtract_date").val(selected_date);
      $("#add_subtract_hours").focus();
    },
    buttons: [
      {
        text: "Ok",
        click: function () {
          var hours = $("#add_subtract_hours").val();
          if (!hours.match(/\d+/)) {
            alert("Invalid Number of Hours");
            return;
          }
          var mins = $("#add_subtract_mins").val();
          if (mins == "") mins = "0";
          if (!mins.match(/\d+/)) {
            alert("Invalid Number of Minutes");
            return;
          }
          var hours = from_time_dec(parseInt(hours), parseInt(mins), 0);
          var date_in = $("#add_subtract_date").val();
          if (!valid_date(date_in)) {
            alert("Invalid Date");
            return;
          }
          var notes_in = $("#add_subtract_notes").val();
          var action = "add";
          $.post(
            "ajax/add_subtract_hours.php",
            {
              hours_in: hours,
              date_in: date_in,
              company: $("#slctCompany").val(),
              notes: notes_in,
              action_in: action,
            },
            function (str) {
              refresh_current_week_hours_worked();
              rebuild_list_ajax();
            }
          );
          $(this).dialog("close");
        },
      },
      {
        text: "Cancel",
        click: function () {
          $(this).dialog("close");
        },
      },
    ],
  });

  //create the "edit companies" dialog box
  $("#edit_companies_dialog").dialog({
    autoOpen: false,
    modal: true,
    open: function () {
      $("#edit_companies_dialog").html("Loading...");
      $.get("ajax/edit_companies.php?action=list", function (str) {
        $("#edit_companies_dialog").html(str);
        $("#add_company_button").click(function () {
          $.get(
            "ajax/edit_companies.php?action=add&descrp=" +
              $("#add_company_description").val(),
            function (str) {
              $("#editCompanies").click();
            }
          );
        });
        $("div[id^=delete_company_]").click(function () {
          if (confirm("Are you sure you want to delete this company?")) {
            var key = this.id.split("_");
            $.get(
              "ajax/edit_companies.php?action=delete&key=" + key[2],
              function (str) {
                $("#editCompanies").click();
              }
            );
          }
        });
      });
    },
    buttons: [
      {
        text: "Close",
        click: function () {
          $(this).dialog("close");
        },
      },
    ],
  });

  //initialize the global "selected_date" variable
  var d = new Date();
  selected_date = d.formatDate("m/d/Y");
  selected_date_end = selected_date;

  //initialize the state of form controls
  $("#txtClockIn").val("").attr("disabled", true);
  $("#txtClockOut").val("").attr("disabled", true);
  $("#slctCompany").attr("disabled", false);
  $("#btn_clock_in").attr("disabled", false);
  $("#btn_clock_out").attr("disabled", true);

  //bind ajax "listener" events
  $("#process_status").hide();
  $(document).ajaxStart(function () {
    $("#process_status")
      .html("Loading... <img id='process_image' src='images/indicator.gif'>")
      .show();
  });
  $(document).ajaxStop(function () {
    $("#process_status").fadeOut("normal", function () {
      $(this).html("");
    });
  });

  $("#checkall").click(function (e) {
    var time_key = "";
    var chk_val = "";
    var that = this;
    $(".chk_class").each(function (i) {
      this.checked = that.checked;
      var parts = this.id.split("_");
      if (i == 0) {
        time_key = "time_key[]=" + parts[1];
      } else {
        time_key += "&time_key[]=" + parts[1];
      }
    });
    chk_val = this.checked ? "1" : "";
    chk_val = "chk_val=" + chk_val;
    $.post(
      "ajax/update_billed.php",
      time_key + "&" + chk_val,
      function (str) {}
    );
  });

  $(document).on("click", "input[name=check_billed]", function (e) {
    var val = e.target.checked ? "1" : "";
    var ids = e.target.id.split("_");
    var data = "time_key[]=" + ids[1] + "&chk_val=" + val;
    $.post("ajax/update_billed.php", data, function (str) {});
    sync_checkall();
  });

  $(document).on("click", ".link_edit", function (e) {
    var times = e.target.id.split("^");
    $("#edit_time_key").val(times[0]);
    $("#edit_time_in").val(times[1]);
    $("#edit_time_out").val(times[2]);
    $("#edit_dialog").dialog("open");
    $("#edit_time_in").focus();
  });

  $(document).on("click", ".link_add_subtract", function (e) {
    if (confirm("Delete this Entry?")) {
      id = this.id.split("^");
      var hours = id[1];
      var date_in = id[0];
      var company = id[2];
      var action = "delete";
      $.post(
        "ajax/add_subtract_hours.php",
        {
          hours_in: hours,
          date_in: date_in,
          company: company,
          action_in: action,
        },
        function (str) {
          refresh_current_week_hours_worked();
          rebuild_list_ajax();
        }
      );
      return false;
    }
  });

  //populate the list box
  rebuild_list_ajax();
});

function update_running_tot() {
  if ($clock_in == null) {
    $("#progress").css({ display: "block" });
    $clock_in = $("#txtClockIn").val();
    //parse the date and time out of the string
    $clock_in = $clock_in.split(" ");
    $clock_in_date = $clock_in[0].split("-");
    $clock_in_time = $clock_in[1].split(":");
    //build the date object from the parsed strings
    $d = new Date();
    $d.setFullYear($clock_in_date[0]);
    $d.setMonth($clock_in_date[1] - 1);
    $d.setDate($clock_in_date[2]);
    $d.setHours($clock_in_time[0]);
    $d.setMinutes($clock_in_time[1]);
    $d.setSeconds($clock_in_time[2]);
  }

  //retrieve current time
  var new_d = new Date();
  var sun = new Date();
  sun.setDate(new_d.getDate() - new_d.getDay());

  //subtract the two times and convert to decimal hours
  var cur = (new_d - $d) / (1000 * 60 * 60);
  cur = cur + parseFloat(current_day_hrs_worked);
  //convert to hrs/mins/secs
  cur = from_dec_to_time(cur);
  cur = cur.split(" ");

  //current clocked in progress
  var progress_bar_today_time =
    left_fill(cur[0], 2, "&nbsp;") +
    "h&nbsp;" +
    left_fill(cur[1], 2, "&nbsp;") +
    "m&nbsp;" +
    left_fill(cur[2], 2, "&nbsp;") +
    "s";
  var mins = parseInt(cur[0]) * 60 + parseInt(cur[1]) + parseInt(cur[2]) / 60;
  var workday_mins = ($("#slctCompany option:selected").attr("wkhrs") / 5) * 60;
  var total_ticks = 31;
  var nbr_of_ticks = Math.floor(
    ((mins / workday_mins) * total_ticks * 100) / 100
  );
  if (total_ticks < nbr_of_ticks) {
    nbr_of_ticks = total_ticks;
  }
  var progress_bar =
    Array(nbr_of_ticks + 1).join("&raquo;") +
    Array(total_ticks - nbr_of_ticks + 1).join("&nbsp;");

  //current week progress (including current clocked in amount)
  var wk_mins_worked = from_dec_to_time(
    parseFloat(current_week_hrs_worked) + parseFloat(mins / 60)
  );
  wk_mins_worked = wk_mins_worked.split(" ");
  var progress_bar_week_time =
    left_fill(wk_mins_worked[0], 2, "&nbsp;") +
    "h&nbsp;" +
    left_fill(wk_mins_worked[1], 2, "&nbsp;") +
    "m";
  wk_mins_worked =
    parseInt(wk_mins_worked[0]) * 60 +
    parseInt(wk_mins_worked[1]) +
    parseInt(wk_mins_worked[2]) / 60;
  var week_minutes_needed =
    $("#slctCompany option:selected").attr("wkhrs") * 60;
  nbr_of_ticks = Math.floor(
    ((wk_mins_worked / week_minutes_needed) * total_ticks * 100) / 100
  );

  if (total_ticks < nbr_of_ticks) {
    nbr_of_ticks = total_ticks;
  }
  var week_progress_bar =
    Array(nbr_of_ticks + 1).join("&raquo;") +
    Array(total_ticks - nbr_of_ticks + 1).join("&nbsp;");

  //calculate when the end of the week will be crossed
  var remaining_mins = week_minutes_needed - wk_mins_worked;
  if (remaining_mins <= 0) {
    var completion_time = "You're Done!";
  } else {
    var workday_hrs = workday_mins / 60;
    var day = Math.floor(wk_mins_worked / 60 / workday_hrs + 1);
    var hrs_needed_today = day * workday_hrs;
    var remaining_today = hrs_needed_today * 60 - wk_mins_worked;
    //check if they have less than (workday - 2 hrs) remaining and if they just clocked in. They are probably making up time lost yesterday so advance the "day" field.
    if (
      remaining_today > 0 &&
      remaining_today < workday_mins - (workday_hrs - 2) * 60 /* - 2 hours */ &&
      mins / 60 < 2 /* clocked in for less than 2 hrs */
    ) {
      day += 1;
      hrs_needed_today = day * workday_hrs;
      remaining_today = hrs_needed_today * 60 - wk_mins_worked;
      //next check if they are past the workday and have been clocked in for more than 2 hrs. They are probably working overtime for that day so don't advance the "day" field.
    } else if (
      mins / 60 >
        workday_hrs / 2 /* been here longer than half the workday */ &&
      remaining_today / 60 > 6 /* more than 6 hrs remaining */
    ) {
      day -= 1;
      hrs_needed_today = day * workday_hrs;
      remaining_today = hrs_needed_today * 60 - wk_mins_worked;
    }

    if (remaining_today <= 0) {
      var completion_time = "You're Done!";
    } else {
      var ct = new Date(new Date().getTime() + remaining_today * 60000);
      //var completion_time = ct.toLocaleTimeString();
      var completion_time = ct.formatDate("h:i a");
    }
  }

  if (completion_time != "You're Done!") {
    var hrs_adjusted = Math.floor(remaining_today / 60);
    var mins_needed_today = (ct.getTime() - $d.getTime()) / 1000 / 60;
    hrs_adjusted = hrs_adjusted == 0 ? "" : hrs_adjusted + "h&nbsp;";
    var mins_adjusted = Math.floor(((remaining_today % 60) * 100) / 100);
    mins_needed_today = mins_needed_today + mins_adjusted;
    var pct_adjusted = Math.floor(
      ((mins / mins_needed_today) * 100 * 100) / 100
    );
    if (mins_adjusted < 0) {
      mins_adjusted = "You're done!";
    } else if (mins_adjusted == 0 && pct_adjusted < 100 && hrs_adjusted <= 0) {
      mins_adjusted = "<1m&nbsp;remaining";
    } else {
      mins_adjusted = mins_adjusted + "m&nbsp;remaining";
    }
    var percent_remaining_adjusted =
      "&nbsp;-&nbsp;" +
      pct_adjusted +
      "%&nbsp;Complete (" +
      hrs_adjusted +
      mins_adjusted +
      ")";
  } else {
    var percent_remaining_adjusted = "";
  }
  var progress_html =
    "&nbsp;Today's Progress |" +
    progress_bar +
    "| " +
    left_fill(
      Math.floor(((mins / workday_mins) * 100 * 100) / 100),
      3,
      "&nbsp;"
    ) +
    "%&nbsp;" +
    progress_bar_today_time +
    "<br>&nbsp;&nbsp;Week's Progress |" +
    week_progress_bar +
    "| " +
    left_fill(
      Math.floor(((wk_mins_worked / week_minutes_needed) * 100 * 100) / 100),
      3,
      "&nbsp;"
    ) +
    "%&nbsp;" +
    progress_bar_week_time +
    "<br>&nbsp;Adjusted Leave Time:&nbsp;" +
    completion_time +
    percent_remaining_adjusted;
  $("#progress").html(progress_html);
}

function format_time(t) {
  //convert to valid time format (01:01 pm with optional leading zero) based on pre-defined accepted patterns
  t = t.replace(/[,.]/g, ":"); //convert accepted separators to colon
  t = t.replace(/[pP]$/, "pm"); //convert "p" to "pm"
  t = t.replace(/[aA]$/, "am"); //convert "a" to "am"
  t = t.replace(/^(0?\d|[1][0-2])$/, t + ":00 am"); //convert single/two digit(s) to proper format
  t = t.replace(/^\d?\d:\d\d$/, t + " am"); //format ex. "1:00"
  if (t.match(/^\d?\d [pa]m$/i)) {
    //format ex. "1 p"
    var t_part = t.split(" ");
    return t.replace(/^\d?\d [pa]m$/i, t_part[0] + ":00 " + t_part[1]);
  }
  if (t.match(/^\d?\d:\d [pa]m$/i)) {
    //format ex. "1:1 p"
    t_part = t.split(" ");
    return t.replace(/^\d?\d:\d [pa]m$/i, t_part[0] + "0 " + t_part[1]);
  }
  t = t.replace(/^\d?\d:\d$/, t + "0 am"); //format ex. "1:1"
  return t;
}

function validate_time(t) {
  if (t.match(/^(1[012]|0?[1-9]):[0-5][0-9] [ap]m$/i)) return true;
  else return false;
}

function valid_date(d) {
  if (d.match(/^(0?[1-9]|1[012])\/(0?[1-9]|[12][0-9]|3[0-1])\/(19|20)\d\d$/))
    return true;
  else return false;
}

function valid_datetime(dt) {
  if (
    dt.match(
      /^(0?[1-9]|1[012])\/(0?[1-9]|[12][0-9]|3[0-1])\/(19|20)\d\d (1[012]|0?[1-9]):[0-5][0-9] [ap]m$/i
    )
  )
    return true;
  else return false;
}

function rebuild_list_ajax() {
  $.get(
    "ajax/create_time_list.php?t=" +
      new Date() +
      "&date_start=" +
      selected_date +
      "&date_end=" +
      selected_date_end +
      "&company=" +
      $("#slctCompany").val(),
    function (str) {
      rebuild_list(str);
    }
  );
  if (selected_date == "unbilled" || selected_date == "all_activity") {
    $("#view_date").html(selected_date);
  } else if (selected_date == selected_date_end) {
    $("#view_date").html(selected_date);
  } else {
    $("#view_date").html(selected_date + " - " + selected_date_end);
  }
}

function rebuild_list(str) {
  $("#list_times tbody").html(str);
  //stripe the list box
  $("#list_times tbody>tr:even").css("backgroundColor", "#dcdcdc");
  //attach click events to items in the list

  $(".link_edit").contextMenu("link_menu", {
    bindings: {
      edit: function (t) {
        $(t).click();
      },
      delete: function (t) {
        t = t.id.split("^");
        var key = t[0];
        var d = t[1];
        if (confirm("Are you sure you wish to delete this time?")) {
          $.post(
            "ajax/delete_time.php",
            {
              time_key: key,
              date_in: d,
              company: $("#slctCompany").val(),
            },
            function () {
              refresh_current_week_hours_worked();
              rebuild_list_ajax();
            }
          );
        }
      },
    },
    itemStyle: {
      fontFamily: "arial",
      fontSize: "12px",
      backgroundColor: "#ffffff",
      padding: "1px",
      border: "none",
    },
    menuStyle: {
      backgroundColor: "#dcdcdc",
      border: "1px solid #000000",
    },
    itemHoverStyle: {
      border: "none",
    },
  });
  sync_checkall();
  compute_selected_total();
}

function sync_checkall() {
  var $i = 0;
  //$("#checkall")[0].checked=true;
  $(".chk_class").each(function () {
    $i++;
    if (!this.checked) {
      $("#checkall")[0].checked = false;
    }
  });
  if ($i == 0) {
    //$("#checkall")[0].checked=false;
  }
}

function compute_selected_total() {
  var tot = 0;
  var hour = 0;
  var min = 0;
  $("input[name=tot_time]").each(function () {
    tot = tot + parseFloat(this.value);
  });

  //convert from decimal to hrs & mins
  tot = from_dec_to_time(tot);
  tot = tot.split(" ");
  hour = tot[0];
  min = tot[1];

  $("#selected_total_time").html(
    "&nbsp;Selection Total: " + hour + "h " + min + "m"
  );
}

function from_dec_to_time(t) {
  //convert from decimal to hrs & mins
  var hour = parseInt(t);
  t -= parseInt(t);
  t *= 60;
  var min = parseInt(t);
  t -= parseInt(t);
  t *= 60;
  var sec = parseInt(t);
  return hour + " " + min + " " + sec;
}

function from_time_dec(h, m, s) {
  return h + m / 60 + s / 3600;
}

function left_fill(value, width, fill_char) {
  width -= value.toString().length;
  if (width > 0) {
    return (
      new Array(width + (/\./.test(value) ? 2 : 1)).join(fill_char) + value
    );
  }
  return value + ""; // always return a string
}

function refresh_current_week_hours_worked() {
  $.get(
    "ajax/current_week_hours.php?company=" + $("#slctCompany").val(),
    function (str) {
      current_week_hrs_worked = str;
    }
  );
}

function refresh_current_day_hours_worked() {
  $.ajax({
    url: "ajax/current_day_hours.php?company=" + $("#slctCompany").val(),
    success: function (str) {
      current_day_hrs_worked = str;
    },
    async: false,
  });
  // $.get("ajax/current_day_hours.php?company="+$('#slctCompany').val(),function(str){
  //   current_day_hrs_worked = str;
  // });
}

window.onbeforeunload = myunload;
