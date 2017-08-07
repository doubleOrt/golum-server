<?php
// when a user presses the "comments" button on a post, we make a call to this page to get the comments

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_comment_function.php";

$echo_arr = [[]];

if(isset($_GET["comment_id"]) && isset($_GET["pin_comment_to_top"]) && isset($_GET["row_offset"]) && filter_var($_GET["comment_id"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["pin_comment_to_top"], FILTER_VALIDATE_INT) !== false) {


$not_pin_to_top_comment = "";
// when we want to pin a comment to the top of the comments, for example we need to do this when a user taps a comment or reply notification to see the comment.
if($_GET["pin_comment_to_top"] != 0) {
$not_pin_to_top_comment = " and id != ". $_GET["pin_comment_to_top"];
$_SESSION["pinned_to_top_reply"] = $_GET["pin_comment_to_top"];
}
// unset this whenever the user opens the comments for another post or this post. 
else if($_GET["row_offset"] < 1) {
unset($_SESSION["pinned_to_top_reply"]);
}

/* we are saying '0 as replies' because we just don't want a comment-reply to have a "number of replies" next 
to its reply button, not to even mention that due to the database architecture, it is beyond our capabilities. */
$post_comments_arr_prepared = $con->prepare("SELECT * FROM (SELECT *, 0 AS replies, (SELECT type FROM reply_upvotes_and_downvotes WHERE user_id = :base_user_id AND comment_id = comment_replies.id) as base_user_opinion, (SELECT post_id from post_comments where id = :comment_id) as reply_owner_post_id FROM comment_replies) comment_replies LEFT JOIN (SELECT user_id AS user_id2,post_id AS post_id2,option_index FROM post_votes) post_votes ON comment_replies.user_id = post_votes.user_id2 AND reply_owner_post_id = post_votes.post_id2 WHERE comment_replies.comment_id = :comment_id ". $not_pin_to_top_comment . (isset($_SESSION["pinned_to_top_reply"]) ? " AND id != ". $_SESSION["pinned_to_top_reply"] : "") ." AND comment_replies.user_id not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and comment_replies.user_id not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and comment_replies.user_id not in (SELECT user_id from account_states) ORDER BY upvotes DESC, id DESC LIMIT 15 ". ($_GET["row_offset"] > 0 ? " OFFSET " . $_GET["row_offset"] : ""));
$post_comments_arr_prepared->execute([":base_user_id" => $GLOBALS["base_user_id"], ":comment_id" => $_GET["comment_id"]]);
$post_comments_arr = $post_comments_arr_prepared->fetchAll();


// now we want to append the comment the user wants to pin to the top to the beginning of the $post_comments_arr
if(intval($_GET["pin_comment_to_top"]) !== 0) {
array_unshift($post_comments_arr,custom_pdo("SELECT * FROM (SELECT *, (SELECT COUNT(id) FROM comment_replies WHERE is_reply_to = comment_replies.id) AS replies, (SELECT type FROM reply_upvotes_and_downvotes WHERE user_id = :base_user_id AND comment_id = comment_replies.id) as base_user_opinion, (SELECT post_id from post_comments where id = :comment_id) as reply_owner_post_id FROM comment_replies) comment_replies LEFT JOIN (SELECT user_id AS user_id2,post_id AS post_id2,option_index FROM post_votes) post_votes ON comment_replies.user_id = post_votes.user_id2 AND reply_owner_post_id = post_votes.post_id2 WHERE comment_replies.comment_id = :comment_id and id = :pin_comment_to_top", [":base_user_id" => $GLOBALS["base_user_id"], ":comment_id" => $_GET["comment_id"], ":pin_comment_to_top" => $_GET["pin_comment_to_top"]])->fetch());
}
		
		
// this serves one purpose only, to add a background to comments and replies by original posters.
$poster_id = custom_pdo("select posted_by from posts where id in (select post_id from post_comments where id = :comment_id)", [":comment_id" => $_GET["comment_id"]])->fetch()[0];	
		
for( $i = 0; $i < count($post_comments_arr); $i++ )	{	
if($post_comments_arr[$i][0] != "") {	
$post_comments_arr[$i]["original_post_by"] = $poster_id;
array_push($echo_arr[0], get_comment($post_comments_arr[$i],1));	
}
}

$comment_replies_total_num_prepared = $con->prepare("select count(id) from comment_replies where comment_id = :comment_id and comment_replies.user_id not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and comment_replies.user_id not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and comment_replies.user_id not in (SELECT user_id from account_states)");
$comment_replies_total_num_prepared->execute([":comment_id" => $_GET["comment_id"], ":base_user_id" => $GLOBALS["base_user_id"]]);
$comment_replies_total_num = $comment_replies_total_num_prepared->fetch()[0];	

$echo_arr[1] = $comment_replies_total_num;
}


echo json_encode($echo_arr);	


unset($con);


?>