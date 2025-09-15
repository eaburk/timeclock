<?PHP
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	session_start();

	if($_SESSION['user'] !='eric' || $_SESSION['password'] != 'rocks!'){
		exit;
	}

	include("../includes/db_cnx.php");

	($_GET["work_week"] > 0)? define("WORK_WEEK",$_GET['work_week']): define("WORK_WEEK",45);

	//$sd = "12/31/2000";
	$sd = date("m/d/Y",strtotime("first Sunday of January ".date("Y"))); //really should go back to first week that has a day from january in it
	$ed = date("m/d/Y",strtotime("last saturday"));                      //would need to re-figure this value too
	$current_week_flag=false;
	$cur_date = time();
	$table = "";

	$sd_tms = date("Y-m-d",strtotime($sd))." 00:00:00";
	$ed_tms = date("Y-m-d",strtotime($ed))." 23:59:59";


	$company = 2; //set to actual company

	$query="select sum(TIMESTAMPDIFF(SECOND,time_in,time_out)) secs
		  from times
		  where company=$company
		    and time_in between '$sd_tms' and '$ed_tms'";
	$sql=$conn->query($query);
	$result = $sql->fetch_assoc();
	$wk_hrs = $result["secs"] / 3600; //convert to hrs


	$query = "select sum(`hours`) hrs from add_subtract_hours
			where `date` between '$sd_tms' and '$ed_tms' and company=$company";
	$sql=$conn->query($query);
	$result=$sql->fetch_assoc();
	$wk_hrs = $wk_hrs + $result["hrs"];



	$query = "select TIMESTAMPDIFF(WEEK,'$sd_tms','$ed_tms') + 1 weeks
			From times";
	$sql=$conn->query($query);
	$result = $sql->fetch_assoc();
	$weeks = $result["weeks"];

	$hours_diff = round($wk_hrs - (WORK_WEEK * $weeks),2);
	echo "Current Year Over/Under: $hours_diff hours, ".($hours_diff * 60)." minutes";
?>