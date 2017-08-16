<?php

/*Algorithm:
  0. Select the LDA.
  	1. d) Delete existing LDA
*/ 

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
<form method="post" action="12_delete_LDA/topic_analysis_12_delete_LDA.php">

	Select an existing topic analysis for a LDA to delete:<br />
	<table style="width: 100%">
		<tr>
			<td>
			<select name="select_existing_topic_analysis" style="width: 402px" tabindex="1">
			';
			while(list($id,$name) = mysqli_fetch_row($returndbselect)){
        	echo '<option value="' . $id . '">' . $name . '</option>';
			}
			echo '</select></td>
			<td><a href="topic_analysis_00_menu.php">Go back to the menu</a></td>
		</tr>
		<tr>
			<td style="height: 23px;">
			<table style="width: 136%">
				<tr>
					<td style="width: 96px">&nbsp;</td>
					<td>
<input name="submit_topic_analysis_12_delete_LDA" type="submit" value="Select topic analysis" class="auto-style1" style="height: 26px; width: 182px;" tabindex="2"/></td>
				</tr>
			</table>
			</td>
			<td style="height: 23px">&nbsp;</td>
		</tr>
		</table>

</form>
</body>

</html>';

?>