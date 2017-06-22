<?php

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_GET["user_id"])) {

$prepared = $con->prepare("select * from following_tags where id_of_user = :id_of_user");
$prepared->bindParam(":id_of_user",$_GET["user_id"]);
$prepared->execute();	

$prepared_arr = $prepared->fetchAll();

$echo_arr = [];

$echo_arr[0] = "";
for($i = 0;$i<count($prepared_arr);$i++) {
$echo_arr[0] .= "<span class='hashtag getTagPosts modal-trigger' data-target='tagPostsModal' data-tag='". htmlspecialchars($prepared_arr[$i]["tag"]) ."'>". htmlspecialchars($prepared_arr[$i]["tag"]) ."</span>";		
}	
	
echo json_encode($echo_arr);	
}
	



?>