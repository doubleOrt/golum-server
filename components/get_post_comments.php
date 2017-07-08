<?php
// when a user presses the "comments" button on a post, we make a call to this page to get the comments

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_comment_function.php";

$echo_arr = [[]];


if(isset($_GET["post_id"]) && filter_var($_GET["post_id"], FILTER_VALIDATE_INT) !== "" && isset($_GET["row_offset"]) && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== "" && isset($_GET["pin_comment_to_top"]) && filter_var($_GET["pin_comment_to_top"], FILTER_VALIDATE_INT) !== "") {

$not_pin_to_top_comment = "";
// when we want to pin a comment to the top of the comments, for example we need to do this when a user taps a comment or reply notification to see the comment.
if($_GET["pin_comment_to_top"] != 0) {
$not_pin_to_top_comment = " and id != ". $_GET["pin_comment_to_top"];
$_SESSION["pinned_to_top_comment"] = $_GET["pin_comment_to_top"];
}
// unset this whenever the user opens the comments for another post or this post. 
else if($_GET["row_offset"] < 1) {
unset($_SESSION["pinned_to_top_comment"]);
}

$post_comments_arr = $con->query("SELECT *, (SELECT COUNT(id) FROM comment_replies WHERE comment_id = post_comments.id) AS replies, (SELECT type FROM comment_upvotes_and_downvotes WHERE user_id = ". $_SESSION["user_id"] ." AND comment_id = post_comments.id) as base_user_opinion FROM post_comments LEFT JOIN (SELECT user_id AS user_id2,post_id AS post_id2,option_index FROM post_votes) post_votes ON post_comments.user_id = post_votes.user_id2 AND post_comments.post_id = post_votes.post_id2 WHERE post_comments.post_id = ". $_GET["post_id"] . $not_pin_to_top_comment . (isset($_SESSION["pinned_to_top_comment"]) ? " and id != ". $_SESSION["pinned_to_top_comment"] : "") ." ORDER BY upvotes DESC, id DESC LIMIT 15 ". ($_GET["row_offset"] > 0 ? " OFFSET " . $_GET["row_offset"] : ""))->fetchAll();	



// now we want to append the comment the user wants to pin to the top to the beginning of the $post_comments_arr
if(isset($_GET["pin_comment_to_top"]) && is_integer(intval($_GET["pin_comment_to_top"])) && $_GET["pin_comment_to_top"] != 0) {
array_unshift($post_comments_arr,$con->query("SELECT *, (SELECT COUNT(id) FROM comment_replies WHERE comment_id = post_comments.id) AS replies, (SELECT type FROM comment_upvotes_and_downvotes WHERE user_id = ". $_SESSION["user_id"] ." AND comment_id = post_comments.id) as base_user_opinion FROM post_comments LEFT JOIN (SELECT user_id AS user_id2,post_id AS post_id2,option_index FROM post_votes) post_votes ON post_comments.user_id = post_votes.user_id2 AND post_comments.post_id = post_votes.post_id2 WHERE post_id = ". $_GET["post_id"] ." and id = ". $_GET["pin_comment_to_top"])->fetch());
}


// select all from the user blocking table where this user has blocked another user or this user has been blocked by another user.
$this_user_related_blocks = $con->query("select user_ids from blocked_users where user_ids like '%-" . $_SESSION["user_id"]."' or user_ids like '". $_SESSION["user_id"] ."-%'")->fetchAll();	


$poster_id = $con->query("select posted_by from posts where id = ". $_GET["post_id"])->fetch()[0];		

for( $i = 0; $i < count($post_comments_arr); $i++ )	{
	
//iterate through the user blocking table and continue the posts loop if you find out that the poster has either blocked this user or has been blocked by this user.	
foreach($this_user_related_blocks as $this_user_related_block) {
$blocked_or_blocking_user_id = (explode("-",$this_user_related_block[0])[0] == $_SESSION["user_id"] ? explode("-",$this_user_related_block[0])[1] : explode("-",$this_user_related_block[0])[0]);
if($post_comments_arr[$i]["user_id"] == $blocked_or_blocking_user_id) {
continue 2;	
}
}


$post_comments_arr[$i]["original_post_by"] = $poster_id;	
array_push($echo_arr[0], get_comment($post_comments_arr[$i],0));	
}

$echo_arr[1] = $con->query("select count(id) from post_comments where post_id = ". $_GET["post_id"])->fetch()[0];	
}

echo json_encode($echo_arr);	


unset($con);

?>