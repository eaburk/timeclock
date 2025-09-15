<?php
	session_start();
	if($_SESSION['user'] !='eric' || $_SESSION['password'] != 'rocks!'){
		exit;
	}
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	include_once("../includes/db_cnx.php");

	$clock_in=$_POST['clock_in'];
	$clock_out=$_POST['clock_out'];
	$company=$_POST['company'];

  $result = $conn->prepare("insert into times (time_in,time_out,company)
				    values (?, ?, ?)");
  $result->bind_param("ssi", $clock_in, $clock_out, $company);
  $result->execute();
?>
