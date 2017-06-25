<?php
//we make a call to this page whenever the user opens the notificatons modal.

require_once "common_requires.php";
require_once "logged_in_importants.php";

$echo_arr = [];

if(isset($_GET["row_offset"]) && is_integer(intval($_GET["row_offset"]))) {

// when the user wants to see the first 10 notifs	
if($_GET["row_offset"] == 0) {
$notifications_arr = $con->query("select * from (select *, (count(*) - 1) and_others, @rn:=@rn+1 AS new_id from (select * from notifications) t1, (SELECT @rn:=0) t2 where notification_to = ". $_SESSION["user_id"] ." group by type, extra, read_yet) t3 order by id desc limit 15")->fetchAll();	
}
// when the user is scrolling
else {
$notifications_arr = $con->query("select * from (select *, (count(*) - 1) and_others, @rn:=@rn+1 AS new_id from (select * from notifications) t1, (SELECT @rn:=0) t2 where notification_to = ". $_SESSION["user_id"] ." group by type, extra, read_yet) t3 order by id desc limit 15 OFFSET ". $_GET["row_offset"])->fetchAll();	
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
$con->exec("update notifications set read_yet = ". time() ." where read_yet = 0 and notification_to = ". $_SESSION["user_id"] ." and (". $notification_where .")");

for($i = 0;$i<count($notifications_arr);$i++) {
$notification = $notifications_arr[$i];

$sender_arr = $con->query("select first_name, last_name, avatar_picture from users where id = ". $notification["notification_from"])->fetch();
$sender_avatar_arr = $con->query("SELECT positions, rotate_degree FROM avatars WHERE id_of_user = ". $notification["notification_from"] ." order by id desc limit 1")->fetch();
$sender_avatar_positions = explode(",",$sender_avatar_arr["positions"]);
//if avatar positions does not exist 
if(count($sender_avatar_positions) < 2) {
$sender_avatar_positions = [0,0];
}

array_push($echo_arr, [
"notification_id" => $notification["id"],
"notification_time" => $notification["time"], 
"notification_time_string" => time_to_string($notification["time"]),
"notification_type" => $notification["type"], 
"notification_extra" => $notification["extra"], 
"notification_extra2" => $notification["extra2"], 
"notification_extra3" => $notification["extra3"], 
"notification_read_yet" => $notification["read_yet"], 
"notification_and_others" => $notification["and_others"], 
"notification_sender_info" => [
	"id" => $notification["notification_from"], 
	"first_name" => $sender_arr["first_name"],
	"last_name" => $sender_arr["last_name"],
	"avatar" => $sender_arr["avatar_picture"],
	"avatar_positions" => $sender_avatar_positions,
	"avatar_rotate_degree" => $sender_avatar_arr["rotate_degree"]
	] 
]);

} 
}

}

echo json_encode($echo_arr);







function time_to_string($time) {
		
$time = intval($time);	
	
$today = new DateTime(); // This object represents current date/time
$today->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$match_date = DateTime::createFromFormat( "Y-m-d H:i", date("Y-m-d H:i",$time));
$match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$diff = $today->diff( $match_date );
$diffDays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

if(time() - $time < 120) {
return "Just Now";
}	
else if(time() - $time < 3600) {
return round((time() - $time)/60) ." Minutes Ago";
}
else if($diffDays == 0) {
return round((((time() - $time)/60)/60)) . " Hour". (round((((time() - $time)/60)/60)) != 1 ? "s" : "")  ." Ago";	
}
else if($diffDays == -1) {
return "Yesterday At ". date("H:i",$time);	
} 
else if(time() - $time < 604800){
return date("l",$time);	
}
else {
return date("Y/m/d H:i",$time);		
}
}


?>