<?php
// when a user wants to see posts from his feed, we make a call to this page.


require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";


$echo_arr = [];	


if(isset($_GET["row_offset"]) && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false) {	
	
$tags_followed_by_user = $con->query("select tag from following_tags where id_of_user = ". $_SESSION["user_id"])->fetchAll();		
	

$prepared = $con->prepare("select * from posts where (posted_by in (select contact from contacts where contact_of = :base_user_id) or posted_by = :base_user_id) and (posted_by not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and posted_by not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and posted_by not in (SELECT user_id from account_states)) order by id desc limit 3 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$prepared->bindParam(":base_user_id",$_SESSION["user_id"]);
$prepared->execute();
$posts_arr = $prepared->fetchAll();

for($i = 0;$i<count($posts_arr);$i++) {
	
// if post has been reported too many times
if($posts_arr[$i]["disabled"] === "true") {
continue;	
}	

array_push($echo_arr, get_post_markup($posts_arr[$i]));
}

}

echo json_encode($echo_arr);

// close the connection
unset($con);

?>