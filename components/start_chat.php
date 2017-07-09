<?php
#we make a call to this page everytime a user wants to chat with someone.

require_once "common_requires.php";

require_once "logged_in_importants.php";

include_once "letter_avatars.php";



function last_online($time) {
	
if($time == 0) {
return $time;	
}
	
$time = intval($time);	
	
$today = new DateTime(); // This object represents current date/time
$today->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$match_date = DateTime::createFromFormat( "Y-m-d H:i", date("Y-m-d H:i",$time));
$match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$diff = $today->diff( $match_date );
$diffDays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

if(time() - $time < 120) {# we say he was online that number of minutes ago.
return "Just Went Offline";
}	
else if($diffDays == 0) {
return "Last Online At " . date("H:i",$time);	
}
else if($diffDays == -1) {
return "Last Online Yesterday At " . date("H:i",$time);	
} 
else if(time() - $time < 604800){
return "Last Online On " . date("l",$time);	
}
else {
return "Last Online On " . date("j, M Y",$time);		
}
}



# the first index in this arr being an empty string ("") has its significance, since otherwise we couldn't append to the first index ($echo_arr[0] .=). 
$echo_arr = [""];	
	

if(!isset($_GET["chat_id"]) && isset($_GET["user_id"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false) {
		
$chat_recipient_id = $_GET["user_id"];
	
# if this is the first time the users are chatting, then add a new row to the chats table (the table mainly associated with the chatPortals).
$start_date = date("Y/m/d H:i");	
$chatter_ids = $_SESSION["user_id"] . "-" . $_GET["user_id"]; 
$latest_activity = time();

$chat_arr = $con->query("select * from chats where chatter_ids = '".$_SESSION["user_id"]."-".$_GET["user_id"]."' or chatter_ids = '".$_GET["user_id"]."-".$_SESSION["user_id"]."'")->fetch();
$chat_id = $chat_arr["id"];
# if this is the first time the users are chatting, then add a new row to the chats table (the table mainly associated with the chatPortals).
if($chat_id == "") {
$chat_prepare = $con->prepare("insert into chats (start_date,chatter_ids,latest_activity) values(:start_date,:chatter_ids,:latest_activity);");
$chat_prepare->bindParam(":start_date",$start_date);
$chat_prepare->bindParam(":chatter_ids",$chatter_ids);
$chat_prepare->bindParam(":latest_activity",$latest_activity);
$chat_prepare->execute();
$chat_id = $con->lastInsertId();
}

$chat_recipient_info_arr = $con->query("select * from users where id = ".$_GET["user_id"])->fetch();
}
// if the users are continuing a previous chat.
else if(isset($_GET["chat_id"]) && filter_var($_GET["chat_id"], FILTER_VALIDATE_INT) !== false){
$chat_id = $_GET["chat_id"];	

$chat_arr = $con->query("select * from chats where id = ". $chat_id)->fetch();	
$chat_recipient_id = explode("-",$chat_arr["chatter_ids"])[0] == $_SESSION["user_id"] ? explode("-",$chat_arr["chatter_ids"])[1] :  explode("-",$chat_arr["chatter_ids"])[0];	
	
$chat_recipient_info_arr = $con->query("select * from users where id = ".$chat_recipient_id)->fetch();
}


// unhide the chat if the chat is hidden. (we don't do this all the time, only when the user is opening the chat by clicking the startChat button in the recipient's user modal.)
if($_GET["unhide_chat_if_hidden"] == "true") {
$con->exec("delete from hidden_chats where chat_id = ". $chat_id ." and user_id = ". $_SESSION["user_id"]);		
}


if(count($chat_recipient_info_arr) > 0) {
	
$current_status	= "Online";

$rawStr =  read_shm($chat_recipient_info_arr["id"] . "" . 2);

// $rawStr2 == 1 means the user is currently logged out, we know this because he logged out by pressing the log out button, and $rawStr2 == 0 means he is logged in because he pressed the login button.
$rawStr2 =  read_shm($chat_recipient_info_arr["id"] . "" . 3);


$rawStr3 =  read_shm($chat_recipient_info_arr["id"] . "" . 4);


if($rawStr2 == "1")  {
$current_status = last_online($rawStr);
}
if(time() - $rawStr > 4 && $rawStr2 != "1")  {
$current_status = last_online($rawStr);
write_shm($chat_recipient_info_arr["id"] . "" . 4,"none");
}
if(time() - $rawStr < 5 && $rawStr2 != "1" && $chat_id != $rawStr3) {
$current_status = "Online";
}
if(time() - $rawStr < 5 && $rawStr2 != "1" && $chat_id == $rawStr3) {
$current_status = "Here";
}


$messages_query =  $con->query("select id,message_from,message,read_yet,date_of,message_type from messages where chat_id = ". $chat_id ." order by id desc limit 15");

$messager_arr = $con->query("select id,first_name,last_name,avatar_picture from  users where id = ". $chat_recipient_id)->fetch();
$messager_avatar_arr = $con->query("SELECT positions,rotate_degree FROM avatars WHERE id_of_user = ". $messager_arr["id"] ." order by id desc limit 1")->fetch();
$messager_avatar_arr_positions = explode(",",$messager_avatar_arr["positions"]);

$sent_message_last = "";

$messages_all = array_reverse($messages_query->fetchAll());

for($x = 0;$x < count($messages_all);$x++) {	

$message_raw = openssl_decrypt($messages_all[$x]["message"],"aes-128-cbc","georgedies",OPENSSL_RAW_DATA,"dancewithdragons");

//if this message is a sent by this user to someone else, then set this variable to true, else set it to false.
$sent_message = ($messages_all[$x]["message_from"] == $_SESSION["user_id"] ? true : false);		
		
$uniq_id = rand(10000000,100000000);
	
$message_uniq_id = rand(100000000,100000000000);			
	
if($messages_all[$x]["message_type"] == "text-message") {
$echo_arr[0] .= "<div class='messageContainer message". ($sent_message == true ? "0" : "1") ."' id='message".$message_uniq_id."'>
". ($sent_message == false && $sent_message_last !== $sent_message ? "
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
<div class='messageDate'>
- ". date("H:i",strtotime($messages_all[$x]["date_of"])) ."
</div>
</div>
</div><!-- end messageContainer -->
<script>

	$('#".$uniq_id."').on('load',function(){
		$(this).parent().css('transform','rotate(' + ". ($messager_avatar_arr["rotate_degree"] != "" ? $messager_avatar_arr["rotate_degree"] : 0) ." + 'deg)');
		fitToParent($(this));
		adaptRotateWithMargin($(this),". ($messager_avatar_arr["rotate_degree"] != "" ? $messager_avatar_arr["rotate_degree"] : 0) .",false);
	});
	
	/*
	if(parseFloat($('#chatModal').css('z-index')) == currentZindexStack) {
	setTimeout(function(){
		if(!$('#message".$message_uniq_id."').hasClass('message0')) {
		fadeItOut('#message".$message_uniq_id."');
		}
		},10000);
	}
	else { 
	setTimeout(function(){
		if(!$('#message".$message_uniq_id."').hasClass('message0')) {
		hideMessage('message".$message_uniq_id."');
		}
	},10000);
	}
	*/
</script>";
}
else if($messages_all[$x]["message_type"] == "emoji-message") {	
$echo_arr[0] .= "<div class='messageContainer emojiMessageContainer message". ($sent_message == true ? "0" : "1") ."'  id='message".$message_uniq_id."'>
". ($sent_message == false  && $sent_message_last !==  $sent_message ? "
<div class='chatRecipientAvatar showUserModal modal-trigger' data-target='modal1' data-user-id='".$messager_arr["id"]."'>
". ($messager_arr["avatar_picture"] == "" ? letter_avatarize($messager_arr["first_name"],"small") : "
<div class='rotateContainer' style='transform:none;display:inline-block;width:100%;height:100%;margin-top:".$messager_avatar_arr_positions[0]."%;margin-left:".$messager_avatar_arr_positions[1]."%;'>
<div class='userAvatarRotateDiv'>
<img  id='".$uniq_id."' src='".$messager_arr["avatar_picture"]."' alt='Avatar Picture' style='position:absolute;'/>
</div>
</div>
") ."</div>" : "") ."
<div class='message emojiMessage ". ($sent_message == false && $messages_all[$x]["read_yet"] == false ? "unreadEmoji" : "") ."'>
<img src='".$message_raw."' alt='Emoji'/>
</div>
</div><!-- end messageContainer -->
<script>

	$('#".$uniq_id."').on('load',function(){
		$(this).parent().css('transform','rotate(' + ". ($messager_avatar_arr["rotate_degree"] != "" ? $messager_avatar_arr["rotate_degree"] : 0) ." + 'deg)');
		fitToParent($(this));
		adaptRotateWithMargin($(this),". ($messager_avatar_arr["rotate_degree"] != "" ? $messager_avatar_arr["rotate_degree"] : 0) .",false);
	});
	
	/*
	
	if(parseFloat($('#chatModal').css('z-index')) == currentZindexStack) {
	setTimeout(function(){
		if(!$('#message".$message_uniq_id."').hasClass('message0')) {
		fadeItOut('#message".$message_uniq_id."');
		}
		},10000);
	}
	else { 
	setTimeout(function(){
		if(!$('#message".$message_uniq_id."').hasClass('message0')) {
		hideMessage('message".$message_uniq_id."');
		}
	},10000);
	}*/
	
</script>";
}
else if($messages_all[$x]["message_type"] == "file-message") {

$file_uniq_id = rand(10000000,100000000);

$file_arr = $con->query("select * from sent_files where id = ". intval($messages_all[$x]["message"]))->fetch();

$echo_arr[0] .= "<div class='messageContainer imageMessageContainer message". ($sent_message == true ? "0" : "1") ."' id='message".$message_uniq_id."' data-message-id='".$messages_all[$x]["id"]."'>" . 
"<div class='chatRecipientAvatar showUserModal modal-trigger' data-target='modal1' data-user-id='".$messager_arr["id"]."'>
". ($messager_arr["avatar_picture"] == "" ? letter_avatarize($messager_arr["first_name"],"small") : "
<div class='rotateContainer' style='transform:none;display:inline-block;width:100%;height:100%;margin-top:".$messager_avatar_arr_positions[0]."%;margin-left:".$messager_avatar_arr_positions[1]."%;'>
<div class='userAvatarRotateDiv'>
<img  id='".$uniq_id."' src='".$messager_arr["avatar_picture"]."' alt='Avatar Picture' style='position:absolute;'/>
</div>
</div>
") ."</div>
<div class='fileMessageContainer'><img id='file".$file_uniq_id."' src='".$file_arr["path"]."' alt='File' /></div>
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


if($current_status == "Online") {
$data_current_status = 1;
}
else if($current_status == "Here") {
$data_current_status = 2;
}
else {
$data_current_status = 0;	
}

#set all messages's read_yet to true
$con->exec("update messages set read_yet = true where chat_id = ". $chat_id." and message_from != ".$_SESSION["user_id"]);


array_push($echo_arr,"<a id='sendMessage' class='btn-floating waves-effect wavesCustom myBackground' data-file-or-send='0' data-chat-id='".$chat_id."'><i class='material-icons' style='font-size:160%'>camera_alt</i></a>");

array_push($echo_arr,$chat_recipient_info_arr["first_name"] . "<br><span id='currentStatus' data-current-status='".$data_current_status."' class='modalHeaderFullNameSecondary'>". $current_status ."</span>");


write_shm($_SESSION["user_id"] . "" . 4,$chat_id);

echo json_encode($echo_arr);
}



?>