<?php
// you need this function to retrieve the markup of a post, you just need to pass the post's array (from the database) as the first argument and you are good to go.

require_once "handleTagsFunction.php";

function get_post_markup($post_arr,$post_type) {
global $con, $user_favorite_posts_arr;

// a call to this function means that the post is being viewed by 1 more user, so we need to update the post's total views.
$con->query("update posts set post_views = post_views + 1 where id = ". $post_arr["id"]);
// we also need to update the views for the post array passed to this function, for example i just shared a post, i make a call to this function, and without incrementing the array, i would see 0 views instead of 1.
$post_arr["post_views"] = ++$post_arr["post_views"];


// if target has delete or deactivated their account, or the current user has been blocked by the target.
if($con->query("select id from account_states where user_id = ". $post_arr["posted_by"])->fetch()[0] != "" || $con->query("select id from blocked_users where user_ids = '".$post_arr["posted_by"]. "-" . $_SESSION["user_id"]."'")->fetch() != "") {	
return "";
}

$viewing_own_posts = ($post_arr["posted_by"] == $_SESSION["user_id"] ? true : false);

$poster_arr = $con->query("select first_name, last_name, avatar_picture from users where id = ". $post_arr["posted_by"])->fetch();

$user_already_voted = $con->query("select id from post_votes where post_id =". $post_arr["id"] ." and user_id = ". $_SESSION["user_id"])->fetch();

$post_is_added_to_favorites = false;

for($i = 0;$i < count($user_favorite_posts_arr);$i++) {
if(in_array($post_arr["id"],$user_favorite_posts_arr[$i])) {
$post_is_added_to_favorites = true;	
}	
}

$poster_avatar_arr = $con->query("SELECT positions, rotate_degree FROM avatars WHERE id_of_user = ". $post_arr["posted_by"] ." order by id desc limit 1")->fetch();

$poster_avatar_positions = explode(",",$poster_avatar_arr["positions"]);
//if avatar positions does not exist 
if(count($poster_avatar_positions) < 2) {
$poster_avatar_positions = [0,0];
}


$post_comments_num = $con->query("select count(id) from post_comments where post_id = ". $post_arr["id"])->fetch()[0];

$file_types_arr = explode(",",$post_arr["file_types"]);	
		
$imagesContainerChildren = "";	

	
if($post_arr["type"] == 3 || $post_arr["type"] == 4) {
$height = "50%";	
}	
else {
$height = "100%";	
}

$cols_arr = [];

if($post_arr["type"] % 2 == 0) {
for($x = 0;$x<$post_arr["type"];$x++) {
$cols_arr[$x] = "l6 m6 s6";	
}
}
else {
for($x = 0;$x<$post_arr["type"];$x++) {
if($x != $post_arr["type"] - 1) {	
$cols_arr[$x] = "l6 m6 s6";	
}
else {
$cols_arr[$x] = "l12 m12 s12";		
}
}	
}



if($post_arr["type"] != 1) {	
for($x = 0; $x < $post_arr["type"]; $x++) {
	
$image_src = "posts/". $post_arr["id"]. "-". $x . "." . $file_types_arr[$x];

$image_id = "image" . rand(1000000,10000000)  . $post_arr["id"] . "-" . $x;


$imagesContainerChildren .= "
<div class='col ".$cols_arr[$x]." postSingleImageContainer' data-option-index='". $x ."' style='height:".$height."' data-image-path='". $image_src ."'>
<img class='postSingleImageContainerImage' id='". $image_id ."' src='". $image_src ."' alt='Photo ".$x."'/>
</div><!-- end .postSingleImageContainer -->
<script>

$('#". $image_id ."').on('load',function(){	
fitToParent('#". $image_id ."');	
});

</script>
";
}	
}
// need to bend some rules and such for those type 1 posts. (the ones that you can like or dislike instead of choose)
else {

$image_src = "posts/". $post_arr["id"]. "-0." . $file_types_arr[0];

$image_id = "image" . rand(1000000,10000000)  . $post_arr["id"] . "-0";

$imagesContainerChildren .= "
<div class='col ".$cols_arr[0]." postSingleImageContainer' style='height:".$height."' data-image-path='". $image_src . "'>
<img class='postSingleImageContainerImage' id='". $image_id ."' src='". $image_src ."' alt='Photo 0'/>
</div><!-- end .postSingleImageContainer -->
<div class='col l6 m6 s6 postSingleImageContainer' data-option-index='0' data-image-path='". $image_src ."' style='height:100%;transform:translate(0,-100%);background:transparent'>
</div><div class='col l6 m6 s6 postSingleImageContainer' data-option-index='1' data-image-path='". $image_src ."' style='height:100%;transform:translate(0,-100%);background:transparent'></div>
<script>

$('#". $image_id ."').on('load',function(){	
fitToParent('#". $image_id ."');	
});

</script>
";
}


$random_num = rand(1000000,10000000);

/* this .loadPostComponents class is just so we can distinguish between already loaded classes and the newly loaded so we don't load post components for posts that we already have those
components, we remove this class from a post immediately after we have loaded its components */
return "<div class='singlePost loadPostComponents ". $post_type ." col l12 m12 s12' data-actual-post-id='".$post_arr["id"]."' data-post-id='". $post_arr["new_id"] ."' data-post-type='". $post_arr["type"] ."' data-poster-id='". $post_arr["posted_by"] ."' data-positive-icon='". ($post_arr["type"] != 1 ? "check" : "thumb_up") ."' data-negative-icon='". ($post_arr["type"] != 1 ? "close" : "thumb_down") ."' data-already-voted='". ($user_already_voted["id"] == "" ? "false" : "true") ."'>
<div class='postTop'>
<div class='postTitle scaleItem'>
". handleTags($post_arr["title"], "class='hashtag getTagPosts opacityChangeOnActive modal-trigger' data-target='tagPostsModal' data-tag='$0'") ."
</div>
</div>
<div class='postImagesContainer row'>
". $imagesContainerChildren ."
</div>
<div class='postBottomContainer row'>

<ul id='postSettings".$random_num."' class='dropdown-content'>
<li class='reportPost' data-actual-post-id='".$post_arr["id"]."'><a href='#!' class='waves-effect waves-lightgrey'>Report</a></li>
". ($post_arr["posted_by"] == $_SESSION["user_id"] ? "<li class='deletePost' data-actual-post-id='".$post_arr["id"]."'><a href='#!' class='waves-effect waves-lightgrey'>Delete</a></li>" : "" ) ."
</ul>


<div class='col l8 m8 s10 postTagsContainer'>
".
($viewing_own_posts == true ? ("<a href='#tagsModal' data-filename='add_tags_to_post.php' data-actual-post-id='".$post_arr["id"]."' class='modal-trigger postAddTagCircle'><i class='material-icons'>add_circle_outline</i></a>") : "")
."</div><!-- end .postTagsContainer -->
<div class='postRightContainer col l4 m4 s2'>
<div class='postDate right'></div>
<a href='#' class='dropdown-button waves-effect waves-lightgrey postSettingsButton' data-activates='postSettings".$random_num."'><i class='material-icons'>more_horiz</i></a>
</div>

<div class='postActionsContainer'>

<div class='postButtonsContainer'>
</div>


<div class='posterInfoMegaContainer focusOnVerticallyCenteredItemBeforeScalingToNormal'>

<div class='avatarContainer posterAvatarContainer'>
<div class='avatarContainerChild posterAvatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". $post_arr["posted_by"] ."'>
". ($poster_arr["avatar_picture"] == "" ? letter_avatarize($poster_arr["first_name"],"small") : "
<div class='rotateContainer' style='margin-top:".$poster_avatar_positions[0]."%;margin-left:".$poster_avatar_positions[1]."%;'>
<div class='avatarRotateDiv ". ($post_arr["posted_by"] == $_SESSION["user_id"] ? "baseUserAvatarRotateDivs" : "") ."' data-rotate-degree='".$poster_avatar_arr["rotate_degree"]."'>
<img id='avatar".$random_num."' class='avatarImages posterAvatarImages' src='".$poster_arr["avatar_picture"]."' alt='Image'/>
</div>
</div>") ."
</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->

<a href='#modal1' class='commonLink modal-trigger view-user showUserModal' data-user-id='". $post_arr["posted_by"] ."'>". htmlspecialchars($poster_arr["first_name"] . " " . $poster_arr["last_name"]) ."</a>
</div><!-- end .posterInfoMegaContainer -->

</div>

</div><!-- end .postBottomContainer -->
</div><!-- end .singlePost -->
";	
}


?>