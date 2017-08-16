<?php
/*Algorithm:
  1. d) Save anything that is relevant for the new LDA and that is submitted from "topic_analysis_10_add_LDA_treatment_01.php"
  to table "lda".
*/ 

	set_include_path("../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	
	if(isset($_POST["texttopicanalysisid"])){
		$topicanalysisid=htmlstringtostring($_POST["texttopicanalysisid"]);
		$createsnapshottablesearchresults=true;
		$snapshottablesearchresult='';
		if(isset($_POST["textldaname"])){
			$ldaname=htmlstringtostring($_POST["textldaname"]);
			if(ltrim($ldaname)!=''){
				//Already in use?
				$returndbconnect=dbconnect();
				$returndbselect=dbselect($returndbconnect,"lda","name='$ldaname'","name");
				$returndbnumrows=dbnumrows($returndbselect);
				$issettextldaid=isset($_POST["textldaid"]);
				if(($returndbnumrows==0 && $issettextldaid==false)||$issettextldaid){//not in use if adding
					//Fetch the number of topics this LDA should create.
	
					if(isset($_POST["textnumberoftopics"])){
						$numberoftopics=htmlstringtostring($_POST["textnumberoftopics"]);
						if(ltrim($numberoftopics)!=''){
							if(ltrim(is_numeric($numberoftopics))){							
								if(ltrim($numberoftopics<1)){
									echo 'The field for the amount of topics LDA should provide was less than 1. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
								}else{	 												
									if(ltrim($numberoftopics>20)){
										echo 'The field for the amount of topics LDA should provide was greater than 20. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
									}else{
										//Get Topic Analysis Name based on its id in order to show it.
										$returndbselect=dbselect($returndbconnect,"topic_analysis","id=$topicanalysisid","id");
										$returndbfetcharray=dbfetcharray($returndbselect);
										$returndbfetchfield=dbfetchfield($returndbfetcharray,'name');
										$topicanalysisname=$returndbfetchfield;
										mysqli_free_result($returndbselect);

										//Select the search_strings_ids	for this topicanalysisid
										$dbselectresult=dbselect($returndbconnect,"search_strings","id_topic_analysis='$topicanalysisid'","id");
										$dbnumrowsvar=dbnumrows($dbselectresult);
										if($dbnumrowsvar>0){
											//We have more than 0 searchstrings for this topic analysis. Thus continue.
											$zaehler=0;
											while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($dbselectresult)){							
												$searchstringsids[$zaehler]=$id;
												$searchstringsnames[$zaehler]=$name;
												$zaehler++;
											}
											mysqli_free_result($dbselectresult);			
											//Secondly we select the relevant search results
											$searchstringhasnosearchresults=false;
											$zaehler3=0;
											
											for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){											
												$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","id");			
												$dbnumrowsvar2=dbnumrows($returndbselect);
												echo $dbnumrowsvar2;
												if($dbnumrowsvar2==0){
													echo 'No search results found for search string ' . $searchstringsnames[$zaehler2] . ' You should preprocess <a href="../topic_analysis_07_a_preprocessing_abstracts.php">abstracts</a> and <a href="../topic_analysis_07_b_preprocessing_pdffulltexts.php">pdffulltexts</a> first.</br>';
													$searchstringhasnosearchresults=true;
												}
											}
											mysqli_free_result($returndbselect);
											if($searchstringhasnosearchresults==false){
												//Save the new LDA from "topic_analysis_10_add_LDA_treatment_01.php".	
												$boolconferenceselected=false;
												if(isset($_POST["select_conference"])){
													$conferenceselected=htmlstringtostring($_POST["select_conference"]);
													if(ltrim($conferenceselected)!=''){
														$boolconferenceselected=true;
													}
												}else{
													$conferenceselected='';
												}
												
												$boolyearfromselected=false;
												if(isset($_POST["select_year_from"])){
													$yearfromselected=htmlstringtostring($_POST["select_year_from"]);
													if(ltrim($yearfromselected)!=''){
														$boolyearfromselected=true;
													}
												}else{
													$yearfromselected='';
												}
																								
												$boolyeartoselected=false;
												if(isset($_POST["select_year_to"])){
													$yeartoselected=htmlstringtostring($_POST["select_year_to"]);
													if(ltrim($yeartoselected)!=''){
														$boolyeartoselected=true;
													}
												}else{
													$yeartoselected='';
												}
												
												$boolyearfromgreateryearto=false;
												if($boolyearfromselected==true){
													if($boolyeartoselected==true){
														if((int)$yearfromselected>(int)$yeartoselected){
															echo 'Please select a "year from" that is before "year to".</br>Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
															$boolyearfromgreateryearto=true;
														}
													}
												}

												if($boolyearfromgreateryearto==false){												
												
													if(isset($_POST["textldaid"])){
														$ldaid=$_POST["textldaid"];
													/*We modfiy table "lda" because we made modifications at
													  topic_analysis_11_modify_LDA_treatment_01 where new selections could be made.
													  Thus we have to delete old selections in table lda_id_search_results first and
													  then we have to delete the old row in table "lda" that does not contain the modifications from
													  the form of topic_analysis_11_modify_LDA_treatment_01.php.*/
														$returndbdelete=dbdelete($returndbconnect,"lda_id_search_results","id_lda='$ldaid'");			
														if($returndbdelete==0){
															$createsnapshottablesearchresults=false;			
														}	
	 													$returndbdelete=dbdelete($returndbconnect,"lda","id='$ldaid'");			
														if($returndbdelete==0){
															$createsnapshottablesearchresults=false;
														}
													}	
													
													if($issettextldaid){//unless a new lda is added but an existing lda is modified																																											
														//Delete former version of this lda in table "lda" if we modify in order not to have more than 1 row of the same lda in table "lda".
														$returndbdelete=dbdelete($returndbconnect,"lda","name='$ldaname'");
														if($returndbdelete==0){
															echo "DELETE-Query for row of table lda before modifications went wrong";
															$returndeleteresearchquestions=0;
														}
														
														//DELETE former folders and files of this lda if these folders and files exist.				
														if(is_dir(pathtolda() . $topicanalysisname . '/' . $ldaname)==false){
															rrmdir(pathtolda() . $topicanalysisname . '/' . $ldaname);
														}														
													}
													
													//Create the topic analysis folder if it does not exist
													if(is_dir(pathtolda() . $topicanalysisname)==false){
														mkdir(pathtolda() . $topicanalysisname);
													}
													//Create the lda folder for this topic analysis folder if it does not exist
													if(is_dir(pathtolda() . $topicanalysisname . '/' . $ldaname)==false){
														mkdir(pathtolda() . $topicanalysisname . '/' . $ldaname);
													}
													
													//Create the folders for input-, execute- and outputfiles for this lda if they do not exist				
													if(is_dir(pathtolda() . $topicanalysisname . '/' . $ldaname . '/input')==false){
														mkdir(pathtolda() . $topicanalysisname . '/' . $ldaname . '/input');
													}
													
													if(is_dir(pathtolda() . $topicanalysisname . '/' . $ldaname . '/execute')==false){
														mkdir(pathtolda() . $topicanalysisname . '/' . $ldaname . '/execute');
													}

													if(is_dir(pathtolda() . $topicanalysisname . '/' . $ldaname . '/output')==false){
														mkdir(pathtolda() . $topicanalysisname . '/' . $ldaname . '/output');
													}
													
													//We create an executeable-file for R for this lda and save it in the executeable-folder
													createexecuteableforr($topicanalysisname,$ldaname,$numberoftopics);
													
													$dirname=pathtolda() . $topicanalysisname . '/' . $ldaname . '/';
													/*At last, the modifications from the form from
													 topic_analysis_11_modify_LDA_treatment_01 and the name of the 
													 directory to the input-, executeable- and outputfiles are inserted to
													 table "lda"*/
													$returndbinsert=dbinsert($returndbconnect,"lda","(id_topic_analysis,name,number_of_topics_to_output,conference_selected,year_from_selected,year_to_selected,dirname)","('$topicanalysisid','$ldaname','$numberoftopics','$conferenceselected','$yearfromselected','$yeartoselected','$dirname')");
													if($returndbinsert==false){
														$createsnapshottablesearchresults=false;
													}
													
													/*Give a snapshot of table search_results about what the user selected
													at topic_analysis_10_add_LDA_treatment_01.php or at
													topic_analysis_11_modify_LDA_treatment_01.php*/
													$dbselectresult=dbselect($returndbconnect,"lda","name='$ldaname'","id");
    												$dbfetcharray=dbfetcharray($dbselectresult);
	            									$idlda=dbfetchfield($dbfetcharray,'id');
													mysqli_free_result($dbselectresult);													
													//SELECT the ids from table "search_results" that must be displayed in the snapshot.													
													//Any possible case referring to the selected conference, year_from and year_to must be taken into consideration for this.
													if($boolconferenceselected==false&&$boolyearfromselected==false&&$boolyeartoselected==false){
														//Any row belonging to this $topicanalysisid is selected

														$zaehler2=0;
														for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
															$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler]' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","id");			
															while(list($id) = mysqli_fetch_row($returndbselect)){							
																$idsearchresult[$zaehler2]=$id;
																$zaehler2++;
															}
														}

													}elseif($boolconferenceselected==false&&$boolyearfromselected==false&&$boolyeartoselected==true){
														//Any row where any year until "year_to" is selected
														$zaehler2=0;
														for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
															$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler]' AND year<='$yeartoselected' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","year asc");			
															while(list($id) = mysqli_fetch_row($returndbselect)){							
																$idsearchresult[$zaehler2]=$id;
																$zaehler2++;
															}
														}

													}elseif($boolconferenceselected==false&&$boolyearfromselected==true&&$boolyeartoselected==false){
														//Any row where any year after "year_from" including "year_from" is selected
														$zaehler2=0;
														for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
															$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler]' AND year>='$yearfromselected' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","year asc");			
															while(list($id) = mysqli_fetch_row($returndbselect)){							
																$idsearchresult[$zaehler2]=$id;
																$zaehler2++;
															}
														}

													}elseif($boolconferenceselected==false&&$boolyearfromselected==true&&$boolyeartoselected==true){
														//Any row between "year_from" and "year_to" is selected
														$zaehler2=0;
														for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
															$returndbselect=dbselect($returndbconnect,"search_results", "year BETWEEN '$yearfromselected' AND '$yeartoselected' AND id_search_strings='$searchstringsids[$zaehler]' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","year asc");			
															while(list($id) = mysqli_fetch_row($returndbselect)){							
																$idsearchresult[$zaehler2]=$id;
																$zaehler2++;
															}
														}

													}elseif($boolconferenceselected==true&&$boolyearfromselected==false&&$boolyeartoselected==false){
														//Any row with a selected conference is selected
														$zaehler2=0;
														for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
															$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler]' AND conference='$conferenceselected' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","id");			
															while(list($id) = mysqli_fetch_row($returndbselect)){							
																$idsearchresult[$zaehler2]=$id;
																$zaehler2++;
															}
														}
													
													}elseif($boolconferenceselected==true&&$boolyearfromselected==false&&$boolyeartoselected==true){
														//Any row with a selected conference and years until "year_to" is selected
														$zaehler2=0;
														for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
															$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler]' AND conference='$conferenceselected' AND year<='$yeartoselected' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","year asc");			
															while(list($id) = mysqli_fetch_row($returndbselect)){							
																$idsearchresult[$zaehler2]=$id;
																$zaehler2++;
															}
														}
														
													}elseif($boolconferenceselected==true&&$boolyearfromselected==true&&$boolyeartoselected==false){
														//Any row with a selected conference and years since "year_from" is selected
														$zaehler2=0;
														for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
															$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler]' AND conference='$conferenceselected' AND year>='$yearfromselected' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","year asc");			
															while(list($id) = mysqli_fetch_row($returndbselect)){						
																$idsearchresult[$zaehler2]=$id;
																$zaehler2++;
															}
														}
														
													}elseif($boolconferenceselected==true&&$boolyearfromselected==true&&$boolyeartoselected==true){
														//Any row with a selected conference and years between "year_from" and "year_to" is selected
														$zaehler2=0;
														for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
															$returndbselect=dbselect($returndbconnect,"search_results", "year BETWEEN '$yearfromselected' AND '$yeartoselected' AND id_search_strings='$searchstringsids[$zaehler]' AND conference='$conferenceselected' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","year asc");																
															if(dbnumrows($returndbselect)>0){
																while(list($id) = mysqli_fetch_row($returndbselect)){							
																	$idsearchresult[$zaehler2]=$id;
																	$zaehler2++;
																}
															}
														}
													}
													if(isset($_POST["textldaid"])){												
														$modifyoradd='Modifying';
													}else{
														$modifyoradd='Adding';
													}
													if($createsnapshottablesearchresults==false){
														$messagetouser='Results for table "lda" could not be saved because an error occurred.';
														$snapshottablesearchresult='';
													}else{
														if(isset($idsearchresult)){	
															$messagetouser='Results for LDA "' . $ldaname . '" are saved correctly. The snapshot of the data is below and will be selected by LDA "' . $ldaname . '"  if you <a href="../topic_analysis_13_execute_LDA.php">execute</a> LDA "' . $ldaname . '".';														
															$snapshottablesearchresult=createsnapshotoftablesearchresultsafteraddingmodifyinglda($idsearchresult);
														}else{
															$messagetouser='Results for LDA "' . $ldaname . '" are saved correctly. No scientific articles in your selection.';														
															$snapshottablesearchresult='';
														}	
													}
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
		<br />
		<table style="width: 100%">
			<tr>
				<td style="width: 481px; height: 79px;">
				<table style="width: 159%">
					<tr>
						<td style="width: 644px; height: 23px;">'. $modifyoradd .' LDA ' . $ldaname . ' for
						topic analysis "' . $topicanalysisname . '".</td>
						<td style="height: 23px"><a href="../topic_analysis_00_menu.php">Back to the menu</a></td>
					</tr>
					<tr>
						<td style="width: 644px; height: 23px;">&nbsp;</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					</table><table style="width: 445px"><tr><td style="width: 191px">
						' . $messagetouser . '</td></tr></table>' . $snapshottablesearchresult . '
	
					</body>
					</html>';
													
													
												}		
											}else{//No search results for at least one search string id. 
													echo 'Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
											}
										}else{//No search strings found for the topic analysis.
											echo 'No search strings for this topic analysis.<a href="../topic_analysis_00_menu.php">menu</a>.</br>';
										}
									}
								}						
							}else{
							echo 'The field for the amount of topics LDA should provide was not numeric. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a> and transfer this information as a number.</br>'; 												
							}							
						}else{
							echo 'The field for the amount of topics LDA should provide was empty. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a> and transfer this information.</br>'; 					
						}
					}else{
						echo 'The field for the amount of topics LDA should provide was empty. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a> and transfer this information.</br>'; 					
					}		
				}else{
					echo 'The LDA name you transferred is already in use. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a> and transfer another LDA name.</br>'; 					
				}
			}else{
				echo 'The field for the name of the LDA was empty. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a> and transfer this information.</br>'; 					
			}
		}else{
			echo 'The field for the name of the LDA was empty. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a> and transfer this information.</br>'; 							
		}
	}else{
		echo 'No topic analysis selected. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
	}													
?>