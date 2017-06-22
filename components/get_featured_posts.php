<?php
// when a user wants to see his posts, we make a call to this page.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";

if(isset($_GET["last_post_id"]) && is_numeric($_GET["last_post_id"])) {
	
$echo_arr = [""];	
	
	
// when the user wants to see the first 10 posts	
if($_GET["last_post_id"] == 0) {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from ( select *, (select count(id) from post_votes where post_votes.post_id = posts.id) as total_votes, (select count(id) from favorites where favorites.post_id = posts.id) as total_favorites, (select count(id) from notifications where type = 5 and notifications.extra = posts.id) as total_sends from posts order by total_votes desc, total_favorites desc) t1, (SELECT @rn:=0 ) t2) new_table order by new_table.new_id asc limit 3");
$prepared->execute();
}
// when the user is infinite scrolling
else {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from ( select *, (select count(id) from post_votes where post_votes.post_id = posts.id) as total_votes, (select count(id) from favorites where favorites.post_id = posts.id) as total_favorites, (select count(id) from notifications where type = 5 and notifications.extra = posts.id) as total_sends from posts order by total_votes desc, total_favorites desc) t1, (SELECT @rn:=0 ) t2) new_table where new_table.new_id > :last_post_id order by new_table.new_id asc limit 3");
$prepared->bindParam(":last_post_id",$_GET["last_post_id"]);
$prepared->execute();	
}


$my_posts_arr = $prepared->fetchAll();

if(count($my_posts_arr) > 0) {
for($i = 0;$i<count($my_posts_arr);$i++) {
$echo_arr[0] .= get_post_markup($my_posts_arr[$i],"featuredPosts");
}
}
else if($_GET["last_post_id"] == 0) {
$echo_arr[0] .= "<div class='emptyNowPlaceholder'>
<i class='material-icons'>info</i>
<br>
You Have No Posts! </div>";	
}	

echo json_encode($echo_arr);

}


?>