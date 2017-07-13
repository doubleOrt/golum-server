<?php
// when users vote, we process it here

require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_POST["post_id"]) && isset($_POST["option_index"]) && isset($_POST["already_voted"]) && isset($_POST["poster_id"]) && filter_var($_POST["post_id"], FILTER_VALIDATE_INT) !== false && filter_var($_POST["option_index"], FILTER_VALIDATE_INT) !== false && filter_var($_POST["poster_id"], FILTER_VALIDATE_INT) !== false) {

// if the user is not voting on his own post, we want to send a notification
if($_POST["poster_id"] != $_SESSION["user_id"]) {
	
$time = time();	
	
if($_POST["already_voted"] === "true") {	
// if the user has already voted, we just update the notification's time.
$prepared = $con->prepare("update notifications set time = :new_time where notification_from = :notification_from and notification_to = :notification_to and type = 1 and extra = :extra");
$prepared->bindParam(":new_time", $time);
$prepared->bindParam(":notification_from", $_SESSION["user_id"]);
$prepared->bindParam(":notification_to", $_POST["poster_id"]);
$prepared->bindParam(":extra", $_POST["post_id"]);
$prepared->execute();
}
else {		
//insert a notification 
$prepared = $con->prepare("insert into notifications (notification_from, notification_to, time, type, extra) values (:notification_from, :notification_to, :time, 1, :extra)");
$prepared->bindParam(":notification_from", $_SESSION["user_id"]);
$prepared->bindParam(":notification_to", $_POST["poster_id"]);
$prepared->bindParam(":time", $time);
$prepared->bindParam(":extra", $_POST["post_id"]);
$prepared->execute();
}

}

$vote_time = time();

$post_was_sent_to_me_prepared = $con->prepare("select notification_from from notifications where type = 4 and notification_to = :notification_to and extra = :extra");
$post_was_sent_to_me_prepared->bindParam(":notification_to", $_SESSION["user_id"]);
$post_was_sent_to_me_prepared->bindParam(":extra", $_POST["post_id"]);
$post_was_sent_to_me_prepared->execute();
$post_was_sent_to_me = $post_was_sent_to_me_prepared->fetch();
if($post_was_sent_to_me[0] != "") {
$prepared = $con->prepare("insert into notifications (notification_from, notification_to, time, type, extra) values (:notification_from, :notification_to, :time, 11, :extra)");
$prepared->bindParam(":notification_from", $_SESSION["user_id"]);
$prepared->bindParam(":notification_to", $post_was_sent_to_me["notification_from"]);
$prepared->bindParam(":time", $time);
$prepared->bindParam(":extra", $_POST["post_id"]);
$prepared->execute();
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