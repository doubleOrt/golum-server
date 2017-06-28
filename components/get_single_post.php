<?php
// when the user opens a single post, e.g. he can open one from the notifications modal.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";


$echo_arr = [];	

if(isset($_GET["post_id"]) && is_integer(intval($_GET["post_id"]))) {
$single_post_query = $con->query("select *, 1 as new_id from posts where id = ". $_GET["post_id"])->fetch();	
array_push($echo_arr, get_post_markup($single_post_query));
}

echo json_encode($echo_arr);


?>