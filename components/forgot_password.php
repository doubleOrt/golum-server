<?php

require_once "common_requires.php";
require_once '../../phpmailer/PHPMailerAutoload.php';



if(isset($_GET["user_name"]) && !isset($_GET["reset_code"])) {
		
$select_email_via_user_name = $con->prepare("select * from users where user_name = :user_name");	
$select_email_via_user_name->bindParam(":user_name",$_GET["user_name"]);
$select_email_via_user_name->execute();	

$select_email_via_user_name_arr = $select_email_via_user_name->fetch();	
	
if($select_email_via_user_name_arr["email_address"] != "") {
	
$last_forgot_password_email_sent = $con->query("select last_forgot_password_email_sent from users where id = ". $select_email_via_user_name_arr["id"])->fetch()[0];	
	
if(time() - $last_forgot_password_email_sent >= 120) {
	
$forgot_password_code = rand(100000,1000000);			

$mail = new PHPMailer;

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'fantasybl8@gmail.com';                 // SMTP username
$mail->Password = 'georgedies';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;                                    // TCP port to connect to

$mail->setFrom('fantasybl8@gmail.com', "Password Reset - Ortify");
$mail->addAddress($select_email_via_user_name_arr["email_address"], 'Message From Ortify');     // Add a recipient
$mail->addReplyTo('fantasybl8@gmail.com', '');
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = $select_email_via_user_name_arr["user_name"];
$mail->Body = "Hello <b>" . $select_email_via_user_name_arr["user_name"] . "</b>, You Recently Requested To Reset Your Password, Here Is The Password Reset Code: ".
$forgot_password_code;
$mail->AltBody = "";

if($mail->send()){
$con->exec("update users set last_forgot_password_email_sent = ". time() .", password_reset_code = ". $forgot_password_code ." where id = ". $select_email_via_user_name_arr["id"]);
echo "Materialize.toast('Password Reset Code Sent To Your Email',5000);";
echo "$('.forgotPasswordFormContainer').html(\"<div class='input-field col l8 m8 s8'><input id='password_reset_code' name='password_reset_code' type='text'><label for='password_reset_code'>Reset Code</label></div><div class='input-field col l4 m4 s4 smallFormButtonContainer'><a href='#' id='password_reset_code_button' class='waves-effect waves-ff3333 btn commonButton'>Next</a></div>\");";
}
else {
echo "Materialize.toast('Something Went Wrong, Sorry',5000,'red');";			
}
}
else {
echo "Materialize.toast('Please Try Again In ". date("i:s",(($select_email_via_user_name_arr["last_forgot_password_email_sent"] + 120)-time())) ."',5000);";	
}
}	
else{
echo "Materialize.toast('You Can Not Reset Your Password Because This Account Is Not Linked With Any Email Addresses!',5000,'red');";	
}
}




if(isset($_GET["user_name"]) && isset($_GET["reset_code"]) && !isset($_GET["new_password"])) {

$check_if_valid_reset_code = $con->prepare("select id from users where user_name = :user_name and password_reset_code = :password_reset_code");	
$check_if_valid_reset_code->bindParam(":user_name",$_GET["user_name"]);
$check_if_valid_reset_code->bindParam(":password_reset_code",$_GET["reset_code"]);
$check_if_valid_reset_code->execute();	

$check_if_valid_reset_code_user_id = $check_if_valid_reset_code->fetch()[0];

if($check_if_valid_reset_code_user_id != "") {
echo "$('.forgotPasswordFormContainer').html(\"<div class='input-field col l8 m8 s8'><input id='new_password_input' name='new_password_input' type='password'><label for='new_password_input'>New Password</label></div><div class='input-field col l4 m4 s4 smallFormButtonContainer'><a href='#' id='new_password_button' class='waves-effect waves-ff3333 btn commonButton'>Update</a></div>\");";	
}
else {
echo "Materialize.toast('Invalid Code',5000,'red');";		
}
	
}


if(isset($_GET["user_name"]) && isset($_GET["reset_code"]) && isset($_GET["new_password"])) {

$check_if_valid_reset_code = $con->prepare("select id from users where user_name = :user_name and password_reset_code = :password_reset_code");	
$check_if_valid_reset_code->bindParam(":user_name",$_GET["user_name"]);
$check_if_valid_reset_code->bindParam(":password_reset_code",$_GET["reset_code"]);
$check_if_valid_reset_code->execute();	

$check_if_valid_reset_code_user_id = $check_if_valid_reset_code->fetch()[0];

if($check_if_valid_reset_code_user_id != "") {	
$check_login_password = new ValidateItem($_GET["new_password"],"/^(?=.*[A-Za-z])(?=.*\d)(?=.*([$@$!%*#?& ]*))[A-Za-z\d($@$!%*#?& )*]{8,50}$/i","Password Must Contain At Least 1 Digit And Must Be Between 8-50 Characters, Special Characters And Spaces Are Optional"); 
if($check_login_password->validate() == true) {
$new_password = password_hash($_GET["new_password"],PASSWORD_BCRYPT);
$con->exec("update users set password_reset_code = '', last_forgot_password_email_sent = 0, password = '".$new_password."' where id = ". $check_if_valid_reset_code_user_id);		
echo "Materialize.toast('Password Updated Successfully',5000,'green');$('#forgotPasswordModal').closeModal();check_new_password = undefined;forgot_password_username = undefined;reset_code = undefined;";
echo "$('.forgotPasswordFormContainer').html(\"<div class='input-field col l8 m8 s8'><input id='forgot_password_username' name='forgot_password_username' type='text'><label for='forgot_password_username'>Username</label></div><div class='input-field col l4 m4 s4 smallFormButtonContainer'><a href='#' id='forgot_password_username_button' class='waves-effect waves-ff3333 btn commonButton'>Next</a></div>\");";
}
else {
echo "Materialize.toast('Password Must Contain At Least 1 Digit And Must Be Between 8-50 Characters, Special Characters And Spaces Are Optional',5000,'red');";	
}
}
}


?>