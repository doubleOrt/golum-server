<?php
//we make a call to this page whenever the user opens the sidebar to tell them the number of new messages they got.

require_once "common_requires.php";
require_once "logged_in_importants.php";

$echo_arr = [[]];

$user_chats = $con->query("select id, chatter_ids from chats where chatter_ids like '%". $_SESSION["user_id"] ."%'")->fetchAll();

$this_user_related_blocks = $con->query("select id, user_ids from blocked_users where user_ids like '%-" . $_SESSION["user_id"]."'")->fetchAll();	

$chat_ids_string = "";
for($i = 0;$i<count($user_chats);$i++) {
	
	
# we use this to parse the chat recipient's id.
$chat_portal_user_id = explode("-", $user_chats[$i]["chatter_ids"])[0] == $_SESSION["user_id"] ? explode("-", $user_chats[$i]["chatter_ids"])[1] : explode("-", $user_chats[$i]["chatter_ids"])[0];

if($con->query("SELECT * FROM account_states where user_id = ". $chat_portal_user_id)->fetch()[0] != "") {
continue;	
}	

for($x = 0; $x < count($this_user_related_blocks); $x++) {
if(explode("-", $this_user_related_blocks[$x]["user_ids"])[0] == $chat_portal_user_id) {
continue 2;
}
}

if($i != 0) {
$chat_ids_string .= " or ";	
}	
$chat_ids_string .= " chat_id = ". $user_chats[$i]["id"];	
}

if($chat_ids_string != "") {
array_push($echo_arr[0], $con->query("select count(id) from messages where (". $chat_ids_string .") and message_from != ". $_SESSION["user_id"] ." and read_yet = false")->fetch()[0]
);
}

echo json_encode($echo_arr);



?>