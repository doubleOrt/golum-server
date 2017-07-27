<?php

require_once "common_requires.php";

if(isset($_SESSION["user_id"])) {
echo "window.location.href = 'logged_in.html';";	
}

?>