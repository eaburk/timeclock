<?php
	session_start();
	if($_SESSION['user'] !='eric' || $_SESSION['password'] != 'rocks!'){
		exit;
	}
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	include("../includes/db_cnx.php");

	$key=$_POST['time_key'];
	$clock_in=$_POST['time_in'];
	$clock_out=$_POST['time_out'];
	$company=$_POST['company'];

	//reformat $clock_in from "m/d/Y h:i A" to "Y-m-d h:i:s"
	$format = explode(" ",$clock_in);
	$format[1]=$format[1].' '.$format[2];
	$clock_in = date("Y-m-d",strtotime($format[0]))." ".date("H:i:s",strtotime($format[1]));

	//reformat $clock_out from "m/d/Y h:i A" to "Y-m-d h:i:s"
	$format = explode(" ",$clock_out);
	$format[1]=$format[1].' '.$format[2];
	$clock_out = date("Y-m-d",strtotime($format[0]))." ".date("H:i:s",strtotime($format[1]));

	$result=$conn->query("update times set time_in='$clock_in',time_out='$clock_out',company=$company
						  where time_key=$key") or die(mysql_error());
?>
