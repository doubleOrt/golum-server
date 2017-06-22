<?php
// we make an ajax call to this page everytime a user's avatar is clicked (or any other thing meant to cause the same action happens).


require_once "common_requires.php";
require_once "logged_in_importants.php";

$echo_arr = [];

//check if the request actually has the requested users id for us, if not, then do nothing.
if(isset($_GET["user_id"]) && (is_integer(intval($_GET["user_id"])) || intval($_GET["user_id"]) === 0)) {

/* if we sent a "0" $_GET["user_id"], it means that this request is from a user who wants to view their own profile. so we set it to the $_SESSION["user_id"] 
because "0" is not the id of any user,  it is just something we send that this page understands. */
$user_id = (intval($_GET["user_id"]) === 0 ? $_SESSION["user_id"] : $_GET["user_id"]);

$user_modal_info_arr = $con->query("select * from users where id = ". $user_id)->fetch();

$user_age_in_years = date_diff(date_create(date("Y-m-d")),date_create(str_replace(",","",$user_modal_info_arr["birthdate"])))->y;
$preselect_date = date_format(date_create(str_replace(",","",$user_info_arr["birthdate"])),"Y-m-d");


$avatar_arr = $con->query("SELECT * FROM avatars WHERE id_of_user = ".$user_id." order by id desc limit 1")->fetch();

if($avatar_arr[0] != "") {
$avatar_rotate_degree = $avatar_arr["rotate_degree"];
$avatar_positions = explode(",",$avatar_arr["positions"]);
}
else {
$avatar_rotate_degree = 0;
$avatar_positions = [0,0];	
}

$user_followed_by_num = $con->query("select count(id) from contacts where contact = ". $user_id)->fetch()[0];
$user_following_num = $con->query("select count(id) from contacts where contact_of = ". $user_id)->fetch()[0];

$number_of_posts_shared_by_this_user = $con->query("select count(id) from posts where posted_by = ". $user_id)->fetch()[0];




$user_is_trendy_or_grumpy_arr = $con->query("select count(post_id0) as agreed_with_others_votes, (select count(id) from post_votes where user_id = ". $user_id .") as total_votes from (select t1.post_id0, t1.option_index0, t2.user_option_index from (select distinct post_id as post_id0, option_index as option_index0, (select count(id) from post_votes where post_id = post_id0 and option_index = option_index0) as option_votes from post_votes where post_id in (select post_id from post_votes where user_id = ". $user_id .") order by option_votes desc) t1 left join (select post_id as post_id0, option_index as user_option_index from post_votes where user_id = ". $user_id ." ) t2 on t1.post_id0 = t2.post_id0 group by t1.post_id0) t3 where option_index0 = user_option_index")->fetch();

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


$contact_already_added = "";
$user_blocked_state = "";
// if the user is not the base user viewing his own profile
if($user_id != $_SESSION["user_id"]) {
// check if base user has already added this person	
$contact_already_added = $con->query("select * from contacts where contact_of = ".$_SESSION["user_id"]." and contact = ".$user_id)->fetch();
$user_blocked_state = $con->query("select id from blocked_users where user_ids = '".$_SESSION["user_id"]."-".htmlspecialchars($user_id,ENT_QUOTES)."' or user_ids = '".htmlspecialchars($user_id,ENT_QUOTES) . "-" . $_SESSION["user_id"]."'")->fetch();		
}	


$echo_arr[0] = "var info = {
'id': ". htmlspecialchars($user_id) .",	
'is_base_user': ". ($user_id == $_SESSION["user_id"] ? "1" : "0") .",
'first_name': '". htmlspecialchars($user_modal_info_arr["first_name"]) ."',
'last_name': '". htmlspecialchars($user_modal_info_arr["last_name"]) ."',
'user_name': '". htmlspecialchars($user_modal_info_arr["user_name"]) ."',
'background': '". htmlspecialchars($user_modal_info_arr["background_path"]) ."',
'avatar': '". htmlspecialchars($user_modal_info_arr["avatar_picture"]) ."',
'avatar_positions': [". htmlspecialchars($avatar_positions[0]) .",". htmlspecialchars($avatar_positions[1]) ."],
'avatar_rotate_degree': ". htmlspecialchars($avatar_rotate_degree) .",
'personality': '". htmlspecialchars($user_is_trendy_or_grumpy) ."',
'gender': '". htmlspecialchars($user_modal_info_arr["gender"]) ."',
'country': '". htmlspecialchars($user_modal_info_arr["country"]) ."',
'birthdate': '". htmlspecialchars($user_modal_info_arr["birthdate"]) ."',
'preselect_date': '". htmlspecialchars($preselect_date) ."',
'age_in_years': '". htmlspecialchars($user_age_in_years) ."',
'sign_up_date': '". htmlspecialchars($user_modal_info_arr["sign_up_date"]) ."',
'followers_num': ". htmlspecialchars($user_followed_by_num) .",
'following_num': ". htmlspecialchars($user_following_num) .",
'user_blocked_state': '". htmlspecialchars($user_blocked_state) ."',
'total_posts_num': ". htmlspecialchars($number_of_posts_shared_by_this_user) ."
}";

echo json_encode($echo_arr);	
}

unset($con);



?>