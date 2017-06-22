<?php

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_comment_function.php";

if(isset($_POST["post_id"]) && is_numeric($_POST["post_id"]) && isset($_POST["comment"])) {

$echo_arr = [];

if(strlen($_POST["comment"]) > 800) {
$echo_arr[0] = "Materialize.toast('Comments Cannot Be Longer Than 800 Characters, Currently At ' + $('#postCommentTextarea').html().length,4000,'red');";
die();	
}


// pdo parameters must be passed by reference, and thus, this useless var.
$comment_time = time();

$comment = strip_tags($_POST["comment"]);

$prepared = $con->prepare("insert into post_comments (post_id,user_id,comment,time) values(:post_id,:user_id,:comment,:time)");
$prepared->bindParam(":post_id",$_POST["post_id"]);
$prepared->bindParam(":user_id",$_SESSION["user_id"]);
$prepared->bindParam(":comment",$comment);
$prepared->bindParam(":time",$comment_time);

if($prepared->execute()) {

$comment_id = $con->lastInsertId();	

$poster_id = $con->query("select posted_by from posts where id =". $_POST["post_id"])->fetch()["posted_by"];
	
$comment_arr = $con->query("select * from (SELECT *, @rn:=@rn+1 AS new_id FROM ( SELECT * FROM post_comments ORDER BY upvotes DESC, id desc ) t1, (SELECT @rn:=0) t2) new_table left join (select user_id as user_id2,post_id as post_id2,option_index from post_votes) post_votes on new_table.user_id = post_votes.user_id2 and new_table.post_id = post_votes.post_id2 where id = ". $comment_id)->fetch();	
$comment_arr["original_post_by"] = $poster_id;
$echo_arr[0] = get_comment($comment_arr,18,0);	
$echo_arr[1] = "$('#postCommentTextarea').html(\"<span class='placeholder' style='color:#aaaaaa'>Type Comment...</span>\");/*remove the no comments yet placeholder */$('#noCommentsYet').remove();$('#postCommentTextarea').attr('data-state','0');";

// if commenter is not a user commenting on his own post, send them a notification.
if($poster_id != $_SESSION["user_id"]) {

//insert a notification 
$con->exec("insert into notifications (notification_from,notification_to,time,type,extra,extra2) values (". $_SESSION["user_id"] .",". $poster_id .",". time() .",2,". $_POST["post_id"] .",". $comment_id .");");		

$shmid = $poster_id . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);	
}

// so people who open the replies modal for this comment can get live updates.
$shmid = $comment_id . "" . 7; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts(""), 0);
shmop_close($shm);	

echo json_encode($echo_arr);
}
	
} 



?>