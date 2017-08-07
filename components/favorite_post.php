<?php
// we make a call to this page whenever the user wants to favorite a post.


require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_POST["post_id"]) && filter_var($_POST["post_id"], FILTER_VALIDATE_INT) !== false) {

$poster_id = custom_pdo("select posted_by from posts where id = :post_id", [":post_id" => $_POST["post_id"]])->fetch()["posted_by"];

if(custom_pdo("select id from favorites where post_id = :post_id and user_id = :base_user_id", [":post_id" => $_POST["post_id"], ":base_user_id" => $GLOBALS["base_user_id"]])->fetch()["id"] == "") {
custom_pdo("insert into favorites (post_id, user_id, time) values (:post_id, :base_user_id, :time)", [":post_id" => $_POST["post_id"], ":base_user_id" => $GLOBALS["base_user_id"], ":time" => time()]);
// if the current favorite is not a user favoriting their own post, send them a notification.
if($poster_id != $GLOBALS["base_user_id"]) {
custom_pdo("insert into notifications (notification_from,notification_to,time,type,extra) values (:base_user_id, :poster_id, :time, 5, :post_id)", [":base_user_id" => $GLOBALS["base_user_id"], ":poster_id" => $poster_id, ":time" => time(), ":post_id" => $_POST["post_id"]]);	
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
echo "1";	
}
else {
custom_pdo("delete from favorites where post_id = :post_id and user_id = :base_user_id", [":post_id" => $_POST["post_id"], ":base_user_id" => $GLOBALS["base_user_id"]]);	
// remove the notification sent to the user when you favorited the post.
if($poster_id != $GLOBALS["base_user_id"]) {
custom_pdo("delete from notifications where notification_from = :base_user_id and notification_to = :poster_id and type = 5 and extra = :post_id", [":base_user_id" => $GLOBALS["base_user_id"], ":poster_id" => $poster_id, ":post_id" => $_POST["post_id"]]);
}
echo "0";
}

}



?>