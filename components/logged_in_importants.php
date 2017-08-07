<?php

include_once "common_requires.php";

// if the user has logged in, create an array that contains all the columns from the corresponding row in our database. and create a variable that contains the users full name.
if(!is_null($GLOBALS["base_user_id"])) {
$user_info_arr =  custom_pdo("select * from users where id = :base_user_id", [":base_user_id" => $GLOBALS["base_user_id"]])->fetch();	
//this variables makes a user's full name out of his first and last names.
$user_full_name = $user_info_arr["first_name"] . " " . $user_info_arr["last_name"];	

$base_user_avatar_arr = custom_pdo("SELECT * FROM avatars WHERE id_of_user = :base_user_id order by id desc limit 1", [":base_user_id" => $GLOBALS["base_user_id"]])->fetch();

if($base_user_avatar_arr[0] != "") {
$base_user_avatar_rotate_degree = htmlspecialchars($base_user_avatar_arr["rotate_degree"]);
$base_user_avatar_positions = explode(",",htmlspecialchars($base_user_avatar_arr["positions"]));
}
else {
$base_user_avatar_rotate_degree = 0;
$base_user_avatar_positions = [0,0];	
}

}

$background_type_num_to_str = [1=>"jpg",2=>"png",3=>"gif"];


?>
