<?php

$con = new PDO("mysql:host=localhost;dbname=ortify;charset=latin1","root","");
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

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
            $results = $queryResults->fetchAll(PDO::FETCH_ASSOC);
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


?>