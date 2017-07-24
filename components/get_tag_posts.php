<?php
//we make a call to this page wants to view all of a tag's posts.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";


$echo_arr = [[],""];

if(isset($_GET["tag"]) && isset($_GET["row_offset"]) && isset($_GET["sort_posts_by"]) && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false) {

if($_GET["row_offset"] == 0) {
$current_tag_follow_state = $con->query("select id from following_tags where id_of_user = ". $_SESSION["user_id"] ." and tag = '". htmlspecialchars($_GET["tag"], ENT_QUOTES, "utf-8") ."'")->fetch();
// the follow tag button.
$echo_arr[1] = $current_tag_follow_state[0] != "" ? 1 : 0;
}
	

if($_GET["sort_posts_by"] == 0) {
// we have increased the time thresholds a bit here, since unlike the featured posts section, not every tag may have a lot of posts in a short period of time.
$prepared = $con->prepare("select *, (select count(id) from post_votes where post_votes.post_id = posts.id) as total_votes, (select count(id) from favorites where favorites.post_id = posts.id) as total_favorites, (select count(id) from notifications where type = 5 and notifications.extra = posts.id) as total_sends, ROUND(time, -5) as condensed_post_time from posts where (title like concat('%',:tag ,'%') or title like concat('%', :tag) or title like concat(:tag,'%')) and (posted_by not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and posted_by not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and posted_by not in (SELECT user_id from account_states)) order by condensed_post_time desc, total_votes desc, total_favorites desc limit 3 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$prepared->bindParam(":tag", $_GET["tag"]);
$prepared->bindParam(":base_user_id", $_SESSION["user_id"]);
$prepared->execute();
}
else if($_GET["sort_posts_by"] == 1) {
$prepared = $con->prepare("select * from posts where (title like concat('%',:tag ,'%') or title like concat('%', :tag) or title like concat(:tag,'%')) and (posted_by not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and posted_by not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and posted_by not in (SELECT user_id from account_states)) order by id desc limit 3 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$prepared->bindParam(":tag", $_GET["tag"]);
$prepared->bindParam(":base_user_id", $_SESSION["user_id"]);
$prepared->execute();
}

$posts_arr = $prepared->fetchAll();

if(count($posts_arr) > 0) {
for($i = 0;$i<count($posts_arr);$i++) {

// if post has been reported too many times
if($posts_arr[$i]["disabled"] === "true") {
continue;	
}	
	
array_push($echo_arr[0], get_post_markup($posts_arr[$i]));
}	
}

	
}

echo json_encode($echo_arr);	


unset($con);

?>