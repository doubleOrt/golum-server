<?php
# this is supposed to give you all the messages newer than the newer_than field.

require_once "common_requires.php";
require_once "logged_in_importants.php";

$echo_arr = [[]];


if(isset($_GET["chat_id"]) && isset($_GET["newer_than"]) && filter_var($_GET["chat_id"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["newer_than"], FILTER_VALIDATE_INT) !== false) {

$chat_id = $_GET["chat_id"];
$newer_than = $_GET["newer_than"];

$chat_arr = custom_pdo("select id, chatter_ids from chats where id = :chat_id", [":chat_id" => $chat_id])->fetch();	
$chat_recipient_id = explode("-",$chat_arr["chatter_ids"])[0] == $GLOBALS["base_user_id"] ? explode("-",$chat_arr["chatter_ids"])[1] :  explode("-",$chat_arr["chatter_ids"])[0];	
	
$messages_arr = custom_pdo("select id, message_from, message, read_yet, date_of, message_type from messages where chat_id = :chat_id and id > :newer_than", [":chat_id" => $chat_id, ":newer_than" => $newer_than])->fetchAll();

$messager_arr = custom_pdo("select id,first_name,last_name,avatar_picture from  users where id = :recipient_id", [":recipient_id" => $chat_recipient_id])->fetch();
$messager_avatar_arr = custom_pdo("SELECT positions,rotate_degree FROM avatars WHERE id_of_user = :messager_id order by id desc limit 1", [":messager_id" => $messager_arr["id"]])->fetch();
if($messager_avatar_arr[0] != "") {
$messager_avatar_rotate_degree = $messager_avatar_arr["rotate_degree"];
$messager_avatar_positions = explode(",",htmlspecialchars($messager_avatar_arr["positions"], ENT_QUOTES, "utf-8"));
}
else {
$messager_avatar_rotate_degree = 0;
$messager_avatar_positions = [0,0];	
}


for($x = 0; $x < count($messages_arr); $x++) {

$message_raw = openssl_decrypt($messages_arr[$x]["message"],"aes-128-cbc","georgedies",OPENSSL_RAW_DATA,"dancewithdragons");

//if this message is a sent by this user to someone else, then set this variable to true, else set it to false.
$sent_message = ($messages_arr[$x]["message_from"] == $GLOBALS["base_user_id"] ? 1 : 0);		

if($sent_message == 1) {
$sender_info = [
"id" => $user_info_arr["id"],
"first_name" => htmlspecialchars($user_info_arr["first_name"], ENT_QUOTES, "utf-8"),
"last_name" => htmlspecialchars($user_info_arr["last_name"], ENT_QUOTES, "utf-8"),
"avatar" => ($messager_arr["avatar_picture"] != "" ? (preg_match('/https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif)/', $messager_arr["avatar_picture"]) ? $messager_arr["avatar_picture"] : ($SERVER_URL . htmlspecialchars($messager_arr["avatar_picture"], ENT_QUOTES, "utf-8"))) : ""),
"avatar_rotate_degree" => htmlspecialchars($base_user_avatar_rotate_degree, ENT_QUOTES, "utf-8"),
"avatar_positions" => $base_user_avatar_positions
];	
}
else {
$sender_info = [
"id" => $messager_arr["id"],
"first_name" => htmlspecialchars($messager_arr["first_name"], ENT_QUOTES, "utf-8"),
"last_name" => htmlspecialchars($messager_arr["last_name"], ENT_QUOTES, "utf-8"),
"avatar" => ($messager_arr["avatar_picture"] != "" ? (preg_match('/https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif)/', $messager_arr["avatar_picture"]) ? $messager_arr["avatar_picture"] : ($SERVER_URL . htmlspecialchars($messager_arr["avatar_picture"], ENT_QUOTES, "utf-8"))) : ""),
"avatar_rotate_degree" => htmlspecialchars($messager_avatar_rotate_degree, ENT_QUOTES, "utf-8"),
"avatar_positions" => $messager_avatar_positions
];	
}
			
if($messages_arr[$x]["message_type"] == "text-message") {
array_push($echo_arr[0], [
"message" => htmlspecialchars($message_raw, ENT_QUOTES, "utf-8"),
"message_id" => $messages_arr[$x]["id"],
"message_type" => 0,
"read_yet" => htmlspecialchars($messages_arr[$x]["read_yet"], ENT_QUOTES, "utf-8"), 
"time_string" => date("H:i",strtotime($messages_arr[$x]["date_of"])),
"message_sent_by_base_user" => $sent_message,
"sender_info" => $sender_info
]);
}
else if($messages_arr[$x]["message_type"] == "emoji-message") {	
array_push($echo_arr[0], [
"message" => htmlspecialchars($message_raw, ENT_QUOTES, "utf-8"),
"message_id" => $messages_arr[$x]["id"],
"message_type" => 1,
"read_yet" => htmlspecialchars($messages_arr[$x]["read_yet"], ENT_QUOTES, "utf-8"), 
"time_string" => date("H:i",strtotime($messages_arr[$x]["date_of"])),
"message_sent_by_base_user" => $sent_message,
"sender_info" => $sender_info
]);
}
else if($messages_arr[$x]["message_type"] == "file-message") {
$file_arr = $con->query("select * from sent_files where id = ". intval($messages_arr[$x]["message"]))->fetch();
array_push($echo_arr[0], [
"message" => $SERVER_URL . $file_arr["path"],
"message_id" => $messages_arr[$x]["id"],
"message_type" => 2,
"read_yet" => htmlspecialchars($messages_arr[$x]["read_yet"], ENT_QUOTES, "utf-8"), 
"time_string" => date("H:i",strtotime($messages_arr[$x]["date_of"])),
"message_sent_by_base_user" => $sent_message,
"sender_info" => $sender_info
]);
}

}

#set all messages's read_yet to true
custom_pdo("update messages set read_yet = true where chat_id = :chat_id and id > :newer_than", [":chat_id" => $chat_id, ":newer_than" => $newer_than]);
}



echo json_encode($echo_arr);

unset($con);


?>
