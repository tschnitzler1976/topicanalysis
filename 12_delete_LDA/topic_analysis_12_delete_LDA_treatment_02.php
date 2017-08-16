<?php
	set_include_path("../00_general");
	include_once("php_functions.php");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	
	if(isset($_POST["select_existing_lda"])){
		$returndbconnect=dbconnect();
		$returndeletequeries=1;
		$ldaid=htmlstringtostring($_POST["select_existing_lda"]);
				
		//Delete LDA of this lda_id
		$returndbselect=dbselect($returndbconnect,"lda","id='$ldaid'","id");			
		$returndbnumrows=dbnumrows($returndbselect);
		if($returndbnumrows>0){
			$returndbfetcharray=dbfetcharray($returndbselect);
			$ldaname=dbfetchfield($returndbfetcharray,'name');
			$topicanalysisid=dbfetchfield($returndbfetcharray,'id_topic_analysis');
			$returndbselect=dbselect($returndbconnect,"topic_analysis","id='$topicanalysisid'","id");
			$returndbfetcharray=dbfetcharray($returndbselect);
			$topicanalysisname=dbfetchfield($returndbfetcharray,'name');
			
			//Delete the folder for this lda 
			deleteldainputexecuteoutputfolder(pathtolda() . $topicanalysisname . '/' . $ldaname);
			
			$returndbdelete=dbdelete($returndbconnect,"lda_id_search_results","id_lda='$ldaid'");			
			if($returndbdelete==0){
				echo "DELETE-Query for the table 'lda_id_search_results' went wrong";
				$returndeletequeries=0;
			}

			$returndbdelete=dbdelete($returndbconnect,"lda","id='$ldaid'");			
			if($returndbdelete==0){
				echo "DELETE-Query for the table 'lda' went wrong";
				$returndeletequeries=0;
			}
		}
		
		if($returndeletequeries==1){//successfully deleted anything
			echo 'Anything belonging to this lda is successfully deleted.<br> Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.';
		}else{
			echo 'A mistake happened while deleting anything belonging to this lda.<br> Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.';
		}
		dbdisconnect($returndbconnect);
	}else{
		echo 'Please go to the <a href="../topic_analysis_00_menu.php">menu</a>.';
	}

?>