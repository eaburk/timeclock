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

	$hours_in=$_POST['hours_in'];
	$date_in=$_POST['date_in'];
	$notes=$_POST['notes'];
	$company=$_POST['company'];
	$action=$_POST['action_in'];

	//reformat $date_in
	$date_in = date("Y-m-d",strtotime($date_in));

	if($action=="add"){
		$result=$conn->query("insert into add_subtract_hours (`hours`,`date`,`notes`,`company`)
							 values ($hours_in,'$date_in','$notes',$company)") or die(mysql_error());
	}
	if($action=="delete"){
		$result=$conn->query("delete from `add_subtract_hours`
							 where `hours`=$hours_in and `date`='$date_in' and `company`=$company") or die(mysql_error());
	}
?>
