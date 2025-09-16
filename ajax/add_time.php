<?php
	//session_start();
	//if($_SESSION['user'] !='eric' || $_SESSION['password'] != 'rocks!'){
		//exit;
	//}
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	include("../includes/db_cnx.php");

    $date=$_POST['date_in'];
	$time_in=$_POST['time_in'];
	$time_out=$_POST['time_out'];
	$company=$_POST['company'];

	//reformat the date from "m/d/Y" to "Y-m-d"
    $date=date("Y-m-d",strtotime($date));

	//format $clock_in
	$clock_in=$date." ".$time_in;
	$clock_in=date("Y-m-d H:i:s",strtotime($clock_in));

	//format $clock_out
	$clock_out=$date." ".$time_out;
	$clock_out=date("Y-m-d H:i:s",strtotime($clock_out));

	$result=$conn->query("insert into times (time_in,time_out,company)
				    values ('$clock_in','$clock_out',$company)") or die(mysql_error());
?>
