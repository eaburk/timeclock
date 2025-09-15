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
	$company=$_POST['company'];
	$date = date("Y-m-d",strtotime($_POST["date_in"]));

	$result=$conn->query("delete from times
						  where time_key=$key") or die(mysql_error());
						  echo "Complete";
?>
