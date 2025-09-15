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

	$sd_tms = date("Y-m-d",strtotime("last sunday"));
	$ed_tms = date("Y-m-d",strtotime($sd_tms." +6 days"));
	if(isset($_GET["company"])){
		$company=$_GET["company"];
	}
	else{
		$company=2; //default to PDI
	}
	$query = "select time_in,time_out from
		      times where company=$company
			  and time_in between '$sd_tms' and '$ed_tms'";

  $sql=$conn->query($query);
	$hrs = 0;
	while($result=$sql->fetch_assoc()){
		$hrs = $hrs + round((strtotime($result["time_out"])-strtotime($result["time_in"]))/60/60,2);
	}
	$a_s_sd = date("Y-m-d",strtotime($sd_tms));
	$a_s_ed = date("Y-m-d",strtotime($ed_tms));
	$sql = "select `hours` from add_subtract_hours
			    where `date` between '$a_s_sd' and '$a_s_ed' and company=$company";
	$sql=$conn->query($sql);
	while($result=$sql->fetch_assoc()){
		$hrs = $hrs + $result["hours"];
	}
	echo $hrs;
?>