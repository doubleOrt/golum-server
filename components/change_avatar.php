<?php
//users use this file whenever they change their avatar, this file is called via ajax.

require_once "common_requires.php";
require_once "logged_in_importants.php";

$MAXIMUM_USER_PROFILE_AVATAR_IMAGE_SIZE = 5000000;

$MAXIMUM_DAILY_AVATAR_UPLOADS = 15;



$echo_arr = ["" , ""];

if(isset($_FILES["new_avatar"])) {


//this is the path of the image after upload and before renaming the file
$upload_to = "../users/" . $_SESSION["user_id"] . "/media/";

//the extension of the uploaded file 
$upload_pathinfo = strtolower(pathinfo($upload_to . basename($_FILES["new_avatar"]["name"]),PATHINFO_EXTENSION));

//check if file is smaller than 5mb
if($_FILES["new_avatar"]["size"] < 5000000) {
//check if file is a jpg, png, or gif.
if($upload_pathinfo == "jpeg" || $upload_pathinfo == "jpg" || $upload_pathinfo == "png" || $upload_pathinfo == "gif") {
	
//move the uploaded file
if(move_uploaded_file($_FILES["new_avatar"]["tmp_name"], $upload_to . basename($_FILES["new_avatar"]["name"]))) {

//what is going to be the id of the new avatar picture ? we get this by getting the id of the last row and adding 1 to it.
$what_id_query = custom_pdo("SELECT id FROM AVATARS where id_of_user = :base_user_id", [":base_user_id" => $_SESSION["user_id"]])->fetchAll();

$what_id = count($what_id_query) > 0 ? $what_id_query[count($what_id_query)-1]["id"] + 1 : 1;

//this is the new path to the avatar.
$new_path = "users/". $_SESSION["user_id"] ."/media/" . "$what_id" . $upload_pathinfo;

//rename the file and check if it is successful
if(rename($upload_to . basename($_FILES["new_avatar"]["name"]) , "../" . $new_path)) {

$daily_avatar_uploads_limit_exceeded = daily_avatar_uploads_limit_exceeded($MAXIMUM_DAILY_AVATAR_UPLOADS);

// the avatar upload limit has not been exceeded.
if($daily_avatar_uploads_limit_exceeded === false) {

//add a new row to the avatars table, check if it is successful.
$insert_into_avatars = custom_pdo("INSERT INTO AVATARS (id_of_user,avatar_path,date_of) values(:base_user_id, :new_path, :date_of)", [":base_user_id" => $_SESSION["user_id"], ":new_path" => $new_path, ":date_of" => date("Y/m/d H:i")]);

//if query was successful
if($insert_into_avatars->rowCount() > 0) {
//change the user's avatar_picture column in the users table
$update_users = custom_pdo("UPDATE USERS SET avatar_picture = :new_path where id = :base_user_id", [":new_path" => $new_path, ":base_user_id" => $_SESSION["user_id"]]);

//if query was successful
if($update_users->rowCount() > 0) {
$echo_arr[0] = htmlspecialchars($new_path, ENT_QUOTES, "utf-8");
}	
else {
$echo_arr[1] = "Something Went Wrong, Sorry!";
}
}
// if there was an sql error
else {
$echo_arr[1] = "Something Went Wrong, Sorry!";
}
}
// the avatar upload limit has been exceeded.
else {
$echo_arr[1] = "You are uploading too many avatars, which costs us a lot! Please wait until tomorrow before you upload another avatar!";	
}

}
//if there was an error while inserting the row into :avatars".
else {
$echo_arr[1] = "Something Went Wrong, Sorry!";
}	

}
//if there is an error uploading the file
else {
$echo_arr[1] = "Something Went Wrong, Sorry!";
}
	
}
//if filetype is not one of the filetypes specified above, alert the user.
else {
$echo_arr[1] = "Image Type Must Be Either \"JPEG\", \"JPG\" \"PNG\" Or \"GIF\" !";
}

}
//if file is larger than 5mb
else {
$echo_arr[1] = "Image Size Must Be Smaller Than ". ($MAXIMUM_USER_PROFILE_AVATAR_IMAGE_SIZE/1000000) ."MB";
die();	
}
	
}


echo json_encode($echo_arr);



/* if the user has uploaded more than $amount images in less than $period seconds, returns the time the user has to wait before they can upload another image in seconds, otherwise
returns false. */
function daily_avatar_uploads_limit_exceeded($limit) {
global $con;	
$background_uploads_today = custom_pdo("SELECT count(*) from avatars where id_of_user = :base_user_id and date_of like concat(:date_of,' %')", [":base_user_id" => $_SESSION["user_id"], ":date_of" => date("Y/m/d")])->fetch()[0];
return ($background_uploads_today >= $limit ? true : false);
}


unset($con);

?>