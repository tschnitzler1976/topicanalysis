<?php
	set_include_path("../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	
	$showmodificationdetails=0;	
	$returndbconnect=dbconnect();
	if(isset($_POST["add_name_topic_analysis"])){
		//Save the new topic analysis name if it is not already in use.
		$topicanalysisname=htmlstringtostring($_POST["add_name_topic_analysis"]);//ok
		if(ltrim($topicanalysisname)!=''){
			//Already in use?
			$returndbselect=dbselect($returndbconnect,"topic_analysis","name='$topicanalysisname'","name");
			$returndbnumrows=dbnumrows($returndbselect);
			
			if($returndbnumrows>0){//in use
				echo 'The topic analysis with the name "' . $topicanalysisname . '" already exists.</br>';
			}
			
			if($returndbnumrows==0){//not in use; insert new topic analysis name; show fields for new topic analysis to fill out 
				$returndbinsert=dbinsert($returndbconnect,"topic_analysis","(name)","('$topicanalysisname')");
				//What is the $topicanalysisid?
				$returndbselect=dbselect($returndbconnect,"topic_analysis","name='$topicanalysisname'","id");
				$returndbfetcharray=dbfetcharray($returndbselect);
				$topicanalysisid=dbfetchfield($returndbfetcharray,'id');
				$returndbnumrows=dbnumrows($returndbselect);
				if($returndbnumrows==0){
					echo 'Inserting the new topic analysis ' . $topicanalysisname . ' failed.';
					$showmodificationdetails=0;
				}else{
					$showmodificationdetails=1;
				}		
			}
		}else{//An empty string for the new topic analysis name was submitted
			echo 'The string for the new topic analysis name was empty.</br>';
		}	
	}
		
	if(isset($_POST["select_existing_topic_analysis"])){
		$topicanalysisid=htmlstringtostring($_POST["select_existing_topic_analysis"]);
		//Get Topic Analysis Name based on its id in order to show it.
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id=$topicanalysisid","name");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$topicanalysisname=dbfetchfield($returndbfetcharray,'name');
		$showmodificationdetails=1;		
	}
	
	if($showmodificationdetails==1){	
		//Get data for research questions into the field for this topic analysis if there are already any.
		$returndbselect1=dbselect($returndbconnect,"research_questions","id_topic_analysis=$topicanalysisid","id");
		//Get data for search strings in terms of the systematic mapping study's second step.
		$returndbselect3=dbselect($returndbconnect,"search_strings","id_topic_analysis=$topicanalysisid","id");
		//Get search strings to get data for the search string's search results. The search string's search results are the results in terms of the systematic mapping study's second step..
		$returndbselect4=dbselect($returndbconnect,"search_strings_for_results","id_topic_analysis=$topicanalysisid","id");
		//Have there already been saved any research questions, search engines or search strings for this topic
		//analysis?
		
		$returndbnumrows=0;
		$returndbnumrows1=dbnumrows($returndbselect1);
		$returndbnumrows3=dbnumrows($returndbselect3);
		$returndbnumrows4=dbnumrows($returndbselect4);
	
		$txtresearchquestions="";
		$txtresearchquestionsid="";
		if($returndbnumrows1>0){	
			while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($returndbselect1)){
			$txtresearchquestions=$txtresearchquestions . $name;
			//$txtresearchquestions=$txtresearchquestions . $name . chr(13) . chr(10);
			$txtresearchquestionsid=$txtresearchquestionsid . $id . ",";
			}
		}
				
		$txtsearchstrings="";
		$txtsearchstringsid="";
		if($returndbnumrows3>0){
			
			while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($returndbselect3)){
			$txtsearchstrings=$txtsearchstrings . $name . '$';
			//$txtsearchstrings=$txtsearchstrings . $name . '$' . chr(13) . chr(10);
			$txtsearchstringsid=$txtsearchstringsid . $id . ',';
			}
		}
		
		$txtsearchstrings2="";
		$txtsearchstringsid2="";
		if($returndbnumrows4>0){
			
			while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($returndbselect4)){
			$txtsearchstrings2=$txtsearchstrings2 . $name . '$';
			//$txtsearchstrings=$txtsearchstrings . $name . '$' . chr(13) . chr(10);
			$txtsearchstringsid2=$txtsearchstringsid2 . $id . ',';
			}
		}
	
	
	echo '?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	
	<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	</head>
	
	<body>
	<form method="post" action="topic_analysis_02_modify_treatment_02.php">
	
		<br />
		<table style="width: 100%">
			<tr>
				<td style="width: 481px">
				<table style="width: 159%">
					<tr>
						<td style="width: 520px; height: 23px;"></td>
						<td style="height: 23px"><a href="../topic_analysis_00_menu.php">Go back to the menu</a></td>
					</tr>
					<tr>
						<td style="width: 520px; height: 23px;">View and modify topic analysis&nbsp;&nbsp;&nbsp;&nbsp; 
						<input name="txttopicanalysis" type="text" tabindex="0" value=' . $topicanalysisname .' size="35" /></td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 520px; height: 23px;">&nbsp;</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					</table>
				</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 481px">Please provide research questions for 
				"' . $topicanalysisname . '" in the text field </td>
				<td><input name="hiddentextresearchid" type="hidden" value="' . $txtresearchquestionsid .'"/></td>
			</tr>
			<tr>
				<td style="width: 481px">below. In order to divide research questions from each other 
				please finalize</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td style="height: 23px; width: 481px">&nbsp;each research 
					question with a question mark (question mark=?).</td>
				<td style="height: 23px"></td>
			</tr>
			<tr>
				<td style="height: 23px; width: 481px">&nbsp;</td>
				<td style="height: 23px">&nbsp;</td>
			</tr>
			<tr>
				<td style="height: 236px; width: 481px">
				<textarea name="textarea_research_questions" style="width: 657px; height: 231px" tabindex="3">' . $txtresearchquestions .'</textarea></td>
				<td style="height: 236px"></td>
			</tr>
			<tr>
				<td style="width: 481px;height: 23px;"width: 509px">
				&nbsp;</td><td>&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 481px; height: 9px;"width: 509px">
				If you have a handler for a web search engine please provide the 
				search strings </td><td style="height: 9px"></td>
			</tr>
			<tr>
				<td style="width: 481px; height: 23px;"width: 509px">
				as URLs in the text field below for the web search engine.</td><td style="height: 23px">
				&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 481px; height: 23px;"width: 509px">
					Search results from each URL should be contributrions for answering 
					the above</td><td style="height: 23px"></td>
			</tr>
			<tr>
				<td style="width: 481px; height: 23px;"width: 509px">
					provided research questions</td><td style="height: 23px">&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 481px; height: 9px;">The search strings will 
				be executed in the order you list them in the text field</td>
				<td style="height: 9px"></td>
			</tr>
			<tr>
				<td style="width: 481px">below.</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 481px">Please write the symbol = "$" after 
				each search string.</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 481px">A search string without a Dollarsymbol 
				will not be saved!</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 481px">&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td style="width: 481px">
	<textarea name="textareasearchstrings" style="width: 653px; height: 231px" rows="1" tabindex="5">' . $txtsearchstrings . '</textarea></td>
				<td><input name="hiddentopicanalysisid" type="hidden" value="' . $topicanalysisid .'"/></td>
			</tr>
							<tr>
					<td style="width: 481px; height: 23px;">
					<table style="width: 83%">
						<tr>
							<td style="width: 342px">&nbsp;</td>
							<td>
							&nbsp;</td>
						</tr>
						
										<tr>
					<td style="width: 342px; height: 156px;"width: 509px">
					The picture beside illustrates how rows of the text area 
					field below the picture have to be filled.<br>If you have a handler to 
					complete the search 
					then 
					write this handler\'s identifier in the i-th row where the 
					i-th column is an attribute in the picture beside. If you do 
					not have a handler for this attribute please write 0$ in 
					this related row instead. Any row 
					must be finished by a Dollarsymbol "$" otherwise the 
					parameter for a row will not be saved.<br>The following handler for the following attributes 
					already exist:<br>
					<a href="http://www.researchgate.net">www.researchgate.net</a>$ 
					for "first_link_to_abstracttext",<br>
					<a href="http://www.researchgate.net">www.researchgate.net</a>$ 
					for 
					"abstracttext",<br>dummy_searchengine$ for 
					"path_to_pdffulltext",<br>dummy_searchengine$ for 
					"pdffulltext_as_text" and<br>dummy_searchengine$ for 
					"pdffulltext_as_text_extracted.</td>
											<td style="height: 156px">
											<img alt="" height="521" src="01.png" width="461"></td>
				</tr>
						
				<tr>
					<td style="width: 342px"><input name="hiddentextsearchstringsid2" type="hidden" value="' . $txtsearchstringsid2 .'"/></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td style="width: 342px">
					<textarea name="textareasearchstringsforresults" style="width: 653px; height: 231px" rows="1" tabindex="3">' . $txtsearchstrings2 . '</textarea></td>
					<td>&nbsp;</td>
				</tr>		
			
			<tr>
				<td style="width: 342px; height: 23px;">
				<table style="width: 83%">
					<tr>
						<td style="width: 255px">&nbsp;</td>
						<td>
	<input name="submit_topic_analysis_02_modify" type="submit" value="Save" tabindex="6"/></td>
					</tr>
				</table>
				</td>
				<td style="height: 23px"></td>
			</tr>
			</table>
	
	</form>
	</body>
	
	</html>';
		dbdisconnect($returndbconnect);
	}else{
		echo 'Please go to the <a href="../topic_analysis_00_menu.php">menu</a>.';
	}
?>