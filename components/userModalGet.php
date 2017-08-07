<?php
// we make an ajax call to this page everytime a user's avatar is clicked (or any other thing meant to cause the same action happens).


require_once "common_requires.php";
require_once "logged_in_importants.php";

$echo_arr = [];

//check if the request actually has the requested user's id for us, if not, then do nothing.
if(isset($_GET["user_id"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false) {

/* if we sent a "0" $_GET["user_id"], it means that this request is from a user who wants to view their own profile. so we set it to the $GLOBALS["base_user_id"] 
because "0" is not the id of any user,  it is just something we send that this page understands. */
$user_id = (intval($_GET["user_id"]) === 0 ? $GLOBALS["base_user_id"] : $_GET["user_id"]);

$prepared = $con->prepare("select *, (select count(id) from following_tags where id_of_user = :user_id) as following_tags_num from users where id = :user_id");
$prepared->bindParam(":user_id", $user_id);
$prepared->execute();
$user_modal_info_arr = $prepared->fetch();

$user_age_in_years = date_diff(date_create(date("Y-m-d")),date_create(str_replace(",","",$user_modal_info_arr["birthdate"])))->y;

$avatar_arr_prepared = $con->prepare("SELECT * FROM avatars WHERE id_of_user = :user_id order by id desc limit 1");
$avatar_arr_prepared->bindParam(":user_id", $user_id);
$avatar_arr_prepared->execute();
$avatar_arr = $avatar_arr_prepared->fetch();

if($avatar_arr[0] != "") {
$avatar_rotate_degree = $avatar_arr["rotate_degree"];
$avatar_positions = explode(",",$avatar_arr["positions"]);
}
else {
$avatar_rotate_degree = 0;
$avatar_positions = [0,0];	
}

$user_followed_by_num_prepared = $con->prepare("select count(id) from contacts where contact = :user_id and contact_of not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and contact_of not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and contact_of not in (SELECT user_id from account_states)");
$user_followed_by_num_prepared->bindParam(":user_id", $user_id);
$user_followed_by_num_prepared->bindParam(":base_user_id", $GLOBALS["base_user_id"]);
$user_followed_by_num_prepared->execute();
$user_followed_by_num = $user_followed_by_num_prepared->fetch()[0];

$user_following_num_prepared = $con->prepare("select count(id) from contacts where contact_of = :user_id and contact not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and contact not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and contact not in (SELECT user_id from account_states)");
$user_following_num_prepared->bindParam(":user_id", $user_id);
$user_following_num_prepared->bindParam(":base_user_id", $GLOBALS["base_user_id"]);
$user_following_num_prepared->execute();
$user_following_num = $user_following_num_prepared->fetch()[0];


$number_of_posts_shared_by_this_user_prepared = $con->prepare("select count(id) from posts where posted_by = :user_id");
$number_of_posts_shared_by_this_user_prepared->bindParam(":user_id", $user_id);
$number_of_posts_shared_by_this_user_prepared->execute();
$number_of_posts_shared_by_this_user = $number_of_posts_shared_by_this_user_prepared->fetch()[0];




$user_is_trendy_or_grumpy_arr_prepared = $con->prepare("select count(post_id0) as agreed_with_others_votes, (select count(id) from post_votes where user_id = :user_id) as total_votes from (select t1.post_id0, t1.option_index0, t2.user_option_index from (select distinct post_id as post_id0, option_index as option_index0, (select count(id) from post_votes where post_id = post_id0 and option_index = option_index0) as option_votes from post_votes where post_id in (select post_id from post_votes where user_id = :user_id) order by option_votes desc) t1 left join (select post_id as post_id0, option_index as user_option_index from post_votes where user_id = :user_id ) t2 on t1.post_id0 = t2.post_id0 group by t1.post_id0) t3 where option_index0 = user_option_index");
$user_is_trendy_or_grumpy_arr_prepared->bindParam(":user_id", $user_id);
$user_is_trendy_or_grumpy_arr_prepared->execute();
$user_is_trendy_or_grumpy_arr = $user_is_trendy_or_grumpy_arr_prepared->fetch();

if($user_is_trendy_or_grumpy_arr["total_votes"] > 0) {
// if the user has agreed with the majority on more than 49 of the posts, set $user_is_trendy_or_grumpy to 0 (trendy), else set it 
$user_agreed_with_others_percentage = (($user_is_trendy_or_grumpy_arr["agreed_with_others_votes"] / $user_is_trendy_or_grumpy_arr["total_votes"]) * 100);
}
else {
$user_agreed_with_others_percentage = 0;	
}

if($user_agreed_with_others_percentage > 60) {	
$user_is_trendy_or_grumpy = 1;	
}
else if($user_agreed_with_others_percentage > 40) {
$user_is_trendy_or_grumpy = 3;	
}
else if($user_is_trendy_or_grumpy_arr["total_votes"] > 0) {
$user_is_trendy_or_grumpy = 2;	
}
else {
$user_is_trendy_or_grumpy = 0;	
}


$followed_by_base_user = "";
$user_blocked_state = "";
// if the user is not the base user viewing his own profile
if($user_id != $GLOBALS["base_user_id"]) {
// check if base user has already added this person	
$followed_by_base_user_prepared = $con->prepare("select * from contacts where contact_of = ". $GLOBALS["base_user_id"] ." and contact = :user_id");
$followed_by_base_user_prepared->bindParam(":user_id", $user_id);
$followed_by_base_user_prepared->execute();
$followed_by_base_user = $followed_by_base_user_prepared->fetch()[0] != "" ? 1 : 0;
$user_blocked_state_prepared = $con->prepare("select id from blocked_users where user_ids = concat(". $GLOBALS["base_user_id"] .", '-', :user_id) or user_ids = concat(:user_id, '-', ". $GLOBALS["base_user_id"] .")");
$user_blocked_state_prepared->bindParam(":user_id", $user_id);
$user_blocked_state_prepared->execute();
$user_blocked_state = $user_blocked_state_prepared->fetch()[0] != "" ? 1 : 0;		
}	


$echo_arr[0] = "var info = {
'id': ". htmlspecialchars($user_id, ENT_QUOTES, "utf-8") .",	
'is_base_user': ". ($user_id == $GLOBALS["base_user_id"] ? "1" : "0") .",
'first_name': '". htmlspecialchars($user_modal_info_arr["first_name"], ENT_QUOTES, "utf-8") ."',
'last_name': '". htmlspecialchars($user_modal_info_arr["last_name"], ENT_QUOTES, "utf-8") ."',
'user_name': '". htmlspecialchars($user_modal_info_arr["user_name"], ENT_QUOTES, "utf-8") ."',
'background': '". ($user_modal_info_arr["background_path"] != "" ? $SERVER_URL . htmlspecialchars($user_modal_info_arr["background_path"], ENT_QUOTES, "utf-8") : "") ."',
'avatar': '". ($user_modal_info_arr["avatar_picture"] != "" ? (preg_match('/https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif)/', $user_modal_info_arr["avatar_picture"]) ? $user_modal_info_arr["avatar_picture"] : ($SERVER_URL . htmlspecialchars($user_modal_info_arr["avatar_picture"], ENT_QUOTES, "utf-8"))) : "") ."',
'avatar_editable': '". ($user_modal_info_arr["avatar_picture"] != "" ? "true" : "false") /* this seems redundant, but it is necessary for our client-side to function properly */ ."', 
'avatar_positions': [". htmlspecialchars($avatar_positions[0], ENT_QUOTES, "utf-8") .",". htmlspecialchars($avatar_positions[1], ENT_QUOTES, "utf-8") ."],
'avatar_rotate_degree': ". htmlspecialchars($avatar_rotate_degree, ENT_QUOTES, "utf-8") .",
'personality': '". htmlspecialchars($user_is_trendy_or_grumpy, ENT_QUOTES, "utf-8") ."',
'gender': '". htmlspecialchars($user_modal_info_arr["gender"], ENT_QUOTES, "utf-8") ."',
'country': '". htmlspecialchars($user_modal_info_arr["country"], ENT_QUOTES, "utf-8") ."', 
'birthdate': '". htmlspecialchars($user_modal_info_arr["birthdate"], ENT_QUOTES, "utf-8") ."',
'age_in_years': '". htmlspecialchars($user_age_in_years, ENT_QUOTES, "utf-8") ."',
'sign_up_date': '". htmlspecialchars($user_modal_info_arr["sign_up_date"], ENT_QUOTES, "utf-8") ."',
'followers_num': ". htmlspecialchars($user_followed_by_num, ENT_QUOTES, "utf-8") .",
'followings_num': ". htmlspecialchars($user_following_num, ENT_QUOTES, "utf-8") .",
'followed_by_base_user': '". htmlspecialchars($followed_by_base_user, ENT_QUOTES, "utf-8") ."',
'following_tags_num': '". htmlspecialchars($user_modal_info_arr["following_tags_num"], ENT_QUOTES, "utf-8") ."',
'user_blocked_state': '". htmlspecialchars($user_blocked_state, ENT_QUOTES, "utf-8") ."',
'total_posts_num': ". htmlspecialchars($number_of_posts_shared_by_this_user, ENT_QUOTES, "utf-8") ."
}";

echo json_encode($echo_arr);	
}

unset($con);



?>