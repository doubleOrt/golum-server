<?php
//we make a call to this page wants to view a section's posts.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";

if(isset($_GET["section_id"]) && isset($_GET["last_post_id"]) && is_integer(intval($_GET["section_id"])) && is_integer(intval($_GET["last_post_id"]))) {


// if user wants to view hot posts in the section
if($_GET["hot_or_new"] == 0) {
// when the user wants to see the first 10 posts	
if($_GET["last_post_id"] == 0) {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from ( select *, (select count(id) from post_votes where post_votes.post_id = posts.id) as total_votes, (select count(id) from favorites where favorites.post_id = posts.id) as total_favorites, (select count(id) from notifications where type = 5 and notifications.extra = posts.id) as total_sends from posts where post_section = :post_section order by total_votes desc, total_favorites desc) t1, (SELECT @rn:=0 ) t2) new_table order by new_table.new_id asc limit 3");
$prepared->bindParam(":post_section",$_GET["section_id"]);
$prepared->execute();
}
// when the user is infinite scrolling
else {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from ( select *, (select count(id) from post_votes where post_votes.post_id = posts.id) as total_votes, (select count(id) from favorites where favorites.post_id = posts.id) as total_favorites, (select count(id) from notifications where type = 5 and notifications.extra = posts.id) as total_sends from posts where post_section = :post_section order by total_votes desc, total_favorites desc) t1, (SELECT @rn:=0 ) t2) new_table where new_table.new_id > :last_post_id order by new_table.new_id asc limit 3");
$prepared->bindParam(":post_section",$_GET["section_id"]);
$prepared->bindParam(":last_post_id",$_GET["last_post_id"]);
$prepared->execute();	
}
}
// if user wants to view latest posts in the section
else {
// when the user wants to see the first 10 posts	
if($_GET["last_post_id"] == 0) {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from (select * , (SELECT @rn:=0) from posts where post_section = :post_section) t1) new_table order by new_table.new_id desc limit 3");
$prepared->bindParam(":post_section",$_GET["section_id"]);
$prepared->execute();
}
// when the user is infinite scrolling
else {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from (select * , (SELECT @rn:=0) from posts where post_section = :post_section) t1) new_table where new_table.post_section = :post_section and new_table.new_id < :last_post_id order by new_table.new_id desc limit 3");
$prepared->bindParam(":post_section",$_GET["section_id"]);
$prepared->bindParam(":last_post_id",$_GET["last_post_id"]);
$prepared->execute();	
}	
}

$posts_arr = $prepared->fetchAll();


if(count($posts_arr) > 0) {
for($i = 0;$i<count($posts_arr);$i++) {
echo get_post_markup($posts_arr[$i],"sectionPosts");
}
}
else if($_GET["last_post_id"] == 0) {
echo "<div class='emptyNowPlaceholder'>
<i class='material-icons'>info</i>
<br>
No Posts For This Section! </div>";	
}	
		
}


?>