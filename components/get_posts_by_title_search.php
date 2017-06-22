<?php
// calls are made to this page to get posts that match a search term

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "post_markup_function.php";


if(isset($_GET["search_value"]) && isset($_GET["last_post_id"]) && is_integer(intval($_GET["last_post_id"]))) {

$search_value_raw = trim(addslashes($_GET["search_value"]));

if($_GET["last_post_id"] == 0) {
$posts_arr = $con->query("select * from posts where title like '%". $search_value_raw ."%' order by id desc limit 3")->fetchAll();	
}
else {
$posts_arr = $con->query("select * from posts where title like '%". $search_value_raw ."%' and id < ". $_GET["last_post_id"] ." order by id desc limit 3")->fetchAll();		
}

$echo_arr = [""];


if(count($posts_arr) > 0) {
for($i = 0;$i<count($posts_arr);$i++) {
$posts_arr[$i]["new_id"] = $_GET["last_post_id"] + $i + 1;		
$echo_arr[0] .= get_post_markup($posts_arr[$i],"searchForTitlePosts");
}	
}
/* this is relatively useless since we only allow users to make a call to this page if there is at least one post matching their term, but, meh, we will just allow it in 
case we do allow users to make a call to this page even if there are 0 results matching their search. */
else if($_GET["last_post_id"] == 0) {
$echo_arr[0] = "<div class='emptyNowPlaceholder'>
<i class='material-icons'>info</i>
<br>
No Posts Matching Your Search Term!
</div>";
}
	
echo json_encode($echo_arr);	
}



?>