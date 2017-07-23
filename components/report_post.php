<?php 

require_once "common_requires.php";
require_once "logged_in_importants.php";


$NUMBER_OF_REPORTS_TO_DISABLE_POST = 30;

if(isset($_POST["post_id"]) && filter_var($_POST["post_id"], FILTER_VALIDATE_INT) !== false) {

if($con->query("select id from post_reports where post_id = ". $_POST["post_id"] ." and user_id = ". $_SESSION["user_id"])->fetch()["id"] == "") {

if($con->prepare("insert into post_reports (post_id,user_id,time) values (:post_id, :user_id, :time)")->execute([":post_id" => $_POST["post_id"], ":user_id" => $_SESSION["user_id"], ":time" => time()])) {
	
/* check if the number of reports for this post has exceeded the number of 
reports required to disable a post, if so, then disable the post. */
$post_total_reports_prepared = $con->prepare("select count(id) from post_reports where post_id = :post_id");
$post_total_reports_prepared->execute([":post_id" => $_POST["post_id"]]);	
$post_total_reports = $post_total_reports_prepared->fetch()[0];
if($post_total_reports >= $NUMBER_OF_REPORTS_TO_DISABLE_POST) {
$con->prepare("update posts set disabled = 'true' where id = :post_id")->execute([":post_id" => $_POST["post_id"]]);	
}

echo "Materialize.toast('Post Reported!',5000,'green');";	
}
	
}
else {
echo "Materialize.toast('You\'ve Already Reported This Post!',5000,'red');";
}
	
}



?>