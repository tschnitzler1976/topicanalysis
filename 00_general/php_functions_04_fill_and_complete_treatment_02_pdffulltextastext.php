<?php
include_once("mysql_db_connection.php");
include_once("mysql_db_queries.php");
include_once("mysql_db_functions.php");

	function pdffulltextastext($topicanalysisid,$topicanalysisname,$linksforcompletions){
		/*We look into the column "path_to_pdffulltext" of table search_results. If we have more than 0 links for
		  the column pdffulltext_as_text then we put out a link to a popup-window for updating the column pdffulltext_as_text*/
		$returndbconnect=dbconnect();
		/*In order to be able to relate the handler for filling the column "pdffulltext_as_text" of table "search_results"
	    with the help of the information in column "path_to_pdffulltext" we fetch each $searchstringid for
	    the $topicanalysisid*/
		$returndbselect=dbselect($returndbconnect,"search_strings", "id_topic_analysis='$topicanalysisid'","id");
		$zaehler=0;
		while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($returndbselect)){
			$searchstringsids[$zaehler]=$id;
			$zaehler++;
		}
		mysqli_free_result($returndbselect);
		$dbnumrowscount1=0;		
		for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
			$searchstringid=$searchstringsids[$zaehler];
			$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringid' AND path_to_pdffulltext <> ''","id");			
			$dbnumrowscount1=$dbnumrowscount1+dbnumrows($returndbselect);
		}
		if($dbnumrowscount1>0){
			/*We provide a link if at least $searchstringid has a filled field for the column "path_to_pdffulltext".*/
			$dbnumrowscount2=0;
			for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
				$searchstringid=$searchstringsids[$zaehler];
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringid' AND pdffulltext_as_text <> ''","id");			
				$dbnumrowscount2=$dbnumrowscount2+dbnumrows($returndbselect);
			}			
			if($dbnumrowscount1-$dbnumrowscount2==$dbnumrowscount1){
				/*We have some filled fields in column path_to_pdffulltext and no filled fields in pdffulltext_as_text.
				 Thus, we have to do the following: We generate the link to update the pdffulltext_as_text which is related
				 to a filled field in column "path_to_pdffulltext".*/
				$linksforcompletions=$linksforcompletions .
			'<tr>
				<td style="width: 743px; height: 23px;"><a href="topic_analysis_04_fill_and_complete_treatment_02_pdffulltext_as_text_all.php?topicanalysisid=' . $topicanalysisid . '" target="_blank">Complete the search results for the conversion of any pdffulltext to text.</a></td>
				<td>&nbsp;</td>
			</tr><tr>
				<td style="width: 743px; height: 23px;">&nbsp;</td>
				<td>&nbsp;</td>
			</tr>';
			}elseif($dbnumrowscount1-$dbnumrowscount2!=$dbnumrowscount1){
				if($dbnumrowscount1-$dbnumrowscount2>0){

					//We just update one field in column path_to_pdffulltext.s
					$linksforcompletions=$linksforcompletions .
			'<tr>
				<td style="width: 743px; height: 23px;"><a href="topic_analysis_04_fill_and_complete_treatment_02_pdffulltext_as_text_one.php?topicanalysisid=' . $topicanalysisid . '" target="_blank">Complete one search result for the conversion of one pdffulltext to textt.</a></td>
				<td>&nbsp;</td>
			</tr><tr>
				<td style="width: 743px; height: 23px;">&nbsp;</td>
				<td>&nbsp;</td>
					</tr>';
				}
			}	
		}
		dbdisconnect($returndbconnect);
		return $linksforcompletions;
	}	
?>