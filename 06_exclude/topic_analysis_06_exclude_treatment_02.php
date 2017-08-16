<?php
/*Algorithm:
  Treatment of the checked 'exclude' checkboxes from topic_analysis_06_exclude_treatment_01.php which has the following issue:
  1. c) Show the research questions that are subordinated to the selected topic analysis and relate them to the search results
   	 	from above in order to let the user exclude search results that do not answer the research questions of this topic
   	 	analysis.

*/ 

	set_include_path("../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	
	if(isset($_POST["texttopicanalysisid"])){
		$topicanalysisid=htmlstringtostring($_POST["texttopicanalysisid"]);		
		$returndbconnect=dbconnect();
			
		//Get Topic Analysis Name based on its id in order to show it.
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id=$topicanalysisid","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$returndbfetchfield=dbfetchfield($returndbfetcharray,'name');
		$topicanalysisname=$returndbfetchfield;
		
		/*Before saving any checked and unchecked exclusion checkboxes we need to have the search_result_ids that
		identify each checkbox. Then we access each value of each POST-Variable of each checkbox.*/
		
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
			$zaehler3=0;
			for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]'","id");			
				$dbnumrowsvar2=dbnumrows($returndbselect);
				if($dbnumrowsvar2==0){
					echo "No search results found for search string ' . $searchstringsnames[$zaehler2] . '.</br>";
					$searchstringhasnosearchresults=true;
				}else{
					while(list($id) = mysqli_fetch_row($returndbselect)){							
						$searchresultids[$zaehler3]=$id;
						$zaehler3++;
					}
				}
			}
			mysqli_free_result($returndbselect);			
			
			if($searchstringhasnosearchresults==false){
				//Get each value of each 'exclude'-checkbox in the form from topic_analysis_06_exclude_treatment_01.php.
				for($zaehler=0;$zaehler<sizeof($searchresultids);$zaehler++){
					$id=$searchresultids[$zaehler];
					$checkbox="exclusion_id_" . $id;
					if(isset($_POST['' . $checkbox . ''])){
						$returnupdate=dbupdate($returndbconnect,"search_results","exclude=1,exclusion_already_done=1","id='$id'");
					}else{
						$returnupdate=dbupdate($returndbconnect,"search_results","exclude=0,exclusion_already_done=1","id='$id'");
					}
				}
				//We display the updated table search_results without excluded rows
				$htmlcodeforsnapshot=createsnapshotoftablesearchresults($topicanalysisid);
				
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
						<td style="width: 644px; height: 23px;">Exclusion 
						results for the 
						topic analysis "' . $topicanalysisname . '".</td>
						<td style="height: 23px"><a href="../topic_analysis_00_menu.php">Back to the menu</a></td>
					</tr>
					<tr>
						<td style="width: 644px; height: 23px;">&nbsp;</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					</table><table style="width: 445px"><tr><td>&nbsp;</td>
						<td style="width: 334px">&nbsp;</td><td style="width: 191px">
						&nbsp;</td></tr></table>
			' . $htmlcodeforsnapshot . '
	
	</form>
	</body>
	
					</html>';
															
			}else{//No search results found for at least one search strings.
				echo 'Please go to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';	
			}
		}else{//No search strings found for the topic analysis.
			echo 'No search strings for this topic analysis.<a href="../topic_analysis_00_menu.php">menu</a>.</br>';
		}		
	}else{//No topic analysis id found.
		echo 'No topic analysis selected. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
	}
?>