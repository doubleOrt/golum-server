<?php
/* we make a call to this page whenever a user wants types in the send to friend modal */

require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_GET["friend_name"]) && isset($_GET["post_id"]) && is_numeric($_GET["post_id"])) {

$echo_arr = [""];


$all_friends = $con->query("select contact from contacts where contact_of = ". $_SESSION["user_id"])->fetchAll();

foreach($all_friends as $friend) {
$friend_arr = $con->query("select id, first_name, last_name, avatar_picture from users where id = ". $friend["contact"])->fetch();	
$friend_fullname = $friend_arr["first_name"] . " " . $friend_arr["last_name"];

if(strpos(strtolower($friend_fullname),strtolower($_GET["friend_name"])) !== false) {

// if target has delete or deactivated their account, or the current user has been blocked by the target.
if($con->query("select id from account_states where user_id = ". $friend_arr["id"])->fetch()[0] != "" || $con->query("select id from blocked_users where user_ids = '".$friend_arr["id"]. "-" . $_SESSION["user_id"]."'")->fetch() != "") {	
continue;
}

$disable_button_class = "";
// if the user has already sent the current iteration this post, disable the button.
if($con->query("select id from notifications where notification_from = ". $_SESSION["user_id"] ." and notification_to = ". $friend_arr["id"] ." and type = 4 and extra = ". $_GET["post_id"])->fetch()[0] != "") {
$disable_button_class = "disabledButton";
}


$friend_avatar_arr = $con->query("SELECT positions, rotate_degree FROM avatars WHERE id_of_user = ". $friend_arr["id"] ." order by id desc limit 1")->fetch();

$friend_avatar_positions = explode(",",$friend_avatar_arr["positions"]);
//if avatar positions does not exist 
if(count($friend_avatar_positions) < 2) {
$friend_avatar_positions = [0,0];
}

$random_num = rand(10000000,100000000);

$echo_arr[0] .= "
<div class='sendToFriendSingleRow row'>
<div class='sendToFriendAvatarContainerParent col l1 m1 s2'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='".$friend_arr["id"]."' style='width:45px;height:45px;'>
". ($friend_arr["avatar_picture"] == "" ? letter_avatarize($friend_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$friend_avatar_positions[0]."%;margin-left:".$friend_avatar_positions[1]."%;'>
<div class='avatarRotateDiv' data-rotate-degree='".$friend_avatar_arr["rotate_degree"]."'>
<img id='friendAvatar".$random_num."' class='avatarImages sendToFriendAvatarImages' src='".$friend_arr["avatar_picture"]."' alt='Image'/>
</div></div>") ."
</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div>
<div class='col l11 m11 s10 sendToFriendRightCol'>
<a href='#modal1' class='friendFullName modal-trigger showUserModal view-user commonLinkMainColor' data-user-id='".$friend_arr["id"]."'>". $friend_fullname ."</a><!-- end .friendFullName -->
<a href='#' class='waves-effect wavesCustom btn commonButton sendToFriendButton ". $disable_button_class ."' data-user-id='".$friend_arr["id"]."'>". ($disable_button_class == "" ? "SEND" : "SENT") ."</a>
</div>
</div><!-- end .sendToFriendSingleRow -->
";

}	
}

// if there were no matches for the user's search
if($echo_arr[0] == "") {
$echo_arr[0] = "<span id='sendToFriendModalPlaceholder' class='emptyNowPlaceholder aaaaaaColor'>No Results For \"". htmlspecialchars($_GET["friend_name"]) ."\"!</span>";
}

echo json_encode($echo_arr);
}




?>