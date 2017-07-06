
var SEND_TO_FRIEND_ROWS_CONTAINER;
var SEND_TO_FRIEND_POST_ID_HOLDER;
var SEND_TO_FRIEND_INPUT_ELEMENT;

var prevent_multiple_calls_to_get_send_to_friend_friends = false;

function sendPost(postId, recipientId, callback) {	
	
$.post({
url:"components/send_post_to_friend.php",
data:{
"post_id": postId,
"friend_id": recipientId
},
success:function(data) {
	
// post was sent successfully	
if(data == 1) {
callback();
}

}	
});

}

function get_send_to_friend_friends(search_term, post_id, row_offset, callback) {
		
if(typeof search_term == "undefined" || typeof post_id == "undefined") {
return false;	
}	

if(prevent_multiple_calls_to_get_send_to_friend_friends == false) {
	
prevent_multiple_calls_to_get_send_to_friend_friends = true;	

$.get({
url:"components/get_send_to_friend_friends.php",
data:{
"search_term": search_term.trim(),
"post_id": post_id,
"row_offset": row_offset
},
success:function(data) {
var data_arr = JSON.parse(data);
if(typeof callback == "function") {
callback(data_arr);
}
prevent_multiple_calls_to_get_send_to_friend_friends = false;
}	
});

}
	
}

function get_send_to_friend_friends_callback(data) {

// no results
if(data.length < 1 && SEND_TO_FRIEND_ROWS_CONTAINER.find(".sendToFriendSingleRow").length < 1) {
SEND_TO_FRIEND_ROWS_CONTAINER.html("<div class='emptyNowPlaceholder'><i class='material-icons'>error</i><br>Not a single friend matched your search term :(</div>");	
}

for(var i = 0; i < data.length; i++) {
SEND_TO_FRIEND_ROWS_CONTAINER.append(get_send_to_friend_row_mark_up(data[i]));	
}

SEND_TO_FRIEND_ROWS_CONTAINER.find(".avatarImages").on("load", function(){
fitToParent($(this));
adaptRotateWithMargin($(this), $(this).parent().attr("data-rotate-degree"), false);	
});


}


function get_send_to_friend_row_mark_up(data) {
	
var random_num = Math.floor(Math.random() * 1000000);	

return `<div class='sendToFriendSingleRow row'>
<div class='sendToFriendAvatarContainerParent col l1 m1 s2'>
<div class='avatarContainer'>
<div class='avatarContainerChild modal-trigger showUserModal' data-target='user_modal' data-user-id='` + data["id"] + `'>
<div class='rotateContainer' style='margin-top:` + data["avatar_positions"][0] + `%;margin-left:` + data["avatar_positions"][1] + `%;'>
<div class='avatarRotateDiv' style='transform: rotate(` + data["avatar_rotate_degree"] + `deg);'data-rotate-degree='` + data["avatar_rotate_degree"] + `'>
<img id='friendAvatar` + random_num + `' class='avatarImages sendToFriendAvatarImages' src='` + (data["avatar_picture"] != "" ? data["avatar_picture"] : LetterAvatar(data["first_name"], 120)) + `' alt='Image'/>
</div></div>
</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->
</div>
<div class='col l11 m11 s10 sendToFriendRightCol'>
<a href='#user_modal' class='friendFullName modal-trigger showUserModal' data-user-id='` + data["id"] + `'>` + (data["first_name"] + " " + data["last_name"]) + `</a><!-- end .friendFullName -->
<a href='#' class='waves-effect wavesCustom btn commonButton sendToFriendButton ` + (data["current_state"] != 0 ? "disabledButton" : "") + `' data-user-id='` + data["id"] + `'>` + (data["current_state"] != 0 ? "sent" : "send") + `</a>
</div>
</div><!-- end .sendToFriendSingleRow -->`;	
}


$(document).ready(function() {
	
	
SEND_TO_FRIEND_ROWS_CONTAINER = $(".sendToFriendContainerCol");	
SEND_TO_FRIEND_POST_ID_HOLDER = $(".sendToFriendContainerCol");	
SEND_TO_FRIEND_INPUT_ELEMENT = $("#sendToFriendInput");	
	
	

$(document).on("click",".sendPostToFriend",function(){
$(".sendToFriendContainerCol").attr("data-actual-post-id",$(this).attr("data-actual-post-id"));	
});


// user is searching for friends using the search-for-friends input in order to send them a post. 
SEND_TO_FRIEND_INPUT_ELEMENT.on("keyup",function(){

// if the value of the sendPostToFriend input is empty
if($(this).val().trim() == "") {
$(".sendToFriendContainerCol").html("<div class='emptyNowPlaceholder'><i class='material-icons'>info</i><br>Friends will appear here :)</div>");	
return false;
}	

if(typeof $(".sendToFriendContainerCol").attr("data-actual-post-id") == "undefined") {
return false;	
}

SEND_TO_FRIEND_ROWS_CONTAINER.html(""); // empty the rows container

get_send_to_friend_friends(SEND_TO_FRIEND_INPUT_ELEMENT.val(), SEND_TO_FRIEND_POST_ID_HOLDER.attr("data-actual-post-id"), 0, get_send_to_friend_friends_callback);
});
// user is infinite scrolling
SEND_TO_FRIEND_ROWS_CONTAINER.scroll(function(){
if(($(this)[0].scrollHeight - ($(this).scrollTop() + $(this).outerHeight()) < 100)) {
get_send_to_friend_friends(SEND_TO_FRIEND_INPUT_ELEMENT.val(), SEND_TO_FRIEND_POST_ID_HOLDER.attr("data-actual-post-id"), SEND_TO_FRIEND_ROWS_CONTAINER.find(".sendToFriendSingleRow").length , get_send_to_friend_friends_callback);
}
});



});