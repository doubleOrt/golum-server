<?php
require_once "common_requires.php";
require_once "logged_in_importants.php";

// first index will be filled with search result data rows, second index will be the number of the search results.
$echo_arr = [];	


if(isset($_GET["search_value"])) {
	
if( isset($_GET["row_offset"]) &&  filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false )	{	
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

$tag = "#" . trim($_GET["search_value"]);

// this query selects only accounts not existing in the account_states database table.
$search_prepare = $con->prepare("select matching_tag, total_posts, total_followers, sample_post_id_and_file_type, tag as current_state from (select case when LOCATE(',', tags) != 0 then MID(MID(tags, LOCATE(:tag, tags), CHAR_LENGTH(TITLE)), 1, LOCATE(',', MID(tags, LOCATE(:tag, tags), CHAR_LENGTH(MID(tags, LOCATE(:tag, tags))))) - 1) else MID(MID(tags, LOCATE(:tag, tags), CHAR_LENGTH(TITLE)), 1) end as matching_tag, (select count(id) from posts where (tags like CONCAT('%,', matching_tag, ',%') or tags like concat(matching_tag, ',%') or tags like concat('%,', matching_tag) or tags like matching_tag) and disabled != 'true') as total_posts, (select count(id) from following_tags where following_tags.tag like matching_tag) as total_followers, (select concat(id, '-' ,file_types) from posts where (tags like CONCAT('%,', matching_tag, ',%') or tags like concat(matching_tag, ',%') or tags like concat('%,', matching_tag) or tags like matching_tag) order by id desc limit 1) as sample_post_id_and_file_type from posts where (title like concat('%', :tag, '%') or title like concat(:tag, '%') or title like concat('%', :tag)) and disabled != 'true' group by matching_tag) t1 left join following_tags on tag = matching_tag and id_of_user = :user_id where matching_tag != '' order by total_posts limit 15 ". ($row_offset > 0 ? "OFFSET :row_offset" : ""));

$search_prepare->bindParam(':tag', $tag, PDO::PARAM_STR);
$search_prepare->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
$search_prepare->bindParam(":row_offset", $row_offset , PDO::PARAM_INT);
$search_prepare->execute();

$all_search_results = $search_prepare->fetchAll(PDO::FETCH_ASSOC);


foreach($all_search_results as $row) {	
$sample_image_path = "posts/" . explode("-", $row["sample_post_id_and_file_type"])[0] . "-0." . explode(",", explode("-", $row["sample_post_id_and_file_type"])[1])[0];
array_push($echo_arr, [
"tag" => htmlspecialchars($row["matching_tag"], ENT_QUOTES, "utf-8") ,
"total_posts" => htmlspecialchars($row["total_posts"], ENT_QUOTES, "utf-8") ,
"total_followers" => htmlspecialchars($row["total_followers"], ENT_QUOTES, "utf-8") ,
"sample_image_path" => $SERVER_URL . htmlspecialchars($sample_image_path, ENT_QUOTES, "utf-8"),
"current_state" => ($row["current_state"] != "" ? 1 : 0)
]);
}

}

echo json_encode($echo_arr);

unset($con);



?>