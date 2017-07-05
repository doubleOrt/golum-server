<?php
// when user wants to get the votes markup for a post

require_once "common_requires.php";
require_once "logged_in_importants.php";


function get_post_votes_markup($post_type,$vote_index,$all_indices_arr,$user_vote_index,$positive_icon,$negative_icon) {
$total_votes_number = 0;
$index_is_majority = true;
$index_votes_number = $all_indices_arr[$vote_index][1];
for($i = 0;$i < count($all_indices_arr);$i++){
$total_votes_number += $all_indices_arr[$i][1];	
if($all_indices_arr[$i][1] > $all_indices_arr[$vote_index][1]) {
$index_is_majority = false;	
}
}	

$total_votes_percentage = ($total_votes_number > 0 ? round(($index_votes_number / $total_votes_number)*100) : 0);

$votes_line_max_height = 105;

return "<div class='votesContainer z-depth-2'>
<div class='votesContainerChild'>
<div class='votesIcon fullyRoundedBorder z-depth-1 white-text ". ($index_is_majority == true ? "majorityVoteBackgroundColor" : "minorityVoteBackgroundColor") ."' ". ($vote_index == $user_vote_index ? "data-user-vote='true'"  : "" ) ."><i class='material-icons'>". ($post_type == 1 ? ($vote_index == 0 ? $positive_icon : $negative_icon) : ($vote_index == $user_vote_index ? $positive_icon : $negative_icon)) ."</i></div>
<div class='totalVotesNumber' ". ($vote_index == $user_vote_index ? "data-user-vote='true'"  : "" ) ." data-votes-number='". $index_votes_number ."'>". $index_votes_number . " Vote" . ($index_votes_number != 1 ? "s" : "") ."</div>
<div class='totalVotesPercentage'>". $total_votes_percentage ."%</div>
</div>
<div class='votesLineContainer' style='height:". (($total_votes_percentage/100) * $votes_line_max_height) ."px' data-max-height='". $votes_line_max_height ."'>
<div class='votesLine ". ($index_is_majority == true ? "majorityVoteBackgroundColor" : "minorityVoteBackgroundColor") ."'></div>
</div>
</div>";
}

if(isset($_POST["post_id"]) && is_numeric($_POST["post_id"]) && isset($_POST["post_type"]) && is_numeric($_POST["post_type"]) && isset($_POST["positive_icon"]) && isset($_POST["negative_icon"])) {
	
$all_votes = $con->query("select post_id, user_id, option_index from post_votes where post_id = ". $_POST["post_id"])->fetchAll();

$echo_arr = [];

$post_votes = [];


// bend some rules for type 1 posts
if($_POST["post_type"] != 1) {
for($i = 0;$i<$_POST["post_type"];$i++) {
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
if($all_votes[$i]["user_id"] == $_SESSION["user_id"]) {
$user_option_index = $all_votes[$i]["option_index"];
}	
for($x = 0;$x < count($post_votes);$x++) {
if($post_votes[$x][0] == $all_votes[$i]["option_index"]) {
$post_votes[$x][1]++; 	
}	
}
}

if(!isset($user_option_index)) {
$user_option_index = 1000;	
}


// echo'em out.
for($i = 0;$i<count($post_votes);$i++) {
$echo_arr[$i][0] = $post_votes[$i][0];
$echo_arr[$i][1] = get_post_votes_markup($_POST["post_type"],$post_votes[$i][0],$post_votes,$user_option_index,$_POST["positive_icon"],$_POST["negative_icon"]);
}

echo json_encode($echo_arr);

}

?>