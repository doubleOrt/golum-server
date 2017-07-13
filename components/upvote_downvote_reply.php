<?php
// when a user wants to upvote or downvote a reply, we make a call to this page.

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_POST["type"]) && isset($_POST["reply_id"]) && filter_var($_POST["reply_id"], FILTER_VALIDATE_INT) !== false) {

// upvote or downvote, 0 for up, 1 for down
$action_type = ($_POST["type"] == "upvote" ? 0 : 1);

$notification_type = 9 + $action_type;

$reply_arr_prepared = $con->prepare("select user_id,comment_id from comment_replies where id = :reply_id");
$reply_arr_prepared->bindParam(":reply_id", $_POST["reply_id"]);
$reply_arr_prepared->execute();
$reply_arr = $reply_arr_prepared->fetch();

$post_id_prepared = $con->prepare("select post_id from post_comments where id = :comment_id");
$post_id_prepared->bindParam(":comment_id", $reply_arr["comment_id"]);
$post_id_prepared->execute();
$post_id = $post_id_prepared->fetch()[0];

$already_upvoted_downvoted_prepared = $con->prepare("select id from reply_upvotes_and_downvotes where comment_id = :reply_id and user_id = :user_id");
$already_upvoted_downvoted_prepared->bindParam(":reply_id", $_POST["reply_id"]);
$already_upvoted_downvoted_prepared->bindParam(":user_id", $_SESSION["user_id"]);
$already_upvoted_downvoted_prepared->execute();
$already_upvoted_downvoted = $already_upvoted_downvoted_prepared->fetch()[0];

$time = time();	

if($already_upvoted_downvoted != "") {

//delete the previous notification
$con->prepare("delete from notifications where notification_from = :user_id and notification_to = :notification_to and (type = 9 or type = 10) and extra = :comment_id and extra2 = :post_id and extra3 = :reply_id")->execute([":user_id" => $_SESSION["user_id"], ":notification_to" => $reply_arr["user_id"], ":comment_id" => $reply_arr["comment_id"], ":post_id" => $post_id, ":reply_id" => $_POST["reply_id"]]);

$shmid = $reply_arr["user_id"] . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);	

$con->prepare("select type from reply_upvotes_and_downvotes where comment_id = ". $_POST["reply_id"] ." and user_id = ". $_SESSION["user_id"])->fetch()["type"];

$previous_action_type_prepared = $con->prepare("select type from reply_upvotes_and_downvotes where comment_id = :reply_id and user_id = :user_id");
$previous_action_type_prepared->bindParam(":reply_id", $_POST["reply_id"]);
$previous_action_type_prepared->bindParam(":user_id", $_SESSION["user_id"]);
$previous_action_type_prepared->execute();
$previous_action_type = $previous_action_type_prepared->fetch()[0];
// if the new action type is equal to the previous one, it means that the user wants to remove their upvote/downvote
if($action_type == $previous_action_type) {	
$con->prepare("delete from reply_upvotes_and_downvotes where comment_id = :reply_id and user_id = :user_id")->execute([ ":reply_id" => $_POST["reply_id"], ":user_id" => $_SESSION["user_id"] ]);	
}
else {	
$con->prepare("update reply_upvotes_and_downvotes set type = :type, time = :time where comment_id = :reply_id and user_id = :user_id")->execute([ ":type" => $action_type, ":time" => $time, ":reply_id" => $_POST["reply_id"], ":user_id" => $_SESSION["user_id"] ]);

echo ($action_type == 0 ? "thisUpvotesObject.addClass('upvoteOrDownvoteActive');" : "thisDownvotesObject.addClass('upvoteOrDownvoteActive');");

if($_SESSION["user_id"] != $reply_arr["user_id"]) {
$con->prepare("insert into notifications (notification_from,notification_to,time,type,extra,extra2,extra3) values (:notification_from, :notification_to, :time, :type, :comment_id, :post_id, :reply_id)")->execute([ ":notification_from" => $_SESSION["user_id"], ":notification_to" => $reply_arr["user_id"], ":time" => $time, ":type" => $notification_type, ":comment_id" => $reply_arr["comment_id"], ":post_id" => $post_id, ":reply_id" => $_POST["reply_id"] ]);	
}
}


update_comment($_POST["reply_id"]);
$new_upvotes_downvotes_number_prepared = $con->prepare("select upvotes, downvotes from comment_replies where id = :reply_id");
$new_upvotes_downvotes_number_prepared->bindParam(":reply_id", $_POST["reply_id"]);
$new_upvotes_downvotes_number_prepared->execute();
$new_upvotes_downvotes_number_arr = $new_upvotes_downvotes_number_prepared->fetch();

$new_upvotes_number = $new_upvotes_downvotes_number_arr["upvotes"];
$new_downvotes_number = $new_upvotes_downvotes_number_arr["downvotes"];

echo "thisUpvotesNumberObject.html('". ($new_upvotes_number > 0 ? ("(" . htmlspecialchars($new_upvotes_number . ")", ENT_QUOTES, "utf-8")) : "") ."');";
echo "thisDownvotesNumberObject.html('". ($new_downvotes_number > 0 ? ("(" . htmlspecialchars($new_downvotes_number, ENT_QUOTES, "utf-8") . ")") : "") ."');";
}
else {
$con->exec("insert into reply_upvotes_and_downvotes (comment_id,user_id,time,type) values(". $_POST["reply_id"] .",". $_SESSION["user_id"] .",". time() .",". $action_type .")");
update_comment($_POST["reply_id"]);
$new_upvotes_downvotes_number_prepared = $con->prepare("select upvotes, downvotes from comment_replies where id = :reply_id");
$new_upvotes_downvotes_number_prepared->bindParam(":reply_id", $_POST["reply_id"]);
$new_upvotes_downvotes_number_prepared->execute();
$new_upvotes_downvotes_number_arr = $new_upvotes_downvotes_number_prepared->fetch();

$new_upvotes_number = $new_upvotes_downvotes_number_arr["upvotes"];
$new_downvotes_number = $new_upvotes_downvotes_number_arr["downvotes"];

// insert a notification
if($_SESSION["user_id"] != $reply_arr["user_id"]) {
$con->prepare("insert into notifications (notification_from,notification_to,time,type,extra,extra2,extra3) values (:notification_from, :notification_to, :time, :type, :comment_id, :post_id, :reply_id)")->execute([ ":notification_from" => $_SESSION["user_id"], ":notification_to" => $reply_arr["user_id"], ":time" => $time, ":type" => $notification_type, ":comment_id" => $reply_arr["comment_id"], ":post_id" => $post_id, ":reply_id" => $_POST["reply_id"] ]);		

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