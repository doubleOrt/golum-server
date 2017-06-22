<?php

$con = new PDO("mysql:host=localhost;dbname=ortify;charset=latin1","root","");
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

session_start();


//without this snippet, nothing would be pushed into the "toasts" session on the first push because it would be equal to null and you can't push to null. 
if(!isset($_SESSION["toasts"])) {
$_SESSION["toasts"] = [];	 	
}

?>