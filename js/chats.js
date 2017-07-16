
// we use this variable to prevent multiple calls to "previous_messages.php", otherwise there would be duplicate messages, be sure to set this variable to true whenever you call "showPreviousMessages()", and set it to false after a successful return in the function.
var chat_prevent_multiple_calls = false;

var startChatId;
var startChatRecipientId;

// this function is called whenever a chat modal is opened, and whenever the user wants to see previous messages.
function start_chat(chat_id, user_id, unhide_chat_if_hidden, row_offset, callback) {	

if(typeof callback != "function" || typeof row_offset == "undefined") {
return false;
}


if(chat_prevent_multiple_calls == false) {
									
var dataObj = {};

dataObj["row_offset"] = row_offset;
dataObj["unhide_chat_if_hidden"] = unhide_chat_if_hidden;

if(typeof chat_id != "undefined") {
dataObj["chat_id"] = chat_id;	
}
if(typeof user_id != "undefined") {
dataObj["user_id"] = user_id;	
}

chat_prevent_multiple_calls = true;	

$.get({
url:"components/start_chat.php",
data:dataObj,
type:"get",
success: function(data) {	
var data_arr = JSON.parse(data);
callback(data_arr);
chat_prevent_multiple_calls = false;
}
});
}

}

function start_chat_callback(is_infinite_scroll, data) {
		
if(data.length > 1) {	
$("#emojisContainer").appendTo(".chatModalContentChild");	
$("#recipient_name").html(data[1]["recipient_first_name"]);	
$("#recipient_current_status").attr("data-current-status", data[1]["recipient_current_status"]);	
$("#recipient_current_status").html(data[1]["recipient_current_status_string"]);	
$("#sendMessage").attr("data-chat-id", data[1]["chat_id"]);
last_opened_chat_id = data[1]["chat_id"];
}	

// this chat has no messages
if(data[0].length < 1 && $(".chatWindowChild .messageContainer").length < 1) {
$(".chatWindowChild").html("<div class='emptyNowPlaceholder'><i class='material-icons'>info</i><br>This chat has no messages</div>");	
}

/* all this (new/old)_scroll_height stuff is to take care of scrolling back to the element that was the 
oldest message before this query (because jquery scrolls to the top on prepending). */
if(is_infinite_scroll === true) {
var old_scroll_height = $(".chatWindowChild")[0].scrollHeight;
}

/* needs to be "prepend" instead of "append", because of the scrolling nature of the chat modal, 
which is different than the rest of the modals, in that you scroll to the top to see the older messages, 
while in the other modals, you scroll to the bottom to see the older whatevers. */
for(var i = 0; i < data[0].length; i++) {	

$(".chatWindowChild").prepend(get_message_markup(data[0][i]));	
}


if(is_infinite_scroll === false) {
// scroll to the bottom
$('.chatWindowChild').scrollTop($('.chatWindowChild')[0].scrollHeight);	
}
else if(is_infinite_scroll === true) {
var new_scroll_height = $(".chatWindowChild")[0].scrollHeight;
$(".chatWindowChild").scrollTop(new_scroll_height - old_scroll_height);
}

getChatPortalActivities(updateChatPortalActivities);
}



function getNewMessages(chatId, lastMessage) {
	
if(typeof chatId == "undefined" || typeof lastMessage == "undefined") {
return false;
}	
	
$.get({	
type:"get",
url:"components/new_messages.php",
data:{
"chat_id": chatId,
"last_message": lastMessage
},
success:function(data) {	
$(".chatWindowChild").append(data);
// scroll to the bottom
$('.chatWindowChild').scrollTop($('.chatWindowChild')[0].scrollHeight);
}
});

}



function switchChatModalSendButton(switchButtonTo) {

// the button can now be used to send images.
if(switchButtonTo == 0) {
$("#sendMessage i").html("camera_alt");	
$("#sendMessage").attr("data-file-or-send","0");		
}
// the button can now be used to send text.
else if(switchButtonTo == 1) {
$("#sendMessage i").html("send");	
$("#sendMessage").attr("data-file-or-send","1");		
}
	
}




function sendMessage(chatId, message, messageType, callback) {

$.post({
url: 'components/send_message.php',
data: {
"chat_id": chatId,
"message": message,
"type": messageType
},
success: function(data){	
var data_arr = JSON.parse(data);
if(typeof callback == "function") {
callback(data_arr);	
}
}
}); 
	
}


function sendTextMessage(chatId, message, callback) {

if(typeof chatId == "undefined" || typeof message == "undefined" || message.trim() == "" || typeof $("#sendMessage").attr("data-chat-id") == "undefined") {
return false;	
}

if(message.length > 400) {
Materialize.toast("Message Must Be Smaller Than 400 Characters",4000,"red");	
return false;
}

sendMessage(chatId, message, "text-message", callback);	
}


function sendImage(callback) {
var imageSizeLimit = 5000000;

var sendImageType = $("#sendImage")[0].files[0]["type"];
var sendImageSize = $("#sendImage")[0].files[0]["size"];	

if(sendImageType == "image/jpeg" || sendImageType == "image/jpg" || sendImageType == "image/png" || sendImageType == "image/gif") {
if(sendImageSize < imageSizeLimit) {					
if(sendImageSize > 1) {

var data = new FormData();
data.append("the_file", $("#sendImage")[0].files[0]);
data.append("chat_id",$("#sendMessage").attr("data-chat-id"));

$.post({
url: 'components/sendFiles.php',
data: data,
cache: false,
contentType: false,
processData: false,
success: function(data){
var data_arr = JSON.parse(data);	
if(typeof callback == "function") {
callback(data_arr);	
}	
}
}); 

}
else {
Materialize.toast("Sorry, There Is Something Wrong With Your Picture",4000,"red");	
}
}
else {
Materialize.toast("Image Size Must Be Smaller Than 5MB",6000,"red");
}	
}
else {
Materialize.toast("Image Type Must Be Either \"JPG\", \"PNG\" Or \"GIF\" !",6000,"red");	
}

}



function get_message_markup(data) {

var random_num = Math.floor(Math.random()*1000000);

// text-message
if(data["message_type"] == "0") {
/* a bit weird, but that "message + 1/0" classnaming logic is months old, if i were to recreate this now, the role 
of 1 and 0 would be reverted, with 1 referring to the base user, and 0 to the recipient */
return `
<div class='messageContainer message`+ (data["message_sent_by_base_user"] != "1" ? "1" : "0") +`' id='message` + random_num + `' data-message-id='` + data["message_id"] + `'>

`+ ((data["message_sent_by_base_user"] == "0" && data["message_is_first_in_sequence"] == "1") ? `

<div class='avatarContainer chatRecipientAvatar'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + data["sender_info"]["id"] + `'>
<div class='rotateContainer' style='margin-top:` + data["sender_info"]["avatar_positions"][0] + `%;margin-left:` + data["sender_info"]["avatar_positions"][1] +`%;'>
<div class='avatarRotateDiv' data-rotate-degree='` + data["sender_info"]["avatar_rotate_degree"] + `' style='transform: rotate(` + data["sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='chat_avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (data["sender_info"]["avatar"] != "" ? data["sender_info"]["avatar"] : LetterAvatar(data["sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>
</div>
</div>` : "") + `

<div class='message'>
` + data["message"] + `
</div>
<div class='messageDate'>
` + data["time_string"] + `
</div>

<script>
	$('#chat_avatar` + random_num + `').on('load',function(){
		fitToParent($(this));
		adaptRotateWithMargin($(this),` + (data["sender_info"]["avatar_rotate_degree"] != "" ? data["sender_info"]["avatar_rotate_degree"] : 0) + `,false);
	});
</script>

</div><!-- end .messageContainer -->`;
}
else if(data["message_type"] == "1") {
return `<div class='messageContainer emojiMessageContainer message`+ (data["message_sent_by_base_user"] != "1" ? "1" : "0") +`' id='message` + random_num + `' data-message-id='` + data["message_id"] + `'>

`+ ((data["message_sent_by_base_user"] == "0" && data["message_is_first_in_sequence"] == "1") ? `

<div class='avatarContainer chatRecipientAvatar'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + data["sender_info"]["id"] + `'>
<div class='rotateContainer' style='margin-top:` + data["sender_info"]["avatar_positions"][0] + `%;margin-left:` + data["sender_info"]["avatar_positions"][1] +`%;'>
<div class='avatarRotateDiv' data-rotate-degree='` + data["sender_info"]["avatar_rotate_degree"] + `' style='transform: rotate(` + data["sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='chat_avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (data["sender_info"]["avatar"] != "" ? data["sender_info"]["avatar"] : LetterAvatar(data["sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>
</div>
</div>` : "") + `


<div class='message emojiMessage ` + (data["message_sent_by_base_user"] == "0" && data["read_yet"] == "0" ? "unreadEmoji" : "") + `'>
<img src='` + data["message"] + `' alt='Emoji'/>
</div>
<div class='messageDate'>
` + data["time_string"] + `
</div>

</div><!-- end messageContainer -->

<script>
	$('#chat_avatar` + random_num + `').on('load',function(){
		fitToParent($(this));
		adaptRotateWithMargin($(this),` + (data["sender_info"]["avatar_rotate_degree"] != "" ? data["sender_info"]["avatar_rotate_degree"] : 0) + `,false);
	});
</script>

</div><!-- end .messageContainer -->
`;	
}
else if(data["message_type"] == "2") {
return `<div class='messageContainer imageMessageContainer message`+ (data["message_sent_by_base_user"] != "1" ? "1" : "0") +`' id='message` + random_num + `' data-message-id='` + data["message_id"] + `'>

`+ ((data["message_sent_by_base_user"] == "0" && data["message_is_first_in_sequence"] == "1") ? `

<div class='avatarContainer chatRecipientAvatar'>
<div class='avatarContainerChild showUserModal modal-trigger' data-target='user_modal' data-user-id='` + data["sender_info"]["id"] + `'>
<div class='rotateContainer' style='margin-top:` + data["sender_info"]["avatar_positions"][0] + `%;margin-left:` + data["sender_info"]["avatar_positions"][1] +`%;'>
<div class='avatarRotateDiv' data-rotate-degree='` + data["sender_info"]["avatar_rotate_degree"] + `' style='transform: rotate(` + data["sender_info"]["avatar_rotate_degree"] + `deg)'>
<img id='chat_avatar` + random_num + `' class='avatarImages notificationAvatarImages' src='` + (data["sender_info"]["avatar"] != "" ? data["sender_info"]["avatar"] : LetterAvatar(data["sender_info"]["first_name"] , 60) ) + `' alt='Image'/>
</div>
</div>
</div>
</div>` : "") + `

<div class='fileMessageContainer'>
<img id='file` + random_num + `' src='` + data["message"] + `' alt='File' />
</div><!-- end .fileMessageContainer -->
<div class='messageDate'>
` + data["time_string"] + `
</div>

<script>
	$('#chat_avatar` + random_num + `').on('load',function(){
		fitToParent($(this));
		adaptRotateWithMargin($(this),` + (data["sender_info"]["avatar_rotate_degree"] != "" ? data["sender_info"]["avatar_rotate_degree"] : 0) + `,false);
	});
</script>

</div><!-- end .messageContainer -->
`;	
}
}




var last_opened_chat_id = 0;
function unsubscribe_from_last_chat() {
if(last_opened_chat_id != 0) {	
websockets_con.unsubscribe("chat_" + last_opened_chat_id);		
// so that we don't get an error in case we unsubscribe twice (happens when you close a chat using the close button and then open a new chat).
last_opened_chat_id = 0;
}
}






$(document).ready(function(){



// when a user clicks on the start chat button or the chat icon in the usermodals
$(document).on("click",".startChat",function(e){
	
if((typeof $(this).attr("data-chat-id") == "undefined" || typeof $(this).attr("data-user-id") == "undefined") && typeof $(this).attr("data-from") == "undefined") {
return false;	
}

var unhide_chat_if_hidden;
if($(this).attr("data-from") == "userModal") {
unhide_chat_if_hidden = true;
}
else {
unhide_chat_if_hidden = false;
}

var chat_id;
if(typeof $(this).attr("data-chat-id") != "undefined") {
chat_id = $(this).attr("data-chat-id");	
}

var recipient_id;
if(typeof $(this).attr("data-user-id") != "undefined") {
recipient_id = $(this).attr("data-user-id");
}

$(".chatWindowChild").html("");

start_chat(chat_id, recipient_id, unhide_chat_if_hidden, 0, function(data){

/* we don't need to receive any updates from the last chat since the user just opened a new one and 
overwrote the last one with it (this line of code is necessary for cases where the user opens a new chat 
not by closing their current one and selecting a new one from the chat portals, but by sneaking their way 
to a chat portal without closing their current chat). */
unsubscribe_from_last_chat();
	
start_chat_callback(false, data);
// we want to update the badge on the user's profile that displays the number of their unread messages each time they view some of those unread messages.
get_new_messages_num(function(num) {
if(parseFloat(num) > 0) {
USER_PROFILE_NEW_MESSAGES_NUM.html(num).css("display", "inline-block");	
}
else {
USER_PROFILE_NEW_MESSAGES_NUM.html(num).hide();	
}
});


websockets_con.subscribe('chat_' + data[1]["chat_id"], function(topic, data) {
console.log(data);	
/* this conditional prevents the message from being duplicate-added to the original sender's chat modal, since the sender has already appended the message onto their chatModal directly after they pressed the "send" button, there is no need to append another one that is broadcasted by our websocket architecture. Another way would be to not send the websocket message to the sender in the back-end, but we went with this solution. */
if($(".messageContainer[data-message-id='" + data["message_id"] + "']").length < 1) {	
$(".chatWindowChild").find(".emptyNowPlaceholder").remove();	
$(".chatWindowChild").append(get_message_markup(data));	
$('.chatWindowChild').scrollTop($('.chatWindowChild')[0].scrollHeight);		
}
});

});
});
$(".chatWindowChild").scroll(function(event){
if($(this).scrollTop() == 0) {
start_chat($("#sendMessage").attr("data-chat-id"), undefined, true, $(".chatWindowChild .messageContainer").length, function(data){
start_chat_callback(true, data);
// we want to update the badge on the user's profile that displays the number of their unread messages each time they view some of those unread messages.
get_new_messages_num(function(num) {
if(parseFloat(num) > 0) {
USER_PROFILE_NEW_MESSAGES_NUM.html(num).css("display", "inline-block");	
}
else {
USER_PROFILE_NEW_MESSAGES_NUM.html(num).hide();	
}
});
	
});
}
});


$(document).on("click",".chatModalCloseButton",function() {	
/* bugs.txt #5 */
chat_prevent_multiple_calls = true;
setTimeout(function(){
chat_prevent_multiple_calls = false;	
}, 100);
$(".chatWindowChild").html("");
$("#recipient_name").html("");
$("#recipient_current_status").html("");
// we don't need to receive any updates from the last chat since the user just closed it.
unsubscribe_from_last_chat();
});


// on double tapping, toggle .emojisContainer's display.
$(document).on("doubletap",".chatWindowChild",function(e){
setTimeout(function(){
$("#emojisContainer").toggle();	
},50);
});


// when a user is writing a message we call this, if the message is currently empty, we change the send message button to a send photo button, otherwise we change it to a send mesage button.
$(document).on("keyup",".messageTextarea",function(){

// if the new value of this element is empty, then we want to change our button to a send image button
if($(this).val().trim() == "") {
switchChatModalSendButton(0);	
}
// this means the user is typing in a message, so we need to change our button to a send message button.
else {	
switchChatModalSendButton(1);
}

});


// hide the .emojisContainer when its sides are clicked.	
$("#emojisContainer").click(function(event){
if(event.target.tagName != "IMG") {	
$(this).hide();	
}
});

// when the user presses the #chatModal's send message button.
$(document).on("click","#sendMessage",function(){

// if the user wants to send an image
if($(this).attr("data-file-or-send") == "0") {
$("#sendImage").click();
return;	
}
// user wants to send a text message
else {
switchChatModalSendButton(0);
sendTextMessage($("#sendMessage").attr("data-chat-id"), $(".messageTextarea").val(), function(data){
$(".chatWindowChild").find(".emptyNowPlaceholder").remove();	
$(".chatWindowChild").append(get_message_markup(data[0][0]));	
$('.chatWindowChild').scrollTop($('.chatWindowChild')[0].scrollHeight);	
});
$(".messageTextarea").val("");	
}

});

// user wants to send an emoji
$(document).on("click",".emoji",function(e){	
$("#emojisContainer").fadeOut();
sendMessage($("#sendMessage").attr("data-chat-id"), $(this).attr("src"), "emoji-message", function(data){
$(".chatWindowChild").find(".emptyNowPlaceholder").remove();	
$(".chatWindowChild").append(get_message_markup(data[0][0]));	
$('.chatWindowChild').scrollTop($('.chatWindowChild')[0].scrollHeight);	
});
});

// when users want to send files (images).
$(document).on("change","#sendImage",function(){
sendImage(function(data){
// there were no errors	
if(data[1] == "0") {	
$(".chatWindowChild").find(".emptyNowPlaceholder").remove();
$('.chatWindowChild').append(get_message_markup(data[0][0]));
// scroll to the bottom
$('.chatWindowChild').scrollTop($('.chatWindowChild')[0].scrollHeight);		
}
else {
Materialize.toast(data[1], 6000, "red");	
}
});
});

// user wants to open an image-message in fullscreen
$(document).on("click",".fileMessageContainer",function(){
openFullScreenFileView($(this).find("img").attr("src"));
});


var message_touchdown_timeout; 
var this_message;
$(document).on("touchstart", ".message", function(){
this_message = $(this);	
message_touchdown_timeout = setTimeout(function(){
this_message.css("opacity", ".6");
this_message.parents(".messageContainer").find(".messageDate").css("display", "inline-block");		
}, 150);	
}).on("touchend", ".message", function(){
clearTimeout(message_touchdown_timeout);	
this_message.css("opacity", "1");
this_message.parents(".messageContainer").find(".messageDate").css("display", "none");		
});




var file_message_touchdown_timeout;
var this_message;
$(document).on("touchstart", ".fileMessageContainer", function(){
this_message = $(this);	
file_message_touchdown_timeout = setTimeout(function(){
this_message.css("filter", "brightness(50%)");
this_message.parents(".messageContainer").find(".messageDate").css("display", "inline-block");		
}, 150);	
}).on("touchend", ".fileMessageContainer", function(){
clearTimeout(file_message_touchdown_timeout);	
this_message.css("filter", "brightness(100%)");
this_message.parents(".messageContainer").find(".messageDate").css("display", "none");		
});




	
});
