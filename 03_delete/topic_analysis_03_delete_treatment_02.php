<?php
	set_include_path("../00_general");
	include_once("php_functions.php");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	
	if(isset($_POST["select_existing_topic_analysis"])){
		$returndbconnect=dbconnect();
		$returndeletequeries=1;
		$topicanalysisid=htmlstringtostring($_POST["select_existing_topic_analysis"]);

		$returndbdelete=dbdelete($returndbconnect,"research_questions","id_topic_analysis='$topicanalysisid'");			
		if($returndbdelete==0){
			echo "DELETE-Query for the table 'research_questions' went wrong";
			$returndeletequeries=0;
		}
	
		$returndbselect=dbselect($returndbconnect,"search_strings","id_topic_analysis='$topicanalysisid'","id");			
		$returndbnumrows=dbnumrows($returndbselect);
		if($returndbnumrows>0){
			$zaehler2=0;
			while(list($id) = mysqli_fetch_row($returndbselect)){
				$searchid[$zaehler2]=$id;
				$returndbdelete=dbdelete($returndbconnect,"search_results","id_search_strings='$searchid[$zaehler2]'");			
				if($returndbdelete==0){
					echo "DELETE-Query for the table 'search_results' went wrong";
					$returndeletequeries=0;
				}
			}
		}

		$returndbdelete=dbdelete($returndbconnect,"search_strings_for_results","id_topic_analysis='$topicanalysisid'");			
		if($returndbdelete==0){
			echo "DELETE-Query for the table 'search_strings_for_results' went wrong";
			$returndeletequeries=0;
		}
	
		$returndbdelete=dbdelete($returndbconnect,"search_strings","id_topic_analysis='$topicanalysisid'");			
		if($returndbdelete==0){
			echo "DELETE-Query for the table 'search_strings' went wrong";
			$returndeletequeries=0;
		}
				
		//Delete LDAs of this topic analysis id
		$returndbselect=dbselect($returndbconnect,"lda","id_topic_analysis='$topicanalysisid'","id");			
		$returndbnumrows=dbnumrows($returndbselect);
		if($returndbnumrows>0){
			$zaehler=0;
			while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($returndbselect)){
				$ldaid[$zaehler]=$id;
				$ldaname[$zaehler]=$name;
				$returndbdelete=dbdelete($returndbconnect,"lda_id_search_results","id_lda='$ldaid[$zaehler]'");			
				if($returndbdelete==0){
					echo "DELETE-Query for the table 'lda_id_search_results' went wrong";
					$returndeletequeries=0;
				}
			}
			$returndbdelete=dbdelete($returndbconnect,"lda","id_topic_analysis='$topicanalysisid'");			
			if($returndbdelete==0){
				echo "DELETE-Query for the table 'lda' went wrong";
				$returndeletequeries=0;
			}else{
				//If delete-query was successful delete any lda that is subordinated to this 
				deleteldainputexecuteoutputfolder(pathtolda() . $topicanalysisname);
			}
		}
		
		//Get Topic Analysis Name based on its id in order to delete its folder for its search results.
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id=$topicanalysisid","name");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$topicanalysisname=dbfetchfield($returndbfetcharray,'name');
		
		//Delete the folder of this topic analysis that contains the search results of this topic analysis.
		rrmdir(pathtosearchresults($topicanalysisname));
				
		//Delete from table 'topic_analysis' where the id is the above id.
		$returndbdelete=dbdelete($returndbconnect,"topic_analysis","id='$topicanalysisid'");			
		if($returndbdelete==0){
			echo "DELETE-Query for the table 'topic_analysis' went wrong";
			$returndeletequeries=0;
		}
		
		if($returndeletequeries==1){//successfully deleted anything
			echo 'Anything belonging to this topic analysis is successfully deleted.<br> Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.';
		}else{
			echo 'A mistake happened while deleting anything belonging to this topic analysis.<br> Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.';
		}
		dbdisconnect($returndbconnect);
	}else{
		echo 'Please go to the <a href="../topic_analysis_00_menu.php">menu</a>.';
	}

?>