<?php
#a call should be made to this page on all pages where the user is supposed to be logged in.


require_once "common_requires.php";

if(!isset($_SESSION["user_id"])) {
echo 0;
die();
}
else {
echo $_SESSION["user_id"];	
}


// notice that the order of these two conditionals matters, the first one unsets the session when the user logs out, the second one which wouldn't be true if it was above this conditional redirects the user to the login page.
if(isset($_POST["sign_out"]) && isset($_SESSION["user_id"])) {
unset($_SESSION["user_id"]);
setcookie("user_id","0",time() - 3600,"/");
header("location:../login_and_sign_up.html");	
}


?>