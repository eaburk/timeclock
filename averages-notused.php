<?PHP
	session_start();
	if($_SESSION['user'] !='eric' || $_SESSION['password'] != 'rocks!'){
		header("Location:index.php");
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?PHP
	include_once("includes/db_cnx.php");
	if($_GET['work_week']!="")
	{
		define(WORK_WEEK,$_GET['work_week']);
	}
	else
	{
		$sql=mysql_query("select work_week from companies where company_key=".$_GET['company']);
		$result=mysql_fetch_array($sql);
		if(mysql_num_rows($sql)>0)
		{
			define(WORK_WEEK,$result["work_week"]);
		}
		else
		{
			define(WORK_WEEK,45);
		}
	}
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Time Clock - Averages</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script type="text/javascript">
			$(function(){
				$(document).scrollTop(1000000);
			});
		</script>
	</head>
	<body>
		<table cellspacing='0' cellpadding='5' width='650'>
			<tr>
				<td align="center"><div style="height:16px;"><div id="process_status"></div>&nbsp;</div></td>
			</tr>
			<tr>
				<td>
					<form method="get">
					Company: <select name="company" id="company">
						<?PHP
							$result=mysql_query("select company_key,company_description from companies");
							while($row=mysql_fetch_array($result))
							{
								$company=$row['company_key'];
								if($_GET['company']==$company)
								{
									$selected='selected';
								}
								else
								{
									$selected='';
								}
								echo "<option $selected value=".$row['company_key'].">".$row['company_description']."</option>";
							}
						?>
					</select>
					<input type="submit" value="Go">
					</form>
				</td>
			</tr>
			<tr><td>Based on a work week of <?PHP echo WORK_WEEK; ?> hours</td></tr>
			<tr>
				<td>
					<table width='100%' border="1" cellpadding='1' cellspacing='0' id="averages_table">
						<thead>
							<tr>
								<td>Week</td>
								<td>Tot. Hrs.</td>
								<td>Avg. Daily Hrs.</td>
								<td>+/- Mins | Hrs</td>
							</tr>
						</thead>
						<tbody>
							<?PHP include("ajax/create_averages.php"); ?>
						</tbody>
					</table>
				</td>
			</tr>
		</table>
		<a id="bottom">&nbsp;</a>
	</body>
</html>