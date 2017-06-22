<?php
//we make an ajax call to this page everytime a user changes his profile info , gender, country or birthdate.

require_once "common_requires.php";
require_once "logged_in_importants.php";







// if user wants to change his gender.
if(isset($_GET["gender"])) {
if($_GET["gender"] != "") {	
$new_gender = $_GET["gender"];
$prepared = $con->prepare("update users set gender = :gender where id = ".$_SESSION["user_id"]);
$prepared->bindParam(":gender",$new_gender);
$prepared->execute();
echo "$(\"#genderContainer\").html(\"Gender: ". strtoupper($_GET["gender"]) ." <img src='icons/".$_GET["gender"].".png' alt='".$_GET["gender"]." Icon'/>\");";
echo "changeInfosGetObjDefaults['gender'] = '". $new_gender ."';
Materialize.toast('Gender Changed To&nbsp;<b>".strtoupper($new_gender)."</b>',5000,'green');";
}
}

// if user wants to change his country.
if(isset($_GET["country"])) {
if($_GET["country"] != "") {		
$new_country = $_GET["country"];
$prepared = $con->prepare("update users set country = :country where id = ".$_SESSION["user_id"]);
$prepared->bindParam(":country",$new_country);
$prepared->execute();
echo "$(\"#countryContainer\").html(\"Lives In: ". strtoupper($new_country) ." <span class='flag-icon-background flag-icon-". strtolower($_GET["country"]) ."' style='box-shadow:0 0 10px rgba(0,0,0,.2);'></span>\");";
echo "changeInfosGetObjDefaults['country'] = '". strtoupper($new_country) ."';
Materialize.toast('Country Changed To&nbsp;<b>".strtoupper($new_country)."</b>',5000,'green');";
}
}

// when user wants to change his birthdate.
if(isset($_GET["birthdate"])) {
if($_GET["birthdate"] != "") {	
$new_birthdate = $_GET["birthdate"];	
$prepared = $con->prepare("update users set birthdate = :birthdate where id = ".$_SESSION["user_id"]);
$prepared->bindParam(":birthdate",$new_birthdate);
$prepared->execute();
echo "$(\"#birthdateContainer\").html(\"Birthdate: ". $new_birthdate ." <span class='birthdateContainer'><div>". date_diff(date_create(date("Y-m-d")),date_create(str_replace(",","",$_GET["birthdate"])))->y . "</div></span>\");";
echo "changeInfosGetObjDefaults['birthdate'] = '". $new_birthdate ."';
Materialize.toast('Birthdate Changed To&nbsp;<b>".strtoupper($new_birthdate)."</b>',5000,'green');";
}
}




/* when a user repositions his/her avatar image, we inser the new positions into a table in our database */
if(isset($_GET["avatar_positions"])) {
if($_GET["avatar_positions"] != "") {		
$con->exec("update avatars set positions = '".$_GET["avatar_positions"][0] . "," . $_GET["avatar_positions"][1] ."' where id_of_user = ".$_SESSION["user_id"]." order by id desc limit 1");
echo "
changeInfosGetObjDefaults['avatar_positions'] = ['".$_GET["avatar_positions"][0]."','".$_GET["avatar_positions"][1]."'];

$('.baseUserAvatarRotateDivs').each(function(){	
$(this).parent().css({'margin-top':'".$_GET["avatar_positions"][0]."%','margin-left':'".$_GET["avatar_positions"][1]."%'});
adaptRotateWithMargin($(this).find('img'),$(this).attr('data-rotate-degree') ,false);
});


Materialize.toast('Avatar Repositioned',5000,'green');	
";
}
}


if(isset($_GET["avatar_rotation"])) {
if($_GET["avatar_rotation"] != "") {
$con->exec("update avatars set rotate_degree = '". $_GET["avatar_rotation"] ."', positions = '0,0' where id_of_user = ".$_SESSION["user_id"]." order by id desc limit 1");		
echo "
Materialize.toast('Avatar Rotated',2000,'green');
changeInfosGetObjDefaults['avatar_rotation'] = '". $_GET["avatar_rotation"] ."';

$('.baseUserAvatarRotateDivs').each(function(){	
$(this).attr('data-rotate-degree','". $_GET["avatar_rotation"] . "');
$(this).css('transform','rotate(' + $(this).attr('data-rotate-degree') + 'deg)');	
adaptRotateWithMargin($(this).find('img'),$(this).attr('data-rotate-degree') ,false);
});

";
}	
}





?>