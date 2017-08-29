<?php

require_once "initialization.php";


if(isset($_POST["message_id"]) && filter_var($_POST["message_id"], FILTER_VALIDATE_INT) !== false) {
// using 2 queries because of an SQL error #1093	
$user_can_modify_chat_prepared = $con->prepare("select case when (select chat_id from messages where id = :message_id) in (select id from chats where chatter_ids like concat(:base_user_id, '-%') or chatter_ids like concat('%-', 1)) then true else false end as user_involved_in_chat");	
$user_can_modify_chat_prepared->execute([":message_id" => $_POST["message_id"], ":base_user_id" => $GLOBALS["base_user_id"]]);
$user_can_modify_chat = $user_can_modify_chat_prepared->fetch()[0] !== "1" ? false : true;
if($user_can_modify_chat === true) {
$con->prepare("update messages set read_yet = 1 where id = :message_id and message_from != :base_user_id and (select chat_id from messages where id = :message_id) in (select id from chats where chatter_ids = concat(:base_user_id, '-%') or chatter_ids = concat('%-', :base_user_id))")->execute([":message_id" => $_POST["message_id"], ":base_user_id" => $GLOBALS["base_user_id"]]);	
}
}
else if(isset($_POST["chat_id"]) && filter_var($_POST["chat_id"], FILTER_VALIDATE_INT) !== false) {
$con->prepare("update messages set read_yet = 1 where chat_id = :chat_id and message_from != :base_user_id and :chat_id in (select id from chats where chatter_ids like concat(:base_user_id, '-%') or chatter_ids like concat('%-', :base_user_id))")->execute([":chat_id" => $_POST["chat_id"], ":base_user_id" => $GLOBALS["base_user_id"]]);		
}

	
unset($con);
	
?>