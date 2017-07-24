<?php
#page is used when users block other users.


include_once "common_requires.php";

$echo_arr = [""];

if(isset($_GET["user_id"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false) {
	
$current_state_prepared = $con->prepare("select id from contacts where contact_of = :base_user_id and contact = :user_id");
$current_state_prepared->execute([":base_user_id" => $_SESSION["user_id"], ":user_id" => $_GET["user_id"]]);
$echo_arr[1] = $current_state_prepared->fetch()[0] == "" ? 0 : 1;	
	
$already_blocked_prepared = $con->prepare("select id from blocked_users where user_ids = concat(:base_user_id, '-', :user_id)");
$already_blocked_prepared->execute([":base_user_id" => $_SESSION["user_id"], ":user_id" => $_GET["user_id"]]);
$already_blocked = $already_blocked_prepared->fetch();	

if($already_blocked[0] == "") {
if($con->prepare("insert into blocked_users (user_ids,time) values(concat(:base_user_id, '-', :user_id),:time)")->execute([":base_user_id" => $_SESSION["user_id"], ":user_id" => $_GET["user_id"], ":time" => time()])) {
$con->prepare("delete from contacts where contact_of = :base_user_id and contact = :user_id")->execute([":base_user_id" => $_SESSION["user_id"], ":user_id" => $_GET["user_id"]]);	
$con->prepare("delete from contacts where contact = :base_user_id and contact_of = :user_id")->execute([":base_user_id" => $_SESSION["user_id"], ":user_id" => $_GET["user_id"]]);	
/* nullify the "x is now following you" button inserted previously, just in case the user starts following someone and then immediately unfollows them, 
else the receiver would be confused */
$con->prepare("delete from notifications where notification_from = :base_user_id and notification_to = :user_id and type = 6")->execute([":base_user_id" => $_SESSION["user_id"], ":user_id" => $_GET["user_id"]]);
$echo_arr[0] = "0";
}
}
else if($con->prepare("delete from blocked_users where user_ids = concat(:base_user_id, '-', :user_id)")->execute([":base_user_id" => $_SESSION["user_id"], ":user_id" => $_GET["user_id"]])){	
$echo_arr[0] = "1";	
}

}


echo json_encode($echo_arr);

unset($con);


?>