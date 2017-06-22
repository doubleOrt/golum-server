<?php
# we use this page for long polling new messages and such.

/* shm1 means new messages, shm2 is the updates every user sends every 2 secs via our longpoll.php page, shm3, we set it to 1 when a user presses the logout button and when we find out
that he hasn't sent any new shm2 updates in the last x secs. $shm4 is the chat modal the chat participant has currently opened, $shm5 is the one we set to the id of a file-message 
whenever a user opens it. so that the sender's client-side can receive a message that tells it to hide that pic in 10 secs. shm 6 is used to get live notifications.
shm7 is used to get live replies whenever a user opens the replies modal. */



require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_comment_function.php";


set_time_limit(0);
session_write_close();

function last_online($time) {
	
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



$echo_arr = ["","","","","",""];


$rawStr =  read_shm($_SESSION["user_id"] . "" . 4);

if(isset($_GET["currently_chatting"])) {
$chat_participants_arr = explode("-",$con->query("select chatter_ids from chats where id = ".$_GET["currently_chatting"])->fetch()["chatter_ids"]);	

if($rawStr != $_GET["currently_chatting"]) {
write_shm($_SESSION["user_id"] . "" . 4,$_GET["currently_chatting"]);	
}
}	
else {
write_shm($_SESSION["user_id"] . "" . 4,"none");	
}

	
	

$counter = 0;	
	
while(true) {
clearstatcache();


# abort after 16 secs (counter * sleeptime).	
if($counter == 8) {
echo "";
break;	
}	



write_shm($_SESSION["user_id"] . "" . 2,time());

if(isset($_GET["currently_chatting"])) {
	
for($i = 0;$i<count($chat_participants_arr);$i++) {


#if our current array index is the querying user, then continue, since it is his recipient's status we want, not his.
if($chat_participants_arr[$i] == $_SESSION["user_id"]) {
continue;	
}
	

$rawStr = read_shm($chat_participants_arr[$i] . "" . 2);	

// $rawStr2 == 1 means the user is currently logged out, we know this because he logged out by pressing the log out button, and $rawStr2 == 0 means he is logged in because he pressed the login button.
$rawStr2 =  read_shm($chat_participants_arr[$i] . "" . 3);


$rawStr3 =  read_shm($chat_participants_arr[$i] . "" . 4);



if($rawStr2 == "1" && $_GET["current_status"] != 0)  {
$echo_arr[0] = last_online($rawStr);
}
if(time() - $rawStr > 5 && $_GET["current_status"] != 0 && $rawStr2 != "1")  {
$echo_arr[0] = last_online($rawStr);
write_shm($chat_participants_arr[$i] . "" . 4,"none"); 
}
if(time() - $rawStr < 5 && $_GET["current_status"] != 1 && $rawStr2 != "1" && $_GET["currently_chatting"] != $rawStr3) {
$echo_arr[0] = "Online";
}
if(time() - $rawStr < 5 && $_GET["current_status"] != 2 && $rawStr2 != "1" && $_GET["currently_chatting"] == $rawStr3) {
$echo_arr[0] = "Here";	
}


}
}


// the sender of a message sets the shm of the receiver (receipient's id + "" + 1) to true when he sends the message, so that the receiver can update his chat modal via this following snippet.
$rawStr = read_shm($_SESSION["user_id"] . "" . 1);

//check if user has any new messages. if this is true, the client side will make an ajax call to the new_messages.php file.
if($rawStr == "true"  && isset($_GET["currently_chatting"])) {
$echo_arr[1] = "true";
}

// if this is true, the client side will make an ajax call to the chat_portal_activities.php file.
if($rawStr == "true") {
$echo_arr[2] = "true";	
}


// this is for the snapchat effect of text and emojis, so we only hide them from the sender after the receiver has seen them.
if(isset($_GET["currently_chatting"]) && $_GET["sent_unread_messages"] == "true") {

$chat_participant_has_opened_same_chat_modal = false;

for($i = 0;$i < count($chat_participants_arr);$i++) {
if($chat_participants_arr[$i] == $_SESSION["user_id"]) {
continue;	
}

$rawStr = read_shm($chat_participants_arr[$i] . "" . 4);


if($rawStr == $_GET["currently_chatting"]) {
$chat_participant_has_opened_same_chat_modal = true;	
}
else {
$chat_participant_has_opened_same_chat_modal = false;	
}
}
	
if($chat_participant_has_opened_same_chat_modal == true) {
$echo_arr[3] = "true";	
}
	
}


// this is useful for one case, if both users are active in a chat, one sends the other a pic, the receiver sees it, and so via this snippet we tell the sender's clientside that the receiver saw his message and set a 10 sec timeout to hide it from the sender.
if(isset($_GET["currently_chatting"])) {

for($i = 0;$i < count($chat_participants_arr);$i++) {
if($chat_participants_arr[$i] == $_SESSION["user_id"]) {
continue;	
}

$rawStr =  read_shm($chat_participants_arr[$i] . "" . 5);

if($rawStr != "none") {
$echo_arr[4] = $rawStr;	
write_shm($chat_participants_arr[$i] . "" . 5,"none");
}

}

}


// check if there are any new notifications
$rawStr6 = read_shm($_SESSION["user_id"] . "" . 6);	

if($rawStr6 == "true") {
$shmid = $_SESSION["user_id"] . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("false"), 0);
shmop_close($shm);	
$echo_arr[5] = $con->query("select count(*) from (select *, (count(*) - 1) and_others, @rn:=@rn+1 AS new_id from (select * from notifications) t1, (SELECT @rn:=0) t2 where notification_to = ". $_SESSION["user_id"] ." and read_yet = 0 group by type, extra, read_yet) t3")->fetch()[0];
}





// checks if there is an update anywhere, if there is, send it to the clientside.
for($i = 0;$i<count($echo_arr);$i++)	{
if($echo_arr[$i] !== "") {
echo json_encode($echo_arr);	
unset($con);
break 2;	
}	
}

$counter++;
sleep(2);	
}


?>