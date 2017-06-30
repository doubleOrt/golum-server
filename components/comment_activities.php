<?php
// calls are made to this page to get the activities of comments.

require_once "common_requires.php";
require_once "logged_in_importants.php";




if(isset($_GET["comment_ids"]) && is_array($_GET["comment_ids"]) && count($_GET["comment_ids"]) > 0) {
$comment_ids_arr = $_GET["comment_ids"];
$table_name = "post_comments";	
$is_comment_reply = true;
}
else if(isset($_GET["reply_ids"]) && is_array($_GET["reply_ids"]) && count($_GET["reply_ids"]) > 0) {
$comment_ids_arr = $_GET["reply_ids"];
$table_name = "comment_replies";	
$is_comment_reply = false;
}
else {
die();	
}

$comments_query_string = "";
for($i = 0;$i < count($comment_ids_arr);$i++) {
if($i != 0) {
$comments_query_string .= " or ";	
}	
$comments_query_string .= "id = ". $comment_ids_arr[$i];	
}

$all_comments_arr = $con->query("select id, user_id, time, upvotes, downvotes from ". $table_name ." where ". $comments_query_string)->fetchAll();

$echo_arr = [];


foreach($all_comments_arr as $comment_arr) {
$commenter_arr = $con->query("select first_name,last_name from users where id = ". $comment_arr["user_id"])->fetch();	

$comment_replies_num = $con->query("select count(id) from comment_replies where comment_id = ". $comment_arr["id"])->fetch()[0];


if($is_comment_reply == false) {
$user_has_upvote_or_downvoted_comment = $con->query("select type from reply_upvotes_and_downvotes where user_id = ". $_SESSION["user_id"] ." and comment_id = ". $comment_arr["id"])->fetch()["type"];	
}
else {
$user_has_upvote_or_downvoted_comment = $con->query("select type from comment_upvotes_and_downvotes where user_id = ". $_SESSION["user_id"] ." and comment_id = ". $comment_arr["id"])->fetch()["type"];	
}

array_push($echo_arr,[$comment_arr["id"],"<a href='#". ($is_comment_reply == true ? "commentRepliesModal" : "") ."' class='". ($is_comment_reply == true ? "modal-trigger addReplyToComment' data-comment-id='". $comment_arr["id"] ."'" : "addReplyToReply' data-commenter-id='". $comment_arr["user_id"] ."' data-commenter-full-name='". $commenter_arr["first_name"] . " " . $commenter_arr["last_name"] ."'") .">Reply". ($comment_replies_num > 0 ? "&nbsp;&nbsp;<span class='commentActionNums'>(". $comment_replies_num .")</span>" : "") ."</a>
<a href='#' class='waves-effect waves-lightgrey upvoteOrDownvote ". ($user_has_upvote_or_downvoted_comment == 0 && !is_null($user_has_upvote_or_downvoted_comment) ? "upvoteOrDownvoteActive" : "") ."' data-upvote-or-downvote='upvote'><i class='material-icons'>arrow_upward</i></a> <span class='commentUpvotes commentActionNums'>". ($comment_arr["upvotes"] > 0 ? "(". $comment_arr["upvotes"] .")" : "") ."</span>
<a href='#' class='waves-effect waves-lightgrey upvoteOrDownvote ". ($user_has_upvote_or_downvoted_comment == 1 ? "upvoteOrDownvoteActive" : "") ."' data-upvote-or-downvote='downvote'><i class='material-icons'>arrow_downward</i></a> <span class='commentDownvotes commentActionNums'>". ($comment_arr["downvotes"] > 0 ? "(". $comment_arr["downvotes"] .")" : "") ."</span>
<span class='commentDate'>". time_to_string($comment_arr["time"]) ."</span>"]);
}

echo json_encode($echo_arr);


?>