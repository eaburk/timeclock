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

	$val=($_POST['chk_val']!="") ? "X" : "";
    $i=0;
    foreach($_POST["time_key"] as $tk){
        $keys .= ($i==0) ? "$tk" : ",$tk";
        $i++;
    }
    $query = "update times set time_in=time_in, billed='$val' where time_key in ($keys)";
  	$conn->query($query) or die(mysql_error());
?>
