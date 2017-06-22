<?php
// when a user wants to reply to a comment, we make a call to this page.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_comment_function.php";

if(isset($_POST["comment_id"]) && is_numeric($_POST["comment_id"]) && isset($_POST["reply"])) {

$echo_arr = [];

if(strlen($_POST["reply"]) > 800) {
echo "Materialize.toast('Comments Cannot Be Longer Than 800 Characters, Currently At ' + $('#replyToCommentTextarea').html().length,4000,'red');";
die();	
}


// pdo parameters must be passed by reference, and thus, this useless var.
$reply_time = time();

$reply = strip_tags($_POST["reply"]);


$prepared = $con->prepare("insert into comment_replies (comment_id,user_id,comment,time,is_reply_to) values(:comment_id,:user_id,:comment,:time,:is_reply_to)");
$prepared->bindParam(":comment_id",$_POST["comment_id"]);
$prepared->bindParam(":user_id",$_SESSION["user_id"]);
$prepared->bindParam(":comment",$reply);
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

$comment_arr = $con->query("select user_id, post_id from post_comments where id =". $_POST["comment_id"])->fetch();

$poster_id = $con->query("select posted_by from posts where id = ". $comment_arr["post_id"])->fetch()[0];
	
$reply_arr = $con->query("select * from (SELECT *, @rn:=@rn+1 AS new_id FROM ( SELECT * FROM comment_replies ORDER BY upvotes DESC, id desc ) t1, (SELECT @rn:=0) t2) new_table left join (select user_id as user_id2,post_id as post_id2,option_index from post_votes) post_votes on new_table.user_id = post_votes.user_id2 and (select post_id from post_comments where id = new_table.comment_id) = post_votes.post_id2 where id = ". $reply_id)->fetch();
$reply_arr["original_post_by"] = $poster_id;
	
$echo_arr[0] = get_comment($reply_arr,18,1);	
$echo_arr[1] = "$('#replyToCommentTextarea').html(\"<span class='placeholder' style='color:#aaaaaa'>Type Reply...</span>\");$('#replyToCommentTextarea').attr('data-state','0');/*remove the no comments yet placeholder */ $('#noRepliesYet').remove();";

// if replier is not a user replying to his own comment, send them a notification.
if($comment_arr["user_id"] != $_SESSION["user_id"]) {

//insert a notification 
$con->exec("insert into notifications (notification_from,notification_to,time,type,extra,extra2,extra3) values (". $_SESSION["user_id"] .",". $comment_arr["user_id"] .",". time() .",3,". $_POST["comment_id"] .",". $comment_arr["post_id"] .",". $reply_id .");");		

$shmid = $comment_arr["user_id"] . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 10);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);
}
// if the user is replying to a reply, then we need to send a notification to the replied to as well.
if(isset($_POST["is_reply_to"]) && $_POST["is_reply_to"] != $_SESSION["user_id"] && $_POST["is_reply_to"] != $comment_arr["user_id"]) {
// if the first one is true, it means we just sent a notification to the user in the above line. so it is unnecessary to do so again.  the second just assures we don't send a notification to a user when he replies to his own reply.
if($_POST["is_reply_to"] != $comment_arr["user_id"] && $_POST["is_reply_to"] != $_SESSION["user_id"]) {	

$con->exec("insert into notifications (notification_from,notification_to,time,type,extra,extra2,extra3) values (". $_SESSION["user_id"] .",". $_POST["is_reply_to"] .",". time() .",3,". $_POST["comment_id"] .",". $comment_arr["post_id"] .",". $reply_id .");");		

$shmid = $_POST["is_reply_to"] . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 10);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);	
}
}


echo json_encode($echo_arr);
}
	
} 


?>