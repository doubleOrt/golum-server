<?php
require_once "common_requires.php";


// you need this function to retrieve the markup of a post, you just need to pass the post's array (from the database) as the first argument and you are good to go.
function get_post_data($post_arr) {
global $con, $SERVER_URL;

// a call to this function means that the post is being viewed by 1 more user, so we need to update the post's total views.
custom_pdo("update posts set post_views = post_views + 1 where id = :post_id", [":post_id" => $post_arr["id"]]);
// we also need to update the views for the post array passed to this function, for example i just shared a post, i make a call to this function, and without incrementing the array, i would see 0 views instead of 1.
$post_arr["post_views"] = ++$post_arr["post_views"];


$poster_arr = custom_pdo("select first_name, last_name, avatar_picture from users where id = :posted_by", [":posted_by" => $post_arr["posted_by"]])->fetch();
$poster_avatar_arr = custom_pdo("SELECT positions, rotate_degree FROM avatars WHERE id_of_user = :posted_by order by id desc limit 1", [":posted_by" => $post_arr["posted_by"]])->fetch();
$poster_avatar_positions = explode(",", htmlspecialchars($poster_avatar_arr["positions"], ENT_QUOTES, "utf-8"));
//if avatar positions does not exist 
if(count($poster_avatar_positions) < 2) {
$poster_avatar_positions = [0,0];
}


$posted_by_base_user = ($post_arr["posted_by"] == $GLOBALS["base_user_id"] ? true : false);
$base_user_already_voted = custom_pdo("select id from post_votes where post_id = :post_id and user_id = :base_user_id", [":post_id" => $post_arr["id"], ":base_user_id" => $GLOBALS["base_user_id"]])->fetch();


$post_images_dir_path = $SERVER_URL . "users/" . $GLOBALS["base_user_id"] . "/posts/" ;
$post_file_types_arr = explode(",", htmlspecialchars($post_arr["file_types"], ENT_QUOTES, "utf-8"));	
$post_images = [];
for($i = 0; $i < count($post_file_types_arr); $i++) {
array_push($post_images, $post_images_dir_path . htmlspecialchars($post_arr["id"], ENT_QUOTES, "utf-8") . "-" . $i . "." . $post_file_types_arr[$i]);
}


return [
"post_id" => htmlspecialchars($post_arr["id"], ENT_QUOTES, "utf-8"),
"post_title" => htmlspecialchars($post_arr["title"], ENT_QUOTES, "utf-8"),
"post_views" => htmlspecialchars($post_arr["post_views"], ENT_QUOTES, "utf-8"),
"post_type" => htmlspecialchars($post_arr["type"], ENT_QUOTES, "utf-8"),
"post_images" => $post_images,
"base_user_already_voted" => ($base_user_already_voted != "" ? true : false),
"posted_by_base_user" => $posted_by_base_user,
"post_owner_info" => [
		"id" => htmlspecialchars($post_arr["posted_by"], ENT_QUOTES, "utf-8"),
		"first_name" => htmlspecialchars($poster_arr["first_name"], ENT_QUOTES, "utf-8"),
		"last_name" => htmlspecialchars($poster_arr["last_name"], ENT_QUOTES, "utf-8"),
		"avatar_picture" => ($poster_arr["avatar_picture"] != "" ? (preg_match('/https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif)/', $poster_arr["avatar_picture"]) ? $poster_arr["avatar_picture"] : ($SERVER_URL . htmlspecialchars($poster_arr["avatar_picture"], ENT_QUOTES, "utf-8"))) : ""),
		"avatar_rotate_degree" => ($poster_avatar_arr["rotate_degree"] != "" ? htmlspecialchars($poster_avatar_arr["rotate_degree"], ENT_QUOTES, "utf-8") : 0),
		"avatar_positions" => $poster_avatar_positions
	]
];
}


?>