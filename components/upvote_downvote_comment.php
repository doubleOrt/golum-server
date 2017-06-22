<?php
// when a user wants to upvote or downvote a comment, we make a call to this page.

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_POST["type"]) && isset($_POST["comment_id"]) && is_numeric($_POST["comment_id"])) {

// upvote or downvote, 0 for up, 1 for down
$action_type = ($_POST["type"] == "upvote" ? 0 : 1);

$notification_type = 7 + $action_type;

$comment_arr = $con->query("select user_id, post_id from post_comments where id = ". $_POST["comment_id"])->fetch();

echo "thisUpvotesObject.removeClass('upvoteOrDownvoteActive');thisDownvotesObject.removeClass('upvoteOrDownvoteActive');";

if($con->query("select id from comment_upvotes_and_downvotes where comment_id = ". $_POST["comment_id"] ." and user_id = ". $_SESSION["user_id"])->fetch()["id"] != "") {

// delete the notification
$con->exec("delete from notifications where notification_from = ". $_SESSION["user_id"] ." and notification_to = ". $comment_arr["user_id"] ." and (type = 7 or type = 8) and extra = ". $comment_arr["post_id"] . " and extra2 = ". $_POST["comment_id"]);


$shmid = $comment_arr["user_id"] . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);	

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
echo "thisUpvotesNumberObject.html('". ($new_upvotes_number > 0 ? ("(" . $new_upvotes_number . ")") : "") ."');";
echo "thisDownvotesNumberObject.html('". ($new_downvotes_number > 0 ? ("(" . $new_downvotes_number . ")") : "") ."');";
}
else {
	
$con->exec("insert into comment_upvotes_and_downvotes (comment_id,user_id,time,type) values(". $_POST["comment_id"] .",". $_SESSION["user_id"] .",". time() .",". $action_type .")");

update_comment($_POST["comment_id"]);
$new_upvotes_number = $con->query("select upvotes from post_comments where id = ". $_POST["comment_id"])->fetch()["upvotes"];
$new_downvotes_number = $con->query("select downvotes from post_comments where id = ". $_POST["comment_id"])->fetch()["downvotes"];

// insert a notification
if($comment_arr["user_id"] != $_SESSION["user_id"]) {
$con->exec("insert into notifications (notification_from,notification_to,time,type,extra,extra2) values (". $_SESSION["user_id"] .",". $comment_arr["user_id"] .",". time() .",". $notification_type .",". $comment_arr["post_id"] .",". $_POST["comment_id"] .");");	

$shmid = $comment_arr["user_id"] . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);	
}

echo "thisUpvotesNumberObject.html('". ($new_upvotes_number > 0 ? ("(" . $new_upvotes_number . ")") : "") ."');";
echo "thisDownvotesNumberObject.html('". ($new_downvotes_number > 0 ? ("(" . $new_downvotes_number . ")") : "") ."');";
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