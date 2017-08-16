<?php
include_once("mysql_db_connection.php");
include_once("mysql_db_queries.php");
include_once("mysql_db_functions.php");

	function abstracttext_www_researchgate_net($topicanalysisid,$topicanalysisname,$linksforcompletions){
		/*We look into the column "abstracttext" of table search_results. If we exactly have 0 fields into this 
		  column that is filled we fill all fields of this column by fetching information from www.researchgate.net/publication/{identifier_for_paper_on_www.researchgate.net}
		*/
		$returndbconnect=dbconnect();
		/*In order to be able to relate the handler for filling the column "abstracttext" of table "search_results"
	    with the help of www.researchgate.net's information we fetch each $searchstringid for the $topicanalysisid*/
		$returndbselect=dbselect($returndbconnect,"search_strings", "id_topic_analysis='$topicanalysisid'","id");
		$zaehler=0;
		while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($returndbselect)){
			$searchstringsids[$zaehler]=$id;
			$zaehler++;
		}
		mysqli_free_result($returndbselect);
		$handle_any_abstracttext=0;	
		$dbnumrowsnull=1;				
		for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
			/*We look for any $searchstringid in table "search_results" whether column "abstracttext" has not empty
			fields for this $searchstringid. If there are only empty fields we get 0 fields.*/
			$searchstringid=$searchstringsids[$zaehler];
			$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringid' AND abstracttext <> ''","id");			
			if(dbnumrows($returndbselect)!=0){
				$dbnumrowsnull=0;
			}
		}
		//Anything is empty. Therefore dbnumrowsnull is 1. We generate the link to update the fields for the column "abstracttext".
		if($dbnumrowsnull==1){
			$handle_any_abstracttext=1;
			$linksforcompletions=$linksforcompletions .
					'<tr>
						<td style="width: 743px; height: 23px;"><a href="../www_researchgate_net/topic_analysis_04_create_and_complete_treatment_02_abstracttext_all.php?topicanalysisid=' . $topicanalysisid . '&searchresultid=0" target="_blank">Complete the search results for all abstracttexts with the help of "http://www.researchgate.net".</a></td>
						<td>&nbsp;</td>
					</tr><tr>
						<td style="width: 743px; height: 23px;">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>';
		}
		
		if($handle_any_abstracttext==0){
			//Another possibility is just to look for one left empty field if above selection returned more than 0 fields.

			for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
				/*We look for any $searchstringid in table "search_results" whether column "abstracttext" has empty
				fields left for this $searchstringid. If there are at least 1 empty field we proceed.*/
				$searchstringid=$searchstringsids[$zaehler];
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringid' AND abstracttext = ''","id");
				if(dbnumrows($returndbselect)>0){
					$dbfetcharray=dbfetcharray($returndbselect);
					$searchresultid=dbfetchfield($dbfetcharray,"id");
					$linksforcompletions=$linksforcompletions .
							'<tr>
								<td style="width: 743px; height: 23px;"><a href="../www_researchgate_net/topic_analysis_04_create_and_complete_treatment_02_abstracttext_one.php?topicanalysisid=' . $topicanalysisid . '&searchresultid=' . $searchresultid . '" target="_blank">Complete one field for the first links to abstracttext of table "search results".</a></td>
								<td>&nbsp;</td>
							</tr><tr>
								<td style="width: 743px; height: 23px;">&nbsp;</td>
								<td>&nbsp;</td>
							</tr>';
				}
			}
		}
		mysqli_free_result($returndbselect);
		dbdisconnect($returndbconnect);
		return $linksforcompletions;
	}	
?>