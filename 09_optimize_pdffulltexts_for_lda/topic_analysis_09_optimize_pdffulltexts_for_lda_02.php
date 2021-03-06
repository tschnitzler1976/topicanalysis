<?php

	set_include_path("../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	
	/*We select anything here what we have already selected in
	topic_analysis_09_optimize_pdffulltexts_for_lda_01.php in order to recover the ids
	for the textareas-fields with the already preprocessed pdffulltexts for lda that will be saved at the end of this script.*/
	if(isset($_POST["hiddentexttopicanalysisid"])){
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
		$zaehler=0;
		while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($dbselectresult1)){							
			$searchstringsids[$zaehler]=$id;
			$zaehler++;
		}
		mysqli_free_result($dbselectresult1);	
		for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
			$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND pdffulltext_for_lda<>'' AND preprocessing_pdffulltext_already_done=1 AND exclude=0","id");			
			while(list($id) = mysqli_fetch_row($returndbselect)){							
				/*We fetch the id in order to address the pdffulltext_as_text_extracted that manually has to be updated by the user.*/
				$id_search_result=$id;
				//The id_search_result from above is OK. Thus we can get our textarea field.
				$textareafield='textarea_pdffulltext_as_text_extracted_id_' . $id_search_result;
				if(isset($_POST['' . $textareafield . ''])){
					$textareafieldforupdate=base64_encode($_POST['' . $textareafield . '']);
					$returndbupdate=dbupdate($returndbconnect,"search_results","pdffulltext_for_lda='$textareafieldforupdate'","id='$id_search_result'");
					if($returndbupdate){
						$usermessage=$usermessage . '<tr><td>UPDATE-Query for column "pdffulltext_for_lda" for the id ' . $id_search_result . ' was successful.</td><td>&nbsp;</td></tr>';
					}else{	
						$usermessage=$usermessage . '<tr><td>UPDATE-Query for column "pdffulltext_for_lda" for the id ' . $id_search_result . ' failed.</td><td>&nbsp;</td></tr>';
					}
				}else{
					$usermessage=$usermessage . '<tr><td>The text area field for the search result id ' . $id_search_result . ' does not exist.</td><td>&nbsp;</td></tr>';
				}	
			}
		}
			
			mysqli_free_result($returndbselect);
				$headline='<td style="width: 528px; height: 23px;" class="auto-style1">Results for the manual optimization of extracted texts from pdffulltexts for
				 the topic analysis "' . $topicanalysisname . '".</td>
				<td style="height: 23px">&nbsp;</td>
			</tr><tr>
				<td style="width: 528px; height: 23px;" class="auto-style1">Back to the <a href="../topic_analysis_00_menu.php">menu</a></td>
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
	
		dbdisconnect($returndbconnect);	
	}else{
		echo 'No topic analysis provided. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>';	}
?>