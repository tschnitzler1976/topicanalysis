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
	$idldaset=false;
	$showlinktopopup=false;
	if(isset($_POST["select_existing_lda"])){
		$idlda=$_POST["select_existing_lda"];
		$idldaset=true;
		$showlinktopopup=true;
	}elseif(isset($_GET["idlda"])){
		$idlda=$_GET["idlda"];
		$idldaset=true;
	}
	if($idldaset){
		//1. d) Make a selection in table search_results for the selections in table lda for this lda. Copy these selections from table search_results in table lda_id_search_results.
		$returndbconnect=dbconnect();
		//Get any information necessary to do lda.
		$returndbselect=dbselect($returndbconnect,"lda","id=$idlda","id");
		if($returndbselect!=false){
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
			
			if($showlinktopopup){
				$outputtouser='<tr><td><a href="../topic_analysis_00_menu.php">Go back to the menu</a></td><td>&nbsp;</td></tr>';
			}else{
				$outputtouser='';
			}
			
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
	
				if($showlinktopopup){
					$getlink='<a href="topic_analysis_14_output_LDA_treatment_01.php?idlda=' . $idlda . '" target="_blank">link to this output</a>';
					$outputtouser=$outputtouser . '<tr><td>To link this outputpage for lda  "' . $ldaname .'" please copy the ' . $getlink . ' to the external html-page where this output should be linked.</td><td>&nbsp;</td></tr>';
				}
				$outputtouser=$outputtouser . '<tr><td>Output of lda "' . $ldaname .'" of the topic analysis "' . $topicanalysisname . '" for the following selection:</td><td></td></tr>';
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
					
				$outputtouser=$outputtouser . '<tr><td>' . $consideredconferenceoutput . '.</td><td></td></tr>';
				$outputtouser=$outputtouser . '<tr><td>' . $yearfromtoselectedoutput . '.</td><td>&nbsp;</td></tr>';
				$outputtouser=$outputtouser . '<tr><td>LDA-output with the help of <a href="http://kennyshirley.com/LDAvis/#topic=10&lambda=1&term=">LDAvis</a> as follows:</td><td>&nbsp;</td></tr>';
				
				//Show the details of any scientific article that affected the displayed LDAvis-Output above
				$inputdir=$inputexecuteoutputdir . 'input/';
				$handle=opendir($inputdir);
				$outputtouser2='<tr><td>The LDAvis-output from above was affected by the following scientific articles:</td><td>&nbsp;</td></tr>';
				$outputtouser2=$outputtouser2 . '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
				$returndbselect=dbselect($returndbconnect,"lda_id_search_results","id_lda='$idlda'", "id");
				if(dbnumrows($returndbselect)>0){
					$zaehler=1;
					while(list($id,$id_lda,$id_search_result_row) = mysqli_fetch_row($returndbselect)){
						$idsearchresult=$id_search_result_row;
						$returndbselect2=dbselect($returndbconnect,"search_results","id='$idsearchresult'", "id");			
						$returndbfetcharray=dbfetcharray($returndbselect2);
						$title=dbfetchfield($returndbfetcharray,'title');
						$authors=dbfetchfield($returndbfetcharray,'authors');
						$conference=dbfetchfield($returndbfetcharray,'conference');
						$year=dbfetchfield($returndbfetcharray,'year');
						
						$outputtouser2=$outputtouser2 . '<tr><td>' . $zaehler . ') Title: ' . $title . '</td><td>&nbsp;</td></tr>
						<tr><td>Author(s): ' . $authors . '</td><td>&nbsp;</td></tr>
						<tr><td>Conference: ' . $conference . '</td><td>&nbsp;</td></tr>
						<tr><td>Year: ' . $year . '</td><td>&nbsp;</td></tr>';
						$zaehler++;
						$outputtouser2=$outputtouser2 . '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';					
					}
				}else{
						$outputtouser2=$outputtouser2 . '<tr><td>No existing executed lda "' . $ldaname . ' available.</td><td>&nbsp;</td></tr>';					
				}	
	
				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
					
					<head>
					<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
					</head>
					
					<body>
					
					<table>' . $outputtouser . '</table>
					<table><tr><td>
					<iframe name="vis" src="' . $httpoutputdir . 'vis/index.html" style="width: 1266px; height: 792px;">Your 
					browser does not support inline-frames or you have to change the configuration settings 
					of your browser. 			</iframe></td><td>&nbsp;</td></tr></table>
					<table>' . $outputtouser2 . '</table>
					</body>
					
					
					</html>';	
				}else{
					if($showlinktopopup){
						echo 'No output generated for lda ' . $ldaname . ' before. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
					}else{
						echo 'No output generated for lda ' . $ldaname . ' before.</br>';
					}					
				}
			}else{
				if($showlinktopopup){
					echo 'No data found for this lda. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
				}else{
					echo 'No data found for this lda.</br>';
				}				
			}
		dbdisconnect($returndbconnect);
	}else{
		if($showlinktopopup){
			echo 'No lda selected. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
		}else{
			echo 'No lda selected.</br>';		
		}
	}													
?>
