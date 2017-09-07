<?php
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
		/*We complete a field of the column "path_to_pdffulltext" in table "search_strings" if the related field
		in column "first_link_to_pdffulltext" is filled.*/
		
		$dbselectresult1=dbselect($returndbconnect,"search_strings","id_topic_analysis='$topicanalysisid'","id");
		$dbnumrowsvar1=dbnumrows($dbselectresult1);
		if($dbnumrowsvar1>0){
		//We have more than 0 searchstrings for this topic analysis. Thus continue.
			$zaehler=0;
			$returnvalue='Completion was successful. Thank you.';
			while(list($id) = mysqli_fetch_row($dbselectresult1)){							
				$searchstringsids[$zaehler]=$id;
				$zaehler++;
			}
			mysqli_free_result($dbselectresult1);
			$forwgetinputfile='';
			$zaehler3=0;
			//SELECT the filled first_link_to_pdffulltexts for each searchstringid of this topicanalysisid.	
			for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND first_link_to_pdffulltext<>''","id");			
				$dbnumrowsvar2=dbnumrows($returndbselect);
				if($dbnumrowsvar2>0){
					while(list($id,$id_search_strings,$exclude,$authors,$title,$conference,$year,$first_link_to_abstracttext,$abstracttext,$abstracttext_for_lda,$first_link_to_pdffulltext) = mysqli_fetch_row($returndbselect)){							
						/*We fetch the value from the field in column first_link_to_pdffulltext.*/
						$first_link_to_pdffulltext=$first_link_to_pdffulltext;
						//This is for the wget-inputfile:
						$forwgetinputfile=$forwgetinputfile . $first_link_to_pdffulltext . chr(10) . chr(13);
						/*This first_link_to_pdffulltext stored in an array is for finding the right pdf file in the internet input
						 for the particular "first_link_to_abstract" value that is bound to a particular search_result_id.*/
						//We need to have the filename from database without the http-path to http-directory.
						$fltpfc_lev=explode("/",$first_link_to_pdffulltext);
						$sizeof_fltpfc_lev=sizeof($fltpfc_lev)-1;
						$first_link_to_pdffulltext_for_comparsionwithfilename[$zaehler3]=$fltpfc_lev[$sizeof_fltpfc_lev];
						//echo $first_link_to_pdffulltext_for_comparsionwithfilename[$zaehler3] . "<br>";
						/*We also fetch the id where we fetched first_link_to_pdffulltext. Then it is easier to update
						the field in table search_results' colum 'pdffulltext' in table "search_results".*/
						$idforcomparsionwithfilename[$zaehler3]=$id; 
						$zaehler3++;
					}
				}
			}
			mysqli_free_result($returndbselect);
			$dirname=pathtopdffulltexts($topicanalysisname);
			if(is_dir($dirname)==false){
				mkdir($dirname);
			}
			
			$dirname=pathtopdffulltextsall($topicanalysisname);
			if(is_dir($dirname)==false){
				mkdir($dirname);
			}
			
			if((wgetfromlocalorfrominternet()!=1)){
				//We concatenate the search string for the search engine with the extracted first_link_to_abstracttext.
				//We write the concatenated string in a variable called $searchstringforresult.
				//$searchstringforresult can be used for writing into the inputfile for wget.
				execwget($forwgetinputfile,false,$dirname);
			}

			if(file_exists($dirname . "wgetinput.txt")){
				unlink($dirname . "wgetinput.txt");
			}
			
			if(file_exists($dirname . "index.html")){
				unlink($dirname . "index.html");
			}
			
			/*UPDATE each field in column "pdffulltext" in table "search_results" that fulfills the
			following criteria. The file name that because of levenshtein is the closest approach between first_link_to_pdffulltext in
			table "search_results" and the filename.*/
		
			/*Stringcomparison between filename and title of table search_results with the help levenshtein. 
			Only the smallest comparison-value of levenshtein is taken because these arrayvalues of filename
			and databaseentry address the same scientific article and therefore the file contains the
			link to this scientific paper's pdffulltext.	
			
			If there is a match between filename and database entry as described above the link to the pdffulltext
			is extracted from the filename's filecontent in order to update the field of table
			"search result's" column "pdffulltext".*/
			
			$handle=opendir($dirname);
	        $levenshteinvalue="";
	        $zaehler=0;
	        $testispartoffilename=0;
	        while(($file = readdir($handle)) !== false) {
			    $levenshteinvalue="";
				for($zaehler2=0;$zaehler2<sizeof($first_link_to_pdffulltext_for_comparsionwithfilename);$zaehler2++){
	        		$levenshteinvalue[$zaehler2]=levenshtein($first_link_to_pdffulltext_for_comparsionwithfilename[$zaehler2],$file);
					$levenshteinvalueafter=$levenshteinvalue[$zaehler2];
					if($zaehler2==0){
						$valuesmallest=$levenshteinvalueafter;
					}else{					
						if($levenshteinvalueafter<$valuesmallest){
							$valuesmallest=$levenshteinvalueafter;
							$databaseentry=$first_link_to_pdffulltext_for_comparsionwithfilename[$zaehler2];
							
							$id=$idforcomparsionwithfilename[$zaehler2];
							$filename=$file;
						}
					}				
				}												
				/*We now have the filename for the scientific paper's new content in field of column
				"first_link_to_pdffulltext":*/
				if($file!="." && $file!=".."){
					$localfilepath=$dirname . $filename;
					$returnupdate=dbupdate($returndbconnect,"search_results","path_to_pdffulltext='$localfilepath'","id='$id'");					
					if($returnupdate==false){
						$returnvalue='Completion was not successful. MySQL-Update-Query failed.';

					}
				}
			}
			
			closedir($handle);
		
			$headline='<tr>
				<td style="width: 528px; height: 23px;" class="auto-style1">Completion of the paths for the pdffulltexts for
				 the topic analysis "' . $topicanalysisname . '" if zero pdffulltexts have already been provided related to
				 the provided first_links_to_pdffulltexts.</td>
				<td style="height: 23px">&nbsp;</td>
			</tr><tr>
				<td style="width: 528px; height: 23px;" class="auto-style1"><a href="javascript:close();">Close this window</a></td>
				<td style="height: 23px">&nbsp;</td>
			</tr>';
			
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
						' . $returnvalue . '</td>
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
		dbdisconnect($returndbconnect);	
		}else{
			echo 'No topic analysis provided. <a href="javascript:close();">Close this window</a>';
		}
	}
?>