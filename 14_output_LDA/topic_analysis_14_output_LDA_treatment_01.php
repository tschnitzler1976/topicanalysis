<?php
/*Algorithm:
  0. Select the existing LDA.
  	1. g) Generate the output page. Show the http-path to the user which can be linked to a Wiki-Page like "Parsing"
  	 ("Parsing is this diploma thesis' example for the topic analysis). 
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
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id='$topicanalysisid'","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$topicanalysisname=dbfetchfield($returndbfetcharray,'name');			
		$outputdir=$inputexecuteoutputdir . 'output/';
		
		$outputtouser='<tr><td><a href="../topic_analysis_00_menu.php">Go back to the menu</a></td><td></td></tr>';
		if(is_file($outputdir . 'vis/index.html')){
			//We need to convert the $outputdir to its http-path appearance
			$httpoutputdirarray=explode('/',$outputdir);
			$dirfound=false;
			$httpoutputdir='../';
			for($zaehler=0;$zaehler<sizeof($httpoutputdirarray);$zaehler++){
				if($httpoutputdirarray[$zaehler]=='13_execute_LDA'){
					$dirfound=true;
				}
				if($dirfound){
					$httpoutputdir=$httpoutputdir . $httpoutputdirarray[$zaehler] . '/';
				}
			}
			

			$outputtouser=$outputtouser . '<tr><td>Output of lda "' . $ldaname .'" for the following attributes:</td><td></td></tr>';
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
			$outputtouser=$outputtouser . 'LDA-output with the help of <a href="http://kennyshirley.com/LDAvis/#topic=10&lambda=1&term=">LDAvis</a> as follows:</td><td></td></tr>';
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				
				<head>
				<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
				</head>
				
				<body>
				
				<table>' . $outputtouser . '</table>
				
				
				<iframe name="vis" src="' . $httpoutputdir . 'vis/index.html" style="width: 1266px; height: 792px;">Your 
				browser does not support inline-frames or you have to change the configuration settings 
				of your browser. 			</iframe>
				</body>
				</html>';	
			}else{
				echo 'No output generated for lda ' . $ldaname . ' before. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
			}			
		dbdisconnect($returndbconnect);
	}else{
		echo 'No lda selected. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
	}													
?>
