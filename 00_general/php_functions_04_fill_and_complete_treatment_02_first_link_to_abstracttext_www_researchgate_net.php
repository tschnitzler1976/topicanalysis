<?php
include_once("mysql_db_connection.php");
include_once("mysql_db_queries.php");
include_once("mysql_db_functions.php");

	function first_link_to_abstracttext_www_researchgate_net($zaehler10,$topicanalysisid,$topicanalysisname,$linksforcompletions){
		/*We look into the column "first_link_to_abstracttext" of table search_results. If we exactly have 0 fields into this 
		  column that is filled we fill all fields of this column by fetching information from www.researchgate.net?q=search{title}
		*/
		$returndbconnect=dbconnect();
		/*In order to be able to relate the handler for filling the column "first_links_to_abstracts" of table "search_results"
	    with the help of www.researchgate.net's information we fetch each $searchstringid for the $topicanalysisid*/
		$returndbselect=dbselect($returndbconnect,"search_strings", "id_topic_analysis='$topicanalysisid'","id");
		$zaehler=0;
		while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($returndbselect)){
			$searchstringsids[$zaehler]=$id;
			$zaehler++;
		}
		mysqli_free_result($returndbselect);
		$handleanyfirst_link_to_abstracttext=0;	
		$dbnumrowsnull=1;				
		for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
			/*We look for any $searchstringid in table "search_results" whether column "first_link_to_abstracttext" has not empty
			fields for this $searchstringid. If there are only empty fields we get 0 fields.*/
			$searchstringid=$searchstringsids[$zaehler];
			$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringid' AND first_link_to_abstracttext <> ''","id");			
			if(dbnumrows($returndbselect)!=0){
				$dbnumrowsnull=0;
			}
		}
		//Anything is empty. Therefore dbnumrowsnull is 1. We generate the link to update the fields for the column "first_link_to_abstract".
		if($dbnumrowsnull==1){
			$handleanyfirst_link_to_abstracttext=1;
			$linksforcompletions=
					'<tr>
						<td style="width: 743px; height: 23px;"><a href="../www_researchgate_net/topic_analysis_04_fill_and_complete_treatment_02_first_link_to_abstracttext_all.php?topicanalysisid=' . $topicanalysisid . '&searchresultid=0" target="_blank">Complete the search results for all first links to abstracttexts with the help of "http://www.researchgate.net".</a></td>
						<td>&nbsp;</td>
					</tr><tr>
						<td style="width: 743px; height: 23px;">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>';
		}
		
		if($handleanyfirst_link_to_abstracttext==0){
			//Another possibility is just to look for one left empty field if above selection returned more than 0 fields.

			for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
				/*We look for any $searchstringid in table "search_results" whether column "first_link_to_abstracttext" has empty
				fields left for this $searchstringid. If there are at least 1 empty field we proceed.*/
				$searchstringid=$searchstringsids[$zaehler];
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringid' AND first_link_to_abstracttext = ''","id");
				if(dbnumrows($returndbselect)>0){
					$dbfetcharray=dbfetcharray($returndbselect);
					$searchresultid=dbfetchfield($dbfetcharray,"id");
					$linksforcompletions=
							'<tr>
								<td style="width: 743px; height: 23px;"><a href="../www_researchgate_net/topic_analysis_04_fill_and_complete_treatment_02_first_link_to_abstracttext_one.php?topicanalysisid=' . $topicanalysisid . '&searchresultid=' . $searchresultid . '" target="_blank">Complete one field for the first links to abstracttext of table "search results".</a></td>
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