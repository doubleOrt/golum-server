<?php

require_once "db_connection.php";

session_start();


//without this snippet, nothing would be pushed into the "toasts" session on the first push because it would be equal to null and you can't push to null. 
if(!isset($_SESSION["toasts"])) {
$_SESSION["toasts"] = [];	 	
}



function dataQuery($query, $params) {
global $con;
$queryType = explode(' ', $query);

// run query
try {
$queryResults = $con->prepare($query);
$queryResults->execute($params);
if($queryResults != null && 'SELECT' == strtoupper($queryType[0])) {
$results = $queryResults->fetchAll();
return $results;
}
$queryResults = null; // first of the two steps to properly close
$dbh = null; // second step to close the connection
}
catch(PDOException $e) {
$errorMsg = $e->getMessage();
echo $errorMsg;
}
}


function time_to_string($time) {
		
$time = intval($time);	
	
$today = new DateTime(); // This object represents current date/time
$today->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$match_date = DateTime::createFromFormat( "Y-m-d H:i", date("Y-m-d H:i",$time));
$match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$diff = $today->diff( $match_date );
$diffDays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

if(time() - $time < 120) {
return "Just Now";
}	
else if(time() - $time < 3600) {
return round((time() - $time)/60) ." Minutes Ago";
}
else if($diffDays == 0) {
return round((((time() - $time)/60)/60)) . " Hour". (round((((time() - $time)/60)/60)) != 1 ? "s" : "")  ." Ago";	
}
else if($diffDays == -1) {
return "Yesterday At ". date("H:i",$time);	
} 
else if(time() - $time < 604800){
return date("l",$time);	
}
else {
return date("Y/m/d H:i",$time);		
}
}




?>