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
	$action=$_GET["action"];
	if($action=="list"){
		$sql = "Select * from companies";
		$result=$conn->query($sql);
		echo "<table width='100%'>
				<tr>
					<td><input type='text' value='' id='add_company_description'><button id='add_company_button'>Add</button></td>
				</tr>
			</table>";
		echo "<table width='100%' cellpadding='5' cellspacing='0' border='0'><tr style='font-weight:bold'><td><u>Company Id</u></td><td><u>Description</u></td><td><u>Action</u></tr>";
		while($row=$result->fetch_assoc()){
			echo "<tr><td>".$row["company_key"]."</td><td>".$row["company_description"]."</td><td><div id='delete_company_".$row["company_key"]."' style='color:blue;text-decoration:underline;cursor:pointer'>delete</div></td></tr>";
		}
		echo "</table>";
	}
	if($action=="delete"){
		$key = mysql_real_escape_string($_GET["key"]);
		$sql="delete from companies where company_key=$key";
		$conn->query($sql);
		$sql = "delete from times where company = $key";
		$conn->query($sql);
		$sql = "delete from add_subtract_hours where company = $key";
		$conn->query($sql);
	}
	if($action=="add"){
		$descrp = mysql_real_escape_string($_GET["descrp"]);
		$sql="insert into companies (company_description)
			values ('".$descrp."')";
		$conn->query($sql);
	}
?>
