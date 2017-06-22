<?php
// when a user wants to see posts from his feed, we make a call to this page.


require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";


if(isset($_GET["user_id"]) && is_numeric($_GET["user_id"]) && isset($_GET["last_post_id"]) && is_numeric($_GET["last_post_id"])) {
	
$echo_arr = [""];	
	
// when the user wants to see the first 10 posts	
if($_GET["last_post_id"] == 0) {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from (select * from posts where posted_by = :posted_by) t1, (SELECT @rn:=0) t2) new_table order by new_table.new_id desc limit 3");
$prepared->bindParam(":posted_by",$_GET["user_id"]);
$prepared->execute();
}
// when the user is infinite scrolling
else {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from (select * from posts where posted_by = :posted_by) t1, (SELECT @rn:=0) t2) new_table where new_table.new_id < :last_post_id order by new_table.new_id desc limit 3");
$prepared->bindParam(":posted_by",$_GET["user_id"]);
$prepared->bindParam(":last_post_id",$_GET["last_post_id"]);
$prepared->execute();	
}

$posts_arr = $prepared->fetchAll();

for($i = 0;$i<count($posts_arr);$i++) {
$echo_arr[0] .= get_post_markup($posts_arr[$i],"userPosts");
}

echo json_encode($echo_arr);
}

?>