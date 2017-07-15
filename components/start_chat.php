<?php
#we make a call to this page everytime a user wants to chat with someone.

require_once "common_requires.php";

require_once "logged_in_importants.php";

include_once "letter_avatars.php";




$echo_arr = [[]];


if(!isset($_GET["chat_id"]) && isset($_GET["user_id"]) && isset($_GET["row_offset"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false) {
		
$chat_recipient_id = $_GET["user_id"];
	
# if this is the first time the users are chatting, then add a new row to the chats table (the table mainly associated with the chatPortals).
$start_date = date("Y/m/d H:i");	
$chatter_ids = $_SESSION["user_id"] . "-" . $_GET["user_id"]; 
$latest_activity = time();

$chat_arr = $con->query("select * from chats where chatter_ids = '".$_SESSION["user_id"]."-".$_GET["user_id"]."' or chatter_ids = '".$_GET["user_id"]."-".$_SESSION["user_id"]."'")->fetch();
$chat_id = $chat_arr["id"];
# if this is the first time the users are chatting, then add a new row to the chats table (the table mainly associated with the chatPortals).
if($chat_id == "") {
$chat_prepare = $con->prepare("insert into chats (start_date,chatter_ids,latest_activity) values(:start_date,:chatter_ids,:latest_activity);");
$chat_prepare->bindParam(":start_date",$start_date);
$chat_prepare->bindParam(":chatter_ids",$chatter_ids);
$chat_prepare->bindParam(":latest_activity",$latest_activity);
$chat_prepare->execute();
$chat_id = $con->lastInsertId();
}

$chat_recipient_info_arr = $con->query("select * from users where id = ".$_GET["user_id"])->fetch();
}
// if the users are continuing a previous chat.
else if(isset($_GET["chat_id"]) && filter_var($_GET["chat_id"], FILTER_VALIDATE_INT) !== false){
$chat_id = $_GET["chat_id"];	

$chat_arr = $con->query("select * from chats where id = ". $chat_id)->fetch();	
$chat_recipient_id = explode("-",$chat_arr["chatter_ids"])[0] == $_SESSION["user_id"] ? explode("-",$chat_arr["chatter_ids"])[1] :  explode("-",$chat_arr["chatter_ids"])[0];	
	
$chat_recipient_info_arr = $con->query("select * from users where id = ".$chat_recipient_id)->fetch();
}


// unhide the chat if the chat is hidden. (we don't do this all the time, only when the user is opening the chat by clicking the startChat button in the recipient's user modal.)
if($_GET["unhide_chat_if_hidden"] == "true") {
$con->exec("delete from hidden_chats where chat_id = ". $chat_id ." and user_id = ". $_SESSION["user_id"]);		
}


if(count($chat_recipient_info_arr) > 0) {
	
$current_status_string	= "Online";

$messages_arr =  $con->query("select id,message_from,message,read_yet,date_of,message_type from messages where chat_id = ". $chat_id ." order by id desc limit 15 OFFSET ". $_GET["row_offset"])->fetchAll();

$messager_arr = $con->query("select id,first_name,last_name,avatar_picture from  users where id = ". $chat_recipient_id)->fetch();
$messager_avatar_arr = $con->query("SELECT positions,rotate_degree FROM avatars WHERE id_of_user = ". $messager_arr["id"] ." order by id desc limit 1")->fetch();

if($messager_avatar_arr[0] != "") {
$messager_avatar_rotate_degree = $messager_avatar_arr["rotate_degree"];
$messager_avatar_positions = explode(",",htmlspecialchars($messager_avatar_arr["positions"], ENT_QUOTES, "utf-8"));
}
else {
$messager_avatar_rotate_degree = 0;
$messager_avatar_positions = [0,0];	
}


$sent_message_last = 0;

for($x = 0;$x < count($messages_arr);$x++) {	

$message_raw = openssl_decrypt($messages_arr[$x]["message"],"aes-128-cbc","georgedies",OPENSSL_RAW_DATA,"dancewithdragons");

//if this message is a sent by this user to someone else, then set this variable to true, else set it to false.
$sent_message = ($messages_arr[$x]["message_from"] == $_SESSION["user_id"] ? 1 : 0);		
$message_is_first_in_sequence =  ($sent_message_last !=  $sent_message ? 1 : 0);		
			
if($messages_arr[$x]["message_type"] == "text-message") {
array_push($echo_arr[0], [
"message" => htmlspecialchars($message_raw, ENT_QUOTES, "utf-8"),
"message_type" => 0,
"read_yet" => htmlspecialchars($messages_arr[$x]["read_yet"], ENT_QUOTES, "utf-8"), 
"time_string" => date("H:i",strtotime($messages_arr[$x]["date_of"])),
"message_sent_by_base_user" => $sent_message,
"message_is_first_in_sequence" => $message_is_first_in_sequence,
"sender_info" => [
"id" => $messager_arr["id"],
"first_name" => htmlspecialchars($messager_arr["first_name"], ENT_QUOTES, "utf-8"),
"last_name" => htmlspecialchars($messager_arr["last_name"], ENT_QUOTES, "utf-8"),
"avatar" => htmlspecialchars($messager_arr["avatar_picture"], ENT_QUOTES, "utf-8"),
"avatar_rotate_degree" => htmlspecialchars($messager_avatar_rotate_degree, ENT_QUOTES, "utf-8"),
"avatar_positions" => $messager_avatar_positions
]
]);
}
else if($messages_arr[$x]["message_type"] == "emoji-message") {	
array_push($echo_arr[0], [
"message" => htmlspecialchars($message_raw, ENT_QUOTES, "utf-8"),
"message_type" => 1,
"read_yet" => htmlspecialchars($messages_arr[$x]["read_yet"], ENT_QUOTES, "utf-8"), 
"time_string" => date("H:i",strtotime($messages_arr[$x]["date_of"])),
"message_sent_by_base_user" => $sent_message,
"message_is_first_in_sequence" => $message_is_first_in_sequence,
"sender_info" => [
"id" => $messager_arr["id"],
"first_name" => htmlspecialchars($messager_arr["first_name"], ENT_QUOTES, "utf-8"),
"last_name" => htmlspecialchars($messager_arr["last_name"], ENT_QUOTES, "utf-8"),
"avatar" => htmlspecialchars($messager_arr["avatar_picture"], ENT_QUOTES, "utf-8"),
"avatar_rotate_degree" => htmlspecialchars($messager_avatar_rotate_degree, ENT_QUOTES, "utf-8"),
"avatar_positions" => $messager_avatar_positions
]
]);
}
else if($messages_arr[$x]["message_type"] == "file-message") {
$file_arr = $con->query("select * from sent_files where id = ". intval($messages_arr[$x]["message"]))->fetch();
array_push($echo_arr[0], [
"message" => $file_arr["path"],
"message_type" => 2,
"read_yet" => htmlspecialchars($messages_arr[$x]["read_yet"], ENT_QUOTES, "utf-8"), 
"time_string" => date("H:i",strtotime($messages_arr[$x]["date_of"])),
"message_sent_by_base_user" => $sent_message,
"message_is_first_in_sequence" => $message_is_first_in_sequence,
"sender_info" => [
"id" => $messager_arr["id"],
"first_name" => htmlspecialchars($messager_arr["first_name"], ENT_QUOTES, "utf-8"),
"last_name" => htmlspecialchars($messager_arr["last_name"], ENT_QUOTES, "utf-8"),
"avatar" => htmlspecialchars($messager_arr["avatar_picture"], ENT_QUOTES, "utf-8"),
"avatar_rotate_degree" => htmlspecialchars($messager_avatar_rotate_degree, ENT_QUOTES, "utf-8"),
"avatar_positions" => $messager_avatar_positions
]
]);
}

$sent_message_last = $sent_message;
}

#set all messages's read_yet to true
$con->exec("update messages set read_yet = true where chat_id = ". $chat_id." and message_from != ". $_SESSION["user_id"]);


if($current_status_string == "Online") {
$current_status = 1;
}
else if($current_status_string == "Here") {
$current_status = 2;
}
else {
$current_status = 0;	
}

array_push($echo_arr, [
"chat_id" => $chat_id,
"recipient_first_name" => htmlspecialchars($chat_recipient_info_arr["first_name"], ENT_QUOTES, "utf-8"),
"recipient_current_status" => $current_status,
"recipient_current_status_string" => $current_status_string
]);
}



echo json_encode($echo_arr);
unset($con);





function last_online($time) {
	
if($time == 0) {
return $time;	
}
	
$time = intval($time);	
	
$today = new DateTime(); // This object represents current date/time
$today->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$match_date = DateTime::createFromFormat( "Y-m-d H:i", date("Y-m-d H:i",$time));
$match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$diff = $today->diff( $match_date );
$diffDays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

if(time() - $time < 120) {# we say he was online that number of minutes ago.
return "Just Went Offline";
}	
else if($diffDays == 0) {
return "Last Online At " . date("H:i",$time);	
}
else if($diffDays == -1) {
return "Last Online Yesterday At " . date("H:i",$time);	
} 
else if(time() - $time < 604800){
return "Last Online On " . date("l",$time);	
}
else {
return "Last Online On " . date("j, M Y",$time);		
}
}



?>