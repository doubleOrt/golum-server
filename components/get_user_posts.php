<?php
// when a user wants to see posts from his feed, we make a call to this page.


require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";

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
	
$prepared = $con->prepare("select * from posts where posted_by = :posted_by and (posted_by not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and posted_by not in (SELECT user_id from account_states)) order by id desc limit 3 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$prepared->bindParam(":posted_by", $_GET["user_id"]);
$prepared->bindParam(":base_user_id", $GLOBALS["base_user_id"]);
$prepared->execute();

$posts_arr = $prepared->fetchAll();

for($i = 0;$i<count($posts_arr);$i++) {

// if post has been reported too many times
if($posts_arr[$i]["disabled"] === "true") {
continue;	
}		
	
array_push($echo_arr[0], get_post_markup($posts_arr[$i]));
}

}

echo json_encode($echo_arr);


unset($con);

?>