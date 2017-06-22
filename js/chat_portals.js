
//this takes care of showing the chat portals
function getChatPortals() {
$.get({
url:"components/chat_portal.php",
type:"get",
success: function(data) {
$("#chatsPortalContainerChild").html(data);
getChatPortalActivities(updateChatPortalActivities);
$("#chatsPortalContainer").show();
}
});
}


function updateChatPortalActivities(chatPortalActivities) {
	
for(var i = 0;i<chatPortalActivities.length;i++) {
var elem = $(".singleChatPortal[data-chat-id='" + chatPortalActivities[i]["chatId"] + "']");

// if current row has any new messages.
if(chatPortalActivities[i]["newMessagesNum"] > 0) {
// if the current chat row already has a .notificationNum element (added to tell users how many new messages they have), then just update that existing element's value, otherwise add a new element. 
if(elem.find(".notificationNumContainer").length > 0) {
elem.find(".notificationNumContainer .notificationNum").html(chatPortalActivities[i]["newMessagesNum"]);
}
else {
elem.find(".chatPortalFullName").append("<span class='notificationNumContainer notificationNumContainerSmall' style='top:-2px;left:5px;position:relative;'><span class='notificationNum'>" + chatPortalActivities[i]["newMessagesNum"] + "</span></span>");
}
}
// if current row has no new messages 
else {	
// remove the new messages .notificationNumContainer element for that row 
if(elem.find(".notificationNumContainer").length > 0) {
elem.find(".chatPortalFullName").find("notificationNumContainer").remove();
}

}

elem.find(".chatPortalMessagePreview").html(chatPortalActivities[i]["latestMessage"]);
elem.find(".latestMessageContainer").html(chatPortalActivities[i]["latestMessageDate"]);
}

}


// takes care of updating the chat portal.
function getChatPortalActivities(callback) {

$.get({
url:"components/chat_portal_activities.php",
success:function(data) {

if(data != "") {

var dataArr = JSON.parse(data);	

var chatPortalActivities = [];	

for(var i = 0;i<dataArr.length;i++) {
chatPortalActivities.push({"chatId":dataArr[i][0],"newMessagesNum":dataArr[i][1],"latestMessage":dataArr[i][2],"latestMessageDate":dataArr[i][3]});	
}

// if the callback function has a parameter that we can pass our array to.
if(callback.length > 0) {
callback(chatPortalActivities);
}

}

}	
});

}


function hideChat(chatId,callback) {
	
$.post({
url:"components/hide_chat.php",
data:{"chat_id":chatId},
success:function(data) {

// chat was successfully hidden
if(data == "1")	{
callback();	
}
	
}
});

}



