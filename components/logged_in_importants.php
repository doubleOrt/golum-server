<?php

include_once "common_requires.php";

// if the user has logged in, create an array that contains all the columns from the corresponding row in our database. and create a variable that contains the users full name.
if(isset($_SESSION["user_id"])) {
$user_info_arr =  $con->query("select * from users where id = ".$_SESSION["user_id"])->fetch();	
//this variables makes a user's full name out of his first and last names.
$user_full_name = $user_info_arr["first_name"] . " " . $user_info_arr["last_name"];	

$base_user_avatar_arr = $con->query("SELECT * FROM avatars WHERE id_of_user = ". $_SESSION["user_id"] ." order by id desc limit 1")->fetch();

if($base_user_avatar_arr[0] != "") {
$base_user_avatar_rotate_degree = $base_user_avatar_arr["rotate_degree"];
$base_user_avatar_positions = explode(",",$base_user_avatar_arr["positions"]);
}
else {
$base_user_avatar_rotate_degree = 0;
$base_user_avatar_positions = [0,0];	
}

$user_favorite_posts_arr = $con->query("select post_id from favorites where user_id = ". $_SESSION["user_id"])->fetchAll();
}

$background_type_num_to_str = [1=>"jpg",2=>"png",3=>"gif"];

?>
