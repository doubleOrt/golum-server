<?php
// we make an ajax call to this page when the user wants to add a section to a post.

require_once 'common_requires.php';
require_once "logged_in_importants.php";

if(isset($_POST["post_id"]) && isset($_POST["section_id"]) && is_numeric(intval($_POST["post_id"])) && is_numeric(intval($_POST["section_id"]))) {

// useful if someone wants to hack us, we check if the user requesting to change the post's section actually owns the post.
if($con->query("select posted_by from posts where id = ". $_POST["post_id"])->fetch()[0] == $_SESSION["user_id"]) {
$con->exec("update posts set post_section = ". $_POST["section_id"] ." where id = ". $_POST["post_id"]);	
}

}


?>