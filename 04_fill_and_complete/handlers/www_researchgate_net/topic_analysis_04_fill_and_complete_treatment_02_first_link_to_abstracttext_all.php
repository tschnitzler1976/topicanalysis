<?php
/*   This is the script for handling the first_links_to_abstracts in case the searchresultid=0 (update all first_links_to_abstracts) 
*/
	set_include_path("../../../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	
	if(isset($_GET["topicanalysisid"])){
		$returndbconnect=dbconnect();
		$topicanalysisid=htmlstringtostring($_GET["topicanalysisid"]);
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id='$topicanalysisid'","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$topicanalysisname=dbfetchfield($returndbfetcharray,'name');
		
		if(isset($_GET["searchresultid"])){
			/*If searchresultid is 0 then we know that we have to fill any field in column "first_link_to_abstracts" for table
			"search_results". If searchresultid is anything else then 0 then we know that we have to fill one field in column "first_link_to_abstracts"
			for table "search_results". Let's find the value out.*/				
			$searchresultid=htmlstringtostring($_GET["searchresultid"]);
			if($searchresultid==0){
				/*We complete any fields in table "search_strings" for column "first_links_for_abstracttexts" 
				Now we need all searchstringids that belong to the committed topicanalysisid.
				That means let us find any searchstringid at first.*/
				
				$dbselectresult1=dbselect($returndbconnect,"search_strings","id_topic_analysis='$topicanalysisid'","id");
				$dbnumrowsvar1=dbnumrows($dbselectresult1);
				if($dbnumrowsvar1>0){
				//We have more than 0 searchstrings for this topic analysis. Thus continue.
					$zaehler=0;
					$searchstringforresult="";
					$returnvalue[0]=true;
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
							while(list($id,$id_search_strings,$exclude,$authors,$title) = mysqli_fetch_row($returndbselect)){							//Conversion from title that is embedded in XML to title without XML
								/*We fetch the value from the field in column title and filter.
								filtering we will have a string without xml-tag if xml-tags are around the title.*/
								$title=excludexmltags($title);
								/*This title stored in an array is for finding the right file input for the particular
								"first_link_to_abstract" value that is bound to a particular search_result_id.*/
								$titleforcomparsionwithfilename[$zaehler3]=$title;
								/*We also fetch the id where we fetched the title. Then it is easier to update
								the field in table search_results' colum 'first_link_to_abstracts' in table "search_results" with the
								html-information from www.researchgate.net.*/
								$idforcomparsionwithfilename[$zaehler3]=$id; 
								/*We concatenate the search string for the search engine with the extracted title.
								We write the concatenated string in a variable called $searchstringforresult.
								$searchstringforresult can be used for writing into the inputfile for wget.*/
								$searchstringforresult=$searchstringforresult . "https://www.researchgate.net/search?q=" . $titleforcomparsionwithfilename[$zaehler3] . chr(10) . chr(13);
								$zaehler3++;
							}
						}
					}
					mysqli_free_result($returndbselect);
					
					$dirname=pathtosearchresulttwo($topicanalysisname);	
					if(is_dir($dirname)==false){
						mkdir($dirname);
					}
					
					$dirname=$dirname . 'first_link_to_abstract/';	
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

		
					/*UPDATE each field in column "first_link_to_abstracts" in table "search_results" that fulfills the
					following criteria. The file name that because of levenshtein is the closest approach to the title in
					table "search_results" is the row where the field of the column "first_link_to_abstracts" has to be
					updated with the file contents of this file.*/
				
					/*Stringcomparison between filename and title of table search_results with the help levenshtein. 
					Only the smallest comparison-value of levenshtein is taken because these arrayvalues of filename
					and databaseentry address the same scientific article and therefore the file contains the
					link to this scientific paper's abstract.	
					
					If there is a match between filename and database entry as described above the link to the abstract
					is extracted from the filename's filecontent in order to update the field of table
					"search result's" column "first_link_to_abstracttext".*/
					
					$handle=opendir($dirname);
			        $levenshteinvalue="";
			        $zaehler=0;
			        while(($file = readdir($handle)) !== false) {

					    $levenshteinvalue="";
						for($zaehler2=0;$zaehler2<sizeof($titleforcomparsionwithfilename);$zaehler2++){
			        		$levenshteinvalue[$zaehler2]=levenshtein($titleforcomparsionwithfilename[$zaehler2],$file);
							$levenshteinvalueafter=$levenshteinvalue[$zaehler2];
							if($zaehler2==0){
								$valuesmallest=$levenshteinvalueafter;
							}else{					
								if($levenshteinvalueafter<$valuesmallest){
									$valuesmallest=$levenshteinvalueafter;
									$databaseentry=$titleforcomparsionwithfilename[$zaehler2];
									$id=$idforcomparsionwithfilename[$zaehler2];
									$filename=$file;
									
								}
							}				
						}
			
						/*We now have the filename for the scientific paper's new content in field of column
						"first_link_to_abstracttext":*/
						if ($file!="." && $file!=".."){
							//we extract the abstract link from the file content
							$returnreadfileindir=htmlstringtostring(read_file_in_dir($dirname,$file));
							$importantbeginofreturnreadfileindir=stripos($returnreadfileindir,'class="publication-title js-publication-title-link');
							$importantmiddle01ofreturnreadfileindir=stripos($returnreadfileindir,'href=',$importantbeginofreturnreadfileindir);
							$importantmiddle02ofreturnreadfileindir=stripos($returnreadfileindir,'"',$importantmiddle01ofreturnreadfileindir);
							$importantendofreturnreadfileindir=stripos($returnreadfileindir,'">',$importantmiddle02ofreturnreadfileindir);
							//We do not want " at the end of the link
							$importantendofreturnreadfileindir=$importantendofreturnreadfileindir-1;
							$returnreadfileindir=substr($returnreadfileindir,$importantmiddle02ofreturnreadfileindir+1,$importantendofreturnreadfileindir-$importantmiddle02ofreturnreadfileindir);
							//and update the field from column first_link_to_abstracttext from table "search_results" where the title's field in this row
							//has the same content as the content of $databaseentry
							$returnupdate=dbupdate($returndbconnect,"search_results","first_link_to_abstracttext='$returnreadfileindir'","id='$id'");
							if($returnreadfileindir==''){
								$returnvalue[0]=false;
								$returnvalue[1]='Error while updating column "first_link_to_abstracttext" in table "search_results: A link that should be updated to the column "first_link_to_abstracttext" was empty';
							}elseif($id==''){
								$returnvalue[0]=false;
								$returnvalue[1]='Error while updating column "first_link_to_abstracttext" in table "search_results: One or more than one search result ids were empty.';
							}elseif($returnupdate==false){
								$returnvalue[0]=false;
								$returnvalue[1]='Error while updating column "first_link_to_abstracttext" in table "search_results: MySQL update query failed.';
							}
						}
						$zaehler++;		
					}
					closedir($handle);
			
					$headline='<tr>
				<td style="width: 528px; height: 23px;" class="auto-style1">Completion of the search results for the topic analysis
				"' . $topicanalysisname . '" of the links to the abstracts with the help of www.researchgate.net
				 as a part of the search results if zero links to the abstracts have already been provided.</td>
				<td style="height: 23px">&nbsp;</td>
			</tr><tr>
				<td style="width: 528px; height: 23px;" class="auto-style1"><a href="javascript:close();">Close this window</a></td>
				<td style="height: 23px">&nbsp;</td>
			</tr>';
			
					if($returnvalue[0]==true){
						$returnstringforuser='	<tr>
							<td style="width: 743px; height: 23px;">Completion was
							successful. Thank you.</td>
							<td>&nbsp;</td>
						</tr>';
					}else{
						$returnstringforuser='<tr>
							<td style="width: 743px; height: 23px;">' . $returnvalue[1] . '</td>
							<td>&nbsp;</td>
						</tr>';
					}
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
						<td style="width: 743px; height: 23px;">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
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
			</table>
	</body>
			</html>';						
			}
		}else{
			echo 'No search result identifier provided for the completion of the links to the abstracts with the help of www.researchgate.net as a part of the search results if zero links to the abstracts have already been provided. <a href="javascript:close();">Close this window</a>';
		}
		dbdisconnect($returndbconnect);	
		}else{
			echo 'No topic analysis provided for the completion of the links to the abstracts with the help of www.researchgate.net as a part of the search results if zero links to the abstracts have already been provided. <a href="javascript:close();">Close this window</a>';
		}
?>