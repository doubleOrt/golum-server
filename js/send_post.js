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

$(document).ready(function() {

$(document).on("click",".sendPostToFriend",function(){
$(".sendToFriendContainerCol").attr("data-actual-post-id",$(this).attr("data-actual-post-id"));	
});

$(document).on("keyup","#sendToFriendInput",function(){

// if the value of the sendPostToFriend input is empty
if($(this).val().trim() == "") {
$(".sendToFriendContainerCol").html("<span id='sendToFriendModalPlaceholder' class='emptyNowPlaceholder aaaaaaColor'>Friends Will Appear Here...</span>");	
return false;
}	

if(typeof $(".sendToFriendContainerCol").attr("data-actual-post-id") == "undefined") {
return false;	
}

$(".sendToFriendContainerCol").find("#sendToFriendModalPlaceholder").remove();	

$.get({
url:"components/get_send_to_friend_friends.php",
data:{
"friend_name":$(this).val().trim(),
"post_id":$(".sendToFriendContainerCol").attr("data-actual-post-id")
},
success:function(data) {
console.log(data);

var dataArr = JSON.parse(data);

$(".sendToFriendContainerCol").html(dataArr[0]);	

$("#sendToFriendModal .avatarRotateDiv").each(function(){
$("#sendToFriendModal .avatarRotateDiv").css("transform","rotate(" + $(this).attr("data-rotate-degree") + "deg)");	
fitToParent("#" + $(this).find(".sendToFriendAvatarImages").attr("id"));
adaptRotateWithMargin($(this).find("img").find(".sendToFriendAvatarImages"),$(this).attr("data-rotate-degree") ,false);
});

}	
});

});



});