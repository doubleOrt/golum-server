<?php

if(isset($_GET["user_id"]) && isset($_GET["activation_code"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["activation_code"], FILTER_VALIDATE_INT) !== false) {
$verify_id = $_GET["user_id"];
$verify_activation_code = $_GET["activation_code"];

$prepared = $con->prepare("update users set activated = 'yes' where id = :id and activated = :activated");
$prepared->bindParam(":id",$verify_id);
$prepared->bindParam(":activated",$verify_activation_code);
$prepared->execute();

if($prepared->rowCount() > 0) {
array_push($_SESSION["toasts"],"<script>Materialize.toast('Email Address Verified',5000,'green')</script>");
}

}


?>