<?php
// when a user wants to see his posts, we make a call to this page.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_post_data_function.php";


$echo_arr = [];	

if(isset($_GET["row_offset"]) && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false) {	
	
	
/* 86400 = 1 day, 259200 = 3 days, you could probably make this more efficient by dividing the post times by a certain number and then rounding them to the nearest decimal or whatever, you get the idea. That would make it more dynamic. */
$prepared = $con->prepare("select *, (select count(id) from post_votes where post_votes.post_id = posts.id) as total_votes, (select count(id) from favorites where favorites.post_id = posts.id) as total_favorites, (select count(id) from notifications where type = 5 and notifications.extra = posts.id) as total_sends, ROUND(time, -5) as condensed_post_time from posts where posted_by not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and posted_by not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and posted_by not in (SELECT user_id from account_states) order by condensed_post_time desc, total_votes desc, total_favorites desc limit 3 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$prepared->execute([":base_user_id" => $GLOBALS["base_user_id"]]);
$my_posts_arr = $prepared->fetchAll();


if(count($my_posts_arr) > 0) {
for($i = 0;$i<count($my_posts_arr);$i++) {

// if post has been reported too many times
if($my_posts_arr[$i]["disabled"] === "true") {
continue;	
}			
	
array_push($echo_arr, get_post_data($my_posts_arr[$i]));
}
}

}

echo json_encode($echo_arr);


unset($con);

?>