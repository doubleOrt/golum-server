<?php

require_once "common_requires.php";
require_once "logged_in_importants.php";



if(isset($_FILES["new_background"])) {

//this is the path of the image after upload and before renaming the file
$upload_to = "../users/" . $_SESSION["user_id"] . "/media/backgrounds/";

//the extension of the uploaded file 
$upload_pathinfo = strtolower(pathinfo($upload_to . basename($_FILES["new_background"]["name"]),PATHINFO_EXTENSION));

//check if file is smaller than 5mb
if($_FILES["new_background"]["size"] < 5000000) {
//check if file is a jpg, png, or gif.
if($upload_pathinfo == "jpeg" || $upload_pathinfo == "jpg" || $upload_pathinfo == "png" || $upload_pathinfo == "gif") {
	
//move the uploaded file
if(move_uploaded_file($_FILES["new_background"]["tmp_name"],$upload_to . basename($_FILES["new_background"]["name"]))) {

//what is going to be the id of the new avatar picture ? we get this by getting the id of the last row and adding 1 to it.
$what_id_query = $con->query("SELECT * FROM backgrounds where id_of_user = ". $_SESSION["user_id"])->fetchAll();

$what_id = count($what_id_query) > 0 ? $what_id_query[count($what_id_query)-1]["id"] + 1 : 1;

//this is the new path to the avatar.
$new_path = "users/". $_SESSION["user_id"] ."/media/backgrounds/" . "$what_id" . "." . $upload_pathinfo;

//rename the file and check if it is successful
if(rename($upload_to . basename($_FILES["new_background"]["name"]) , "../" . $new_path)) {

//add a new row to the backgrounds table, check if it is successful.
$insert_into_avatars = $con->query("INSERT INTO backgrounds (id_of_user,background_path,date_of) values('". $_SESSION["user_id"] ."','". $new_path ."','".date("Y/m/d H:i")."')");

//if query was successful
if($insert_into_avatars->rowCount() > 0) {
//change the user's background column in the users table
$update_users = $con->query("UPDATE USERS SET background_path = '". $new_path ."' where id = ".$_SESSION["user_id"]);

//if query was successful
if($update_users->rowCount() > 0) {
	
//echo some js (a toast and some other things to update the user's profile picture until the page loads again.
echo "
$('.userView').css({'background':'url(".$new_path.")','background-size':'cover','background-position':'center center'});
$('#thisUserModalContentChild').css({'background':'url(".$new_path.")','background-size':'cover','background-position':'center center'});
Materialize.toast('Background Changed',5000,'green');";
die();			
}	
else {
echo "Materialize.toast('Something Went Wrong, Sorry!',6000,'red')";
die();			
}
}
// if there was an sql error
else {
echo "Materialize.toast('Something Went Wrong, Sorry!',6000,'red')";
die();		
}

}
//if there was an error while inserting the row into :avatars".
else {
echo "Materialize.toast('Something Went Wrong, Sorry!',6000,'red')";
die();			
}	

}
//if there is an error uploading the file
else {
echo "Materialize.toast('Something Went Wrong, Sorry!',6000,'red')";
die();	
}
	
}
//if filetype is not one of the filetypes specified above, alert the user.
else {
echo "Materialize.toast('Image Type Must Be Either \"JPEG\", \"JPG\" \"PNG\" Or \"GIF\" !',6000,'red')";
die();
}

}
//if file is larger than 5mb
else {
echo "Materialize.toast('Image Size Must Be Smaller Than 5MB',6000,'red')";
die();	
}
	
}


?>