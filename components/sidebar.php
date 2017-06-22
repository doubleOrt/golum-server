<?php 

require_once "common_requires.php";

if(isset($_SESSION["user_id"])) {
	
require_once "logged_in_importants.php";

echo "
<li class='userViewListItem'>
<div class='userView' style='background:url(\"". ($user_info_arr["background_path"] == "" ? "nature/".rand(1,10).".jpg" : $user_info_arr["background_path"]) ."\");background-size:cover;background-position:center center;padding-top:15% !important;padding-bottom:15% !important;'>
<div id='userViewChild'>
<div id='userAvatarContainer' data-target='modal1' data-user-id='".$user_info_arr["id"]."' class='modal-trigger view-user showUserModal'>
". ($user_info_arr["avatar_picture"] == "" ? letter_avatarize($user_info_arr["first_name"],"large") : "
<div class='rotateContainer' style='position:relative;transform:none;display:inline-block;width:100%;height:100%;margin-top:".$base_user_avatar_positions[0]."%;margin-left:".$base_user_avatar_positions[1]."%;'>
<div class='userAvatarRotateDiv avatarRotateDiv baseUserAvatarRotateDivs' data-rotate-degree='". $base_user_avatar_arr["rotate_degree"] ."'>
<img id='userAvatar' class='avatarImages' src='".$user_info_arr["avatar_picture"]."' alt='hello'/>
</div>
</div><!-- .end #rotateContainer -->" ) 
."
</div>
<div id='userInfoContainer'>
<a href='#'><span class='baseUserFullNameContainers'>".htmlspecialchars($user_full_name)."</span></a>
<a href='#'>@<span class='sideBarUserName baseUserUserNameContainers'>".htmlspecialchars($user_info_arr['user_name'])."</span></a>
</div>
</div>
</div>
</li>
<li><a class='subheader userRelated'>User Related</a></li>		
<li><a href='#' id='sidebarChatPortalGetter' class=' getChatPortal showLoadingOnClick'><i class='material-icons'>message</i>Messages</a></li>
<li><a href='#contactsModal' class=' modal-trigger contactsButton'><i class='material-icons'>people</i>My Followings</a></li>
<li><a href='#followingMeModal' class=' modal-trigger followingMeButton'><i class='material-icons'>people_outline</i>Following Me</a></li>
<li><a href='#settingsModal' class=' modal-action modal-trigger'><i class='material-icons'>settings</i>Settings</a></li>
<li><a id='logOutButton' href='#' class=''><i class='material-icons'>cancel</i>Log Out</a></li>";
}

?>
  

 