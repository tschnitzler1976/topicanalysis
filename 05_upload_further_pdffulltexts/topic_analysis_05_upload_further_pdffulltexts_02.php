<?php
/*Algorithm:
  Treatment of topic_analysis_05_upload_further_pdffulltexts_01.php
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
				//Get each value of each file_upload_field in the form from topic_analysis_05_upload_further_pdffulltexts_01.php.
				$zaehler3=0;
				$publicfullpathwget='';
				$outputstring='';
				//This is the path to the pdffulltexts of this topic analysis
				$dirname=pathtopdffulltexts($topicanalysisname);
				if(is_dir($dirname)==false){
					mkdir($dirname);
				}
				
				$dirname=pathtopdffulltextsall($topicanalysisname);
				if(is_dir($dirname)==false){
					mkdir($dirname);
				}
				
				$dirname=pathtopdffulltextsalltemp($topicanalysisname);
					if(is_dir($dirname)==false){
					mkdir($dirname);
				}
								
				//Download each pdffile from any submitted http-fullpath with the help of wget. Save the new information in
				//table search_results 
				for($zaehler=0;$zaehler<sizeof($searchresultids);$zaehler++){
					$publicfullpath="file_" . $searchresultids[$zaehler];
					if(isset($_POST['' . $publicfullpath . ''])){
						if(ord($_POST['' . $publicfullpath . ''])!=0){
							$publicfiletableexplodearray=explode('/',$_POST['' . $publicfullpath . '']);
							if(sizeof($publicfiletableexplodearray>2)){
								if(ltrim(substr($publicfiletableexplodearray[0],0,4))=='http'){
									//Information for wget
									$publicfullpathwget=$publicfullpathwget . $_POST['' . $publicfullpath . ''] . chr(13) . chr(10);
									//Information for table "search_results"
									$publicfullpathtable[$zaehler3]=$_POST['' . $publicfullpath . ''];						
									$publicfiletable[$zaehler3]=$publicfiletableexplodearray[sizeof($publicfiletableexplodearray)-1];						
									$localfullpath[$zaehler3]=$dirname . $publicfiletable[$zaehler3]; 
									$outputstring=$outputstring . '<tr><td>Downloading and saving ' . $publicfullpathtable[$zaehler3] . ' to ' . $localfullpath[$zaehler3]. '.</td><td>&nbsp;</td></tr>';
									$idlocalfullpath[$zaehler3]=$searchresultids[$zaehler];
									$zaehler3++;
								}else{
									$outputstring=$outputstring . '<tr><td>The first four characters of the download http-fullpath must be "http" in ' . $_POST['' . $publicfullpath . ''] . '.</td><td>&nbsp;</td></tr>';
								}
							}else{
								$outputstring=$outputstring . '<tr><td>There must be at least 3 slashes in your download http-fullpath in in ' . $_POST['' . $publicfullpath . ''] . '.</td><td>&nbsp;</td></tr>';
							}
						}
					}	
				}
				
				if($zaehler3==0){
					$outputstring=$outputstring . '<tr><td>No upload because no textfield contained a http-fulltextlink.</td><td>&nbsp;</td></tr>';
				}elseif($zaehler3>0){
					//We download with wget.exe
					execwgettempdir($publicfullpathwget,false,$dirname);
					$finfo = finfo_open(FILEINFO_MIME_TYPE); 
					for($zaehler=0;$zaehler<$zaehler3;$zaehler++){
						//We check whether the downloaded file is pdf. Unless it is pdf it is deleted.
					    if(file_exists($localfullpath[$zaehler])){
					    	if(stripos(finfo_file($finfo, $localfullpath[$zaehler]),'pdf')!=false){
								/*It is pdf. Thus we do dbupdate with the pdffulltext to all affected columns in table "search_results"*/
								//We upload data from the downloaded pdf-file to table search_results and to the pdffulltext folder of this topic analysis
								$firstlinktopdffulltext='The pdffulltext was manually uploaded. Thus there is no downloadlink available.';
								$pathtopdffulltext=pathtopdffulltextsall($topicanalysisname) . $publicfiletable[$zaehler];
								$pdffulltextastext=base64_encode('This text was deleted because it was replaced by the manually extracted text in column pdffulltext_as_text_extracted.');
								/*We execute pdftotxt in order to save pdftotext's output in column "pdffulltext_as_text_extracted"*/
								exec(pdftotext_executable() . ' ' . $localfullpath[$zaehler]);
								/*We address the output of pdftotext*/
								$strlength=strlen($localfullpath[$zaehler]);
								$pathtopdffulltextastext=substr($localfullpath[$zaehler],0,$strlength-3) . "txt";
								$contentsoftextfile=base64_encode(file_get_contents($pathtopdffulltextastext));
								$returnupdate=dbupdate($returndbconnect,"search_results","first_link_to_pdffulltext='$firstlinktopdffulltext',path_to_pdffulltext='$pathtopdffulltext',pdffulltext_as_text='$pdffulltextastext',pdffulltext_as_text_extracted='$contentsoftextfile'","id='$idlocalfullpath[$zaehler]'");						
								if($returnupdate){
									$outputstring=$outputstring . '<tr><td>Update-Query for ' . $localfullpath[$zaehler] . ' successful.</td><td>&nbsp;</td></tr>';
									copy($localfullpath[$zaehler],pathtopdffulltextsall($topicanalysisname) . $publicfiletable[$zaehler]);
									$outputstring=$outputstring . '<tr><td>Upload: Copying ' .  $localfullpath[$zaehler] . ' to ' . pathtopdffulltextsall($topicanalysisname) . $publicfiletable[$zaehler] . '.</td><td>&nbsp;</td></tr>';
								}else{
									$outputstring=$outputstring . '<tr><td>Update-Query for ' . $localfullpath[$zaehler] . ' not successful.</td><td>&nbsp;</td></tr>';
								}
					   		}else{
					    		//Delete the file because it is not pdf.
					    		if(file_exists($localfullpath[$zaehler])){
						    		unlink($localfullpath[$zaehler]);
									$outputstring=$outputstring . '<tr><td>' .  $localfullpath[$zaehler] . ' not uploaded and deleted because it is not a pdf-file.</td><td>&nbsp;</td></tr>';
						    	}
					    	}
					    }else{
					    	$outputstring=$outputstring . '<tr><td>File ' . $localfullpath[$zaehler] . ' was not downloaded. Is the link to the pdffulltext correct? Are you connected to the internet?</td><td>&nbsp;</td></tr>';
						}
					}
					rrmdir(pathtopdffulltextsalltemp($topicanalysisname));
			    	$outputstring=$outputstring . '<tr><td>temp-folder ' . pathtopdffulltextsalltemp($topicanalysisname) . ' deleted.</td><td>&nbsp;</td></tr>';
				}				
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
						<td style="width: 644px; height: 23px;">Results for enriching
						topic analysis "' . $topicanalysisname . '" with manually downloaded pdffulltexts.</td>
						<td style="height: 23px"><a href="../topic_analysis_00_menu.php">Back to the menu</a></td>
					</tr>
					<tr>
						<td style="width: 644px; height: 23px;">&nbsp;</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					</table><table>' . $outputstring . '</table>
					<table style="width: 445px"><tr><td>&nbsp;</td>
						<td style="width: 334px">&nbsp;</td><td style="width: 191px">
						&nbsp;</td></tr></table>	
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