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
		in column "first_link_to_pdffulltext"*/
		
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
			$forwgetinputfile='';
			$zaehler3=0;
			//SELECT the filled first_link_to_pdffulltexts for each searchstringid of this topicanalysisid.	
			for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND first_link_to_pdffulltext<>''","id");			
				
				$dbnumrowsvar2=dbnumrows($returndbselect);
							
				if($dbnumrowsvar2>0){
					while(list($id,$id_search_strings,$exclude,$authors,$title,$conference,$year,$first_link_to_abstracttext,$abstracttext,$abstracttext_for_lda,$first_link_to_pdffulltext) = mysqli_fetch_row($returndbselect)){							
						/*We fetch the ids where we fetched filled first_link_to_pdffulltexts. Then it is easier to update
						the field in table search_results' colum 'pdffulltext' in table "search_results".*/
						$idforcomparisonwithpathtopdffulltext[$zaehler3]=$id; 
						$zaehler3++;
					}
				}
			}
			mysqli_free_result($returndbselect);
			$forwgetinputfile='';
			//We must select the row where we have a link to a fulltextpdf in column first_link_to_pdffulltext 							
			for($zaehler3=0;$zaehler3<sizeof($idforcomparisonwithpathtopdffulltext);$zaehler3++){
				$returndbselect=dbselect($returndbconnect,"search_results", "id='$idforcomparisonwithpathtopdffulltext[$zaehler3]' AND path_to_pdffulltext=''","id");							
				if(dbnumrows($returndbselect)==1){
					//We found one row where path_to_pdffulltext has to be updated because we have a related value in column first_link_to_pdffulltext
					//fetch the corresponding value of first_link_to_pdffulltext and write it in $forwgetinputfile
					$returndbfetcharray=dbfetcharray($returndbselect);
					$rememberedid=dbfetchfield($returndbfetcharray,'id');
					$rememberedfirstlink=dbfetchfield($returndbfetcharray,'first_link_to_pdffulltext');
					
    				//We need to have the filename from database without the http-path to http-directory.
					$fltpfc_lev=explode("/",$rememberedfirstlink);
					$sizeof_fltpfc_lev=sizeof($fltpfc_lev)-1;
					//This is the filename:
					$first_link_to_pdffulltext_for_comparsionwithfilename=$fltpfc_lev[$sizeof_fltpfc_lev];
			
					//Do we already have this pdffile in pathtopdffulltexts()?
					$dirname=pathtopdffulltextsall($topicanalysisname);
					if(is_dir($dirname)==false){
						mkdir($dirname);
					}
					$file_not_exists=true;
					if(file_exists($dirname . $first_link_to_pdffulltext_for_comparsionwithfilename)){
						$file_not_exists=false;
					}	
					
					//If $file_not_exists=false, we do not have to download this file with the help of wget
					$updatestring=$dirname . $first_link_to_pdffulltext_for_comparsionwithfilename;
					//Otherwise we should.
					if($file_not_exists){
						//fetch the pdffile linked by $rememberedfirstlink with wget.exe
						$dirname2=pathtopdffulltextsone($topicanalysisname);
						if(is_dir($dirname2)==false){
							mkdir($dirname2);
						}
						execwget($forwgetinputfile,false,$dirname2); 
						if(file_exists($dirname2 . "wgetinput.txt")){
							unlink($dirname2 . "wgetinput.txt");
						}
			
						if(file_exists($dirname2 . "index.html")){
							unlink($dirname2 . "index.html");
						}
				
						$handle=opendir($dirname2);
						while(($file = readdir($handle)) !== false) {
							if($file!="." && $file!=".."){
								copy($dirname2 . $file,$dirname);
							}
						}
						closedir($handle);
						//delete_files_in_dir($dirname2);
						$updatestring=$dirname . $file;
					}
					$returndbupdate=dbupdate($returndbconnect,"search_results","path_to_pdfstring='$updatestring'","id='$rememberedid'");
				}
			}

	
			$returnvalue='Completion was successful for id=' . $rememberedid . '. Thank you.';
			$returnupdate=dbupdate($returndbconnect,"search_results","path_to_pdffulltext='$updatestring'","id='$rememberedid'");
			if($returnupdate==false){
				$returnvalue='Completion was not successful. MySQL-Update-Query failed for id=' . $rememberedid .'.';

			}

			if(dbnumrows($returndbselect)==0){
				$returnvalue='Update finished. Close this window with the help of the link above.';
			}
				$headline='<tr>
				<td style="width: 528px; height: 23px;" class="auto-style1">Completion of the paths for the pdffulltexts for
				 the topic analysis "' . $topicanalysisname . '" if more than zero pdffulltexts but less than all pdffulltexts
				 have already been provided related to the provided first_links_to_pdffulltexts.</td>
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
			</table></body>
			</html>';		
		dbdisconnect($returndbconnect);	
		}else{
			echo 'No topic analysis provided. <a href="javascript:close();">Close this window</a>';
		}
	}
?>