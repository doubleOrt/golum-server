<?php
require_once "common_requires.php";
require_once "logged_in_importants.php";


$echo_arr = [[]];

if(isset($_GET["user_id"]) && isset($_GET["row_offset"]) && isset($_GET["type"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["type"], FILTER_VALIDATE_INT) !== false) {
	
// user followers requested	
if($_GET["type"] === "0") {	 
$all_contacts_arr = $con->query("select users.id, first_name, last_name, user_name, gender, avatar_picture from contacts left join users on contacts.contact_of = users.id where contact = ". $_GET["user_id"] ." order by first_name asc limit 15 ". ($_GET["row_offset"] > 0 ? "OFFSET " . $_GET["row_offset"] : ""))->fetchAll();	
($_GET["row_offset"] == 0 ? array_push($echo_arr, $con->query("select (select count(users.id) from contacts left join users on contacts.contact_of = users.id where contact = ". $_GET["user_id"] ." and gender='male') as total_male, (select count(users.id) from contacts left join users on contacts.contact_of = users.id where contact = ". $_GET["user_id"] ." and gender='female') as total_female, (select count(id) from contacts where contact = ". $_GET["user_id"] .") as total")->fetch()) : null);
}
// user followings requested	
else if($_GET["type"] === "1") {
$all_contacts_arr = $con->query("select users.id, first_name, last_name, user_name, gender, avatar_picture from contacts left join users on contacts.contact = users.id where contact_of = ". $_GET["user_id"] ." order by first_name asc limit 15 ". ($_GET["row_offset"] > 0 ? "OFFSET " . $_GET["row_offset"] : ""))->fetchAll();	
($_GET["row_offset"] == 0 ? array_push($echo_arr, $con->query("select (select count(users.id) from contacts left join users on contacts.contact = users.id where contact_of = ". $_GET["user_id"] ." and gender='male') as total_male, (select count(users.id) from contacts left join users on contacts.contact = users.id where contact_of = ". $_GET["user_id"] ." and gender='female') as total_female, (select count(id) from contacts where contact_of = ". $_GET["user_id"] .") as total")->fetch()) : null);
}
else {
echo json_encode($echo_arr);	
die();
}

foreach($all_contacts_arr as $row) {
	
// check if the user in the current iteration has disabled or requested to delete his account, if so then skip this iteration.
if($con->query("SELECT * FROM account_states where user_id = ". $row["id"])->fetch()[0] != "") {
continue;	
}	


$avatar_arr = $con->query("SELECT * FROM avatars WHERE id_of_user = ".$row["id"]." order by id desc limit 1")->fetch();	
$avatar_positions = explode(",",$avatar_arr["positions"]);	

if(count($avatar_positions) < 2) {
$avatar_positions = [0,0];	
}
	
	
array_push($echo_arr[0], [
"id" => htmlspecialchars($row["id"], ENT_QUOTES, "utf-8"),
"first_name" => htmlspecialchars($row["first_name"], ENT_QUOTES, "utf-8"),
"last_name" => htmlspecialchars($row["last_name"], ENT_QUOTES, "utf-8"),
"user_name" => htmlspecialchars($row["user_name"], ENT_QUOTES, "utf-8"),
"gender" => htmlspecialchars($row["gender"], ENT_QUOTES, "utf-8"),
"avatar_picture" => htmlspecialchars($row["avatar_picture"], ENT_QUOTES, "utf-8"),
"avatar_positions" => [htmlspecialchars($avatar_positions[0], ENT_QUOTES, "utf-8") , htmlspecialchars($avatar_positions[1], ENT_QUOTES, "utf-8")],
"avatar_rotate_degree" => htmlspecialchars($avatar_arr["rotate_degree"], ENT_QUOTES, "utf-8")
]);

}

}

echo json_encode($echo_arr);

unset($con);


?>