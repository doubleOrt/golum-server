<?php
// when a user wants to upvote or downvote a comment, we make a call to this page.

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_POST["type"]) && isset($_POST["comment_id"]) && filter_var($_POST["comment_id"], FILTER_VALIDATE_INT) !== false) {

// upvote or downvote, 0 for up, 1 for down
$action_type = ($_POST["type"] == "upvote" ? 0 : 1);

$notification_type = 7 + $action_type;

$comment_arr = dataQuery("select user_id, post_id from post_comments where id = :comment_id", [":comment_id" => $_POST["comment_id"]])[0];

echo "thisUpvotesObject.removeClass('upvoteOrDownvoteActive');thisDownvotesObject.removeClass('upvoteOrDownvoteActive');";

if($con->query("select id from comment_upvotes_and_downvotes where comment_id = ". $_POST["comment_id"] ." and user_id = ". $_SESSION["user_id"])->fetch()["id"] != "") {

// delete the notification
$con->exec("delete from notifications where notification_from = ". $_SESSION["user_id"] ." and notification_to = ". $comment_arr["user_id"] ." and (type = 7 or type = 8) and extra = ". $comment_arr["post_id"] . " and extra2 = ". $_POST["comment_id"]);

if($action_type == $con->query("select type from comment_upvotes_and_downvotes where comment_id = ". $_POST["comment_id"] ." and user_id = ". $_SESSION["user_id"])->fetch()["type"]) {
$con->exec("delete from comment_upvotes_and_downvotes where comment_id = ". $_POST["comment_id"] ." and user_id = ". $_SESSION["user_id"]);	
}
else {
$con->exec("update comment_upvotes_and_downvotes set type = ". $action_type .", time = ". time() ." where comment_id = ". $_POST["comment_id"] ." and user_id = ". $_SESSION["user_id"]);
echo ($action_type == 0 ? "thisUpvotesObject.addClass('upvoteOrDownvoteActive');" : "thisDownvotesObject.addClass('upvoteOrDownvoteActive');");
if($_SESSION["user_id"] != $comment_arr["user_id"]) {
// insert a notification	
$con->exec("insert into notifications (notification_from,notification_to,time,type,extra,extra2) values (". $_SESSION["user_id"] .",". $comment_arr["user_id"] .",". time() .",". $notification_type .",". $comment_arr["post_id"] .",". $_POST["comment_id"] .");");	
}
}
update_comment($_POST["comment_id"]);
$new_upvotes_number = $con->query("select upvotes from post_comments where id = ". $_POST["comment_id"])->fetch()["upvotes"];
$new_downvotes_number = $con->query("select downvotes from post_comments where id = ". $_POST["comment_id"])->fetch()["downvotes"];
echo "thisUpvotesNumberObject.html('". ($new_upvotes_number > 0 ? ("(" . htmlspecialchars($new_upvotes_number) . ")") : "") ."');";
echo "thisDownvotesNumberObject.html('". ($new_downvotes_number > 0 ? ("(" . htmlspecialchars($new_downvotes_number, ENT_QUOTES, "utf-8") . ")") : "") ."');";
}
else {
	
$con->exec("insert into comment_upvotes_and_downvotes (comment_id,user_id,time,type) values(". $_POST["comment_id"] .",". $_SESSION["user_id"] .",". time() .",". $action_type .")");

update_comment($_POST["comment_id"]);
$new_upvotes_number = $con->query("select upvotes from post_comments where id = ". $_POST["comment_id"])->fetch()["upvotes"];
$new_downvotes_number = $con->query("select downvotes from post_comments where id = ". $_POST["comment_id"])->fetch()["downvotes"];

// insert a notification
if($comment_arr["user_id"] != $_SESSION["user_id"]) {
$con->exec("insert into notifications (notification_from,notification_to,time,type,extra,extra2) values (". $_SESSION["user_id"] .",". $comment_arr["user_id"] .",". time() .",". $notification_type .",". $comment_arr["post_id"] .",". $_POST["comment_id"] .");");	
$notification_id = $con->lastInsertId();

$socket_message = [
"update_type" => "1",
"notification_id" => $notification_id,
"notification_time" => time(), 
"notification_time_string" => time_to_string(time()),
"notification_type" => $notification_type, 
"notification_extra" => htmlspecialchars($comment_arr["post_id"], ENT_QUOTES, "utf-8"), 
"notification_extra2" => $_POST["comment_id"], 
"notification_extra3" => "0", 
"notification_read_yet" => "0", 
"notification_and_others" => "0", 
"notification_to" => htmlspecialchars($comment_arr["user_id"], ENT_QUOTES, "utf-8"),
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

echo "thisUpvotesNumberObject.html('". ($new_upvotes_number > 0 ? ("(" . htmlspecialchars($new_upvotes_number, ENT_QUOTES, "utf-8") . ")") : "") ."');";
echo "thisDownvotesNumberObject.html('". ($new_downvotes_number > 0 ? ("(" . htmlspecialchars($new_downvotes_number, ENT_QUOTES, "utf-8") . ")") : "") ."');";
echo ($action_type == 0 ? "thisUpvotesObject.addClass('upvoteOrDownvoteActive');" : "thisDownvotesObject.addClass('upvoteOrDownvoteActive');");
}	

}

// just want to reuse some code here, we need to update the comments' upvote and downvote cols once they are changed in the comment_upvotes_and_downvotes table.
function update_comment($comment_id) {
global $con, $action_type;	
$con->exec("update post_comments set upvotes = ". $con->query("select count(id) from comment_upvotes_and_downvotes where comment_id = ". $comment_id ." and type = 0")->fetch()[0] ." where id = ". $comment_id);	
$con->exec("update post_comments set downvotes = ". $con->query("select count(id) from comment_upvotes_and_downvotes where comment_id = ". $comment_id ." and type = 1")->fetch()[0] ." where id = ". $comment_id);		
}



?>