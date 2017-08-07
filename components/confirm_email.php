<?php

require_once "common_requires.php";

$echo_arr = [];

if(isset($_POST["confirmation_code"]) && filter_var($_POST["confirmation_code"], FILTER_VALIDATE_INT) !== false) {

$valid_confirmation_code = custom_pdo("select activated from users where id = :base_user_id", [":base_user_id" => $GLOBALS["base_user_id"]])->fetch()[0];

if($valid_confirmation_code == $_POST["confirmation_code"]) {
custom_pdo("update users set activated = 'true' where id = :base_user_id", [":base_user_id" => $GLOBALS["base_user_id"]]);
$echo_arr[0] = 1;
}	
else {
$echo_arr[0] = 0;
}
	
}

echo json_encode($echo_arr);

unset($con);

?>