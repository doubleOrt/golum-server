<?php
#we make an ajax call to this page whenever a user wants to send a message

require_once "common_requires.php";


# if the post variables are set.
if(isset($_POST["message"]) && isset($_POST["chat_id"]) && filter_var($_POST["chat_id"], FILTER_VALIDATE_INT) !== false) {

$chat_id = intval($_POST["chat_id"]);
// we are not using this directly because you can't pass valued to pdo prepare directly.
$from = intval($_SESSION["user_id"]);	
$date_of = date("Y/m/d H:i");
$read_yet = false;
$message = openssl_encrypt($_POST["message"],"aes-128-cbc","georgedies",OPENSSL_RAW_DATA,"dancewithdragons");
$message_type = $_POST["type"];

# prepare the insert statement.
$prepare = $con->prepare("insert into messages(chat_id,message_from,message,date_of,read_yet,message_type) values(:chat_id,:from,:message_from,:date_of,:read_yet,:message_type)");
$prepare->bindValue(":chat_id",$chat_id, PDO::PARAM_INT);
$prepare->bindValue(":from",$from, PDO::PARAM_INT);
$prepare->bindParam(":message_from",$message);
$prepare->bindParam(":date_of",$date_of);
$prepare->bindParam(":read_yet",$read_yet);
$prepare->bindParam(":message_type",$message_type);

# check if the query executes without any errors, if so, echo out some js that will empty the send message textarea element, append the message to the sender's html, and scroll to the bottom of the chatWindowChild div.
if($prepare->execute()) {
	
$recipient_is_in_this_chat_modal = false;	
	
$con->exec("update chats set latest_activity = ".time()." where id = ".$chat_id);	
# takes care of updating the new messages field of the users table for all recipients.
$chat_id_arr = $con->query("select * from chats where id = ".$chat_id)->fetch();	
$chatter_ids_arr = explode("-",$chat_id_arr["chatter_ids"]);
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

if($_POST["type"] == "text-message") {
$message = "<div class='message'>" .htmlspecialchars($_POST["message"]) . "</div>";	
}
else if($_POST["type"] == "emoji-message") {
$message = "<div class='message emojiMessage'><img src='".$_POST["message"]."' alt='Emoji'/></div>";	
}



$message_uniq_id = rand(1000000,10000000);

echo "<div class='messageContainer message0' id='message".$message_uniq_id."'>".$message."</div>
". ($recipient_is_in_this_chat_modal == true ? "<script>

/*
if(parseFloat($('#chatModal').css('z-index')) == currentZindexStack) {
setTimeout(function(){fadeItOut('#message".$message_uniq_id."');},10000);
}
else { 
setTimeout(function(){hideMessage('message".$message_uniq_id."');},10000);
}
*/

</script>" : "");	
}
}



?>