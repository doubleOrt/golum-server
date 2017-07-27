<?php
# this page is used to show the chat portals (the parts the users click to open chat modals).

require_once "common_requires.php";
require_once "logged_in_importants.php";



$echo_arr = [[]];
	
if(isset($_SESSION["user_id"]) && isset($_GET["row_offset"]) && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false) {

$chat_portals_arr_prepared = $con->prepare("select * from (select *, case SUBSTRING_INDEX(chatter_ids, '-', 1) when :base_user_id then SUBSTRING_INDEX(chatter_ids, '-', -1) else SUBSTRING_INDEX(chatter_ids, '-', 1) end as chat_recipient from chats where chatter_ids like concat('%', :base_user_id, '%')) t1 where chat_recipient not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and chat_recipient not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and chat_recipient not in (SELECT user_id from account_states) order by latest_activity desc limit 15 offset :row_offset");
$chat_portals_arr_prepared->bindParam(":base_user_id", $_SESSION["user_id"]);
$chat_portals_arr_prepared->bindValue(":row_offset", (int) $_GET["row_offset"], PDO::PARAM_INT);
$chat_portals_arr_prepared->execute();
$chat_portals_arr = $chat_portals_arr_prepared->fetchAll();

$hidden_chats_arr_prepared = $con->prepare("select * from hidden_chats where user_id = :base_user_id");
$hidden_chats_arr_prepared->execute([":base_user_id" => $_SESSION["user_id"]]);
$hidden_chats_arr = $hidden_chats_arr_prepared->fetchAll();

foreach($chat_portals_arr as $row) {
		
for($i = 0;$i<count($hidden_chats_arr);$i++) {
if($hidden_chats_arr[$i]["chat_id"] == $row["id"]) {
continue 2;	
}	
}	

$chat_portal_user_info_arr = custom_pdo("select * from users where id = :user_id", [":user_id" => $row["chat_recipient"]])->fetch();
$chat_portal_avatar_arr = custom_pdo("SELECT * FROM avatars WHERE id_of_user = :user_id order by id desc limit 1", [":user_id" => $row["chat_recipient"]])->fetch();	
$chat_portal_avatar_positions = explode(",",$chat_portal_avatar_arr["positions"]);	

if($chat_portal_avatar_arr[0] != "") {
$avatar_rotate_degree = $chat_portal_avatar_arr["rotate_degree"];
$avatar_positions = explode(",",htmlspecialchars($chat_portal_avatar_arr["positions"], ENT_QUOTES, "utf-8"));
}
else {
$avatar_rotate_degree = 0;
$avatar_positions = [0,0];	
}


array_push($echo_arr[0], [
"id" => htmlspecialchars($row["id"], ENT_QUOTES, "utf-8"),
"recipient_info" => [
"id" => htmlspecialchars($row["chat_recipient"], ENT_QUOTES, "utf-8"),
"first_name" => htmlspecialchars($chat_portal_user_info_arr["first_name"], ENT_QUOTES, "utf-8"),
"last_name" => htmlspecialchars($chat_portal_user_info_arr["last_name"], ENT_QUOTES, "utf-8"),
"avatar" => ($chat_portal_user_info_arr["avatar_picture"] != "" ? $SERVER_URL . htmlspecialchars($chat_portal_user_info_arr["avatar_picture"], ENT_QUOTES, "utf-8") : ""),
"avatar_rotate_degree" => htmlspecialchars($avatar_rotate_degree, ENT_QUOTES, "utf-8"),
"avatar_positions" => $avatar_positions
]
]);
}

}



echo json_encode($echo_arr);

unset($con);



?>