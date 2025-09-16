<?php
	//session_start();
	//if($_SESSION['user'] !='eric' || $_SESSION['password'] != 'rocks!'){
		//exit;
	//}
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	if($_GET['date_start']=="all_activity"){
		$date_start="all_activity";
	} else if($_GET['date_start']=="unbilled"){
		$date_start="unbilled";
	} else {
    	$date_start=date("Y-m-d",strtotime($_GET['date_start']));
		$date_end=date("Y-m-d",strtotime($_GET['date_end']));
	}
	$company=$_GET['company'];
	include("../includes/db_cnx.php");
	include("../includes/build_time_list.php");
?>
