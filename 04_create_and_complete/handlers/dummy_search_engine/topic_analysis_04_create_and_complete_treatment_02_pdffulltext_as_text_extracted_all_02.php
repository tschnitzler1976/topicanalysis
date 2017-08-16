<?php

	set_include_path("../../../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	
	/*We select anything here what we have already selected in
	topic_analysis_04_create_and_complete_treatment_02_pdffulltext_as_text_extracted_all_01.php in order to recover the ids
	for the textareas-fields with the manually extracted texts for pdffulltexts that will be saved at the end of this script.*/
	if(isset($_POST["hiddentextzaehler3"])){
		/*$zaehler3 is a checkvariable that prevents another script than
		"topic_analysis_04_create_and_complete_treatment_02_pdffulltext_as_text_extracted_all_01.php" from loading this script.*/
		$zaehler3=htmlstringtostring($_POST["hiddentextzaehler3"]);
		$topicanalysisid=htmlstringtostring($_POST["hiddentexttopicanalysisid"]);
		$usermessage="";	
		$returndbconnect=dbconnect();
			
		//Let us start to fetch the right search_result_ids that address the submitted textarea fields (see above)
		//Get Topic Analysis Name based on its id in order to show it.
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id=$topicanalysisid","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$returndbfetchfield=dbfetchfield($returndbfetcharray,'name');
		$topicanalysisname=$returndbfetchfield;
		
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
							//The id_search_result from above is OK. Thus we can get our textarea field.
							$textareafield="textarea_pdffulltext_as_text_extracted_id_" . $id_search_result;
							if(isset($_POST['' . $textareafield . ''])){
								$textareafieldforupdate=base64_encode($_POST['' . $textareafield . '']);
								$returndbupdate=dbupdate($returndbconnect,"search_results","pdffulltext_as_text_extracted='$textareafieldforupdate'","id='$id_search_result'");
								if($returndbupdate){
									$usermessage=$usermessage . '<tr><td>UPDATE-Query for column "pdffulltext_as_text_extracted" for the id ' . $id_search_result . ' was successful.</td><td>&nbsp;</td></tr>';
								}else{	
									$usermessage=$usermessage . '<tr><td>UPDATE-Query for column "pdffulltext_as_text_extracted" for the id ' . $id_search_result . ' failed.</td><td>&nbsp;</td></tr>';
								}
								//We delete the not-extracted text in column "pdffulltext_as_text" in order to save disk space.
								$updatemessage="This text was deleted because it was replaced by the manually extracted text in column pdffulltext_as_text_extracted.";
								$updatemessage=base64_encode($updatemessage);
								$returnupdate=dbupdate($returndbconnect,"search_results","pdffulltext_as_text='$updatemessage'","id='$id_search_result'");
								if($returndbupdate){
									$usermessage=$usermessage . '<tr><td>UPDATE-Query for column "pdffulltext_as_text" for the id ' . $id_search_result . ' was successful.</td><td>&nbsp;</td></tr>';
								}else{	
									$usermessage=$usermessage . '<tr><td>UPDATE-Query for column "pdffulltext_as_text" for the id ' . $id_search_result . ' failed.</td><td>&nbsp;</td></tr>';
								}
							}else{
								$usermessage=$usermessage . '<tr><td>The text area field for the search result id ' . $id_search_result . ' does not exist.</td><td>&nbsp;</td></tr>';
							}	
						}
					}
				}
			}
			
			mysqli_free_result($returndbselect);
			//mysqli_free_result($returndbselect2);
				$headline='<td style="width: 528px; height: 23px;" class="auto-style1">Results for the manual extraction of the converted pdffulltexts for
				 the topic analysis "' . $topicanalysisname . '".</td>
				<td style="height: 23px">&nbsp;</td>
			</tr><tr>
				<td style="width: 528px; height: 23px;" class="auto-style1"><a href="javascript:close();">Close this window</a></td>
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
		<table style="width: 100%">
			<tr>' . $headline . '</tr>' . $usermessage . '							
				</table>
				</body>
			</html>';		
	
		}else{
			echo 'Search strings missing for this topic analysis. <a href="javascript:close();">Close this window</a>';
		}
		dbdisconnect($returndbconnect);	
	}else{//This script was called as an alternative to a HTTP-POST-Submit of the script "topic_analysis_04_create_and_complete_treatment_02_pdffulltext_as_text_extracted_all_01.php. Therefore we throw an error message.
		echo 'This scripts depends on a HTTP-POST-Submit of the script "topic_analysis_04_create_and_complete_treatment_02_pdffulltext_as_text_extracted_all_01.php". <a href="javascript:close();">Close this window</a>.</br>';
	}
?>