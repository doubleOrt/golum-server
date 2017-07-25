<?php

require_once "common_requires.php";
require_once "logged_in_importants.php";

/* index 0 should be the path to the uploaded background in case of a success, and empty in a failure case. The second index should be the error message in failure cases, 
and should be left empty in a successful upload. */
$echo_arr = ["" , ""];

$MAXIMUM_USER_PROFILE_BACKGROUND_IMAGE_SIZE = 5000000;

$MAXIMUM_DAILY_BACKGROUND_UPLOADS = 15;

if(isset($_FILES["new_background"])) {

//this is the path of the image after upload and before renaming the file
$upload_to = "../users/" . $_SESSION["user_id"] . "/media/backgrounds/";

//the extension of the uploaded file 
$upload_pathinfo = strtolower(pathinfo($upload_to . basename($_FILES["new_background"]["name"]),PATHINFO_EXTENSION));

//check if file is smaller than 5mb
if($_FILES["new_background"]["size"] < $MAXIMUM_USER_PROFILE_BACKGROUND_IMAGE_SIZE) {
//check if file is a jpg, png, or gif.
if($upload_pathinfo == "jpeg" || $upload_pathinfo == "jpg" || $upload_pathinfo == "png" || $upload_pathinfo == "gif") {
	
//move the uploaded file
if(move_uploaded_file($_FILES["new_background"]["tmp_name"],$upload_to . basename($_FILES["new_background"]["name"]))) {

//what is going to be the id of the new avatar picture ? we get this by getting the id of the last row and adding 1 to it.
$what_id_query = custom_pdo("SELECT * FROM backgrounds where id_of_user = :base_user_id", [":base_user_id" => $_SESSION["user_id"]])->fetchAll();

$what_id = count($what_id_query) > 0 ? $what_id_query[count($what_id_query)-1]["id"] + 1 : 1;

//this is the new path to the avatar.
$new_path = "users/". $_SESSION["user_id"] ."/media/backgrounds/" . "$what_id" . "." . $upload_pathinfo;

//rename the file and check if it is successful
if(rename($upload_to . basename($_FILES["new_background"]["name"]) , "../" . $new_path)) {

$daily_background_uploads_limit_exceeded = daily_background_uploads_limit_exceeded($MAXIMUM_DAILY_BACKGROUND_UPLOADS);

// the background upload limit has not been exceeded.
if($daily_background_uploads_limit_exceeded === false) {
//add a new row to the backgrounds table, check if it is successful.
$insert_into_backgrounds = custom_pdo("INSERT INTO backgrounds (id_of_user,background_path,date_of) values(:base_user_id, :new_path, :date_of)", [":base_user_id" => $_SESSION["user_id"], ":new_path" => $new_path, ":date_of" => date("Y/m/d H:i")]);

//if query was successful
if($insert_into_backgrounds->rowCount() > 0) {
//change the user's background column in the users table
$update_users = custom_pdo("UPDATE USERS SET background_path = :new_path where id = :base_user_id", [":new_path" => $new_path, ":base_user_id" => $_SESSION["user_id"]]);

//if query was successful
if($update_users->rowCount() > 0) {
// set the first index of $echo_arr to the path of the uploaded image.	
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
// the background upload limit has been exceeded.
else {
$echo_arr[1] = "You are uploading too many background images, which costs us a lot! Please wait until tomorrow before you upload another background image!";	
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
//if file is larger than the limit
else {
$echo_arr[1] = "Image Size Must Be Smaller Than ". ($MAXIMUM_USER_PROFILE_BACKGROUND_IMAGE_SIZE/1000000) . "MB";
}
	
}
else {
$echo_arr[1] = "Something Went Wrong, Sorry!";	
}


/* if the user has uploaded more than $amount images in less than $period seconds, returns the time the user has to wait before they can upload another image in seconds, otherwise
returns false. */
function daily_background_uploads_limit_exceeded($limit) {
global $con;	
$background_uploads_today = custom_pdo("SELECT count(*) from backgrounds where id_of_user = :base_user_id and date_of like concat(:date_of,' %')", [":base_user_id" => $_SESSION["user_id"], ":date_of" => date("Y/m/d")])->fetch()[0];
return ($background_uploads_today >= $limit ? true : false);
}



echo json_encode($echo_arr);


unset($con);


?>