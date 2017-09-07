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
		/*We complete one field of the column "pdffulltext_as_text" in table "search_strings" if the related field
		in column "path_to_pdffulltext" is filled */
		
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
			$zaehler3=0;
			//SELECT the filled path_to_pdffulltext for each searchstringid of this topicanalysisid.	
			for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
				$returndbselect=dbselect($returndbconnect,"search_results", "id_search_strings='$searchstringsids[$zaehler2]' AND path_to_pdffulltext<>''","id");			
				$dbnumrowsvar2=dbnumrows($returndbselect);
				if($dbnumrowsvar2>0){
					while(list($id) = mysqli_fetch_row($returndbselect)){							
						/*We fetch the id for the next update-operation*/
						$id_search_result=$id;
						$returndbselect2=dbselect($returndbconnect,"search_results", "id='$id_search_result' AND pdffulltext_as_text_extracted=''","id");			
						if(dbnumrows($returndbselect2)>0){
						
							/*We fetch the value from the field in column path_to_pdffulltext.*/
							$pathtopdffulltext=$path_to_pdffulltext_row;
							/*We execute pdftotxt in order to save pdftotext's output in column "pdffulltext_as_text"*/
							exec(pdftotext_executable() . ' ' . $pathtopdffulltext);
							/*We address the output of pdftotext*/
							$strlength=strlen($pathtopdffulltext);
							$pathtopdffulltextastext=substr($pathtopdffulltext,0,$strlength-3) . "txt";
							$contentsoftextfile=base64_encode(file_get_contents($pathtopdffulltextastext));
							$returnupdate=dbupdate($returndbconnect,"search_results","pdffulltext_as_text='$contentsoftextfile'","id='$id_search_result'");						
							if($returnupdate==0){
								$returnvalue='Completion was not successful. The MySQL-Update-Query failed.';
							}
							if(file_exists($pathtopdffulltextastext)){
								unlink($pathtopdffulltextastext);
							}
						}
					}
				}
			}
			mysqli_free_result($returndbselect);

			
			
			$headline='<tr>
				<td style="width: 528px; height: 23px;" class="auto-style1">Completion of the texts for the pdffulltexts for
				 the topic analysis "' . $topicanalysisname . '" if more than zero pdffulltexts_as_text have already been provided
				 related to the provided path_to_pdffulltexts.</td>
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