<?php
# this page is used to show the chat portals (the parts the users click to open chat modals).



require_once "common_requires.php";
require_once "logged_in_importants.php";

	
if(isset($_SESSION["user_id"])) {

$chat_portals_query = $con->query("select * from chats where chatter_ids like '%".$_SESSION["user_id"]."%' order by latest_activity desc");

$hidden_chats_query = $con->query("select * from hidden_chats where user_id = ". $_SESSION["user_id"])->fetchAll();

// we use this variable to find out if the user has any chats, by default this is set to true, meaning a user has no chats, we set it to false upon the first successful chat portal fetch.
$no_chat_portals_fetched = true;

while($row = $chat_portals_query->fetch()) {
	
$no_chat_portals_fetched = false;	
	
for($i = 0;$i<count($hidden_chats_query);$i++) {
if($hidden_chats_query[$i]["chat_id"] == $row["id"]) {
continue 2;	
}	
}	

# we use this to parse the chat recipients id.
$chat_portal_user_id = explode("-",$row["chatter_ids"])[0] == $_SESSION["user_id"] ? explode("-",$row["chatter_ids"])[1] : explode("-",$row["chatter_ids"])[0];


$current_state = $con->query("select id from blocked_users where user_ids = '".$chat_portal_user_id. "-" . $_SESSION["user_id"]."'")->fetch();	

if($current_state[0] != "") {
continue;	
}


if($con->query("SELECT * FROM account_states where user_id = ".$chat_portal_user_id)->fetch()[0] != "") {
continue;	
}	

$chat_portal_user_info_arr = $con->query("select * from users where id = ".$chat_portal_user_id)->fetch();


$chat_portal_avatar_arr = $con->query("SELECT * FROM avatars WHERE id_of_user = ".$chat_portal_user_id." order by id desc limit 1")->fetch();	
$chat_portal_avatar_positions = explode(",",$chat_portal_avatar_arr["positions"]);	


$uniq_id = rand(1000000,100000000);


echo "

<script>

	$('#".$uniq_id."').on('load',function(){
		$(this).parent().css('transform','rotate(' + ". ($chat_portal_avatar_arr["rotate_degree"] != "" ? htmlspecialchars($chat_portal_avatar_arr["rotate_degree"], ENT_QUOTES, "utf-8") : 0) ." + 'deg)');
		fitToParent($(this));
		adaptRotateWithMargin($(this),". ($chat_portal_avatar_arr["rotate_degree"] != "" ? htmlspecialchars($chat_portal_avatar_arr["rotate_degree"], ENT_QUOTES, "utf-8") : 0) .",false);
	});
	
	Waves.attach('#chatPortalTo". htmlspecialchars($chat_portal_user_id, ENT_QUOTES, "utf-8") ."', ['waves-block']);
	Waves.init();
	
</script>

<div class='wrapper' style='width:100%;'>

<div class='singleChatPortal startChat modal-trigger' id='chatPortalTo". htmlspecialchars($chat_portal_user_id, ENT_QUOTES, "utf-8") ."' data-target='chatModal' data-from='chatPortals' data-chat-id='". htmlspecialchars($row["id"], ENT_QUOTES, "utf-8") ."' data-user-id='". htmlspecialchars($chat_portal_user_id, ENT_QUOTES, "utf-8") ."'>

<div class='col l4 m3 s3 singleChatPortalAvatarCol'>
<a href='#' class='removeChat'><i class='material-icons'>close</i></a>
<div class='singleChatPortalAvatarContainer modal-trigger view-user showUserModal' data-target='modal1' data-user-id='". htmlspecialchars($chat_portal_user_id, ENT_QUOTES, "utf-8") ."'>
". ($chat_portal_user_info_arr["avatar_picture"] == "" ? letter_avatarize($chat_portal_user_info_arr["first_name"],"medium") : "
<div class='singleChatPortalRotateContainer rotateContainer' style='margin-top:". htmlspecialchars($chat_portal_avatar_positions[0], ENT_QUOTES, "utf-8") ."%;margin-left:". htmlspecialchars($chat_portal_avatar_positions[1], ENT_QUOTES, "utf-8") ."%;'>
<div class='singleChatPortalRotateDiv'>
<img class='avatarImages' id='". $uniq_id ."' src='". htmlspecialchars($chat_portal_user_info_arr["avatar_picture"], ENT_QUOTES, "utf-8") ."' alt='Avatar'/>
</div>
</div>") ."
</div><!-- end .contactsAvatarContainer -->
</div><!-- end .contactsAvatarRow -->


<div class='col l8 m9 s9 chatPortalInfosContainer'>
<div class='chatPortalInfosContainerChild'>
<div class='chatPortalFullName'>". htmlspecialchars($chat_portal_user_info_arr["first_name"] . " " . $chat_portal_user_info_arr["last_name"], ENT_QUOTES, "utf-8") ."</div>
<div class='chatPortalMessagePreview'></div>
</div>
</div><!-- end .chatPortalInfosContainer -->
<div class='latestMessageContainer'>
</div>
</div><!-- end .singleChatPortal -->
</div>
";
	
}

// if the user has no chats, tell them they have no chats.
if($no_chat_portals_fetched == true) {
echo "<div class='emptyNowPlaceholder'>
<i class='material-icons'>info</i>
<br>
No Chats Started Yet!
</div>";		
}

}



?>