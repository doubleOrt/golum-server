<?php
// when a user presses the "comments" button on a post, we make a call to this page to get the comments

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_comment_function.php";

$echo_arr = [[]];


if(isset($_GET["post_id"]) && isset($_GET["pin_comment_to_top"]) && isset($_GET["row_offset"]) && filter_var($_GET["post_id"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["pin_comment_to_top"], FILTER_VALIDATE_INT) !== false) {

unset($_SESSION["pinned_to_top_comment"]);
$not_pin_to_top_comment = "";
// when we want to pin a comment to the top of the comments, for example we need to do this when a user taps a comment or reply notification to see the comment.
if($_GET["pin_comment_to_top"] != 0) {
$not_pin_to_top_comment = " and id != ". $_GET["pin_comment_to_top"];
$_SESSION["pinned_to_top_comment"] = $_GET["pin_comment_to_top"];
}

$post_comments_arr_prepared = $con->prepare("SELECT *, (SELECT COUNT(id) FROM comment_replies WHERE comment_id = post_comments.id and comment_replies.user_id not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and comment_replies.user_id not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and comment_replies.user_id not in (SELECT user_id from account_states)) AS replies, (SELECT type FROM comment_upvotes_and_downvotes WHERE user_id = :base_user_id AND comment_id = post_comments.id) as base_user_opinion FROM post_comments LEFT JOIN (SELECT user_id AS user_id2,post_id AS post_id2,option_index FROM post_votes) post_votes ON post_comments.user_id = post_votes.user_id2 AND post_comments.post_id = post_votes.post_id2 WHERE post_comments.post_id = :post_id ".$not_pin_to_top_comment . (isset($_SESSION["pinned_to_top_comment"]) ? " and id != ". $_SESSION["pinned_to_top_comment"] : "") ." and post_comments.user_id not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and post_comments.user_id not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and post_comments.user_id not in (SELECT user_id from account_states) ORDER BY upvotes DESC, id DESC LIMIT 15 ". ($_GET["row_offset"] > 0 ? " OFFSET " . $_GET["row_offset"] : ""));	
$post_comments_arr_prepared->execute([":base_user_id" => $GLOBALS["base_user_id"], ":post_id" => $_GET["post_id"]]);
$post_comments_arr = $post_comments_arr_prepared->fetchAll();


// now we want to append the comment the user wants to pin to the top to the beginning of the $post_comments_arr
if(isset($_GET["pin_comment_to_top"]) && is_integer(intval($_GET["pin_comment_to_top"])) && $_GET["pin_comment_to_top"] != 0) {
array_unshift($post_comments_arr,custom_pdo("SELECT *, (SELECT COUNT(id) FROM comment_replies WHERE comment_id = post_comments.id) AS replies, (SELECT type FROM comment_upvotes_and_downvotes WHERE user_id = :base_user_id AND comment_id = post_comments.id) as base_user_opinion FROM post_comments LEFT JOIN (SELECT user_id AS user_id2,post_id AS post_id2,option_index FROM post_votes) post_votes ON post_comments.user_id = post_votes.user_id2 AND post_comments.post_id = post_votes.post_id2 WHERE post_id = :post_id and id = :pin_comment_to_top", [":base_user_id" => $GLOBALS["base_user_id"], ":post_id" => $_GET["post_id"], ":pin_comment_to_top" => $_GET["pin_comment_to_top"]])->fetch());
}

$poster_id = custom_pdo("select posted_by from posts where id = :post_id", [":post_id" => $_GET["post_id"]])->fetch()[0];		

for( $i = 0; $i < count($post_comments_arr); $i++ )	{
if($post_comments_arr[$i][0] != "") {	
$post_comments_arr[$i]["original_post_by"] = $poster_id;	
array_push($echo_arr[0], get_comment($post_comments_arr[$i],0));	
}
}

$post_comments_total_num_prepared = $con->prepare("select count(id) from post_comments where post_id = :post_id and post_comments.user_id not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and post_comments.user_id not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and post_comments.user_id not in (SELECT user_id from account_states)");
$post_comments_total_num_prepared->execute([":post_id" => $_GET["post_id"], ":base_user_id" => $GLOBALS["base_user_id"]]);
$post_comments_total_num = $post_comments_total_num_prepared->fetch()[0];
$echo_arr[1] = $post_comments_total_num;	
}

echo json_encode($echo_arr);	


unset($con);

?>