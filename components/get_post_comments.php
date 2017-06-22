<?php
// when a user presses the "comments" button on a post, we make a call to this page to get the comments

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_comment_function.php";


if(isset($_GET["post_id"]) && isset($_GET["last_comment_id"])) {

$echo_arr = [""];


$not_pin_to_top_comment = "";
// when we want to pin a comment to the top of the comments, for example we need to do this when a user taps a comment or reply notification to see the comment.
if(isset($_GET["pin_comment_to_top"]) && is_numeric($_GET["pin_comment_to_top"])) {
$not_pin_to_top_comment = " and id != ". $_GET["pin_comment_to_top"];
}

// when the user wants to see the first 10 posts	
if($_GET["last_comment_id"] == 0) {
$post_comments_arr = $con->query("select * from (SELECT *, @rn:=@rn+1 AS new_id FROM ( SELECT * FROM post_comments ORDER BY upvotes DESC, id desc ) t1, (SELECT @rn:=0) t2) new_table left join (select user_id as user_id2,post_id as post_id2,option_index from post_votes) post_votes on new_table.user_id = post_votes.user_id2 and new_table.post_id = post_votes.post_id2 where new_table.post_id = ". $_GET["post_id"] . $not_pin_to_top_comment ." limit 15")->fetchAll();	
}
// when the user is infinite scrolling
else {
$post_comments_arr = $con->query("select * from (SELECT *, @rn:=@rn+1 AS new_id FROM ( SELECT * FROM post_comments ORDER BY upvotes DESC, id desc ) t1, (SELECT @rn:=0) t2) new_table left join (select user_id as user_id2,post_id as post_id2,option_index from post_votes) post_votes on new_table.user_id = post_votes.user_id2 and new_table.post_id = post_votes.post_id2 where new_table.post_id = ". $_GET["post_id"] . $not_pin_to_top_comment ." and new_id > ". $_GET["last_comment_id"] ." limit 15")->fetchAll();	
}


// now we want to append the comment the user wants to pin to the top to the beginning of the $post_comments_arr
if(isset($_GET["pin_comment_to_top"]) && is_numeric($_GET["pin_comment_to_top"])) {
array_unshift($post_comments_arr,$con->query("select * from (SELECT *, @rn:=@rn+1 AS new_id FROM ( SELECT * FROM post_comments ORDER BY upvotes DESC, id desc ) t1, (SELECT @rn:=0) t2) new_table where post_id = ". $_GET["post_id"] ." and id = ". $_GET["pin_comment_to_top"])->fetch());
}




// if there are no comments say there are no comments
if(count($post_comments_arr) < 1 && $_GET["last_comment_id"] == 0) {
$echo_arr[0] = "<div id='noCommentsYet' class='emptyNowPlaceholder'>You Are Going To Be The First To Comment</div>";
}		
else {
// this serves one purpose only, to add a background to comments and replies by original posters.
$poster_id = $con->query("select posted_by from posts where id = ". $_GET["post_id"])->fetch()[0];	

// select all from the user blocking table where this user has blocked another user or this user has been blocked by another user.
$this_user_related_blocks = $con->query("select user_ids from blocked_users where user_ids like '%-" . $_SESSION["user_id"]."' or user_ids like '". $_SESSION["user_id"] ."-%'")->fetchAll();	


for( $i = 0; $i < count($post_comments_arr); $i++ )	{

//iterate through the user blocking table and continue the posts loop if you find out that the poster has either blocked this user or has been blocked by this user.	
foreach($this_user_related_blocks as $this_user_related_block) {
$blocked_or_blocking_user_id = (explode("-",$this_user_related_block[0])[0] == $_SESSION["user_id"] ? explode("-",$this_user_related_block[0])[1] : explode("-",$this_user_related_block[0])[0]);
if($post_comments_arr[$i]["user_id"] == $blocked_or_blocking_user_id) {
continue 2;	
}
}
	
$post_comments_arr[$i]["original_post_by"] = $poster_id;	
$echo_arr[0] .= get_comment($post_comments_arr[$i],18,45,0);	
}
}

$echo_arr[1] = $con->query("select count(id) from post_comments where post_id = ". $_GET["post_id"])->fetch()[0];
	
echo json_encode($echo_arr);	
	
}



?>