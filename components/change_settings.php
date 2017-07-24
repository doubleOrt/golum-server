<?php
//we make an ajax call to this page everytime a user wants to change his settings from the account tab in the settings page.


require_once 'common_requires.php';
require_once "logged_in_importants.php";
require_once "send_confirmation_code_function.php";
require_once '../../phpmailer/PHPMailerAutoload.php';





if(isset($_POST["current_password"])) {
	
$echo_arr = ["","true", "true"];	

$current_password = $_POST["current_password"];

$check_current_password = new ValidateItem($current_password,"/^(?=.*[A-Za-z])(?=.*\d)(?=.*([$@$!%*#?& ]*))[A-Za-z\d($@$!%*#?& )*]{8,50}$/i","Wrong Info"); 

/* if the user has provided us with a valid password */
if($check_current_password->validate() === true) {

/* if the user has provided us with a correct password */
if(password_verify($current_password,$user_info_arr["password"])) {

# when a user wants to deactivate or delete his account.
if(isset($_POST["deactivate_or_delete"]) && $_POST["deactivate_or_delete"] != "") {

# if user wants to deactivate his/her account.
if($_POST["deactivate_or_delete"] == "deactivate") {
$con->query("insert into account_states (user_id,type,time) values(".$_SESSION["user_id"].",'deactivate',".time().")");
array_push($_SESSION["toasts"],"<script>Materialize.toast('Your Account Is Currently Deactivated, You Can Activate It Again By Simply Logging In.',25000,'green');</script>");
unset($_SESSION["user_id"]);
$echo_arr[0] .= "window.location.href =  'login_and_sign_up.html'";
$echo_arr[1] = "false";
echo json_encode($echo_arr);	
die();		
}
# if user wants to delete his/her account.
else if($_POST["deactivate_or_delete"] == "delete") {
$con->query("insert into account_states (user_id,type,time) values(".$_SESSION["user_id"].",'delete',".time().")");
array_push($_SESSION["toasts"],"<script>Materialize.toast('Your Account Will Be Deleted If You Don\'t Log In In The Next 2 Weeks',25000,'red');</script>");
unset($_SESSION["user_id"]);
$echo_arr[0] .= "window.location.href =  'login_and_sign_up.html'";
$echo_arr[1] = "false";
echo json_encode($echo_arr);	
die();		
}

}






$checks_arr = [
"change_first_name"=>new ValidateItem($_POST["change_first_name"],'/^[a-zA-Z\s]{3,18}$/i',"First Name Must Only Contain Letters And Spaces And Must Be Longer Than 3 And Shorter Than 18 Characters"),
"change_last_name"=>new ValidateItem($_POST["change_last_name"],'/^[a-zA-Z\s]{3,18}$/i',"Last Name Must Only Contain Letters And Spaces And Must Be Longer Than 3 And Shorter Than 18 Characters"),
"change_user_name"=>new ValidateItem($_POST["change_user_name"],'/^([a-zA-Z]+[0-9 ]*){6,36}$/i',"Username Must Be A Combination Of Letters, Numbers And Spaces And Muse Be Between 6-36 Characters In Length"),
"change_password"=>new ValidateItem($_POST["change_password"],'/^(?=.*[A-Za-z])(?=.*\d)(?=.*([$@$!%*#?& ]*))[A-Za-z\d($@$!%*#?& )*]{8,50}$/i',"Password Must Contain At Least 1 Digit And Must Be Between 8-50 Characters, Special Characters And Spaces Are Optional"),
"add_email"=>new ValidateItem($_POST["add_email"],'/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/',"Your Email Address Is Invalid"),
];

foreach($checks_arr as $key => $item) {

if(($key == "change_password"|| $key == "add_email") && $item->value == "") {
continue;
}

if($item->value != "") {
if($item->validate() == false) {
$echo_arr[0] .= "Materialize.toast('". $item->on_wrong ."',6000,'red');";
$echo_arr[1] = "false";
clearToasts();
echo json_encode($echo_arr);	
die();		
}
}
}

$change_first_name = $_POST["change_first_name"];
$change_last_name = $_POST["change_last_name"];
$change_user_name = $_POST["change_user_name"];

$check_if_username_already_exists = $con->prepare("select id from users where user_name = :user_name and id != :id");
$check_if_username_already_exists->bindParam(":user_name",$_POST["change_user_name"]);
$check_if_username_already_exists->bindParam(":id",$_SESSION["user_id"]);
$check_if_username_already_exists->execute();
if($check_if_username_already_exists->fetch()["id"] != "") {
$echo_arr[0] .= "Materialize.toast('Username Already Exists',6000,'red');";
$echo_arr[1] = "false";
clearToasts();
echo json_encode($echo_arr);	
die();			
}

$change_password = ($_POST["change_password"] != "" ? password_hash($_POST["change_password"],PASSWORD_BCRYPT) : $user_info_arr["password"]);
$add_email = ($_POST["add_email"] != "" ? $_POST["add_email"] : $user_info_arr["email_address"]);

$prepare_changes = $con->prepare("update users set first_name = :first_name, last_name = :last_name, user_name = :user_name, password = :password, email_address = :email_address where id = ". $_SESSION["user_id"]);
$prepare_changes->bindParam(":first_name",$change_first_name);
$prepare_changes->bindParam(":last_name",$change_last_name);
$prepare_changes->bindParam(":user_name",$change_user_name);
$prepare_changes->bindParam(":password",$change_password);

$check_if_email_exists_already = $con->prepare("SELECT * FROM users where email_address = :email_address");
$check_if_email_exists_already->bindParam(":email_address",$add_email);
$check_if_email_exists_already->execute();
$check_if_email_exists_already_arr = $check_if_email_exists_already->fetchAll();

if($add_email != "" && $add_email != $user_info_arr["email_address"]) {

if(count($check_if_email_exists_already_arr) == 0) {
	
$prepare_changes->bindParam(":email_address",$add_email);

if(send_confirmation_code($_SESSION["user_id"], $add_email, $user_info_arr["user_name"]) === true) {
$echo_arr[0] .= "Materialize.toast('Verification Email Sent To Your Email Address, Please Verify Your Email To Complete The Email Linking Process.',5000,'green');";
}
else {
$echo_arr[0] .= "Materialize.toast('Something Went Wrong, Sorry!',5000,'red');";
$echo_arr[1] = "false";
echo json_encode($echo_arr);	
die();		
}

}
else {			 
$echo_arr[0] .= "Materialize.toast('Email Already Linked With Another Account',5000,'red');";
$echo_arr[1] = "false";
echo json_encode($echo_arr);	
die();		
}
}
else {
$prepare_changes->bindParam(":email_address",$user_info_arr["email_address"]);
}



if($prepare_changes->execute() === true) {
$echo_arr[0] .= "Materialize.toast('Changes Made Successfully',5000,'green');";
clearToasts();
echo json_encode($echo_arr);	
die();		
}
else {
$echo_arr[0] .= "Materialize.toast('Something Went Wrong, Sorry!',5000,'red');";
$echo_arr[1] = "false";
clearToasts();
echo json_encode($echo_arr);	
die();		
}

}
else {
$echo_arr[0] .= "Materialize.toast('Wrong Password',5000,'red');";		
$echo_arr[1] = "false";
$echo_arr[2] = "false";
clearToasts();
echo json_encode($echo_arr);	
die();		
}

	
}		
else {
$echo_arr[0] .= "Materialize.toast('Wrong Password',5000,'red');";	
$echo_arr[1] = "false";
$echo_arr[2] = "false";
clearToasts();
echo json_encode($echo_arr);	
die();		
}
	
}


/* note that we are calling and have created this function to fix an inconsistency where if a regex doesn't return true, it is pushed to the toasts are and thus toasted whenever 
the user refreshes, but we want to add our toasts directly via $echo_arr[0] .= to the ajax page. */
function clearToasts() {
$_SESSION["toasts"] = [];	
}





?>