<?php

require_once "common_requires.php";
require_once '../../phpmailer/PHPMailerAutoload.php';


$echo_arr = [0, ""];

$ONLY_ONE_PASSWORD_RESET_IN_EVERY_X_SECONDS = 30;


if(isset($_POST["user_name_or_email_address"]) && !isset($_POST["reset_code"])) {
		
$prepared = $con->prepare("select id, user_name, email_address, activated, last_forgot_password_email_sent from users where user_name = :user_name_or_email_address or (email_address = :user_name_or_email_address and activated = 'true')");	
$prepared->bindParam(":user_name_or_email_address",$_POST["user_name_or_email_address"]);
$prepared->execute();	

$user_name_info_array = $prepared->fetch();			
	
// if an account with that username exists, proceed to the next check.	
if($user_name_info_array["id"] != "") {
	
if($user_name_info_array["email_address"] != "") {
	
if($user_name_info_array["activated"] === "true") {
	
$last_forgot_password_email_sent = $con->query("select last_forgot_password_email_sent from users where id = ". $user_name_info_array["id"])->fetch()[0];	
	
if(time() - $last_forgot_password_email_sent >= $ONLY_ONE_PASSWORD_RESET_IN_EVERY_X_SECONDS) {
	
$forgot_password_code = rand(100000,1000000);			

$mail = new PHPMailer;

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'fantasybl8@gmail.com';                 // SMTP username
$mail->Password = 'georgedies';                           // SMTP password
$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 587;                                    // TCP port to connect to

$mail->setFrom('fantasybl8@gmail.com', "Password Reset - Golum");
$mail->addAddress($user_name_info_array["email_address"], '');     // Add a recipient
$mail->addReplyTo('fantasybl8@gmail.com', '');
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = $user_name_info_array["user_name"];
$mail->Body = "<div style='width: 260px;height: 350px;margin:20px auto 20px auto;overflow:hidden;font-family:arial;border-radius: 0 0 5px 5px;box-sizing: border-box;border:1px solid lightgrey;border-top:none;'>
<div style='display:block !important;width:100%;height:5px;background:-webkit-linear-gradient(0, yellow, green);margin:0;'></div>
<div style='width:100%;height:100%;padding: 20px 25px 10px 25px;box-sizing:border-box;'>
<img src='http://orig15.deviantart.net/18a0/f/2017/190/1/7/17203bbd420b1366e19ce20b93609b3b-dbfo8ck.png' alt='Logo' style='display:block;width:130px;height:130px;margin:0 auto;'>
<p style='color:#494949;font-weight: 600;font-size: 12px;text-align: center;border-top:1px solid #ebeaea;padding-top:10px;'>Hello <span style='color:yellowgreen'>". $user_name_info_array["user_name"] ."</span>, Here is the code you will need to reset your password:
</p>
<div style='width:50%;height:1px;margin:0 auto;background-color:#ebeaea;'></div>
<div style='font-size:24px;color:white;text-align:center;font-family:arial;font-weight:bold;margin:20px 0;letter-spacing: 2px;cursor: default;'>
<span style='background:-webkit-linear-gradient(36deg, yellow, green);padding:6px 16px'>". $forgot_password_code ."</span>
</div>
</div>
</div>";
$mail->AltBody = "";

if($mail->send()){
$con->exec("update users set last_forgot_password_email_sent = ". time() .", password_reset_code = ". $forgot_password_code ." where id = ". $user_name_info_array["id"]);
$echo_arr[0] = 1;
$echo_arr[1] = "Password Reset Code Sent To Your Email Address"; 
}
else {
$echo_arr[1] = "Something Went Wrong, Sorry";			
}
}
else {
$echo_arr[1] = "Please Try Again In ". date("i:s",(($user_name_info_array["last_forgot_password_email_sent"] + $ONLY_ONE_PASSWORD_RESET_IN_EVERY_X_SECONDS) - time()));	
}
}
else {
$echo_arr[1] = "The Email Address Associated with this account has not been confirmed, You Can Not Reset Your Password, Sorry :(";		
}
}	
else{
$echo_arr[1] = "You Can Not Reset Your Password Because This Account Is Not Linked With Any Email Addresses!";	
}

}
else {
$echo_arr[1] = "Not a Single Account With The Username Or Email Address You Provided Exists On Our App :(";	
}

}




if(isset($_POST["user_name_or_email_address"]) && isset($_POST["reset_code"]) && !isset($_POST["new_password"]) && filter_var($_POST["reset_code"], FILTER_VALIDATE_INT) !== false) {

$check_if_valid_reset_code = $con->prepare("select id from users where password_reset_code = :password_reset_code and (user_name = :user_name_or_email_address or (email_address = :user_name_or_email_address and activated = 'true'))");	
$check_if_valid_reset_code->bindParam(":user_name_or_email_address",$_POST["user_name_or_email_address"]);
$check_if_valid_reset_code->bindParam(":password_reset_code",$_POST["reset_code"]);
$check_if_valid_reset_code->execute();	

$check_if_valid_reset_code_user_id = $check_if_valid_reset_code->fetch()[0];

if($check_if_valid_reset_code_user_id != "") {
$echo_arr[0] = 1;
$echo_arr[1] = "Nice, your code was correct :)";
}
else {
$echo_arr[1] = "Invalid Code :(";		
}
	
}


if(isset($_POST["user_name_or_email_address"]) && isset($_POST["reset_code"]) && isset($_POST["new_password"]) && filter_var($_POST["reset_code"], FILTER_VALIDATE_INT) !== false) {

$check_if_valid_reset_code = $con->prepare("select id from users where password_reset_code = :password_reset_code and (user_name = :user_name_or_email_address or (email_address = :user_name_or_email_address and activated = 'true'))");	
$check_if_valid_reset_code->bindParam(":user_name_or_email_address",$_POST["user_name_or_email_address"]);
$check_if_valid_reset_code->bindParam(":password_reset_code",$_POST["reset_code"]);
$check_if_valid_reset_code->execute();	

$check_if_valid_reset_code_user_id = $check_if_valid_reset_code->fetch()[0];

if($check_if_valid_reset_code_user_id != "") {	
$check_login_password = new ValidateItem($_POST["new_password"],"/^(?=.*[A-Za-z])(?=.*\d)(?=.*([$@$!%*#?& ]*))[A-Za-z\d($@$!%*#?& )*]{8,50}$/i","Password Must Contain At Least 1 Digit And Must Be Between 8-50 Characters, Special Characters And Spaces Are Optional"); 
if($check_login_password->validate() == true) {
$new_password = password_hash($_POST["new_password"],PASSWORD_BCRYPT);
$con->exec("update users set password_reset_code = '', last_forgot_password_email_sent = 0, password = '".$new_password."' where id = ". $check_if_valid_reset_code_user_id);		
$echo_arr[0] = 1;
$echo_arr[1] = "Password Updated Successfully :)";
}
else {
$echo_arr[1] = "Password Must Contain At Least 1 Digit And Must Be Between 8-50 Characters, Special Characters And Spaces Are Optional";	
}
}
}


echo json_encode($echo_arr);


unset($con);




?>