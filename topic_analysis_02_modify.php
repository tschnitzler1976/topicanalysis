<?php
set_include_path("00_general");
include_once("mysql_db_connection.php");
include_once("mysql_db_queries.php");

$returndbconnect=dbconnect();
$returndbselect=dbselect($returndbconnect,"topic_analysis","1","name");

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<style type="text/css">
.auto-style1 {
	margin-left: 0px;
}
</style>
</head>

<body>
<form method="post" action="02_modify/topic_analysis_02_modify_treatment_01.php">

	Please select an existing topic analysis to modify<br />
	<table style="width: 100%">
		<tr>
			<td style="width: 442px">
			<table style="width: 101%; height: 33px;">
				<tr>
					<td style="width: 404px; height: 31px;">
			<select name="select_existing_topic_analysis" style="width: 402px" tabindex="1">
			';
			while(list($id,$name) = mysqli_fetch_row($returndbselect)){
        	echo '<option value="' . $id . '">' . $name . '</option>';
			}
			echo '</select></td>
					<td style="width: 75px; height: 31px;">
					<input name="select_topic_analysis" type="submit" value="Select" tabindex="2" class="auto-style1" /></td>
				</tr>
			</table>
			</td>
			<td><a href="topic_analysis_00_menu.php">Go back to the menu</a></td>
		</tr>
		</table>

</form>
</body>

</html>';
dbdisconnect($returndbconnect);
?>