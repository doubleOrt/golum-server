<?php

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_POST["tag"])) {
custom_pdo("delete from following_tags where id_of_user = :base_user_id and tag = :tag", [":base_user_id" => $_SESSION["user_id"], ":tag" => $_POST["tag"]]);	
}

unset($con);

?>