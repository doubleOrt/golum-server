<?php
#page is used when users block other users.


include_once "common_requires.php";


if(isset($_GET["user_id"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false) {
$already_blocked = $con->query("select id from blocked_users where user_ids = '".$_SESSION["user_id"]."-".htmlspecialchars($_GET["user_id"], ENT_QUOTES, "utf-8")."'")->fetch();	
if($already_blocked[0] == "") {
if($con->exec("insert into blocked_users (user_ids,time) values('". $_SESSION["user_id"] . "-" . htmlspecialchars($_GET["user_id"], ENT_QUOTES, "utf-8")."',".time().")")) {
$con->exec("delete from contacts where contact_of = ".$_SESSION["user_id"]." and contact = ". htmlspecialchars($_GET["user_id"], ENT_QUOTES, "utf-8"));	
$con->exec("delete from contacts where contact = ".$_SESSION["user_id"]." and contact_of = ". htmlspecialchars($_GET["user_id"], ENT_QUOTES, "utf-8"));
/* nullify the "x is now following you" button inserted previously, just in case the user starts following someone and then immediately unfollows them, 
else the receiver would be confused */
$con->exec("delete from notifications where notification_from = ". $_SESSION["user_id"] ." and notification_to = ". $_GET["user_id"] ." and type = 6");
echo "0";
}
}
else if($con->exec("delete from blocked_users where user_ids = '".$_SESSION["user_id"]."-".htmlspecialchars($_GET["user_id"], ENT_QUOTES, "utf-8")."'")){	
echo "1";	
}
}




?>