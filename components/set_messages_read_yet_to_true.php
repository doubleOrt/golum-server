<?php

require_once "initialization.php";


if(isset($_POST["message_id"]) && filter_var($_POST["message_id"], FILTER_VALIDATE_INT) !== false) {
$con->prepare("update messages set read_yet = 1 where id = :message_id")->execute([":message_id" => $_POST["message_id"]]);	
}
else if(isset($_POST["chat_id"]) && filter_var($_POST["chat_id"], FILTER_VALIDATE_INT) !== false) {
$con->prepare("update messages set read_yet = 1 where chat_id = :chat_id and message_from != :base_user_id ")->execute([":chat_id" => $_POST["chat_id"], ":base_user_id" => $_SESSION["user_id"]]);		
}

	
unset($con);	
	
?>