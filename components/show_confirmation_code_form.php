<?php
#we make a call to this page everytime the settings modal is opened, and we show the user the confirm email code if we have to.


require_once "common_requires.php";
require_once "logged_in_importants.php";

$echo_arr = [];

if(is_numeric($user_info_arr["activated"])) {
$echo_arr[0] = "1";
$echo_arr[1] = $user_info_arr["email_address"];
}
else {
$echo_arr[0] = "0";	
}


echo json_encode($echo_arr);

?>