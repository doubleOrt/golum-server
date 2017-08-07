<?php
// when the user opens a single post, e.g. he can open one from the notifications modal.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "get_post_data_function.php";


$echo_arr = [];	

if(isset($_GET["post_id"]) && filter_var($_GET["post_id"], FILTER_VALIDATE_INT) !== false) {
$single_post_arr_prepared = $con->prepare("select * from posts where id = :post_id and (posted_by not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and posted_by not in (SELECT user_id from account_states))");	
$single_post_arr_prepared->execute([":post_id" => $_GET["post_id"], ":base_user_id" => $GLOBALS["base_user_id"]]);
$single_post_arr = $single_post_arr_prepared->fetch();

// just in case the post was deleted
if($single_post_arr !== false && $single_post_arr["disabled"] !== "true") {
array_push($echo_arr, get_post_data($single_post_arr));
}

}

echo json_encode($echo_arr);

unset($con);

?>