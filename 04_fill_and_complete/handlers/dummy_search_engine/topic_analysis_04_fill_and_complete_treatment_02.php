<?php
/*   
  1.    b) Complete the search results that were inserted by topic_analysis_04_fill_and_complete_treatment_01.php
  		if there is an up-to-date handler for completion complete this part of search results in order to present the
     	product "search_results" for the exclusion procedure according to slide 22 in   
        https://userpages.uni-koblenz.de/~laemmel/esecourse/slides/sms.pdf.        
        */

	set_include_path("../../../00_general");

	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	include_once("php_functions_04_fill_and_complete_treatment_02_first_link_to_abstracttext_www_researchgate_net.php");
	include_once("php_functions_04_fill_and_complete_treatment_02_abstracttext_www_researchgate_net.php");
	include_once("php_functions_04_fill_and_complete_treatment_02_pathtopdffulltext.php");
	include_once("php_functions_04_fill_and_complete_treatment_02_pdffulltextastext.php");
	include_once("php_functions_04_fill_and_complete_treatment_02_pdffulltextastextextracted.php");
	
	if(isset($_POST["texttopicanalysisid"])){
		$topicanalysisid=htmlstringtostring($_POST["texttopicanalysisid"]);	
		$returndbconnect=dbconnect();
			$zaehler10=0;
		//Get Topic Analysis Name based on its id in order to show it.
		$returndbselect=dbselect($returndbconnect,"topic_analysis","id=$topicanalysisid","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$returndbfetchfield=dbfetchfield($returndbfetcharray,'name');
		$topicanalysisname=$returndbfetchfield;
		
		//Get data for research questions into the field for this topic analysis.
		$returndbselect1=dbselect($returndbconnect,"research_questions","id_topic_analysis=$topicanalysisid","id");
		$returndbnumrows1=dbnumrows($returndbselect1);
		//Get data for search strings into the field for this topic analysis if there are already any.
		$returndbselect3=dbselect($returndbconnect,"search_strings","id_topic_analysis=$topicanalysisid","id");
		$returndbnumrows3=dbnumrows($returndbselect3);
		
		if($returndbnumrows1>0){	
			/*We know from topic_analysis_04_fill_and_complete_treatment_01.php that we have research questions for this
			  topic analysis. The message from topic_analysis_04_fill_and_complete_treatment_01.php for this is:
			  "There are research questions for this topic analysis
			  This is necessary to proceed because the first step in the systematic mapping study
			  in https://userpages.uni-koblenz.de/~laemmel/esecourse/slides/sms.pdf between slide 12 and slide 16
			  must be a control functionality for the search (second step in the systematic mapping study)
			  (see slide 18 at https://userpages.uni-koblenz.de/~laemmel/esecourse/slides/sms.pdf)"
			  
			  We will show them after we submitted this form. After submitted this form the user can exclude the search
			  results that do not fit to his research questions. This is in line with the feature of systematic mapping study
			  specified at slide 18 at https://userpages.uni-koblenz.de/~laemmel/esecourse/slides/sms.pdf*/
			
			if($returndbnumrows3>0){
			/*This is for the computation of the amount of abstracttexts in column 'abstracttext' and path_to_pdffulltexts in
			column 'path_to_pdffulltext' in table 'search_results' after the completion of any column in table 'search_results'
			is finished.*/
				$zaehler11=0;
				while(list($id) = mysqli_fetch_row($returndbselect3)){
					$idsearchstring[$zaehler11]=$id;
					$zaehler11++;
				}
			/*Search strings are also part of this topic analysis.
			  The message from topic_analysis_04_fill_and_complete_treatment_01.php for this is:
			  "Fetch the search strings for the search results that will be completed with the help of
			   topic_analysis_04_fill_and_complete_treatment_02.php.
			   Because it is $returndbnumrows3>0 there are also search strings that are already saved for this topic
			   analysis. Therefore extract the search engines to an input-file for wget. Switch to the right
			   search engine's treatment procedure later in line of topic_analysis_04_fill_and_complete_treatment_01.php
			   (search results for search strings from the second textarea field in the html-modify-form).
			   and in line of topic_analysis_04_fill_and_complete_treatment_02.php (search results for search strings
			   from the third textarea field in the html-modify-form).
			   
				Now we continue as follows:
				We complete the search results in the favour of the user. The user must insert the URL to the search engine the
				user likes to contact in order to fetch the information that is an update for the part of the search results the
                user likes to enrich. At first we check whether the amount of rows in the third textarea of the form where we
                added or modified the topic analysis is the same as the amount of table columns in table search_results that are
                updateable by the user. If the inserted rows in this textfield is equal to the updateable fields of table
                search_results then we continue to ask what row in this textfield is not 0$. If we have a row that is not 0$ then
                we relate the row to the table column. Then we read the string that is not 0$. This string is the URL to the
                search engine and our means to switch to the function that treats fetching information for the respective
                column in table "search_strings" if this particular search engine is called.*/
				
				/*First step (first part): Check of amount of rows in the third textarea for this topic analysis.*/ 

				$returndbselect=dbselect($returndbconnect,"search_strings_for_results","id_topic_analysis=$topicanalysisid","id");
				$zaehler=0;		
				while(list($id,$id_topic_analysis,$name) = mysqli_fetch_row($returndbselect)){
					$id_search_strings_for_results[$zaehler]=$id;
					$search_string_for_results[$zaehler]=$name;
					$zaehler++;
				}
				mysqli_free_result($returndbselect);
			
				$amountofinsertedrows=$zaehler;
			
				/*For the first step's check (second and last part): How many columns have to be filled in table "search_results"?*/
				$returndbselect=dbselect($returndbconnect,"search_results","1","id");
				$amountofcolumns=mysqli_num_fields($returndbselect);
				//We do not count id-fields and the exclude-bitfield
				$amountofmandatoryrows=$amountofcolumns-notimportantcolumnstablesearchresults()-notimportantcolumnstablesearchresultsattheend();		  
				$finfo = mysqli_fetch_fields($returndbselect);
				//Output of the up-to-date columns of table "search_results" in $finfoarray 
				$zaehler=0;
				foreach ($finfo as $val) {
        			$finfoarray[$zaehler]=$val->name;
 	        		$zaehler++;
	        	}
				mysqli_free_result($returndbselect);
				
				if($amountofinsertedrows!=$amountofmandatoryrows){	
					/*The amount of rows in the third textfield of the html-form for modifying topic analysis components is not equal
					to the amount of mandatory columns to fill out in table "search_results". Therefore we echo an error.*/
	        
		        	/*In order to give an up-to-date output to the user the columns of table "search_results" are read into
					an array for output.*/
		        	$hint="";
					for($zaehler=notimportantcolumnstablesearchresults()-1;$zaehler<$amountofinsertedrows;$zaehler++){
						$whatrow=$zaehler-notimportantcolumnstablesearchresults()+1;
						$hint=$hint . '</br>You have to write either the wildcard 0$ or the URL of the search engine like
						"http://www.researchgate.net" in the
					' . $whatrow . '. row for completing "' . $finfoarray[$zaehler] . '".';
					}					
				
					echo 'The amount of rows in the third textarea field in the <a href="../../../topic_analysis_02_modify.php">html-form for modifying the components
					of a topic analysis</a> are not the same as the amount of updateable columns in table "search_results". The amount
					of updateable columns in table "search_results" is ' . $amountofmandatoryrows . '.</br>If you do not have a column
					to update please write 0$ in the row for this column of table "search_results". ' . $hint . '</br>Please go
					to the <a href="../../../topic_analysis_00_menu.php">menu</a>.';
				}else{
				    $htmlcodeforsnapshot='';
				    /*Second step: We continue to ask what row in this textfield is not 0$. This information is saved in
				    $search_string_for_results. Then we use the column names in $finfoarray. Then we can relate each row's
				    entry to each column's name.*/
				    $showerrormessage="";
				    $linksforcompletions="";
				    $errormessage=0;
				    $markforlinksforcompletions=0;
				    for($zaehler=0;$zaehler<$amountofinsertedrows;$zaehler++){
				    	$whatrow=$zaehler+1;
				    	if($search_string_for_results[$zaehler]<>"0"){
				    		//A string for a search engine is standing in a row.
				    		$columnincrement=$zaehler+notimportantcolumnstablesearchresults();
				    		$columnname=$finfoarray[$columnincrement];
				    		switch($columnname){
				    			case "authors":
				    				$showerrormessage=$showerrormessage . '<tr>
						<td style="width: 637px; height: 23px;">No handler for completing "author" in table "search_results" with the help of the search
				    				engine ' . $search_string_for_results[$zaehler] . '.</br>Please write a function
				    				and call it from "topic_analysis_04_fill_and_complete_treatment_02.php" for handling the completion of
				    				"author" in table "search_results" with the help of the search engine
				    				' . $search_string_for_results[$zaehler] . '. If you do not have a handler for this search engine
				    				please write 0$ in the ' . $whatrow . '. row of the third
				    				field of the <a href="../../../topic_analysis_02_modify.php">html-form for modifying components of
				    				a topic analysis</a>.</br></td>
						<td style="height: 23px">&nbsp;</td>
					</tr>';		
							    		$errormessage=1;
				    			break;
				    				
				    			case "title":
				    				$showerrormessage=$showerrormessage . '<tr>
						<td style="width: 637px; height: 23px;">No handler for completing "title" in table "search_results" with the help of the search
				    				engine ' . $search_string_for_results[$zaehler] . '.</br>Please write a function
				    				and call it from "topic_analysis_04_fill_and_complete_treatment_02.php" for handling the completion of
				    				"title" in table "search_results" with the help of the search engine
				    				' . $search_string_for_results[$zaehler] . '. If you do not have a handler for this search engine
				    				please write 0$ in the ' . $whatrow . '. row of the third field of the <a href="../../../topic_analysis_02_modify.php">html-form for modifying components of
				    				a topic analysis</a>.</br></td>
						<td style="height: 23px">&nbsp;</td>
					</tr>';
						    		$errormessage=1;
									break;
				    			
				    			case "conference":
					    			$showerrormessage=$showerrormessage . '<tr>
						<td style="width: 637px; height: 23px;">No handler for completing "conference" in table "search_results" with the help of the search
				    				engine ' . $search_string_for_results[$zaehler] . '.</br>Please write a function
				    				and call it from "topic_analysis_04_fill_and_complete_treatment_02.php" for handling the completion of
				    				"conference" in table "search_results" with the help of the search engine
				    				' . $search_string_for_results[$zaehler] . '. If you do not have a handler for this search engine
				    				please write 0$ in the ' . $whatrow . '. row of the third field of the <a href="../../../topic_analysis_02_modify.php">html-form for modifying components of
				    				a topic analysis</a>.</br></td>
						<td style="height: 23px">&nbsp;</td>
					</tr>';					
							   		$errormessage=1;
							   	break;

				    			case "year":
				    				$showerrormessage=$showerrormessage . '<tr>
						<td style="width: 637px; height: 23px;">No handler for completing "year" in table "search_results" with the help of the search
				    				engine ' . $search_string_for_results[$zaehler] . '.</br>Please write a function
				    				and call it from "topic_analysis_04_fill_and_complete_treatment_02.php" for handling the completion of
				    				"year" in table "search_results" with the help of the search engine
				    				' . $search_string_for_results[$zaehler] . '. If you do not have a handler for this search engine
				    				please write 0$ in the ' . $whatrow . '. row of the third
				    				field of the <a href="../../../topic_analysis_02_modify.php">html-form for modifying components of
				    				a topic analysis</a>.</br></td>
						<td style="height: 23px">&nbsp;</td>
					</tr>';					
									$errormessage=1;
						    		break;
									
				    			case "first_link_to_abstracttext":
			    					//We have a handler for the links to the abstracts for titles already in table "search_results
 	   								//for this topic analysis. The handler is specified for https://www.researchgate.net/search?q=
    								$validsearchenginehere="www.researchgate.net";
    								$trimmed_search_string_for_results=ltrim($search_string_for_results[$zaehler]);
    								if(strcmp($trimmed_search_string_for_results,$validsearchenginehere)==0){
			    					 	if($markforlinksforcompletions==0){
			    					 		$markforlinksforcompletions=5;
			    					 	}else{
			    					 		if($linksforcompletions==''){
			    					 			$markforlinksforcompletions=5;
			    					 		}
			    					 	}
			    					 	if($markforlinksforcompletions==5){
				    					 	//Prevent first_link_to_abstracttext from writing $linksforcompletions if any other
				    					 	//column still has to be filled.
				    					 	$linksforcompletions=first_link_to_abstracttext_www_researchgate_net($zaehler10,$topicanalysisid,$topicanalysisname,$linksforcompletions);
				    					}
				    					$htmlcodeforsnapshot=createsnapshotoftablesearchresults($topicanalysisid);
				    				}else{
					    				$showerrormessage=$showerrormessage . '<tr>
					<td style="width: 637px; height: 23px;">No handler for completing "first_link_to_abstracttext" in table "search_results" with the help of the search
				    				engine ' . $search_string_for_results[$zaehler] . '.</br>Please write a function
				    				and call it from "topic_analysis_04_fill_and_complete_treatment_02.php" for handling the completion of
				    				"first_link_to_abstracttext" in table "search_results" with the help of the search engine
				    				' . $search_string_for_results[$zaehler] . '. If you do not have a handler for this search engine
				    				please write 0$ in the ' . $whatrow . '. row of the third
				    				field of the <a href="../../../topic_analysis_02_modify.php">html-form for modifying components of
				    				a topic analysis</a>.</br></td>
						<td style="height: 23px">&nbsp;</td>
					</tr>';				
										$errormessage=1;
									}	
	 		    				break;
				    				
				    			case "abstracttext":
    								$validsearchenginehere="www.researchgate.net";
    								$trimmed_search_string_for_results=ltrim($search_string_for_results[$zaehler]);
    								if(strcmp($trimmed_search_string_for_results,$validsearchenginehere)==0){
				    					//We have a handler for the abstracttexts for titles already in table "search_results.
	 	   								//The handler is specified for https://www.researchgate.net/search?q=
										if($markforlinksforcompletions==0){
			    					 		$markforlinksforcompletions=6;
			    					 	}else{
			    					 		if($linksforcompletions==''){
			    					 			$markforlinksforcompletions=6;
			    					 		}
			    					 	}

										if($markforlinksforcompletions==6){
											$linksforcompletions=abstracttext_www_researchgate_net($topicanalysisid,$topicanalysisname,$linksforcompletions);				    					
										}
				    					$htmlcodeforsnapshot=createsnapshotoftablesearchresults($topicanalysisid);
				    				}else{
						    			$showerrormessage=$showerrormessage . '<tr>
										<td style="width: 637px; height: 23px;">No handler for completing "abstracttext" in table "search_results" with the help of the search
					    				engine ' . $search_string_for_results[$zaehler] . '.</br>Please write a function
					    				and call it from "topic_analysis_04_fill_and_complete_treatment_02.php" for handling the completion of
					    				"abstracttext" in table "search_results" with the help of the search engine
					    				' . $search_string_for_results[$zaehler] . '. If you do not have a handler for this search engine
					    				please write 0$ in the ' . $whatrow . '. row of the third
					    				field of the <a href="../../../topic_analysis_02_modify.php">html-form for modifying components of
					    				a topic analysis</a>.</br></td>
										<td style="height: 23px">&nbsp;</td>
										</tr>';					
							    		$errormessage=1;
						    		}
							    break;
							  	case "abstracttext_for_lda":
				    				$showerrormessage=$showerrormessage . '<tr>
						<td style="width: 637px; height: 23px;">No handler for completing "abstracttext_for_lda" in table "search_results" with the help of the search
				    				engine ' . $search_string_for_results[$zaehler] . '.</br>Please write a function
				    				and call it from "topic_analysis_04_fill_and_complete_treatment_02.php" for handling the completion of
				    				"abstracttext_for_lda" in table "search_results" with the help of the search engine
				    				' . $search_string_for_results[$zaehler] . '. If you do not have a handler for this search engine
				    				please write 0$ in the ' . $whatrow . '. row of the third
				    				field of the <a href="../../../topic_analysis_02_modify.php">html-form for modifying components of
				    				a topic analysis</a>.</br></td>
						<td style="height: 23px">&nbsp;</td>
					</tr>';					
						    		$errormessage=1;
									break;
				    			case "first_link_to_pdffulltext":
				    				$showerrormessage=$showerrormessage . '<tr>
						<td style="width: 637px; height: 23px;">No handler for completing "first_link_to_pdffulltext" in table "search_results" with the help of the search
				    				engine ' . $search_string_for_results[$zaehler] . '.</br>Please write a function
				    				and call it from "topic_analysis_04_fill_and_complete_treatment_02.php" for handling the completion of
				    				"first_link_to_pdffulltext" in table "search_results" with the help of the search engine
				    				' . $search_string_for_results[$zaehler] . '. If you do not have a handler for this search engine
				    				please write 0$ in the ' . $whatrow . '. row of the third
				    				field of the <a href="../../../topic_analysis_02_modify.php">html-form for modifying components of
				    				a topic analysis</a>.</br></td>
						<td style="height: 23px">&nbsp;</td>
					</tr>';					
						    		$errormessage=1;
									break;
				    				
				    			case "path_to_pdffulltext":
			    					//We have a handler for path_to_pdffulltext in table "search_results
 	   								$validsearchenginehere="dummy_searchengine";
    								$trimmed_search_string_for_results=ltrim($search_string_for_results[$zaehler]);
    								if(strcmp($trimmed_search_string_for_results,$validsearchenginehere)==0){
										if($markforlinksforcompletions==0){
			    					 		$markforlinksforcompletions=8;
			    					 	}else{
			    					 		if($linksforcompletions==''){
			    					 			$markforlinksforcompletions=8;
			    					 		}
			    					 	}

										if($markforlinksforcompletions==8){
											$linksforcompletions=pathtopdffulltext($topicanalysisid,$topicanalysisname,$linksforcompletions);				    					
										}
				    					$htmlcodeforsnapshot=createsnapshotoftablesearchresults($topicanalysisid);
				    				}else{
						    			$showerrormessage=$showerrormessage . '<tr>
						<td style="width: 637px; height: 23px;">'. $search_string_for_results[$zaehler] .' is not a valid search engine handler for completing
						"path_to_pdffulltext" in table "search_results".</br>Please try the search engine handler "dummy_searchengine$" instead.</td>
						<td style="height: 23px">&nbsp;</td>
						</tr>';					
						    			$errormessage=1;
						    		}
				    				break;
				    				
				    				case "pdffulltext_as_text":
			    					//We have a handler for pdffulltext_as_text in table "search_results
			    					//The background is to convert the pdffulltext from pdf to text automatically. 
 	   								$validsearchenginehere="dummy_searchengine";
    								$trimmed_search_string_for_results=ltrim($search_string_for_results[$zaehler]);
    								if(strcmp($trimmed_search_string_for_results,$validsearchenginehere)==0){
										if($markforlinksforcompletions==0){
			    					 		$markforlinksforcompletions=9;
			    					 	}else{
			    					 		if($linksforcompletions==''){
			    					 			$markforlinksforcompletions=9;
			    					 		}
			    					 	}

										if($markforlinksforcompletions==9){
											$linksforcompletions=pdffulltextastext($topicanalysisid,$topicanalysisname,$linksforcompletions);				    					
										}
				    					$htmlcodeforsnapshot=createsnapshotoftablesearchresults($topicanalysisid);
				    				}else{
						    			$showerrormessage=$showerrormessage . '<tr>
						<td style="width: 637px; height: 23px;">'. $search_string_for_results[$zaehler] .' is not a valid search engine handler for completing
						"pdffulltext_as_text" in table "search_results".</br>Please try the search engine handler "dummy_searchengine$" instead.</td>
						<td style="height: 23px">&nbsp;</td>
						</tr>';					
						    			$errormessage=1;
						    		}
				    				break;
				    				
				    				case "pdffulltext_as_text_extracted":
			    					/*We have a handler for pdffulltext_as_text_extracted in table "search_results
			    					The user must manually extract the important parts of the text in
			    					column "pdffulltext_as_text" so that the result of this manual extraction can be saved in
			    					column "pdffulltext_as_text_extracted". Normally the most important part of the pdf is the
			    					abstract and the introduction.*/
 	   								$validsearchenginehere="dummy_searchengine";
    								$trimmed_search_string_for_results=ltrim($search_string_for_results[$zaehler]);
    								if(strcmp($trimmed_search_string_for_results,$validsearchenginehere)==0){
										if($markforlinksforcompletions==0){
			    					 		$markforlinksforcompletions=10;
			    					 	}else{
			    					 		if($linksforcompletions==''){
			    					 			$markforlinksforcompletions=10;
			    					 		}
			    					 	}

										if($markforlinksforcompletions==10){
											$linksforcompletions=pdffulltextastextextracted($topicanalysisid,$topicanalysisname,$linksforcompletions);				    					
										}
				    					$htmlcodeforsnapshot=createsnapshotoftablesearchresults($topicanalysisid);
				    				}else{
						    			$showerrormessage=$showerrormessage . '<tr>
						<td style="width: 637px; height: 23px;">'. $search_string_for_results[$zaehler] .' is not a valid search engine handler for completing
						"pdffulltext_as_text_extracted" in table "search_results".</br>Please try the search engine handler "dummy_searchengine$" instead.</td>
						<td style="height: 23px">&nbsp;</td>
						</tr>';					
						    			$errormessage=1;
						    		}
				    				break;
							  	case "pdffulltext_for_lda":
				    				$showerrormessage=$showerrormessage . '<tr>
						<td style="width: 637px; height: 23px;">No handler for completing "pdffulltext_for_lda" in table "search_results" with the help of the search
				    				engine ' . $search_string_for_results[$zaehler] . '.</br>Please write a function
				    				and call it from "topic_analysis_04_fill_and_complete_treatment_02.php" for handling the completion of
				    				"pdffulltext_for_lda" in table "search_results" with the help of the search engine
				    				' . $search_string_for_results[$zaehler] . '. If you do not have a handler for this search engine
				    				please write 0$ in the ' . $whatrow . '. row of the third
				    				field of the <a href="../../../topic_analysis_02_modify.php">html-form for modifying components of
				    				a topic analysis</a>.</br></td>
						<td style="height: 23px">&nbsp;</td>
					</tr>';					
						    		$errormessage=1;
									break;
					    			default:
				    				$showerrormessage=$showerrormessage . '<tr>
						<td style="width: 637px; height: 23px;">There is no column and the search engine ' . $search_string_for_results[$zaehler] . '
										  is unknown to this unknown column for table "search_results". Please add a new column that has this
				    				name after the last column in table "search_results" and write a php-function that handles the
				    				completion of this unknown column.</br></td>
						<td style="height: 23px">&nbsp;</td>
						</tr>';
									$errormessage=1;					
				    				break;
				    		}
				    	}		
				    }
				    if($errormessage==0){
					    $showerrormessage='<tr>
						<td style="width: 637px; height: 23px;">No error messages.</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>';					
				    }
				    if($linksforcompletions==''){
				    //Compute how much columns 'abstracttext' and how much columns 'path_to_pdffulltext' are filled for this topic analysis in table 'search_results' 
				    	$returndbselect6=dbselect($returndbconnect,"search_results","1","id");				    		
				    	$amountabstracttexts=0;
				    	$amountpathpdfs=0;
				    	$amountofrows=0;
				    	for($zaehler12=0;$zaehler12<sizeof($idsearchstring);$zaehler12++){
				    		//abstractttext:
				    		$returndbselect4=dbselect($returndbconnect,"search_results","id_search_strings='$idsearchstring[$zaehler12]' AND abstracttext!=''","id");
				    		$amountabstracttexts=$amountabstracttexts+dbnumrows($returndbselect4);
				    		mysqli_free_result($returndbselect4);
				    		//pdfs:
				    		$returndbselect5=dbselect($returndbconnect,"search_results","id_search_strings='$idsearchstring[$zaehler12]' AND path_to_pdffulltext!=''","id");
				    		$amountpathpdfs=$amountpathpdfs+dbnumrows($returndbselect5);
				    		mysqli_free_result($returndbselect5);	
				    	}
				    	for($zaehler12=0;$zaehler12<sizeof($idsearchstring);$zaehler12++){
					    	$returndbselect6=dbselect($returndbconnect,"search_results","id_search_strings='$idsearchstring[$zaehler12]'","id");
				    	 	$amountofrows=$amountofrows+dbnumrows($returndbselect6);
				    	}
				    					
			    		$linksforcompletions='<tr>
						<td style="width: 637px; height: 23px;">No fields to update in a column in table search results.</td>
						<td style="height: 23px">&nbsp;</td>
					</tr><tr>
						<td style="width: 637px; height: 23px;">&nbsp;</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 637px; height: 23px;">Information for the <a href="../../../topic_analysis_13_execute_LDA.php">lda-execution</a>: 
						' . $amountabstracttexts . ' of possible ' . $amountofrows . ' abstracttexts are filled.</td>
						<td style="height: 23px">&nbsp;</td>
					</tr><tr>
						<td>' . $amountpathpdfs . ' of possible ' . $amountofrows . ' pdffulltexts are filled. Please open the 
						<a href="../../../topic_analysis_05_upload_further_pdffulltexts.php">form for manual upload of pdffulltexts</a> in
						order to upload them ex post if you have any pdffulltexts on your harddisk that is meaningful for the
						<a href="../../../topic_analysis_13_execute_LDA.php">lda-execution</a>.</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>';					
					
					
				    }				    
				    
				    
						
						echo'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	
	<head>
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
	<style type="text/css">
   {font-weight:normal;color:#181818;background-color:#fffccf}b.b4{font-weight:normal;color:#0c0c0c;background-color:#fffccf}b.b2{font-weight:normal;color:#242424;background-color:#fffeef}</style>
	</head>
	
	<body>
		<table style="width: 100%">
			<tr>
				<td style="width: 593px; height: 79px;">
				<table style="width: 159%">
					<tr>
						<td style="width: 637px; height: 23px;">Completion of 
						search results for the 
						topic analysis "' . $topicanalysisname . '".</td>
						<td style="height: 23px"><a href="../../../topic_analysis_00_menu.php">Back to the menu</a></td>
					</tr>
					<tr>
						<td style="width: 637px; height: 23px;"></td>
						<td style="height: 23px"></td>
					</tr>
					<tr>
						<td style="width: 637px; height: 23px;">Status 
						information for completion of columns in table 
						"search_results" that have not a</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 637px; height: 23px;">handler for the search engine:</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>' . $showerrormessage . '
					<tr>
						<td style="width: 637px; height: 23px;">&nbsp;</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 637px; height: 23px;">Please abort if 
						the fetching for search results exceeds a reasonable 
						period of time. This is because sometimes search engines 
						stay idle although we are waiting for results. In this 
						case please change your public ip-address!</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 637px; height: 23px;">If anything has 
						been successful please have a 
						look at the messages below. These messages inform you 
						whether it is possible to complete your desired column 
						in table "search_results". If not the same link is shown 
						more than twice after pressing F5 each time.</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 637px; height: 23px;">For any 
						completion a particular pop-up-window is opened. Please 
						read the results of this window and<br>close it after a 
						pop-up-procedure is finished.</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 637px; height: 23px;">If completion is 
						finished please close the pop-up-window and press F5 in 
						order to verify that no completion job is left. If there
						are still links to completion please proceed as
						described above.</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 637px; height: 23px;"></td>
						<td>&nbsp;</td>
					</tr>'. $linksforcompletions .'
					<tr>
						<td style="width: 637px; height: 23px;">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 637px; height: 23px;">If you are 
						finished with completing the search results above and if 
						you agree with the search results in the table 
						below please <a href="../../../topic_analysis_06_exclude_search_results.php">exclude</a> any search result
						for this topic 
						analysis that does not answer your research 
						questions for this topic analysis (these research 
						questions will be shown again
						<a href="../../../topic_analysis_06_exclude_search_results.php">after you selected the
						existing topic analysis for this exclusion</a>.</td>
						<td>&nbsp;</td>
					</tr>
					</table>
				</td>
				<td style="height: 79px"></td>
			</tr>
			<tr>
				<td style="width: 593px; height: 23px;">
				<table style="width: 83%">
					<tr>
						<td style="width: 209px; height: 23px;"></td>
						<td style="height: 23px">
						</td>
					</tr>
				</table>
				</td>
				<td style="height: 23px">&nbsp;</td>
			</tr>
			</table>' . $htmlcodeforsnapshot . '</body>
	
	</html>
     			';}}else{
					echo 'No search strings for topic analysis "' . $topicanalysisname .'". Please go to the <a href="../../../topic_analysis_00_menu.php">menu</a>.';	
			      }
						
			}else{
				echo 'No research questions for topic analysis "' . $topicanalysisname .'". Please go to the <a href="../../../topic_analysis_00_menu.php">menu</a>.';
			}	
			dbdisconnect($returndbconnect);		
		}else{
			echo 'No ID for the topic analysis. Please go to the <a href="../../../topic_analysis_00_menu.php">menu</a>.';
		}	
?>