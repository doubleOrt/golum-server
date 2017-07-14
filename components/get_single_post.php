<?php
// when the user opens a single post, e.g. he can open one from the notifications modal.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";


$echo_arr = [];	

if(isset($_GET["post_id"]) && filter_var($_GET["post_id"], FILTER_VALIDATE_INT) !== false) {
$single_post_arr = $con->query("select * from posts where id = ". $_GET["post_id"])->fetch();	
// just in case the post was deleted
if($single_post_arr !== false) {
array_push($echo_arr, get_post_markup($single_post_arr));
}
}

echo json_encode($echo_arr);


?>