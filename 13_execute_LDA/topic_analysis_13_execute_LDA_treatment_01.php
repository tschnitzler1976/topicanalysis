<?php
/*Algorithm:
  0. Select the existing LDA.
  	1. d) Make a selection in table search_results for the selections in table lda for this lda. Copy these selections from table search_results in table lda_id_search_results.
  	1. e) Extract abstracts and optionally pdffulltexts for the scientific articles stored in table lda_id_search_results before in files 
*/ 

	set_include_path("../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	if(isset($_POST["select_existing_lda"])){
		$idlda=$_POST["select_existing_lda"];
		//1. d) Make a selection in table search_results for the selections in table lda for this lda. Copy these selections from table search_results in table lda_id_search_results.
		$returndbconnect=dbconnect();
		//Get any information necessary to do lda.
		$returndbselect=dbselect($returndbconnect,"lda","id=$idlda","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		//Get topic analysis id in order to access rows in table "search_results" that are in the set of selections for this lda.
		$topicanalysisid=dbfetchfield($returndbfetcharray,'id_topic_analysis');
		//Get the name of lda
		$ldaname=dbfetchfield($returndbfetcharray,'name');
		//Get the amount of topics lda must provide as a result.	
		$numberoftopics=dbfetchfield($returndbfetcharray,'number_of_topics_to_output');
		//Selections for this lda.
		$conferenceselected=dbfetchfield($returndbfetcharray,'conference_selected');
		$yearfromselected=dbfetchfield($returndbfetcharray,'year_from_selected');
		$yeartoselected=dbfetchfield($returndbfetcharray,'year_to_selected');
		//Get the path to input_execute_output directory of this topic analysis
		$inputexecuteoutputdir=dbfetchfield($returndbfetcharray,'dirname');
		//Get the path to the pdffulltexts of this lda
		mysqli_free_result($returndbselect);
		
		//Get the topic analysis name for output
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id=$topicanalysisid","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$topicanalysisname=dbfetchfield($returndbfetcharray,'name');
		mysqli_free_result($returndbselect);

		//Select the search_strings_ids	for this topicanalysisid
		$dbselectresult=dbselect($returndbconnect,"search_strings","id_topic_analysis='$topicanalysisid'","id");
		$dbnumrowsvar=dbnumrows($dbselectresult);
		if($dbnumrowsvar>0){
				$zaehler=0;
			while(list($id) = mysqli_fetch_row($dbselectresult)){							
				$searchstringsids[$zaehler]=$id;
				$zaehler++;
			}
			//We have more than 0 searchstrings for this topic analysis. Thus continue.
			/*We have to look in each row of table search_results whether the value of the column
  		     exclusion_already_done is 1. If the value of "exclusion_already_done" is 0 then an exclusion did not happen before.
			 Thus we prevent the user from proceeding. The same for the status of preprocessed or not preprocessed abstracttexts and
			 pdffulltexts. If either the abstracttexts or the pdffullltexts were not preprocessed then we will not
			 execute the lda because we could  not fetch an abstracttext-value if we needed it from
			 column "abstracttext_for_lda". The same for a potential pdffulltext-value to fetch from
			 column "pdffulltext_for_lda".*/
			$exclusionhappenedbefore=true;
			$preprocessingabstracttexthappenedbefore=true;
			$preprocessingpdffulltexthappenedbefore=true;
			for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND exclusion_already_done=0","id");			
				$dbnumrowsvar2=dbnumrows($returndbselect);
				if($dbnumrowsvar2>0){	
					$exclusionhappenedbefore=false;
				}
				$returndbselect2=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND abstracttext<>'' AND preprocessing_abstracttext_already_done=0 AND exclude=0","id");			
				$dbnumrowsvar3=dbnumrows($returndbselect2);
				if($dbnumrowsvar3>0){	
					$preprocessingabstracttexthappenedbefore=false;
				}
				$returndbselect3=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND pdffulltext_as_text_extracted<>'' AND preprocessing_pdffulltext_already_done=0 AND exclude=0","id");			
				$dbnumrowsvar4=dbnumrows($returndbselect3);
				if($dbnumrowsvar4>0){	
					$preprocessingpdffulltexthappenedbefore=false;
				}
			}

			if($exclusionhappenedbefore==true){			
				if($preprocessingabstracttexthappenedbefore==true){
					if($preprocessingpdffulltexthappenedbefore==true){
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
							if($dbnumrowsvar2==0){
								echo 'No search results found for search string ' . $searchstringsnames[$zaehler2] . ' You should preprocess <a href="../topic_analysis_07_a_preprocessing_abstracts.php">abstracts</a> and <a href="../topic_analysis_07_b_preprocessing_pdffulltexts.php">pdffulltexts</a> first.</br>';
								$searchstringhasnosearchresults=true;
							}
						}
						
						mysqli_free_result($returndbselect);
						if($searchstringhasnosearchresults==false){
							$boolconferenceselected=false;
							if(ltrim($conferenceselected)!=''){
								$boolconferenceselected=true;
							}
											
							$boolyearfromselected=false;
							if(ltrim($yearfromselected)!=''){
								$boolyearfromselected=true;
							}
																			
							$boolyeartoselected=false;
							if(ltrim($yeartoselected)!=''){
								$boolyeartoselected=true;
							}
							
							/*INSERT the search_results to table 'lda_id_search_results' that are in the set of the selections for this lda.				
							  SELECT the ids from table "search_results" that must be inserted in table 'lda_id_search_results'.													
							  Any possible case referring to the selected conference, year_from and year_to must be taken into consideration for this.*/
							$insert_lda_id_search_results_ok=false;
							if($boolconferenceselected==false&&$boolyearfromselected==false&&$boolyeartoselected==false){
								//Any row belonging to this $topicanalysisid is selected
			
								$zaehler2=0;
								for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
									$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler]' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","id");			
									while(list($id_row,$id_search_strings_row,$exclude_row,$authors_row,$title_row,$conference_row,$year_row,$first_link_to_abstracttext_row,$abstracttext_row,$abstracttext_for_lda_row,$first_link_to_pdffulltext_row,$path_to_pdffulltext_row,$pdffulltext_as_text_row,$pdffulltext_as_text_extracted_row,$pdffulltext_for_lda_row) = mysqli_fetch_row($returndbselect)){							
										$idsearchresult[$zaehler2]=$id_row;
										$authors[$zaehler2]=$authors_row;
										$title[$zaehler2]=$title_row;
										$conference[$zaehler2]=$conference_row;
										$year[$zaehler2]=$year_row;							
										$abstracttextforlda[$zaehler2]=$abstracttext_for_lda_row;
										$pathtopdffulltext[$zaehler2]=$path_to_pdffulltext_row;
										$pdffulltextforlda[$zaehler2]=$pdffulltext_for_lda_row;
										$zaehler2++;
									}
								}
								$insert_lda_id_search_results_ok=true;
							}elseif($boolconferenceselected==false&&$boolyearfromselected==false&&$boolyeartoselected==true){
								//Any row where any year until "year_to" is selected
								$zaehler2=0;
								for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
									$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler]' AND year<='$yeartoselected' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1","year asc");			
									while(list($id_row,$id_search_strings_row,$exclude_row,$authors_row,$title_row,$conference_row,$year_row,$first_link_to_abstracttext_row,$abstracttext_row,$abstracttext_for_lda_row,$first_link_to_pdffulltext_row,$path_to_pdffulltext_row,$pdffulltext_as_text_row,$pdffulltext_as_text_extracted_row,$pdffulltext_for_lda_row) = mysqli_fetch_row($returndbselect)){							
										$idsearchresult[$zaehler2]=$id_row;
										$authors[$zaehler2]=$authors_row;
										$title[$zaehler2]=$title_row;
										$conference[$zaehler2]=$conference_row;
										$year[$zaehler2]=$year_row;							
										$abstracttextforlda[$zaehler2]=$abstracttext_for_lda_row;
										$pathtopdffulltext[$zaehler2]=$path_to_pdffulltext_row;
										$pdffulltextforlda[$zaehler2]=$pdffulltext_for_lda_row;
										$zaehler2++;
									}
								}
								$insert_lda_id_search_results_ok=true;
			
							}elseif($boolconferenceselected==false&&$boolyearfromselected==true&&$boolyeartoselected==false){
								//Any row where any year after "year_from" including "year_from" is selected
								$zaehler2=0;
								for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
									$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler]' AND year>='$yearfromselected' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","year asc");			
									while(list($id_row,$id_search_strings_row,$exclude_row,$authors_row,$title_row,$conference_row,$year_row,$first_link_to_abstracttext_row,$abstracttext_row,$abstracttext_for_lda_row,$first_link_to_pdffulltext_row,$path_to_pdffulltext_row,$pdffulltext_as_text_row,$pdffulltext_as_text_extracted_row,$pdffulltext_for_lda_row) = mysqli_fetch_row($returndbselect)){							
										$idsearchresult[$zaehler2]=$id_row;
										$authors[$zaehler2]=$authors_row;
										$title[$zaehler2]=$title_row;
										$conference[$zaehler2]=$conference_row;
										$year[$zaehler2]=$year_row;							
										$abstracttextforlda[$zaehler2]=$abstracttext_for_lda_row;
										$pathtopdffulltext[$zaehler2]=$path_to_pdffulltext_row;
										$pdffulltextforlda[$zaehler2]=$pdffulltext_for_lda_row;
										$zaehler2++;
									}
								}
								$insert_lda_id_search_results_ok=true;
			
							}elseif($boolconferenceselected==false&&$boolyearfromselected==true&&$boolyeartoselected==true){
								//Any row between "year_from" and "year_to" is selected
								$zaehler2=0;
								for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
									$returndbselect=dbselect($returndbconnect,"search_results", "year BETWEEN '$yearfromselected' AND '$yeartoselected' AND id_search_strings='$searchstringsids[$zaehler]' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","year asc");			
									while(list($id_row,$id_search_strings_row,$exclude_row,$authors_row,$title_row,$conference_row,$year_row,$first_link_to_abstracttext_row,$abstracttext_row,$abstracttext_for_lda_row,$first_link_to_pdffulltext_row,$path_to_pdffulltext_row,$pdffulltext_as_text_row,$pdffulltext_as_text_extracted_row,$pdffulltext_for_lda_row) = mysqli_fetch_row($returndbselect)){							
										$idsearchresult[$zaehler2]=$id_row;
										$authors[$zaehler2]=$authors_row;
										$title[$zaehler2]=$title_row;
										$conference[$zaehler2]=$conference_row;
										$year[$zaehler2]=$year_row;							
										$abstracttextforlda[$zaehler2]=$abstracttext_for_lda_row;
										$pathtopdffulltext[$zaehler2]=$path_to_pdffulltext_row;
										$pdffulltextforlda[$zaehler2]=$pdffulltext_for_lda_row;
										$zaehler2++;
									}
								}
								$insert_lda_id_search_results_ok=true;
			
							}elseif($boolconferenceselected==true&&$boolyearfromselected==false&&$boolyeartoselected==false){
								//Any row with a selected conference is selected
								$zaehler2=0;
								for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
									$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler]' AND conference='$conferenceselected' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","id");			
									while(list($id_row,$id_search_strings_row,$exclude_row,$authors_row,$title_row,$conference_row,$year_row,$first_link_to_abstracttext_row,$abstracttext_row,$abstracttext_for_lda_row,$first_link_to_pdffulltext_row,$path_to_pdffulltext_row,$pdffulltext_as_text_row,$pdffulltext_as_text_extracted_row,$pdffulltext_for_lda_row) = mysqli_fetch_row($returndbselect)){							
										$idsearchresult[$zaehler2]=$id_row;
										$authors[$zaehler2]=$authors_row;
										$title[$zaehler2]=$title_row;
										$conference[$zaehler2]=$conference_row;
										$year[$zaehler2]=$year_row;							
										$abstracttextforlda[$zaehler2]=$abstracttext_for_lda_row;
										$pathtopdffulltext[$zaehler2]=$path_to_pdffulltext_row;
										$pdffulltextforlda[$zaehler2]=$pdffulltext_for_lda_row;
										$zaehler2++;
									}
								}
								$insert_lda_id_search_results_ok=true;
							
							}elseif($boolconferenceselected==true&&$boolyearfromselected==false&&$boolyeartoselected==true){
								//Any row with a selected conference and years until "year_to" is selected
								$zaehler2=0;
								for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
									$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler]' AND conference='$conferenceselected' AND year<='$yeartoselected' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","year asc");			
									while(list($id_row,$id_search_strings_row,$exclude_row,$authors_row,$title_row,$conference_row,$year_row,$first_link_to_abstracttext_row,$abstracttext_row,$abstracttext_for_lda_row,$first_link_to_pdffulltext_row,$path_to_pdffulltext_row,$pdffulltext_as_text_row,$pdffulltext_as_text_extracted_row,$pdffulltext_for_lda_row) = mysqli_fetch_row($returndbselect)){							
										$idsearchresult[$zaehler2]=$id_row;
										$authors[$zaehler2]=$authors_row;
										$title[$zaehler2]=$title_row;
										$conference[$zaehler2]=$conference_row;
										$year[$zaehler2]=$year_row;							
										$abstracttextforlda[$zaehler2]=$abstracttext_for_lda_row;
										$pathtopdffulltext[$zaehler2]=$path_to_pdffulltext_row;
										$pdffulltextforlda[$zaehler2]=$pdffulltext_for_lda_row;
										$zaehler2++;
									}
								}
								$insert_lda_id_search_results_ok=true;
								
							}elseif($boolconferenceselected==true&&$boolyearfromselected==true&&$boolyeartoselected==false){
								//Any row with a selected conference and years since "year_from" is selected
								$zaehler2=0;
								for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
									$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler]' AND conference='$conferenceselected' AND year>='$yearfromselected' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","year asc");			
									while(list($id_row,$id_search_strings_row,$exclude_row,$authors_row,$title_row,$conference_row,$year_row,$first_link_to_abstracttext_row,$abstracttext_row,$abstracttext_for_lda_row,$first_link_to_pdffulltext_row,$path_to_pdffulltext_row,$pdffulltext_as_text_row,$pdffulltext_as_text_extracted_row,$pdffulltext_for_lda_row) = mysqli_fetch_row($returndbselect)){							
										$idsearchresult[$zaehler2]=$id_row;
										$authors[$zaehler2]=$authors_row;
										$title[$zaehler2]=$title_row;
										$conference[$zaehler2]=$conference_row;
										$year[$zaehler2]=$year_row;							
										$abstracttextforlda[$zaehler2]=$abstracttext_for_lda_row;
										$pathtopdffulltext[$zaehler2]=$path_to_pdffulltext_row;
										$pdffulltextforlda[$zaehler2]=$pdffulltext_for_lda_row;
										$zaehler2++;
									}
								}
								$insert_lda_id_search_results_ok=true;
								
							}elseif($boolconferenceselected==true&&$boolyearfromselected==true&&$boolyeartoselected==true){
								//Any row with a selected conference and years between "year_from" and "year_to" is selected
								$zaehler2=0;
								for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
									$returndbselect=dbselect($returndbconnect,"search_results", "year BETWEEN '$yearfromselected' AND '$yeartoselected' AND id_search_strings='$searchstringsids[$zaehler]' AND conference='$conferenceselected' AND exclude=0 AND ((preprocessing_abstracttext_already_done=0 AND preprocessing_pdffulltext_already_done=1) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=0) OR (preprocessing_abstracttext_already_done=1 AND preprocessing_pdffulltext_already_done=1))","year asc");																
									while(list($id_row,$id_search_strings_row,$exclude_row,$authors_row,$title_row,$conference_row,$year_row,$first_link_to_abstracttext_row,$abstracttext_row,$abstracttext_for_lda_row,$first_link_to_pdffulltext_row,$path_to_pdffulltext_row,$pdffulltext_as_text_row,$pdffulltext_as_text_extracted_row,$pdffulltext_for_lda_row) = mysqli_fetch_row($returndbselect)){							
										$idsearchresult[$zaehler2]=$id_row;
										$authors[$zaehler2]=$authors_row;
										$title[$zaehler2]=$title_row;
										$conference[$zaehler2]=$conference_row;
										$year[$zaehler2]=$year_row;							
										$abstracttextforlda[$zaehler2]=$abstracttext_for_lda_row;
										$pathtopdffulltext[$zaehler2]=$path_to_pdffulltext_row;
										$pdffulltextforlda[$zaehler2]=$pdffulltext_for_lda_row;
										$zaehler2++;
									}
								}
								$insert_lda_id_search_results_ok=true;
							}
							
							if($insert_lda_id_search_results_ok==true){
								if(isset($idsearchresult)){					
									if(sizeof($idsearchresult)>0){ /*$idsearchresult is not NULL if one of the above listed selections led
										to some fetched rows from table search_results. If this is the case then insert the ids of these
										search results' rows to table 'lda_id_search_results'.*/	
										//Delete former entries for this $ldaid in table 'lda_id_search_results' before inserting new entries
										$returndbdelete=dbdelete($returndbconnect,"lda_id_search_results","id_lda='$idlda'");
										if($returndbdelete==0){
											echo "DELETE-Query for deleting old entries for this lda in table 'lda_id_search_results' was not successful.";
										}
										$bool_lda_id_search_results=true;
										for($zaehler=0;$zaehler<sizeof($idsearchresult);$zaehler++){
											$returndbinsert=dbinsert($returndbconnect,"lda_id_search_results","(id_lda,id_search_result)","('$idlda','$idsearchresult[$zaehler]')");
											if($returndbinsert==false){
												$bool_lda_id_search_results=false;									
											}
										}
										if($bool_lda_id_search_results){//INSERT-Query happened to table "lda_id_search_results"
											//1. e) Extract abstracts and optionally pdffulltexts for the scientific articles stored in table lda_id_search_results before in files 
											$outputtouser='<tr><td><a href="../topic_analysis_00_menu.php">Go back to the menu</a></td><td></td></tr>';
											$outputtouser=$outputtouser . '<tr><td>Preparing LDA execution: Copying the corpus consisting of pdffulltexts and abstracts of the selection of LDA "' . $ldaname .'" to the inputfolder of LDA "' . $ldaname .'" for the following attributes:</td><td></td></tr>';
											if(ltrim(strlen($conferenceselected))>0){
												$consideredconferenceoutput='The considered conference is "' . $conferenceselected . '"';
											}else{
												$consideredconferenceoutput='No conference selected';
											}
											
											if(ltrim(strlen($yearfromselected))>0){
												if(ltrim(strlen($yeartoselected))>0){
													$yearfromtoselectedoutput='The considered epoch for the publishing date for each scientific paper is from  "' . $yearfromselected . '" until "' . $yeartoselected . '"';
												}else{
													$yearfromtoselectedoutput='The considered epoch for the publishing date for each scientific paper is from "' . $yearfromselected . '"';
												}
											}else{
												if(ltrim(strlen($yeartoselected))>0){
													$yearfromtoselectedoutput='The considered epoch for the publishing date for each scientific paper is until  "' . $yeartoselected . '"';
												}else{
													$yearfromtoselectedoutput='No epoch selected';
												}
											}
												
											$outputtouser=$outputtouser . '<tr><td>Name of the topic analysis: ' . $topicanalysisname . '.</td><td></td></tr>';
											$outputtouser=$outputtouser . '<tr><td>' . $consideredconferenceoutput . '.</td><td></td></tr>';
											$outputtouser=$outputtouser . '<tr><td>' . $yearfromtoselectedoutput . '.</td><td></td></tr>';
											$outputtouser=$outputtouser . '<tr><td>DELETE old files in inputfolder "' . pathtolda() . $topicanalysisname . '/' . $ldaname . '/input".</td><td></td></tr>';
											rrmdir(pathtolda() . $topicanalysisname . '/' . $ldaname . '/input');
											mkdir(pathtolda() . $topicanalysisname . '/' . $ldaname . '/input');
											$outputtouser=$outputtouser . '<tr><td>DELETE old files in outputfolder "' . pathtolda() . $topicanalysisname . '/' . $ldaname . '/output".</td><td></td></tr>';
											rrmdir(pathtolda() . $topicanalysisname . '/' . $ldaname . '/output');
											mkdir(pathtolda() . $topicanalysisname . '/' . $ldaname . '/output');
											$outputtouser=$outputtouser . '<tr><td>Copying the corpusfiles from the selection above from table "search_results" to "' . pathtolda() . $topicanalysisname . '/' . $ldaname . '/input".</td><td></td></tr>';
											for($zaehler=0;$zaehler<$zaehler2;$zaehler++){
												$zaehler3=$zaehler+1;
												if(ltrim($pathtopdffulltext[$zaehler])!=''){
													//If pdffulltext is available we do not extract abstract of the same scientific paper.
													$outputtouser=$outputtouser . '<tr><td>' . $zaehler3 . ') Pdffullttext "' . $title[$zaehler] .'" from author(s) "' . $authors[$zaehler] . '" from conference "' . $conference[$zaehler] .'" from year "' . $year[$zaehler] .'" copied to the corpusfile "' . pathtolda() . $topicanalysisname . '/' . $ldaname . '/input/' . $idsearchresult[$zaehler] . '.txt".</td><td></td></tr>';   
													file_put_contents(pathtolda() . $topicanalysisname . '/' . $ldaname . '/input/' . $idsearchresult[$zaehler] . '.txt',base64_decode($pdffulltextforlda[$zaehler]));
												}else{
													$striposition1=stripos(base64_decode($abstracttextforlda[$zaehler]),"Without Abstract");
													$striposition2=stripos(base64_decode($abstracttextforlda[$zaehler]),"No abstract available");
													$striposition1numyesno=is_numeric($striposition1);
													$striposition2numyesno=is_numeric($striposition2);
													if($striposition1numyesno==false){														
														if($striposition2numyesno==false){
															//No pdffulltext for this scientific article is available. We extract the abstract of this scientific article.
															$outputtouser=$outputtouser . '<tr><td>' . $zaehler3 . ') Abstract "' . $title[$zaehler] .'" from author(s) "' . $authors[$zaehler] . '" from conference "' . $conference[$zaehler] .'" from year "' . $year[$zaehler] .'" copied to the corpusfile "' . pathtolda() . $topicanalysisname . '/' . $ldaname . '/input/' . $idsearchresult[$zaehler] . '.txt".</td><td></td></tr>';   
															file_put_contents(pathtolda() . $topicanalysisname . '/' . $ldaname . '/input/' . $idsearchresult[$zaehler] . '.txt',base64_decode($abstracttextforlda[$zaehler]));
														}
													}
													if($striposition1numyesno){
														$outputtouser=$outputtouser . '<tr><td>' . $zaehler3 . ') Abstract "' . $title[$zaehler] .'" from author(s) "' . $authors[$zaehler] . '" from conference "' . $conference[$zaehler] .'" from year "' . $year[$zaehler] .'" not copied to "' . pathtolda() . $topicanalysisname . '/' . $ldaname . '/input/" because this scientific article is without an abstract.</td><td></td></tr>';   
														$returndbdelete=dbdelete($returndbconnect,"lda_id_search_results","id_lda='$idlda' AND id_search_result='$idsearchresult[$zaehler]'");
														if($returndbdelete==0){
															echo "DELETE-Query for deleting a row in table lda_id_search_results because of a missing abstract went wrong";
															$returndeletesearchstrings=0;
														}

													}
													
													if($striposition2numyesno){
														$outputtouser=$outputtouser . '<tr><td>' . $zaehler3 . ') Abstract "' . $title[$zaehler] .'" from author(s) "' . $authors[$zaehler] . '" from conference "' . $conference[$zaehler] .'" from year "' . $year[$zaehler] .'" not copied to "' . pathtolda() . $topicanalysisname . '/' . $ldaname . '/input/" because this scientific article is without an abstract.</td><td></td></tr>';   
														$returndbdelete=dbdelete($returndbconnect,"lda_id_search_results","id_lda='$idlda' AND id_search_result='$idsearchresult[$zaehler]'");
														if($returndbdelete==0){
															echo "DELETE-Query for deleting a row in table lda_id_search_results because of a missing abstract went wrong";
															$returndeletesearchstrings=0;
														}
													}
												}
											}
											$outputtouser=$outputtouser . '&nbsp;</td><td>&nbsp;</td></tr>';											
											$outputtouser=$outputtouser . '<tr><td>Please execute lda as follows (because of timeout-issues in php if executing lda in php via ' . r_script_executable() . '):</td><td>&nbsp;</td></tr>';
											$outputtouser=$outputtouser . '<tr><td>Open a shell of your operating system (e.g. "C:/Windows/System32/cmd.exe" on Windows) in order to do the following:</td><td>&nbsp;</td></tr>';
											$outputtouser=$outputtouser . '<tr><td>Within the opened shell change to the following directory as follows: "cd ' . pathtolda() . $topicanalysisname . '/' . $ldaname . '/execute".</td><td>&nbsp;</td></tr>';
											$outputtouser=$outputtouser . '<tr><td>Within the shell execute the following lda-command: "' . r_script_executable() . ' lda.R".</td><td>&nbsp;</td></tr>';  
											$outputtouser=$outputtouser . '<tr><td>Wait until ' . r_script_executable() . ' is finished and then go to <a href="../topic_analysis_14_display_LDA.php">selection for displaying lda-pages</a> in order to see and link the output-page.</td><td>&nbsp;</td></tr>'; 
											$outputtouser=$outputtouser . '<tr><td>Thank you for using this tool!</td><td>&nbsp;</td></tr>';
											echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
											<html xmlns="http://www.w3.org/1999/xhtml">
											
											<head>
											<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
											<style type="text/css">
											.auto-style1 {
												margin-left: 0px;
											}
											.auto-style2 {
												text-align: center;
											}
											.auto-style3 {
												text-align: left;
											}
											</style>
											</head>
											
											<body>								
												<table style="width: 100%">
													<tr>
														<td class="auto-style2" style="width: 476px">Execute lda "' . $ldaname .'":</td>
														<td>&nbsp;</td>
													</tr>
													' . $outputtouser . '
													<tr>
														<td style="width: 476px">&nbsp;</td>
														<td>&nbsp;</td>
													</tr>
													</table>
											</body>
											</html>';	
										}else{//Mistakes happened while inserting to table "lda_id_search_results".
											echo 'INSERT-Query for table "lda_id_search_results" failed';
											$returndbdelete=dbdelete($returndbconnect,"lda_id_search_results","id_lda='$idlda'");
											if($returndbdelete==0){
												echo "DELETE-Query for deleting any entry for this lda in table 'lda_id_search_results' was not successful.";
											}
										}			
									}else{//No search results found for the selections that are related to this lda.
										echo 'No search results found for the selections that are related to this lda.</br>Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
									}
								}else{//The lda's selection is not a valid selection that is treated here.
									echo 'The selection for this lda is not a valid selection that is treated here. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';				
								}
							}else{
								echo 'Your selections for lda ' . $ldaname . ' did not return scientific papers for a lda procedure for lda ' . $ldaname . '.';
							}
						}else{
							echo 'No search results found for the selections that are related to this lda.</br>Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';	
						}
					}else{
						echo 'Please <a href="../topic_analysis_07_b_preprocessing_pdffulltexts.php">preprocess</a> first.';
					}
				}else{
					echo 'Please <a href="../topic_analysis_07_a_preprocessing_abstracts.php">preprocess</a> first.';
				}			
			}else{
				echo 'Please run the <a href="../topic_analysis_06_exclude_search_results.php">exclusion procedure</a> before executing lda for topic analysis "' . $topicanalysisname . '". Thank you!';
			}			
		}else{
			echo 'No search strings found for the selected topic analysis. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>'; 					
		}
		dbdisconnect($returndbconnect);
	}else{
		echo 'No lda selected. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
	}													
?>
