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

	($_GET["work_week"] > 0)? define(WORK_WEEK,$_GET['work_week']): define(WORK_WEEK,45);
?>
		<table cellspacing='0' cellpadding='5' width='650'>
			<tr><td>Based on a work week of <?PHP echo WORK_WEEK; ?> hours</td></tr>
			<tr>
				<td>
					<table width='100%' border="1" cellpadding='1' cellspacing='0' id="averages_table">
						<thead>
							<tr style="background-color:#ff0000;color:#fff">
								<th align='center'>Week</th>
								<th align='center'>Tot. Hrs.</th>
								<th align='center'>Avg. Daily Hrs.</th>
								<th align='center'>+/- Mins</th>
								<th align='center'>+/- Hrs</th>
							</tr>
						</thead>
						<tbody>
<?PHP
	$sd = date("m/d/Y",strtotime("first Sunday of January ".date("Y")));
	$ed = date("m/d/Y",strtotime("last saturday"));
	$current_week_flag=false;
	$cur_date = time();
	$full_year=true;
	$table = "";


	if($sd=="full_year"){
		$full_year=true;
		//start of the current year
		$sd=mktime(0,0,0,1,1,date("Y"));
		$sd_tms=date("Y-m-d",$sd)." 00:00:00";

		//end of the current year
		$ed=mktime(0,0,0,12,31,date("Y"));
		$ed_tms=date("Y-m-d",$ed)." 23:59:59";
	} else {
		$sd_tms = date("Y-m-d",strtotime($sd))." 00:00:00";
		$ed_tms = date("Y-m-d",strtotime($ed))." 23:59:59";
	}
	if(isset($_GET["company"])){
		$company=$_GET["company"];
	} else {
		$company=2; //default to PDI
	}
	$weeks_ary=build_weeks_array();
	$zero_week=0;
	$j=0;
	for($i=0;$i<count($weeks_ary);$i++){
		$sd_tms=date("Y-m-d",$weeks_ary[$i]["start"])." 00:00:00";
		$ed_tms=date("Y-m-d",$weeks_ary[$i]["end"])." 23:59:59";
		$sql="select time_in,time_out from
		      times where company=$company
			  and time_in between '$sd_tms' and '$ed_tms'";
		$sql=$conn->query($sql);
		$rcd_cnt=0;
		$wk_hrs=0;
		while($result=$sql->fetch_assoc()){
			$sd_out=date("m/d/y",strtotime($sd_tms));
			$ed_out=date("m/d/y",strtotime($ed_tms));
			$wk_hrs=$wk_hrs + ((strtotime($result["time_out"])-strtotime($result["time_in"]))/60/60);
			$rcd_cnt++;
		}
		$a_s_sd = date("Y-m-d",$weeks_ary[$i]["start"]);
		$a_s_ed = date("Y-m-d",$weeks_ary[$i]["end"]);
		$sql = "select `hours` from add_subtract_hours
			    where `date` between '$a_s_sd' and '$a_s_ed' and company=$company";
		$sql=mysql_query($sql);
		$add_subtract_hours_flag=false;
		while($result=$sql->fetch_assoc()){
			$add_subtract_hours_flag=true;
			$sd_out=date("m/d/y",strtotime($sd_tms));
			$ed_out=date("m/d/y",strtotime($ed_tms));
			$wk_hrs = $wk_hrs + $result["hours"];
			$rcd_cnt++;
		}
		if($cur_date >= strtotime($sd_tms) && $cur_date <= strtotime($ed_tms) && !$current_week_flag&&$full_year){
			$rcd_cnt=0;
		}
		if($rcd_cnt==0){
			$sd_out=date("m/d/y",strtotime($sd_tms));
			$ed_out=date("m/d/y",strtotime($ed_tms));
			$zero_weeks++;
			continue;
		}
		$avg_daily_hrs=$wk_hrs/5;
		$act_plus_minutes=($wk_hrs*60)-(WORK_WEEK*60);
		$tot_act_plus_minus_minutes = $tot_act_plus_minus_minutes+$act_plus_minutes;
		$act_plus_minutes=round($act_plus_minutes,0);
		$asflag = ($add_subtract_hours_flag===true) ? "*" : "";
		$j++;
		if($j <= 50){
			$table.="<tr>
					<td>$sd_out - $ed_out $asflag</td>
					<td align='right'>".round($wk_hrs,2)."</td>
					<td align='right'>".round($avg_daily_hrs,2)."</td>
					<td align='right'>".$act_plus_minutes."</td>
					<td align='right'>".round(($act_plus_minutes/60),2)."</td>
				  </tr>";
		}
		$tot_hrs=$tot_hrs+$wk_hrs;
		$wk_hrs=0;
	}
	$tot_wks=$i-$zero_weeks;
	$avg_wk_hrs=$tot_hrs/$tot_wks;
	$table="<tr>
			<td style='font-weight:bold;' colspan='5'>Totals:</td>
	 	  </tr>
		  <tr>
			<td># Of Weeks: ".$tot_wks."</td>
			<td align='right'>".round($tot_hrs,2)."</td>
			<td align='right'>".round($avg_wk_hrs,2)."</td>
			<td align='right'>".round($tot_act_plus_minus_minutes,2)."</td>
			<td align='right'>".round(($tot_act_plus_minus_minutes/60),2)."</td>
		  </tr>
		  <tr><td align='center' style='font-weight:bold;' colspan='5'>&nbsp;</td></tr>
		  <tr><td style='font-weight:bold;' colspan='5'>Most Recent 50 Weeks:</td>".$table;
	echo $table;


	function build_weeks_array(){
		global $sd_tms;
		global $ed_tms;
		$sd=strtotime("previous sunday",strtotime($sd_tms));
		$ed=0;
		$i=0;
		while($ed<=strtotime($ed_tms)){
			$wk_ary[$i]["start"]=$sd;
			$ed = strtotime("this saturday",$sd);
			$wk_ary[$i]["end"]=$ed;
			//start a new week
			$sd = strtotime("this sunday",$ed);
			$i++;
		}
		return array_reverse($wk_ary);
	}
?>
						</tbody>
					</table>
				</td>
			</tr>
		</table>
