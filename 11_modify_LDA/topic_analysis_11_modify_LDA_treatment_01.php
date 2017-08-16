<?php
/*Algorithm:
  1. d) Modify a LDA, i.e. let the user change his former selection of the lda's components.
*/ 

	set_include_path("../00_general");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	include_once("php_functions.php");
	
	if(isset($_POST["select_existing_lda"])){
		$ldaid=htmlstringtostring($_POST["select_existing_lda"]);		
		$returndbconnect=dbconnect();
			
		//Get lda-name based on its id in order to show it.
		//We also need the topic analysis id for further computations
		$returndbselect=dbselect($returndbconnect,"lda","id=$ldaid","id");
		$returndbfetcharray=dbfetcharray($returndbselect);
		$returndbfetchfield=dbfetchfield($returndbfetcharray,'name');
		$ldaname=$returndbfetchfield;
		$number_of_topics_to_output=dbfetchfield($returndbfetcharray,'number_of_topics_to_output');
		$selected_conference=dbfetchfield($returndbfetcharray,'conference_selected');
		$selected_year_from=dbfetchfield($returndbfetcharray,'year_from_selected');
		$selected_year_to=dbfetchfield($returndbfetcharray,'year_to_selected');
		$topicanalysisid=dbfetchfield($returndbfetcharray,'id_topic_analysis');
	
		//GROUP-SELECTION of conference ORDER BY conference
		$returndbselect1=dbselectgrouped($returndbconnect,"search_results","conference","conference");
		//GROUP-SELECTION of year (year_from) ORDER BY year
		$returndbselect2=dbselectgrouped($returndbconnect,"search_results","year","year");	
		//GROUP-SELECTION of year (year_to) ORDER BY year
		$returndbselect3=dbselectgrouped($returndbconnect,"search_results","year","year");

					
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<style type="text/css">
.auto-style1 {
	margin-left: 0px;
}
</style>
</head>

<body>
<form method="post" action="topic_analysis_11_modify_LDA_treatment_02.php">

	<br />
	<table style="width: 100%">
		<tr>
			<td style="width: 486px">
			Modify LDA "' . $ldaname . '":</td>
			<td><a href="../topic_analysis_00_menu.php">Go back to the menu</a></td>
		</tr>
		<tr>
			<td style="width: 486px">
			<input name="texttopicanalysisid" type="hidden" value="' . $topicanalysisid .'"/></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 486px">
			conference:</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 486px">
			<select name="select_conference" style="width: 180px">
			<option value=""></option>
			<option selected="">'. $selected_conference . '</option>';
			while(list($id,$id_search_strings,$exclude,$authors,$title,$conference) = mysqli_fetch_row($returndbselect1)){	
				if($conference!=$selected_conference){
		       		echo '<option>' . $conference . '</option>';
				}
			}
			echo '
			</select></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 486px">
			from year:</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 486px; height: 26px;">
			<select name="select_year_from" style="width: 180px">
			<option value=""></option>
			<option selected="">'. $selected_year_from . '</option>';
			while(list($id,$id_search_strings,$exclude,$authors,$title,$conference,$year) = mysqli_fetch_row($returndbselect2)){	
				if($year!=$selected_year_from){
		       		echo '<option>' . $year . '</option>';
				}
			}
			echo '
			</select></td>
			<td style="height: 26px"></td>
		</tr>
		<tr>
			<td style="width: 486px">
			until year:</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 486px">
			<select name="select_year_to" style="width: 180px">
			<option value=""></option>
			<option selected="">'. $selected_year_to . '</option>';
			while(list($id,$id_search_strings,$exclude,$authors,$title,$conference,$year) = mysqli_fetch_row($returndbselect3)){	
				if($year!=$selected_year_to){
		       		echo '<option>' . $year . '</option>';
				}
			}
			echo '
			</select></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 486px; height: 23px;">
			</td>
			<td style="height: 23px"></td>
		</tr>
		<tr>
			<td style="width: 486px">
				LDA: Please give the LDA for the above selections a unique name:</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 486px">
			<input name="textldaname" type="text" value="' . $ldaname .'" style="width: 397px; height: 25px"/></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 486px">
				LDA: How much topics do you want
				as LDA-output? (1-20):</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 486px">
			<input name="textnumberoftopics" type="text" value="' . $number_of_topics_to_output . '" style="width: 397px; height: 25px"/></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 486px">
			<input name="textldaid" type="hidden" value="' . $ldaid .'"/></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 486px; height: 23px;">
			<table style="width: 60%">
				<tr>
					<td style="width: 132px">&nbsp;</td>
					<td>
<input name="submit_topic_analysis_11_modify_LDA_treatment_01" type="submit" value="Save data for the modified LDA" class="auto-style1" style="height: 26px" tabindex="2"/></td>
				</tr>
			</table>
			</td>
			<td style="height: 23px">&nbsp;</td>
		</tr>
		</table>

</form>
</body>

</html>';
						
						
		}else{//no ldaid found.
			echo 'No LDA selected. Please go back to the <a href="../topic_analysis_00_menu.php">menu</a>.</br>';
	}
?>