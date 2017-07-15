
var scrollEventAlreadyAttached = false;

// we use this variable to prevent multiple calls to "previous_messages.php", otherwise there would be duplicate messages, be sure to set this variable to true whenever you call "showPreviousMessages()", and set it to false after a successful return in the function.
var chatPreventMultipleCalls = false;

var startChatId;
var startChatRecipientId;

// this function is called whenever a chat modal is opened, and whenever the user wants to see previous messages.
function startChatModal(targetTagname) {	

// if the user is actually clicking on the avatar images (meaning he wants to go to the user modal, not to the chat modal) of the chatPortals, return.
if(targetTagname == "IMG") {
return;	
}

if(typeof $(this).attr("data-chat-id") != "undefined" || typeof $(this).attr("data-user-id") != "undefined" && typeof $(this).attr("data-from") != "undefined") {

var dataObj = {};

if($(this).attr("data-from") == "userModal") {
dataObj["unhide_chat_if_hidden"] = "true";	
}
else {
dataObj["unhide_chat_if_hidden"] = "false";		
}

if(typeof $(this).attr("data-chat-id") != "undefined") {
startChatId = $(this).attr("data-chat-id");	
dataObj["chat_id"] = startChatId;
}
if(typeof $(this).attr("data-user-id") != "undefined") {
startChatRecipientId = $(this).attr("data-user-id");
dataObj["user_id"] = startChatRecipientId;
}

dataObj["currently_shown"] = $(".messageContainer").length;

$("#chatModal").modal('open');	

$.get({
url:"components/start_chat.php",
data:dataObj,
type:"get",
success: function(data) {

if(scrollEventAlreadyAttached == false) {
	
$(".chatWindowChild").scroll(function(event){
if(chatPreventMultipleCalls == false && $(this).scrollTop() == 0) {
showPreviousMessages($("#sendMessage").attr("data-chat-id"),$(".messageContainer").length);
chatPreventMultipleCalls = true;
}
});

scrollEventAlreadyAttached = true;
}

var dataArr = JSON.parse(data);

$(".chatWindowChild").prepend(dataArr[0]);	
$(".sendMessageButton").html(dataArr[1]);
$(".chatModalFullName").html(dataArr[2]);
$(".emojisContainer").appendTo(".chatModalContentChild");



// scroll to the bottom
$('.chatWindowChild').scrollTop($('.chatWindowChild')[0].scrollHeight);	


getChatPortals();

longpollingVar.abort();
startStatusChecks();
}
});


}		
}



function showPreviousMessages(chatId,currentlyShownMessagesNumber) {

$.get({
url:"components/previous_messages.php",
data:{
"chat_id":chatId,
"currently_shown":currentlyShownMessagesNumber	
},
success:function(data){
// take care of scrolling back to the element that was the oldest message before this query (because jquery scrolls to the top on prepending). order is important here.
var firstHeight = $(".chatWindowChild")[0].scrollHeight;
$(".chatWindowChild").prepend(data);
var newHeight = $(".chatWindowChild")[0].scrollHeight;
$(".chatWindowChild").scrollTop(newHeight - firstHeight);

chatPreventMultipleCalls = false;		
}
});	
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






function startStatusChecks() {

var data = {};

if($("#currentStatus").length > 0) {
data["current_status"] = $("#currentStatus").attr("data-current-status");	
}

if($("#sendMessage").length > 0) {
data["currently_chatting"] = $("#sendMessage").attr("data-chat-id");	
}

var sentUnreadMessages = false;

$(".messageContainer").each(function(index){
if(!$(this).hasClass("imageMessageContainer")) {	
var currentId = $(this).attr("id");
var markedForDeletionAttr = $("#" + currentId).attr("data-marked-for-deletion");
if(typeof markedForDeletionAttr == "undefined") {
sentUnreadMessages = true;	
}
}
});

if(sentUnreadMessages == true) {
data["sent_unread_messages"] = "true";	
}
else {
data["sent_unread_messages"] = "false";	
}


longpollingVar = $.get({
url:"components/longpoll.php",
data:data,
type:"get",
success:function(data) {	

if(data != "") {
	
var dataArr = JSON.parse(data);

// takes care of adding the recipient's status (online, last seen, etc...) for the #chatModal
if(dataArr[0] != "") {
$("#currentStatus").html(dataArr[0]);	
if(dataArr[0] == "Online") {
$("#currentStatus").attr("data-current-status",1);		
}
else if(dataArr[0] == "Here") {
$("#currentStatus").attr("data-current-status",2);			
}
else {
$("#currentStatus").attr("data-current-status",0);			
}
}

// if there are any new messages
if(dataArr[1] == "true") {
getNewMessages($("#sendMessage").attr("data-chat-id"), ($(".messageContainer:last").hasClass("message0") || $(".messageContainer").length < 1 ? true : false));	
}

if(dataArr[2] == "true") {
getChatPortalActivities(updateChatPortalActivities);	
}

// if we should set a 10s timeout to hide all messages.
if(dataArr[3] == "true") {
}

if(dataArr[4] != "") {
var imageMessageId = dataArr[4];	
setTimeout(function(){
fadeItOut(".messageContainer[data-message-id=" + imageMessageId + "]");	
},10000);	

}


// notification related
if(dataArr[5] !== "") {
// if user has new notifications	
if(dataArr[5] > 0) {
// if the element exists, then just change its innerHTML, otherwise create a new element.	
if($("#newNotificationsNumber").find(".notificationNumContainer").length > 0) {	
$("#newNotificationsNumber .notificationNum").html(dataArr[5]);
}
else {
$("#newNotificationsNumber").prepend("<span class='notificationNumContainer notificationNumContainerSmall' style='top:10px;right:2px;'><span class='notificationNum'>" + dataArr[5] + "</span></span>");	
}
}
else {
$("#newNotificationsNumber").find(".notificationNumContainer").remove();	
}

}

}


startStatusChecks();
}
});

}





function chatModalClosed() {
$.get({
url:"components/chat_modal_closed.php"	
});
}


function switchChatModalSendButton(switchButtonTo) {

// the button can now be used to send images.
if(switchButtonTo == 0) {
$("#sendMessage").html("<i class='material-icons' style='font-size:160%'>camera_alt</i>");	
$("#sendMessage").attr("data-file-or-send","0");		
}
// the button can now be used to send text.
else if(switchButtonTo == 1) {
$("#sendMessage").html("<i class='material-icons' style='font-size:120%'>send</i>");	
$("#sendMessage").attr("data-file-or-send","1");		
}
	
}




function sendMessage(chatId, message, messageType) {

$.post({
url: 'components/send_message.php',
data: {
"chat_id": chatId,
"message": message,
"type": messageType
},
success: function(data){	
$('.chatWindowChild').append(data);
// scroll to the bottom
$('.chatWindowChild').scrollTop($('.chatWindowChild')[0].scrollHeight);	
}
}); 
	
}




function sendTextMessage(chatId, message) {

if(typeof chatId == "undefined" || typeof message == "undefined" || message.trim() == "" || typeof $("#sendMessage").attr("data-chat-id") == "undefined") {
return false;	
}

if(message.length > 400) {
Materialize.toast("Message Must Be Smaller Than 400 Characters",4000,"red");	
return false;
}

sendMessage(chatId, message, "text-message");	
}


function sendImage() {
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
$('.chatWindowChild').append(data);
// scroll to the bottom
$('.chatWindowChild').scrollTop($('.chatWindowChild')[0].scrollHeight);	
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










$(document).ready(function(){



// when a user clicks on the start chat button or the chat icon in the usermodals
$(document).on("click",".startChat",function(e){
startChatModal.call($(this),e.target.tagName);
startStatusChecks();		
});

$(document).on("click",".chatModalCloseButton",function() {

$(".chatWindowChild").html("");
$(".chatModalFullName").html("");
$(".sendMessageButton").html("");

scrollEventAlreadyAttached = false;
$(".chatWindow").html("<div class='chatWindowChild'></div>");
chatModalClosed();
longpollingVar.abort();
});

// on double tapping, toggle .emojisContainer's display.
$(document).on("doubletap",".chatWindowChild",function(e){
setTimeout(function(){
$(".emojisContainer").toggle();	
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
$(".emojisContainer").click(function(event){
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
sendTextMessage($("#sendMessage").attr("data-chat-id"), $(".messageTextarea").val());
$(".messageTextarea").val("");	
}

});

// user wants to send an emoji
$(document).on("click",".emoji",function(e){	
$(".emojisContainer").fadeOut();
sendMessage($("#sendMessage").attr("data-chat-id"), $(this).attr("src"), "emoji-message");
});

// when users want to send files (images).
$(document).on("change","#sendImage",function(){
sendImage();
});

// user wants to open an image-message in fullscreen
$(document).on("click",".fileMessageContainer",function(){
openFullScreenFileView($(this).find("img").attr("src"));
});



	
});
