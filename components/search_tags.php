<?php
require_once "common_requires.php";
require_once "logged_in_importants.php";

// first index will be filled with search result data rows, second index will be the number of the search results.
$echo_arr = [];	


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


$tag_search = "%#" . trim($_GET["search_value"]) . " %";
$tag_search2 = "%#" . trim($_GET["search_value"]);
$tag_search3 = "#" . trim($_GET["search_value"]) . " %";

// this query selects only accounts not existing in the account_states database table.
$search_prepare = $con->prepare("select type, file_types from posts where title like :tag_search or title like :tag_search2 or title like :tag_search3 order by id desc limit 20 OFFSET :row_offset");
$search_prepare->bindParam(':tag_search', $tag_search, PDO::PARAM_STR);
$search_prepare->bindParam(':tag_search2', $tag_search2, PDO::PARAM_STR);
$search_prepare->bindParam(':tag_search3', $tag_search3, PDO::PARAM_STR);
$search_prepare->bindParam(":row_offset", $row_offset , PDO::PARAM_INT);
$search_prepare->execute();

$all_search_results = $search_prepare->fetchAll();


foreach($all_search_results as $row) {
$current_state = $con->query("select id from blocked_users where user_ids = '".$row["posted_by"]. "-" . $_SESSION["user_id"]."'")->fetch();		
// if the current user has been blocked by the user of the current iteration	
if($current_state[0] != "") {
continue;	
}
	
	
array_push($echo_arr, $row);

}

echo json_encode($echo_arr);

}


?>