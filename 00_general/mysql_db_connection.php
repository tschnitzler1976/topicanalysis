<?php
	function dbconnect(){
		$returndbconnect = mysqli_connect("localhost", "root", "", "topic_analysis");
		if (!$returndbconnect) {
	    	echo "Error: Unable to connect to MySQL." . PHP_EOL;
	    	echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
	    	echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
	    	exit;
		}
		return $returndbconnect;
	}
	
	function dbdisconnect($returndbconnect){
		mysqli_close($returndbconnect);
	}
?>
