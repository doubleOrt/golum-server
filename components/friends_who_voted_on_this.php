<?php 
// calls are made to this page to get the friends of a user who have voted on the same post the user has.

require_once "common_requires.php";
require_once "logged_in_importants.php";

if(isset($_GET["post_id"]) && filter_var($_GET["post_id"], FILTER_VALIDATE_INT) !== false) {
	
$post_arr = $con->query("select id,type from posts where id = ". $_GET["post_id"])->fetch();	

$echo_arr = [];

$pin_these_users = (isset($_GET["pin_these_users"]) ? $_GET["pin_these_users"] : []);

// if the user has voted on this post.
if($con->query("select id from post_votes where user_id = ". $_SESSION["user_id"] ." and post_id = ". $_GET["post_id"])->fetch()[0] != "") {
for($i = 0;$i < ($post_arr["type"] == "1" ? 2 : $post_arr["type"]);$i++) {
$echo_arr[$i] = [];	
$echo_arr[$i][0] = $i;
$friends_who_voted_on_this = $con->query("select users.id, users.first_name, avatars.avatar_path, avatars.positions, avatars.rotate_degree from users left join avatars on users.id = avatars.id_of_user where users.id in (select user_id from post_votes where post_id = ". $_GET["post_id"] ." and option_index = ". $i ." and user_id in (select contact from contacts where contact_of = ". $_SESSION["user_id"] ."))")->fetchAll();
$echo_arr[$i][1] = get_friends_who_voted_on_this_markup($friends_who_voted_on_this,$pin_these_users);
}

}	

echo json_encode($echo_arr);
}

function get_friends_who_voted_on_this_markup($users_arr,$pin_users_to_beginning) {

$echo = "";

/* remember that this div's width is pretty sensitive, as far as i know, this is the only way to do this, you must calculate its width manually, so in this case, since it only 
contains .avatarContainer divs, hence, the formula right now is the count($users_arr) * [their width including margin]. this formula has to change if more children are added or 
the width of these .avatarContainer divs is increased. */
$echo .= "<div class='avatarsHorizontalContainer'>
<div class='avatarsHorizontalContainerChild' style='width:". (count($users_arr) * 33) ."px'>";

for($i = 0;$i < count($users_arr);$i++) {
for($x = 0;$x < count($pin_users_to_beginning);$x++) {
if($users_arr[$i]["id"] == $pin_users_to_beginning[$x]) {
$echo .= getAvatarContainer($users_arr[$i]);	
array_splice($users_arr,$i,1);
$i--;	
}	
}	
}

foreach($users_arr as $user_arr) {
$echo .= getAvatarContainer($user_arr);
}

$echo .= "</div><!-- end .avatarsHorizontalContainerChild -->
</div><!-- end .avatarsHorizontalContainer -->";

return $echo;	
}

	
function getAvatarContainer($user_arr) {
$random_num = rand(1000000,10000000);	
$user_avatar_positions = explode(",",htmlspecialchars($user_arr["positions"], ENT_QUOTES, "utf-8"));
//if avatar positions does not exist 
if(count($user_avatar_positions) < 2) {
$user_avatar_positions = [0,0];
}

return "<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". htmlspecialchars($user_arr["id"], ENT_QUOTES, "utf-8") ."'>
". ($user_arr["avatar_path"] == "" ? letter_avatarize($user_arr["first_name"],"small") : "
<div class='rotateContainer' style='margin-top:". htmlspecialchars($user_avatar_positions[0], ENT_QUOTES, "utf-8") ."%;margin-left:". htmlspecialchars($user_avatar_positions[1], ENT_QUOTES, "utf-8") ."%;'>
<div class='avatarRotateDiv' data-rotate-degree='". htmlspecialchars($user_arr["rotate_degree"], ENT_QUOTES, "utf-8") ."'>
<img id='friendsWhoVotedThisAvatar".$random_num."' class='avatarImages' src='". htmlspecialchars($user_arr["avatar_path"], ENT_QUOTES, "utf-8") ."' alt='Image'/>
</div>
</div>") ."
</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
<script>
$('#friendsWhoVotedThisAvatar". $random_num ."').on('load',function(){	
fitToParent('#friendsWhoVotedThisAvatar". $random_num ."');	
});
</script>";		
}	
	

?>