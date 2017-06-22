<?php

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_POST["tag"])) {
$con->exec("delete from following_tags where id_of_user = ". $_SESSION["user_id"] ." and tag = '". $_POST["tag"] ."'");	
}


?>