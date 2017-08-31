<?php

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PDOSessionHandler;

/* i don't know why, but for some weird reason, if i include 
the db_connection.php file after the autoload file, the $con 
variable will be undefined, which is why i must require 
it before the autoload file or it won't work. */
require_once "db_connection.php";
require_once '/../composer_things/vendor/autoload.php';

$pdo_session_handler =  new PDOSessionHandler($con, ['lock_mode' => 0]);
/* remember that this $storage always needs to exist as an independent 
variable since we are using it to call .regenerate(true) in the login.php file. */
$storage = new NativeSessionStorage(array(), $pdo_session_handler);
$session = new Session($storage);
$session->start();

$GLOBALS["base_user_id"] = $session->get("user_id");

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


// call this function on a path to remove the directory itself along with everything inside of it (Do note that this function does not work with URLs, only paths).
function deleteDir($dir) {
$it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
$files = new RecursiveIteratorIterator($it,
RecursiveIteratorIterator::CHILD_FIRST);
foreach($files as $file) {
if ($file->isDir()){
rmdir($file->getRealPath());
} else {
unlink($file->getRealPath());
}
}
rmdir($dir);
}


?>