<?php
// when a user wants to upvote or downvote a reply, we make a call to this page.

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_POST["type"]) && isset($_POST["reply_id"]) !== false && filter_var($_POST["reply_id"], FILTER_VALIDATE_INT) !== false) {

// upvote or downvote, 0 for up, 1 for down
$action_type = ($_POST["type"] == "upvote" ? 0 : 1);

$notification_type = 9 + $action_type;

$reply_arr = $con->query("select user_id,comment_id from comment_replies where id = ". $_POST["reply_id"])->fetch();
$post_id = $con->query("select post_id from post_comments where id = ". $reply_arr["comment_id"])->fetch()["post_id"];


if($con->query("select id from reply_upvotes_and_downvotes where comment_id = ". $_POST["reply_id"] ." and user_id = ". $_SESSION["user_id"])->fetch()["id"] != "") {

//delete the notification
$con->exec("delete from notifications where notification_from = ". $_SESSION["user_id"] ." and notification_to = ". $reply_arr["user_id"] ." and (type = 9 or type = 10) and extra = ". $reply_arr["comment_id"] . " and extra2 = ". $post_id ." and extra3 = ". $_POST["reply_id"]);


$shmid = $reply_arr["user_id"] . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);	


if($action_type == $con->query("select type from reply_upvotes_and_downvotes where comment_id = ". $_POST["reply_id"] ." and user_id = ". $_SESSION["user_id"])->fetch()["type"]) {
$con->exec("delete from reply_upvotes_and_downvotes where comment_id = ". $_POST["reply_id"] ." and user_id = ". $_SESSION["user_id"]);	
}
else {
$con->exec("update reply_upvotes_and_downvotes set type = ". $action_type .", time = ". time() ." where comment_id = ". $_POST["reply_id"] ." and user_id = ". $_SESSION["user_id"]);
echo ($action_type == 0 ? "thisUpvotesObject.addClass('upvoteOrDownvoteActive');" : "thisDownvotesObject.addClass('upvoteOrDownvoteActive');");

if($_SESSION["user_id"] != $reply_arr["user_id"]) {
$con->exec("insert into notifications (notification_from,notification_to,time,type,extra,extra2,extra3) values (". $_SESSION["user_id"] .",". $reply_arr["user_id"] .",". time() .",". $notification_type .",". $reply_arr["comment_id"] .",". $post_id .",". $_POST["reply_id"] .");");	
}
}
update_comment($_POST["reply_id"]);
$new_upvotes_number = $con->query("select upvotes from comment_replies where id = ". $_POST["reply_id"])->fetch()["upvotes"];
$new_downvotes_number = $con->query("select downvotes from comment_replies where id = ". $_POST["reply_id"])->fetch()["downvotes"];
echo "thisUpvotesNumberObject.html('". ($new_upvotes_number > 0 ? ("(" . htmlspecialchars($new_upvotes_number . ")", ENT_QUOTES, "utf-8")) : "") ."');";
echo "thisDownvotesNumberObject.html('". ($new_downvotes_number > 0 ? ("(" . htmlspecialchars($new_downvotes_number, ENT_QUOTES, "utf-8") . ")") : "") ."');";
}
else {
$con->exec("insert into reply_upvotes_and_downvotes (comment_id,user_id,time,type) values(". $_POST["reply_id"] .",". $_SESSION["user_id"] .",". time() .",". $action_type .")");
update_comment($_POST["reply_id"]);
$new_upvotes_number = $con->query("select upvotes from comment_replies where id = ". $_POST["reply_id"])->fetch()["upvotes"];
$new_downvotes_number = $con->query("select downvotes from comment_replies where id = ". $_POST["reply_id"])->fetch()["downvotes"];


// insert a notification
if($_SESSION["user_id"] != $reply_arr["user_id"]) {
$con->exec("insert into notifications (notification_from,notification_to,time,type,extra,extra2,extra3) values (". $_SESSION["user_id"] .",". $reply_arr["user_id"] .",". time() .",". $notification_type .",". $reply_arr["comment_id"] .",". $post_id .",". $_POST["reply_id"] .");");	

$shmid = $reply_arr["user_id"] . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);	
}


echo "thisUpvotesNumberObject.html('". ($new_upvotes_number > 0 ? ("(" . htmlspecialchars($new_upvotes_number, ENT_QUOTES, "utf-8") . ")") : "") ."');";
echo "thisDownvotesNumberObject.html('". ($new_downvotes_number > 0 ? ("(" . htmlspecialchars($new_downvotes_number, ENT_QUOTES, "utf-8") . ")") : "") ."');";
echo ($action_type == 0 ? "thisUpvotesObject.addClass('upvoteOrDownvoteActive');" : "thisDownvotesObject.addClass('upvoteOrDownvoteActive');");
}	

}

// just want to reuse some code here, we need to update the comments' upvote and downvote cols once they are changed in the comment_upvotes_and_downvotes table.
function update_comment($comment_id) {
global $con, $action_type;	
$con->exec("update comment_replies set upvotes = ". $con->query("select count(id) from reply_upvotes_and_downvotes where comment_id = ". $comment_id ." and type = 0")->fetch()[0] ." where id = ". $comment_id);	
$con->exec("update comment_replies set downvotes = ". $con->query("select count(id) from reply_upvotes_and_downvotes where comment_id = ". $comment_id ." and type = 1")->fetch()[0] ." where id = ". $comment_id);		
}


?>