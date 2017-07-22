<?php
/* users sign in with google or some social network, we register them with a password-less account, 
however, we want them to have passwords so that they can change their account's settings and login 
directly using our app's login, so we force them to set one as soon as they login. */

require_once "common_requires.php";
require_once "logged_in_importants.php";

$echo_arr = [0, ""];


// make sure the user's state is as we want it (meaning they just logged in with google or something else for the first time).
if($user_info_arr["password"] == "") {

if(isset($_POST["password"])) {
$check_password = new ValidateItem($_POST["password"],'/^(?=.*[A-Za-z])(?=.*\d)(?=.*([$@$!%*#?& ]*))[A-Za-z\d($@$!%*#?& )*]{8,50}$/i',"Password Must Contain At Least 1 Digit And Must Be Between 8-50 Characters, Special Characters And Spaces Are Optional");

if($check_password->validate() === true) {
$hashed_password = password_hash($_POST["password"],PASSWORD_BCRYPT);
$con->prepare("update users set password = :password where id = :user_id")->execute([":password" => $hashed_password, ":user_id" => $_SESSION["user_id"]]);
$echo_arr[0] = 1;	
}
else {
$echo_arr[1] = $check_password->on_wrong();	
}
	
}
	
}


echo json_encode($echo_arr);

unset($con);


?>