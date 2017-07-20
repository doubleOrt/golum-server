<?php
require_once "initialization.php";  
require_once "logged_in_importants.php";  

if(isset($_GET["user_id"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false) {	 

$check_current_state = $con->query("select * from contacts where contact_of = ".$_SESSION["user_id"]." and contact = ".$_GET["user_id"])->fetch();

// if the contact is not added already
if($check_current_state["id"] == "") {
$con->exec("insert into contacts (contact_of,contact,date_added) values(".$_SESSION["user_id"].",". $_GET["user_id"] .",'".date("Y/m/d H:i")."')");

// insert a notification
$con->exec("insert into notifications (notification_from,notification_to,time,type) values (". $_SESSION["user_id"] .",". $_GET["user_id"] .",". time() .",6);");	
$notification_id = $con->lastInsertId();
$socket_message = [
"update_type" => "1",
"notification_id" => $notification_id,
"notification_time" => time(), 
"notification_time_string" => time_to_string(time()),
"notification_type" => "6", 
"notification_extra" => "0", 
"notification_extra2" => "0", 
"notification_extra3" => "0", 
"notification_read_yet" => "0", 
"notification_and_others" => "0", 
"notification_to" => htmlspecialchars($_GET["user_id"], ENT_QUOTES, "utf-8"),
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

echo "0";
}
// if the contact is already added meaning the user wants to remove this contact
else {
$con->exec("delete from contacts where contact_of = ".$_SESSION["user_id"]." and contact = ".$_GET["user_id"]);

/* nullify the "x is now following you" button inserted previously, just in case the user starts following someone and then immediately unfollows them, 
else the receiver would be confused */
$con->exec("delete from notifications where notification_from = ". $_SESSION["user_id"] ." and notification_to = ". $_GET["user_id"] ." and type = 6");

echo "1";	
}

}
 
 unset($con);
 
 ?>