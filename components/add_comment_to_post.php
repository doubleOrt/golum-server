<?php

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_comment_function.php";

$MAXIMUM_COMMENT_LENGTH = 800;

$echo_arr = ["",""];

if(isset($_POST["post_id"]) && is_integer(intval($_POST["post_id"])) && isset($_POST["comment"])) {

if(strlen($_POST["comment"]) > $MAXIMUM_COMMENT_LENGTH) {
$echo_arr[1] = "Comment cannot be longer than 800 characters!";
die();	
}


// pdo parameters must be passed by reference, and thus, this useless var.
$comment_time = time();

$prepared = $con->prepare("insert into post_comments (post_id,user_id,comment,time) values(:post_id,:user_id,:comment,:time)");
$prepared->bindParam(":post_id",$_POST["post_id"]);
$prepared->bindParam(":user_id",$_SESSION["user_id"]);
$prepared->bindParam(":comment",$_POST["comment"]);
$prepared->bindParam(":time",$comment_time);

if($prepared->execute()) {

$comment_id = $con->lastInsertId();	

$poster_id = $con->query("select posted_by from posts where id =". $_POST["post_id"])->fetch()["posted_by"];
	
$comment_arr = $con->query("SELECT *, (SELECT COUNT(id) FROM comment_replies WHERE comment_id = post_comments.id) AS replies, (SELECT type FROM comment_upvotes_and_downvotes WHERE user_id = ". $_SESSION["user_id"] ." AND comment_id = post_comments.id) as base_user_opinion FROM post_comments LEFT JOIN (SELECT user_id AS user_id2,post_id AS post_id2,option_index FROM post_votes) post_votes ON post_comments.user_id = post_votes.user_id2 AND post_comments.post_id = post_votes.post_id2 WHERE post_comments.id = ". $comment_id)->fetch();	
$comment_arr["original_post_by"] = $poster_id;
$echo_arr[0] = get_comment($comment_arr);	

// if commenter is not a user commenting on his own post, send them a notification.
if($poster_id != $_SESSION["user_id"]) {
//insert a notification 
$con->exec("insert into notifications (notification_from,notification_to,time,type,extra,extra2) values (". $_SESSION["user_id"] .",". $poster_id .",". time() .",2,". $_POST["post_id"] .",". $comment_id .");");		
}

}
	
} 

echo json_encode($echo_arr);


?>