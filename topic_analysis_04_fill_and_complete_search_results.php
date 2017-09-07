<?php
/*Algorithm:
  0. Select the topic analysis.
  1. a) Fetch and save the search results from the internet for the search strings of the second textfield in the modify-html-form.
     b) Complete these search results where necessary according to the user's input in the third textfield in the modify-html-form.

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

<form method="post" action="04_fill_and_complete/handlers/dummy_search_engine/topic_analysis_04_fill_and_complete_treatment_01.php">

	Select an existing topic analysis to fill and complete its search results<br />
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
			<table style="width: 88%">
				<tr>
					<td style="width: 108px; height: 30px;"></td>
					<td style="height: 30px">
<input name="submit_topic_analysis_04_fill_and_complete" type="submit" value="Select topic analysis and fill search results" class="auto-style1" style="height: 26px; width: 333px;" tabindex="2"/></td>
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