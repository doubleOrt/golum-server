<?php

require_once "common_requires.php";

$echo_arr = [];

if(isset($_GET["confirmation_code"]) && filter_var($_GET["confirmation_code"], FILTER_VALIDATE_INT) !== false) {

$valid_confirmation_code = $con->query("select activated from users where id = ". $_SESSION["user_id"])->fetch()[0];

if($valid_confirmation_code == $_GET["confirmation_code"]) {
$con->exec("update users set activated = 'true' where id = ". $_SESSION["user_id"]);
$echo_arr[0] = 1;
}	
else {
$echo_arr[0] = 0;
}
	
}

echo json_encode($echo_arr);


?>