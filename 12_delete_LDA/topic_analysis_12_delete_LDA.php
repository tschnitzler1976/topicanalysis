<?php

/*Algorithm:
  0. Select the LDA.
  	1. d) Delete existing LDA
*/ 

	set_include_path("../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("php_functions.php");

	if(isset($_POST["select_existing_topic_analysis"])){
		$topicanalysisid=htmlstringtostring($_POST["select_existing_topic_analysis"]);		
		$returndbconnect=dbconnect();
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id='$topicanalysisid'","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$topicanalysisname=dbfetchfield($returndbfetcharray,'name');
		mysqli_free_result($returndbselect);
		$returndbselect2=dbselect($returndbconnect,"lda","id_topic_analysis='$topicanalysisid'","name");
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
<form method="post" action="topic_analysis_12_delete_LDA_treatment_01.php">

	Select an existing LDA of topic analysis "' . $topicanalysisname . '" to delete:<br />
	<table style="width: 100%">
		<tr>
			<td>
			<select name="select_existing_lda" style="width: 402px" tabindex="1">
			';
			while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($returndbselect2)){
        	echo '<option value="' . $id . '">' . $name . '</option>';
			}
			echo '</select></td>
			<td><a href="../topic_analysis_00_menu.php">Go back to the menu</a></td>
		</tr>
		<tr>
			<td style="height: 23px;">
			<table style="width: 136%">
				<tr>
					<td style="width: 96px">&nbsp;</td>
					<td>
<input name="submit_topic_analysis_12_delete_LDA" type="submit" value="Select an existing LDA" class="auto-style1" style="height: 26px; width: 182px;" tabindex="2"/></td>
				</tr>
			</table>
			</td>
			<td style="height: 23px">&nbsp;</td>
		</tr>
		</table>

</form>
</body>

		</html>';

	}else{//no topic analysis found.
			echo 'No topic analysis selected. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
	}

?>