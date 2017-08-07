<?php
/* we make a call to this page whenever a user wants to delete his comments */

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_POST["comment_id"]) && filter_var($_POST["comment_id"], FILTER_VALIDATE_INT) !== false) {

$comment_arr = custom_pdo("select id,user_id, post_id from post_comments where id = :comment_id", [":comment_id" => $_POST["comment_id"]])->fetch();
if($comment_arr["user_id"] != $GLOBALS["base_user_id"]) {
die();
} 
	
$poster_id = $con->query("select posted_by from posts where id =". $comment_arr["post_id"])->fetch()["posted_by"];

// delete all notifications triggered directly or indirectly by this comment
$con->prepare("delete from notifications where type = 2 and extra = :post_id and extra2 = :comment_id")->execute([":post_id" => $comment_arr["post_id"], ":comment_id" => $_POST["comment_id"]]);
$con->prepare("delete from notifications where type = 3 and extra = :comment_id")->execute([":comment_id" => $_POST["comment_id"]]);
$con->prepare("delete from notifications where (type = 7 or type = 8) and extra = :comment_id")->execute([":comment_id" => $_POST["comment_id"]]);

$con->prepare("delete from reply_upvotes_and_downvotes  WHERE comment_id in (select id from comment_replies where comment_id = :comment_id)")->execute([":comment_id" => $_POST["comment_id"]]);
$con->prepare("delete from comment_upvotes_and_downvotes where comment_id = :comment_id")->execute([":comment_id" => $_POST["comment_id"]]);
$con->prepare("delete from comment_replies where comment_id = :comment_id")->execute([":comment_id" => $_POST["comment_id"]]);
$con->prepare("delete from post_comments where id = :comment_id limit 1")->execute([":comment_id" => $_POST["comment_id"]]);	


echo "1";	
}



unset($con);

?>