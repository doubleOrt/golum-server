<?php 

require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_POST["post_id"]) && filter_var($_POST["post_id"], FILTER_VALIDATE_INT) !== false) {

if($con->query("select id from post_reports where post_id = ". $_POST["post_id"] ." and user_id = ". $_SESSION["user_id"])->fetch()["id"] == "") {

if($con->exec("insert into post_reports (post_id,user_id,time) values (". $_POST["post_id"] .",". $_SESSION["user_id"] .",". time() .")")) {
echo "Materialize.toast('Post Reported!',5000,'green');";	
}
	
}
else {
echo "Materialize.toast('You\'ve Already Reported This Post!',5000,'red');";
}
	
}



?>