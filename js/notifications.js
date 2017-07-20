
// value will be set on document load
var NOTIFICATIONS_CONTAINER_ELEMENT;
var NOTIFICATIONS_TABS_STATE_HOLDER;
var NOTIFICATIONS_IMPORTANT_CONTAINER;
var NOTIFICATIONS_ALL_CONTAINER;

var FIRST_TAB_EMPTY_NOW_MESSAGE = "No unread notifications :)";
var SECOND_TAB_EMPTY_NOW_MESSAGE = "No notifications :(";


var notificationsPreventMultipleCalls = false;

function getNotifications(row_offset, type, callback) {
notificationsPreventMultipleCalls = true;	

$.get({
url:"components/notifications.php",
data:{
"row_offset":row_offset,
"type": type
},
success:function(data) {

var data_arr = JSON.parse(data);

if(typeof callback == "function") {
callback(data_arr);
}

notificationsPreventMultipleCalls = false;
} 
});

}


function get_new_notifications_num(callback) {

if(typeof callback !== "function") {
return false;	
}	
	
$.get({
url: "components/get_new_notifications_num.php",
success:function(data) {
var data_arr = JSON.parse(data);
callback(data_arr[0]);
}	
});

}


/*

1 = voted on your post

2 = commented on your post

3 = replied to your comment

4 = sent you a post

5 = faved your post

6 = started following you

7 = upvoted your comment 

8 = downvoted your comment

9 = upvoted your reply 

10 = downvoted your reply 

11 = reacted to the post you sent him
	
*/
function get_notification_markup(notification_arr) {

var notification_sender_full_name = notification_arr["notification_sender_info"]["first_name"] + " " + notification_arr["notification_sender_info"]["last_name"];
var random_num = Math.floor(Math.random() * 1000000);

// user reacting to your post (voting and like/disliking)
if(notification_arr["notification_type"] == 1) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive openSinglePost modal-trigger' data-read-yet='`+ (notification_arr[`read_yet`] != `` ? `true` : `false`) +`' data-target='singlePostModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>
<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `' ` + notification_arr[`notification_from`] + `'>
<div class='rotateContainer' style='margin-top:`+notification_arr["notification_sender_info"]["avatar_positions"][0]+`%;margin-left:`+notification_arr["notification_sender_info"]["avatar_positions"][1]+`%;'>
<div class='avatarRotateDiv' data-rotate-degree='`+notification_arr["notification_sender_info"]["avatar_rotate_degree"]+`' style='transform: rotate(` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (notification_arr["notification_sender_info"]["avatar"] != "" ? notification_arr["notification_sender_info"]["avatar"] : LetterAvatar(notification_arr["notification_sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>

</div><!-- end +avatarContainerChild -->
</div><!-- end +avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a>  `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) +` Reacted To Your Post.
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}


// user commented on your post
if(notification_arr["notification_type"] == 2) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive showPostComments modal-trigger' data-target='commentsModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-pin-comment-to-top='`+ notification_arr["notification_extra2"]  +`' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>
<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `' ` + notification_arr[`notification_from`] + `'>
<div class='rotateContainer' style='margin-top:`+notification_arr["notification_sender_info"]["avatar_positions"][0]+`%;margin-left:`+notification_arr["notification_sender_info"]["avatar_positions"][1]+`%;'>
<div class='avatarRotateDiv' data-rotate-degree='`+notification_arr["notification_sender_info"]["avatar_rotate_degree"]+`' style='transform: rotate(` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (notification_arr["notification_sender_info"]["avatar"] != "" ? notification_arr["notification_sender_info"]["avatar"] : LetterAvatar(notification_arr["notification_sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>

</div><!-- end +avatarContainerChild -->
</div><!-- end +avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) +` Commented On Your <a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>Post</a>.
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}

// replies
if(notification_arr["notification_type"] == 3) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive  modal-trigger addReplyToComment' data-target='commentRepliesModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-pin-comment-to-top='`+ notification_arr["notification_extra3"]  +`' data-comment-id='`+ notification_arr["notification_extra"] +`' data-actual-post-id='`+ notification_arr["notification_extra2"] +`'>
<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `' ` + notification_arr[`notification_from`] + `'>
<div class='rotateContainer' style='margin-top:`+notification_arr["notification_sender_info"]["avatar_positions"][0]+`%;margin-left:`+notification_arr["notification_sender_info"]["avatar_positions"][1]+`%;'>
<div class='avatarRotateDiv' data-rotate-degree='`+notification_arr["notification_sender_info"]["avatar_rotate_degree"]+`' style='transform: rotate(` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (notification_arr["notification_sender_info"]["avatar"] != "" ? notification_arr["notification_sender_info"]["avatar"] : LetterAvatar(notification_arr["notification_sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>

</div><!-- end +avatarContainerChild -->
</div><!-- end +avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) +` Replied To Your Comment. <br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='`+ notification_arr["notification_extra2"] +`'>View Post</a>)</span>
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}


// user sent you a post (wants to share a post with you)
if(notification_arr["notification_type"] == 4) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive openSinglePost modal-trigger' data-target='singlePostModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>
<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `' ` + notification_arr[`notification_from`] + `'>
<div class='rotateContainer' style='margin-top:` + notification_arr["notification_sender_info"]["avatar_positions"][0] + `%;margin-left:`+notification_arr["notification_sender_info"]["avatar_positions"][1]+`%;'>
<div class='avatarRotateDiv' data-rotate-degree='` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `' style='transform: rotate(` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (notification_arr["notification_sender_info"]["avatar"] != "" ? notification_arr["notification_sender_info"]["avatar"] : LetterAvatar(notification_arr["notification_sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>

</div><!-- end +avatarContainerChild -->
</div><!-- end +avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> Wants To Share a Post With You.
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}

// user favorited your post
if(notification_arr["notification_type"] == 5) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive openSinglePost modal-trigger' data-target='singlePostModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>
<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `' ` + notification_arr[`notification_from`] + `'>
<div class='rotateContainer' style='margin-top:`+notification_arr["notification_sender_info"]["avatar_positions"][0]+`%;margin-left:`+notification_arr["notification_sender_info"]["avatar_positions"][1]+`%;'>
<div class='avatarRotateDiv' data-rotate-degree='`+notification_arr["notification_sender_info"]["avatar_rotate_degree"]+`' style='transform: rotate(` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (notification_arr["notification_sender_info"]["avatar"] != "" ? notification_arr["notification_sender_info"]["avatar"] : LetterAvatar(notification_arr["notification_sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>

</div><!-- end +avatarContainerChild -->
</div><!-- end +avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) +` Favorited Your Post.
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}

// user started following you
if(notification_arr["notification_type"] == 6) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive showUserModal modal-trigger' data-notification-id='`+ notification_arr["notification_id"] +`' data-target='user_modal' data-user-id='`+ notification_arr["notification_sender_info"]["id"] +`' >
<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `' ` + notification_arr[`notification_from`] + `'>
<div class='rotateContainer' style='margin-top:` + notification_arr["notification_sender_info"]["avatar_positions"][0] + `%;margin-left:`+notification_arr["notification_sender_info"]["avatar_positions"][1]+`%;'>
<div class='avatarRotateDiv' data-rotate-degree='` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `' style='transform: rotate(` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (notification_arr["notification_sender_info"]["avatar"] != "" ? notification_arr["notification_sender_info"]["avatar"] : LetterAvatar(notification_arr["notification_sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>

</div><!-- end +avatarContainerChild -->
</div><!-- end +avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a>  `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) +` Started Following You.
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}

// user upvoted your comment to a post
if(notification_arr["notification_type"] == 7) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive showPostComments modal-trigger' data-target='commentsModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-pin-comment-to-top='`+ notification_arr["notification_extra2"]  +`' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>
<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `' ` + notification_arr[`notification_from`] + `'>
<div class='rotateContainer' style='margin-top:`+notification_arr["notification_sender_info"]["avatar_positions"][0]+`%;margin-left:`+notification_arr["notification_sender_info"]["avatar_positions"][1]+`%;'>
<div class='avatarRotateDiv' data-rotate-degree='`+notification_arr["notification_sender_info"]["avatar_rotate_degree"]+`' style='transform: rotate(` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (notification_arr["notification_sender_info"]["avatar"] != "" ? notification_arr["notification_sender_info"]["avatar"] : LetterAvatar(notification_arr["notification_sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>

</div><!-- end +avatarContainerChild -->
</div><!-- end +avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) +` Upvoted Your Comment.<br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>View Post</a>)</span>
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}

// user downvoted your comment to a post
if(notification_arr["notification_type"] == 8) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive showPostComments modal-trigger' data-target='commentsModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-pin-comment-to-top='`+ notification_arr["notification_extra2"]  +`' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>
<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `' ` + notification_arr[`notification_from`] + `'>
<div class='rotateContainer' style='margin-top:`+notification_arr["notification_sender_info"]["avatar_positions"][0]+`%;margin-left:`+notification_arr["notification_sender_info"]["avatar_positions"][1]+`%;'>
<div class='avatarRotateDiv' data-rotate-degree='`+notification_arr["notification_sender_info"]["avatar_rotate_degree"]+`' style='transform: rotate(` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (notification_arr["notification_sender_info"]["avatar"] != "" ? notification_arr["notification_sender_info"]["avatar"] : LetterAvatar(notification_arr["notification_sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>

</div><!-- end +avatarContainerChild -->
</div><!-- end +avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) +` Downvoted Your Comment. <br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>View Post</a>)</span>
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}

// user upvoted your reply to a comment or reply
if(notification_arr["notification_type"] == 9) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive  modal-trigger addReplyToComment' data-target='commentRepliesModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-pin-comment-to-top='`+ notification_arr["notification_extra3"]  +`' data-comment-id='`+ notification_arr["notification_extra"] +`' data-actual-post-id='`+ notification_arr["notification_extra2"] +`'>
<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `' ` + notification_arr[`notification_from`] + `'>
<div class='rotateContainer' style='margin-top:`+notification_arr["notification_sender_info"]["avatar_positions"][0]+`%;margin-left:`+notification_arr["notification_sender_info"]["avatar_positions"][1]+`%;'>
<div class='avatarRotateDiv' data-rotate-degree='`+notification_arr["notification_sender_info"]["avatar_rotate_degree"]+`' style='transform: rotate(` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (notification_arr["notification_sender_info"]["avatar"] != "" ? notification_arr["notification_sender_info"]["avatar"] : LetterAvatar(notification_arr["notification_sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>

</div><!-- end +avatarContainerChild -->
</div><!-- end +avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) + ` Upvoted Your Reply.  <br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='`+ notification_arr["notification_extra2"] +`'>View Post</a>)</span>
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}

// user downvoted your reply to a comment
if(notification_arr["notification_type"] == 10) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive  modal-trigger addReplyToComment' data-target='commentRepliesModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-pin-comment-to-top='`+ notification_arr["notification_extra3"]  +`' data-comment-id='`+ notification_arr["notification_extra"] +`' data-actual-post-id='`+ notification_arr["notification_extra2"] +`'>
<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `' ` + notification_arr[`notification_from`] + `'>
<div class='rotateContainer' style='margin-top:`+notification_arr["notification_sender_info"]["avatar_positions"][0]+`%;margin-left:` + notification_arr["notification_sender_info"]["avatar_positions"][1] + `%;'>
<div class='avatarRotateDiv' data-rotate-degree='`+notification_arr["notification_sender_info"]["avatar_rotate_degree"]+`' style='transform: rotate(` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (notification_arr["notification_sender_info"]["avatar"] != "" ? notification_arr["notification_sender_info"]["avatar"] : LetterAvatar(notification_arr["notification_sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>

</div><!-- end +avatarContainerChild -->
</div><!-- end +avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) +` Downvoted Your Reply.  <br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='`+ notification_arr["notification_extra2"] +`'>View Post</a>)</span>
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}

// user reacted to a post you sent to him
if(notification_arr["notification_type"] == 11) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive openSinglePost modal-trigger' data-target='singlePostModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>
<div class='notificationsAvatarContainer'>
<div class='avatarContainer'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `' ` + notification_arr[`notification_from`] + `'>
<div class='rotateContainer' style='margin-top:`+ notification_arr["notification_sender_info"]["avatar_positions"][0]+`%;margin-left:`+notification_arr["notification_sender_info"]["avatar_positions"][1]+`%;'>
<div class='avatarRotateDiv' data-rotate-degree='`+ notification_arr["notification_sender_info"]["avatar_rotate_degree"]+`' style='transform: rotate(` + notification_arr["notification_sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (notification_arr["notification_sender_info"]["avatar"] != "" ? notification_arr["notification_sender_info"]["avatar"] : LetterAvatar(notification_arr["notification_sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>

</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div> 

<div class='notificationTextContainer'>
<div class='notificationText'>
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> Reacted To The Post Sent By You.
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}

}



function get_notification_callback(notifications_arr, empty_now_message, container_element, callback) {

/* if the user is not infinite scrolling and they have no notifications (only supposed to happen when the user has never had a notification, 
not when they don't have any new notifications), add a placeholder div to tell the user there have been no results. */
if(notifications_arr.length < 1 && container_element.find(".singleNotification").length < 1) {
container_element.html("<div class='emptyNowPlaceholder'><i class='material-icons'>info</i><br>" + empty_now_message + "</div>");	
}
else if(notifications_arr.length < 1) {
container_element.append(get_end_of_results_mark_up("End of notifications"));	
container_element.attr("data-end-of-results", "true");	
}
		
for(var i = 0; i < notifications_arr.length; i++) {	
container_element.append( get_notification_markup(notifications_arr[i]) );
}

container_element.find(".avatarImages").on("load", function(){
fitToParent($(this));
adaptRotateWithMargin($(this), $(this).parent().attr("data-rotate-degree"), false);	
});

if(typeof callback == "function") {
callback();	
}

}



function notifications_section_tabs_changed() {

var active_tab = NOTIFICATIONS_TABS_STATE_HOLDER.attr("data-active-tab");

FIRST_TAB_NOTIFICATIONS_CONTAINER.html("");
FIRST_TAB_NOTIFICATIONS_CONTAINER.attr("data-end-of-results", "false");
SECOND_TAB_NOTIFICATIONS_CONTAINER.html("");
SECOND_TAB_NOTIFICATIONS_CONTAINER.attr("data-end-of-results", "false");

// user switched to the PEOPLE tab
if(active_tab == "0") {
SECOND_TAB_NOTIFICATIONS_CONTAINER.hide();
FIRST_TAB_NOTIFICATIONS_CONTAINER.show();
getNotifications(0, 0, function(data){
get_notification_callback(data, FIRST_TAB_EMPTY_NOW_MESSAGE, FIRST_TAB_NOTIFICATIONS_CONTAINER, function(){
removeLoading(FIRST_TAB_NOTIFICATIONS_CONTAINER);
});
});
}
// user switched to the TAGS tab
else if(active_tab == "1"){
FIRST_TAB_NOTIFICATIONS_CONTAINER.hide();	
SECOND_TAB_NOTIFICATIONS_CONTAINER.show();
getNotifications(0, 1, function(data){
get_notification_callback(data, SECOND_TAB_EMPTY_NOW_MESSAGE, SECOND_TAB_NOTIFICATIONS_CONTAINER, function(){
removeLoading(SECOND_TAB_NOTIFICATIONS_CONTAINER);
});
});
}



}


function there_are_new_notifications(data) {


// if the user is not in the notifications section, then just update the new-notifications badge.
if(check_if_main_screen_is_open("main_screen_notifications") == false) {
get_new_notifications_num(function(num) {	
if(parseFloat(num) > 0) {
NEW_NOTIFICATIONS_NUM_CONTAINER.html(num).show();	
}
});	
}
else {	
if(NOTIFICATIONS_TABS_STATE_HOLDER.attr("data-active-tab") == "0") {
FIRST_TAB_NOTIFICATIONS_CONTAINER.scrollTop("0");		
FIRST_TAB_NOTIFICATIONS_CONTAINER.find(".emptyNowPlaceholder").remove();	
FIRST_TAB_NOTIFICATIONS_CONTAINER.prepend(get_notification_markup(data));
FIRST_TAB_NOTIFICATIONS_CONTAINER.find(".avatarImages").on("load", function(){
fitToParent($(this));
adaptRotateWithMargin($(this), $(this).parent().attr("data-rotate-degree"), false);	
});
}	
else {
SECOND_TAB_NOTIFICATIONS_CONTAINER.scrollTop("0");		
SECOND_TAB_NOTIFICATIONS_CONTAINER.find(".emptyNowPlaceholder").remove();		
SECOND_TAB_NOTIFICATIONS_CONTAINER.prepend(get_notification_markup(data));
SECOND_TAB_NOTIFICATIONS_CONTAINER.find(".avatarImages").on("load", function(){
fitToParent($(this));
adaptRotateWithMargin($(this), $(this).parent().attr("data-rotate-degree"), false);	
});	
}
set_notifications_read_yet_to_true(data["notification_id"]);
}

}



// use this function to set a message from unread to read.
function set_notifications_read_yet_to_true(notification_id) {
	
console.log(notification_id);	
	
if(typeof notification_id == "undefined") {
return false;	
}	

$.post({
url: "components/set_notifications_read_yet_to_true.php",
data: {
"notification_id": notification_id
},
success:function(data) {
console.log(data);	
}
});

}



$(document).ready(function(){
	
FIRST_TAB_NOTIFICATIONS_CONTAINER = $("#first_tab_notifications_container");
SECOND_TAB_NOTIFICATIONS_CONTAINER = $("#second_tab_notifications_container");
NOTIFICATIONS_TABS_STATE_HOLDER = $("#notifications_container");
NOTIFICATIONS_IMPORTANT_CONTAINER = $("#notifications_important_container");
NOTIFICATIONS_ALL_CONTAINER = $("#notifications_all_container");
NEW_NOTIFICATIONS_NUM_CONTAINER = $("#new_notifications_num");

get_new_notifications_num(function(num) {	
if(parseFloat(num) > 0) {
NEW_NOTIFICATIONS_NUM_CONTAINER.html(num).show();	
}
});


$(document).on("click", "#notifications_tabs .tab", function() {
NOTIFICATIONS_TABS_STATE_HOLDER.attr("data-active-tab", $(this).attr("data-tab-index"));		
notifications_section_tabs_changed();
});


// the user wants to see their unread notifications
$(document).on("click",".openNotificationsModal",function(){	
	
// empty the notification container element	
FIRST_TAB_NOTIFICATIONS_CONTAINER.html("");	
SECOND_TAB_NOTIFICATIONS_CONTAINER.html("");	
FIRST_TAB_NOTIFICATIONS_CONTAINER.attr("data-end-of-results", "false");
SECOND_TAB_NOTIFICATIONS_CONTAINER.attr("data-end-of-results", "false");

var notifications_active_tab = parseFloat(NOTIFICATIONS_TABS_STATE_HOLDER.attr("data-active-tab"));

var notifications_container;
var request_notifications_type;
var empty_now_message;

if(notifications_active_tab == "0") {
request_notifications_type = 0;	
notifications_container = FIRST_TAB_NOTIFICATIONS_CONTAINER;
empty_now_message = FIRST_TAB_EMPTY_NOW_MESSAGE;
}
else if(notifications_active_tab == "1") {
request_notifications_type = 1;
notifications_container = SECOND_TAB_NOTIFICATIONS_CONTAINER;
empty_now_message = SECOND_TAB_EMPTY_NOW_MESSAGE;
}

showLoading(notifications_container, "50%");

getNotifications(0, request_notifications_type, function(data){
get_notification_callback(data, empty_now_message, notifications_container, function(){
removeLoading(notifications_container);
// remove the new notifications badge
NEW_NOTIFICATIONS_NUM_CONTAINER.hide();
});
});
$("#newNotificationsNumber").find(".notificationNumContainer").remove();
});
// user is infinite scrolling their notifications section 
FIRST_TAB_NOTIFICATIONS_CONTAINER.scroll(function(){
if($(this).attr("data-end-of-results") === "false") {	
if(($(this)[0].scrollHeight - ($(this).scrollTop() + $(this).outerHeight()) < 100) && notificationsPreventMultipleCalls == false) {
add_secondary_loading(FIRST_TAB_NOTIFICATIONS_CONTAINER);	
getNotifications(FIRST_TAB_NOTIFICATIONS_CONTAINER.find(".singleNotification").length, 0, function(data){
get_notification_callback(data, FIRST_TAB_EMPTY_NOW_MESSAGE,  FIRST_TAB_NOTIFICATIONS_CONTAINER, function(){
remove_secondary_loading(FIRST_TAB_NOTIFICATIONS_CONTAINER);	
});
});
}
}
});


// user is infinite scrolling their notifications section 
SECOND_TAB_NOTIFICATIONS_CONTAINER.scroll(function(){
if($(this).attr("data-end-of-results") === "false") {	
if(($(this)[0].scrollHeight - ($(this).scrollTop() + $(this).outerHeight()) < 100) && notificationsPreventMultipleCalls == false) {
add_secondary_loading(SECOND_TAB_NOTIFICATIONS_CONTAINER);	
getNotifications(SECOND_TAB_NOTIFICATIONS_CONTAINER.find(".singleNotification").length, 1, function(data){
get_notification_callback(data, SECOND_TAB_EMPTY_NOW_MESSAGE, SECOND_TAB_NOTIFICATIONS_CONTAINER, function(){
remove_secondary_loading(SECOND_TAB_NOTIFICATIONS_CONTAINER);	
});
});
}
}
});


	
	
});