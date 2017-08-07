<?php

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_comment_data_function.php";

$MAXIMUM_COMMENT_LENGTH = 800;

$echo_arr = ["",""];

if(isset($_POST["post_id"]) && filter_var($_POST["post_id"], FILTER_VALIDATE_INT) !== false && isset($_POST["comment"])) {

if(strlen($_POST["comment"]) > $MAXIMUM_COMMENT_LENGTH) {
$echo_arr[1] = "Comment cannot be longer than ". $MAXIMUM_COMMENT_LENGTH ." characters!";
die();	
}


// pdo parameters must be passed by reference, and thus, this useless var.
$comment_time = time();

$prepared = $con->prepare("insert into post_comments (post_id,user_id,comment,time) values(:post_id,:user_id,:comment,:time)");
$prepared->bindParam(":post_id",$_POST["post_id"]);
$prepared->bindParam(":user_id",$GLOBALS["base_user_id"]);
$prepared->bindParam(":comment",$_POST["comment"]);
$prepared->bindParam(":time",$comment_time);

if($prepared->execute()) {
$comment_id = $con->lastInsertId();	

$poster_id = custom_pdo("select posted_by from posts where id = :post_id", [":post_id" => $_POST["post_id"]])->fetch()["posted_by"];
	
$comment_arr = custom_pdo("SELECT *, (SELECT COUNT(id) FROM comment_replies WHERE comment_id = post_comments.id) AS replies, (SELECT type FROM comment_upvotes_and_downvotes WHERE user_id = ". $GLOBALS["base_user_id"] ." AND comment_id = post_comments.id) as base_user_opinion FROM post_comments LEFT JOIN (SELECT user_id AS user_id2,post_id AS post_id2,option_index FROM post_votes) post_votes ON post_comments.user_id = post_votes.user_id2 AND post_comments.post_id = post_votes.post_id2 WHERE post_comments.id = :comment_id", [":comment_id" => $comment_id])->fetch();	
$comment_arr["original_post_by"] = $poster_id;

$echo_arr[0] = get_comment_data($comment_arr);	

// if commenter is not a user commenting on his own post, send them a notification.
if($poster_id != $GLOBALS["base_user_id"]) {
	
//insert a notification 
custom_pdo("insert into notifications (notification_from,notification_to,time,type,extra,extra2) values (:notification_from, :poster_id, :time, 2, :post_id, :comment_id)", [":notification_from" => $GLOBALS["base_user_id"], ":poster_id" => $poster_id, ":time" => time(), ":post_id" => $_POST["post_id"], ":comment_id" => $comment_id]);		
$notification_id = $con->lastInsertId();

$socket_message = [
"update_type" => "1",
"notification_id" => $notification_id,
"notification_time" => time(), 
"notification_time_string" => time_to_string(time()),
"notification_type" => 2, 
"notification_extra" => htmlspecialchars($_POST["post_id"], ENT_QUOTES, "utf-8"), 
"notification_extra2" => $comment_id, 
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

}
	
} 

echo json_encode($echo_arr);

unset($con);

?>