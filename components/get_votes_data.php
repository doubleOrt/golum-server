<?php
// when user wants to get the votes markup for a post

require_once "common_requires.php";
require_once "logged_in_importants.php";


$echo_arr = [];

if(isset($_GET["post_id"]) && isset($_GET["post_type"]) && filter_var($_GET["post_id"], FILTER_VALIDATE_INT) !== false && filter_var($_GET["post_type"], FILTER_VALIDATE_INT) !== false) {

$post_votes_info_arr = get_post_votes_data($_GET["post_id"], $_GET["post_type"]);

$post_votes = $post_votes_info_arr[0];
$user_vote_index = $post_votes_info_arr[1];
$majority_vote_index = $post_votes_info_arr[2];
$total_votes_number = $post_votes_info_arr[3];

array_push($echo_arr, [
"post_type" => htmlspecialchars($_GET["post_type"], ENT_QUOTES, "utf-8"),
"user_vote_index" => htmlspecialchars($user_vote_index, ENT_QUOTES, "utf-8"),
"majority_vote_index" => htmlspecialchars($majority_vote_index, ENT_QUOTES, "utf-8"),
"total_votes_number" => htmlspecialchars($total_votes_number, ENT_QUOTES, "utf-8")
], []);


// add the requried info for each vote to the #1 index of the $echo_arr
for($i = 0;$i<count($post_votes);$i++) {
array_push($echo_arr[1], [
"vote_index" => htmlspecialchars($post_votes[$i][0], ENT_QUOTES, "utf-8"),
"index_total_votes" => htmlspecialchars($post_votes[$i][1], ENT_QUOTES, "utf-8"),
"index_votes_percentage_in_total" => htmlspecialchars($post_votes[$i][2], ENT_QUOTES, "utf-8")
]);
}

}


echo json_encode($echo_arr);

unset($con);




function get_post_votes_data($post_id, $post_type) {
global $con;	
	
$all_votes = custom_pdo("select post_id, user_id, option_index from post_votes where post_id = :post_id", [":post_id" => $post_id])->fetchAll();

$total_votes_number = count($all_votes);

$post_votes = [];

// bend some rules for type 1 posts
if($post_type != 1) {
for($i = 0;$i<$post_type;$i++) {
$post_votes[$i][0] = $i;
$post_votes[$i][1] = 0;
}
}
else {	
$post_votes[0][0] = 0;
$post_votes[0][1] = 0;	
$post_votes[1][0] = 1;
$post_votes[1][1] = 0;	
}


for($i = 0;$i<count($all_votes);$i++) {
if($all_votes[$i]["user_id"] == $GLOBALS["base_user_id"]) {
$user_vote_index = $all_votes[$i]["option_index"];
}	
for($x = 0;$x < count($post_votes);$x++) {
if($post_votes[$x][0] == $all_votes[$i]["option_index"]) {
$post_votes[$x][1]++; 	
}
}
}


$majority_vote = 0;
for($x = 0; $x < count($post_votes); $x++) {	
$is_majority_vote = true;
$index_votes_number = $post_votes[$x][1];
for($y = 0; $y < count($post_votes); $y++) {
if($post_votes[$y][1] > $post_votes[$x][1]) {
$is_majority_vote = false;	
}
}	
$post_votes[$x][2] = ($total_votes_number > 0 ? round(($index_votes_number / $total_votes_number)*100) : 0);
if($is_majority_vote === true) {
$majority_vote = $x;	
}
}


if(!isset($user_vote_index)) {
$user_vote_index = 1000;	
}
		
		
return [$post_votes, $user_vote_index, $majority_vote, $total_votes_number];	
}




?>