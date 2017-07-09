<?php
/* we make an ajax call to this page whenever a user has unread messages */

require_once "common_requires.php";
include_once "letter_avatars.php";




if(isset($_GET["chat_id"]) && filter_var($_GET["chat_id"], FILTER_VALIDATE_INT) !== false) {

$chat_id = $_GET["chat_id"];
	
$user_arr = $con->query("select * from users where id = ". $_SESSION["user_id"])->fetch();

$chat_arr = $con->query("select * from chats where id = ". $_GET["chat_id"])->fetch();

$chat_recipient_id = explode("-",$chat_arr["chatter_ids"])[0] == $_SESSION["user_id"] ? explode("-",$chat_arr["chatter_ids"])[1] :  explode("-",$chat_arr["chatter_ids"])[0];	

$messages_query =  $con->query("select id,message_from,message,read_yet,date_of,message_type from messages where chat_id = ".$chat_id." and message_from != ".$_SESSION["user_id"]." order by id desc limit 15");

$messager_arr = $con->query("select id,first_name,last_name,avatar_picture from  users where id = ". $chat_recipient_id)->fetch();
$messager_avatar_arr = $con->query("SELECT positions,rotate_degree FROM avatars WHERE id_of_user = ". $messager_arr["id"] ." order by id desc limit 1")->fetch();
$messager_avatar_arr_positions = explode(",",$messager_avatar_arr["positions"]);


$messages_all = array_reverse($messages_query->fetchAll());

$sent_message_last = ($_GET["last_message"] == "true" ? true : false);

for($x = 0;$x < count($messages_all);$x++) {

$message_raw = openssl_decrypt($messages_all[$x]["message"],"aes-128-cbc","georgedies",OPENSSL_RAW_DATA,"dancewithdragons");

//if this message is a sent by this user to someone else, then set this variable to true, else set it to false.
$sent_message = ($messages_all[$x]["message_from"] == $_SESSION["user_id"] ? true : false);		
		
$uniq_id = rand(10000000,100000000);
		
$message_uniq_id = rand(100000000,100000000000);		
		
if($messages_all[$x]["message_type"] == "text-message") {
echo "<div class='messageContainer message". ($sent_message == true ? "0" : "1") ."'  id='message".$message_uniq_id."'>
". ($sent_message == false  && $sent_message_last !==  $sent_message ? "
<div class='chatRecipientAvatar showUserModal modal-trigger' data-target='modal1' data-user-id='".$messager_arr["id"]."'>
". ($messager_arr["avatar_picture"] == "" ? letter_avatarize($messager_arr["first_name"],"small") : "
<div class='rotateContainer' style='transform:none;display:inline-block;width:100%;height:100%;margin-top:".$messager_avatar_arr_positions[0]."%;margin-left:".$messager_avatar_arr_positions[1]."%;'>
<div class='userAvatarRotateDiv'>
<img id='".$uniq_id."' class='searchResultAvatar' src='".$messager_arr["avatar_picture"]."' alt='Avatar Picture' style='position:absolute;'/>
</div>
</div>
") ."</div>" : "") ."
<div class='message'>
". htmlspecialchars($message_raw) ."
</div>
</div><!-- end messageContainer -->
<script>

	$('#".$uniq_id."').on('load',function(){
		$(this).parent().css('transform','rotate(' + ". ($messager_avatar_arr["rotate_degree"] != "" ? $messager_avatar_arr["rotate_degree"] : 0) ." + 'deg)');
		fitToParent($(this));
		adaptRotateWithMargin($(this),". ($messager_avatar_arr["rotate_degree"] != "" ? $messager_avatar_arr["rotate_degree"] : 0) .",false);
	});
	
</script>";
}
else if($messages_all[$x]["message_type"] == "emoji-message") {	
echo "<div class='messageContainer emojiMessageContainer message". ($sent_message == true ? "0" : "1") ."' id='message".$message_uniq_id."'>
". ($sent_message == false  && $sent_message_last !==  $sent_message ? "
<div class='chatRecipientAvatar showUserModal modal-trigger' data-target='modal1' data-user-id='".$messager_arr["id"]."'>
". ($messager_arr["avatar_picture"] == "" ? letter_avatarize($messager_arr["first_name"],"small") : "
<div class='rotateContainer' style='transform:none;display:inline-block;width:100%;height:100%;margin-top:".$messager_avatar_arr_positions[0]."%;margin-left:".$messager_avatar_arr_positions[1]."%;'>
<div class='userAvatarRotateDiv'>
<img id='".$uniq_id."' src='".$messager_arr["avatar_picture"]."' alt='Avatar Picture' style='position:absolute;'/>
</div>
</div>
") ."</div>" : "") ."
<div class='message emojiMessage unreadEmoji'>
<img src='".$message_raw."' alt='Emoji'/>
</div>
</div><!-- end messageContainer -->
<script>

	$('#".$uniq_id."').on('load',function(){
		$(this).parent().css('transform','rotate(' + ". ($messager_avatar_arr["rotate_degree"] != "" ? $messager_avatar_arr["rotate_degree"] : 0) ." + 'deg)');
		fitToParent($(this));
		adaptRotateWithMargin($(this),". ($messager_avatar_arr["rotate_degree"] != "" ? $messager_avatar_arr["rotate_degree"] : 0) .",false);
	});
	
	
</script>";
}
else if($messages_all[$x]["message_type"] == "file-message") {

$file_uniq_id = rand(10000000,100000000);

$file_arr = $con->query("select * from sent_files where id = ". intval($messages_all[$x]["message"]))->fetch();

echo "<div class='messageContainer imageMessageContainer message". ($sent_message == true ? "0" : "1") ."' id='message".$message_uniq_id."' data-message-id='".$messages_all[$x]["id"]."'>" . 
"<div class='chatRecipientAvatar showUserModal modal-trigger' data-target='modal1' data-user-id='".$messager_arr["id"]."'>
". ($messager_arr["avatar_picture"] == "" ? letter_avatarize($messager_arr["first_name"],"small") : "
<div class='rotateContainer' style='transform:none;display:inline-block;width:100%;height:100%;margin-top:".$messager_avatar_arr_positions[0]."%;margin-left:".$messager_avatar_arr_positions[1]."%;'>
<div class='userAvatarRotateDiv'>
<img  id='".$uniq_id."' src='".$messager_arr["avatar_picture"]."' alt='Avatar Picture' style='position:absolute;'/>
</div>
</div>
") ."</div>
<div class='fileMessageContainer'><img id='file".$file_uniq_id."' src='".$file_arr["path"]."' alt='File'/></div>
</div>
<script>
	
	$('#".$uniq_id."').on('load',function(){
		$(this).parent().css('transform','rotate(' + ". ($messager_avatar_arr["rotate_degree"] != "" ? $messager_avatar_arr["rotate_degree"] : 0) ." + 'deg)');
		fitToParent($(this));
		adaptRotateWithMargin($(this),". ($messager_avatar_arr["rotate_degree"] != "" ? $messager_avatar_arr["rotate_degree"] : 0) .",false);
	});
	
</script>";	
}


$sent_message_last = $sent_message;
}


#set all messages' "read_yet" columns to true
$con->exec("update messages set read_yet = true = true where chat_id = ". $chat_id." and message_from != ".$_SESSION["user_id"]);


write_shm($_SESSION["user_id"] . "" . 1,"false");
}



?>