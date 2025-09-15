<?PHP
// session_start();
// $_SESSION["user"]="";
// $_SESSION["password"]="";
// if(isset($_POST["user"])){
// 	$_SESSION["user"] = $_POST["user"];
// 	$_SESSION["password"] = $_POST["password"];
// } else {
// 	echo "<form method='post'>
// 		please login<br>
// 		<input autofocus name='user'><br>
// 		<input type='password' name='password'><br>
// 		<input value='login' type='submit'>
// 	</form>";
// 	exit;
// }
// if($_SESSION['user'] != 'eric' || $_SESSION['password'] != 'rocks!'){
// 	die("Wrong login");
// }
?>
<!DOCTYPE html>
<?PHP
	include_once("includes/db_cnx.php");
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>Time Clock</title>
       <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/blitzer/jquery-ui.css" />
        <style>
            .date-range-selected > .ui-state-active,
            .date-range-selected > .ui-state-default {
                background: none;
                background-color: lightsteelblue;
            }

            .ui-datepicker {
                width: 12em;
                padding: .2em .2em 0;
                display: none;
            }
            .ui-datepicker-month,.ui-datepicker-year {
                width: 49%;
                font-size: .9em;
            }
            .ui-datepicker table {
                width: 100%;
                font-size: .8em;
                border-collapse: collapse;
                margin: 0 0 .4em;
            }
            .ui-datepicker th {
                padding: .3em .1em;
                text-align: center;
                font-weight: bold;
                border: 0;
            }
            .ui-datepicker td span,
            .ui-datepicker td a {
                display: block;
                padding: .1em;
                text-align: right;
                text-decoration: none;
            }
            .ui-dialog{
                font-size:12px;
            }

			.dp-highlight .ui-state-default {
				background: #484;
				color: #FFF;
			}

        </style>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
		<script src="js/formatDate.js"></script>
		<script src="js/jquery.contextmenu.js"></script>
		<script src="js/jquery.cookie.js"></script>
		<script src="js/timeclock.js?ver=<?= rand(); ?>"></script>
		<style>
            .total-box{
                width:190px;
                color:red;
                border: 1px solid black;
                background-color:#dcdcdc;
                margin:10px;
                font-size: 12px;
            }
		</style>
	</head>
	<body>
		<div style="margin-left:auto; margin-right:auto; width:750px; border: 1px solid black;">
			<table>
				<tr>
					<td colspan="2" align="center"><span style="font-weight:bold;font-size:24px">Time Clock</span></td>
				</tr>
				<tr><td colspan="2" align="center"><div style="height:16px;"><div id="process_status"></div>&nbsp;</div></td></tr>
				<tr>
					<td>
						Company: <select id="slctCompany">
							<?PHP
                $result = $conn->query("select company_key,company_description,work_week from companies order by company_description");
								while($row=$result->fetch_assoc())
								{
									$company=$row['company_key'];
									if(isset($_COOKIE["cook1"]) && $_COOKIE["cook1"]==$company)
									{
										$selected='selected';
									}
									else
									{
										$selected='';
									}
									echo "<option wkhrs='{$row["work_week"]}' $selected value=".$row['company_key'].">".$row['company_description']."</option>";
								}
							?>
						</select>
						<span id="editCompanies" style='cursor:pointer'><img src="images/edit_16x16.png"></span>
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>
						<button style="width:100px;" id="btn_clock_in">Clock In</button><input type="text" id="txtClockIn" name="txtClockIn">&nbsp;<img style="cursor:pointer;" id="reactivate_controls" src="images/edit_16x16.png"><br>
						<button style="width:100px;" id="btn_clock_out">Clock Out</button><input type="text" id="txtClockOut" name="txtClockOut">
					</td>
					<td>
						&nbsp;
					</td>
				</tr>
				<tr>
					<td align="center" style="font-size:12px;">Currently Viewing <span id="view_date"></span></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td valign="top">
						<div style="width:500px;">
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
								<thead>
								<tr>
									<th width="60"><input type="checkbox" id="checkall"><span style="font-size:10px;">Billed</span></th>
									<th width="90">Date</th>
									<th width="120">Clock In Time</th>
									<th width="120">Clock Out Time</th>
									<th width="110">Total Time</th>
								</tr>
								</thead>
							</table>
						</div>
						<div id="div_time_list" style="border:1px solid black; width:500px; height:300px; overflow:scroll;">
							<table width="100%" id="list_times" border="0" cellspacing="0" cellpadding="0">
								<tbody align="center">
								</tbody>
							</table>
						</div>
						<div title="Click to see overtime worked" id="progress" style='display:none;margin-top:5px;border:1px solid black;border-radius:5px 5px; font-family:Courier;font-size:12px;background-color:#CD0505;color:#FFFFFF'>&nbsp;</div>
					</td>
					<td valign="top">
						<div id="range_selector" style="color:blue;text-decoration:underline;cursor:pointer;">Turn Range On</div>
						<div id="inline"></div>
						<div class="total-box" type="text" id="selected_total_time"></div>
                        <table>
                            <tr>
                                <td><button style="width:100px;" id="btnAdd">Add Punch</button></td>
                                <td><button id="averages" style="width:100px;">Averages</button></td>
                            </tr>
                            <tr>
                                <td><button style="width:100px;" id="btnAddSubtract">Adjustments</button></td>
                                <td><button id="unbilled_btn" style="width:100px;">Unbilled</button></td>
                            </tr>
                        </table>
					</td>
				</tr>
			</table>

			<div id="add_dialog" title="Add Times">
				<table>
					<tr>
						<td>Date:</td>
						<td><input type="text" id="add_date"></td>
					</tr>
					<tr>
						<td>Time In:</td>
						<td><input type="text" id="add_time_in"></td>
					</tr>
					<tr>
						<td>Time Out:</td>
						<td><input type="text" id="add_time_out"></td>
					</tr>
				</table>
			</div>
			<div id="edit_dialog" title="Edit Time">
				<table width="100%">
					<tr>
						<td>&nbsp;</td>
						<td align="right"><span class="delete_link" title="Delete Time" style="color:blue; text-decoration:underline; cursor: pointer">Delete</span></td>
					</tr>
					<tr>
						<td>Time In:</td>
						<td><input type="text" id="edit_time_in"></td>
					</tr>
					<tr>
						<td>Time Out:</td>
						<td><input type="text" id="edit_time_out"><input type="hidden" id="edit_time_key"></td>
					</tr>
				</table>
			</div>
			<div id="add_subtract_dialog" title="Adjustments">
				<table width="100%">
					<tr>
						<td align='right'>Hrs:</td>
						<td><input size='2' type="text" id="add_subtract_hours"> Min: <input size='2' type="text" id="add_subtract_mins"></td>
					</tr>
					<tr>
						<td align='right'>Date:</td>
						<td><input type="text" id="add_subtract_date"></td>
					</tr>
					<tr>
						<td align='right' valign="top">Notes:</td>
						<td><textarea id="add_subtract_notes"></textarea></td>
					</tr>
				</table>
			</div>
			<div id="edit_companies_dialog" title="Edit Companies" style='text-align:center'>
			</div>
		    <div class="contextMenu" id="link_menu">
		      <ul>
		        <li id="edit">Edit</li>
		        <li id="delete">Delete</li>
		      </ul>
		    </div>
		</div>
		<div id="averages_dialog">
		</div>
	</body>
</html>
