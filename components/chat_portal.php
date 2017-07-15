<?php
# this page is used to show the chat portals (the parts the users click to open chat modals).

require_once "common_requires.php";
require_once "logged_in_importants.php";



$echo_arr = [[]];
	
if(isset($_SESSION["user_id"]) && isset($_GET["row_offset"]) && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false) {

$chat_portals_arr = $con->query("select * from chats where chatter_ids like '%".$_SESSION["user_id"]."%' order by latest_activity desc limit 15 OFFSET ". $_GET["row_offset"])->fetchAll();

$hidden_chats_arr = $con->query("select * from hidden_chats where user_id = ". $_SESSION["user_id"])->fetchAll();

foreach($chat_portals_arr as $row) {
		
for($i = 0;$i<count($hidden_chats_arr);$i++) {
if($hidden_chats_arr[$i]["chat_id"] == $row["id"]) {
continue 2;	
}	
}	

# we use this to parse the chat recipient's id.
$chat_portal_user_id = explode("-",$row["chatter_ids"])[0] == $_SESSION["user_id"] ? explode("-",$row["chatter_ids"])[1] : explode("-",$row["chatter_ids"])[0];

$current_state = $con->query("select id from blocked_users where user_ids = '".$chat_portal_user_id. "-" . $_SESSION["user_id"]."'")->fetch();	
if($current_state[0] != "") {
continue;	
}
if($con->query("SELECT * FROM account_states where user_id = ". $chat_portal_user_id)->fetch()[0] != "") {
continue;	
}	


$chat_portal_user_info_arr = $con->query("select * from users where id = ".$chat_portal_user_id)->fetch();
$chat_portal_avatar_arr = $con->query("SELECT * FROM avatars WHERE id_of_user = ".$chat_portal_user_id." order by id desc limit 1")->fetch();	
$chat_portal_avatar_positions = explode(",",$chat_portal_avatar_arr["positions"]);	


if($chat_portal_avatar_arr[0] != "") {
$avatar_rotate_degree = $chat_portal_avatar_arr["rotate_degree"];
$avatar_positions = explode(",",$chat_portal_avatar_arr["positions"]);
}
else {
$avatar_rotate_degree = 0;
$avatar_positions = [0,0];	
}



array_push($echo_arr[0], [
"id" => htmlspecialchars($row["id"], ENT_QUOTES, "utf-8"),
"recipient_info" => [
"id" => $chat_portal_user_id,
"first_name" => $chat_portal_user_info_arr["first_name"],
"last_name" => $chat_portal_user_info_arr["last_name"],
"avatar" => $chat_portal_user_info_arr["avatar_picture"],
"avatar_rotate_degree" => $avatar_rotate_degree,
"avatar_positions" => $avatar_positions
]
]);
}

}



echo json_encode($echo_arr);

unset($con);



?>