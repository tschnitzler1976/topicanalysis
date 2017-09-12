<?php
/*Algorithm:
  1. c) Show the research questions that are subordinated to the selected topic analysis and relate them to the search results
   	 	from above in order to let the user exclude search results that do not answer the research questions of this topic
   	 	analysis.

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
		
		/*Before proceeding we analyze the search results of this topic analysis. There must be no search result that
		has an empty field for authors, title, conference, year and abstracttext. If any of these columns' fields has at
		least 1 empty field we do not proceed with exclusion. Instead we show a snapshot of the search_results table
		and give a hint what field of which search_result_id is also responsible for causing this error.*/
		
		//At first select the search_strings_ids	
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
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]'","id");			
				$dbnumrowsvar2=dbnumrows($returndbselect);
				if($dbnumrowsvar2==0){
					echo "No search results found for search string ' . $searchstringsnames[$zaehler2] . '.</br>";
					$searchstringhasnosearchresults=true;
				}
			}
			mysqli_free_result($returndbselect);						
			if($searchstringhasnosearchresults==false){
			//For any search string we have search results.
				//Therefore, we look into each column whether we find an empty field.
				//If we find empty fields we throw an error and do not continue.
				$searchresulthasemptyfields=false;
				for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
					$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND authors=''","id");			
					$dbnumrowsvar2=dbnumrows($returndbselect);
					if($dbnumrowsvar2>0){
						echo "At least one empty field found in column authors in table search_results.</br>";
						$searchresulthasemptyfields=true;
					}
				}
				mysqli_free_result($returndbselect);	

				for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
					$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND title=''","id");			
					$dbnumrowsvar2=dbnumrows($returndbselect);
					if($dbnumrowsvar2>0){
						echo "At least one empty field found in column title in table search_results.</br>";
						$searchresulthasemptyfields=true;
					}
				}
				mysqli_free_result($returndbselect);					
												
				for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
					$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND conference=''","id");			
					$dbnumrowsvar2=dbnumrows($returndbselect);
					if($dbnumrowsvar2>0){
						echo "At least one empty field found in column conference in table search_results.</br>";
						$searchresulthasemptyfields=true;
					}
				}
				mysqli_free_result($returndbselect);	
				
				for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
					$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND year=''","id");			
					$dbnumrowsvar2=dbnumrows($returndbselect);
					if($dbnumrowsvar2>0){
						echo "At least one empty field found in column year in table search_results.</br>";
						$searchresulthasemptyfields=true;
					}
				}
				mysqli_free_result($returndbselect);
				
				for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
					$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND abstracttext=''","id");			
					$dbnumrowsvar2=dbnumrows($returndbselect);
					if($dbnumrowsvar2>0){
						echo "At least one empty field found in column abstracttext in table search_results.</br>";
						$searchresulthasemptyfields=true;
					}
				}
				mysqli_free_result($returndbselect);	
				
				if($searchresulthasemptyfields==false){				
					/*We have no empty fields in authors,title,conference,year,abstracttext. Thus we continue.
					 We have research questions we must present to the user because according to the feature of
					 systematic mapping study specified at slide 21 and at slide 22 at
					 https://userpages.uni-koblenz.de/~laemmel/esecourse/slides/sms.pdf the user must decide whether
					 the search results answer his former saved research questions.*/
					  
					//Get data for research questions into the field for this topic analysis.
					$returndbselect=dbselect($returndbconnect,"research_questions","id_topic_analysis=$topicanalysisid","id");
					$returndbnumrows=dbnumrows($returndbselect);
					if($returndbnumrows>0){
						$researchquestions='';
						/*We will show the research questions below. Then the user can compare the search results with the research
			              questions.*/
						while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($returndbselect)){
							$researchquestions=$researchquestions . $name;
						}
						$htmlcodeforsnapshot=createsnapshotoftablesearchresultsforexclusion($topicanalysisid,$htmlcodeforsnapshot);
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
	<form method="post" action="topic_analysis_06_exclude_treatment_02.php">
		<input name="texttopicanalysisid" type="hidden" value="' . $topicanalysisid .'"/>
		<br />
		<table style="width: 100%">
			<tr>
				<td style="width: 481px; height: 79px;">
				<table style="width: 159%">
					<tr>
						<td style="width: 644px; height: 23px;">Exclusion of search results of 
						topic analysis "' . $topicanalysisname . '".</td>
						<td style="height: 23px"><a href="../topic_analysis_00_menu.php">Back to the menu</a></td>
					</tr>
					<tr>
						<td style="width: 644px; height: 23px;">&nbsp;</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 644px; height: 23px;">Please exclude 
						the particular search results with the help of&nbsp; the 
						checkboxes below. If these search results answer the 
						research questions below you need not to exclude the 
						particular search results. This step is in terms of the
						 third step of the systematic mapping study
						 (see <a href="https://userpages.uni-koblenz.de/~laemmel/esecourse/slides/sms.pdf">
					slides for the systematic mapping study</a>) at slide 21 and is compulsive.</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 255px">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
			<tr>
				<td style="height: 236px; width: 481px">
				<textarea name="textarea_research_questions" style="width: 657px; height: 231px" tabindex="3" disabled="disabled">' . $researchquestions .'</textarea></td>
				<td style="height: 236px"></td>
			</tr>
			<tr>
				<td style="width: 481px; width: 509px">
				&nbsp;</td><td>&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 481px; width: 509px">
				&nbsp;</td><td>&nbsp;</td>
			</tr></table><table style="width: 445px"><tr><td>&nbsp;</td>
					<td style="width: 334px">&nbsp;</td><td style="width: 191px">
					<input name="submit_topic_analysis_06_exclude_treatment_02" type="submit" value="Exclude" class="auto-style1" style="width: 265px"/></td></tr><tr><td>
						&nbsp;</td><td style="width: 334px">&nbsp;</td>
						<td style="width: 191px">&nbsp;</td></tr><tr><td>&nbsp;</td>
						<td style="width: 334px">&nbsp;</td><td style="width: 191px">
						&nbsp;</td></tr></table>
			' . $htmlcodeforsnapshot[1] . '
	
	</form>
	</body>
	
					</html>';
						
						
					}else{
						echo 'No research questions for topic analysis "' . $topicanalysisname .'". Please go to the <a href="../topic_analysis_00_menu.php">menu</a>.';
					}
				}else{//Empty fields found in in authors,title,conference,year,abstracttext in table search results.
				echo 'Please go to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
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