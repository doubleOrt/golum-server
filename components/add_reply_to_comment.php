<?php
// when a user wants to reply to a comment, we make a call to this page.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_comment_data_function.php";

$MAXIMUM_COMMENT_LENGTH = 800;

$echo_arr = ["",""];

if(isset($_POST["comment_id"]) && isset($_POST["reply"]) && filter_var($_POST["comment_id"], FILTER_VALIDATE_INT) !== false) {

if(strlen($_POST["reply"]) > $MAXIMUM_COMMENT_LENGTH) {
$echo_arr[1] = "Comment cannot be longer than ". $MAXIMUM_COMMENT_LENGTH ." characters!";
die();	
}


// pdo parameters must be passed by reference, and thus, this useless var.
$reply_time = time();

$prepared = $con->prepare("insert into comment_replies (comment_id,user_id,comment,time,is_reply_to) values(:comment_id,:user_id,:comment,:time,:is_reply_to)");
$prepared->bindParam(":comment_id",$_POST["comment_id"]);
$prepared->bindParam(":user_id",$GLOBALS["base_user_id"]);
$prepared->bindParam(":comment",$_POST["reply"]);
$prepared->bindParam(":time",$reply_time);


/* if the user is replying someone inside a reply, oh yeah, please don't confuse the is_reply_to column or this reply_to var with something else, this is useful for one case only,
when the user is replying someone inside a reply */
if(isset($_POST["is_reply_to"])) {
$prepared->bindParam(":is_reply_to",$_POST["is_reply_to"]);	
}
else {
// because can't pass directly	
$is_reply_to = 0;	
$prepared->bindParam(":is_reply_to",$is_reply_to);	
}

if($prepared->execute()) {
$reply_id = $con->lastInsertId();	

$comment_arr = custom_pdo("select user_id, post_id from post_comments where id = :comment_id", [":comment_id" => $_POST["comment_id"]])->fetch();

$poster_id = custom_pdo("select posted_by from posts where id = :post_id", [":post_id" => $comment_arr["post_id"]])->fetch()[0];
	
$reply_arr = custom_pdo("SELECT * FROM (SELECT *, (SELECT COUNT(id) FROM comment_replies WHERE is_reply_to = comment_replies.id) AS replies, (SELECT type FROM reply_upvotes_and_downvotes WHERE user_id = :base_user_id AND comment_id = comment_replies.id) as base_user_opinion, (SELECT post_id from post_comments where id = :comment_id) as reply_owner_post_id FROM comment_replies) comment_replies LEFT JOIN (SELECT user_id AS user_id2,post_id AS post_id2,option_index FROM post_votes) post_votes ON comment_replies.user_id = post_votes.user_id2 AND reply_owner_post_id = post_votes.post_id2 WHERE comment_replies.id = :reply_id", [":base_user_id" => $GLOBALS["base_user_id"], ":comment_id" => $_POST["comment_id"], ":reply_id" => $reply_id])->fetch();
$reply_arr["original_post_by"] = $poster_id;
	
$echo_arr[0] = get_comment_data($reply_arr);	


// if replier is not a user replying to his own comment, send them a notification.
if($comment_arr["user_id"] != $GLOBALS["base_user_id"]) {
//insert a notification 
custom_pdo("insert into notifications (notification_from,notification_to,time,type,extra,extra2,extra3) values ( :base_user_id, :commenter_id, :time, 3, :comment_id, :post_id, :reply_id)", [":base_user_id" => $GLOBALS["base_user_id"], ":commenter_id" => $comment_arr["user_id"], ":time" => time(), ":comment_id" => $_POST["comment_id"], ":post_id" => $comment_arr["post_id"], ":reply_id" => $reply_id]);		
$notification_id = $con->lastInsertId();

$socket_message = [
"update_type" => "1",
"notification_id" => $notification_id,
"notification_time" => time(), 
"notification_time_string" => time_to_string(time()),
"notification_type" => 3, 
"notification_extra" => htmlspecialchars($_POST["comment_id"], ENT_QUOTES, "utf-8"), 
"notification_extra2" => $comment_arr["post_id"], 
"notification_extra3" => $reply_id, 
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

// if the user is replying to a reply, then we need to send a notification to the replied to as well.
if(isset($_POST["is_reply_to"]) && $_POST["is_reply_to"] != $GLOBALS["base_user_id"] && $_POST["is_reply_to"] != $comment_arr["user_id"]) {
// if the first one is true, it means we just sent a notification to the user in the above line. so it is unnecessary to do so again.  the second just assures we don't send a notification to a user when he replies to his own reply.
custom_pdo("insert into notifications (notification_from,notification_to,time,type,extra,extra2,extra3) values ( :base_user_id, :is_reply_to, :time, 3, :comment_id, :post_id, :reply_id)", [":base_user_id" => $GLOBALS["base_user_id"], ":is_reply_to" => $_POST["is_reply_to"], ":time" => time(), ":comment_id" => $_POST["comment_id"], ":post_id" => $comment_arr["post_id"], ":reply_id" => $reply_id]);		
$notification_id = $con->lastInsertId();

$socket_message = [
"update_type" => "1",
"notification_id" => $notification_id,
"notification_time" => time(), 
"notification_time_string" => time_to_string(time()),
"notification_type" => 3, 
"notification_extra" => htmlspecialchars($_POST["comment_id"], ENT_QUOTES, "utf-8"), 
"notification_extra2" => $comment_arr["post_id"], 
"notification_extra3" => $reply_id, 
"notification_read_yet" => "0", 
"notification_and_others" => "0", 
"notification_to" => htmlspecialchars($_POST["is_reply_to"], ENT_QUOTES, "utf-8"),
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
	
} 

echo json_encode($echo_arr);

unset($con);

?>