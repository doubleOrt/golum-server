<?php
require_once "common_requires.php";
require_once "logged_in_importants.php";
  

if(isset($_GET["user_id"]) && is_numeric($_GET["user_id"])) {	 

$check_current_state = $con->query("select * from contacts where contact_of = ".$_SESSION["user_id"]." and contact = ".$_GET["user_id"])->fetch();

// if the contact is not added already
if($check_current_state["id"] == "") {
$con->exec("insert into contacts (contact_of,contact,date_added) values(".$_SESSION["user_id"].",".$_GET["user_id"].",'".date("Y/m/d H:i")."')");

// insert a notification
$con->exec("insert into notifications (notification_from,notification_to,time,type) values (". $_SESSION["user_id"] .",". $_GET["user_id"] .",". time() .",6);");	

echo "0";
}
// if the contact is already added meaning the user wants to remove this contact
else {
$con->exec("delete from contacts where contact_of = ".$_SESSION["user_id"]." and contact = ".$_GET["user_id"]);

// insert a notification
$con->exec("delete from notifications where notification_from = ". $_SESSION["user_id"] ." and notification_to = ". $_GET["user_id"] ." and type = 6");

echo "1";	
}

$shmid = $_GET["user_id"] . "" . 6; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("true"), 0);
shmop_close($shm);	

}
 
 
 
 ?>