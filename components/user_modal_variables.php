<?php

require_once "common_requires.php";

if(isset($GLOBALS["base_user_id"])) {

require_once "logged_in_importants.php";
	

echo "
default_first_name = '". htmlspecialchars($user_info_arr["first_name"], ENT_QUOTES, "utf-8") ."';
default_last_name = '". htmlspecialchars($user_info_arr["last_name"], ENT_QUOTES, "utf-8") ."';
default_user_name = '". htmlspecialchars($user_info_arr["user_name"], ENT_QUOTES, "utf-8") ."';
default_email_address = '". htmlspecialchars($user_info_arr["email_address"], ENT_QUOTES, "utf-8") ."';
";

}


?>