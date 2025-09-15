<?PHP
	require "../includes/nocache.php";
	require "../includes/db_cnx.php";
	
    $date_start=date("Y-m-d",strtotime($_GET['date_start']));
	$date_end=date("Y-m-d",strtotime($_GET['date_end']));
	$company=$_GET['company'];
	
	//find any add/subtract hours with this date
	$result2 = mysql_query("select `hours`,`date` from add_subtract_hours
							where `date` between '$date_start' and '$date_end' and company=$company order by `date`");
	while($row2=mysql_fetch_array($result2)){ 
		$i++;
		$json_array["adjustments"][] = array("adjustment_date"=>$row2["date"],"adjustment_hours"=>$row2["hours"]);
	}
	
	$query = "select time_key,date(time_in) 'date_in',time(time_in) 'time_in',time(time_out) 'time_out',date(time_out) 'date_out', billed from times where date(time_in) between '$date_start' and '$date_end' and company=$company order by `times`.`time_in`";
	$result=mysql_query($query);
	while($row=mysql_fetch_array($result)){
		$i++;
		$total_time=round((strtotime($row["time_out"])-strtotime($row["time_in"]))/60/60,2);
		$hr = floor($total_time);
		$min = floor(($total_time-$hr)*60);
		
		$json_array["punches"][] = array("punch_key"=>$row['time_key'],
											"punch_date"=>date("m-d-Y",strtotime($row["date_in"])),
											"punch_in"=>date("h:i A",strtotime($row["time_in"])),
											"punch_out"=>date("h:i A",strtotime($row["time_out"])),
											"duration_hours"=>$hr,
											"duration_minutes"=>$min,
											"duration_total"=>$total_time);
	}
	echo json_encode($json_array);
?>
