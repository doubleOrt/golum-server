<?php
require_once "common_requires.php";



function get_comment($comment_arr) {
global $con, $SERVER_URL;

$commenter_arr = custom_pdo("select id,first_name, last_name, avatar_picture from users where id = :user_id", [":user_id" => $comment_arr["user_id"]])->fetch();
$commenter_avatar_arr = custom_pdo("SELECT positions, rotate_degree FROM avatars WHERE id_of_user = :user_id order by id desc limit 1", [":user_id" => $comment_arr["user_id"]])->fetch();
$commenter_avatar_positions = explode(",", htmlspecialchars($commenter_avatar_arr["positions"], ENT_QUOTES, "utf-8"));
//if avatar positions does not exist 
if(count($commenter_avatar_positions) < 2) {
$commenter_avatar_positions = [0,0];
}

//if the current comment is actually a comment reply inside another comment reply.
$is_reply_to = [];
if(isset($comment_arr["is_reply_to"])) {
if($comment_arr["is_reply_to"] != 0) {
$is_reply_to_user_arr = custom_pdo("select id,first_name, last_name from users where id = :is_reply_to", [":is_reply_to" => $comment_arr["is_reply_to"]])->fetch();
$is_reply_to[0] = $is_reply_to_user_arr["id"];
$is_reply_to[1] = $is_reply_to_user_arr["first_name"] . " " . $is_reply_to_user_arr["last_name"];
} 	
}


$comment_by_base_user = ($comment_arr["user_id"] == $_SESSION["user_id"]);
$comment_by_poster = ($comment_arr["user_id"] == $comment_arr["original_post_by"]);


return [
"comment_id" => htmlspecialchars($comment_arr["id"], ENT_QUOTES, "utf-8"),
"comment_text" => htmlspecialchars($comment_arr["comment"], ENT_QUOTES, "utf-8"),
"comment_time_string" => time_to_string($comment_arr["time"], ENT_QUOTES, "utf-8"),
"comment_replies_num" => htmlspecialchars($comment_arr["replies"], ENT_QUOTES, "utf-8"),
"comment_upvotes_num" => htmlspecialchars($comment_arr["upvotes"], ENT_QUOTES, "utf-8"),
"comment_downvotes_num" => htmlspecialchars($comment_arr["downvotes"], ENT_QUOTES, "utf-8"),
"base_user_upvoted_comment" => ($comment_arr["base_user_opinion"] === "0" ? 1 : 0),
"base_user_downvoted_comment" => ($comment_arr["base_user_opinion"] === "1" ? 1 : 0),
"is_reply_to" => $is_reply_to,
"comment_by_base_user" => htmlspecialchars($comment_by_base_user, ENT_QUOTES, "utf-8"),
"comment_by_poster" => htmlspecialchars($comment_by_poster, ENT_QUOTES, "utf-8"),
"comment_owner_vote" => htmlspecialchars($comment_arr["option_index"], ENT_QUOTES, "utf-8"),
"comment_owner_info" => [
	"id" => htmlspecialchars($commenter_arr["id"], ENT_QUOTES, "utf-8"),
	"first_name" => htmlspecialchars($commenter_arr["first_name"], ENT_QUOTES, "utf-8"),
	"last_name" => htmlspecialchars($commenter_arr["last_name"], ENT_QUOTES, "utf-8"),
	"avatar_picture" => ($commenter_arr["avatar_picture"] != "" ? $SERVER_URL . htmlspecialchars($commenter_arr["avatar_picture"], ENT_QUOTES, "utf-8") : ""),
	"avatar_rotate_degree" => ($commenter_avatar_arr["rotate_degree"] != "" ? htmlspecialchars($commenter_avatar_arr["rotate_degree"], ENT_QUOTES, "utf-8") : 0),
	"avatar_positions" => $commenter_avatar_positions
	]
];
}





?>