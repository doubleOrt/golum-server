<?php
/* we make a call to this page whenever a user clicks the send button to send a post to a friend */

require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_POST["post_id"]) && isset($_POST["friend_id"]) && filter_var($_POST["post_id"], FILTER_VALIDATE_INT) !== false && filter_var($_POST["friend_id"], FILTER_VALIDATE_INT) !== false) {

$time = time();
// type for notification is when users send their friends posts.
$type = 4;

// if the user has already sent this post to the target user by some hacking trick perhaps, die.
if(custom_pdo("select id from notifications where notification_from = :base_user_id and notification_to = :friend_id and type = 4 and extra = :post_id", [":base_user_id" => $_SESSION["user_id"], ":friend_id" => $_POST["friend_id"], ":post_id" => $_POST["post_id"]])->fetch()[0] != "") {
die();
}

$prepared = $con->prepare("insert into notifications (notification_from,notification_to,time,type,extra) values (:notification_from,:notification_to,:time,:type,:extra)");
$prepared->bindParam(":notification_from",$_SESSION["user_id"]);	
$prepared->bindParam(":notification_to",$_POST["friend_id"]);	
$prepared->bindParam(":time",$time);	
$prepared->bindParam(":type",$type);	
$prepared->bindParam(":extra",$_POST["post_id"]);	

if($prepared->execute()) {
$notification_id = $con->lastInsertId();
echo "1";

$socket_message = [
"update_type" => "1",
"notification_id" => $notification_id,
"notification_time" => $time, 
"notification_time_string" => time_to_string($time),
"notification_type" => $type, 
"notification_extra" => htmlspecialchars($_POST["post_id"], ENT_QUOTES, "utf-8"), 
"notification_extra2" => "0", 
"notification_extra3" => "0", 
"notification_read_yet" => "0", 
"notification_and_others" => "0", 
"notification_to" => htmlspecialchars($_POST["friend_id"], ENT_QUOTES, "utf-8"),
"notification_sender_info" => [
	"id" => $user_info_arr["id"], 
	"first_name" => htmlspecialchars($user_info_arr["first_name"], ENT_QUOTES, "utf-8"),
	"last_name" => htmlspecialchars($user_info_arr["last_name"], ENT_QUOTES, "utf-8"),
	"avatar" => htmlspecialchars($user_info_arr["avatar_picture"], ENT_QUOTES, "utf-8"),
	"avatar_positions" => $base_user_avatar_positions,
	"avatar_rotate_degree" => $base_user_avatar_rotate_degree
	] 
];	
	
$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://localhost:5555");
$socket->send(json_encode($socket_message));
}
	
	
}


?>