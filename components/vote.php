<?php
// when users vote, we process it here

require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_POST["post_id"]) && isset($_POST["option_index"]) && isset($_POST["already_voted"]) && isset($_POST["poster_id"]) && filter_var($_POST["post_id"], FILTER_VALIDATE_INT) !== false && filter_var($_POST["option_index"], FILTER_VALIDATE_INT) !== false && filter_var($_POST["poster_id"], FILTER_VALIDATE_INT) !== false) {

// if the user is not voting on his own post, we want to send a notification
if($_POST["poster_id"] != $_SESSION["user_id"]) {
	
if($_POST["already_voted"] === "true") {	
// if the user has already voted, we just update the notification's time.
$con->exec("update notifications set time = ". time() ." where notification_from = ". $_SESSION["user_id"] ." and notification_to = ". $_POST["poster_id"] ." and type = 1 and extra = ". $_POST["post_id"]);
}
else {		
//insert a notification 
$con->exec("insert into notifications (notification_from,notification_to,time,type,extra) values (". $_SESSION["user_id"] .",". $_POST["poster_id"] .",". time() .",1,". $_POST["post_id"] .");");	
}

}

$vote_time = time();

$post_was_sent_to_me = $con->query("select notification_from from notifications where type = 4 and notification_to = ". $_SESSION["user_id"] ." and extra = ". $_POST["post_id"])->fetch();
if($post_was_sent_to_me[0] != "") {
$con->exec("insert into notifications (notification_from,notification_to,time,type,extra) values(". $_SESSION["user_id"] .",". $post_was_sent_to_me["notification_from"] .",". time() .",11,". $_POST["post_id"] .")");	
}

if($_POST["already_voted"] == "true") {
$new_time = time();	
$prepared = $con->prepare("update post_votes set option_index = :option_index, time = :time where post_id = :post_id and user_id = :user_id");	
$prepared->bindParam(":option_index",$_POST["option_index"]);
$prepared->bindParam(":post_id",$_POST["post_id"]);
$prepared->bindParam(":user_id",$_SESSION["user_id"]);
$prepared->bindParam(":time",$new_time);
$prepared->execute();
}
else {
$prepared = $con->prepare("insert into post_votes (post_id,user_id,option_index,time) values(:post_id,:user_id,:option_index,:time)");
$prepared->bindParam(":post_id",$_POST["post_id"]);
$prepared->bindParam(":user_id",$_SESSION["user_id"]);
$prepared->bindParam(":option_index",$_POST["option_index"]);
$prepared->bindParam(":time",$vote_time);
$prepared->execute();
}

}



?>