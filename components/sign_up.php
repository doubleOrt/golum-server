<?php
require_once "common_requires.php";



//if chat clicked the submit button, call the sign up function.
if(isset($_POST["first_name"]) && isset($_POST["last_name"]) && isset($_POST["user_name"]) && isset($_POST["password"])) {
sign_up();	
}

function sign_up() {
global $con; 
	 
// check if any of the values from post are empty, if they are, then echo a toast and die(). 
foreach($_POST as $i) {
if($i == "") {
echo "Materialize.toast('Something Went Wrong, Sorry!',5000,'red');";
return false;
}	
}	 

//these 2 are the user's first name and last name.
$sign_up_first_name = trim($_POST["first_name"]);
$sign_up_last_name = trim($_POST["last_name"]);
// assign each of the post values to a variable.
$sign_up_user_name = trim($_POST["user_name"]);
// get and hash the password.
$sign_up_password_base = $_POST["password"];
$sign_up_password = password_hash($_POST["password"],PASSWORD_BCRYPT);


$check_sign_up_first_name = new ValidateItem($sign_up_first_name,"/^[a-zA-Zs]{3,18}$/i","First Name Must Only Contain Letters And Spaces And Must Be Longer Than 3 And Shorter Than 18 Characters");
$check_sign_up_last_name = new ValidateItem($sign_up_last_name,"/^[a-zA-Zs]{3,18}$/i","First Name Must Only Contain Letters And Spaces And Must Be Longer Than 3 And Shorter Than 18 Characters");
$check_sign_up_user_name = new ValidateItem($sign_up_user_name,"/^([a-zA-Z]+[0-9 ]*){6,36}$/i","Username Must Be A Combination Of Letters, Numbers And Spaces And Muse Be Between 6-36 Characters In Length");
$check_sign_up_password = new ValidateItem($sign_up_password_base,"/^(?=.*[A-Za-z])(?=.*\d)(?=.*([$@$!%*#?& ]*))[A-Za-z\d($@$!%*#?& )*]{8,50}$/i","Password Must Contain At Least 1 Digit And Must Be Between 8-50 Characters, Special Characters And Spaces Are Optional");

//regex checkes
if($check_sign_up_first_name->validate() == true && $check_sign_up_last_name-> validate() == true && $check_sign_up_user_name->validate() == true && $check_sign_up_password->validate() == true) {


// anything associated with prepare_availability_check checks if the username already exists.
$prepare_availability_check = $con->prepare("SELECT count(id) from users where user_name = :user_name");
$prepare_availability_check->bindParam(":user_name",$sign_up_user_name);
$prepare_availability_check->execute();

if($prepare_availability_check->fetch()[0] < 1) {

	
#we are creating a variable for this only because we can't directly bind it with a pdo value.	
$logged_in = true;	

$sign_up_date = date("Y/m/d H:i");	
	
// prepare, bind and execute.
$prepared = $con->prepare("INSERT INTO users (user_name, first_name, last_name, password,sign_up_date) VALUES (:user_name, :first_name, :last_name, :password,:sign_up_date)");
$prepared->bindParam(":user_name", $sign_up_user_name);
$prepared->bindParam(":first_name", $sign_up_first_name);
$prepared->bindParam(":last_name", $sign_up_last_name);
$prepared->bindParam(":password", $sign_up_password);
$prepared->bindParam(":sign_up_date", $sign_up_date);


if($prepared->execute()) {
// here we create a directory for the user which has the user's id, later we will put all media of a user inside this directory.
mkdir("../users/" . $con->lastInsertId());
mkdir("../users/" . $con->lastInsertId() . "/media");
mkdir("../users/" . $con->lastInsertId() . "/media/backgrounds");
mkdir("../users/" . $con->lastInsertId() . "/sentFiles");

$last_id = $con->lastInsertId();

// set the user_id session to the user's id in our database, this is required in order for our app to identify that the user is logged in.
$_SESSION["user_id"] = $last_id;

// shared memory segments, we need to write them.
write_shm($_SESSION["user_id"] . "" . 1,"false");
write_shm($_SESSION["user_id"] . "" . 2,time());
write_shm($_SESSION["user_id"] . "" . 3,"0");
write_shm($_SESSION["user_id"] . "" . 4,"none");	
write_shm($_SESSION["user_id"] . "" . 5,"none");


echo "success";
die();
}
// if the insert process was not successful, just tell the user something was wrong.
else {
echo "Materialize.toast('Something Went Wrong, Sorry!',10000,'red');";	
die();	
}

}
//if username exists already, alert the user.
else {
echo "Materialize.toast('Username Already Exists!',10000,'red');";	
die();
}
} 	
else {
echo "Materialize.toast('Something Went Wrong, Sorry!',5000,'red');";
die();
} 	 

}



?>