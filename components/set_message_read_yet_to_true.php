<?php
// we make a call to this page immediately after an image in a chat has been opened.

require_once "common_requires.php";


if(isset($_GET["message_id"]) && filter_var($_GET["message_id"], FILTER_VALIDATE_INT) !== false) {
$message_id = intval($_GET["message_id"]);
$con->exec("update messages set read_yet = true where id = ". $message_id);	
write_shm($_SESSION["user_id"] . "" . 5,$message_id);
}


?>