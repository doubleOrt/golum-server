<?php
// we make a call to this page whenever the user wants to see his favorite posts.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";

$echo_arr = [[]];	

if(isset($_GET["user_id"]) && isset($_GET["row_offset"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false) {
	
	
if($_GET["row_offset"] < 1) {	
$echo_arr[1] = $con->query("select * from contacts where contact_of = ". $_SESSION["user_id"] ." and contact = ". $_GET["user_id"])->fetch()[0] == "" ? 0 : 1;
}	
	
$prepared = $con->prepare("SELECT * FROM favorites INNER JOIN posts ON favorites.post_id = posts.id WHERE favorites.user_id = :user_id ORDER BY favorites.id DESC LIMIT 3 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$prepared->bindParam(":user_id", $_GET["user_id"]);
$prepared->execute();

$posts_arr = $prepared->fetchAll();

// select all from the user blocking table where this user has blocked another user or this user has been blocked by another user.
$this_user_related_blocks = $con->query("select id from blocked_users where user_ids like '%-" . $_SESSION["user_id"]."' or user_ids like '". $_SESSION["user_id"] ."-%'")->fetchAll();	

if(count($posts_arr) > 0) {
	
for($i = 0;$i<count($posts_arr);$i++) {
//iterate through the user blocking table and continue the posts loop if you find out that the poster has either blocked this user or has been blocked by this user.	
foreach($this_user_related_blocks as $this_user_related_block) {
$blocked_or_blocking_user_id = (explode("-",$this_user_related_block[0])[0] == $_SESSION["user_id"] ? explode("-",$this_user_related_block[0])[1] : explode("-",$this_user_related_block[0])[0]);
if($posts_arr[$i]["posted_by"] == $blocked_or_blocking_user_id) {
continue 2;	
}
}		

array_push($echo_arr[0], get_post_markup($posts_arr[$i]));
}
}

}


echo json_encode($echo_arr);


unset($con);


?>