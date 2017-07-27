<?php

require_once "db_connection.php";

session_start();


$SERVER_URL = "http://192.168.1.100/golum/";


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


function custom_pdo($query, $params) {
global $con;
try {
$prepared = $con->prepare($query);
foreach($params as $key => &$value) {
if(filter_var($value, FILTER_VALIDATE_INT) !== false) {
$prepared->bindValue($key, $value, PDO::PARAM_INT);	
}
else {
$prepared->bindParam($key, $value);	
}
}
$prepared->execute();
return $prepared;	
}
catch(PDOException $e) {
$errorMsg = $e->getMessage();
return $errorMsg;
}
}



?>