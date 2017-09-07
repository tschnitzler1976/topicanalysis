<?php
/*   The user should see the search results for each search string. Therefore we generate
	 links to a pop-up-page. Then the user can see whether the search results are
	 correct or not. In case the user decides that the search results are not correct
	 the user should modify the search strings in order to improve
	 the search results.
*/
	set_include_path("../../../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	
	if(isset($_GET["searchstringid"]) && isset($_GET["topicanalysisname"])){
		$searchstringid=htmlstringtostring($_GET["searchstringid"]);
		$topicanalysisname=htmlstringtostring($_GET["topicanalysisname"]);
	
		$returndbconnect=dbconnect();

		//Get search string and the search result for the manual verification
		$dbselectresult=dbselect($returndbconnect,"search_strings","id='$searchstringid'","id");
		$dbfetcharray=dbfetcharray($dbselectresult);
		$searchstring=dbfetchfield($dbfetcharray,"name");
		$searchresult=dbfetchfield($dbfetcharray,"htmlsource");
		if(stripos($searchresult,"<")==false){
			$searchresult=stringtohtmlstring($searchresult);
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
				<td style="width: 589px; height: 79px;">
				<table style="width: 159%">
					<tr>
						<td style="width: 651px; height: 23px;" class="auto-style1">
						Verification of the search results as they are provided 
						from </td>
						<td style="height: 23px"><a href="javascript:close();">Close this window</a></td>
					</tr>
					<tr>
						<td style="width: 651px; height: 23px;" class="auto-style1">
						the search string ' . $searchstring . ' for the topic analysis</td>
						<td style="height: 23px">&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 651px; height: 23px;" class="auto-style1">
						"' . $topicanalysisname . '".</td>
						<td style="height: 23px"></td>
					</tr>
					<tr>
						<td style="width: 651px; height: 23px;" class="auto-style1">
						&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 651px; height: 23px;" class="auto-style1">
						Search result for the search string</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 651px; height: 23px;" class="auto-style1">' . $searchstring . '</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 651px; height: 23px;" class="auto-style1">
						&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 651px; height: 23px;">The search 
						result of the search string is as follows.</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 651px; height: 23px;">If the search 
						result does not satisfy your needs please
						<a href="../topic_analysis_02_modify.php">modify</a> 
						this search string.</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 651px; height: 23px;">&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					<tr>
						<td style="width: 651px; height: 23px;">' . $searchresult . '</td>
						<td>&nbsp;</td>
					</tr>
					</table>
				</td>
				<td style="height: 79px"></td>
			</tr>
			</table>
	</body>
	
	</html>';
		dbdisconnect($returndbconnect);
	}else{
		echo 'Please go to the <a href="../../../topic_analysis_00_menu.php">menu</a>.';
	}
?>