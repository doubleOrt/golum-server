<?php
/* we make a call to this page whenever a user clicks the send button to send a post to a friend */

require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_POST["post_id"]) && isset($_POST["friend_id"]) && filter_var($_POST["post_id"], FILTER_VALIDATE_INT) !== false && filter_var($_POST["friend_id"], FILTER_VALIDATE_INT) !== false) {

$time = time();
// type for notification is when users send their friends posts.
$type = 4;

// if the user has already sent this post to the target user by some hacking trick perhaps, die.
if($con->query("select id from notifications where notification_from = ". $_SESSION["user_id"] ." and notification_to = ". $_POST["friend_id"] ." and type = 4 and extra = ". $_POST["post_id"])->fetch()[0] != "") {
die();
}

$prepared = $con->prepare("insert into notifications (notification_from,notification_to,time,type,extra) values (:notification_from,:notification_to,:time,:type,:extra)");
$prepared->bindParam(":notification_from",$_SESSION["user_id"]);	
$prepared->bindParam(":notification_to",$_POST["friend_id"]);	
$prepared->bindParam(":time",$time);	
$prepared->bindParam(":type",$type);	
$prepared->bindParam(":extra",$_POST["post_id"]);	

if($prepared->execute()) {
$shmid = $_POST["friend_id"] . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);	
echo "1";
}
	
	
}


?>