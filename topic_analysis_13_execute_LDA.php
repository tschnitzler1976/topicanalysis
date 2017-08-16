<?php
/*Algorithm:
  0. Select the existing LDA.
  	1. d) Make a selection in table search_results for the selections in table lda for this lda. Copy these selections from table search_results in table lda_id_search_results.
  	1. e) Extract abstracts and optionally pdffulltexts for the scientific articles stored in table lda_id_search_results before in files 
  	1. f) Analyze the abstracts and optionally the pdffulltexts  with the help of LDAvis.
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
<form method="post" action="13_execute_LDA/topic_analysis_13_execute_LDA.php">
	<table style="width: 100%">
		<tr><td style="width: 640px">LDA execution for an existing topic 
			analysis</td>
			<td><a href="topic_analysis_00_menu.php">Go back to the menu</a></td>
		</tr>
		<tr><td style="width: 640px">&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr><td style="width: 640px">Did you already manually filtered anything out in the <a href="topic_analysis_08_optimize_abstracts.php">optimization form for abstracts</a> </td>
			<td>&nbsp;</td>
		</tr>
		<tr><td style="width: 640px">and in the <a href="topic_analysis_09_optimize_pdffulltexts_for_lda.php">optimization form for pdffulltexts for lda</a>?</td>
			<td>&nbsp;</td>
		</tr>

		<tr><td style="width: 640px">If no please follow the links to 
			optimization forms above first. Otherwise continue below.</td><td>&nbsp;</td>
		</tr>

		<tr><td style="width: 640px">&nbsp;</td><td>&nbsp;</td>
		</tr>

		<tr><td style="width: 640px">Select an existing topic analysis for an lda to execute:</td><td>
			&nbsp;</td>
		</tr>
		<tr>
			<td>
			<select name="select_existing_topic_analysis" style="width: 402px" tabindex="1">
			';
			while(list($id,$name) = mysqli_fetch_row($returndbselect)){
        	echo '<option value="' . $id . '">' . $name . '</option>';
			}
			echo '</select></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="height: 23px;">
			<table style="width: 136%">
				
			<tr><td style="height: 23px; width: 640px;">
			<table style="width: 136%">
				<tr>
					<td style="width: 96px">&nbsp;</td>
					<td>
<input name="submit_topic_analysis_13_execute_LDA" type="submit" value="Select topic analysis" class="auto-style1" style="height: 26px; width: 182px;" tabindex="2"/></td>
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