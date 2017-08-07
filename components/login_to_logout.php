<?php
#a call should be made to this page on all pages where the user is supposed to be logged in.

require_once "common_requires.php";
require_once "logged_in_importants.php";

$echo_arr = [0, 0];

if(!is_null($GLOBALS["base_user_id"])) {
$echo_arr[0] = $GLOBALS["base_user_id"];	
if($user_info_arr["password"] == "") {
$echo_arr[1] = 1;	
}
}


// notice that the order of these two conditionals matters, the first one unsets the session when the user logs out, the second one which wouldn't be true if it was above this conditional redirects the user to the login page.
if(isset($_POST["sign_out"]) && !is_null($GLOBALS["base_user_id"])) {
$session->remove("user_id");
}


echo json_encode($echo_arr);

unset($con);

?>