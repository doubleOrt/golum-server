<?php
//we make a call to this page whenever the user opens the notificatons modal.

require_once "common_requires.php";
require_once "logged_in_importants.php";

$echo_arr = [];

if(isset($_GET["row_offset"]) && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false && isset($_GET["type"]) && filter_var($_GET["type"], FILTER_VALIDATE_INT) !== false) {


if($_GET["type"] === "0") {
$notifications_arr_prepared = $con->prepare("select * from (select *, (count(*) - 1) as and_others from (select * from notifications) t1 where notification_to = :base_user_id and read_yet = 0 group by type, extra, read_yet) t3 where notification_from not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and notification_from not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and notification_from not in (SELECT user_id from account_states) order by id desc limit 15 OFFSET ". $_GET["row_offset"]);
$notifications_arr_prepared->execute([":base_user_id" => $_SESSION["user_id"]]);
$notifications_arr = $notifications_arr_prepared->fetchAll();	
}
else if($_GET["type"] === "1") {
$notifications_arr_prepared = $con->prepare("select * from (select *, (count(*) - 1) as and_others from (select * from notifications) t1 where notification_to = :base_user_id group by type, extra, read_yet) t3 where notification_from not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and notification_from not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and notification_from not in (SELECT user_id from account_states) order by id desc limit 15 OFFSET ". $_GET["row_offset"]);
$notifications_arr_prepared->execute([":base_user_id" => $_SESSION["user_id"]]);
$notifications_arr = $notifications_arr_prepared->fetchAll();		
}

if(count($notifications_arr) > 0) {
	
// update read-yets.
$notification_where = "";
for($i = 0;$i < count($notifications_arr);$i++) {
if($i != 0) {
$notification_where .= " or ";
}
$notification_where .= "(type = ". $notifications_arr[$i]["type"] ." and extra = ". $notifications_arr[$i]["extra"] .")";
}

custom_pdo("update notifications set read_yet = :time where read_yet = 0 and notification_to = :base_user_id and (". $notification_where .")", [":time" => time(), ":base_user_id" => $_SESSION["user_id"]]);

for($i = 0;$i<count($notifications_arr);$i++) {
$notification = $notifications_arr[$i];

$sender_arr = custom_pdo("select first_name, last_name, avatar_picture from users where id = :notification_from", [":notification_from" => $notification["notification_from"]])->fetch();
$sender_avatar_arr = custom_pdo("SELECT positions, rotate_degree FROM avatars WHERE id_of_user = :notification_from order by id desc limit 1", [":notification_from" => $notification["notification_from"]])->fetch();
$sender_avatar_positions = explode(",",htmlspecialchars($sender_avatar_arr["positions"], ENT_QUOTES, "utf-8"));
//if avatar positions does not exist 
if(count($sender_avatar_positions) < 2) {
$sender_avatar_positions = [0,0];
}

array_push($echo_arr, [
"notification_id" => htmlspecialchars($notification["id"], ENT_QUOTES, "utf-8"),
"notification_time" => htmlspecialchars($notification["time"], ENT_QUOTES, "utf-8"), 
"notification_time_string" => time_to_string($notification["time"]),
"notification_type" => htmlspecialchars($notification["type"], ENT_QUOTES, "utf-8"), 
"notification_extra" => htmlspecialchars($notification["extra"], ENT_QUOTES, "utf-8"), 
"notification_extra2" => htmlspecialchars($notification["extra2"], ENT_QUOTES, "utf-8"), 
"notification_extra3" => htmlspecialchars($notification["extra3"], ENT_QUOTES, "utf-8"), 
"notification_read_yet" => htmlspecialchars($notification["read_yet"], ENT_QUOTES, "utf-8"), 
"notification_and_others" => htmlspecialchars($notification["and_others"], ENT_QUOTES, "utf-8"), 
"notification_sender_info" => [
	"id" => htmlspecialchars($notification["notification_from"], ENT_QUOTES, "utf-8"), 
	"first_name" => htmlspecialchars($sender_arr["first_name"], ENT_QUOTES, "utf-8"),
	"last_name" => htmlspecialchars($sender_arr["last_name"], ENT_QUOTES, "utf-8"),
	"avatar" => htmlspecialchars($sender_arr["avatar_picture"], ENT_QUOTES, "utf-8"),
	"avatar_positions" => $sender_avatar_positions,
	"avatar_rotate_degree" => htmlspecialchars($sender_avatar_arr["rotate_degree"], ENT_QUOTES, "utf-8")
	] 
]);

} 
}

}

echo json_encode($echo_arr);



?>