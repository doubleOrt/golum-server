<?php
#page is used when users block other users.


include_once "common_requires.php";


if(isset($_GET["user_id"]) && is_numeric($_GET["user_id"])) {
$already_blocked = $con->query("select id from blocked_users where user_ids = '".$_SESSION["user_id"]."-".htmlspecialchars($_GET["user_id"],ENT_QUOTES)."'")->fetch();	
if($already_blocked[0] == "") {
if($con->exec("insert into blocked_users (user_ids,time) values('". $_SESSION["user_id"] . "-" . htmlspecialchars($_GET["user_id"],ENT_QUOTES)."',".time().")")) {
$con->exec("delete from contacts where contact_of = ".$_SESSION["user_id"]." and contact = ". htmlspecialchars($_GET["user_id"],ENT_QUOTES));	
$con->exec("delete from contacts where contact = ".$_SESSION["user_id"]." and contact_of = ". htmlspecialchars($_GET["user_id"],ENT_QUOTES));	
echo "0";
}
}
else if($con->exec("delete from blocked_users where user_ids = '".$_SESSION["user_id"]."-".htmlspecialchars($_GET["user_id"],ENT_QUOTES)."'")){	
echo "1";	
}
}




?>