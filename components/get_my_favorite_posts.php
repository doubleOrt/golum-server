<?php
// we make a call to this page whenever the user wants to see his favorite posts.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";


if(isset($_GET["last_post_id"]) && is_numeric($_GET["last_post_id"])) {
	
$echo_arr = [""];	

// when the user wants to see the first 10 posts	
if($_GET["last_post_id"] == 0) {
$prepared = $con->prepare("select * from (SELECT *, @rn:=@rn+1 AS new_id FROM ( SELECT * FROM favorites where user_id = ". $_SESSION["user_id"] ." ) t1, (SELECT @rn:=0) t2) new_table inner join posts on new_table.post_id = posts.id order by new_table.new_id desc limit 3");
$prepared->execute();
}
// when the user is infinite scrolling
else {
$prepared = $con->prepare("select * from (SELECT *, @rn:=@rn+1 AS new_id FROM ( SELECT * FROM favorites where user_id = ". $_SESSION["user_id"] ." ) t1, (SELECT @rn:=0) t2) new_table inner join posts on new_table.post_id = posts.id where new_table.new_id < :last_post_id order by new_table.id desc limit 3");
$prepared->bindParam(":last_post_id",$_GET["last_post_id"]);
$prepared->execute();	
}

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

$echo_arr[0] .= get_post_markup($posts_arr[$i],"favoritePosts");
}
}
else if($_GET["last_post_id"] == 0) {
$echo_arr[0] .= "<div class='emptyNowPlaceholder'>
<i class='material-icons'>info</i>
<br>
No Posts Favorited! </div>";	
}	
 
echo json_encode($echo_arr);

}



?>