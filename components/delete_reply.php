<?php
/* we make a call to this page whenever a user wants to delete his comments */

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_POST["reply_id"]) && filter_var($_POST["reply_id"], FILTER_VALIDATE_INT) !== false) {

if($con->query("select user_id from comment_replies where id = ". $_POST["reply_id"])->fetch()["user_id"] != $_SESSION["user_id"]) {
die();
} 
	
$reply_arr = $con->query("select comment_id, is_reply_to from comment_replies where id = ". $_POST["reply_id"])->fetch();	
$comment_arr = $con->query("select id,user_id,post_id from post_comments where id = ". $reply_arr["comment_id"])->fetch();	
	
$con->exec("delete from comment_replies where id = ". $_POST["reply_id"]);	
$con->exec("delete from reply_upvotes_and_downvotes where comment_id = ". $_POST["reply_id"]);

// delete the reply notification
$con->exec("delete from notifications where notification_from = ". $_SESSION["user_id"] ." and notification_to = ". $comment_arr["user_id"] ." and type = 3 and extra = ". $comment_arr["id"] ." and extra2 = ". $comment_arr["post_id"] ." and extra3 = ". $_POST["reply_id"]);


echo "1";
	
}



?>