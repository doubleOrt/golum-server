<?php
// when a user wants to see posts from his feed, we make a call to this page.


require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";

$echo_arr = [[]];	

if(isset($_GET["user_id"]) && isset($_GET["row_offset"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false) {	
	
if($_GET["row_offset"] < 1) {	
$echo_arr[1] = $con->query("select * from contacts where contact_of = ".$_SESSION["user_id"]." and contact = ".$_GET["user_id"])->fetch()[0] == "" ? 0 : 1;
}
	
$prepared = $con->prepare("select * from posts where posted_by = :posted_by order by id desc limit 3 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$prepared->bindParam(":posted_by", $_GET["user_id"]);
$prepared->execute();

$posts_arr = $prepared->fetchAll();

for($i = 0;$i<count($posts_arr);$i++) {
array_push($echo_arr[0], get_post_markup($posts_arr[$i]));
}

}

echo json_encode($echo_arr);


unset($con);

?>