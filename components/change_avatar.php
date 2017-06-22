<?php
//users use this file whenever they change their avatar, this file is called via ajax.

require_once "common_requires.php";
require_once "logged_in_importants.php";




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
if(move_uploaded_file($_FILES["new_avatar"]["tmp_name"],$upload_to . basename($_FILES["new_avatar"]["name"]))) {

//what is going to be the id of the new avatar picture ? we get this by getting the id of the last row and adding 1 to it.
$what_id_query = $con->query("SELECT id FROM AVATARS where id_of_user = ". $_SESSION["user_id"])->fetchAll();

$what_id = count($what_id_query) > 0 ? $what_id_query[count($what_id_query)-1]["id"] + 1 : 1;

//this is the new path to the avatar.
$new_path = "users/". $_SESSION["user_id"] ."/media/" . "$what_id" . $upload_pathinfo;

//rename the file and check if it is successful
if(rename($upload_to . basename($_FILES["new_avatar"]["name"]) , "../" . $new_path)) {

//add a new row to the avatars table, check if it is successful.
$insert_into_avatars = $con->query("INSERT INTO AVATARS (id_of_user,avatar_path,date) values('". $_SESSION["user_id"] ."','". $new_path ."','".date("Y/m/d H:i")."')");

//if query was successful
if($insert_into_avatars->rowCount() > 0) {
//change the user's avatar_picture column in the users table
$update_users = $con->query("UPDATE USERS SET avatar_picture = '". $new_path ."' where id = ".$_SESSION["user_id"]);

//if query was successful
if($update_users->rowCount() > 0) {

	
//echo some js (a toast and some other things to update the user's profile picture until the page loads again.
echo "
if(!$('#userAvatarImage').length) {
// this to are used when the user is uploading his first avatar, meaning the image refs in the loop below wouldn't work because they would be undefined, so we need to use these, and please be sure to change the html of any other base user avatar picture that is persistent on the screen.
$('.userModalAvatarImageContainer').html(\"<div class='userModalAvatarImage'><div class='rotateContainer' style='margin-top:0%;margin-left:0%;'><div class='userAvatarRotateDiv'><img id='userAvatarImage' class='avatarImages' src='".$new_path."' alt='Image'/></div></div><div class='changeAvatarContainer'><i class='material-icons'>camera_alt</i><form action='#' method='post' enctype='multipart/form-data' style='display:none;' id='changeAvatarForm'><input id='changeAvatarInput' type='file' name='new_avatar_image'/></form></div><div id='repositionAvatarDiv'><i class='material-icons repositionAvatar' data-direction='up' style='top:0;left:50%;transform:translate(-50%,0) scale(1,.8) rotate(270deg);'>trending_flat</i><i class='material-icons repositionAvatar' data-direction='right' style='right:0;top:50%;transform:translate(0%,-50%) scale(.8,1) rotate(0deg);'>trending_flat</i><i class='material-icons repositionAvatar' data-direction='down' style='left:50%;bottom:0;transform:translate(-50%,0%) scale(1,.8) rotate(90deg);'>trending_flat</i><i class='material-icons repositionAvatar' data-direction='left' style='left:0%;top:50%;transform:translate(0%,-50%) scale(.8,1) rotate(180deg);'>trending_flat</i></div></div><button id='rotateAvatarButton' data-change-name='avatar_rotation'><i class='material-icons'>rotate_right</i></button>\");
$('#userAvatarContainer').html(\"<div class='rotateContainer' style='position:relative;transform:none;display:inline-block;width:100%;height:100%;margin-top:0%;margin-left:0%;'><div class='userAvatarRotateDiv'><img id='userAvatar' class='avatarImages' src='".$new_path."' alt='hello'/></div></div><!-- .end #rotateContainer -->\");
}


$('.baseUserAvatarRotateDivs').each(function(){	
$(this).attr('data-rotate-degree','0');
$(this).parent().css({'margin-top':'0px','margin-left':'0px'});
$(this).css({'top':'0%','left':'0%'});
$(this).find('img').attr('src','". $new_path ."');
$(this).css('transform','rotate(' + $(this).attr('data-rotate-degree') + 'deg)');	
fitToParent('#' + $(this).find('img').attr('id'));
});


Materialize.toast('Avatar Changed',5000,'green')";
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