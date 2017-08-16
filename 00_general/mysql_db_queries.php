<?php
	
	function dbinsert($returndbconnect,$table,$columns,$values){
		//echo "Host information: " . mysqli_get_host_info($returndbconnect);
		$insertquery="INSERT INTO $table $columns VALUES $values"; 
		$mysqli_query_result=mysqli_query($returndbconnect,$insertquery);
		if($mysqli_query_result!=1){
			echo "MySQL-Query in function dbinsert failed!";
		}
		return $mysqli_query_result;
	}	
		
	function dbupdate($returndbconnect,$table,$columns,$row){
		$updatequery="UPDATE $table SET $columns WHERE $row";
		//echo $updatequery . "<br>"; 
		$mysqli_query_result=mysqli_query($returndbconnect,$updatequery);
		if($mysqli_query_result!=1){
			//echo "MySQL-Query in function dbupdate failed!";
		}	
		return $mysqli_query_result;
	}
	
	function dbselect($returndbconnect,$table,$whereconstraint,$sortby){
		$selectqueryresult="SELECT * FROM $table WHERE $whereconstraint ORDER BY $sortby";
		$mysqli_query_result=mysqli_query($returndbconnect,$selectqueryresult);		
		return $mysqli_query_result;
	}
	
	function dbselectgrouped($returndbconnect,$table,$groupconstraint,$sortby){
		$selectqueryresult="SELECT * FROM $table GROUP BY $groupconstraint ORDER BY $sortby";
		$mysqli_query_result=mysqli_query($returndbconnect,$selectqueryresult);		
		return $mysqli_query_result;
	}
	
	function dbdelete($returndbconnect,$table,$whereconstraint){
			$deletequeryresult="DELETE FROM $table WHERE $whereconstraint"; 
		$mysqli_query_result=mysqli_query($returndbconnect,$deletequeryresult);
		if($mysqli_query_result!=1){
			echo "MySQL-Query in function dbdelete failed!";
		}	
		return $mysqli_query_result;
	}
?>
