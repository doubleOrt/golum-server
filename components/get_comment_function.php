<?php

require_once "handleTagsFunction.php";

function get_comment($comment_arr,$main_font_size,$reply_type) {

global $con;

// if target has delete or deactivated their account, or the current user has been blocked by the target.
if($con->query("select id from account_states where user_id = ". $comment_arr["user_id"])->fetch()[0] != "" || $con->query("select id from blocked_users where user_ids = '".$comment_arr["user_id"]. "-" . $_SESSION["user_id"]."'")->fetch() != "") {	
return "";
}

$commenter_arr = $con->query("select id,first_name, last_name, avatar_picture from users where id = ". $comment_arr["user_id"])->fetch();

$commenter_avatar_arr = $con->query("SELECT positions, rotate_degree FROM avatars WHERE id_of_user = ". $comment_arr["user_id"] ." order by id desc limit 1")->fetch();

$commenter_avatar_positions = explode(",",$commenter_avatar_arr["positions"]);
//if avatar positions does not exist 
if(count($commenter_avatar_positions) < 2) {
$commenter_avatar_positions = [0,0];
}


//if the current comment is actually a comment reply inside another comment reply.
$is_reply_to_markup = "";
if(isset($comment_arr["is_reply_to"])) {
if($comment_arr["is_reply_to"] != 0) {
$is_reply_to_user_arr = $con->query("select first_name, last_name from users where id = ". $comment_arr["is_reply_to"])->fetch();
$is_reply_to_markup = "<a href='#modal1' class='replyToFullname modal-trigger showUserModal view-user' data-user-id='". $comment_arr["is_reply_to"] ."'>". $is_reply_to_user_arr["first_name"] . " " . $is_reply_to_user_arr["last_name"] ."</a> ";	
} 	
}


$random_num = rand(1000000,10000000);

$commenter_vote_emojis = [preg_replace("/\\\\u([0-9A-F]{2,5})/i", "&#x$1;", "\xF0\x9F\x85\xB0"),preg_replace("/\\\\u([0-9A-F]{2,5})/i", "&#x$1;", "\xF0\x9F\x85\xB1"),preg_replace("/\\\\u([0-9A-F]{2,5})/i", "&#x$1;", "\xF0\x9F\x85\xB2"),preg_replace("/\\\\u([0-9A-F]{2,5})/i", "&#x$1;", "\xF0\x9F\x85\xB3")];

return "<div class='singleComment myHorizontalCardStyle cardStyles scaleHorizontallyCenteredItem myGreyBackgroundOnActive row' style='font-size:". $main_font_size ."px;' data-comment-id='". $comment_arr["new_id"] ."' data-actual-comment-id='". $comment_arr["id"] ."'>

". ($comment_arr["user_id"] == $_SESSION["user_id"] ? "
<a href='#' class='dropdown-button deleteCommentButton' data-activates='commentSettings".$random_num."'></a>
<!-- Dropdown Structure -->
<ul id='commentSettings".$random_num."' class='dropdown-content'>
<li><a href='#!' class='deleteComment' data-comment-id='".$comment_arr["id"]."'>Delete</a></li>
</ul>" : "") ."

<div class='commenterAvatarContainerParent col l1 m1 s2'>
<div class='avatarContainer commenterAvatarContainer'>
<div class='avatarContainerChild commenterAvatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='".$comment_arr["user_id"]."'>
". ($commenter_arr["avatar_picture"] == "" ? letter_avatarize($commenter_arr["first_name"],"medium") : "
<div class='rotateContainer' style='margin-top:".$commenter_avatar_positions[0]."%;margin-left:".$commenter_avatar_positions[1]."%;'>
<div class='avatarRotateDiv ". ($commenter_arr["id"] == $_SESSION["user_id"] ? "baseUserAvatarRotateDivs" : "") ."' data-rotate-degree='".$commenter_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages commenterAvatarImages' src='".$commenter_arr["avatar_picture"]."' alt='Image'/>
</div></div>") ."
</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div><!-- end .commenterAvatarContainerParent -->
<div class='actualComment col l10 m10 s10'>
<a href='#modal1' class='commenterFullName modal-trigger showUserModal view-user ". ($comment_arr["user_id"] == $comment_arr["original_post_by"] ? "commentByPoster" : "") ."' data-user-id='".$comment_arr["user_id"]."'><span class='". ($comment_arr["user_id"] == $_SESSION["user_id"] ? "baseUserFullNameContainers" : "") ."'>". $commenter_arr["first_name"] . " " . $commenter_arr["last_name"] ."</span></a><!-- end .commenterFullName -->
<span class='commenterVotedThis'>". ($comment_arr["option_index"] != "" ? ($comment_arr["option_index"] == 0 ? $commenter_vote_emojis[0] : ($comment_arr["option_index"] == 1 ? $commenter_vote_emojis[1] : ($comment_arr["option_index"] == 2 ? $commenter_vote_emojis[2] : $commenter_vote_emojis[3]))) : "") ."</span>
<div class='actualCommentComment' >". $is_reply_to_markup . handleTags($comment_arr["comment"]) ."</div>
<div class='postCommentActions' data-comment-id='". $comment_arr["id"] ."'>

</div><!-- end .postCommentActions -->
</div>
</div>";
}


?>