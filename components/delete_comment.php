<?php
/* we make a call to this page whenever a user wants to delete his comments */

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_POST["comment_id"]) && is_integer(intval($_POST["comment_id"]))) {

$comment_arr = $con->query("select id,user_id, post_id from post_comments where id = ". $_POST["comment_id"])->fetch();
if($comment_arr["user_id"] != $_SESSION["user_id"]) {
die();
} 
	
// delete all notifications triggered directly or indirectly by this comment
$poster_id = $con->query("select posted_by from posts where id =". $comment_arr["post_id"])->fetch()["posted_by"];
$con->exec("delete from notifications where type = 2 and extra = ". $comment_arr["post_id"] ." and extra2 = ". $comment_arr["id"]);
$con->exec("delete from notifications where type = 3 and extra = ". $_POST["comment_id"]);
$con->exec("delete from notifications where (type = 7 or type = 8) and extra = ". $comment_arr["id"]);
$con->exec("delete from reply_upvotes_and_downvotes  WHERE comment_id in (select id from comment_replies where comment_id = ". $comment_arr["id"] .")");
$con->exec("delete from comment_upvotes_and_downvotes where comment_id = ". $comment_arr["id"]);
$con->exec("delete from comment_replies where comment_id = ". $comment_arr["id"]);
$con->exec("delete from post_comments where id = ". $_POST["comment_id"]);	


echo "1";	
}



?>