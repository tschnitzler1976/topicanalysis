<?php
/*   This is the script for handling the first_links_to_abstracts in case the searchresultid<>0 (update one first_links_to_abstracts)*/
	
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
			/*If searchresultid is 0 then we know that we have to fill any field in column "first_link_to_abstracts" for table
			"search_results". If searchresultid is anything else then 0 then we know that we have to fill one field in column "first_link_to_abstracts"
			for table "search_results". Let's find the value out.*/				
			$searchresultid=htmlstringtostring($_GET["searchresultid"]);
			if($searchresultid>0){
				/*We complete one field in table "search_strings" for column "first_links_for_abstracttexts"*/
				$returndbselect=dbselect($returndbconnect,"search_results", "id='$searchresultid'","id");			
				$dbnumrowsvar=dbnumrows($returndbselect);
				if($dbnumrowsvar>0){
					/*We are connected to the internet and we do not want to use local saved files but we want new files from the internet again.
					That means: Fetch anything with wget.exe in many files and update anything at column abstracttext of table "search_results"*/
					
					$dbfetcharray=dbfetcharray($returndbselect);
					//We fetch the value from the field in column first_link_to_abstracttext. 
					$first_link_to_abstracttext=dbfetchfield($dbfetcharray,"first_link_to_abstracttext");
					$searchstringforresult="https://www.researchgate.net/" . $first_link_to_abstracttext;
					
					$dirname=pathtosearchresulttwo($topicanalysisname) . 'abstracttext/one/';
					if(is_dir($dirname)==false){
						mkdir($dirname);
					}
					if(wgetfromlocalorfrominternet_abstracttext_one()!=1){
						//We concatenate the search string for the search engine with the extracted first_link_to_abstracttext.
						//We write the concatenated string in a variable called $searchstringforresult.
						//$searchstringforresult can be used for writing into the inputfile for wget.
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

			        //select the downloaded researchgate.net-file with the link to the abstract
			        $handle=opendir($dirname);
			        $zaehler=0;
					$returnreadfileindir='';
					while ($file = readdir ($handle)) {
						if ($file!="." && $file!=".."){
							$returnreadfileindir=htmlstringtostring(file_get_contents($dirname . $file));
							$zaehler++;
						}
					}
					closedir($handle);
					//Less files than titles in table "search_results". That means we probably have a problem
					//with www.researchgate.net. We have to change the public IP-address of the local computer
					//in order to fix the problem.
					if($zaehler<1){
						$returnvalue[0]=false;
						$returnvalue[1]='Error while updating column "abstracttext" in table "search_results: Not enough information from www.researchgate.net for updating all rows in "abstracttext". Possibly no valid link to abstract or you have to change the public IP-address of this computer. The abstracttext for the abstract with the search_result_id=' . $searchresultid . ' is updated to "No abstract available"';
						$returnreadfileindir='No abstract available';
						$returnreadfileindir=base64_encode($returnreadfileindir);
						$returnupdate=dbupdate($returndbconnect,"search_results","abstracttext='$returnreadfileindir'","id='$searchresultid'");		
					}
					
					if($returnvalue[0]==true){			
						/*We now have the filename for the scientific paper's new content in field of column
						"first_link_to_abstracttext":*/					
						if(isset($returnreadfileindir)){
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
									}else{
										//in this case we have no abstracts
										$returnreadfileindir='No abstract available from www.researchgate.net.';									}
								}else{
									//here are the abstract at the "normal position:
									$importantbeginofreturnreadfileindir0102=stripos($returnreadfileindir,'"abstract"',$importantbeginofreturnreadfileindir01);
									$importantbeginofreturnreadfileindir0103=stripos($returnreadfileindir,':',$importantbeginofreturnreadfileindir0102);
									$beginning=$importantbeginofreturnreadfileindir0103+2;
									$end=stripos($returnreadfileindir,'","publicationHeader',$beginning);
									$returnreadfileindir=substr($returnreadfileindir,$beginning,$end-$beginning);								
								}
							}
							if($searchresultid==''){
								$returnvalue[0]=false;
								$returnvalue[1]=$returnvalue[1] . 'Error while updating column "abstracttext" in table "search_results:</br> One id was empty.</br>';
							}
							if($returnvalue[0]!=false){
								$returnreadfileindir=htmlspecialchars_decode($returnreadfileindir);
								$returnreadfileindir=htmlstringtostring($returnreadfileindir);								 
								$returnreadfileindir=base64_encode($returnreadfileindir);
								$returnupdate=dbupdate($returndbconnect,"search_results","abstracttext='$returnreadfileindir'","id='$searchresultid'");
								
								if($returnupdate==false){
									$returnvalue[0]=false;
									$returnvalue[1]=$returnvalue[1] . 'Error while updating column "abstracttext" in table "search_results:</br>MySQL update query failed.</br>';
								}
							}
						}
					}
					
					$headline='<tr>
				<td style="width: 528px; height: 23px;" class="auto-style1">Completion of the search results for the topic analysis
				"' . $topicanalysisname . '" of the links to the abstracts with the help of www.researchgate.net
				 as a part of the search results if one link to the abstracts has to be provided.</td>
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
			</table></body>
			</html>';						
			}
		}else{
			echo 'No search result identifier provided for the completion of the abstracts with the help of www.researchgate.net as a part of the search results if zero links to the abstracts have already been provided. <a href="javascript:close();">Close this window</a>';
		}
		dbdisconnect($returndbconnect);	
		}else{
			echo 'No topic analysis provided for the completion of the abstracts with the help of www.researchgate.net as a part of the search results if zero links to the abstracts have already been provided. <a href="javascript:close();">Close this window</a>';
		}
?>