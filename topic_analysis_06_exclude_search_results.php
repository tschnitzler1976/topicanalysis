<?php

/*Algorithm:
  0. Select the topic analysis.
  1. c) Show the research questions that are subordinated to the selected topic analysis and relate them to the search results
   	 	from above in order to let the user exclude search results that do not answer the research questions of this topic
   	 	analysis.

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
<form method="post" action="06_exclude/topic_analysis_06_exclude_treatment_01.php">

	Select an existing topic analysis to exclude its search results<br />
	<table style="width: 100%">
		<tr>
			<td style="width: 416px">
			<select name="select_existing_topic_analysis" style="width: 402px" tabindex="1">
			';
			while(list($id,$name) = mysqli_fetch_row($returndbselect)){
        	echo '<option value="' . $id . '">' . $name . '</option>';
			}
			echo '</select></td>
			<td><a href="topic_analysis_00_menu.php">Go back to the menu</a></td>
		</tr>
		<tr>
			<td style="width: 416px; height: 23px;">
			<table style="width: 79%">
				<tr>
					<td style="width: 95px">&nbsp;</td>
					<td style="width: 189px">
<input name="submit_topic_analysis_06_exclude" type="submit" value="Select topic analysis" class="auto-style1" style="height: 26px" tabindex="2"/></td>
				</tr>
			</table>
			</td>
			<td style="height: 23px"></td>
		</tr>
		</table>

</form>
</body>

</html>';

?>