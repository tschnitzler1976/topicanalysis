<?php
	set_include_path("../00_general");
	include_once("php_functions.php");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	
	if(isset($_POST["hiddentopicanalysisid"])){
		$topicanalysisid=htmlstringtostring($_POST["hiddentopicanalysisid"]);
		
		$returndbconnect=dbconnect();
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id='$topicanalysisid'","id");
		$dbfetcharray=dbfetcharray($returndbselect);
		$dbfetchfield=dbfetchfield($dbfetcharray,"name");
		$topicanalysisname=$dbfetchfield;
		//Create the folder for search results for this topic analysis if this folder does not exist.
		if(is_dir(pathtosearchresults($topicanalysisname))==false){
			mkdir(pathtosearchresults($topicanalysisname));
		} 

		$returnupdatetopicanalysis=2;
		
		if(isset($_POST["txttopicanalysis"])){			
			$topicanalysisupdatename=htmlstringtostring($_POST["txttopicanalysis"]);
			if($topicanalysisname!=$topicanalysisupdatename){
				$returnupdatetopicanalysis=1;
				$returndbupdate=dbupdate($returndbconnect,"topic_analysis","name='$topicanalysisupdatename'","id='$topicanalysisid'");
				if($returndbupdate!=1){
					echo "UPDATE-Query for the topic analysis went wrong";
					$returnupdatetopicanalysis=0;
					
				}else{
					if(is_dir(pathtosearchresults($topicanalysisname))){
						rename(pathtosearchresults($topicanalysisname),pathtosearchresults($topicanalysisupdatename));
					}else{
						echo 'Renaming directory ' . pathtosearchresults($topicanalysisname) . ' failed.'; 
					}
				}
			}
		}	
		
		/*Delete the research questions and search strings that belong to this topic analysis in
		order to insert them again
		because of possible modifications in "topic_analysis_02_modify_treatment_01.php".*/
		$returndeleteresearchquestions=1;
		$returninsertresearchquestions=1;
		
		//Treat the research questions first.
		//Delete old research questions first.
		$returndbdelete=dbdelete($returndbconnect,"research_questions","id_topic_analysis='$topicanalysisid'");
		if($returndbdelete==0){
			echo "DELETE-Query for research questions went wrong";
			$returndeleteresearchquestions=0;
		}
	
		//Split the research questions because of '?' after each research question and insert each of them at table 'research_questions'
		if(isset($_POST["textarea_research_questions"])){
			if(isset($_POST["hiddentextresearchid"])){
				$researchquestionsid=htmlstringtostring($_POST["hiddentextresearchid"]);
			}
			$researchquestions=htmlstringtostring($_POST["textarea_research_questions"]);
			$researchquestionsarray=explodestring("?",$researchquestions);
			if(sizeof($researchquestionsarray)>1){
				
				$length=sizeofarray($researchquestionsarray)-1;
				for($zaehler=0;$zaehler<$length;$zaehler++){
					$researchquestion=$researchquestionsarray[$zaehler] . "?";
					$returndbinsert[$zaehler]=dbinsert($returndbconnect,"research_questions","(id_topic_analysis,name)","($topicanalysisid,'$researchquestion')");
					if($returndbinsert[$zaehler]==0){
						echo "INSERT-Query for research questions went wrong";
						$returninsertresearchquestions=0;
					}
				}
				
			}elseif(ord($researchquestionsarray[0])!=0){
				//We have not a NULL-Value which means we did not delete anything which means anything went wrong.
				$returninsertresearchquestions=0;
			}
		}
		
		$returndeletesearchstrings=1;
		$returninsertsearchstrings=1;
		
		//Treat the search strings next.
		//Delete old search strings first.
		$returndbdelete=dbdelete($returndbconnect,"search_strings","id_topic_analysis='$topicanalysisid'");
		if($returndbdelete==0){
			echo "DELETE-Query for search strings went wrong";
			$returndeletesearchstrings=0;
		}
		
		if(isset($_POST["textareasearchstrings"])){
			//$searchstrings=htmlstringtostring($_POST["textareasearchstrings"]);
			$searchstrings=$_POST["textareasearchstrings"];
			
			if(isset($_POST["hiddentextsearchstringsid"])){
				$searchstringsid=htmlstringtostring($_POST["hiddentextsearchstringsid"]);
			}
			
			//Treat the search strings. Split them because of '$' after each search string and insert each of them at table 'search_strings'
			$searchstringsarray=explodestring("$",$searchstrings);
			if(sizeof($searchstringsarray)>1){
				$length=sizeofarray($searchstringsarray)-1;
				for($zaehler=0;$zaehler<$length;$zaehler++){
					$searchstring=$searchstringsarray[$zaehler];
					$returndbinsert[$zaehler]=dbinsert($returndbconnect,"search_strings","(id_topic_analysis,name)","($topicanalysisid,'$searchstring')");
					if($returndbinsert[$zaehler]==0){
						echo "INSERT-Query for search strings went wrong";
						$returninsertsearchstrings=0;
					}
				}
			}elseif(ord($searchstringsarray[0])!=0){
				//We have not a NULL-Value which means we did not delete anything which means anything went wrong.
				$returninsertsearchstrings=0;
			}
		}
		
		$returndeletesearchstringsresults=1;
		$returninsertsearchstringsresults=1;

		
		/*Treat the search strings for enriching the search results (second step of the systematic mapping study) with further data like abstract or pdffulltext.
		  Delete old search strings first.*/
		$returndbdelete=dbdelete($returndbconnect,"search_strings_for_results","id_topic_analysis='$topicanalysisid'");
		if($returndbdelete==0){
			echo "DELETE-Query for search strings to enrich the search results with further data went wrong";
			$returndeletesearchstringsresults=0;
		}
		if(isset($_POST["textareasearchstringsforresults"])){
			$searchstrings2=$_POST["textareasearchstringsforresults"];
			
			if(isset($_POST["hiddentextsearchstringsid2"])){
				$searchstringsid2=htmlstringtostring($_POST["hiddentextsearchstringsid2"]);
			}
			
			//Treat the search strings for the still included search results. Split them because of '$' after each search string and insert each of them at table 'search_strings_for_still_included_results'
			$searchstringsarray=explodestring("$",$searchstrings2);
			if(sizeof($searchstringsarray)>1){
				$length=sizeofarray($searchstringsarray)-1;
				for($zaehler=0;$zaehler<$length;$zaehler++){
					$searchstring=$searchstringsarray[$zaehler];
					$returndbinsert[$zaehler]=dbinsert($returndbconnect,"search_strings_for_results","(id_topic_analysis,name)","($topicanalysisid,'$searchstring')");
					if($returndbinsert[$zaehler]==0){
						echo "INSERT-Query for search strings to enrich the search results with further data went wrong";
						$returninsertsearchstringsresults=0;
					}
				}
			}elseif(ord($searchstringsarray[0])!=0){
				//We have not a NULL-Value which means we did not delete anything which means anything went wrong.
				$returninsertsearchstringsresults=0;
			}
		}
		
		
		$anythingfine=1;
		if($returnupdatetopicanalysis==0){
			echo 'There was an error while updating topic analysis. Please go to the <a href="../topic_analysis_00_menu.php">menu</a></br>';
			$anythingfine=0;
		}
		
		if($returndeleteresearchquestions==0){
			echo 'There was an error while deleting the research questions. Please go to the <a href="../topic_analysis_00_menu.php">menu</a></br>';
			$anythingfine=0;
		}
		
		if($returninsertresearchquestions==0){
			echo 'There was an error while inserting the research questions. Do you have a question mark after each research question?</br>
			 Please go to the <a href="../topic_analysis_00_menu.php">menu</a></br>';
			$anythingfine=0;
		}
		
		if($returndeletesearchstrings==0){
			echo 'There was an error while deleting the search strings. Please go to the <a href="../topic_analysis_00_menu.php">menu</a></br>';
			$anythingfine=0;
		}
		
		if($returninsertsearchstrings==0){
			echo 'There was an error while inserting the search strings. Do you have a Dollarsymbol which is $ after each search string?</br>
			Please go to the <a href="../topic_analysis_00_menu.php">menu</a></br>';
			$anythingfine=0;
		}
				
		if($returndeletesearchstringsresults==0){
			echo 'There was an error while deleting the search strings for completing the results. Please go to the <a href="../topic_analysis_00_menu.php">menu</a></br>';
			$anythingfine=0;
		}

		if($returninsertsearchstringsresults==0){
			echo 'There was an error while inserting the search strings for completing the results. Do you have a Dollarsymbol which is $ after each search string? Please go to the <a href="../topic_analysis_00_menu.php">menu</a></br>';
			$anythingfine=0;
		}

		if($anythingfine==1){
			echo 'Anything fine. Thank you! Please go to the <a href="../topic_analysis_00_menu.php">menu</a></br>';
		}
		dbdisconnect($returndbconnect);
	}else{
		echo 'Please go to the <a href="../topic_analysis_00_menu.php">menu</a>.';
	}

?>