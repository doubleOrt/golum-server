<?php
// when the user opens a single post, e.g. he can open one from the notifications modal.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";


if(isset($_GET["post_id"]) && is_numeric($_GET["post_id"])) {
$echo_arr = [""];	
$single_post_query = $con->query("select *, 1 as new_id from posts where id = ". $_GET["post_id"])->fetch();	
$echo_arr[0] .= get_post_markup($single_post_query,"singlePosts");
echo json_encode($echo_arr);
}



?>