<?php

require_once "common_requires.php";

if(isset($_SESSION["user_id"])) {

require_once "logged_in_importants.php";
	

echo "
changeInfosGetObjDefaults = {
gender:'". htmlspecialchars($user_info_arr["gender"]) ."',
country:'". htmlspecialchars($user_info_arr["country"]) ."',
birthdate:'". htmlspecialchars($user_info_arr["birthdate"]) ."',
avatar_positions:[". htmlspecialchars($base_user_avatar_positions[0] . "," . $base_user_avatar_positions[1]) ."],
avatar_rotation:". ($base_user_avatar_arr["rotate_degree"] != "" ? htmlspecialchars($base_user_avatar_arr["rotate_degree"]) : 0) ."
};

changeInfosGetObj = {
gender:'". htmlspecialchars($user_info_arr["gender"]) ."',
country:'". htmlspecialchars($user_info_arr["country"]) ."',
birthdate:'". htmlspecialchars($user_info_arr["birthdate"]) ."',
avatar_positions:[". htmlspecialchars($base_user_avatar_positions[0] . "," . $base_user_avatar_positions[1]) ."],
avatar_rotation:". ($base_user_avatar_arr["rotate_degree"] != "" ? htmlspecialchars($base_user_avatar_arr["rotate_degree"]) : 0) ."
};


default_first_name = '". htmlspecialchars($user_info_arr["first_name"]) ."';
default_last_name = '". htmlspecialchars($user_info_arr["last_name"]) ."';
default_user_name = '". htmlspecialchars($user_info_arr["user_name"]) ."';
default_email_address = '". htmlspecialchars($user_info_arr["email_address"]) ."';
";

}


?>