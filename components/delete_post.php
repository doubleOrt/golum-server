<?php
// we make a call to this page when user wants to delete his posts

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_POST["post_id"]) && filter_var($_POST["post_id"], FILTER_VALIDATE_INT) !== false) {

if($con->query("select posted_by from posts where id = ". $_POST["post_id"])->fetch()["posted_by"] == $_SESSION["user_id"]) {

foreach (glob("../posts/" . $_POST["post_id"] . "-*.*") as $filename) {
unlink($filename);
}


$con->exec("delete from reply_upvotes_and_downvotes where comment_id in (select id from comment_replies where comment_id in (select id from post_comments where post_id = ". $_POST["post_id"] ."))");	
$con->exec("delete from comment_upvotes_and_downvotes where comment_id in (select id from post_comments where post_id = ". $_POST["post_id"] .")");
$con->exec("delete from comment_replies where comment_id in (select id from post_comments where post_id = ". $_POST["post_id"] .")");
$con->exec("delete from post_comments where post_id = ". $_POST["post_id"]);
$con->exec("delete from notifications where (type = 1 or type = 2 or type = 4 or type = 5 or type = 7 or type = 8) and extra = ". $_POST["post_id"]);
$con->exec("delete from notifications where (type = 3 or type = 9 or type = 10) and extra2 = ". $_POST["post_id"]);
$con->exec("delete from favorites where post_id = ". $_POST["post_id"]);
$con->exec("delete from posts where id = ". $_POST["post_id"]);


echo "1";	

}
	
}



?>
