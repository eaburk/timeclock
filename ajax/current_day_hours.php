<?PHP
	session_start();
	if($_SESSION['user'] !='eric' || $_SESSION['password'] != 'rocks!'){
		exit;
	}
 	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	include("../includes/db_cnx.php");

	if(isset($_GET["company"])){
		$company=$_GET["company"];
	}
	else{
		$company=2; //default to PDI
	}
  $conn->query('set time_zone = "America/Chicago";');
	$query = "select time_in,time_out from
		      times where company=$company
			  and date(time_in) = current_date()";

  $sql=$conn->query($query);
	$hrs = 0;
	while($result=$sql->fetch_assoc()){
		$hrs = $hrs + round((strtotime($result["time_out"])-strtotime($result["time_in"]))/60/60,2);
	}

	$sql = "select `hours` from add_subtract_hours
			    where date(`date`) = current_date() and company=$company";
	$sql=$conn->query($sql);
	while($result=$sql->fetch_assoc()){
		$hrs = $hrs + $result["hours"];
	}
	echo $hrs;
?>