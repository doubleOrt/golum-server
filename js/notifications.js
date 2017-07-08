
// value will be set on document load
var NOTIFICATIONS_CONTAINER_ELEMENT;
var NOTIFICATIONS_TABS_STATE_HOLDER;
var NOTIFICATIONS_IMPORTANT_CONTAINER;
var NOTIFICATIONS_ALL_CONTAINER;


var notificationsPreventMultipleCalls = false;

function getNotifications(row_offset, callback) {
notificationsPreventMultipleCalls = true;	

$.get({
url:"components/notifications.php",
data:{
"row_offset":row_offset
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



function get_notification_markup(notification_arr) {

var notification_sender_full_name = notification_arr["notification_sender_info"]["first_name"] + " " + notification_arr["notification_sender_info"]["last_name"];
var random_num = Math.floor(Math.random() * 1000000);

// user reacting to your post (voting and like/disliking)
if(notification_arr["notification_type"] == 1) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive openSinglePost modal-trigger' data-read-yet='`+ (notification_arr[`read_yet`] != `` ? `true` : `false`) +`' data-target='singlePostModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>

` + (notification_arr["notification_read_yet"] == "0" ? "<div class='focusStealer'><i class='material-icons'>check</i></div>" : "") + `

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

` + (notification_arr["notification_read_yet"] == "0" ? "<div class='focusStealer'><i class='material-icons'>check</i></div>" : "") + `

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

` + (notification_arr["notification_read_yet"] == "0" ? "<div class='focusStealer'><i class='material-icons'>check</i></div>" : "") + `

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
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) +` Replied To Your Comment+ <br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='`+ notification_arr["notification_extra2"] +`'>View Post</a>)</span>
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}


// user sent you a post (wants to share a post with you)
if(notification_arr["notification_type"] == 4) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive openSinglePost modal-trigger' data-target='singlePostModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>

` + (notification_arr["notification_read_yet"] == "0" ? "<div class='focusStealer'><i class='material-icons'>check</i></div>" : "") + `

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

` + (notification_arr["notification_read_yet"] == "0" ? "<div class='focusStealer'><i class='material-icons'>check</i></div>" : "") + `

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

` + (notification_arr["notification_read_yet"] == "0" ? "<div class='focusStealer'><i class='material-icons'>check</i></div>" : "") + `

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

` + (notification_arr["notification_read_yet"] == "0" ? "<div class='focusStealer'><i class='material-icons'>check</i></div>" : "") + `

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
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) +` Upvoted Your Comment+<br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>View Post</a>)</span>
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}

// user downvoted your comment to a post
if(notification_arr["notification_type"] == 8) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive showPostComments modal-trigger' data-target='commentsModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-pin-comment-to-top='`+ notification_arr["notification_extra2"]  +`' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>

` + (notification_arr["notification_read_yet"] == "0" ? "<div class='focusStealer'><i class='material-icons'>check</i></div>" : "") + `

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
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) +` Downvoted Your Comment+  <br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>View Post</a>)</span>
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}

// user upvoted your reply to a comment or reply
if(notification_arr["notification_type"] == 9) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive  modal-trigger addReplyToComment' data-target='commentRepliesModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-pin-comment-to-top='`+ notification_arr["notification_extra3"]  +`' data-comment-id='`+ notification_arr["notification_extra"] +`' data-actual-post-id='`+ notification_arr["notification_extra2"] +`'>

` + (notification_arr["notification_read_yet"] == "0" ? "<div class='focusStealer'><i class='material-icons'>check</i></div>" : "") + `

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
<a href='#modal1' class='commonLink notificationFromFullName showUserModal modal-trigger' data-target='user_modal' data-user-id='` + notification_arr["notification_sender_info"]["id"] + `'>`+ notification_sender_full_name +`</a> `+ (notification_arr["notification_and_others"] > 0 ?  `And ` + notification_arr["notification_and_others"] + ` Other` + (notification_arr["notification_and_others"] == 1 ? `` : `s`) : ``) + ` Upvoted Your Reply.  <br><span class='smallerFontSize boldText'>(<a href='#singlePostModal' class='commonLink openSinglePost modal-trigger' data-actual-post-id='`+ notification_arr["notification_extra"] +`'>View Post</a>)</span>
</div>
<div class='notificationTime'>` + notification_arr["notification_time_string"] + `</div>
</div>

</div>`;
}

// user downvoted your reply to a comment
if(notification_arr["notification_type"] == 10) {
return `
<div class='singleNotification scaleHorizontallyCenteredItem myGreyBackgroundOnActive  modal-trigger addReplyToComment' data-target='commentRepliesModal' data-notification-id='`+ notification_arr["notification_id"] +`' data-pin-comment-to-top='`+ notification_arr["notification_extra3"]  +`' data-comment-id='`+ notification_arr["notification_extra"] +`' data-actual-post-id='`+ notification_arr["notification_extra2"] +`'>

` + (notification_arr["notification_read_yet"] == "0" ? "<div class='focusStealer'><i class='material-icons'>check</i></div>" : "") + `

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

` + (notification_arr["notification_read_yet"] == "0" ? "<div class='focusStealer'><i class='material-icons'>check</i></div>" : "") + `

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



function notifications_section_tabs_changed() {

var active_tab = NOTIFICATIONS_TABS_STATE_HOLDER.attr("data-active-tab");

// user switched to the PEOPLE tab
if(active_tab == "0") {
NOTIFICATIONS_ALL_CONTAINER.hide();
NOTIFICATIONS_IMPORTANT_CONTAINER.show();
}
// user switched to the TAGS tab
else if(active_tab == "1"){
NOTIFICATIONS_IMPORTANT_CONTAINER.hide();	
NOTIFICATIONS_ALL_CONTAINER.show();
}



}





$(document).ready(function(){
	
NOTIFICATIONS_CONTAINER_ELEMENT = $("#notifications_important_container");
NOTIFICATIONS_TABS_STATE_HOLDER = $("#notifications_container");
NOTIFICATIONS_IMPORTANT_CONTAINER = $("#notifications_important_container");
NOTIFICATIONS_ALL_CONTAINER = $("#notifications_all_container");

$(document).on("click", "#notifications_tabs .tab", function() {
NOTIFICATIONS_TABS_STATE_HOLDER.attr("data-active-tab", $(this).attr("data-tab-index"));	
notifications_section_tabs_changed();
});
	


// the user wants to see their notifications
$(document).on("click",".openNotificationsModal",function(){
// empty the notification container element	
NOTIFICATIONS_CONTAINER_ELEMENT.html("");	
NOTIFICATIONS_IMPORTANT_CONTAINER.attr("data-end-of-results", "false");
NOTIFICATIONS_ALL_CONTAINER.attr("data-end-of-results", "false");
showLoading(NOTIFICATIONS_CONTAINER_ELEMENT, "50%");
getNotifications(0, function(data){
get_notification_callback(data, function(){
removeLoading(NOTIFICATIONS_CONTAINER_ELEMENT);
});
});
$("#newNotificationsNumber").find(".notificationNumContainer").remove();
});
// user is infinite scrolling their notifications section 
NOTIFICATIONS_CONTAINER_ELEMENT.scroll(function(){
if($(this).attr("data-end-of-results") === "false") {	
if(($(this)[0].scrollHeight - ($(this).scrollTop() + $(this).outerHeight()) < 100) && notificationsPreventMultipleCalls == false) {
add_secondary_loading(NOTIFICATIONS_CONTAINER_ELEMENT);	
getNotifications(NOTIFICATIONS_CONTAINER_ELEMENT.find(".singleNotification").length, function(data){
get_notification_callback(data, function(){
remove_secondary_loading(NOTIFICATIONS_CONTAINER_ELEMENT);	
});
});
}
}
});


function get_notification_callback(notifications_arr, callback) {

/* if the user is not infinite scrolling and they have no notifications (only supposed to happen when the user has never had a notification, 
not when they don't have any new notifications), add a placeholder div to tell the user there have been no results. */
if(notifications_arr.length < 1 && NOTIFICATIONS_CONTAINER_ELEMENT.find(".singleNotification").length < 1) {
NOTIFICATIONS_CONTAINER_ELEMENT.html("<div class='emptyNowPlaceholder'><i class='material-icons'>info</i><br>No Notifications Yet :(</div>");	
}
else if(notifications_arr.length < 1) {
NOTIFICATIONS_CONTAINER_ELEMENT.append(get_end_of_results_mark_up("End of notifications"));	
NOTIFICATIONS_CONTAINER_ELEMENT.attr("data-end-of-results", "true");	
}
		
for(var i = 0; i < notifications_arr.length; i++) {	
NOTIFICATIONS_CONTAINER_ELEMENT.append( get_notification_markup(notifications_arr[i]) );
}

NOTIFICATIONS_CONTAINER_ELEMENT.find(".avatarImages").on("load", function(){
fitToParent($(this));
adaptRotateWithMargin($(this), $(this).parent().attr("data-rotate-degree"), false);	
});

if(typeof callback == "function") {
callback();	
}

}
	
	
});