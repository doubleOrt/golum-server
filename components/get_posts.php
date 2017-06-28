<?php
// when a user wants to see posts from his feed, we make a call to this page.


require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";


$echo_arr = [];	


if(isset($_GET["row_offset"]) && is_integer(intval($_GET["row_offset"]))) {	
	
$tags_followed_by_user = $con->query("select tag from following_tags where id_of_user = ". $_SESSION["user_id"])->fetchAll();		
	

$prepared = $con->prepare("select * from posts where posted_by in (select contact from contacts where contact_of = :user_id) or posted_by = :user_id order by id desc limit 3 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$prepared->bindParam(":user_id",$_SESSION["user_id"]);
$prepared->execute();

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
	
array_push($echo_arr, get_post_markup($posts_arr[$i]));
}

}

echo json_encode($echo_arr);

// close the connection
unset($con);

?>