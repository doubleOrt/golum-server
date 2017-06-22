<?php

require_once "common_requires.php";


if(isset($_GET["confirmation_code"]) && is_numeric($_GET["confirmation_code"])) {

$valid_confirmation_code = $con->query("select activated from users where id = ". $_SESSION["user_id"])->fetch()[0];

if($valid_confirmation_code == $_GET["confirmation_code"]) {
$con->exec("update users set activated = 'true' where id = ". $_SESSION["user_id"]);
echo "Materialize.toast('Email Address Successfully Linked With Your Account!',4000,'green');
$('.confirmEmailContainer').fadeOut();";
die();	 
}	
else {
echo "Materialize.toast('Invalid Code!',4000,'red');";
die();	
}
	
}


?>