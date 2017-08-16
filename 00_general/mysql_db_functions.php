<?php

	function dbrealescapestring($returndbconnect,$string){
		$mysqli_real_escape_string_return=mysqli_real_escape_string($returndbconnect,$string);
		return $mysqli_real_escape_string_return;
	}
		
	function dbnumrows($returndbselect){
		$mysqli_num_rows_result=mysqli_num_rows($returndbselect);
		return $mysqli_num_rows_result;
 	}	
 	
	function dbfetcharray($returndbselect){
		$mysqli_fetch_array_result=mysqli_fetch_array($returndbselect,MYSQLI_BOTH);
		return $mysqli_fetch_array_result; 
 	}

	function dbfetchfield($returndbfetcharray,$column){
		$dbfetchfieldresult=$returndbfetcharray[$column];
		return $dbfetchfieldresult;
	}	
	
	function dbfetchrow($returndbselect){
		while ($row = mysqli_fetch_row($returndbselect3)) {
    		printf ("%s (%s)\n", $row[0], $row[1], $row[2]);
   		}
   	}	
?>