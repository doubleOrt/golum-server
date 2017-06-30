<?php

require_once "handleTagsFunction.php";

function get_comment($comment_arr) {
global $con;

// if target has delete or deactivated their account, or the current user has been blocked by the target.
if($con->query("select id from account_states where user_id = ". $comment_arr["user_id"])->fetch()[0] != "" || $con->query("select id from blocked_users where user_ids = '".$comment_arr["user_id"]. "-" . $_SESSION["user_id"]."'")->fetch() != "") {	
return "";
}

$commenter_arr = $con->query("select id,first_name, last_name, avatar_picture from users where id = ". $comment_arr["user_id"])->fetch();
$commenter_avatar_arr = $con->query("SELECT positions, rotate_degree FROM avatars WHERE id_of_user = ". $comment_arr["user_id"] ." order by id desc limit 1")->fetch();
$commenter_avatar_positions = explode(",",$commenter_avatar_arr["positions"]);
//if avatar positions does not exist 
if(count($commenter_avatar_positions) < 2) {
$commenter_avatar_positions = [0,0];
}


//if the current comment is actually a comment reply inside another comment reply.
$is_reply_to_markup = "";
if(isset($comment_arr["is_reply_to"])) {
if($comment_arr["is_reply_to"] != 0) {
$is_reply_to_user_arr = $con->query("select first_name, last_name from users where id = ". $comment_arr["is_reply_to"])->fetch();
$is_reply_to_markup = "<a href='#modal1' class='replyToFullname modal-trigger showUserModal view-user' data-user-id='". $comment_arr["is_reply_to"] ."'>". $is_reply_to_user_arr["first_name"] . " " . $is_reply_to_user_arr["last_name"] ."</a> ";	
} 	
}


$comment_by_base_user = ($comment_arr["user_id"] == $_SESSION["user_id"]);
$comment_by_poster = ($comment_arr["user_id"] == $comment_arr["original_post_by"]);


return [
"comment_id" => htmlspecialchars($comment_arr["id"]),
"comment_text" => htmlspecialchars($comment_arr["comment"]),
"comment_time_string" => time_to_string($comment_arr["time"]),
"comment_replies_num" => htmlspecialchars($comment_arr["replies"]),
"comment_upvotes_num" => htmlspecialchars($comment_arr["upvotes"]),
"comment_downvotes_num" => htmlspecialchars($comment_arr["downvotes"]),
"base_user_upvoted_comment" => ($comment_arr["base_user_opinion"] === "0" ? 1 : 0),
"base_user_downvoted_comment" => ($comment_arr["base_user_opinion"] === "1" ? 1 : 0),
"comment_by_base_user" => htmlspecialchars($comment_by_base_user),
"comment_by_poster" => htmlspecialchars($comment_by_poster),
"comment_owner_vote" => htmlspecialchars($comment_arr["option_index"]),
"comment_owner_info" => [
	"id" => htmlspecialchars($commenter_arr["id"]),
	"first_name" => htmlspecialchars($commenter_arr["first_name"]),
	"last_name" => htmlspecialchars($commenter_arr["last_name"]),
	"avatar_picture" => htmlspecialchars($commenter_arr["avatar_picture"]),
	"avatar_rotate_degree" => htmlspecialchars($commenter_avatar_arr["rotate_degree"]),
	"avatar_positions" => $commenter_avatar_positions
	]
];
}









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

?>