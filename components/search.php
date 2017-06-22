<?php
require_once "common_requires.php";
require_once "logged_in_importants.php";


if(isset($_GET["search_value"])) {
	
$echo_arr = ["","false"];	
	
if($_GET["search_value"] == "" || preg_match("/^\s+$/i",$_GET["search_value"])) {
echo json_encode($echo_arr);
die();
}	
	
$search_value_raw = trim(addslashes($_GET["search_value"]));
//prepare the search value for the sql query.	
$search_value = $search_value_raw . "%";	


# this query selects only accounts not existing in the account_states database table.
$search_prepare = $con->prepare("SELECT * FROM users LEFT JOIN account_states ON users.id = account_states.user_id WHERE (concat(first_name,' ',last_name) LIKE :search_value or user_name LIKE :search_value) AND type IS NULL LIMIT 40");
$search_prepare->bindParam(":search_value",$search_value);
$search_prepare->execute();

$all_search_results = $search_prepare->fetchAll();

$echo_arr[0] .= "<div id='searchResultRowsContainer'>";

if(count($all_search_results) > 0) {
$echo_arr[0] .= "<div class='searchSectionTitles' style='margin-top:10px;'>Accounts</div>";	
}

foreach($all_search_results as $row) {
	
$current_state = $con->query("select id from blocked_users where user_ids = '".$row[0]. "-" . $_SESSION["user_id"]."'")->fetch();		
	
if($current_state[0] != "") {
continue;	
}


if($con->query("SELECT id FROM account_states where user_id = ".$row[0])->fetch()[0] != "") {
continue;	
}	
	
$row_full_name = htmlspecialchars($row["first_name"] . " " . $row["last_name"]);	

$search_result_avatar_arr = $con->query("SELECT * FROM avatars WHERE id_of_user = ". $row[0] ." order by id desc limit 1")->fetch();

$search_result_avatar_arr_positions = explode(",",$search_result_avatar_arr["positions"]);

if(count($search_result_avatar_arr_positions) < 2) {
$search_result_avatar_arr_positions = [0,0];	
}

// everything inside this loop associated with string functions is just to produce the effect of adding the matched letters inside our special "searchResultMatchingLetters", also note that we didn't use a simple str_replace because of upper and lowercase issues.	
$str_search	= strpos(strtolower($row_full_name),strtolower($search_value_raw));
$match_letters_opening_tag = "<span class='searchResultMatchingLetters'>";

$str_search_2 = strpos(strtolower($row["user_name"]),strtolower($search_value_raw));

$uniq_id = rand(1000000,1000000000000);

$echo_arr[0] .= "
<div class='row searchResultRow showUserModal modal-trigger' id='searchUser".$row[0]."' data-target='modal1' data-user-id='".$row[0]."'>
<div class='col l12 m12 s12 searchResultRowChild'>

<div class='col l1 m1 s2' style='height:100%;'>
<div class='searchResultAvatarContainer'>
".
($row["avatar_picture"] == "" ? letter_avatarize($row["first_name"],"medium") : "
<div class='rotateContainer' style='position:relative;transform:none;display:inline-block;width:100%;height:100%;margin-top:".$search_result_avatar_arr_positions[0]."%;margin-left:".$search_result_avatar_arr_positions[1]."%;'>
<div class='userAvatarRotateDiv'>
<img id='".$uniq_id."' class='searchResultAvatar' src='".$row["avatar_picture"]."' alt='Avatar Picture' style='position:absolute;'/>
</div>
</div>
")
."
</div>
</div>

<div class='col l10 m11 s10 searchResultInfosContainer'>
<span class='searchResultNamesContainer'>
<span class='searchResultFullName flow-text'>
". ($str_search !== false ? substr_replace(substr_replace($row_full_name,$match_letters_opening_tag,$str_search,0),"</span>",$str_search + strlen($match_letters_opening_tag) + strlen($search_value_raw),0) : $row_full_name) ."
</span>
<span class='searchResultUserName flow-text'>@". ($str_search_2 !== false ? substr_replace(substr_replace(htmlspecialchars($row["user_name"]),$match_letters_opening_tag,$str_search_2,0),"</span>",$str_search_2 + strlen($match_letters_opening_tag) + strlen($search_value_raw),0) : $row["user_name"]) ."</span>
</span>

</div>
</div>
</div>
<script>

	$('#".$uniq_id."').on('load',function(){
		$(this).parent().css('transform','rotate(' + ". ($search_result_avatar_arr["rotate_degree"] != "" ? $search_result_avatar_arr["rotate_degree"] : 0) ." + 'deg)');
		fitToParent($(this));
		adaptRotateWithMargin($(this),". ($search_result_avatar_arr["rotate_degree"] != "" ? $search_result_avatar_arr["rotate_degree"] : 0) .",false);
	});
	
	
	Waves.attach('#searchUser".$row[0]."', ['waves-block']);
	Waves.init();

</script>";		
}


// tag search results
$tags_arr = $con->query("select * from tags where name_of like '". $search_value_raw ."%'")->fetchAll();

if(count($tags_arr) > 0) {
$echo_arr[0] .= "
<div id='searchTagsContainer'>
<div class='searchSectionTitles'>Tags</div>
<div id='searchTagsContainerChild'>
";

foreach($tags_arr as $tag) {
$echo_arr[0] .= "<div class='hashTag openTagPostsModal modal-trigger' data-target='tagPostsModal' data-tag-id='".$tag["id"]."'><span class='tagName'>". htmlspecialchars($tag["name_of"]) ."</span> <span class='tagFollowedBy'>".round($tag["followed_by"]/1000,1)."K</span></div>";			
}

$echo_arr[0] .= "</div></div>";
}


$matching_post_titles_number = $con->query("select count(id) from posts where title like '%". $search_value_raw ."%'")->fetch()[0];

if($matching_post_titles_number > 0) {
$echo_arr[0] .= "
<div id='searchPostsContainer' class='row'>
<div id='searchPostsContainerChild' class='col l10 m10 s10 offset-l1 offset-m1 offset-s1'>
<a href='#getPostsByTitleModal' class='waves-effect wavesCustom btn commonButton fullWidthAndCenteredText modal-trigger openGetPostsByTitleModal' data-search-value='". $_GET["search_value"] ."'>Post Titles (". $matching_post_titles_number .")</a>
</div>
</div>
";
}






$echo_arr[0] .= "</div>";
// if no results were found
if(count($all_search_results) == 0 && count($tags_arr) == 0 && $matching_post_titles_number < 1) {
$echo_arr[0] = "<div class='emptyNowPlaceholder'> No Results! </div>";
$echo_arr[1] = "false";	
}
else {
$echo_arr[1] = "true";	
}

echo json_encode($echo_arr);

}



?>