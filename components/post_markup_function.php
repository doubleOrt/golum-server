<?php
// you need this function to retrieve the markup of a post, you just need to pass the post's array (from the database) as the first argument and you are good to go.

require_once "handleTagsFunction.php";

function get_post_markup($post_arr) {
global $con;

// if target has delete or deactivated their account, or the current user has been blocked by the target.
if($con->query("select id from account_states where user_id = ". $post_arr["posted_by"])->fetch()[0] != "" || $con->query("select id from blocked_users where user_ids = '". $post_arr["posted_by"] . "-" . $_SESSION["user_id"] ."'")->fetch() != "") {	
return null;
}

// a call to this function means that the post is being viewed by 1 more user, so we need to update the post's total views.
$con->query("update posts set post_views = post_views + 1 where id = ". $post_arr["id"]);
// we also need to update the views for the post array passed to this function, for example i just shared a post, i make a call to this function, and without incrementing the array, i would see 0 views instead of 1.
$post_arr["post_views"] = ++$post_arr["post_views"];


$poster_arr = $con->query("select first_name, last_name, avatar_picture from users where id = ". $post_arr["posted_by"])->fetch();
$poster_avatar_arr = $con->query("SELECT positions, rotate_degree FROM avatars WHERE id_of_user = ". $post_arr["posted_by"] ." order by id desc limit 1")->fetch();
$poster_avatar_positions = explode(",", htmlspecialchars($poster_avatar_arr["positions"], ENT_QUOTES, "utf-8"));
//if avatar positions does not exist 
if(count($poster_avatar_positions) < 2) {
$poster_avatar_positions = [0,0];
}


$posted_by_base_user = ($post_arr["posted_by"] == $_SESSION["user_id"] ? true : false);
$base_user_already_voted = $con->query("select id from post_votes where post_id =". $post_arr["id"] ." and user_id = ". $_SESSION["user_id"])->fetch();


$post_file_types_arr = explode(",", htmlspecialchars($post_arr["file_types"], ENT_QUOTES, "utf-8"));	

return [
"post_id" => htmlspecialchars($post_arr["id"], ENT_QUOTES, "utf-8"),
"post_title" => htmlspecialchars($post_arr["title"], ENT_QUOTES, "utf-8"),
"post_views" => htmlspecialchars($post_arr["post_views"], ENT_QUOTES, "utf-8"),
"post_type" => htmlspecialchars($post_arr["type"], ENT_QUOTES, "utf-8"),
"post_file_types" => $post_file_types_arr,
"base_user_already_voted" => ($base_user_already_voted != "" ? true : false),
"posted_by_base_user" => $posted_by_base_user,
"post_owner_info" => [
		"id" => htmlspecialchars($post_arr["posted_by"], ENT_QUOTES, "utf-8"),
		"first_name" => htmlspecialchars($poster_arr["first_name"], ENT_QUOTES, "utf-8"),
		"last_name" => htmlspecialchars($poster_arr["last_name"], ENT_QUOTES, "utf-8"),
		"avatar_picture" => htmlspecialchars($poster_arr["avatar_picture"], ENT_QUOTES, "utf-8"),
		"avatar_rotate_degree" => ($poster_avatar_arr["rotate_degree"] != "" ? htmlspecialchars($poster_avatar_arr["rotate_degree"], ENT_QUOTES, "utf-8") : 0),
		"avatar_positions" => $poster_avatar_positions
	]
];
}


?>