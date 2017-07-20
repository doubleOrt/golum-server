<?php

require_once "initialization.php";


if(isset($_POST["notification_id"]) && filter_var($_POST["notification_id"], FILTER_VALIDATE_INT) !== false) {
$con->prepare("update notifications set read_yet = :time where id = :notification_id")->execute([":time" => time(), ":notification_id" => $_POST["notification_id"]]);	
}
else {
$con->prepare("update notifications set read_yet = :time where notification_to = :base_user_id ")->execute([":time" => time(), ":base_user_id" => $_SESSION["user_id"]]);		
}

	
unset($con);	
	
?>