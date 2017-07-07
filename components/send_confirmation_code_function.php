<?php



function send_confirmation_code($user_id, $email_address, $user_name) {
global $con;

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
$mail->addAddress($email_address, 'User');     // Add a recipient
$mail->addReplyTo('fantasybl8@gmail.com', '');
$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = $user_name;
$mail->Body = "<div style='width: 260px;height: 350px;margin:20px auto 20px auto;overflow:hidden;font-family:arial;border-radius: 0 0 5px 5px;box-sizing: border-box;border:1px solid lightgrey;border-top:none;'>
<div style='display:block !important;width:100%;height:5px;background:-webkit-linear-gradient(0, yellow, green);margin:0;'></div>
<div style='width:100%;height:100%;padding: 20px 25px 10px 25px;box-sizing:border-box;'>
<img src='http://orig14.deviantart.net/e15b/f/2017/185/7/6/logo_by_torostorocrcs-dbf4t3n.png' alt='Logo' style='display:block;width:100px;height:100px;margin:0 auto;'>
<p style='color:#494949;font-weight: 600;font-size: 12px;text-align: center;border-top:1px solid #ebeaea;padding-top:10px;'>Hello <span style='color:yellowgreen'>". $user_name ."</span>, You Recently Requested To Link This Email To Your Account.
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
if($con->query("update users set activated = '". $activation_code ."' where id = ". $user_id)) {
return true;
}
}

return false;	
}




?>