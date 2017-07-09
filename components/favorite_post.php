<?php
// we make a call to this page whenever the user wants to favorite a post.


require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_POST["post_id"]) && filter_var($_POST["post_id"], FILTER_VALIDATE_INT) !== false) {

$poster_id = $con->query("select posted_by from posts where id = ". $_POST["post_id"])->fetch()["posted_by"];

if($con->query("select id from favorites where post_id = ". $_POST["post_id"] ." and user_id = ". $_SESSION["user_id"])->fetch()["id"] == "") {
$con->exec("insert into favorites (post_id,user_id,time) values (". $_POST["post_id"] .",". $_SESSION["user_id"] .",". time() .");");
// if the current favorite is not a user favoriting their own post, send them a notification.
if($poster_id != $_SESSION["user_id"]) {
$con->exec("insert into notifications (notification_from,notification_to,time,type,extra) values (". $_SESSION["user_id"] .",". $poster_id .",". time() .",5,". $_POST["post_id"] .");");	
}
echo "var favorited = true";	
}
else {
$con->exec("delete from favorites where post_id = ".$_POST["post_id"]." and user_id = ".$_SESSION["user_id"]);	
// remove the notification sent to the user when you favorited the post.
if($poster_id != $_SESSION["user_id"]) {
$con->exec("delete from notifications where notification_from = ". $_SESSION["user_id"] ." and notification_to = ". $poster_id ." and type = 5 and extra = ". $_POST["post_id"]);
}
echo "var favorited = false";
}
$shmid = $poster_id . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);
}



?>