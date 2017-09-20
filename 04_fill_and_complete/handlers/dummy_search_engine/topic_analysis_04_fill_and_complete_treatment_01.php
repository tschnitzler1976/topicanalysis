<?php
/*   
  1. 	a) Fetch the search results from the internet for the search strings that correspond to the systematic mapping
        study's second step from slide 17 until slide 19 in
        https://userpages.uni-koblenz.de/~laemmel/esecourse/slides/sms.pdf
*/

	set_include_path("../../../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	include_once("php_functions_04_fill_and_complete_treatment_01_dblp_xml_publications.php");
	
	
	if(isset($_POST["select_existing_topic_analysis"])){
		$topicanalysisid=htmlstringtostring($_POST["select_existing_topic_analysis"]);
		
		$returndbconnect=dbconnect();
	
		//Get Topic Analysis Name based on its id in order to show it.
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id=$topicanalysisid","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$returndbfetchfield=dbfetchfield($returndbfetcharray,'name');
		$topicanalysisname=$returndbfetchfield;
		
		//Get data for research questions into the field for this topic analysis if there are already any.
		$returndbselect1=dbselect($returndbconnect,"research_questions","id_topic_analysis=$topicanalysisid","id");
		//Get data for search strings into the field for this topic analysis if there are already any.
		$returndbselect3=dbselect($returndbconnect,"search_strings","id_topic_analysis=$topicanalysisid","id");
				
		//Have there already been saved any research questions and search strings for this topic
		//analysis?
		$returndbnumrows=0;
		$returndbnumrows1=dbnumrows($returndbselect1);
		$returndbnumrows3=dbnumrows($returndbselect3);
	
		$txtresearchquestions="";
		if($returndbnumrows1>0){
			/*There are research questions for this topic analysis
			  This is necessary to proceed because the first step in the systematic mapping study
			  in https://userpages.uni-koblenz.de/~laemmel/esecourse/slides/sms.pdf between slide 12 and slide 16
			  must be a control functionality for the search (second step in the systematic mapping study)
			  (see slide 18 at https://userpages.uni-koblenz.de/~laemmel/esecourse/slides/sms.pdf). Switch to the right
			   search engine's treatment procedure later in line of topic_analysis_04_fill_and_complete_treatment_01.php
			   (search results for search strings from the second textarea field in the html-modify-form).
			   and in line of topic_analysis_04_fill_and_complete_treatment_02.php (search results for search strings
			   from the third textarea field in the html-modify-form).*/
			   
			$txtsearchstringsid="";
			if($returndbnumrows3>0){
				/*Fetch the search strings for the search results that will be completed with the help of
				topic_analysis_04_fill_and_complete_treatment_02.php.
				Because it is $returndbnumrows3>0 there are also search strings that are already saved for this topic
				analysis. Therefore extract the search engines to an input-file for wget.*/
				$searchstringsidsnextform="";
				$searchstringsforwgetinputfile="";
				$zaehler=0;
				$zaehlertreatmentparameterwrong=0;
				while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($returndbselect3)){
					//ids for the computation of the extraction output (if the search strings have the search results in their column "htmlsource" in table "search_strings")
					$searchstringsids[$zaehler]=$id;				
					//name in order to find out in what function we have to treat the details that come from this search engine
					$searchstringnames[$zaehler]=htmlstringtostring($name);				
					//searchstringsids for the next form after this form is submitted
					//for wget's input file
					$searchstringsforwgetinputfile=$searchstringsforwgetinputfile . $name;
					$zaehler++;
					/*DELETE any former search result to this search string because we will update this search string with its search results now.
					  If no handler will be found below then there are no search results for this topic analysis until a valid handler is provided.*/
					$returndbdelete=dbdelete($returndbconnect,"search_results","id_search_strings='$searchstringsids[$zaehler]'");
					if($returndbdelete==0){
						echo "DELETE-Query for deleting the search results in table search_results for the search string-id= " . $searchstringid . " went wrong. </br>";
					}		

					
				}
				
				for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
				}

				$dirname=pathtosearchresultone($topicanalysisname);
				if(is_dir($dirname)==false){
					mkdir($dirname);
				}

				//We loop through any search strings for the below problem.
				//We need to know what kind of source for table search_results has to be treated. Therefore
				//We call function checkequalitybetweensearchstringandfilename that has a template for already
				//existing search engines. The return value will be the digit that distinctly identifies the
				//function that treats the insert of extracted file data to selected columns of table "search_results".
				$anythingfine=1;
				for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
					$searchstringname=$searchstringnames[$zaehler];
					$searchstringid=$searchstringsids[$zaehler];
					$whatfunction=checkequalitybetweensearchstringandexistingfunction($searchstringname);
					switch($whatfunction){
						case 1:
							//function that is a handler for the publication search on dblp embedded in xml  
							$anythingfine=1;
						break;	
						case 2:					
							//another handler for search results from another search engine to fill the fields of table search results
							//according to the second step of the systematic mapping study 
							//(see https://userpages.uni-koblenz.de/~laemmel/esecourse/slides/sms.pdf from slide 17 until slide 19)
						break;
						default:
							$anythingfine=0;
						 	echo 'No function handler for search string: ' . $searchstringname . '.</br>Please write one probably as ../00_general/php_functions_treatment_01_xx.php or delete this search string.</br>Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
					}
				}
				
				if($anythingfine==1){
				
					if(wgetfromlocalorfrominternet()!=1){
						//fetch any search results with wget.exe
						execwget($searchstringsforwgetinputfile,false,$dirname);
						//Delete the file with the search strings for wget.exe
						if(file_exists($dirname . "wgetinput.txt")){
							unlink($dirname . "wgetinput.txt");
						}
					}	
				
					$writeok[0]=1;
					$writeokoutput='';				
					for($zaehler=0;$zaehler<sizeof($searchstringsids);$zaehler++){
						$searchstringname=$searchstringnames[$zaehler];
						$searchstringid=$searchstringsids[$zaehler];
						$whatfunction=checkequalitybetweensearchstringandexistingfunction($searchstringname);
						switch($whatfunction){
							case 1:
								//function that is a handler for the publication search on dblp embedded in xml  
								$writeok=write_dblp_xml_publications_search_result_to_table_search_results($dirname,$searchstringid,$searchstringname,$writeok,$zaehler);
								if($writeok[$zaehler]==0){
									$writeokoutput=$writeokoutput . '<tr><td style="width: 629px; height: 23px;">The dblp file for the ' . $zaehler . '. run did not exist.</td><td>&nbsp;</td></tr>';
								}elseif($writeok[$zaehler]==1){
									$zaehlertemp=$zaehler+1;
									$writeokoutput=$writeokoutput . '<tr><td style="width: 629px; height: 23px;">The ' . $zaehlertemp . '. run was successful.</td><td>&nbsp;</td></tr>';
								}
								$htmlcodeforsnapshot=createsnapshotoftablesearchresults($topicanalysisid);
							break;	
							case 2:					
								//another handler for search results from another search engine in terms of the second step of the systematic mapping study (search results for all p
								//(see https://userpages.uni-koblenz.de/~laemmel/esecourse/slides/sms.pdf from slide 17 until slide 19)
							break;
							default:
								$anythingfine=0;
							 	echo 'No function handler for search string: ' . $searchstringname . '.</br>Please write one probably as ../00_general/php_functions_treatment_01_xx.php or delete this search string.</br>Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
						}
					}					
				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	
	<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<style type="text/css">
   {font-weight:normal;color:#181818;background-color:#fffccf}b.b4{font-weight:normal;color:#0c0c0c;background-color:#fffccf}b.b2{font-weight:normal;color:#242424;background-color:#fffeef}
	</style>
	</head>
	
	<body>
	<form method="post" action="topic_analysis_04_fill_and_complete_treatment_02.php">
		<input name="texttopicanalysisid" type="hidden" value="' . $topicanalysisid .'"/>
		<br />
		<table style="width: 100%">
			<tr>
				<td style="width: 617px; height: 79px;">
				<table style="width: 159%">
					<tr>
						<td style="width: 629px; height: 23px;">Execution of 
						topic analysis "' . $topicanalysisname . '".</td>
						<td style="height: 23px"><a href="../../../topic_analysis_00_menu.php">Back to the menu</a></td>
					</tr>' . $writeokoutput . '
					<tr>
						<td style="width: 629px; height: 23px;">Please have a 
						look into the search result of each search string.</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 629px; height: 21px;">If any search 
						result does not satisfy your needs please
						<a href="../topic_analysis_02_modify.php">modify</a> 
						this search string to your greatest advantage.</td>
						<td style="height: 21px"></td>
					</tr>
					<tr>
						<td style="width: 629px; height: 23px;">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>'; //The user should see the search results for each search string. Therefore we generate
	 					//links to a pop-up-page. Then the user can see whether the search results are
	 					//correct or not. In case the user decides that the search results are not correct
	 					//the user should modify the search strings in order to improve
	 					//the search results.
	 					$sizeofsearchstringsids=sizeof($searchstringsids);
						for($zaehler=0;$zaehler<$sizeofsearchstringsids;$zaehler++){					
							$zaehler2=$zaehler+1;		
							$output=htmlstringtostring('<tr>
						<td style="width: 629px; height: 23px;">
						<a href="topic_analysis_04_fill_and_complete_treatment_01_02.php?topicanalysisname=' . $topicanalysisname . ' &searchstringid=' . $searchstringsids[$zaehler] . '" target="_blank">Search result for the ' . $zaehler2 . '. search string for verification purpose.</a></td>
						<td style="height: 23px">&nbsp;</td></tr>');
						echo $output;
						} 
					    echo '<tr>
						<td style="width: 629px; height: 23px;"></td>
						<td>&nbsp;</td>
					</tr>
					</table>
				</td>
				<td style="height: 79px"></td>
			</tr>
			<tr>
				<td style="width: 617px; height: 23px;">
				</td>
				<td style="height: 23px">&nbsp;</td>
			</tr>
			</table>	
		<table style="width: 100%">
			<tr>
				<td>The information in table "search_results" from the search 
				engine because of your search strings for<br>&nbsp;this topic analysis 
				are in the table below. If anything is in your favour please 
				complete the search<br>&nbsp;results in line of your entries in the third 
				text area of the html-form for modifying components for this<br>&nbsp;topic analysis.</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
		</table><table style="width: 83%">
					<tr>
						<td style="width: 393px">&nbsp;</td>
						<td>
	<input name="submit_topic_analysis_02_modify" type="submit" value="Complete search results" tabindex="6"/></td>
					</tr>
					<tr>
						<td style="width: 393px">&nbsp;</td>
						<td>
						&nbsp;</td>
					</tr>
				</table>
				
		
		
		
		' . $htmlcodeforsnapshot . '</form>
	</body>
	
	</html>'
			;}	
				
			}else{
				echo 'No search strings for topic analysis "' . $topicanalysisname .'". Please go to the <a href="../../../topic_analysis_00_menu.php">menu</a>.<br>';
			}									
		}else{
			echo 'No research questions for topic analysis "' . $topicanalysisname .'". Please go to the <a href="../../../topic_analysis_00_menu.php">menu</a>.<br>';
		}
		dbdisconnect($returndbconnect);
	}else{
		echo 'Please go to the <a href="../../../topic_analysis_00_menu.php">menu</a>.<br>';
	}	
?>