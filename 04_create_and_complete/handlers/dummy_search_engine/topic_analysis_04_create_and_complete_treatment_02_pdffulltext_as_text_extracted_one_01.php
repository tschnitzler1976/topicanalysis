<?php
	set_include_path("../../../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	if(isset($_GET["topicanalysisid"])){
		$zaehler3=0;	
		$returndbconnect=dbconnect();
		$topicanalysisid=htmlstringtostring($_GET["topicanalysisid"]);
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id='$topicanalysisid'","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$topicanalysisname=dbfetchfield($returndbfetcharray,'name');

		$dbselectresult1=dbselect($returndbconnect,"search_strings","id_topic_analysis='$topicanalysisid'","id");
		$dbnumrowsvar1=dbnumrows($dbselectresult1);
		$textareafields='';
		if($dbnumrowsvar1>0){
		//We have more than 0 searchstrings for this topic analysis. Thus continue.
			$zaehler=0;
			while(list($id) = mysqli_fetch_row($dbselectresult1)){							
				$searchstringsids[$zaehler]=$id;
				$zaehler++;
			}
			mysqli_free_result($dbselectresult1);
			for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND pdffulltext_as_text<>''","id");			
				$dbnumrowsvar2=dbnumrows($returndbselect);
				if($dbnumrowsvar2>0){
					while(list($id) = mysqli_fetch_row($returndbselect)){							
						/*We fetch the id in order to address the pdffulltext_as_text_extracted that manually has to be updated by the user.*/
						$id_search_result=$id;
						$returndbselect2=dbselect($returndbconnect,"search_results", "id='$id_search_result' AND pdffulltext_as_text_extracted=''","id");			
						$dbnumrowsvar3=dbnumrows($returndbselect2);
						if($dbnumrowsvar3>0){
							$returndbfetcharray=dbfetcharray($returndbselect2);
							$title=dbfetchfield($returndbfetcharray,'title');							
							$authors=dbfetchfield($returndbfetcharray,'authors');
							$conference=dbfetchfield($returndbfetcharray,'conference');
							$year=dbfetchfield($returndbfetcharray,'year');
							$pdffulltextastext=dbfetchfield($returndbfetcharray,'pdffulltext_as_text');
							$pdffulltextastext=base64_decode($pdffulltextastext);
							$textareafields=$textareafields . '<tr><td>Title: ' . $title . '</td>
							<td>&nbsp;</td></tr><tr><td>Author(s): ' . $authors . '</td><td>&nbsp;</td></tr><tr><td>Conference: ' . $conference . '</td>
							<td>&nbsp;</td></tr><tr><td>Year: ' . $year . '</td><td>&nbsp;</td></tr><tr><td style="height: 236px; width: 481px">
							<textarea name="textarea_pdffulltext_as_text_extracted_id_' . $id_search_result . '" style="width: 657px; height: 231px">' . $pdffulltextastext .'</textarea></td>
							<td style="height: 236px">&nbsp;</td></tr>';
							$zaehler3++;
						}
					}
				}
			}
			mysqli_free_result($returndbselect);
			mysqli_free_result($returndbselect2);
			$headline='<tr>
				<td style="width: 528px; height: 23px;" class="auto-style1">Manual extraction form for the converted pdffulltexts for
				 the topic analysis "' . $topicanalysisname . '".</td>
				<td style="height: 23px">&nbsp;</td>
			</tr><tr>
				<td style="width: 528px; height: 23px;" class="auto-style1"><a href="javascript:close();">Close this window</a></td>
				<td style="height: 23px">&nbsp;</td>
			</tr><tr>
				<td style="width: 528px; height: 23px;" class="auto-style1">A good way to extract is to get the text from the abstract and the introduction.</td>
				<td style="height: 23px">&nbsp;</td>
			</tr>';
			
		   echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	
	<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<style type="text/css">
   {font-weight:normal;color:#181818;background-color:#fffccf}b.b4{font-weight:normal;color:#0c0c0c;background-color:#fffccf}b.b2{font-weight:normal;color:#242424;background-color:#fffeef}
	.auto-style1 {
		text-align: center;
	}
	</style>
	</head>
	
	<body>
		<form method="post" action="topic_analysis_04_create_and_complete_treatment_02_pdffulltext_as_text_extracted_all_02.php">
		<table style="width: 100%">
			<tr>
				<td style="width: 775px; height: 79px;">
				<table style="width: 159%">' . $headline . '
					<tr>
						<td style="width: 743px; height: 23px;" class="auto-style1">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>' . $textareafields . '
						<tr>
						<td style="width: 743px; height: 23px;"><input name="submit_topic_analysis_04_create_and_complete_treatment_02_pdffulltext_as_text_extracted_all_01" type="submit" value="Extract"/></td>
						<td><input name="hiddentexttopicanalysisid" type="hidden" value="' . $topicanalysisid .'"/></td>
					</tr>
					</table>
				</td>
				<td style="height: 79px"><input name="hiddentextzaehler3" type="hidden" value="' . $zaehler3 .'"/></td>
			</tr>
			</table></form></body>
			</html>';		
	
		}else{
			echo 'Search strings missing for this topic analysis. <a href="javascript:close();">Close this window</a>';
		}
		dbdisconnect($returndbconnect);	
	}else{
			echo 'No topic analysis provided. <a href="javascript:close();">Close this window</a>';
	}
?>