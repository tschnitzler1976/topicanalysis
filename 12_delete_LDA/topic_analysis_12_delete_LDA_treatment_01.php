<?php
	set_include_path("../00_general");
	include_once("php_functions.php");
	include_once("mysql_db_connection.php");
	include_once("mysql_db_queries.php");
	include_once("mysql_db_functions.php");
	
	if(isset($_POST["select_existing_lda"])){
		if(ltrim($_POST["select_existing_lda"])!=''){
			$ldaid=htmlstringtostring($_POST["select_existing_lda"]);
			$returndbconnect=dbconnect();
			$returndbselect=dbselect($returndbconnect,"lda", "id='$ldaid'","id");
			$dbfetcharray=dbfetcharray($returndbselect);
			$ldaname=dbfetchfield($dbfetcharray,"name");
		
	
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<style type="text/css">
.auto-style1 {
	margin-left: 0px;
}
.auto-style2 {
	text-align: center;
}
.auto-style3 {
	text-align: left;
}
</style>
</head>

<body>
<form method="post" action="topic_analysis_12_delete_LDA_treatment_02.php">

	<table style="width: 100%">
		<tr>
			<td class="auto-style2" style="width: 476px">Confirmation for deleting the lda</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="auto-style2" style="width: 476px">"' . $ldaname . '".</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 476px">&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 476px" class="auto-style3">Do you really 
						want to delete the lda "' . 
						$ldaname . '"? </td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 476px" class="auto-style3">Warning: After deletion 
			this lda will be irrevocably deleted.</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 476px">&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td style="width: 476px">&nbsp;</td>
			<td><input name="select_existing_lda" type="hidden" value="' . $ldaid .'"/></td>
		</tr>
		</table>
	<table style="width: 100%">
		<tr>
			<td style="width: 416px; height: 23px;">
			<table style="width: 79%">
				<tr>
					<td style="width: 159px"><input name="submit_topic_analysis_12_delete_LDA" type="submit" value="Delete irrevocably" class="auto-style1" style="height: 26px" tabindex="2"/></td>
					<td><a href="../topic_analysis_00_menu.php">Back to the menu</a></td>
				</tr>
			</table>
			</td>
			<td style="height: 23px">&nbsp;</td>
		</tr>
		</table>

</form>
</body>

</html>';
			dbdisconnect($returndbconnect);
			}else{
				echo 'No lda selected. Please go to the <a href="../topic_analysis_00_menu.php">menu</a>.';
			}
		}else{
			echo 'Please go to the <a href="../topic_analysis_00_menu.php">menu</a>.';
		}
?>