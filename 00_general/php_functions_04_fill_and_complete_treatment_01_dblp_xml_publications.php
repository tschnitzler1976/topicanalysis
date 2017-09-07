<?php
include_once("mysql_db_connection.php");
include_once("mysql_db_queries.php");
include_once("mysql_db_functions.php");
	function write_dblp_xml_publications_search_result_to_table_search_results($dirname,$searchstringid,$searchstringname,$writeok,$zaehler){
		$searchresultsfromwhat=checkequalitybetweensearchstringandfilename($dirname,$searchstringname);
		/*Update column 'htmlsource' in table 'search_strings' with the file content of $searchresultsfromwhat where
		  $searchstringid if files from dblp exist. If they exist $writeok is 1. Otherwise $writeok is 0.*/
		if(ltrim($searchresultsfromwhat)!=''){
			$writeok[$zaehler]=1;
			$htmlsource=file_get_contents($dirname . $searchresultsfromwhat);
			if(stripos($htmlsource,"<")==false){
				$htmlsource=htmlstringtostring(file_get_contents($dirname . $searchresultsfromwhat));
			}		
			$returnupdate=1;
			$returndbconnect=dbconnect();
			$returndbupdate=dbupdate($returndbconnect,"search_strings","htmlsource='$htmlsource'","id='$searchstringid'");
			if($returndbupdate!=1){
				echo "UPDATE-Query for the topic analysis went wrong.</br>";
				$returnupdatetopicanalysis=0;
			}
			
			/*Relevant for table "search_strings" from the file input of "http://dblp.org/search/publ/api?q=..."
			is author,title,venue and year.
			The xmltag-array below marks each beginning and end of the above enumerated attributes for table "search_strings"*/
			 
			$xmltag[0]="<authors>";
			$xmltag[1]="</authors>";
			$xmltag[2]="<title>";
			$xmltag[3]="</title>";
			$xmltag[4]="<venue>";
			$xmltag[5]="</venue>";
			$xmltag[6]="<year>";
			$xmltag[7]="</year>";
			//Below is the handler for each inputfile that has its input from "http://dblp.org/search/publ/api?q=..."
			$firstidentifiercomesagain=true;
			$newstrposition=0;
			$strposition=0;
			$strposition2=0;
			$zaehler2=0;
			$htmlsource=stringtohtmlstring($htmlsource);		
			while($firstidentifiercomesagain){
				for($zaehler=0;$zaehler<7;$zaehler++){
					$strposition=stripos($htmlsource,stringtohtmlstring($xmltag[$zaehler]),$strposition);
					$zaehler=$zaehler+1;	
					$strposition2=stripos($htmlsource,stringtohtmlstring($xmltag[$zaehler]),$strposition);
					$strposition2=$strposition2+strlen(stringtohtmlstring($xmltag[$zaehler]));
					$strupdatequery[$zaehler2]=excludeanyxmltag(htmlstringtostring(substr($htmlsource,$strposition,$strposition2-$strposition)));
					$zaehler2++;
				}
				$zaehler2=0;
				$returndbconnect=dbconnect();
				$returndbinsert=dbinsert($returndbconnect,"search_results","(id_search_strings,authors,title,conference,year)","('$searchstringid', '$strupdatequery[0]','$strupdatequery[1]','$strupdatequery[2]','$strupdatequery[3]')");
				if($returndbinsert==0){
					echo "INSERT-Query in table search_results for the search string-id= " . $searchstringid . " went wrong.</br>";
				}
				eliminatefakerowsintablesearchresults();
				dbdisconnect($returndbconnect);			
				//If we do not have a next xml-open-tag equal "<authors>" in the dblp-xml-file we are at the end of the search result
				//and we quit the while-loop
				$newstrposition=$strposition;
				$strposition=stripos($htmlsource,stringtohtmlstring($xmltag[0]),$newstrposition);
				if($strposition==false){
					$firstidentifiercomesagain=false;
				}	
			}	
		}else{
			$writeok[$zaehler]=0;
		}
		return $writeok;
	}	
?>