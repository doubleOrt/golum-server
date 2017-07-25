<?php
/* we make a call to this page whenever a user wants to delete his comments */

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_POST["reply_id"]) && filter_var($_POST["reply_id"], FILTER_VALIDATE_INT) !== false) {

if(custom_pdo("select user_id from comment_replies where id = :reply_id", $_POST["reply_id"])->fetch()["user_id"] != $_SESSION["user_id"]) {
die();
} 
	
$reply_arr = custom_pdo("select comment_id, is_reply_to from comment_replies where id = :reply_id", [":reply_id" => $_POST["reply_id"]])->fetch();	
$comment_arr = custom_pdo("select id,user_id,post_id from post_comments where id = :comment_id", [":comment_id" => $reply_arr["comment_id"]])->fetch();	
	
custom_pdo("delete from comment_replies where id = :reply_id", [":reply_id" => $_POST["reply_id"]]);	
custom_pdo("delete from reply_upvotes_and_downvotes where comment_id = :reply_id", [":reply_id" => $_POST["reply_id"]]);

// delete the reply notification
custom_pdo("delete from notifications where notification_from = :base_user_id and notification_to = :commenter_id and type = 3 and extra = :comment_id and extra2 = :post_id and extra3 = :reply_id", [":base_user_id" => $_SESSION["user_id"], ":commenter_id" => $comment_arr["id"], ":comment_id" => $comment_arr["id"], ":post_id" => $comment_arr["post_id"], ":reply_id" => $_POST["reply_id"]]);


echo "1";
	
}



?>