<?php

/*Algorithm:
  0. Select the topic analysis.
  	1. d) Create new LDA
*/ 
    //0 a Select the topic analysis.
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
<form method="post" action="10_add_LDA/topic_analysis_10_add_LDA_treatment_01.php">

	Select an existing topic analysis for 
	adding a new LDA:<br />
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
					<td style="width: 74px">&nbsp;</td>
					<td>
<input name="submit_topic_analysis_10_add_LDA" type="submit" value="Select a topic analysis for a new LDA" class="auto-style1" style="height: 26px; width: 286px;" tabindex="2"/></td>
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