<?php
require_once "common_requires.php";
require_once "logged_in_importants.php";


$echo_arr = [[]];

if(isset($_GET["user_id"]) && isset($_GET["row_offset"]) && isset($_GET["type"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["type"], FILTER_VALIDATE_INT) !== false) {
	
// user followers requested	
if($_GET["type"] === "0") {	 
$all_contacts_arr_prepared = $con->prepare("select users.id, first_name, last_name, user_name, gender, avatar_picture from contacts left join users on contacts.contact_of = users.id where contact = :user_id and contact_of not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and contact_of not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and contact_of not in (SELECT user_id from account_states) order by first_name asc limit 15 ". ($_GET["row_offset"] > 0 ? "OFFSET " . $_GET["row_offset"] : ""));
$all_contacts_arr_prepared->execute([":user_id" => $_GET["user_id"], ":base_user_id" => $GLOBALS["base_user_id"]]);
$all_contacts_arr = $all_contacts_arr_prepared->fetchAll();	

$contacts_male_female_widget_prepared = $con->prepare("select (select count(users.id) from contacts left join users on contacts.contact_of = users.id where contact = :user_id and gender='male' and contact_of not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and contact_of not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and contact_of not in (SELECT user_id from account_states)) as total_male, (select count(users.id) from contacts left join users on contacts.contact_of = users.id where contact = :user_id and gender='female' and contact_of not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and contact_of not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and contact_of not in (SELECT user_id from account_states)) as total_female, (select count(id) from contacts where contact = :user_id and contact_of not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and contact_of not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and contact_of not in (SELECT user_id from account_states)) as total");
$contacts_male_female_widget_prepared->execute([":user_id" => $_GET["user_id"], ":base_user_id" => $GLOBALS["base_user_id"]]);
$contacts_male_female_widget = $contacts_male_female_widget_prepared->fetch();
($_GET["row_offset"] == 0 ? array_push($echo_arr, $contacts_male_female_widget) : null);
}
// user followings requested	
else if($_GET["type"] === "1") {
$all_contacts_arr_prepared = $con->prepare("select users.id, first_name, last_name, user_name, gender, avatar_picture from contacts left join users on contacts.contact = users.id where contact_of = :user_id and contact not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and contact not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and contact not in (SELECT user_id from account_states) order by first_name asc limit 15 ". ($_GET["row_offset"] > 0 ? "OFFSET " . $_GET["row_offset"] : ""));	
$all_contacts_arr_prepared->execute([":user_id" => $_GET["user_id"], ":base_user_id" => $GLOBALS["base_user_id"]]);
$all_contacts_arr = $all_contacts_arr_prepared->fetchAll();	

$contacts_male_female_widget_prepared = $con->prepare("select (select count(users.id) from contacts left join users on contacts.contact = users.id where contact_of = :user_id and gender='male' and contact not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and contact not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and contact not in (SELECT user_id from account_states)) as total_male, (select count(users.id) from contacts left join users on contacts.contact = users.id where contact_of = :user_id and gender='female' and contact not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and contact not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and contact not in (SELECT user_id from account_states)) as total_female, (select count(id) from contacts where contact_of = :user_id and contact not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and contact not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and contact not in (SELECT user_id from account_states)) as total");
$contacts_male_female_widget_prepared->execute([":user_id" => $_GET["user_id"], ":base_user_id" => $GLOBALS["base_user_id"]]);
$contacts_male_female_widget = $contacts_male_female_widget_prepared->fetch();
($_GET["row_offset"] == 0 ? array_push($echo_arr, $contacts_male_female_widget) : null);
}
else {
echo json_encode($echo_arr);	
die();
}

foreach($all_contacts_arr as $row) {

$avatar_arr = custom_pdo("SELECT * FROM avatars WHERE id_of_user = :user_id order by id desc limit 1", [":user_id" => $row["id"]])->fetch();	
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
"avatar_picture" => ($row["avatar_picture"] != "" ? (preg_match('/https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif)/', $row["avatar_picture"]) ? $row["avatar_picture"] : ($SERVER_URL . htmlspecialchars($row["avatar_picture"], ENT_QUOTES, "utf-8"))) : ""),
"avatar_positions" => [htmlspecialchars($avatar_positions[0], ENT_QUOTES, "utf-8") , htmlspecialchars($avatar_positions[1], ENT_QUOTES, "utf-8")],
"avatar_rotate_degree" => htmlspecialchars($avatar_arr["rotate_degree"], ENT_QUOTES, "utf-8")
]);

}

}

echo json_encode($echo_arr);

unset($con);


?>