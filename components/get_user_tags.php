<?php
require_once "common_requires.php";
require_once "logged_in_importants.php";

// first index will be filled with search result data rows, second index will be the number of the search results.
$echo_arr = [];	

if(isset($_GET["user_id"]) && isset($_GET["row_offset"]) && filter_var($_GET["user_id"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["row_offset"], FILTER_VALIDATE_INT) !== false) {

// this query selects only accounts not existing in the account_states database table.
$search_prepare = $con->prepare("select tag, (select count(id) from posts where (tags like CONCAT('%,', following_tags.tag, ',%') or tags like concat(following_tags.tag, ',%') or tags like concat('%,', following_tags.tag) or tags like following_tags.tag)) as total_posts, (select count(id) from following_tags as following_tags1 where following_tags1.tag = following_tags.tag) as total_followers, (select concat(id, '-' ,file_types) from posts where (tags like CONCAT('%,', following_tags.tag, ',%') or tags like concat(following_tags.tag, ',%') or tags like concat('%,', following_tags.tag) or tags like following_tags.tag) order by id desc limit 1) as sample_post_id_and_file_type, (select posted_by from posts where (tags like CONCAT('%,', following_tags.tag, ',%') or tags like concat(following_tags.tag, ',%') or tags like concat('%,', following_tags.tag) or tags like following_tags.tag) order by id desc limit 1) as sample_image_posted_by from following_tags where id_of_user = :user_id and tag != '' order by following_tags.tag limit 15 ". ($_GET["row_offset"] > 0 ? "OFFSET ". $_GET["row_offset"] : ""));
$search_prepare->bindParam(':user_id', $_GET["user_id"], PDO::PARAM_INT);
$search_prepare->execute();

$all_search_results = $search_prepare->fetchAll(PDO::FETCH_ASSOC);


foreach($all_search_results as $row) {	
if($row["sample_post_id_and_file_type"] != "") {
$sample_image_path = $SERVER_URL . "users/" . $row["sample_image_posted_by"] . "/posts/" . explode("-", $row["sample_post_id_and_file_type"])[0] . "-0." . explode(",", explode("-", $row["sample_post_id_and_file_type"])[1])[0];
}
else {
$sample_image_path = "icons/default_tag_image.png";
}
array_push($echo_arr, [
"tag" => htmlspecialchars($row["tag"], ENT_QUOTES, "utf-8"),
"total_posts" => htmlspecialchars($row["total_posts"], ENT_QUOTES, "utf-8"),
"total_followers" => htmlspecialchars($row["total_followers"], ENT_QUOTES, "utf-8"),
"sample_image_path" => htmlspecialchars($sample_image_path, ENT_QUOTES, "utf-8"),
]);
}

}

echo json_encode($echo_arr);

unset($con);

?>