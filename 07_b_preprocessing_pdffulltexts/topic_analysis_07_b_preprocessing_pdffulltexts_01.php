<?php
	set_include_path("../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	if(isset($_POST["select_existing_topic_analysis"])){
		$nopdffulltextextractedtext=true;
		$returndbconnect=dbconnect();
		$topicanalysisid=htmlstringtostring($_POST["select_existing_topic_analysis"]);
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id='$topicanalysisid'","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$topicanalysisname=dbfetchfield($returndbfetcharray,'name');
		
		$dbselectresult1=dbselect($returndbconnect,"search_strings","id_topic_analysis='$topicanalysisid'","id");
		$dbnumrowsvar1=dbnumrows($dbselectresult1);
		if($dbnumrowsvar1>0){
 		//We have more than 0 searchstrings for this topic analysis. Thus continue.
			$zaehler=0;
			while(list($id) = mysqli_fetch_row($dbselectresult1)){							
				$searchstringsids[$zaehler]=$id;
				$zaehler++;
			}			
			mysqli_free_result($dbselectresult1);
			
			/*We have to look in each row of table search_results whether the value of the column
  		     exclusion_already_done is 1. If the value of "exclusion_already_done" is 0 then an exclusion did not happen before.
			 Thus we prevent the user from proceeding.*/
			$exclusionhappenedbefore=true;
			for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND exclusion_already_done=0","id");			
				$dbnumrowsvar2=dbnumrows($returndbselect);
				if($dbnumrowsvar2>0){
					$exclusionhappenedbefore=false;
				}
			}
			mysqli_free_result($returndbselect);	

			if($exclusionhappenedbefore==true){
						
				for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
					$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND pdffulltext_as_text_extracted <>'' AND exclude=0","conference");			
					$dbnumrowsvar2=dbnumrows($returndbselect);
					if($dbnumrowsvar2>0){
						while(list($id,$id_search_strings,$exclude,$authors_row,$title_row,$conference_row,$year_row,$first_link_to_abstracttext,$abstracttext,$abstracttext_for_lda,$first_link_to_pdffulltext,$path_to_pdffulltext,$pdffulltext_as_text,$pdffulltext_as_text_extracted_row) = mysqli_fetch_row($returndbselect)){							
							/*We fetch the id in order to address the pdffulltext_as_text_extracted to move pdffulltext_as_text_extracted to the column pdffulltext_for_lda*/
							$id_search_result=$id;
							$pdffulltextastextextracted=base64_decode($pdffulltext_as_text_extracted_row);
							$pdffulltextastextextractedarray=str_split($pdffulltextastextextracted);
							$pdffulltextastext='';	
							for($zaehler3=0;$zaehler3<sizeof($pdffulltextastextextractedarray);$zaehler3++){
								$asciinumber=ord($pdffulltextastextextractedarray[$zaehler3]);
								switch($asciinumber){
								case 10://LF
									$pdffulltextastext=$pdffulltextastext . $pdffulltextastextextractedarray[$zaehler3];	
								break;
								case 13://CR
									$pdffulltextastext=$pdffulltextastext . $pdffulltextastextextractedarray[$zaehler3];												
								break;
								case 32://Space
									$pdffulltextastext=$pdffulltextastext . $pdffulltextastextextractedarray[$zaehler3];								
								break;
								case 46://Fullstop
									$pdffulltextastext=$pdffulltextastext . $pdffulltextastextextractedarray[$zaehler3];
								break;
								}
								
								if($asciinumber>64){
									if($asciinumber<91){//a-z
										$pdffulltextastext=$pdffulltextastext . $pdffulltextastextextractedarray[$zaehler3];
									}
								}
										
								if($asciinumber>96){
									if($asciinumber<123){//A-Z
										$pdffulltextastext=$pdffulltextastext . $pdffulltextastextextractedarray[$zaehler3];
									}
								}
							}
							$pdffulltextastext=base64_encode($pdffulltextastext);
							$returnupdate=dbupdate($returndbconnect,"search_results","pdffulltext_for_lda='$pdffulltextastext',preprocessing_pdffulltext_already_done='1'","id='$id_search_result'");
							$nopdffulltextextractedtext=false;
						}
					}
				}
				mysqli_free_result($returndbselect);
				if($nopdffulltextextractedtext==false){
					//Show the results in a table
					$tablesearchresultssnapshot=createsnapshotoftablesearchresults($topicanalysisid);
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
		<input name="texttopicanalysisid" type="hidden" value="' . $topicanalysisid .'"/>
		<br />
		<table style="width: 100%">
			<tr>
				<td style="width: 481px; height: 79px;">
				<table style="width: 159%">
					<tr>
						<td style="width: 644px; height: 23px;">Preprocessing for pdffulltexts_as_text_extracted for
						topic analysis "' . $topicanalysisname . '".</td>
						<td style="height: 23px"><a href="../topic_analysis_00_menu.php">Back to the menu</a></td>
					</tr>
					<tr>
						<td style="width: 644px; height: 23px;">&nbsp;</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					</table><table>The preprocessing results from column "pdffulltexts_as_texts_extracted" are saved in the column "pdffulltext_for_lda". Please compare with
					the results in the table below. Additionally, you can 
					<a href="../topic_analysis_09_optimize_pdffulltexts_for_lda.php">manually optimize pdffulltexts for lda</a>. Thank you.</table>
					<table style="width: 445px"><tr><td>&nbsp;</td>
						<td style="width: 334px">&nbsp;</td><td style="width: 191px">
						&nbsp;</td></tr></table>' . $tablesearchresultssnapshot . '	
					</body>
	
					</html>';
			}else{
					echo 'No extracted texts from pdffulltexts found for this topic analysis. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>';			
				}
			}else{
				echo 'Please run the <a href="../topic_analysis_06_exclude_search_results.php">exclusion procedure</a> before preprocessing. Thank you!';
			}	
		}else{
			echo 'No search strings found for this topic analysis. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>';
		}
		dbdisconnect($returndbconnect);	
	}else{
			echo 'No topic analysis provided. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>';
	}
?>