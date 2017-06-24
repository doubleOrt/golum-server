<?php
//we make an ajax call to this page everytime a user changes his profile info , gender, country or birthdate.

require_once "common_requires.php";
require_once "logged_in_importants.php";



$echo_arr = [];



// if user wants to change his gender.
if(isset($_GET["gender"])) {
if($_GET["gender"] != "") {	
$new_gender = $_GET["gender"];
$prepared = $con->prepare("update users set gender = :gender where id = ".$_SESSION["user_id"]);
$prepared->bindParam(":gender",$new_gender);
$prepared->execute();
}
}

// if user wants to change his country.
if(isset($_GET["country"])) {
if($_GET["country"] != "") {		
$new_country = $_GET["country"];
$prepared = $con->prepare("update users set country = :country where id = ".$_SESSION["user_id"]);
$prepared->bindParam(":country",$new_country);
$prepared->execute();
}
}

// when user wants to change his birthdate.
if(isset($_GET["birthdate"])) {
if($_GET["birthdate"] != "") {	
$new_birthdate = $_GET["birthdate"];	
$prepared = $con->prepare("update users set birthdate = :birthdate where id = ".$_SESSION["user_id"]);
$prepared->bindParam(":birthdate",$new_birthdate);
$prepared->execute();
$echo_arr[0] = date_diff(date_create(date("Y-m-d")),date_create(str_replace(",","",$new_birthdate)))->y;
}
}


/* when a user repositions his/her avatar image, we inser the new positions into a table in our database */
if(isset($_GET["avatar_positions"])) {
if($_GET["avatar_positions"] != "") {		
$con->exec("update avatars set positions = '". ($_GET["avatar_positions"][0] . "," . $_GET["avatar_positions"][1]) ."' where id_of_user = ". $_SESSION["user_id"] ." order by id desc limit 1");
}
}


if(isset($_GET["avatar_rotation"]) && is_integer(intval($_GET["avatar_rotation"]))) {
if($_GET["avatar_rotation"] != "") {
$con->exec("update avatars set rotate_degree = '". $_GET["avatar_rotation"] ."' ". (!isset($_GET["avatar_positions"]) ? ",positions = '0,0'" : "") ." where id_of_user = ". $_SESSION["user_id"] ." order by id desc limit 1");		
}	
}

echo json_encode($echo_arr);



?>