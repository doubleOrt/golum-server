<?php
// calls are made to this page to get the time of a post.

require_once "common_requires.php";
require_once "logged_in_importants.php";



$echo_arr = [];

if(isset($_GET["post_ids"]) && is_array($_GET["post_ids"])) {
	
$posts_query_string = "";
for($i = 0;$i < count($_GET["post_ids"]);$i++) {
if(filter_var($_GET["post_ids"][$i], FILTER_VALIDATE_INT) === false) {
echo json_encode($echo_arr);	
die();	
}
if($i != 0) {
$posts_query_string .= " or ";	
}	
$posts_query_string .= "posts.id = ". $_GET["post_ids"][$i];	
}


$all_posts_arr_prepared = $con->prepare("select distinct posts.id, posts.time, favorites.id as added_to_favorites, (select count(id) from post_comments where post_id = posts.id and post_comments.user_id not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and post_comments.user_id not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and post_comments.user_id not in (SELECT user_id from account_states)) as post_comments_num from posts left join favorites on posts.id = favorites.post_id and favorites.user_id = ". $GLOBALS["base_user_id"] ." left join post_comments on post_comments.post_id = posts.id where (". $posts_query_string .")");
$all_posts_arr_prepared->execute([":base_user_id" => $GLOBALS["base_user_id"]]);
$all_posts_arr = $all_posts_arr_prepared->fetchAll();


foreach($all_posts_arr as $post_arr) {
array_push($echo_arr,[$post_arr["id"], time_to_string($post_arr["time"]), htmlspecialchars($post_arr["post_comments_num"], ENT_QUOTES, "utf-8") , ($post_arr["added_to_favorites"] == "" ? 0 : 1)]);
}

}


echo json_encode($echo_arr);

unset($con);



?>