<?php
/*   This is the script for handling the abstracttext in case the searchresultid=0 (update all first_links_to_abstracts) 
*/
	set_include_path("../../../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	if(isset($_GET["topicanalysisid"])){
		$returnvalue[0]=true;	
		$returndbconnect=dbconnect();
		$topicanalysisid=htmlstringtostring($_GET["topicanalysisid"]);
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id='$topicanalysisid'","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$topicanalysisname=dbfetchfield($returndbfetcharray,'name');
		if(isset($_GET["searchresultid"])){
			/*If searchresultid is 0 then we know that we have to fill any field in column "abstracttext" for table
			"search_results". If searchresultid is anything else then 0 then we know that we have to fill one field in column
			"abstracttext" for table "search_results". Let's find the value out.*/				
			$searchresultid=htmlstringtostring($_GET["searchresultid"]);
			if($searchresultid==0){
				/*We complete any fields in table "search_strings" for column "abstracttext" 
				Now we need all searchstringids that belong to the committed topicanalysisid.
				That means let us find any searchstringid at first.*/
				
				$dbselectresult1=dbselect($returndbconnect,"search_strings","id_topic_analysis='$topicanalysisid'","id");
				$dbnumrowsvar1=dbnumrows($dbselectresult1);
				if($dbnumrowsvar1>0){
				//We have more than 0 searchstrings for this topic analysis. Thus continue.
					$zaehler=0;
					$searchstringforresult="";
					$returnvalue[0]=true;
					$returnvalue[1]='Completion was successful. Thank you.';
					$zaehler3=0;
					while(list($id) = mysqli_fetch_row($dbselectresult1)){							
						$searchstringsids[$zaehler]=$id;
						$zaehler++;
					}
					mysqli_free_result($dbselectresult1);
					
					for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
						$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]'","id");			
						$dbnumrowsvar2=dbnumrows($returndbselect);
						if($dbnumrowsvar2>0){
							while(list($id,$id_search_strings,$exclude,$authors,$title,$conference,$year,$first_link_to_abstracttext,$abstracttext) = mysqli_fetch_row($returndbselect)){							
								/*We fetch the value from the field in column first_link_to_abstracttext.*/
								$first_link_to_abstracttext=$first_link_to_abstracttext;
								/*This first_link_to_abstracttext stored in an array is for finding the right file input for the
								particular "first_link_to_abstract" value that is bound to a particular search_result_id.*/
								$first_link_to_abstracttext_forcomparsionwithfilename[$zaehler3]=$first_link_to_abstracttext;
								/*We also fetch the id where we fetched first_link_to_abstracttext. Then it is easier to update
								the field in table search_results' colum 'abstracttext' in table "search_results" with the
								html-information from www.researchgate.net.*/
								$idforcomparsionwithfilename[$zaehler3]=$id; 
								/*We concatenate the search string for the search engine with the extracted title.
								We write the concatenated string in a variable called $searchstringforresult.
								$searchstringforresult can be used for writing into the inputfile for wget.*/
								$searchstringforresult=$searchstringforresult . "https://www.researchgate.net/" . $first_link_to_abstracttext_forcomparsionwithfilename[$zaehler3] . chr(10) . chr(13);
								$zaehler3++;
							}
						}
					}
					mysqli_free_result($returndbselect);	

					$dirname=pathtosearchresulttwo($topicanalysisname) . 'abstracttext/';
					if(is_dir($dirname)==false){
						mkdir($dirname);
					}

					$dirname=$dirname . 'all/';
					if(is_dir($dirname)==false){
						mkdir($dirname);
					}
					
					if(wgetfromlocalorfrominternet()!=1){
						/*We are connected to the internet and we do not want to use local saved files but we want new files from the internet again.
						That means: Fetch anything with wget.exe in many files and update anything at column abstracttext of table "search_results"*/
						execwget($searchstringforresult,false,$dirname);
					}

					if(file_exists($dirname . "wgetinput.txt")){
						unlink($dirname . "wgetinput.txt");
					}
					
					if(file_exists($dirname . "index.html")){
						unlink($dirname . "index.html");
					}

					
					$handle=opendir($dirname);
	 				while(($file = readdir($handle)) !== false) {
		  	    		if ($file!="." && $file!=".."){
							//rename files because file names are too long
							$twopartsoffilenamearray=explode("@",$file);
							$sizearray=sizeof($twopartsoffilenamearray);
							if($sizearray==2){
								//We have @ in that filename, i.e. this filename is too long. It must be shortened.							
								$oldfile=$dirname . $file;
								$newfile=$dirname . $twopartsoffilenamearray[0];
								$renamedfilewithdir=renamefile($oldfile,$newfile);							
							}
						}					
					}	
					closedir($handle);
					
					/*Because we shortened the filenames of the files above that were downloaded with the help of wget.exe
					before we have to shorten the "first_links_to_abstracttexts" as well. Otherwise levenshtein below cannot
					match similar filenames and "first_links_to_abstracttexts".
					*/ 
					for($zaehler4=0;$zaehler4<sizeof($first_link_to_abstracttext_forcomparsionwithfilename);$zaehler4++){
						$first_link_to_abstracttext_forcomparsionwithfilename_array=explode("?",$first_link_to_abstracttext_forcomparsionwithfilename[$zaehler4]);
						$sizeofarray=sizeof($first_link_to_abstracttext_forcomparsionwithfilename_array);
						if($sizeofarray==2){
							$first_link_to_abstracttext_forcomparsionwithfilename[$zaehler4]=$first_link_to_abstracttext_forcomparsionwithfilename_array[0];
						}
					}
					
					/*UPDATE each field in column "abstractext" in table "search_results" that fulfills the
					following criteria. The file name that because of levenshtein is the closest approach to first_link_to_abstracttext in
					table "search_results" and the filename.*/
									
					/*Stringcomparison between filename and title of table search_results with the help levenshtein. 
					Only the smallest comparison-value of levenshtein is taken because these arrayvalues of filename
					and databaseentry address the same scientific article and therefore the file contains the
					link to this scientific paper's abstract.	
					
					If there is a match between filename and database entry as described above the link to the abstract
					is extracted from the filename's filecontent in order to update the field of table
					"search result's" column "abstracttext".*/
					
					$handle=opendir($dirname);
			        $levenshteinvalue="";
			        $zaehler=0;
			        $zaehler5=0;
			        $testispartoffilename=0;
			        while(($file = readdir($handle)) !== false) {
					    $levenshteinvalue="";
						for($zaehler2=0;$zaehler2<sizeof($first_link_to_abstracttext_forcomparsionwithfilename);$zaehler2++){
			        		$levenshteinvalue[$zaehler2]=levenshtein($first_link_to_abstracttext_forcomparsionwithfilename[$zaehler2],$file);
							$levenshteinvalueafter=$levenshteinvalue[$zaehler2];
							if($zaehler2==0){
								$valuesmallest=$levenshteinvalueafter;
							}else{					
								if($levenshteinvalueafter<$valuesmallest){
									$valuesmallest=$levenshteinvalueafter;
									$databaseentry=$first_link_to_abstracttext_forcomparsionwithfilename[$zaehler2];
									
									$id=$idforcomparsionwithfilename[$zaehler2];
									$filename=$file;
								}
							}				
						}												
						/*We now have the filename for the scientific paper's new content in field of column
						"first_link_to_abstracttext":*/
						if($file!="." && $file!=".."){
							/*We extract the abstract from the file content with the help of:
							(function(){widgetLoader.createInitialWidget({"data":{"publicationFulltextFallbackViewer":null,"publicationUid":
							*/
							$returnreadfileindir='';
							$returnreadfileindir=read_file_in_dir($dirname,$file);
							//for first_link_to_pdffulltext because sometimes we have a link to the pdffulltext beside the abstracttext at www.researchgate.net
							//after the abstracttext is fetched.
							$returnreadfileindirforpdflink=$returnreadfileindir;
							//Let us fetch the abstracttext:
							$importantbeginofreturnreadfileindir01=stripos($returnreadfileindir,'function(){widgetLoader.createInitialWidget({"data":{"publicationFulltextFallbackViewer":null,"publicationUid');
							if($importantbeginofreturnreadfileindir01>0){
								$importantbeginofreturnreadfileindir0102=stripos($returnreadfileindir,'"abstract":null,"publicationRomeoClassification"',$importantbeginofreturnreadfileindir01);
								if($importantbeginofreturnreadfileindir0102>0){
									$importantbeginofreturnreadfileindir0103=stripos($returnreadfileindir,'"fullContext":"');
									if($importantbeginofreturnreadfileindir0103>0){
										//this are the abstract that start at a different part
										$importantbeginofreturnreadfileindir010301=stripos($returnreadfileindir,':"',$importantbeginofreturnreadfileindir0103);
										$beginning=$importantbeginofreturnreadfileindir010301+1;
										//We have at the beginning where there is no headline, i.e. headline "abstract" or something else. Now we look for the end of the abstract.
										$end=stripos($returnreadfileindir,'","enforceShowingCompleteContext":true}',$beginning);
										$returnreadfileindir=substr($returnreadfileindir,$beginning,$end-$beginning);
										$zaehler5++;								
									}else{
										//in this case we have no abstracts
										$returnreadfileindir='No abstract available from www.researchgate.net.';
										$zaehler5++;
									}
								}else{
									//here are the abstract at the "normal position:
									$importantbeginofreturnreadfileindir0102=stripos($returnreadfileindir,'"abstract"',$importantbeginofreturnreadfileindir01);
									$importantbeginofreturnreadfileindir0103=stripos($returnreadfileindir,':',$importantbeginofreturnreadfileindir0102);
									$beginning=$importantbeginofreturnreadfileindir0103+2;
									$end=stripos($returnreadfileindir,'","publicationHeader',$beginning);
									$returnreadfileindir=substr($returnreadfileindir,$beginning,$end-$beginning);
									//do not update a wrong abstract to a scientific paper.
									$partofreturnreadfileindir=substr($returnreadfileindir,0,40);
									//if $gotcontentsoffilename does not contain $partofreturnreadfileindir we do not update.
									$gotcontentsoffilename=file_get_contents($dirname . $filename);								
									$testispartoffilename=stripos($gotcontentsoffilename,$partofreturnreadfileindir);
								}
							}
							if($id==''){
								$returnvalue[0]=false;
								$returnvalue[1]=$returnvalue[1] . 'Error while updating column "abstracttext" in table "search_results:</br> One or more than one search result ids were empty.</br>';
							}
																		
							if($returnvalue[0]!=false){
								if($testispartoffilename!=false){
									$returnreadfileindir=htmlspecialchars_decode($returnreadfileindir);
									$returnreadfileindir=htmlstringtostring($returnreadfileindir);								 
									$returnreadfileindir=base64_encode($returnreadfileindir);
									$returnupdate=dbupdate($returndbconnect,"search_results","abstracttext='$returnreadfileindir'","id='$id'");
									if($returnupdate==false){
										$returnvalue[0]=false;
										$returnvalue[1]=$returnvalue[1] . 'Error while updating column "abstracttext" in table "search_results:</br>MySQL update query failed.</br>';
									}
								}
							}
							//We fetch the link to the pdf-document for this abstract from www.researchgate.net if this link is available
							$pdflinksearchstringstart01='data":{"downloadUrl';
							$pdflinksearchstringstart02='http';
							$pdflinksearchstringend='pdf';
							$pdflinksearchstringposstart01=stripos($returnreadfileindirforpdflink,$pdflinksearchstringstart01);
							if($pdflinksearchstringposstart01!=false){
								//We have a link to a pdf-document
								$pdflinksearchstringposstart02=stripos($returnreadfileindirforpdflink,$pdflinksearchstringstart02,$pdflinksearchstringposstart01);
								$beginning=$pdflinksearchstringposstart02;	
								$end=stripos($returnreadfileindirforpdflink,$pdflinksearchstringend,$beginning);
								$first_link_to_pdffulltext=substr($returnreadfileindirforpdflink,$beginning,$end+3-$beginning);
								$withoutbackslasharray=explode(chr(92), $first_link_to_pdffulltext);
								$first_link_to_pdffulltext2="";
								for($zaehler5=0;$zaehler5<sizeof($withoutbackslasharray);$zaehler5++){
									$first_link_to_pdffulltext2=$first_link_to_pdffulltext2 . $withoutbackslasharray[$zaehler5]; 
								}
								$returnupdate=dbupdate($returndbconnect,"search_results","first_link_to_pdffulltext='$first_link_to_pdffulltext2'","id='$id'");
								if($returnupdate==false){
									echo "MySQL-Update for a first_link_to_pdffulltext failed </br>";
								}
							}else{
								/*Here is an alternative Link to PDFs possible if html-files that contain the abstracts from
								  wwwresearchgate.net are considered.*/
							}
							
								

						}
						$zaehler++;	
					}
					closedir($handle);						
					$headline='<tr>
				<td style="width: 528px; height: 23px;" class="auto-style1">Completion of the search results for the topic analysis
				"' . $topicanalysisname . '" of the abstracttexts with the help of www.researchgate.net
				 as a part of the search results if zero abstracttexts have already been provided.</td>
				<td style="height: 23px">&nbsp;</td>
			</tr><tr>
				<td style="width: 528px; height: 23px;" class="auto-style1"><a href="javascript:close();">Close this window</a></td>
				<td style="height: 23px">&nbsp;</td>
			</tr>';
			
					$returnstringforuser='	<tr>
							<td style="width: 743px; height: 23px;">' . $returnvalue[1] . '</td>
							<td>&nbsp;</td>
						</tr>';
			}					
				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	
	<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<style type="text/css">
   {font-weight:normal;color:#181818;background-color:#fffccf}b.b4{font-weight:normal;color:#0c0c0c;background-color:#fffccf}b.b2{font-weight:normal;color:#242424;background-color:#fffeef}
	.auto-style1 {
		text-align: center;
	}
	</style>
	</head>
	
	<body>
		<table style="width: 100%">
			<tr>
				<td style="width: 775px; height: 79px;">
				<table style="width: 159%">' . $headline . '
					<tr>
						<td style="width: 743px; height: 23px;" class="auto-style1">
						&nbsp;</td>
						<td>&nbsp;</td>
					</tr>' . $returnstringforuser . '
					<tr>
						<td style="width: 743px; height: 23px;">Please raise 
						max_execution_time in php.ini if you get a timeout.</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 743px; height: 23px;">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					</table>
				</td>
				<td style="height: 79px"></td>
			</tr>
			</table></body>
			</html>';						
			}
		}else{
			echo 'No search result identifier provided for the completion of the links to the abstracttexts with the help of www.researchgate.net as a part of the search results if zero links to the abstracts have already been provided. <a href="javascript:close();">Close this window</a>';
		}
		dbdisconnect($returndbconnect);	
		}else{
			echo 'No topic analysis provided for the completion of the abstracttexts with the help of www.researchgate.net as a part of the search results if zero abstracttexts have already been provided. <a href="javascript:close();">Close this window</a>';
		}
?>