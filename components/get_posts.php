<?php
// when a user wants to see posts from his feed, we make a call to this page.


require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";


if(isset($_GET["last_post_id"]) && is_numeric($_GET["last_post_id"])) {

$echo_arr = [""];	
	
	
$tags_followed_by_user = $con->query("select tag from following_tags where id_of_user = ". $_SESSION["user_id"])->fetchAll();		
	
// when the user wants to see the first 10 posts	
if($_GET["last_post_id"] == 0) {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from (select * from posts where posted_by in (select contact from contacts where contact_of = :user_id) or posted_by = :user_id) t1, (SELECT @rn:=0) t2) new_table order by new_table.new_id desc limit 3");
$prepared->bindParam(":user_id",$_SESSION["user_id"]);
$prepared->execute();
}
// when the user is infinite scrolling
else {
$prepared = $con->prepare("select * from (select *, @rn:=@rn+1 AS new_id from (select * from posts where posted_by in (select contact from contacts where contact_of = :user_id) or posted_by = :user_id) t1, (SELECT @rn:=0) t2) new_table where new_table.new_id < :last_post_id order by new_table.new_id desc limit 3");
$prepared->bindParam(":last_post_id",$_GET["last_post_id"]);
$prepared->bindParam(":user_id",$_SESSION["user_id"]);
$prepared->execute();	
}

$posts_arr = $prepared->fetchAll();

// select all from the user blocking table where this user has blocked another user or this user has been blocked by another user.
$this_user_related_blocks = $con->query("select user_ids from blocked_users where user_ids like '%-" . $_SESSION["user_id"]."' or user_ids like '". $_SESSION["user_id"] ."-%'")->fetchAll();	

for($i = 0;$i<count($posts_arr);$i++) {
	
//iterate through the user blocking table and continue the posts loop if you find out that the poster has either blocked this user or has been blocked by this user.	
foreach($this_user_related_blocks as $this_user_related_block) {
$blocked_or_blocking_user_id = (explode("-",$this_user_related_block[0])[0] == $_SESSION["user_id"] ? explode("-",$this_user_related_block[0])[1] : explode("-",$this_user_related_block[0])[0]);
if($posts_arr[$i]["posted_by"] == $blocked_or_blocking_user_id) {
continue 2;	
}
}
	
$echo_arr[0] .= get_post_markup($posts_arr[$i],"posts");
}


echo json_encode($echo_arr);
}



?>