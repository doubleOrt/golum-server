<?php

require_once 'common_requires.php';
require_once "logged_in_importants.php";
require_once "send_confirmation_code_function.php";
require_once '../../phpmailer/PHPMailerAutoload.php';

$echo_arr = [0];

$email_info = custom_pdo("select user_name, email_address, activated from users where id = :base_user_id", [":base_user_id" => $GLOBALS["base_user_id"]])->fetch();

if($email_info["email_address"] != "" && $email_info["activated"] !== "true" && $email_info["activated"] != "") {
if(filter_var($email_info["email_address"], FILTER_VALIDATE_EMAIL) !== false) { 
if( send_confirmation_code($GLOBALS["base_user_id"], $email_info["email_address"], $email_info["user_name"]) === true) {
$echo_arr[0] = 1;
}
}
}

echo json_encode($echo_arr);

unset($con);


?>