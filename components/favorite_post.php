<?php
// we make a call to this page whenever the user wants to favorite a post.


require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_POST["post_id"]) && filter_var($_POST["post_id"], FILTER_VALIDATE_INT) !== false) {

$poster_id = $con->query("select posted_by from posts where id = ". $_POST["post_id"])->fetch()["posted_by"];

if($con->query("select id from favorites where post_id = ". $_POST["post_id"] ." and user_id = ". $_SESSION["user_id"])->fetch()["id"] == "") {
$con->exec("insert into favorites (post_id,user_id,time) values (". $_POST["post_id"] .",". $_SESSION["user_id"] .",". time() .");");
// if the current favorite is not a user favoriting their own post, send them a notification.
if($poster_id != $_SESSION["user_id"]) {
$con->exec("insert into notifications (notification_from,notification_to,time,type,extra) values (". $_SESSION["user_id"] .",". $poster_id .",". time() .",5,". $_POST["post_id"] .");");	
$notification_id = $con->lastInsertId();

$socket_message = [
"update_type" => "1",
"notification_id" => $notification_id,
"notification_time" => time(), 
"notification_time_string" => time_to_string(time()),
"notification_type" => 5, 
"notification_extra" => htmlspecialchars($_POST["post_id"], ENT_QUOTES, "utf-8"), 
"notification_extra2" => "0", 
"notification_extra3" => "0", 
"notification_read_yet" => "0", 
"notification_and_others" => "0", 
"notification_to" => htmlspecialchars($poster_id, ENT_QUOTES, "utf-8"),
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
echo "var favorited = true";	
}
else {
$con->exec("delete from favorites where post_id = ".$_POST["post_id"]." and user_id = ".$_SESSION["user_id"]);	
// remove the notification sent to the user when you favorited the post.
if($poster_id != $_SESSION["user_id"]) {
$con->exec("delete from notifications where notification_from = ". $_SESSION["user_id"] ." and notification_to = ". $poster_id ." and type = 5 and extra = ". $_POST["post_id"]);
}
echo "var favorited = false";
}
$shmid = $poster_id . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);
}



?>