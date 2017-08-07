<?php
// we make a call to this page when user wants to delete his posts

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_POST["post_id"]) && filter_var($_POST["post_id"], FILTER_VALIDATE_INT) !== false) {

$post_arr = custom_pdo("select id, posted_by, file_types from posts where id = :post_id", [":post_id" => $_POST["post_id"]])->fetch();
if($post_arr["posted_by"] == $GLOBALS["base_user_id"]) {

foreach (glob("../users/" . $GLOBALS["base_user_id"] . "/posts/" . $_POST["post_id"] . "-*.*") as $filename) {
unlink($filename);
}

custom_pdo("delete from reply_upvotes_and_downvotes where comment_id in (select id from comment_replies where comment_id in (select id from post_comments where post_id = :post_id))", [":post_id" => $_POST["post_id"]]);	
custom_pdo("delete from comment_upvotes_and_downvotes where comment_id in (select id from post_comments where post_id = :post_id)", [":post_id" => $_POST["post_id"]]);
custom_pdo("delete from comment_replies where comment_id in (select id from post_comments where post_id = :post_id)", [":post_id" => $_POST["post_id"]]);
custom_pdo("delete from post_comments where post_id = :post_id", [":post_id" => $_POST["post_id"]]);
custom_pdo("delete from notifications where (type = 1 or type = 2 or type = 4 or type = 5 or type = 7 or type = 8 or type = 11) and extra = :post_id", [":post_id" => $_POST["post_id"]]);
custom_pdo("delete from notifications where (type = 3 or type = 9 or type = 10) and extra2 = :post_id", [":post_id" => $_POST["post_id"]]);
custom_pdo("delete from favorites where post_id = :post_id", [":post_id" => $_POST["post_id"]]);
custom_pdo("delete from post_reports where post_id = :post_id", [":post_id" => $_POST["post_id"]]);
custom_pdo("delete from posts where id = :post_id", [":post_id" => $_POST["post_id"]]);


echo "1";	
}
	
}


unset($con);

?>
