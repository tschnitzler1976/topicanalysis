<?php
/* Here the user has the possibility to add pdffulltexts for this topic analysis if the user is of the opinion that too less
pdffulltexts are in the database after he or she read the information about the amount status of pdffulltexts in the database
at "04_fill_and_complete/handlers/dummy_search_engine/topic_analysis_04_fill_and_complete_treatment_02.php"
*/ 

	set_include_path("../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	
	if(isset($_POST["select_existing_topic_analysis"])){
		$htmlcodeforsnapshot[0]=1;
		$htmlcodeforsnapshot[1]='';
		$topicanalysisid=htmlstringtostring($_POST["select_existing_topic_analysis"]);		
		$returndbconnect=dbconnect();
			
		//Get Topic Analysis Name based on its id in order to show it.
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id=$topicanalysisid","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$returndbfetchfield=dbfetchfield($returndbfetcharray,'name');
		$topicanalysisname=$returndbfetchfield;		
		$htmlcodeforsnapshot=createsnapshotoftablesearchresultsforuploadingpdffulltexts($topicanalysisid,$htmlcodeforsnapshot);
						echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	
	<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<style type="text/css">
   {font-weight:normal;color:#181818;background-color:#fffccf}b.b4{font-weight:normal;color:#0c0c0c;background-color:#fffccf}b.b2{font-weight:normal;color:#242424;background-color:#fffeef}
.auto-style1 {
	margin-left: 0px;
}
</style>
	</head>
	<body>
	<form method="post" action="topic_analysis_05_upload_further_pdffulltexts_02.php">
		<input name="texttopicanalysisid" type="hidden" value="' . $topicanalysisid .'"/>
		<br />
		<table style="width: 100%">
			<tr>
				<td style="width: 481px; height: 79px;">
				<table style="width: 159%">
					<tr>
						<td style="width: 644px; height: 23px;">Upload manually further pdffulltexts from a public directory via http-fullpath for the lda of the topic analysis
						' . $topicanalysisname . ' .</td>
						<td style="height: 23px"><a href="../topic_analysis_00_menu.php">Back to the menu</a></td>
					</tr>
					<tr>
						<td style="width: 644px; height: 23px;">&nbsp;</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 644px; height: 23px;">Because not any pdffiles can be downloaded directly (sometimes they
						are behind a pay-wall or to download them you have to interact with the http-server with your webbrowser and
						not with tools like wget.exe) you can upload them here from your public directory via http.
						Please upload the pdffulltexts you have on your public directory by
						inserting the http-fullpath to the pdffulltext in your public directory in a textfield of a particular
						scientific article below. If the http-fullpath like http://userpages.uni-koblenz.de/~user/example.pdf is
						wrong and no pdf-file is accessed then no file is downloaded. The file format will be checked. The content
						of the file will be not checked. Thus be careful: The pdffulltext you would like to upload must
						exactly match the scientific article in the row that has the same author(s), title, year and conference.
						Any upload is just possible for already existing scientific articles in any row below where the column
						"path_to_pdffulltext" is empty and where this column has a textfield for the http-fullpath to be
						submitted.</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 255px">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					</table>
					<table style="width: 445px"><tr><td>&nbsp;</td>
					<td style="width: 334px">&nbsp;</td><td style="width: 191px">
					<input name="submit_topic_analysis_05_upload_further_pdffulltexts_02" type="submit" value="Upload pdffulltext(s)" class="auto-style1" style="width: 265px"/></td></tr><tr><td>
						&nbsp;</td><td style="width: 334px">&nbsp;</td>
						<td style="width: 191px">&nbsp;</td></tr><tr><td>&nbsp;</td>
						<td style="width: 334px">&nbsp;</td><td style="width: 191px">
						&nbsp;</td></tr></table>

					
			' . $htmlcodeforsnapshot[1] . '
	
	</form>
	</body>
	
					</html>';
						
	}else{//No topic analysis id found.
		echo 'No topic analysis selected. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
	}
?>