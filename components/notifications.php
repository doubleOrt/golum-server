<?php
//we make a call to this page whenever the user opens the notificatons modal.

require_once "common_requires.php";
require_once "logged_in_importants.php";


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


if(isset($_GET["last_notification_id"]) && is_numeric($_GET["last_notification_id"])) {

// when the user wants to see the first 10 notifs	
if($_GET["last_notification_id"] == 0) {
$notifications_arr = $con->query("select * from (select *, (count(*) - 1) and_others, @rn:=@rn+1 AS new_id from (select * from notifications) t1, (SELECT @rn:=0) t2 where notification_to = ". $_SESSION["user_id"] ." group by type, extra, read_yet) t3 order by id desc limit 10")->fetchAll();	
}
// when the user is scrolling
else {
$notifications_arr = $con->query("select * from (select *, (count(*) - 1) and_others, @rn:=@rn+1 AS new_id from (select * from notifications) t1, (SELECT @rn:=0) t2 where notification_to = ". $_SESSION["user_id"] ." group by type, extra, read_yet) t3 where new_id > ". $_GET["last_notification_id"] ." order by id desc limit 10")->fetchAll();	
}


$echo_arr = [""];

if(count($notifications_arr) > 0) {
// update read yets.
$notification_where = "";
for($i = 0;$i < count($notifications_arr);$i++) {
if($i != 0) {
$notification_where .= " or ";
}
$notification_where .= "(type = ". $notifications_arr[$i]["type"] ." and extra = ". $notifications_arr[$i]["extra"] .")";
}
$con->exec("update notifications set read_yet = ". time() ." where read_yet = 0 and notification_to = ". $_SESSION["user_id"] ." and (". $notification_where .")");

for($i = 0;$i<count($notifications_arr);$i++) {
$echo_arr[0] .= get_notification_markup($notifications_arr[$i]);
} 
}
//if there are no notifications, tell the user there are no notifications
else if($_GET["last_notification_id"] == 0) {
$echo_arr[0] .= "<div class='emptyNowPlaceholder'>
<i class='material-icons'>info</i>
<br>
No Notifications Yet!</div>";	
}

echo json_encode($echo_arr);

}


function get_notification_markup($notification_arr) {

global $con;

$sender_arr = $con->query("select first_name, last_name, avatar_picture from users where id = ". $notification_arr["notification_from"])->fetch();

$sender_avatar_arr = $con->query("SELECT positions, rotate_degree FROM avatars WHERE id_of_user = ". $notification_arr["notification_from"] ." order by id desc limit 1")->fetch();

$sender_avatar_positions = explode(",",$sender_avatar_arr["positions"]);
//if avatar positions does not exist 
if(count($sender_avatar_positions) < 2) {
$sender_avatar_positions = [0,0];
}

$sender_full_name = $sender_arr["first_name"] . " " . $sender_arr["last_name"];

$random_num = rand(1000000,10000000);


// user reacting to your post (voting and like/disliking)
if($notification_arr["type"] == 1) {
return "
<div class='singleNotification myHorizontalCardStyle cardStyles scaleHorizontallyCenteredItem myGreyBackgroundOnActive openSinglePost modal-trigger' data-read-yet='". ($notification_arr["read_yet"] != "" ? "true" : "false") ."' data-target='singlePostModal' data-notification-id='". $notification_arr["new_id"] ."' data-actual-post-id='". $notification_arr["extra"] ."'>

". ($notification_arr["read_yet"] != "" ? "<div class='focusStealer'><i class='material-icons'>check</i></div>" : "") ."

<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". $notification_arr["notification_from"] ."'>
". ($sender_arr["avatar_picture"] == "" ? letter_avatarize($sender_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$sender_avatar_positions[0]."%;margin-left:".$sender_avatar_positions[1]."%;'>
<div class='avatarRotateDiv' data-rotate-degree='".$sender_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages notificationAvatarImages' src='".$sender_arr["avatar_picture"]."' alt='Image'/>
</div>
</div>") ."

</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName modal-trigger view-user showUserModal' data-user-id='". $notification_arr["notification_from"] ."'>". $sender_full_name ."</a>  ". ($notification_arr["and_others"] > 0 ?  "And " . $notification_arr["and_others"] . " Other" . ($notification_arr["and_others"] == 1 ? "" : "s") : "") ." Reacted To Your Post.
</div>
</div>

<div class='notificationTime'>". time_to_string($notification_arr["time"]) ."</div>
</div>";
}


// user commented on your post
if($notification_arr["type"] == 2) {
return "
<div class='singleNotification myHorizontalCardStyle cardStyles scaleHorizontallyCenteredItem myGreyBackgroundOnActive showPostComments modal-trigger' data-target='commentsModal' data-notification-id='". $notification_arr["new_id"] ."' data-pin-comment-to-top='". $notification_arr["extra2"]  ."' data-actual-post-id='". $notification_arr["extra"] ."'>

<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". $notification_arr["notification_from"] ."'>
". ($sender_arr["avatar_picture"] == "" ? letter_avatarize($sender_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$sender_avatar_positions[0]."%;margin-left:".$sender_avatar_positions[1]."%;'>
<div class='avatarRotateDiv' data-rotate-degree='".$sender_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages notificationAvatarImages' src='".$sender_arr["avatar_picture"]."' alt='Image'/>
</div>
</div>") ."

</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName modal-trigger view-user showUserModal' data-user-id='". $notification_arr["notification_from"] ."'>". $sender_full_name ."</a> ". ($notification_arr["and_others"] > 0 ?  "And " . $notification_arr["and_others"] . " Other" . ($notification_arr["and_others"] == 1 ? "" : "s") : "") ." Commented On Your <a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='". $notification_arr["extra"] ."'>Post</a>.
</div>
</div>

<div class='notificationTime'>". time_to_string($notification_arr["time"]) ."</div>
</div>";
}

// replies
if($notification_arr["type"] == 3) {
return "
<div class='singleNotification myHorizontalCardStyle cardStyles scaleHorizontallyCenteredItem myGreyBackgroundOnActive  modal-trigger addReplyToComment' data-target='commentRepliesModal' data-notification-id='". $notification_arr["new_id"] ."' data-pin-comment-to-top='". $notification_arr["extra3"]  ."' data-comment-id='". $notification_arr["extra"] ."' data-actual-post-id='". $notification_arr["extra2"] ."'>

<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". $notification_arr["notification_from"] ."'>
". ($sender_arr["avatar_picture"] == "" ? letter_avatarize($sender_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$sender_avatar_positions[0]."%;margin-left:".$sender_avatar_positions[1]."%;'>
<div class='avatarRotateDiv' data-rotate-degree='".$sender_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages notificationAvatarImages' src='".$sender_arr["avatar_picture"]."' alt='Image'/>
</div>
</div>") ."

</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName modal-trigger view-user showUserModal' data-user-id='". $notification_arr["notification_from"] ."'>". $sender_full_name ."</a> ". ($notification_arr["and_others"] > 0 ?  "And " . $notification_arr["and_others"] . " Other" . ($notification_arr["and_others"] == 1 ? "" : "s") : "") ." Replied To Your Comment. <br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='". $notification_arr["extra2"] ."'>View Post</a>)</span>
</div>
</div>

<div class='notificationTime'>". time_to_string($notification_arr["time"]) ."</div>
</div>";
}


// user sent you a post (wants to share a post with you)
if($notification_arr["type"] == 4) {
return "
<div class='singleNotification myHorizontalCardStyle cardStyles scaleHorizontallyCenteredItem myGreyBackgroundOnActive openSinglePost modal-trigger' data-target='singlePostModal' data-notification-id='". $notification_arr["new_id"] ."' data-actual-post-id='". $notification_arr["extra"] ."'>

<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". $notification_arr["notification_from"] ."'>
". ($sender_arr["avatar_picture"] == "" ? letter_avatarize($sender_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$sender_avatar_positions[0]."%;margin-left:".$sender_avatar_positions[1]."%;'>
<div class='avatarRotateDiv' data-rotate-degree='".$sender_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages notificationAvatarImages' src='".$sender_arr["avatar_picture"]."' alt='Image'/>
</div>
</div>") ."

</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName modal-trigger view-user showUserModal' data-user-id='". $notification_arr["notification_from"] ."'>". $sender_full_name ."</a> Wants To Share a Post With You.
</div>
</div>

<div class='notificationTime'>". time_to_string($notification_arr["time"]) ."</div>
</div>";
}

// user favorited your post
if($notification_arr["type"] == 5) {
return "
<div class='singleNotification myHorizontalCardStyle cardStyles scaleHorizontallyCenteredItem myGreyBackgroundOnActive openSinglePost modal-trigger' data-target='singlePostModal' data-notification-id='". $notification_arr["new_id"] ."' data-actual-post-id='". $notification_arr["extra"] ."'>

<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". $notification_arr["notification_from"] ."'>
". ($sender_arr["avatar_picture"] == "" ? letter_avatarize($sender_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$sender_avatar_positions[0]."%;margin-left:".$sender_avatar_positions[1]."%;'>
<div class='avatarRotateDiv' data-rotate-degree='".$sender_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages notificationAvatarImages' src='".$sender_arr["avatar_picture"]."' alt='Image'/>
</div>
</div>") ."

</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName modal-trigger view-user showUserModal' data-user-id='". $notification_arr["notification_from"] ."'>". $sender_full_name ."</a> ". ($notification_arr["and_others"] > 0 ?  "And " . $notification_arr["and_others"] . " Other" . ($notification_arr["and_others"] == 1 ? "" : "s") : "") ." Favorited Your Post.
</div>
</div>

<div class='notificationTime'>". time_to_string($notification_arr["time"]) ."</div>
</div>";
}

// user started following you
if($notification_arr["type"] == 6) {
return "
<div class='singleNotification myHorizontalCardStyle cardStyles scaleHorizontallyCenteredItem myGreyBackgroundOnActive modal-trigger view-user showUserModal' data-target='modal1' data-notification-id='". $notification_arr["new_id"] ."' data-user-id='". $notification_arr["notification_from"] ."'>

<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". $notification_arr["notification_from"] ."'>
". ($sender_arr["avatar_picture"] == "" ? letter_avatarize($sender_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$sender_avatar_positions[0]."%;margin-left:".$sender_avatar_positions[1]."%;'>
<div class='avatarRotateDiv' data-rotate-degree='".$sender_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages notificationAvatarImages' src='".$sender_arr["avatar_picture"]."' alt='Image'/>
</div>
</div>") ."

</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName modal-trigger view-user showUserModal' data-user-id='". $notification_arr["notification_from"] ."'>". $sender_full_name ."</a>  ". ($notification_arr["and_others"] > 0 ?  "And " . $notification_arr["and_others"] . " Other" . ($notification_arr["and_others"] == 1 ? "" : "s") : "") ." Started Following You.
</div>
</div>

<div class='notificationTime'>". time_to_string($notification_arr["time"]) ."</div>
</div>";
}

// user upvoted your comment to a post
if($notification_arr["type"] == 7) {
return "
<div class='singleNotification myHorizontalCardStyle cardStyles scaleHorizontallyCenteredItem myGreyBackgroundOnActive showPostComments modal-trigger' data-target='commentsModal' data-notification-id='". $notification_arr["new_id"] ."' data-pin-comment-to-top='". $notification_arr["extra2"]  ."' data-actual-post-id='". $notification_arr["extra"] ."'>

<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". $notification_arr["notification_from"] ."'>
". ($sender_arr["avatar_picture"] == "" ? letter_avatarize($sender_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$sender_avatar_positions[0]."%;margin-left:".$sender_avatar_positions[1]."%;'>
<div class='avatarRotateDiv' data-rotate-degree='".$sender_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages notificationAvatarImages' src='".$sender_arr["avatar_picture"]."' alt='Image'/>
</div>
</div>") ."

</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName modal-trigger view-user showUserModal' data-user-id='". $notification_arr["notification_from"] ."'>". $sender_full_name ."</a> ". ($notification_arr["and_others"] > 0 ?  "And " . $notification_arr["and_others"] . " Other" . ($notification_arr["and_others"] == 1 ? "" : "s") : "") ." Upvoted Your Comment.<br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='". $notification_arr["extra"] ."'>View Post</a>)</span>
</div>
</div>

<div class='notificationTime'>". time_to_string($notification_arr["time"]) ."</div>
</div>";
}

// user downvoted your comment to a post
if($notification_arr["type"] == 8) {
return "
<div class='singleNotification myHorizontalCardStyle cardStyles scaleHorizontallyCenteredItem myGreyBackgroundOnActive showPostComments modal-trigger' data-target='commentsModal' data-notification-id='". $notification_arr["new_id"] ."' data-pin-comment-to-top='". $notification_arr["extra2"]  ."' data-actual-post-id='". $notification_arr["extra"] ."'>

<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". $notification_arr["notification_from"] ."'>
". ($sender_arr["avatar_picture"] == "" ? letter_avatarize($sender_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$sender_avatar_positions[0]."%;margin-left:".$sender_avatar_positions[1]."%;'>
<div class='avatarRotateDiv' data-rotate-degree='".$sender_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages notificationAvatarImages' src='".$sender_arr["avatar_picture"]."' alt='Image'/>
</div>
</div>") ."

</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName modal-trigger view-user showUserModal' data-user-id='". $notification_arr["notification_from"] ."'>". $sender_full_name ."</a> ". ($notification_arr["and_others"] > 0 ?  "And " . $notification_arr["and_others"] . " Other" . ($notification_arr["and_others"] == 1 ? "" : "s") : "") ." Downvoted Your Comment.  <br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='". $notification_arr["extra"] ."'>View Post</a>)</span>
</div>
</div>

<div class='notificationTime'>". time_to_string($notification_arr["time"]) ."</div>
</div>";
}

// user upvoted your reply to a comment or reply
if($notification_arr["type"] == 9) {
return "
<div class='singleNotification myHorizontalCardStyle cardStyles scaleHorizontallyCenteredItem myGreyBackgroundOnActive  modal-trigger addReplyToComment' data-target='commentRepliesModal' data-notification-id='". $notification_arr["new_id"] ."' data-pin-comment-to-top='". $notification_arr["extra3"]  ."' data-comment-id='". $notification_arr["extra"] ."' data-actual-post-id='". $notification_arr["extra2"] ."'>

<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". $notification_arr["notification_from"] ."'>
". ($sender_arr["avatar_picture"] == "" ? letter_avatarize($sender_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$sender_avatar_positions[0]."%;margin-left:".$sender_avatar_positions[1]."%;'>
<div class='avatarRotateDiv' data-rotate-degree='".$sender_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages notificationAvatarImages' src='".$sender_arr["avatar_picture"]."' alt='Image'/>
</div>
</div>") ."

</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName modal-trigger view-user showUserModal' data-user-id='". $notification_arr["notification_from"] ."'>". $sender_full_name ."</a> ". ($notification_arr["and_others"] > 0 ?  "And " . $notification_arr["and_others"] . " Other" . ($notification_arr["and_others"] == 1 ? "" : "s") : "") ." Upvoted Your Reply.  <br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='". $notification_arr["extra"] ."'>View Post</a>)</span>
</div>
</div>

<div class='notificationTime'>". time_to_string($notification_arr["time"]) ."</div>
</div>";
}

// user downvoted your reply to a comment
if($notification_arr["type"] == 10) {
return "
<div class='singleNotification myHorizontalCardStyle cardStyles scaleHorizontallyCenteredItem myGreyBackgroundOnActive  modal-trigger addReplyToComment' data-target='commentRepliesModal' data-notification-id='". $notification_arr["new_id"] ."' data-pin-comment-to-top='". $notification_arr["extra3"]  ."' data-comment-id='". $notification_arr["extra"] ."' data-actual-post-id='". $notification_arr["extra2"] ."'>

<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". $notification_arr["notification_from"] ."'>
". ($sender_arr["avatar_picture"] == "" ? letter_avatarize($sender_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$sender_avatar_positions[0]."%;margin-left:".$sender_avatar_positions[1]."%;'>
<div class='avatarRotateDiv' data-rotate-degree='".$sender_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages notificationAvatarImages' src='".$sender_arr["avatar_picture"]."' alt='Image'/>
</div>
</div>") ."

</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName modal-trigger view-user showUserModal' data-user-id='". $notification_arr["notification_from"] ."'>". $sender_full_name ."</a> ". ($notification_arr["and_others"] > 0 ?  "And " . $notification_arr["and_others"] . " Other" . ($notification_arr["and_others"] == 1 ? "" : "s") : "") ." Downvoted Your Reply.  <br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='". $notification_arr["extra2"] ."'>View Post</a>)</span>
</div>
</div>

<div class='notificationTime'>". time_to_string($notification_arr["time"]) ."</div>
</div>";
}

// user reacted to a post you sent to him
if($notification_arr["type"] == 11) {
return "
<div class='singleNotification myHorizontalCardStyle cardStyles scaleHorizontallyCenteredItem myGreyBackgroundOnActive openSinglePost modal-trigger' data-target='singlePostModal' data-notification-id='". $notification_arr["new_id"] ."' data-actual-post-id='". $notification_arr["extra"] ."'>

<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". $notification_arr["notification_from"] ."'>
". ($sender_arr["avatar_picture"] == "" ? letter_avatarize($sender_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$sender_avatar_positions[0]."%;margin-left:".$sender_avatar_positions[1]."%;'>
<div class='avatarRotateDiv' data-rotate-degree='".$sender_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages notificationAvatarImages' src='".$sender_arr["avatar_picture"]."' alt='Image'/>
</div>
</div>") ."

</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName modal-trigger view-user showUserModal' data-user-id='". $notification_arr["notification_from"] ."'>". $sender_full_name ."</a> Reacted To The Post Sent By You.
</div>
</div>

<div class='notificationTime'>". time_to_string($notification_arr["time"]) ."</div>
</div>";
}

}


?>