<?php
require_once "common_requires.php";
require_once "logged_in_importants.php";

// first index will be filled with search result data rows, second index will be the number of the search results.
$echo_arr = [[] , ""];	


if(isset($_GET["search_value"])) {
	
if( isset($_GET["row_offset"]) && is_integer(intval($_GET["row_offset"])) )	{	
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
$search_prepare = $con->prepare("SELECT users.id, users.first_name, users.last_name, users.user_name, users.avatar_picture FROM users LEFT JOIN account_states ON users.id = account_states.user_id WHERE (concat(first_name,' ',last_name) LIKE :search_value or user_name LIKE :search_value) AND type IS NULL LIMIT 15 OFFSET :row_offset");
$search_prepare->bindParam(":search_value",$search_value);
$search_prepare->bindParam(":row_offset", $row_offset , PDO::PARAM_INT);
$search_prepare->execute();

$all_search_results = $search_prepare->fetchAll();

$echo_arr[1] = count($all_search_results);


foreach($all_search_results as $row) {
	
$current_state = $con->query("select id from blocked_users where user_ids = '".$row[0]. "-" . $_SESSION["user_id"]."'")->fetch();		
// if the current user has been blocked by the user of the current iteration	
if($current_state[0] != "") {
continue;	
}
	
	
// get the user's current avatar row
$search_result_avatar_arr = $con->query("SELECT * FROM avatars WHERE id_of_user = ". $row[0] ." order by id desc limit 1")->fetch();

// get the user's current avatar positions (left , top)
$search_result_avatar_arr_positions = explode(",",$search_result_avatar_arr["positions"]);

// if they don't exist, set $search_result_avatar_arr_positions to 0,0
if(count($search_result_avatar_arr_positions) < 2) {
$search_result_avatar_arr_positions = [0,0];	
}

array_push($echo_arr[0], [
"id" =>  htmlspecialchars($row["id"]),	
'first_name' => htmlspecialchars($row["first_name"]),
'last_name' => htmlspecialchars($row["last_name"]),
'user_name' => htmlspecialchars($row["user_name"]),
'avatar' => htmlspecialchars($row["avatar_picture"]),
'avatar_positions' => $search_result_avatar_arr_positions,
'avatar_rotate_degree' => htmlspecialchars($search_result_avatar_arr["rotate_degree"])
]);


}

echo json_encode($echo_arr);

}


?>