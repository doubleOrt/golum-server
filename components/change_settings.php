<?php
//we make an ajax call to this page everytime a user wants to change his settings from the account tab in the settings page.


require_once 'common_requires.php';
require_once "logged_in_importants.php";
require_once '../../phpmailer/PHPMailerAutoload.php';





if(isset($_GET["current_password"])) {
	
$echo_arr = ["",true];	

$current_password = $_GET["current_password"];

$check_current_password = new ValidateItem($current_password,"/^(?=.*[A-Za-z])(?=.*\d)(?=.*([$@$!%*#?& ]*))[A-Za-z\d($@$!%*#?& )*]{8,50}$/i","Wrong Info"); 

/* if the user has provided us with a valid password */
if($check_current_password->validate() === true) {

/* if the user has provided us with a correct password */
if(password_verify($current_password,$user_info_arr["password"])) {

# when a user wants to deactivate or delete his account.
if(isset($_GET["deactivate_or_delete"]) && $_GET["deactivate_or_delete"] != "") {

# if user wants to deactivate his/her account.
if($_GET["deactivate_or_delete"] == "deactivate") {
$con->query("insert into account_states (user_id,type,time) values(".$_SESSION["user_id"].",'deactivate',".time().")");
array_push($_SESSION["toasts"],"<script>Materialize.toast('Your Account Is Currently Deactivated, You Can Activate It Again By Simply Logging In.',25000,'green');</script>");
unset($_SESSION["user_id"]);
$echo_arr[0] .= "window.location.href =  'login_and_sign_up.php'";
$echo_arr[1] = "false";
echo json_encode($echo_arr);	
die();		
}
# if user wants to delete his/her account.
else if($_GET["deactivate_or_delete"] == "delete") {
$con->query("insert into account_states (user_id,type,time) values(".$_SESSION["user_id"].",'delete',".time().")");
array_push($_SESSION["toasts"],"<script>Materialize.toast('Your Account Will Be Deleted If You Don\'t Log In In The Next 2 Weeks',25000,'red');</script>");
unset($_SESSION["user_id"]);
$echo_arr[0] .= "window.location.href =  'login_and_sign_up.php'";
$echo_arr[1] = "false";
echo json_encode($echo_arr);	
die();		
}

}






$checks_arr = [
"change_first_name"=>new ValidateItem($_GET["change_first_name"],'/^[a-zA-Z\s]{3,18}$/i',"First Name Must Only Contain Letters And Spaces And Must Be Longer Than 3 And Shorter Than 18 Characters"),
"change_last_name"=>new ValidateItem($_GET["change_last_name"],'/^[a-zA-Z\s]{3,18}$/i',"Last Name Must Only Contain Letters And Spaces And Must Be Longer Than 3 And Shorter Than 18 Characters"),
"change_user_name"=>new ValidateItem($_GET["change_user_name"],'/^([a-zA-Z]+[0-9 ]*){6,36}$/i',"Username Must Be A Combination Of Letters, Numbers And Spaces And Muse Be Between 6-36 Characters In Length"),
"change_password"=>new ValidateItem($_GET["change_password"],'/^(?=.*[A-Za-z])(?=.*\d)(?=.*([$@$!%*#?& ]*))[A-Za-z\d($@$!%*#?& )*]{8,50}$/i',"Password Must Contain At Least 1 Digit And Must Be Between 8-50 Characters, Special Characters And Spaces Are Optional"),
"add_email"=>new ValidateItem($_GET["add_email"],'/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/',"Your Email Address Is Invalid"),
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

$change_first_name = $_GET["change_first_name"];
$change_last_name = $_GET["change_last_name"];
$change_user_name = $_GET["change_user_name"];

$check_if_username_already_exists = $con->prepare("select id from users where user_name = :user_name and id != :id");
$check_if_username_already_exists->bindParam(":user_name",$_GET["change_user_name"]);
$check_if_username_already_exists->bindParam(":id",$_SESSION["user_id"]);
$check_if_username_already_exists->execute();
if($check_if_username_already_exists->fetch()["id"] != "") {
$echo_arr[0] .= "Materialize.toast('Username Already Exists',6000,'red');";
$echo_arr[1] = "false";
clearToasts();
echo json_encode($echo_arr);	
die();			
}

$change_password = ($_GET["change_password"] != "" ? password_hash($_GET["change_password"],PASSWORD_BCRYPT) : $user_info_arr["password"]);
$add_email = ($_GET["add_email"] != "" ? $_GET["add_email"] : $user_info_arr["email_address"]);

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

$activation_code = rand(100000,1000000);			

$mail = new PHPMailer;

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'fantasybl8@gmail.com';                 // SMTP username
$mail->Password = 'georgedies';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;                                    // TCP port to connect to

$mail->setFrom('fantasybl8@gmail.com', "Golum");
$mail->addAddress($add_email, 'User');     // Add a recipient
$mail->addReplyTo('fantasybl8@gmail.com', '');
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = $user_info_arr["user_name"];
$mail->Body = "<div style='width: 260px;height: 350px;margin:20px auto 20px auto;overflow:hidden;font-family:arial;border-radius: 0 0 5px 5px;box-sizing: border-box;border:1px solid lightgrey;border-top:none;'>
<div style='display:block !important;width:100%;height:5px;background:-webkit-linear-gradient(0, yellow, green);margin:0;'></div>
<div style='width:100%;height:100%;padding: 20px 25px 10px 25px;box-sizing:border-box;'>
<img src='http://orig14.deviantart.net/e15b/f/2017/185/7/6/logo_by_torostorocrcs-dbf4t3n.png' alt='Logo' style='display:block;width:100px;height:100px;margin:0 auto;'>
<p style='color:#494949;font-weight: 600;font-size: 12px;text-align: center;border-top:1px solid #ebeaea;padding-top:10px;'>Hello <span style='color:yellowgreen'>". $user_info_arr["user_name"] ."</span>, You Recently Requested To Link This Email To Your Account.
Here Is The Email Confirmation Code:
</p>
<div style='width:50%;height:1px;margin:0 auto;background-color:#ebeaea;'></div>
<div style='font-size:24px;color:white;text-align:center;font-family:arial;font-weight:bold;margin:20px 0;letter-spacing: 2px;cursor: default;'>
<span style='background:-webkit-linear-gradient(36deg, yellow, green);padding:6px 16px'>". $activation_code ."</span>
</div>
</div>
</div>";
$mail->AltBody = "";

if($mail->send()) {
if($con->query("update users set activated = '". $activation_code ."' where id = ". $_SESSION["user_id"])) {
$echo_arr[0] .= "Materialize.toast('Verification Email Sent To Your Email Address, Please Verify Your Email To Complete The Email Linking Process.',5000,'green');";
}
else {
$echo_arr[0] .= "Materialize.toast('Something Went Wrong, The Email Address You Received Is Useless, You Should Just Ignore It.',5000,'red');";
$echo_arr[1] = "false";
echo json_encode($echo_arr);	
die();		
}
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
$echo_arr[1] = "false";
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
clearToasts();
echo json_encode($echo_arr);	
die();		
}

	
}		
else {
$echo_arr[0] .= "Materialize.toast('Wrong Password',5000,'red');";	
$echo_arr[1] = "false";
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