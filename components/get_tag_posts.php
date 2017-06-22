<?php
//we make a call to this page wants to view all of a tag's posts.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";

if(isset($_GET["tag"]) && isset($_GET["last_post_id"]) && is_integer(intval($_GET["last_post_id"])) && isset($_GET["sort_posts_by"])) {

$echo_arr = ["",""];

if($_GET["last_post_id"] == 0) {
$current_tag_follow_state = $con->query("select id from following_tags where id_of_user = ". $_SESSION["user_id"] ." and tag = '". htmlspecialchars($_GET["tag"]) ."'")->fetch();
// the follow tag button.
$echo_arr[1] .= "<a href='#' class='waves-effect wavesCustom btn commonButtonWhite navRightItemsMobileCommonButton addTagFromTagPostsModal scaleVerticallyCenteredItem' data-tag='". htmlspecialchars($_GET["tag"]) ."' data-current-state='". ($current_tag_follow_state == "" ? "0" : "1") ."'>". ($current_tag_follow_state == "" ? "Follow" : "Unfollow") ."</a>";
}
	

if($_GET["sort_posts_by"] == 0) {
if($_GET["last_post_id"] == 0) {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from (select * from posts where title like '% ". $_GET["tag"] ." %' or title like '%". htmlspecialchars($_GET["tag"]) ."' or title like '". htmlspecialchars($_GET["tag"]) ." %') t1, (SELECT @rn:=0) t2) new_table order by new_table.new_id desc limit 3");
$prepared->execute();
}
// when the user is infinite scrolling
else {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from (select * from posts where title like '% ". htmlspecialchars($_GET["tag"]) ." %' or title like '%". htmlspecialchars($_GET["tag"]) ."' or title like '". htmlspecialchars($_GET["tag"]) ." %') t1, (SELECT @rn:=0) t2) new_table where new_table.new_id < :last_post_id order by new_table.new_id desc limit 3");
$prepared->bindParam(":last_post_id",$_GET["last_post_id"]);
$prepared->execute();	
}
}
else if($_GET["sort_posts_by"] == 1) {

if($_GET["last_post_id"] == 0) {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from (select * from posts where title like '% ". $_GET["tag"] ." %' or title like '%". htmlspecialchars($_GET["tag"]) ."' or title like '". htmlspecialchars($_GET["tag"]) ." %') t1, (SELECT @rn:=0) t2) new_table order by new_table.new_id desc limit 3");
$prepared->execute();
}
// when the user is infinite scrolling
else {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from (select * from posts where title like '% ". htmlspecialchars($_GET["tag"]) ." %' or title like '%". htmlspecialchars($_GET["tag"]) ."' or title like '". htmlspecialchars($_GET["tag"]) ." %') t1, (SELECT @rn:=0) t2) new_table where new_table.new_id < :last_post_id order by new_table.new_id desc limit 3");
$prepared->bindParam(":last_post_id",$_GET["last_post_id"]);
$prepared->execute();	
}
	
}

$posts_arr = $prepared->fetchAll();

if(count($posts_arr) > 0) {
for($i = 0;$i<count($posts_arr);$i++) {
$echo_arr[0] .= get_post_markup($posts_arr[$i],"tagPosts");
}	
}
else if($_GET["last_post_id"] == 0) {
$echo_arr[0] .= "<div class='emptyNowPlaceholder'>
<i class='material-icons'>info</i>
<br>
No Posts For This Tag! </div>";
}
	
	
echo json_encode($echo_arr);	
	
}



?>