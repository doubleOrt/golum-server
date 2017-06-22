<?php

session_start();

if(isset($_SESSION["user_id"])) {
echo "window.location.href = 'logged_in.html';";	
}

?>