<?php
// when a user wants to see his posts, we make a call to this page.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";


$echo_arr = [];	

if(isset($_GET["row_offset"]) && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false) {	
	
// this query is currently the same as the featured-posts query. change it later.	
$prepared = $con->prepare("select * from (select *, ROUND(time, -5) as condensed_post_time, (select count(id) from post_votes where post_votes.post_id = posts.id) as total_votes, (select count(id) from favorites where favorites.post_id = posts.id) as total_favorites, (select count(id) from notifications where type = 5 and notifications.extra = posts.id) as total_sends from posts) t1 where ((posted_by in (select contact from contacts where contact_of in (select contact from contacts where contact_of = :base_user_id) and contact not in (select contact from contacts where contact_of = :base_user_id))) or (posted_by in (select posted_by from posts where id in (select post_id from favorites where user_id = :base_user_id and (UNIX_TIMESTAMP() - time) < 259200) and posted_by not in(select contact from contacts where contact_of = :base_user_id))) or (posted_by in (select id_of_user from following_tags where tag in (select tag from following_tags where id_of_user = :base_user_id) and id_of_user not in (select contact from contacts where contact_of = :base_user_id) group by id_of_user having count(*) > 20)) or (id in (select id from posts left join (select tag from following_tags where id_of_user in (select id_of_user from following_tags where tag in (select tag from following_tags where id_of_user = :base_user_id) group by id_of_user having count(*) > 3) and tag not in (select tag from following_tags where id_of_user = :base_user_id)) t1 on (posts.tags like concat('%,', t1.tag) or posts.tags like concat(t1.tag, ',%') or posts.tags like concat('%,', t1.tag, ',%') or posts.tags like t1.tag) where tag is not null))) and posted_by != :base_user_id order by condensed_post_time desc, total_votes desc, total_favorites desc limit 3 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$prepared->execute([":base_user_id" => $GLOBALS["base_user_id"]]);
$my_posts_arr = $prepared->fetchAll();


if(count($my_posts_arr) > 0) {
for($i = 0;$i<count($my_posts_arr);$i++) {
	
// if post has been reported too many times
if($my_posts_arr[$i]["disabled"] === "true") {
continue;	
}		
	
array_push($echo_arr, get_post_markup($my_posts_arr[$i]));
}
}

}

echo json_encode($echo_arr);


unset($con);

?>