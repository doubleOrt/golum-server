<?php

require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_POST["tag"])) {
custom_pdo("insert into following_tags (id_of_user,tag) values(:base_user_id, :tag)", [":base_user_id" => $_SESSION["user_id"], ":tag" => $_POST["tag"]]);
}


unset($con);

?>