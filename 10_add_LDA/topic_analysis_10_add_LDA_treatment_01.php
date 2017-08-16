<?php
/*Algorithm:
  1. d) Add a LDA, i.e. let the user select abstracts and pdffulltexts of the already selected topic analysis. After this the
  LDA must be added. Type in the selection of the abstracts and pdffulltexts for this new LDA, the name for the new LDA and
  and the amount of topics LDA must produce.
*/ 

	set_include_path("../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	
	if(isset($_POST["select_existing_topic_analysis"])){
		$topicanalysisid=htmlstringtostring($_POST["select_existing_topic_analysis"]);		
		$returndbconnect=dbconnect();
			
		//Get Topic Analysis Name based on its id in order to show it.
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id=$topicanalysisid","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$returndbfetchfield=dbfetchfield($returndbfetcharray,'name');
		$topicanalysisname=$returndbfetchfield;
		
		/*We provide the search strings in order to be able to navigate through table "search_results".*/
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
			for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","id");			
				$dbnumrowsvar2=dbnumrows($returndbselect);
				if($dbnumrowsvar2==0){
					echo 'No search results found for search string ' . $searchstringsnames[$zaehler2] . '. You should preprocess <a href="../topic_analysis_07_a_preprocessing_abstracts.php">abstracts</a> and <a href="../topic_analysis_07_b_preprocessing_pdffulltexts.php">pdffulltexts</a> first.</br>';
					$searchstringhasnosearchresults=true;
				}
			}
			mysqli_free_result($returndbselect);						
			if($searchstringhasnosearchresults==false){
					/*We have to look in each row of table search_results whether the value of the column
		  		     exclusion_already_done is 1. If the value of "exclusion_already_done" is 0 then an exclusion did not happen before.
					 Thus we prevent the user from proceeding. The same for the status of preprocessed or not preprocessed abstracttexts and
					 pdffulltexts. If either the abstracttexts or the pdffullltexts were not preprocessed then we will not
					 add a new the lda.*/
					$exclusionhappenedbefore=true;
					$preprocessingabstracttexthappenedbefore=true;
					$preprocessingpdffulltexthappenedbefore=true;
					for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
						$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND exclusion_already_done=0","id");			
						$dbnumrowsvar2=dbnumrows($returndbselect);
						if($dbnumrowsvar2>0){	
							$exclusionhappenedbefore=false;
						}
						$returndbselect2=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND abstracttext<>'' AND exclude=0 AND preprocessing_abstracttext_already_done=0","id");			
						$dbnumrowsvar3=dbnumrows($returndbselect2);
						if($dbnumrowsvar3>0){	
							$preprocessingabstracttexthappenedbefore=false;
						}
						$returndbselect3=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND pdffulltext_as_text_extracted<>'' AND exclude=0 AND preprocessing_pdffulltext_already_done=0","id");			
						$dbnumrowsvar4=dbnumrows($returndbselect3);
						if($dbnumrowsvar4>0){	
							$preprocessingpdffulltexthappenedbefore=false;
						}
					}
							
					if($exclusionhappenedbefore==true){			
						if($preprocessingabstracttexthappenedbefore==true){
							if($preprocessingpdffulltexthappenedbefore==true){
							

								/*The exclusion procedure and the preprocessing procedure for each abstract and each pdffulltext for this topic analysis already happened. Thus we continue. We provide a grouped
								representation of conferences and years. Then the user can raise the understandability of the LDA
								result because he can refer to a particuler conference or from year x to year y while interpreting the LDA
								analysis results.
								*/
								//GROUP-SELECTION of conference ORDER BY conference
								$returndbselect1=dbselectgrouped($returndbconnect,"search_results","conference","conference");
								//GROUP-SELECTION of year (year_from) ORDER BY year
								$returndbselect2=dbselectgrouped($returndbconnect,"search_results","year","year");
								
								//GROUP-SELECTION of year (year_to) ORDER BY year
								$returndbselect3=dbselectgrouped($returndbconnect,"search_results","year","year");
			
								
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
			<form method="post" action="../11_modify_LDA/topic_analysis_11_modify_LDA_treatment_02.php">
			
				<br />
				<table style="width: 100%">
					<tr>
						<td style="width: 453px">
						Add LDA for search results 
										of the topic analysis "' . $topicanalysisname . '":</td>
						<td><a href="../topic_analysis_00_menu.php">Go back to the menu</a></td>
					</tr>
					<tr>
						<td style="width: 453px">
						<input name="texttopicanalysisid" type="hidden" value="' . $topicanalysisid .'"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
						Please make a choice below whether you would like to analyze abstracts 
									and pdffulltexts with a selected "conference" and/or 
									"from year" "until year" in addition to search 
									results that passed the excluding procedure before (If 
									you leave the drop down boxes empty then any abstract 
									and any pdffulltext are considered that passed the
									<a href="../topic_analysis_06_exclude_search_results.php">
									exclusion procedure</a> before.).</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
						Next enter how much topics LDA should 
									be create. The sum of LDA-topics forms the 
									classification scheme (see slide 26 of the
									<a href="../00_general/sms.pdf">systematic mapping study 
									slides</a>).</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
						&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
						conference:</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
						<select name="select_conference" style="width: 180px">
						<option value=""></option>
						';
						while(list($id,$id_search_strings,$exclude,$authors,$title,$conference) = mysqli_fetch_row($returndbselect1)){
			        	echo '<option value="' . $conference . '">' . $conference . '</option>';
						}
						echo '
						</select></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
						from year:</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
						<select name="select_year_from" style="width: 180px">
						<option value=""></option>
						';
						while(list($id,$id_search_strings,$exclude,$authors,$title,$conference,$year) = mysqli_fetch_row($returndbselect2)){
			        	echo '<option value="' . $year . '">' . $year . '</option>';
						}
						echo '
						</select></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
						until year:</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
						<select name="select_year_to" style="width: 180px">
						<option value=""></option>
						';
						while(list($id,$id_search_strings,$exclude,$authors,$title,$conference,$year) = mysqli_fetch_row($returndbselect3)){
			        	echo '<option value="' . $year . '">' . $year . '</option>';
						}
						echo '
						</select></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px; height: 23px;">
						</td>
						<td style="height: 23px"></td>
					</tr>
					<tr>
						<td style="width: 453px">
							LDA: Please give the LDA for the above selections a unique name:</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
						<input name="textldaname" type="text" style="width: 397px; height: 25px"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
							LDA: How much topics do you want
							as LDA-output? (1-20):</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
						<input name="textnumberoftopics" type="text" style="width: 397px; height: 25px"/></td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px">
						&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 453px; height: 23px;">
						<table style="width: 60%">
							<tr>
								<td style="width: 132px">&nbsp;</td>
								<td>
			<input name="submit_topic_analysis_10_add_LDA_treatment_01" type="submit" value="Save data for a new LDA" class="auto-style1" style="height: 26px" tabindex="2"/></td>
							</tr>
						</table>
						</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					</table>
			
			</form>
			</body>
			
			</html>';
						}else{
							echo 'Please <a href="../topic_analysis_07_b_preprocessing_pdffulltexts.php">preprocess</a> first.';
						}
					}else{
						echo 'Please <a href="../topic_analysis_07_a_preprocessing_abstracts.php">preprocess</a> first.';
					}						
				}else{/*We found 0's as field values in column "exclusion_happened_before" in table "search_results". That means
				        the user did not execute the exclusion procedure before. Because of the systematic mapping study presented in
				        the user must pass the exclusion procedure as a prerequisite for classification and creation of the systematic
				        map (compare with anything between slide 21 and slide 34 of
				        https://userpages.uni-koblenz.de/~laemmel/esecourse/slides/sms.pdf*/
						echo 'Please <a href="../topic_analysis_06_exclude_search_results.php">execute the exclusion procedure</a> before adding
						a LDA to the the search results of topic analysis ' . $topicanalysisname . '. If you will not exclude now please go to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
				}	
			}else{//No search results found for a search string.
				echo 'Please go to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';	
			}
		}else{//No search strings found for the topic analysis.
			echo 'No search strings for this topic analysis.<a href="../topic_analysis_00_menu.php">menu</a>.</br>';
		}		
	}else{//No topic analysis id found.
		echo 'No topic analysis selected. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
	}
?>