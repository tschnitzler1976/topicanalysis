<?php
	set_include_path("../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	if(isset($_POST["select_existing_topic_analysis"])){
		$noabstracts=true;
		$returndbconnect=dbconnect();
		$topicanalysisid=htmlstringtostring($_POST["select_existing_topic_analysis"]);
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
			
			/*We have to look in each row of table search_results whether the value of the column
  		     exclusion_already_done is 1. If the value of "exclusion_already_done" is 0 then an exclusion did not happen before.
			 Thus we prevent the user from proceeding. The same for the status of preprocessed or not preprocessed abstracttexts.
			 If the abstracttexts were not preprocessed then we will not optimize because there are no values in column
			 "abstracttext_for_lda" to optimize.*/
			$exclusionhappenedbefore=true;
			$preprocessinghappenedbefore=true;
			for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND exclusion_already_done=0","id");			
				$dbnumrowsvar2=dbnumrows($returndbselect);
				if($dbnumrowsvar2>0){	
					$exclusionhappenedbefore=false;
				}
				$returndbselect2=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND abstracttext<>'' AND preprocessing_abstracttext_already_done=0 AND exclude=0","id");			
				$dbnumrowsvar3=dbnumrows($returndbselect2);
				if($dbnumrowsvar3>0){	
					$preprocessinghappenedbefore=false;
				}
			}
					
			if($exclusionhappenedbefore==true){			
				if($preprocessinghappenedbefore==true){
					for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
						$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND abstracttext<>'' AND preprocessing_abstracttext_already_done=1 AND exclude=0","conference");			
						$dbnumrowsvar2=dbnumrows($returndbselect);
						if($dbnumrowsvar2>0){
							while(list($id,$id_search_strings,$exclude,$authors_row,$title_row,$conference_row,$year_row,$first_link_to_abstracttext,$abstracttext_row,$abstracttext_for_lda_row) = mysqli_fetch_row($returndbselect)){							
								/*We fetch the id in order to address the abstracttext that manually has to be updated by the user.*/
								$id_search_result=$id;
								$authors=$authors_row;
								$title=$title_row;
								$conference=$conference_row;
								$year=$year_row;
								$abstracttext=$abstracttext_for_lda_row;
								$abstracttext=base64_decode($abstracttext);	
								$textareafields=$textareafields . '<tr><td>Title: ' . $title . '</td>
								<td>&nbsp;</td></tr><tr><td>Author(s): ' . $authors . '</td><td>&nbsp;</td></tr><tr><td>Conference: ' . $conference . '</td>
								<td>&nbsp;</td></tr><tr><td>Year: ' . $year . '</td><td>&nbsp;</td></tr><tr><td style="height: 236px; width: 481px">
								<textarea name="textarea_abstracttext_id_' . $id_search_result . '" style="width: 657px; height: 231px">' . $abstracttext .'</textarea></td>
								<td style="height: 236px">&nbsp;</td></tr>';
								$noabstracts=false;
							}
						}
					}
					mysqli_free_result($returndbselect);
					if($noabstracts==false){
						$headline='<tr>
							<td style="width: 528px; height: 23px;" class="auto-style1">Optimization form for abstracttexts for
							 the topic analysis "' . $topicanalysisname . '".</td>
							<td style="height: 23px">&nbsp;</td>
						</tr><tr>
							<td style="width: 528px; height: 23px;" class="auto-style1">If you find special characters that are not wishful for lda-analysis please filter them out..</td>
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
					<form method="post" action="topic_analysis_08_optimize_abstracts_02.php">
					<table style="width: 100%">
						<tr>
							<td style="width: 775px; height: 79px;">
							<table style="width: 159%">' . $headline . '
								<tr>
									<td style="width: 743px; height: 23px;" class="auto-style1">&nbsp;</td>
									<td>&nbsp;</td>
								</tr>' . $textareafields . '
									<tr>
									<td style="width: 743px; height: 23px;"><input name="submit_topic_analysis_04_create_and_complete_treatment_02_pdffulltext_as_text_extracted_all_01" type="submit" value="Optimize"/></td>
									<td><input name="hiddentexttopicanalysisid" type="hidden" value="' . $topicanalysisid .'"/></td>
								</tr>
								</table>
							</td>
							<td style="height: 79px">&nbsp;</td>
						</tr>
						</table></form></body>
						</html>';
					}else{
						echo 'No abstracts found for this topic analysis. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>';			
					}
				}else{
					echo 'Please <a href="../topic_analysis_07_a_preprocessing_abstracts.php">preprocess</a> first.';
				}
			}else{
				echo 'Please run the <a href="../topic_analysis_06_exclude_search_results.php">exclusion procedure</a> before optimizing abstracts. Thank you!';
			}		
		}else{
			echo 'No search strings found for this topic analysis. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>';
		}
		dbdisconnect($returndbconnect);	
	}else{
			echo 'No topic analysis provided. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>';
	}
?>