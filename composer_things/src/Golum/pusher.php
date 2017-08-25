<?php
namespace Golum;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\MessageComponentInterface;

class Pusher implements WampServerInterface, MessageComponentInterface {    

private $connection;
public $user_registration_ids;
public $user_statuses;

public function __construct() {
global $con;		
$this->connection = $con;
$this->user_registration_ids = [];
$this->user_statuses = [];
}

/**
* A lookup of all the topics clients have subscribed to
*/
protected $subscribedTopics = array();
protected $connections_and_user_ids = array();

public function onSubscribe(ConnectionInterface $conn, $topic) {
$topic_arr = explode("_", $topic->getId());	
$user_id = $topic_arr[count($topic_arr) - 1];

// so that the user cannot impersonate another user, therefore receiving their sensitive information such as their messages.
if(count($topic_arr) == 2 && $topic_arr[0] == "user" && $user_id != $conn->Session->get("user_id")) {
return false;	
}

if($topic_arr[0] == "user" && count($topic_arr) === 2) {
if(isset($this->subscribedTopics[$topic->getId()]))	{
// remove the user's subscriptions from a previous connection, just in case they are still there.	
$this->remove_connection_subscriptions($this->subscribedTopics[$topic->getId()]->getIterator()->getInfo());	
}
}

$this->subscribedTopics[$topic->getId()] = $topic;
}

/**
* @param string JSON'ified string we'll receive from ZeroMQ
*/
public function server_side_publish($entry) {
$data = json_decode($entry, true);

// update_type 0 is a new message
if($data["update_type"] == "0") {
for($i = 0; $i < count($data["chatter_ids"]); $i++) {
	
if($data["chatter_ids"][$i] == $data["sender_info"]["id"]) {
continue;	
}	

$user_topic = "user_" . $data["chatter_ids"][$i];

if(array_key_exists($user_topic, $this->subscribedTopics)) {
$this->subscribedTopics[$user_topic]->broadcast(json_encode([
"type" => "1",
"data" => $data
]));
}
if(array_key_exists($user_topic, $this->user_registration_ids)) {

$push_notification_title = "New message from ". ($data["sender_info"]["first_name"] . " " . $data["sender_info"]["last_name"]) . "...";

if($data["message_type"] == 0) {
$push_notification_message = $data["message"];
}
else if($data["message_type"] == 1) {
$push_notification_message = "Emoji";
}
else if($data["message_type"] == 2) {
$push_notification_message = "File";
}

send_push_notification("user_" . $data["chatter_ids"][$i], 1, $push_notification_title, $push_notification_message, [$this->user_registration_ids[$user_topic]], $data);
}

}
}
// update_type 1 means new notifications
else if($data["update_type"] == "1") {
if(array_key_exists("notification_to", $data)) {
$user_topic = "user_" . $data["notification_to"];
if(array_key_exists($user_topic, $this->subscribedTopics)) {
$this->subscribedTopics[$user_topic]->broadcast(json_encode([
"type" => "2",
"data" => $data
]));
}
if(array_key_exists($user_topic, $this->user_registration_ids)) {
/* notification types included in this array won't be sent push notifications for, 
for example, we don't want to send push notifications for comment upvotes/downvotes 
in any case, so we have included notification types 7 and 8 in the array. */
$accepted_notification_types_for_push = [5,7,8,9,10];

$push_notification_title = generate_notification_string(($data["notification_sender_info"]["first_name"] . " " . $data["notification_sender_info"]["last_name"]), $data["notification_type"]) . "...";
$push_notification_message = date("d/m/Y \A\\t H:i", $data["notification_time"]);

if(in_array($data["notification_type"], $accepted_notification_types_for_push) === false) {
/* note the extra "\" before "\t" in the "message" argument, that is because if you use double quotes, a "\t" will be interpreted as a tab character, to prevent this, we escape the "\t" with an extra "\", another option would be to use single quotes, i prefer this one though. */
send_push_notification("user_" . $data["notification_to"], 0, $push_notification_title, $push_notification_message, [$this->user_registration_ids[$user_topic]], $data);
}

}
}
}

}


public function onUnSubscribe(ConnectionInterface $conn, $topic) {
}

public function onOpen(ConnectionInterface $conn) {
$user_id = $conn->Session->get("user_id");	
/* this conditional takes care of garbage collecting the 
topics that the user that just connected had subscribed to
previously, and had somehow managed to disconnect without 
triggering our onClose event, this snippet is basically the 
same in concept as the snippet that does the same thing 
in the onPublish method. Except that this one will evaluate
to true only if that one doesn't. */
if(isset($this->subscribedTopics["user_" . $user_id])) {
$user_topic_subscribers = $this->subscribedTopics["user_" . $user_id]->getIterator();	
if($user_topic_subscribers->count() > 0) {	
/* this is just to get an instance of the leftover connection 
of the user that just opened the connection, so that we can 
ultimately pass it as the first argument for 
remove_connection_subscriptions(). */
$user_topic_subscribers->rewind();
$user_connection = $user_topic_subscribers->current();
$this->remove_connection_subscriptions($user_connection);	
}
else {
unset($this->subscribedTopics["user_" . $user_id]);	
}	
}
}

// need this method solely because it is a must since we are implementing MessageComponentInterface.
public function onMessage(ConnectionInterface $from, $msg){
}
	
public function onClose(ConnectionInterface $conn) {
$this->remove_connection_subscriptions($conn);	
}

public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
}

public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {

if(count($event) >= 3 && filter_var($event[2], FILTER_VALIDATE_INT) !== false) {
// if event type is 0, then it means that the client is querying a user's online/offline state
if($event[0] === 0) {
		
if(substr($event[1], 0, 4) === "user") {
$user_id = explode("_",$event[1])[1];
if(filter_var($user_id, FILTER_VALIDATE_INT) !== false) {
	
if(isset($this->user_statuses["user_" . $user_id])) {
	
$is_user_online = (time() - $this->user_statuses["user_" . $user_id]["last_ping"] <= 5 ? true : $this->user_statuses["user_" . $user_id]["last_ping"]);	
if($is_user_online !== true && $this->user_statuses["user_" . $user_id]["subscriptions_removed_yet"] === false) {
		
/* this conditional takes care of garbage collecting the 
topics that the inquired user had subscribed to, just in 
case they had gotten disconnected in a way which did not 
trigger our onClose event (in my experience, this occurs 
if they change their WIFI from online to offline). */
if(isset($this->subscribedTopics["user_" . $user_id])) {
$user_topic_subscribers = $this->subscribedTopics["user_" . $user_id]->getIterator();
if($user_topic_subscribers->count() > 0) {	
/* this is just to get an instance of the leftover connection 
of the user we are talking about, so that we can ultimately 
pass it as the first argument for remove_connection_subscriptions(). */
$user_topic_subscribers->rewind();
$user_connection = $user_topic_subscribers->current();
$this->remove_connection_subscriptions($user_connection);	
}
else {
unset($this->subscribedTopics["user_" . $user_id]);	
}
}

$this->user_statuses["user_" . $user_id]["subscriptions_removed_yet"] = true;

/* if the user has gone offline, we want to insert their 
last seen into the database, so that it can be retrieved 
later, if the websocket server gets restarted for some 
reason. */
if($is_user_online !== true) {
$this->set_db_user_last_seen($user_id);	
}

}

}	
/* no last seen field exists here, should 
exist in the database then, so we try to 
get it. */
else {	
$user_db_last_seen = $this->get_db_user_last_seen($user_id);
/* if the last_seen field in the database actually 
contained a timestamp, then set is_user_online to 
that, else just set it to 0 so that the string 
"offline" is sent back to the inquirer. */
$is_user_online = $user_db_last_seen != "" ? $user_db_last_seen : 0;
}

if($is_user_online === true) {
$user_state_arr = json_encode([
"type" => "0", 
"data" => [
"request_type" => "0", 
"request_id" => $event[2],
"user_id" => $user_id, 
"current_state" => "Online"
]
]);
}
else {
$user_state_arr = json_encode([
"type" => "0", 
"data" => [
"request_type" => "0", 
"request_id" => $event[2],
"user_id" => $user_id, 
"current_state" => last_online($is_user_online)
]
]);
}
// send the online/offline state back to the client
$conn->event($topic, $user_state_arr); 
}
}
}
}
if(count($event) >= 2) {
/* event 1 is for registering a user's device id so we can send them push notifications or for removing 
that device id so that the user does not get notifications after they log out */
if($event[0] === 1) {
$user_id = explode("_", $topic->getId())[count(explode("_", $topic->getId())) - 1];

// so that a user cannot impersonate another user, therefore receiving their sensitive notifications such as new messages, etc...
if(filter_var($user_id, FILTER_VALIDATE_INT) !== false && $conn->Session->get("user_id") == $user_id) {
	
// event[1] === 0 means a user wants to receive push notifications
if($event[1] === 0) {
/* search the $this->user_registration_ids array and remove elements that have the same 
registration id as the one being sent by the client-side right now, so that multiple 
users using the same device don't end up receiving one another's notifications */
foreach($this->user_registration_ids as $key => $value) {
if($value == $event[2]) {
unset($this->user_registration_ids[$key]);
}
}
/* add a new element to the $this->user_registration_ids id with the topic of the 
publish as the key and the registration-id as the value */
$this->user_registration_ids[$topic->getId()] = $event[2];
}
/* $event[1] === 1 means a user wants to unsubscribe from receiving push notifications, 
usually this is done so that user's don't get notifications after logging out */
else if($event[1] === 1) {
foreach($this->user_registration_ids as $key => $value) {
if($key == $topic->getId()) {
unset($this->user_registration_ids[$key]);
}
}
}

}
}

}
if(count($event) >= 1) {
// event 2 is just a client pinging us to make us aware that they are still connected.
if($event[0] === 2) {
$topic_id_arr = explode("_", $topic->getId());
$user_id = $topic_id_arr[1];
// this conditional makes sure that user's cannot impersonate one another, and that the topic id is the exact same one we expect.
if($user_id == $conn->Session->get("user_id") && count($topic_id_arr) === 2 && filter_var($user_id, FILTER_VALIDATE_INT) !== false) {
$this->user_statuses[$topic->getId()] = ["last_ping" => time(), "subscriptions_removed_yet" => false];	
}
}	
} 

}

public function onError(ConnectionInterface $conn, \Exception $e) {
}

public function remove_connection_subscriptions($conn) {


/* iterate through all the topics and unsubscribe 
the user from everything they have subscribed to.*/
foreach($this->subscribedTopics as $topic) {
if($topic->getIterator()->contains($conn)) {
$topic->getIterator()->detach($conn);	
}
if(count($topic->getIterator()) < 1) {
unset($this->subscribedTopics[$topic->getId()]);
}
}

}

public function get_db_user_last_seen($user_id) {
$prepared = $this->connection->prepare("select last_seen from users where id = :user_id");
$prepared->execute([":user_id" => $user_id]);
return $prepared->fetch()[0];	
}

public function set_db_user_last_seen($user_id) {
if($this->connection->prepare("update users set last_seen = :time where id = :user_id")->execute([":time" => time(), ":user_id" => $user_id])) {
return true;	
}
else {
return false;	
}
}

}


$pushed_notifications = [];
function send_push_notification($target_user_topic_id, $category, $title, $message, $registration_ids, $data) {
global $pushed_notifications;					

if($category === 0) {
$pushed_category_notifications_key_name = "pushed_category_0_notifications";
}
else if($category === 1) {
$pushed_category_notifications_key_name = "pushed_category_1_notifications";	
}

$API_KEY = "AIzaSyCKmj22oWTw5L6qaDmI9PIHGa5Jb-asEB4";

$REMOVE_ENTRIES_OLDER_THAN = 259200;

$notification_id = 0;	
if(isset($pushed_notifications[$target_user_topic_id])) {
if($category === 0) {
foreach($pushed_notifications[$target_user_topic_id][$pushed_category_notifications_key_name] as $index => $entry) {
if(time() - $entry["notification_time"] > $REMOVE_ENTRIES_OLDER_THAN) {
unset($pushed_notifications[$target_user_topic_id][$pushed_category_notifications_key_name][$index]);
continue;
}
if($entry["notification_data"]["notification_type"] === $data["notification_type"] && $entry["notification_data"]["notification_extra"] == $data["notification_extra"]) {
$notification_id = $entry["notification_id"];
}
}
}
else if($category === 1) {
foreach($pushed_notifications[$target_user_topic_id][$pushed_category_notifications_key_name] as $index => $entry) {
if(time() - $entry["notification_time"] > $REMOVE_ENTRIES_OLDER_THAN) {
unset($pushed_notifications[$target_user_topic_id][$pushed_category_notifications_key_name][$index]);
continue;
}	
/* don't use "===" here */
if($entry["notification_data"]["chat_id"] == $data["chat_id"]) {
$notification_id = $entry["notification_id"];
}
}	
}
}

if($notification_id === 0) {
$notification_id = explode("_", $target_user_topic_id)[1] . (isset($pushed_notifications[$target_user_topic_id]) ? $pushed_notifications[$target_user_topic_id]["total_number"] : 1);
}    


// Set POST variables
$url = 'https://android.googleapis.com/gcm/send';

$fields = array(
"registration_ids" => $registration_ids,
"priority" => "high",
"data" => [
"title" => $title,
"message" => $message,
"push_notification_category" => $category,
"summaryText" => "New Notification",
"style" => "inbox",
"data_arr" => json_encode($data),
"notId" => $notification_id 
]
);
$headers = array(
'Authorization: key=' . $API_KEY,
'Content-Type: application/json'
);

// Open connection
$ch = curl_init();

// Set the URL, number of POST vars, POST data
curl_setopt( $ch, CURLOPT_URL, $url);
curl_setopt( $ch, CURLOPT_POST, true);
curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

// Execute post
$result = json_decode(curl_exec($ch));

// Close connection
curl_close($ch);


// checks the results for unregistered ids and removes them if they exist in our user_registration_ids.
for($i = 0; $i < count($result->results); $i++) {
if(isset($result->results[$i]->error) && $result->results[$i]->error == "NotRegistered") {
foreach($this->user_registration_ids as $key => $value) {
if($value == $registration_ids[$i]) {
unset($this->user_registration_ids[$key]);
}
}
}
}


// push the value into the $pushed_notifications array.
if(isset($pushed_notifications[$target_user_topic_id])) {
array_push($pushed_notifications[$target_user_topic_id][$pushed_category_notifications_key_name], ["notification_id" => $notification_id, "notification_time" => time(), "notification_data" => $data]);
$pushed_notifications[$target_user_topic_id]["total_number"]++;
}
else {
if($category === 0) {	
$pushed_notifications[$target_user_topic_id] = ["total_number" => 1, "pushed_category_0_notifications" => [["notification_id" => $notification_id, "notification_time" => time(), "notification_data" => $data]], "pushed_category_1_notifications" => []];
}
else if($category === 1) {
$pushed_notifications[$target_user_topic_id] = ["total_number" => 1, "pushed_category_1_notifications" => [["notification_id" => $notification_id, "notification_time" => time(), "notification_data" => $data]], "pushed_category_0_notifications" => []];	
}
}	
}


// use this function to generate a human-friendly message for the push notifications.
function generate_notification_string($notification_from_full_name, $notification_type) {

if($notification_type == 1) {
return 	$notification_from_full_name .  " Voted On Your Post";
}
else if($notification_type == 2) {
return $notification_from_full_name . " Commented On Your Post";	
}
else if($notification_type == 3) {
return $notification_from_full_name . " Replied To Your Comment";		
}
else if($notification_type == 4) {
return $notification_from_full_name . " Wants You To See This Post";
}
else if($notification_type == 5) {
return $notification_from_full_name . " Favorited Your Post";	
}
else if($notification_type == 6) {
return $notification_from_full_name . "  Started Following You";	
}
else if($notification_type == 7) {
return $notification_from_full_name . " Upvoted Your Comment";	
}
else if($notification_type == 8) {
return $notification_from_full_name . " Downvoted Your Comment";		
}
else if($notification_type == 9) {
return $notification_from_full_name . " Upvoted Your Reply";		
}
else if($notification_type == 10) {
return $notification_from_full_name . " Downvoted Your Reply";		
}
else if($notification_type == 11) {
return $notification_from_full_name . "Reacted To The Post You Sent Him";		
}	

}





function last_online($time) {

if($time == 0) {
return "Offline";	
}

$time = intval($time);	

date_default_timezone_set("Asia/Baghdad");

$today = new \DateTime(); // This object represents current date/time
$today->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$match_date = \DateTime::createFromFormat( "Y-m-d H:i", date("Y-m-d H:i",$time));
$match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$diff = $today->diff( $match_date );
$diffDays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

if(time() - $time < 120) {# we say he was online that number of minutes ago.
return "Just went offline";
}	
else if($diffDays == 0) {
return "Last Online At " . date("H:i", $time);	
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
