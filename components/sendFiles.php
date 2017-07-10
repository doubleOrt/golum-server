<?php
#this page receives an ajax call whenever a user wants to send a file in chat.

require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_FILES["the_file"]) && isset($_POST["chat_id"]) && filter_var($_POST["chat_id"], FILTER_VALIDATE_INT) !== false) {

//this is the path of the image after upload and before renaming the file
$upload_to = "../users/" . $_SESSION["user_id"] . "/sentFiles/";

//the extension of the uploaded file 
$upload_pathinfo = strtolower(pathinfo($upload_to . basename($_FILES["the_file"]["name"]),PATHINFO_EXTENSION));

//check if file is smaller than 5mb
if($_FILES["the_file"]["size"] < 5000000) {
//check if file is a jpg, png, or gif.
if($upload_pathinfo == "jpeg" || $upload_pathinfo == "jpg" || $upload_pathinfo == "png" || $upload_pathinfo == "gif") {
	
//move the uploaded file
if(move_uploaded_file($_FILES["the_file"]["tmp_name"],$upload_to . basename($_FILES["the_file"]["name"]))) {

//what is going to be the id of the new avatar picture ? we get this by getting the id of the last row and adding 1 to it.
$what_id_query = $con->query("SELECT count(id) FROM sent_files where id_of_user = ". $_SESSION["user_id"])->fetch();

$what_id = $what_id_query[0] > 0 ? $what_id_query[0] + 1 : 1;

//this is the new path to the avatar.
$new_path = "users/". $_SESSION["user_id"] ."/sentFiles/" . "$what_id" . "." . $upload_pathinfo;

//rename the file and check if it is successful
if(rename($upload_to . basename($_FILES["the_file"]["name"]) , "../" . $new_path)) {

//add a new row to the sent_files table, check if it is successful.
$insert_into_sent_files = $con->query("INSERT INTO sent_files (id_of_user,path,date_of) values('". $_SESSION["user_id"] ."','". $new_path ."','".date("Y/m/d H:i")."')");

// insert a new row into the messages table, note that we insert the id of the sent file (from the sent_files table) into the "message" column instead of an actual message.
$insert_into_messages = $con->query("INSERT INTO messages (chat_id,message_from,message,date_of,message_type) values(".$_POST["chat_id"].",". $_SESSION["user_id"] .",'".$con->lastInsertId()."','".date("Y/m/d H:i")."','file-message')");

$message_id = $con->lastInsertId();

//if query was successful
if($insert_into_sent_files->rowCount() > 0 && $insert_into_messages->rowCount() > 0) {

$con->exec("update chats set latest_activity = ".time()." where id = ". $_POST["chat_id"]);

$chat_id_arr = $con->query("select * from chats where id = ". $_POST["chat_id"])->fetch();	
$chatter_ids_arr = explode("-",$chat_id_arr["chatter_ids"]);

$recipient_is_in_this_chat_modal = false;	

//update users so they reveive the message immediately.
for($i = 0;$i<count($chatter_ids_arr);$i++) {
if($chatter_ids_arr[$i] == $_SESSION["user_id"]) {
continue;	
}

$shmop_id = $chatter_ids_arr[$i] . "" . 4;
$shmop = shmop_open($shmop_id,"c",0777,1024);
$shmop_val = shmop_read($shmop, 0, shmop_size($shmop));
$rawStr =  str_from_mem($shmop_val);
if($rawStr != "none") {
$recipient_is_in_this_chat_modal = true;	
}
else {
$recipient_is_in_this_chat_modal = false;	
}
shmop_close($shmop);

$shmid = $chatter_ids_arr[$i] . "" . 1; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);
}

$file_uniq_id = rand(10000000,100000000);

$message_uniq_id = rand(1000000,10000000);



echo "<div class='messageContainer imageMessageContainer message0' id='message".$message_uniq_id."' data-message-id='". htmlspecialchars($message_id, ENT_QUOTES, "utf-8") ."'><div class='fileMessageContainer'><img id='file". $file_uniq_id."' src='". htmlspecialchars($new_path, ENT_QUOTES, "utf-8") ."' alt='File'/></div></div>
<script>

	$('#file".$file_uniq_id."').on('load',function(){
		fitToParent($(this));
	});
	
</script>";
}
else {
echo "Materialize.toast('Something Went Wrong, Sorry!',6000,'red')";
die();		
}
}
else {
echo "Materialize.toast('Something Went Wrong, Sorry!',6000,'red')";
die();			
}
}
else {
echo "Materialize.toast('Something Went Wrong, Sorry!',6000,'red')";
die();	
}
}
else {
echo "Materialize.toast('Image Type Must Be Either \"JPEG\", \"JPG\" \"PNG\" Or \"GIF\" !',6000,'red')";
die();
}
}
else {
echo "Materialize.toast('Image Size Must Be Smaller Than 5MB',6000,'red')";
die();	
}
}


?>