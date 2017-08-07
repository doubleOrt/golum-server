<?php
// we make a call to this page whenever the user wants to see his favorite posts.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_post_data_function.php";

$echo_arr = [[]];	

if(isset($_GET["user_id"]) && isset($_GET["row_offset"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false) {
	
	
if($_GET["row_offset"] < 1) {	
$current_state_prepared = $con->prepare("select id from contacts where contact_of = :base_user_id and contact = :user_id");
$current_state_prepared->execute([":base_user_id" => $GLOBALS["base_user_id"], ":user_id" => $_GET["user_id"]]);
$echo_arr[1][0] = $current_state_prepared->fetch()[0] == "" ? 0 : 1;
$user_blocked_by_base_user_prepared = $con->prepare("select id from blocked_users where user_ids = concat(:base_user_id, '-', :user_id)");
$user_blocked_by_base_user_prepared->execute([":base_user_id" => $GLOBALS["base_user_id"], ":user_id" => $_GET["user_id"]]);
$echo_arr[1][1] = ($user_blocked_by_base_user_prepared->fetch()[0] == "" ? 0 : 1);
}	
	
$prepared = $con->prepare("SELECT * FROM favorites INNER JOIN posts ON favorites.post_id = posts.id WHERE favorites.user_id = :user_id and (posted_by not in (SELECT * FROM (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) t1 where blocked_user != :user_id) and posted_by not in (SELECT * FROM (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) t1 where blocker != :user_id) and posted_by not in (SELECT user_id from account_states)) ORDER BY favorites.id DESC LIMIT 3 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$prepared->bindParam(":user_id", $_GET["user_id"]);
$prepared->bindParam(":base_user_id", $GLOBALS["base_user_id"]);
$prepared->execute();
$posts_arr = $prepared->fetchAll();

if(count($posts_arr) > 0) {
	
for($i = 0;$i<count($posts_arr);$i++) {
	
// if post has been reported too many times
if($posts_arr[$i]["disabled"] === "true") {
continue;	
}		

array_push($echo_arr[0], get_post_data($posts_arr[$i]));
}
}

}


echo json_encode($echo_arr);


unset($con);


?>