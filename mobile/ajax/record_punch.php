<?php
	require "../includes/nocache.php";

	require_once "../includes/db_cnx.php";

	$clock_in=$_POST['clock_in'];
	$clock_out=$_POST['clock_out'];
	$company=$_POST['company'];
	
	//$total_time = (strtotime($clock_out)-strtotime($clock_in))/60;  //calculate minutes

	$result=mysql_query("insert into times (time_in,time_out,company)
				    values ('$clock_in','$clock_out',$company)") or die(mysql_error());
?>
