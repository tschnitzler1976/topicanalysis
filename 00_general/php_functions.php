<?php
include_once("mysql_db_connection.php");
include_once("mysql_db_queries.php");
include_once("mysql_db_functions.php");
	
	/*We must have the difference value of columns that are in table search results and actual completing procedures for
	attributes of table search_results which each are in listed in a switch case selection in
	04_fill_and_complete\handlers\dummy_search_engine\topic_analysis_04_fill_and_complete_treatment_02.php, i.e. if
	a column is added to table search_results and the amount for columns for columns to complete in table search_results
	stays the same in 04_fill_and_complete\handlers\dummy_search_engine\topic_analysis_04_fill_and_complete_treatment_02.php
	then we have to increment the returnvalue of this function, otherwise we decrement. At 2017-09-30 we have 18 columns
	in table search_results. We have 10 cases for completing scientific articles' attributes in table search_results.
	Thus we have to return 8 columns that are not interesting for the completing procedures in line of
	topic_analysis_04_fill_and_complete_search_results.php.
	Therefore we have function "notimportantcolumnstablesearchresults" for the columns that are excluded in the above
	mentioned sense at the beginning of table search_results and we have function
	"notimportantcolumnstablesearchresultsattheend" 
	*/
	function notimportantcolumnstablesearchresults(){
		return 3;
	}

	function notimportantcolumnstablesearchresultsattheend(){
		return 3;
	}
	
	
	function renamefile($oldfile,$newfile){
	    if (!rename($oldfile,$newfile)) {
	        if (copy($oldfile,$newfile)) {
	            unlink($oldfile);
	            return TRUE;
	        }
	        return FALSE;
	    }
	    return $newfile;
	}
	
	function pathtosearchresults($topicanalysisname){
		$pathtohtdocs='C:/xampp/htdocs/';
		$returnvalue=$pathtohtdocs . 'topic_analysis/04_fill_and_complete/search_results/' . $topicanalysisname . '/';
		return $returnvalue;		
	}
	
	function pathtosearchresultone($topicanalysisname){
		$pathtohtdocs='C:/xampp/htdocs/';
		$returnvalue=$pathtohtdocs . 'topic_analysis/04_fill_and_complete/search_results/' . $topicanalysisname . '/search_results_01/';
		return $returnvalue;
	}
	
	function pathtosearchresulttwo($topicanalysisname){
		$pathtohtdocs='C:/xampp/htdocs/';
		$returnvalue=$pathtohtdocs . 'topic_analysis/04_fill_and_complete/search_results/' . $topicanalysisname . '/search_results_02/';
		return $returnvalue;
	}

	function pathtopdffulltexts($topicanalysisname){
		$pathtohtdocs='C:/xampp/htdocs/';
		$returnvalue=$pathtohtdocs . 'topic_analysis/04_fill_and_complete/search_results/' . $topicanalysisname . '/pdffulltext/';
		return $returnvalue;
	}

	function pathtopdffulltextsall($topicanalysisname){
		$pathtohtdocs='C:/xampp/htdocs/';
		$returnvalue=$pathtohtdocs . 'topic_analysis/04_fill_and_complete/search_results/' . $topicanalysisname . '/pdffulltext/all/';
		return $returnvalue;
	}

	function pathtopdffulltextsalltemp($topicanalysisname){
		$pathtohtdocs='C:/xampp/htdocs/';
		$returnvalue=$pathtohtdocs . 'topic_analysis/04_fill_and_complete/search_results/' . $topicanalysisname . '/pdffulltext/all/temp/';
		return $returnvalue;
	}

	function pathtopdffulltextsone($topicanalysisname){
		$pathtohtdocs='C:/xampp/htdocs/';
		$returnvalue=$pathtohtdocs . 'topic_analysis/04_fill_and_complete/search_results/' . $topicanalysisname . '/pdffulltext/one/';
		return $returnvalue;
	}

	function wgetfromlocalorfrominternet(){
		//data from local is 1 and data from the internet is 0.
		$local=1;
		return $local;
	}

	function wgetfromlocalorfrominternet_abstracttext_one(){
		//data from local is 1 and data from the internet is 0.
		$local=1;
		return $local;
	}
	
	function pathtowget(){
		$returnvalue="C:/xampp/htdocs/topic_analysis/04_fill_and_complete/tools/wget.exe";
		return $returnvalue;
	}
	
	function execwget($searchstringforresult,$onefile,$dirname){
		chdir($dirname);
		delete_files_in_dir($dirname);
		$fp = fopen("wgetinput.txt", "w");
		fputs ($fp, "$searchstringforresult");
		fclose ($fp);
		//run wget:
		$pathwget=pathtowget();
		if($onefile){
			$wgetstring=$pathwget . " -i " . $dirname . "wgetinput.txt -O output.txt --no-check-certificate --wait 2";
		}else{
			$wgetstring=$pathwget . " -i " . $dirname . "wgetinput.txt --no-check-certificate --wait 2";
		}			
		exec($wgetstring);
	}

	function pdftotext_executable(){
		return "C:/xampp/htdocs/topic_analysis/04_fill_and_complete/tools/pdftotext.exe";
	}

	function pathtolda(){
		$pathtohtdocs='C:/xampp/htdocs/';
		$returnvalue=$pathtohtdocs . 'topic_analysis/13_execute_LDA/lda_input_execute_output/';
		return $returnvalue;
	}
	
	function deleteldainputexecuteoutputfolder($dirname){
		rrmdir($dirname);
	}
		
	function r_script_executable(){
		return "C:/Users/thomas/Programme/R-3.4.1/bin/i386/Rscript.exe";
	}
	
	function stringtohtmlstring($string){
		$htmlstring=htmlentities($string);
		return $htmlstring;
	}

	function htmlstringtostring($htmlstring){
		$string=html_entity_decode($htmlstring);
		return $string;
	}

	function explodestring($explodewhere,$explodewhat){
		$explodestringresult=explode($explodewhere,$explodewhat);
		return $explodestringresult;
	}
	
	function sizeofarray($array){
		return sizeof($array);
	}
	
	function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") 
						rrmdir($dir."/".$object); 
					else unlink   ($dir."/".$object);
				}
			}
			reset($objects);
			rmdir($dir);
		}
	 }

	function delete_files_in_dir($src) { 
    	$dir = opendir($src);
    	while(false !== ( $file = readdir($dir)) ) { 
        	if (( $file != '.' ) && ( $file != '..' )) { 
            	if ( is_dir($src . $file) ) { 
                	delete_dir($src . $file); 
            	} 
            	else {
            		if(is_writeable($src . $file)){
                 		unlink($src . $file);
                	} 
            	} 
        	} 
    	} 
    	closedir($dir); 
    }
    
    function delete_file_in_dir($srcfolder,$filename) { 
    	$dir = opendir($srcfolder);
    	if(file_exists($srcfolder . $filename)){
	    	unlink($srcfolder . $filename); 
	    }
    	closedir($dir); 
    } 
    
    function read_file_in_dir($srcfolder,$filename) { 
    	$dir = opendir($srcfolder);
    	$returnreadfileindir="";
	    if(file_exists($srcfolder . $filename)){
		    $returnreadfileindir=file_get_contents($srcfolder . $filename); 
		}
    	closedir($dir); 
    	return $returnreadfileindir;
    } 
    
    function read_files_in_dir($srcfolder) { 
        $handle=opendir($srcfolder);
        $zaehler=0;
		while ($file = readdir ($handle)) {
			$zaehler++;
		}
		$returncontentofanyfile[$zaehler]="";
		for($zaehler2=0;$zaehler2<$zaehler;$zaehler2++){
			$returncontentofanyfile[$zaehler2]=file_get_contents($srcfolder . $file);
			$zaehler2++;
		}
		closedir($handle);
		return $returncontentofanyfile;
	} 
	
	function excludexmltags($wholestring){	
		$strposition1=stripos($wholestring,">",0);
		//if there is no xml to exclude then we return the same string again.
		if($strposition1!=false){		
			if($strposition1>0){
				$strposition2=stripos($wholestring,"<",$strposition1+1);
				$returnstring=substr($wholestring,$strposition1+1,$strposition2-$strposition1-1);
			}else{
				$returnstring=$wholestring;
			}		
		}else{
			$returnstring=$wholestring;
		}	
		return $returnstring;
	}
			
	function excludeanyxmltag($wholestring){
		$wholestringarray=explode(">",$wholestring);
		$zaehler=0;
		$zaehler2=0;
		$strposopentag=0;
		$excludedstringasstring='';
		if(sizeof($wholestringarray>0)){
			for($zaehler=0;$zaehler<sizeof($wholestringarray);$zaehler++){
				$strposopentag=stripos($wholestringarray[$zaehler],"<");
				if($strposopentag>0){
					//We have a value before "<". Therefore we have to extract this value from anything that is behind "<"
					$excludedstringarray=explode("<",$wholestringarray[$zaehler]);
					$excludedstring[$zaehler2]=$excludedstringarray[0];
					if($excludedstringasstring==''){
						$excludedstringasstring=$excludedstring[$zaehler2];
					}else{
						$excludedstringasstring=$excludedstringasstring . ", " . $excludedstring[$zaehler2];
					}
				}
			}
		}
		return $excludedstringasstring;
	}
			
	function checkequalitybetweensearchstringandexistingfunction($searchstring){
		$returnvalue=0;
		
		if(stripos($searchstring,'dblp.org/search/publ/api')!=false){
			$returnvalue=1;
		}
		return $returnvalue;
	}
	
	function checkequalitybetweensearchstringandfilename($dirname,$searchstringname){
		$handle=opendir($dirname);
        $levenshteinvalue="";
        $zaehler=0;
         while(($file = readdir($handle)) !== false) {
        	$levenshteinvalue=levenshtein($searchstringname,$file);
			if($zaehler==0){
				$valuesmallest=$levenshteinvalue;
				$filename=$file;
			}else{
				if($levenshteinvalue<$valuesmallest){
					$valuesmallest=$levenshteinvalue;
					$filename=$file;
				}
			}			
			$zaehler++;
		}
		//We do not return a point which is equal to '.'. Instead of a point we return NULL.
		if(ord($filename)==46){
			$filename='';
		}
		return $filename;
	}
	
	function eliminatefakerowsintablesearchresults(){

		$returndbconnect=dbconnect();	
		$returndbselect=dbselect($returndbconnect,"search_results","year='0000'","id");
		$dbnum=dbnumrows($returndbselect);
		if($dbnum>0){
			$returndbfetcharray=dbfetcharray($returndbselect);
			$id=dbfetchfield($returndbfetcharray,'id');
			$id_search_strings=dbfetchfield($returndbfetcharray,'id_search_strings');
			$returndbdelete=dbdelete($returndbconnect,"search_results","id='$id'");
			$returndbdelete=dbdelete($returndbconnect,"search_strings","id='$id_search_strings'");
		}
		mysqli_free_result($returndbselect);
	}
	
	function createsnapshotoftablesearchresults($topicanalysisid){
		$returndbconnect=dbconnect();
		$htmlcodeforsnapshot="";
		$htmlcodeforsnapshot='<table border="1" style="width: 100%"><tr><td>Number</td>';			
		/* The columns of table "search_results".
		Output of the up-to-date columns of table "search_results" in $finfoarray*/
		$returndbselect=dbselect($returndbconnect,"search_results","1","id");
		$finfo = mysqli_fetch_fields($returndbselect);
		foreach ($finfo as $val) {
			$htmlcodeforsnapshot=$htmlcodeforsnapshot . '<td>' . $val->name . '</td>';		
    	}
		mysqli_free_result($returndbselect);
		$htmlcodeforsnapshot=$htmlcodeforsnapshot . '</tr>';
		$returndbselect=dbselect($returndbconnect,"search_strings","id_topic_analysis=$topicanalysisid","id");
		$zaehler=1;
		while(list($idsearchstring) = mysqli_fetch_row($returndbselect)){
			$idsearchstring=$idsearchstring;
			$returndbselect2=dbselect($returndbconnect,"search_results","id_search_strings='$idsearchstring'","id");			
			while(list($id,$id_search_strings,$exclude,$authors,$title,$conference,$year,$first_link_to_abstracttext,$abstracttext,$abstracttext_for_lda,$first_link_to_pdffulltext,$path_to_pdffulltext,$pdffulltext_as_text,$pdffulltext_as_text_extracted,$pdffulltext_for_lda,$exclusion_already_done,$preprocessing_abstracttext_already_done,$preprocessing_pdffulltext_already_done) = mysqli_fetch_row($returndbselect2)){					
				$htmlcodeforsnapshot=$htmlcodeforsnapshot . '<tr><td>' . $zaehler . '</td><td>' . $id . '</td><td>' . $id_search_strings . '</td><td>' . $exclude . '</td><td>' . $authors . '</td><td>' . $title . '</td><td>' . $conference . '</td><td>' . $year . '</td><td>' . $first_link_to_abstracttext . '</td><td><textarea style="width: 657px; height: 231px">' . base64_decode($abstracttext)  . '</textarea></td><td><textarea style="width: 657px; height: 231px">' . base64_decode($abstracttext_for_lda) . '</textarea></td><td>' . $first_link_to_pdffulltext . '</td><td>' . $path_to_pdffulltext . '</td><td>' . base64_decode($pdffulltext_as_text) . '</td><td><textarea style="width: 657px; height: 231px">' . base64_decode($pdffulltext_as_text_extracted)  . '</textarea></td><td><textarea style="width: 657px; height: 231px">' . base64_decode($pdffulltext_for_lda)  . '</textarea></td><td>' . $exclusion_already_done . '</td><td>' . $preprocessing_abstracttext_already_done . '</td><td>' . $preprocessing_pdffulltext_already_done . '</td></tr>';
				$zaehler++;
			}
			mysqli_free_result($returndbselect2);
		}
		mysqli_free_result($returndbselect);
		$htmlcodeforsnapshot=$htmlcodeforsnapshot . '</table>';
		dbdisconnect($returndbconnect);
		return $htmlcodeforsnapshot;
	}
	
	function createsnapshotoftablesearchresultsforexclusion($topicanalysisid,$htmlcodeforsnapshot){
		$returndbconnect=dbconnect();
		$zaehler=$htmlcodeforsnapshot[0];
		$htmlcodeforsnapshot[1]="";
		$htmlcodeforsnapshot[1]='<table border="1" style="width: 100%"><tr><td>Number</td>';			
		/* The columns of table "search_results".
		Output of the up-to-date columns of table "search_results" in $finfoarray*/
		$returndbselect=dbselect($returndbconnect,"search_results","1","id");
		$finfo = mysqli_fetch_fields($returndbselect);
		foreach ($finfo as $val) {
			$htmlcodeforsnapshot[1]=$htmlcodeforsnapshot[1] . '<td>' . $val->name . '</td>';		
    	}
		mysqli_free_result($returndbselect);
		$htmlcodeforsnapshot[1]=$htmlcodeforsnapshot[1] . '</tr>';
		$returndbselect=dbselect($returndbconnect,"search_strings","id_topic_analysis=$topicanalysisid","id");
		$htmloptionvalueexclude='';
		while(list($idsearchstring) = mysqli_fetch_row($returndbselect)){
			$idsearchstring=$idsearchstring;
			$returndbselect2=dbselect($returndbconnect,"search_results","id_search_strings='$idsearchstring'","id");	
			while(list($id,$id_search_strings,$exclude,$authors,$title,$conference,$year,$first_link_to_abstracttext,$abstracttext,$abstracttext_for_lda,$first_link_to_pdffulltext,$path_to_pdffulltext,$pdffulltext_as_text,$pdffulltext_as_text_extracted,$pdffulltext_for_lda,$exclusion_already_done,$preprocessing_abstracttext_already_done,$preprocessing_pdffulltext_already_done) = mysqli_fetch_row($returndbselect2)){							
				if($exclude==0){
					$htmloptionvalueexclude='<input name="exclusion_id_' . $id . '" value="' . $id . '" type="checkbox"/>exclude';
				}else{
					$htmloptionvalueexclude='<input name="exclusion_id_' . $id . '" value="' . $id . '" type="checkbox"checked="checked"/>exclude';
				}		
				$htmlcodeforsnapshot[1]=$htmlcodeforsnapshot[1] . '<tr><td>' . $zaehler . '</td><td>' . $id . '</td><td>' . $id_search_strings . '</td><td>' . $htmloptionvalueexclude . '</td><td>' . $authors . '</td><td>' . $title . '</td><td>' . $conference . '</td><td>' . $year . '</td><td>' . $first_link_to_abstracttext . '</td><td><textarea style="width: 657px; height: 231px">' . base64_decode($abstracttext)  . '</textarea></td><td><textarea style="width: 657px; height: 231px">' . base64_decode($abstracttext_for_lda) . '</textarea></td><td>' . $first_link_to_pdffulltext . '</td><td>' . $path_to_pdffulltext . '</td><td>' . base64_decode($pdffulltext_as_text) . '</td><td><textarea style="width: 657px; height: 231px">' . base64_decode($pdffulltext_as_text_extracted)  . '</textarea></td><td><textarea style="width: 657px; height: 231px">' . base64_decode($pdffulltext_for_lda)  . '</textarea></td><td>' . $exclusion_already_done . '</td><td>' . $preprocessing_abstracttext_already_done . '</td><td>' . $preprocessing_pdffulltext_already_done . '</td></tr>';	
				$zaehler++;
			}
			mysqli_free_result($returndbselect2);
		}
		mysqli_free_result($returndbselect);
		$htmlcodeforsnapshot[1]=$htmlcodeforsnapshot[1] . '</table>';
		dbdisconnect($returndbconnect);
		$htmlcodeforsnapshot[0]=$zaehler;
		return $htmlcodeforsnapshot;
	}
	
		function createsnapshotoftablesearchresultsforuploadingpdffulltexts($topicanalysisid,$htmlcodeforsnapshot){
		$returndbconnect=dbconnect();
		$zaehler=$htmlcodeforsnapshot[0];
		$htmlcodeforsnapshot[1]="";
		$htmlcodeforsnapshot[1]='<table border="1" style="width: 100%"><tr><td>Number</td>';			
		/* The columns of table "search_results".
		Output of the up-to-date columns of table "search_results" in $finfoarray*/
		$returndbselect=dbselect($returndbconnect,"search_results","1","id");
		$finfo = mysqli_fetch_fields($returndbselect);
		foreach ($finfo as $val) {
			$htmlcodeforsnapshot[1]=$htmlcodeforsnapshot[1] . '<td>' . $val->name . '</td>';		
    	}
		mysqli_free_result($returndbselect);
		$htmlcodeforsnapshot[1]=$htmlcodeforsnapshot[1] . '</tr>';
		$returndbselect=dbselect($returndbconnect,"search_strings","id_topic_analysis=$topicanalysisid","id");
		$htmloptionvalueexclude='';
		$submitbutton='';
		while(list($idsearchstring) = mysqli_fetch_row($returndbselect)){
			$idsearchstring=$idsearchstring;
			//We just want the rows that have an empty field for "path_to_pdffulltext"
			$returndbselect2=dbselect($returndbconnect,"search_results","id_search_strings='$idsearchstring'","id");	
			while(list($id,$id_search_strings,$exclude,$authors,$title,$conference,$year,$first_link_to_abstracttext,$abstracttext,$abstracttext_for_lda,$first_link_to_pdffulltext,$path_to_pdffulltext,$pdffulltext_as_text,$pdffulltext_as_text_extracted,$pdffulltext_for_lda,$exclusion_already_done,$preprocessing_abstracttext_already_done,$preprocessing_pdffulltext_already_done) = mysqli_fetch_row($returndbselect2)){							
				if(ord($path_to_pdffulltext)==0){
					$submitbutton='<input name="file_' .$id . '" type="text" style="width: 397px; height: 25px"/></td>';
				}else{
					$submitbutton=stringtohtmlstring($path_to_pdffulltext);
				}
				$htmlcodeforsnapshot[1]=$htmlcodeforsnapshot[1] . '<tr><td>' . $zaehler . '</td><td>' . $id . '</td><td>' . $id_search_strings . '</td><td>' . $exclude . '</td><td>' . $authors . '</td><td>' . $title . '</td><td>' . $conference . '</td><td>' . $year . '</td><td>' . $first_link_to_abstracttext . '</td><td><textarea style="width: 657px; height: 231px">' . base64_decode($abstracttext)  . '</textarea></td><td><textarea style="width: 657px; height: 231px">' . base64_decode($abstracttext_for_lda) . '</textarea></td><td>' . $first_link_to_pdffulltext . '</td><td>' . $submitbutton . '</td><td>' . base64_decode($pdffulltext_as_text) . '</td><td><textarea style="width: 657px; height: 231px">' . base64_decode($pdffulltext_as_text_extracted)  . '</textarea></td><td><textarea style="width: 657px; height: 231px">' . base64_decode($pdffulltext_for_lda)  . '</textarea></td><td>' . $exclusion_already_done . '</td><td>' . $preprocessing_abstracttext_already_done . '</td><td>' . $preprocessing_pdffulltext_already_done . '</td></tr>';	
				$zaehler++;
			}
			mysqli_free_result($returndbselect2);
		}
		mysqli_free_result($returndbselect);
		$htmlcodeforsnapshot[1]=$htmlcodeforsnapshot[1] . '</table>';
		dbdisconnect($returndbconnect);
		$htmlcodeforsnapshot[0]=$zaehler;
		return $htmlcodeforsnapshot;
	}

		
	function createsnapshotoftablesearchresultsafteraddingmodifyinglda($idsearchresults){
		$returndbconnect=dbconnect();
		$htmlcodeforsnapshot="";
		$htmlcodeforsnapshot='<table border="1" style="width: 100%"><tr><td>Number</td>';			
		/* The columns of table "search_results".
		Output of the up-to-date columns of table "search_results" in $finfoarray*/
		$returndbselect=dbselect($returndbconnect,"search_results","1","id");
		$finfo = mysqli_fetch_fields($returndbselect);
		foreach ($finfo as $val) {
			$htmlcodeforsnapshot=$htmlcodeforsnapshot . '<td>' . $val->name . '</td>';		
    	}
		mysqli_free_result($returndbselect);
		$htmlcodeforsnapshot=$htmlcodeforsnapshot . '</tr>';
		$zaehler2=1;
		for($zaehler=0;$zaehler<sizeof($idsearchresults);$zaehler++){
			$idsearchresult=$idsearchresults[$zaehler];		
			$returndbselect2=dbselect($returndbconnect,"search_results","id='$idsearchresult'", "id");			
			while(list($id,$id_search_strings,$exclude,$authors,$title,$conference,$year,$first_link_to_abstracttext,$abstracttext,$abstracttext_for_lda,$first_link_to_pdffulltext,$path_to_pdffulltext,$pdffulltext_as_text,$pdffulltext_as_text_extracted,$pdffulltext_for_lda,$exclusion_already_done,$preprocessing_abstracttext_already_done,$preprocessing_pdffulltext_already_done) = mysqli_fetch_row($returndbselect2)){					
				$htmlcodeforsnapshot=$htmlcodeforsnapshot . '<tr><td>' . $zaehler2 . '</td><td>' . $id . '</td><td>' . $id_search_strings . '</td><td>' . $exclude . '</td><td>' . $authors . '</td><td>' . $title . '</td><td>' . $conference . '</td><td>' . $year . '</td><td>' . $first_link_to_abstracttext . '</td><td><textarea style="width: 657px; height: 231px">' . base64_decode($abstracttext)  . '</textarea></td><td><textarea style="width: 657px; height: 231px">' . base64_decode($abstracttext_for_lda) . '</textarea></td><td>' . $first_link_to_pdffulltext . '</td><td>' . $path_to_pdffulltext . '</td><td>' . base64_decode($pdffulltext_as_text) . '</td><td><textarea style="width: 657px; height: 231px">' . base64_decode($pdffulltext_as_text_extracted)  . '</textarea></td><td><textarea style="width: 657px; height: 231px">' . base64_decode($pdffulltext_for_lda)  . '</textarea></td><td>' . $exclusion_already_done . '</td><td>' . $preprocessing_abstracttext_already_done . '</td><td>' . $preprocessing_pdffulltext_already_done . '</td></tr>';				
				$zaehler2++;
			}
			mysqli_free_result($returndbselect2);
		}
		$htmlcodeforsnapshot=$htmlcodeforsnapshot . '</table>';
		dbdisconnect($returndbconnect);
		return $htmlcodeforsnapshot;
	}
	
	
	function createexecuteableforr($topicanalysisname,$ldaname,$numberoftopics){
		$inputfolder=pathtolda() . $topicanalysisname . '/' . $ldaname . '/' . 'input/';
		$outputfolder=pathtolda() . $topicanalysisname . '/' . $ldaname . '/' . 'output/vis/';
		$executefolder=pathtolda() . $topicanalysisname . '/' . $ldaname . '/' . 'execute/';
		$stringtowriteintofile='';
		$rfile01='library(tm) ' . chr(13) . chr(10) . 'stop_words <- stopwords("SMART")' . chr(13) . chr(10);
		$rfile02='path<-"' . $inputfolder . '"' . chr(13) . chr(10);
		$rfile03=file_get_contents(pathtolda() . 'rfile_sourcecode/rfile03.R_part');
		$rfile04=chr(13) . chr(10) . '# MCMC and model tuning parameters:' . chr(13) . chr(10) . 'K <-' . $numberoftopics  . chr(13) . chr(10) .  'G <- 5000' . chr(13) . chr(10) . 'alpha <- 0.02' . chr(13) . chr(10) . 'eta <- 0.02' . chr(13) . chr(10);
		$rfile05=chr(13) . chr(10) . '# Fit the model:' . chr(13) . chr(10) . 'library(lda) ' . chr(13) . chr(10) . 'set.seed(357)' . chr(13) . chr(10);
		$rfile06='fit <- lda.collapsed.gibbs.sampler(documents = documents, K =' . $numberoftopics . ', vocab = vocab,' . chr(13) . chr(10);
		$rfile07=file_get_contents(pathtolda() . 'rfile_sourcecode/rfile07.R_part');
		$rfile08='serVis(json, out.dir = "' . $outputfolder . '", open.browser = FALSE)';
		$stringtowriteintofile=$rfile01 . $rfile02 . $rfile03 .$rfile04 . $rfile05 . $rfile06 . $rfile07 . $rfile08;
		file_put_contents($executefolder . 'lda.R',$stringtowriteintofile);
	}	
?>