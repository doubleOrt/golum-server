<?php
require_once "initialization.php";  
require_once "logged_in_importants.php";  

if(isset($_POST["user_id"]) && filter_var($_POST["user_id"], FILTER_VALIDATE_INT) !== false) {	 

$check_current_state = custom_pdo("select * from contacts where contact_of = :base_user_id and contact = :user_id", [":base_user_id" => $GLOBALS["base_user_id"], ":user_id" => $_POST["user_id"]])->fetch();

// if the contact is not added already
if($check_current_state["id"] == "") {

/* deals with a case where one of the users has been blocked or has blocked the other user and has 
somehow found a way to surpass our UI limits and checks which prevent a follow button between these 
2 users to function in the first place. */
$user_blocked_by_base_user_prepared = $con->prepare("select id from blocked_users where user_ids = concat(:base_user_id, '-', :user_id) or user_ids = concat(:user_id, '-', :base_user_id) limit 1");
$user_blocked_by_base_user_prepared->execute([":base_user_id" => $GLOBALS["base_user_id"], ":user_id" => $_POST["user_id"]]);
if($user_blocked_by_base_user_prepared->fetch()[0] != "") {
echo "1";
die();
}	
	
custom_pdo("insert into contacts (contact_of,contact,date_added) values(:base_user_id, :user_id, :date_of)", [":base_user_id" => $GLOBALS["base_user_id"], ":user_id" => $_POST["user_id"], ":date_of" => date("Y/m/d H:i")]);

// insert a notification
custom_pdo("insert into notifications (notification_from,notification_to,time,type) values (:base_user_id, :user_id, :time,6)", [":base_user_id" => $GLOBALS["base_user_id"], ":user_id" => $_POST["user_id"], ":time" => time()]);	

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
"notification_to" => htmlspecialchars($_POST["user_id"], ENT_QUOTES, "utf-8"),
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
custom_pdo("delete from contacts where contact_of = :base_user_id and contact = :user_id", [":base_user_id" => $GLOBALS["base_user_id"], ":user_id" => $_POST["user_id"]]);

/* nullify the "x is now following you" button inserted previously, just in case the user starts following someone and then immediately unfollows them, 
else the receiver would be confused */
custom_pdo("delete from notifications where notification_from = :base_user_id and notification_to = :user_id and type = 6", [":base_user_id" => $GLOBALS["base_user_id"], ":user_id" => $_POST["user_id"]]);

echo "1";	
}

}
 
 unset($con);
 
 ?>