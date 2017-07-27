<?php
//we make an ajax call to this page everytime a user changes his profile info , gender, country or birthdate.

require_once "common_requires.php";
require_once "logged_in_importants.php";



$echo_arr = [];



// if user wants to change his gender.
if(isset($_POST["gender"])) {
if($_POST["gender"] != "") {	
$new_gender = $_POST["gender"];
$prepared = $con->prepare("update users set gender = :gender where id = ".$_SESSION["user_id"]);
$prepared->bindParam(":gender",$new_gender);
$prepared->execute();
}
}

// if user wants to change his country.
if(isset($_POST["country"])) {
if($_POST["country"] != "") {		
$new_country = $_POST["country"];
$prepared = $con->prepare("update users set country = :country where id = ".$_SESSION["user_id"]);
$prepared->bindParam(":country", $new_country);
$prepared->execute();
}
}

// when user wants to change his birthdate.
if(isset($_POST["birthdate"])) {
if($_POST["birthdate"] != "") {	
$new_birthdate = $_POST["birthdate"];	
$prepared = $con->prepare("update users set birthdate = :birthdate where id = ".$_SESSION["user_id"]);
$prepared->bindParam(":birthdate",$new_birthdate);
$prepared->execute();
$echo_arr[0] = date_diff(date_create(date("Y-m-d")),date_create(str_replace(",","",$new_birthdate)))->y;
}
}


// this snippet has to come before the avatar-positions one
if(isset($_POST["avatar_rotation"]) && filter_var($_POST["avatar_rotation"], FILTER_VALIDATE_INT) !== false) {
if($_POST["avatar_rotation"] != "") {
custom_pdo("update avatars set rotate_degree = :avatar_rotation, positions = '0,0' where id_of_user = :base_user_id order by id desc limit 1", [":avatar_rotation" => $_POST["avatar_rotation"], ":base_user_id" => $_SESSION["user_id"]]);		
}	
}


/* when a user repositions his/her avatar image, we inser the new positions into a table in our database */
if(isset($_POST["avatar_positions"]) && is_array($_POST["avatar_positions"]) && filter_var($_POST["avatar_positions"][0], FILTER_VALIDATE_INT) !== false && filter_var($_POST["avatar_positions"][1], FILTER_VALIDATE_INT) !== false) {
custom_pdo("update avatars set positions = :new_positions where id_of_user = :base_user_id order by id desc limit 1", [":new_positions" => ($_POST["avatar_positions"][0] . "," . $_POST["avatar_positions"][1]), ":base_user_id" => $_SESSION["user_id"]]);
}


echo json_encode($echo_arr);



unset($con);

?>