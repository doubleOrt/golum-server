<?php
#we make an ajax call to this page whenever a user wants to send a message

require_once "common_requires.php";

$echo_arr = [[]];

# if the post variables are set.
if(isset($_POST["message"]) && isset($_POST["chat_id"]) && filter_var($_POST["chat_id"], FILTER_VALIDATE_INT) !== false) {

$chat_id = intval($_POST["chat_id"]);
// we are not using this directly because you can't pass valued to pdo prepare directly.
$from = intval($_SESSION["user_id"]);	
$date_of = date("Y/m/d H:i");
$read_yet = false;
$message = openssl_encrypt($_POST["message"],"aes-128-cbc","georgedies",OPENSSL_RAW_DATA,"dancewithdragons");
$message_type = $_POST["type"];

# prepare the insert statement.
$prepare = $con->prepare("insert into messages(chat_id,message_from,message,date_of,read_yet,message_type) values(:chat_id,:from,:message_from,:date_of,:read_yet,:message_type)");
$prepare->bindValue(":chat_id",$chat_id, PDO::PARAM_INT);
$prepare->bindValue(":from",$from, PDO::PARAM_INT);
$prepare->bindParam(":message_from",$message);
$prepare->bindParam(":date_of",$date_of);
$prepare->bindParam(":read_yet",$read_yet);
$prepare->bindParam(":message_type",$message_type);

# check if the query executes without any errors, if so, echo out some js that will empty the send message textarea element, append the message to the sender's html, and scroll to the bottom of the chatWindowChild div.
if($prepare->execute()) {
	
$message_id = $con->lastInsertId();	
		
custom_pdo("update chats set latest_activity = :time where id = :chat_id", [":time" => time(), ":chat_id" => $chat_id]);	
# takes care of updating the new messages field of the users table for all recipients.
$chat_id_arr = custom_pdo("select * from chats where id = :chat_id", [":chat_id" => $chat_id])->fetch();	
$chatter_ids_arr = explode("-",$chat_id_arr["chatter_ids"]);


if($_POST["type"] == "text-message") {
$message_type = 0;	
}
else if($_POST["type"] == "emoji-message") {
$message_type = 1;
}


$messager_arr = custom_pdo("select id,first_name,last_name,avatar_picture from  users where id = :base_user_id", [":base_user_id" => $_SESSION["user_id"]])->fetch();
$messager_avatar_arr = custom_pdo("SELECT positions,rotate_degree FROM avatars WHERE id_of_user = :base_user_id order by id desc limit 1", [":base_user_id" => $_SESSION["user_id"]])->fetch();

if($messager_avatar_arr[0] != "") {
$messager_avatar_rotate_degree = $messager_avatar_arr["rotate_degree"];
$messager_avatar_positions = explode(",",htmlspecialchars($messager_avatar_arr["positions"], ENT_QUOTES, "utf-8"));
}
else {
$messager_avatar_rotate_degree = 0;
$messager_avatar_positions = [0,0];	
}


array_push($echo_arr[0],[
"update_type" => "0",
"chat_id" => $chat_id,
"chatter_ids" => $chatter_ids_arr,
"message" => htmlspecialchars($_POST["message"], ENT_QUOTES, "utf-8"),
"message_id" => $message_id,
"message_type" => $message_type,
"read_yet" => 0,
"time_string" => date("H:i"),
"message_sent_by_base_user" => 1,
"message_is_first_in_sequence" => 0,
"sender_info" => [
"id" => htmlspecialchars($messager_arr["id"], ENT_QUOTES, "utf-8"),
"first_name" => htmlspecialchars($messager_arr["first_name"], ENT_QUOTES, "utf-8"),
"last_name" => htmlspecialchars($messager_arr["last_name"], ENT_QUOTES, "utf-8"),
"avatar" => htmlspecialchars($messager_arr["avatar_picture"], ENT_QUOTES, "utf-8"),
"avatar_rotate_degree" => htmlspecialchars($messager_avatar_rotate_degree, ENT_QUOTES, "utf-8"),
"avatar_positions" => $messager_avatar_positions
]
]);	

$socket_message = $echo_arr[0][0];
$socket_message["message_sent_by_base_user"] = 0;

// This is our new stuff
$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://localhost:5555");
$socket->send(json_encode($socket_message));

}
}




echo json_encode($echo_arr);

unset($con);


?>