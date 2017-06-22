<?php

require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_POST["tag"])) {
$con->exec("insert into following_tags (id_of_user,tag) values(".$_SESSION["user_id"].",'". htmlspecialchars($_POST["tag"]) ."')");
}



?>