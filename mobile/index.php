<?PHP
	session_start();
	empty($_SESSION["user"]);
	empty($_SESSION["password"]);
	if(isset($_POST["user"])){
		$_SESSION["user"] = $_POST["user"];
		$_SESSION["password"] = $_POST["password"];
	}
	if (!isset($_SESSION['user'])){
		echo "<form method='post'>
			please login<br>
			<input name='user'><br>
			<input type='password' name='password'><br>
			<input value='login' type='submit'>
		</form>";
		exit;
	} else {
		if($_SESSION['user']=='eric' && $_SESSION['password']=='rocks!'){
			
		} else {
			die("Wrong login");
		}
	}
	require "includes/db_cnx.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Timeclock</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.3/jquery.mobile.min.css" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script> 
<script src="//ajax.googleapis.com/ajax/libs/jquerymobile/1.4.3/jquery.mobile.min.js"></script> 
<script src="js/formatDate.js"></script> 
<script>
	$(function(){
		$.mobile.defaultPageTransition = "slide";
		$("#btnClockIn").click(function(){
			$(this).prop("disabled",true);
			$("#btnClockOut").prop("disabled",false);
			
			var d=new Date();
			d=d.formatDate("Y-m-d H:i:s");
			$('#txtClockIn').val(d);
			$('#txtClockOut').val("");
		});
		$("#btnClockOut").click(function(){
			if($("#slctCompany").val() == -1){
				alert("Please select a company");
				return;
			}
			$(this).prop("disabled",true);
			$("#btnClockIn").prop("disabled",false);
			
			var d=new Date();
			d=d.formatDate("Y-m-d H:i:s");
			$('#txtClockOut').val(d);
			
			$.post("ajax/record_punch.php",{
				clock_in: $('#txtClockIn').val(),
				clock_out: $('#txtClockOut').val(),
				company: $('#slctCompany').val()
			},
			function(){
				$("#message").html("Punch successfully recorded.");
				$.getJSON("ajax/create_time_list.php?date_start=10/14/2014&date_end=10/14/2014&company=2",function(str){
					alert(JSON.stringify(str));
				});
			});
		});
	});
</script>
<style>

</style>
</head>
<body>
	<div data-role="page" class="panel_page" id="page_main">
		<?PHP include("includes/panel.php"); ?>
		<?PHP include("includes/header.php"); ?>
		<div data-role="content">
						<div>
							<select id="slctCompany">
<?PHP
	$result=mysql_query("select company_key,company_description,work_week from companies order by company_key");
	$i=0;
	while($row=mysql_fetch_array($result)){	
		if($i == 0){
			$i++;
			$company = $row["company_key"];
		}
		if(isset($_COOKIE["cook1"]) && $_COOKIE["cook1"] == $row['company_key']){
			$selected = 'selected';
		} else {
			$selected = '';
		}
		echo "<option wkhrs='{$row["work_week"]}' $selected value=".$row['company_key'].">".$row['company_description']."</option>";
	}
?>
							</select>
						</div>
			<button id="btnClockIn">Clock In</button>
			<button id="btnClockOut" disabled>Clock Out</button>
			<input type="text" readonly id="txtClockIn">
			<input type="text" readonly id="txtClockOut">
			<div id="message">&nbsp;</div>
<?PHP
	//punches
	$date_start = date("Y-m-d",strtotime("last sunday"));
	$date_end = date("Y-m-d",strtotime("this saturday"));
	$query = "select time_key,date(time_in) 'date_in',time(time_in) 'time_in',time(time_out) 'time_out',date(time_out) 'date_out', billed from times where date(time_in) between '$date_start' and '$date_end' and company=$company order by `times`.`time_in`";
	$sql=mysql_query($query);
	$data_found = false;
	$list_view = "";
	$total_time = 0;
	$i=0;
	while($result=mysql_fetch_array($sql)){
		$data_found = true;
		if($i == 0){
			$list_view .= "<ul data-role='listview'><li data-role='list-divider'>This week's Punches</li>";
		}
		$i++;
		$data_found = true;
		$total_time += round((strtotime($result["time_out"]) - strtotime($result["time_in"]))/60/60,2);
		$list_view .= "<li>".date("m/d/Y",strtotime($result["date_in"]))." In: ".date("h:i a",strtotime($result["time_in"]))." Out: ".date("h:i a",strtotime($result["time_out"]))."</li>";
	}
	
	//adjustments
	$query = "select `hours`,`date` from add_subtract_hours
							where `date` between '$date_start' and '$date_end' and company=$company order by `date`";
	$sql = mysql_query($query);
	$i = 0;
	while($result=mysql_fetch_array($sql)){ 
		$data_found = true;
		if($i == 0){
			$list_view.="<li data-role='list-divider'>Adjustments</li>";
		}
		$i++;
		$total_time += $result["hours"];
		$list_view .= "<li>".date("m/d/Y",strtotime($result["date"]))." Adj: {$result["hours"]}</li>";
	}
	if($data_found){
		$list_view .= "<li data-role='list-divider'>Total time this week is $total_time hours</li></ul>";
		echo $list_view;
	}
?>
			</div>
		</div>
	</div>
</body>
</html>