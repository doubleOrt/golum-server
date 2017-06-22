var notificationsPreventMultipleCalls = false;

function getNotifications(lastNotificationId, notificationMarkUpTarget) {
notificationsPreventMultipleCalls = true;	
	
if(lastNotificationId == 0) {
// empty its innerHTML 	
notificationMarkUpTarget.html("");	
}	
	
$.get({
url:"components/notifications.php",
data:{
"last_notification_id":lastNotificationId
},
success:function(data) {

var dataArr = JSON.parse(data);

notificationMarkUpTarget.append(dataArr[0]);

notificationMarkUpTarget.find(".avatarRotateDiv").each(function(){
$(this).css("transform","rotate(" + $(this).attr("data-rotate-degree") + "deg)");	
fitToParent("#" + $(this).find(".notificationAvatarImages").attr("id"));
adaptRotateWithMargin($(this).find("img"),$(this).attr("data-rotate-degree") ,false);
});

notificationsPreventMultipleCalls = false;
} 
});

}