<?php
// calls are made to this page to get the time of a post.

require_once "common_requires.php";
require_once "logged_in_importants.php";


function time_to_string($time) {
		
$time = intval($time);	
	
$today = new DateTime(); // This object represents current date/time
$today->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$match_date = DateTime::createFromFormat( "Y-m-d H:i", date("Y-m-d H:i",$time));
$match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$diff = $today->diff( $match_date );
$diffDays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

if(time() - $time < 120) {
return "Just Now";
}	
else if(time() - $time < 3600) {
return round((time() - $time)/60) ." Minutes Ago";
}
else if($diffDays == 0) {
return round((((time() - $time)/60)/60)) . " Hour". (round((((time() - $time)/60)/60)) != 1 ? "s" : "")  ." Ago";	
}
else if($diffDays == -1) {
return "Yesterday At ". date("H:i",$time);	
} 
else if(time() - $time < 604800){
return date("l",$time);	
}
else {
return date("Y/m/d H:i",$time);		
}
}

$echo_arr = [];

if(isset($_GET["post_ids"]) && is_array($_GET["post_ids"])) {
	
$posts_query_string = "";
for($i = 0;$i < count($_GET["post_ids"]);$i++) {
if(filter_var($_GET["post_ids"][$i], FILTER_VALIDATE_INT) === false) {
echo json_encode($echo_arr);	
die();	
}
if($i != 0) {
$posts_query_string .= " or ";	
}	
$posts_query_string .= "posts.id = ". $_GET["post_ids"][$i];	
}


$all_posts_arr = $con->query("select distinct posts.id, posts.time, favorites.id as added_to_favorites, (select count(id) from post_comments where post_id = posts.id) as post_comments_num from posts left join favorites on posts.id = favorites.post_id and favorites.user_id = ". $_SESSION["user_id"] ." left join post_comments on post_comments.post_id = posts.id where (". $posts_query_string .")")->fetchAll();


foreach($all_posts_arr as $post_arr) {
array_push($echo_arr,[$post_arr["id"], time_to_string($post_arr["time"]), htmlspecialchars($post_arr["post_comments_num"], ENT_QUOTES, "utf-8") , ($post_arr["added_to_favorites"] == "" ? 0 : 1)]);
}

}



echo json_encode($echo_arr);

unset($con);



?>