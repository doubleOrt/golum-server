<?php
#when a user wants to hide a chat, we make a call to this page.

require_once "common_requires.php";


if(isset($_POST["chat_id"]) && filter_var($_POST["chat_id"], FILTER_VALIDATE_INT) !== false) {

$date_of = date("Y/m/d H:i");

$prepare_hide_chat = $con->prepare("insert into hidden_chats (chat_id,user_id,date_of) values(:chat_id,:user_id,:date_of)");
$prepare_hide_chat->bindParam(":chat_id", $_POST["chat_id"]);	
$prepare_hide_chat->bindParam(":user_id", $GLOBALS["base_user_id"]);	
$prepare_hide_chat->bindParam(":date_of", $date_of);	

if($prepare_hide_chat->execute()) {
echo "1";	
}

}



?>