<?php
# we make an ajax call to this page everytime there are new messages or on page load to update the chat portals with the latest new messages notifications.

require_once "common_requires.php";


// this function is used to calculate message times.
function last_message($time) {

$today = new DateTime(); // This object represents current date/time
$today->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$match_date = DateTime::createFromFormat( "Y-m-d H:i", date("Y-m-d H:i",$time));
$match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$diff = $today->diff( $match_date );
$diffDays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

if(time() - $time < 60) {
return "Just Now";	
}
if(time() - $time < 60) {
return "Less Than A Minute Ago";	
}	
else if(time() - $time < 3600) {# we say he was online that number of minutes ago.
return intval((time() - $time)/60) . " Minute". (intval((time() - $time)/60) != "1" ? "s" : "") ." Ago";
}	
else if($diffDays == 0)  {
return "Today At " . date("H:i",$time);	
}
else if($diffDays == -1) {
return "Yesterday At " . date("H:i",$time);	
}
else if(time() - $time < 604800){
return date("l",$time);	
}
else {
return date("j, M Y",$time);		
}
}



if(isset($_SESSION["user_id"])) {
	
	
	
$echo_arr = [];


// in this page we give our js file key value pairs of chat ids and new messages from each one.

$chats_arr = $con->query("select id, (select count(id) from messages where chat_id = chats.id and message_from != ". $_SESSION["user_id"] ." and read_yet = false) as chat_unread_messages from chats where chatter_ids like '%".$_SESSION["user_id"]."%'")->fetchAll(); 		


for($i = 0;$i < count($chats_arr);$i++) {

$new_messages_arr = $con->query("select message,message_type,message_from,date_of from messages where chat_id = ". $chats_arr[$i]["id"])->fetchAll();

$new_messages_num = 0;

$latest_message = "";

$latest_message_date = "";

for($x = 0;$x < count($new_messages_arr);$x++) {
	
if($new_messages_arr[$x]["message_from"] != $_SESSION["user_id"]) {	
$new_messages_num++;
}
if($x == count($new_messages_arr)-1) {
#the date of the latest message.
$latest_message_date = last_message(strtotime($new_messages_arr[$x]["date_of"]));

if($new_messages_arr[$x]["message_type"] == "text-message") {	
$latest_message = openssl_decrypt($new_messages_arr[$x]["message"],"aes-128-cbc","georgedies",OPENSSL_RAW_DATA,"dancewithdragons");
}
else if($new_messages_arr[$x]["message_type"] == "emoji-message") {
$latest_message = "Emoji";	
}
else if($new_messages_arr[$x]["message_type"] == "file-message") {
$latest_message = "File";	
}
}	
}


#if there are no new latest messages
if($latest_message == "") {

$latest_message_arr = $con->query("select message,message_type,date_of from messages where chat_id = ". $chats_arr[$i]["id"]." order by id desc limit 1")->fetch();

#if the users have never sent each other any messages
if($latest_message_arr["message"] == "") {
$latest_message_date = "";	
$latest_message = "No Messages";	
}
else {
$latest_message_date = "";	
$latest_message = "No New Messages";	
}
}


array_push($echo_arr,[htmlspecialchars($chats_arr[$i]["id"], ENT_QUOTES, "utf-8"), $chats_arr[$i]["chat_unread_messages"], htmlspecialchars($latest_message, ENT_QUOTES, "utf-8"), $latest_message_date]);	
}

echo json_encode($echo_arr);
}





?>