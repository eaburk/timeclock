<?php
$i=0;

//get the list of punches
$where_clause = "Where company = $company ";
if($date_start=="unbilled"){
	$where_clause .= " and (billed = '' or billed is null) ";
} else if($date_start=="all_activity") {
	$where_clause .= "";
} else {
	$where_clause .= " and date(time_in) between '$date_start' and '$date_end' ";
}
$query = "Select time_key,time_in tms_in, time_out tms_out, date(time_in) 'date_in',time(time_in) 'time_in',time(time_out) 'time_out',date(time_out) 'date_out', billed
			From times
			$where_clause";
$result = $conn->query($query);
if($date_start != "unbilled"){
  $result2 = $conn->query("select `hours`,`date` from add_subtract_hours
              where `date` between '$date_start' and '$date_end' and company=$company order by `date`");
  while($row2=$result2->fetch_assoc()){
    $i++;
    echo "<tr><td width='60'>&nbsp;</td>
        <td width='90'><a class='link_add_subtract' id='".$row2["date"]."^".$row2["hours"]."^".$company."' href='#'>".date("m-d-Y",strtotime($row2["date"]))."</a></td>";
    echo "<td width='120'>-</td>";
    echo "<td width='120'>-</td>";
    echo "<td width='110' class='tot_time_class'>".$row2["hours"]."<input type='hidden' name='tot_time' value='".$row2["hours"]."'></td>";
  }
}
while($row=$result->fetch_assoc()){
  $i++;
  if($row['billed']!="")
    $checked="checked";
  else
    $checked="";
  echo "<tr><td width='60'><input $checked class='chk_class' type='checkbox' name='check_billed' id='chk_".$row['time_key']."'><span style='font-size:10px; font-weight:bold; visibility:hidden'>Billed</span></td>
        <td width='90'><a class='link_edit' id='".$row['time_key']."^".date("m/d/Y",strtotime($row["date_in"]))." ".date("h:i A",strtotime($row["time_in"]))."^".date("m/d/Y",strtotime($row["date_out"]))." ".date("h:i A",strtotime($row["time_out"]))."' href='#'>".date("m-d-Y",strtotime($row["date_in"]))."</a></td>";
  echo "<td width='120'>".date("h:i A",strtotime($row["time_in"]))."</td>";
  echo "<td width='120'>".date("h:i A",strtotime($row["time_out"]))."</td>";

  $date_out = new DateTime($row["tms_out"]);
  $date_in = new DateTime($row["tms_in"]);
  $interval = $date_in->diff($date_out);

  $total_time=round((strtotime($row["tms_out"])-strtotime($row["tms_in"]))/60/60,2);

  //build the progress bar HTML
  $decimal_time = $total_time / 9 * 100;
  $decimal_time = ($decimal_time > 100) ? 100 : $decimal_time;
  $factor = .6;
  $progress = (round($decimal_time,2) * $factor)."px";
  $progress_bar_width = (100 * $factor) . "px";
  $progress_bar = '<div style="text-align:left;background-color:#ffffff;height:10px;width:'.$progress_bar_width.';border:1px solid black;"><div style="float:left;background-color:#000000;text-align:center;font-size:10px;font-family:\'Times New Roman\';height:10px;color:#ffffff;width:'.$progress.'">'.$interval->h."h ".$interval->i."m".'</div></div>';
  echo "<td width='110' class='tot_time_class'>".$progress_bar."<input type='hidden' name='tot_time' value='$total_time'></td></tr>";
}
if($i==0){
	echo "<tr><td colspan='5'><span style='color:#ff3300;font-weight:bold;font-size:16px;'>No Activity</span></td></tr>";
}
?>
