<?php
require_once "common_requires.php";
require_once "logged_in_importants.php";

// first index will be filled with search result data rows, second index will be the number of the search results.
$echo_arr = [];	


if(isset($_GET["search_value"])) {
	
if( isset($_GET["row_offset"]) && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false )	{	
// our SQL query requires us to cast this to an integer.
$row_offset = (int) $_GET["row_offset"];	
}
else {
$row_offset = 0;	
}
		
if($_GET["search_value"] == "" || preg_match("/^\s+$/i",$_GET["search_value"])) {
echo json_encode($echo_arr);
die();
}	
	
$search_value_raw = trim(addslashes($_GET["search_value"]));
// prepare the search value for the sql query.	
$search_value = $search_value_raw . "%";	


// this query selects only accounts not existing in the account_states database table.
$search_prepare = $con->prepare("SELECT users.id, users.first_name, users.last_name, users.user_name, users.avatar_picture, (select id from contacts where contact_of = :base_user_id and contact = users.id) as current_state FROM users LEFT JOIN account_states ON users.id = account_states.user_id WHERE ((concat(first_name,' ',last_name) LIKE :search_value or user_name LIKE :search_value) AND users.id != :base_user_id and type IS NULL) and (users.id not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and users.id not in (SELECT user_id from account_states)) LIMIT 15 OFFSET :row_offset");
$search_prepare->bindParam(":base_user_id",$_SESSION["user_id"]);
$search_prepare->bindParam(":search_value",$search_value);
$search_prepare->bindParam(":row_offset", $row_offset , PDO::PARAM_INT);
$search_prepare->execute();

$all_search_results = $search_prepare->fetchAll();


foreach($all_search_results as $row) {
	
// get the user's current avatar row
$search_result_avatar_arr = $con->query("SELECT * FROM avatars WHERE id_of_user = ". $row[0] ." order by id desc limit 1")->fetch();

// get the user's current avatar positions (left , top)
$search_result_avatar_arr_positions = explode(",",htmlspecialchars($search_result_avatar_arr["positions"], ENT_QUOTES, "utf-8"));

// if they don't exist, set $search_result_avatar_arr_positions to 0,0
if(count($search_result_avatar_arr_positions) < 2) {
$search_result_avatar_arr_positions = [0,0];	
}

array_push($echo_arr, [
"id" =>  htmlspecialchars($row["id"], ENT_QUOTES, "utf-8"),	
'first_name' => htmlspecialchars($row["first_name"], ENT_QUOTES, "utf-8"),
'last_name' => htmlspecialchars($row["last_name"], ENT_QUOTES, "utf-8"),
'user_name' => htmlspecialchars($row["user_name"], ENT_QUOTES, "utf-8"),
'current_state' => ($row["current_state"] == "" ? 0 : 1),
'avatar' => htmlspecialchars($row["avatar_picture"], ENT_QUOTES, "utf-8"),
'avatar_positions' => $search_result_avatar_arr_positions,
'avatar_rotate_degree' => htmlspecialchars($search_result_avatar_arr["rotate_degree"], ENT_QUOTES, "utf-8")
]);


}

}

echo json_encode($echo_arr);


?>