$(document).ready(function(){
 
	 
//hide the loading bar and show the document body
hideLoading();
$("#showOnBodyLoad").show();
	 
// call the logOut() function whenever the user presses the logOut() button.
$(document).on("click","#logOutButton",function(){
logOut();
});


$(document).on("click", "[data-open-main-screen]" , function() {
$(".main_screen").removeClass("main_screen_active");
$($(this).attr("data-open-main-screen")).addClass("main_screen_active");
});


// we got 284 emojis in our emojis file, we need to append them all to our emojisContainerChild element.
for(var i = 0;i<285;i++) {
$(".emojisContainerChild").append("<img class='emoji' src='icons/emojis/" + i + ".svg' alt='Emoji' style='width:55px;height:55px;'/>");
}



$(document).on("click",".modal-trigger",function(){
	
// we need to disable the button so the user cannot make multiple calls to the openModalCustom .	
$(this).css("pointer-events","none");
var thisModalTrigger = $(this);	
setTimeout(function(){thisModalTrigger.css("pointer-events","auto");},500);
	
// grab the modal's id
var modalId = ( typeof $(this).attr("data-target") != "undefined" ? $(this).attr("data-target") : $(this).attr("href").substr(1,this.length));

openModalCustom(modalId,currentZindexStack,modalsOpened);
});


$(document).on("click",".modalCloseButton, .modal-overlay",function(){	
closeModal($(this).attr("data-modal"));
});




// we want all images on our app to be not draggable.
$("img").on("dragstart",function(e){
e.preventDefault();
});



// whenever this element is clicked, we want to hide it.
$(document).on("click","#fullScreenFileView",function(){
closeFullScreenFileView();
});



$(document).on("click",".stopPropagationOnClick",function(event){
event.stopPropagation();	
});




// materialize initialize the select elements
$('select').material_select();

//initialize tabs
 $(".tabs").tabs();


// initialize the modals
$('.modal').modal({
ready:function(){
// because of a bug where the indicator for tabs doesn't work if the tabs are not visible when they are initialized, we have to initialize the tabs as soon as they become visible.
$(this).find(".tabs").tabs();
}
});


/* components that require a loading circle to show until they are fully loaded, on clicking those, we hide all the pageContentComponents (these methods are used for them) and show the loading circle, then we have to manually show the div we want to after we know it has fully loaded */
$(document).on("click",".showLoadingOnClick",function(){	
$(".pageContentComponents").hide();
showLoading();
});


// the user wants to see their notifications
$(document).on("click",".openNotificationsModal",function(){
getNotifications(0, $("#main_screen_notifications #notifications_container"));
$("#newNotificationsNumber").find(".notificationNumContainer").remove();
});
$("#main_screen_notifications #notifications_container").scroll(function(){
if(($(this)[0].scrollHeight - ($(this).scrollTop() + $(this).outerHeight()) < 100) && notificationsPreventMultipleCalls == false) {
getNotifications($("#notificationsModal .singleNotification:last-child").attr("data-notification-id"), $("#notificationsModal .modal-content"));
}
});





/* ----- follows and blocks ----- */

// when a user wants to see their contacts
$(document).on("click",".contactsButton",function(){
getBaseUserFollowings($("#contactsModalContentChild"));
});

// when a user wants to see people who follow them
$(document).on("click",".followingMeButton",function(){	
getBaseUserFollows($("#followingMeModalContentChild"));
});

//related to adding or removing contacts 
$(document).on("click",".addOrRemoveContact",function(){

if(typeof $(this).attr("data-user-id") == "undefined") {
return false;
}  

addOrRemoveContact($(this).attr("data-user-id"), addOrRemoveContactCallback);

/* this fixes an inconsistency where if you opened a user modal from the contacts modal, and then deleted a contact, the contacts modal would not be updated. to fix that, we 
update the contacts modal everytime that button is clicked. */
if(typeof $(this).attr("data-not-from-contacts") != "undefined") {
getBaseUserFollowings($("#contactsModalContentChild"));
}



function addOrRemoveContactCallback(newState) {

// user is now following the target user
if(newState == "0") {	
$('.addOrRemoveContact').html('Unfollow');
var userFollowsNum = parseFloat($('.userFollowsNum').html()); 
if(userFollowsNum + 1 != 1) {
$('#userModalFollowedBy').html("<span class='userFollowsNum'>" + (userFollowsNum + 1) + "</span> Followers");
}
else {
$('#userModalFollowedBy').html("<span class='userFollowsNum'>1</span> Follower");	
}
}
// user just unfollowed the target user
else if(newState == "1") {
$('.addOrRemoveContact').html('Follow');
var userFollowsNum = parseFloat($('.userFollowsNum').html()); 
if((userFollowsNum - 1) != 1) {
$('#userModalFollowedBy').html("<span class='userFollowsNum'>" + (userFollowsNum - 1) + "</span> Followers");
}
else {
$('#userModalFollowedBy').html("<span class='userFollowsNum'>1</span> Follower");	
}
}
	
}

});



// used when user blocks or unblocks contacts.
$(document).on("click","#blockUser",function(){
blockOrUnblockUser($(this).attr("data-user-id"),blockOrUnblockUserCallback);

function blockOrUnblockUserCallback(newState) {
// the user is now blocked	
if(newState == "0") {
Materialize.toast('User Blocked, Tap Button To Unblock',3000,'green');	
$("#blockUser").html("Unblock");	
$("#blockUser").attr("data-current-state","1");	
$(".addOrRemoveContact").html("Follow");
// since you unfollow a user when you block them, we have to decrease that user's followings by 1
var userFollowsNum = parseFloat($('.userFollowsNum').html()); 
if(userFollowsNum > 0) {
if((userFollowsNum - 1) != 1) {
$('#userModalFollowedBy').html("<span class='userFollowsNum'>" + (userFollowsNum - 1) + "</span> Followers");
}
else {
$('#userModalFollowedBy').html("<span class='userFollowsNum'>1</span> Follower");
}	
}

$(".addOrRemoveContact").css({"pointer-events":"none","opacity":".5"});
$(".modalFooterButtonReverse.blockUser").html("Unblock");
}	
else if(newState == "1") {
Materialize.toast('User Unblocked, Tap Button To Block',3000,'green');	
$("#blockUser").html("Block");	
$("#blockUser").attr("data-current-state","0");		
$(".addOrRemoveContact").css({"pointer-events":"auto","opacity":"1"});
$(".modalFooterButtonReverse.blockUser").html("Block");	
}
}

});

/* ----- END follows and blocks ----- */










// if user has requested us to link his account with an email address and we have sent him a confirmation code, show him the enter confirmation code form.
$.get({
url:"components/show_confirmation_code_form.php",
success:function(data) {	
$("#accountContainerRow").prepend(data);
}	
});	




$(".baseUserAvatarRotateDivs").each(function(){
//we are adding this because of pages where this script page is included but the page isn't a page where the user has logged in, otherwise there would be a userAvatarImageRotateDegree is not defined error.
if(typeof $(this).attr("data-rotate-degree") == "undefined") {
return false;
}
$(this).parent().css("transform","rotate(" + $(this).attr("data-rotate-degree") + "deg)");	
//fitToParent("#" + $(this).attr("id"));
adaptRotateWithMargin($(this).find("img"),$(this).attr("data-rotate-degree"),false);
});










// when the user wants to go to their messages section (chat portals)
$(document).on("click",".getChatPortal",function(){
hideLoading();	
$("#allPostsContainer").hide();
getChatPortals();	
});

// takes care of showing the remove chat buttons on long pressing.
var removeChatToggleTimeout;
var chatPortalId;
$(document).on("touchstart",".singleChatPortal",function(){
chatPortalId = $(this).attr("id");	
removeChatToggleTimeout = setTimeout(function(){
$(".singleChatPortal[id!=" + chatPortalId + "]").find(".removeChat").fadeOut("fast");	
$(".singleChatPortal[id!=" + chatPortalId + "]").find(".singleChatPortalAvatarContainer").fadeIn("fast");	
$("#" + chatPortalId).find(".singleChatPortalAvatarContainer").fadeToggle("fast");
$("#" + chatPortalId).find(".removeChat").fadeToggle("fast");
},1200);
});
$(document).on("touchend touchmove",".singleChatPortal",function(){
clearTimeout(removeChatToggleTimeout);	
});

// when a user presses the remove chat button.
$(document).on("click",".removeChat",function(e){
e.stopPropagation();	

hideChat($(this).parents(".singleChatPortal").attr("data-chat-id"),function(){Materialize.toast("You Can Unhide a Chat By Tapping The 'Start Chat' Button In a User's Profile.",6000);});

$(this).parents(".singleChatPortal").fadeOut("fast","linear",function(){
$(this).remove();	
});

});







// a mini library for showing things when an element is clicked
$(document).on("click",".onclickShow",function(){	
var onclickShowElement = $(this);
var elemsArr = $(this).attr("data-onclick-show").split(",");
if(typeof $(this).attr("data-onclick-changeThis") != "undefined") {
var changeThisHtmlArr = $(this).attr("data-onclick-changeThis").split(",");	
} 
for(var i = 0;i<elemsArr.length;i++) {
if($(elemsArr[i]).is(":visible")) {
onclickShowElement.css("pointer-events","none");		
$(elemsArr[i]).fadeOut("fast",function(){
onclickShowElement.css("pointer-events","auto");	
});	
if(typeof changeThisHtmlArr != "undefined") {
$(this).html(changeThisHtmlArr[i]);	
}
}	
else {
$(elemsArr[i]).css("display","block");	
}
}
});






// when users want to follow tags

$(document).on("click",".addTagFromTagPostsModal",function(){
	
if($(this).attr("data-current-state") == "0") {	

addTagsToUserById($(this).attr("data-tag"),function(){
$("#tagPostsModal .addTagFromTagPostsModal").attr("data-current-state","1");
$("#tagPostsModal .addTagFromTagPostsModal").html("Unfollow");
});	

}
else {
	
removeTagsFromUserById($(this).attr("data-tag"),function(){
$("#tagPostsModal .addTagFromTagPostsModal").attr("data-current-state","0");	
$("#tagPostsModal .addTagFromTagPostsModal").html("Follow");
});		

}

});






/* ----- getting posts ----- */

$(document).on("click",".getPosts",function(){
emptyAllPostsContainer();
getPosts("components/get_posts.php",{"last_post_id":0},markUpProcessor,$("#allPostsContainer"),function(){$("#allPostsContainer").attr("data-contains-which-posts","posts")});		
});

// make a call to the getPosts() function to get the posts for the user's feed (this call is made when the app is loaded)
getPosts("components/get_posts.php",{"last_post_id":0},markUpProcessor,$("#allPostsContainer"),function(){$("#allPostsContainer").attr("data-contains-which-posts","posts")});		



/* when a user wants to see the featured posts */
$(document).on("click",".getFeaturedPosts",function(){
emptyAllPostsContainer();
getPosts("components/get_featured_posts.php",{"last_post_id":0},markUpProcessor,$("#allPostsContainer"),function(){$("#allPostsContainer").attr("data-contains-which-posts","featuredPosts")});		
});

/* when a user wants to see his favorite posts */


$(document).on("click",".getMyFavoritePosts",function(){	
emptyAllPostsContainer();
getPosts("components/get_my_favorite_posts.php",{"last_post_id":0},markUpProcessor,$("#allPostsContainer"),function(){$("#allPostsContainer").attr("data-contains-which-posts","favoritePosts")});		
});


/* user is scrolling the page content */

$("#pageContent").scroll(function(){	
if($("#allPostsContainer").css("display") != "none") {
if(($(this)[0].scrollHeight - ($(this).scrollTop() + $(this).outerHeight()) < 650) && blockCallsToGetPosts == false) {
var allPostsContainerContainsWhichPosts = $("#allPostsContainer").attr("data-contains-which-posts");
if(allPostsContainerContainsWhichPosts == "posts") {
getPosts("components/get_posts.php",{"last_post_id":$("#allPostsContainer .singlePost:last-child").attr("data-post-id")},markUpProcessor,$("#allPostsContainer"),function(){$("#allPostsContainer").attr("data-contains-which-posts","posts")});		
}	
else if(allPostsContainerContainsWhichPosts == "featuredPosts") {
getPosts("components/get_featured_posts.php",{"last_post_id":$("#allPostsContainer .singlePost:last-child").attr("data-post-id")},markUpProcessor,$("#allPostsContainer"),function(){$("#allPostsContainer").attr("data-contains-which-posts","featuredPosts")});		
}	
else if(allPostsContainerContainsWhichPosts == "favoritePosts") {
getPosts("components/get_my_favorite_posts.php",{"last_post_id":$("#allPostsContainer .singlePost:last-child").attr("data-post-id")},markUpProcessor,$("#allPostsContainer"),function(){$("#allPostsContainer").attr("data-contains-which-posts","favoritePosts")});		
}	

}
}
});



// when a user wants to open a single post, we make a call to the get_single_post.php file, remember that all modal-trigger's for this modal must have a data-actual-post-id attribute.
$(document).on("click",".openSinglePost",function(event){
event.stopPropagation();
if(typeof $(this).attr("data-actual-post-id") == "undefined") {
return false;	
}	
getPosts("components/get_single_post.php",{"post_id":$(this).attr("data-actual-post-id")},markUpProcessor,$("#singlePostsContainer"));		
});





/* when a user wants to view another's posts via clicking the "posts" button on their user modal */
$(document).on("click",".getUserPosts",function(){
if(typeof $(this).attr("data-user-id") == "undefined") {
return false;	
}
//empty the #userPostsContainer of the last query's posts
$("#userPostsContainer").html("");
$("#userPostsContainer").attr("data-user-id",$(this).attr("data-user-id"));
getPosts("components/get_user_posts.php",{"last_post_id":0,"user_id":$(this).attr("data-user-id")},markUpProcessor,$("#userPostsContainer"));		
});

// user is scrolling the user posts modal
$("#userPostsContainer").scroll(function(){
if($(this).scrollTop() > ($(this)[0].scrollHeight - 650) && blockCallsToGetPosts == false && $(this).find(".singlePost").length > 0) {
getPosts("components/get_user_posts.php",{"last_post_id":$("#userPostsContainer .singlePost:last-child").attr("data-post-id"),"user_id":$(this).attr("data-user-id")},markUpProcessor,$("#userPostsContainer"));		
}
});




/* when users want to view posts that match their search terms */
$(document).on("click",".openGetPostsByTitleModal",function(){

if(typeof $(this).attr("data-search-value") == "undefined") {
return false;	
}

//empty the #tagPostsContainer of the last query's posts
$("#postsByTitleContainer").html("");

$("#postsByTitleContainer").attr("data-search-value",$(this).attr("data-search-value"));

// set the #getPostsByTitleModal's title to the name of the tag.
$("#getPostsByTitleModal .modal-header .modalHeaderFullName").html("'" + $(this).attr("data-search-value") + "'");

getPosts("components/get_posts_by_title_search.php",{"last_post_id":0,"search_value":$(this).attr("data-search-value")},markUpProcessor,$("#postsByTitleContainer"));		

});


$("#postsByTitleContainer").scroll(function(){
if($(this).scrollTop() > ($(this)[0].scrollHeight - 650) && blockCallsToGetPosts == false && $(this).find(".singlePost").length > 0) {
getPosts("components/get_posts_by_title_search.php",{"last_post_id":$(this).find(".singlePost:last-child").attr("data-post-id"),"search_value":$(this).attr("data-search-value")},markUpProcessor,$("#postsByTitleContainer"));		
}
});




$(document).on("click",".getTagPosts",function(e){	

if(typeof $(this).attr("data-tag") == "undefined" && typeof $("#tagPostsModal").attr("data-tag") == "undefined") {
return false;	
}

if(typeof $(this).attr("data-hot-or-new") == "undefined") {
var hotOrNew = 0;	
}
else {
var hotOrNew = $(this).attr("data-hot-or-new");
}

$("#tagPostsModal").attr("data-hot-or-new",hotOrNew);	


//empty the #tagPostsContainer of the last query's posts
$("#tagPostsContainer").html("");

//scroll to the top
$("#tagPostsModal .modal-content").scrollTop(0);	

// if the user is switching between the tabs
if($(this).parents(".tabs").length > 0) {
// get the posts
getPosts("components/get_tag_posts.php",{"last_post_id":0,"tag":$(this).parents("#tagPostsModal").attr("data-tag"),"sort_posts_by":$("#tagPostsModal").attr("data-hot-or-new")},markUpProcessor,$("#tagPostsContainer"));			
}
// user is not switching between the tabs
else {
$("#tagPostsModal").attr("data-tag",$(this).attr("data-tag"));
$("#tagPostsModal .modal-header .modalHeaderFullName").html($(this).attr("data-tag"));	

// if this tag has an image, add the necessary markup.
if(typeof $(this).attr("data-image") != "undefined") {
$("#tagPostsModal .modalContentImageContainer").css({"height":"350px","background":"url('" + $(this).attr("data-image") + "')","background-size":"cover","background-position":"center center"});	
}
else {	
$("#tagPostsModal .modalContentImageContainer").css({"height":"0","background":"none"});	
}

// get the posts
getPosts("components/get_tag_posts.php",{"last_post_id":0,"tag":$(this).attr("data-tag"),"sort_posts_by":$("#tagPostsModal").attr("data-hot-or-new")},markUpProcessor,$("#tagPostsContainer"),function(){$("#tagPostsModal .navRightItemsMobile").html(dataArr[1]);});		
}

});



$("#tagPostsContainer").scroll(function(){
if($(this).scrollTop() > ($(this)[0].scrollHeight - $("#tagPostsModal .modalContentImageContainer")[0].scrollHeight - 650) && $(this).find(".singlePost").length > 0) {
getPosts("components/get_tag_posts.php",{"last_post_id":$("#tagPostsModal .singlePost:last-child").attr("data-post-id"),"tag":$("#tagPostsModal").attr("data-tag"),"sort_posts_by":$("#tagPostsModal").attr("data-hot-or-new")},markUpProcessor,$("#tagPostsContainer"));		
}
});



/* ----- END the reign of getting posts ----- */








/* ----- post activities ----- */


/* this is used to delete posts */
$(document).on("click",".deletePost",function() {

if(typeof $(this).attr("data-actual-post-id") == "undefined") {
return false;	
}

var thisSinglePostObject = $(this).parents(".singlePost");

deletePost($(this).attr("data-actual-post-id"),function() {
Materialize.toast('Post Deleted!',3000,'red');
thisSinglePostObject.fadeOut('fast',function(){$(this).remove();});
});

});


/* when a user wants to report a post */

$(document).on("click",".reportPost",function(){
reportPost($(this).attr("data-actual-post-id"));
});





/* when a user wants to favorite a post */

$(document).on("click",".favoritePost",function(){

if(typeof $(this).attr("data-actual-post-id") == "undefined") {
return false;
}	

var thisPostElement = $(this).parents(".singlePost");

favoritePost($(this).attr("data-actual-post-id"),favoritePostCallback);


function favoritePostCallback(postIsNowFavorited) {
if(postIsNowFavorited == true) {
thisPostElement.find('.favoritePost').find('i').html('star');	
}	
else {
thisPostElement.find('.favoritePost').find('i').html('star_border');	
}
}


});




// user wants to send a post to someone
$(document).on("click",".sendToFriendButton",function(){

if(typeof $(".sendToFriendContainerCol").attr("data-actual-post-id") == "undefined" || typeof $(this).attr("data-user-id") == "undefined") {
return false;
}

var sendToFriendButtonObject = $(this);

sendPost($(".sendToFriendContainerCol").attr("data-actual-post-id") ,$(this).attr("data-user-id"),function(){sendToFriendButtonObject.addClass('disabledButton').html('SENT');});
});








/* --- comments and replies --- */
 
// user wants to see post comments
$(document).on("click",".showPostComments",function() {
	
// empty the .postCommentsContainer of the previously viewed post comments 
$(".postCommentsContainer").html("");		

$("#postCommentButton").attr("data-actual-post-id",$(this).attr("data-actual-post-id"));
$(".postCommentsContainer").attr("data-actual-post-id",$(this).attr("data-actual-post-id"));

if(typeof $(this).attr("data-pin-comment-to-top") == "undefined") {
getComments($(this).attr("data-actual-post-id"),0);
}
else {
getComments($(this).attr("data-actual-post-id"),0,$(this).attr("data-pin-comment-to-top"));	
}

});


// user wants to see comment replies

$(document).on("click",".addReplyToComment",function(){
if(typeof $(this).attr("data-comment-id") == "undefined") {
return false;	
}	

$(".commentRepliesContainer").html("");		
$("#replyToCommentButton").attr("data-comment-id",$(this).attr("data-comment-id"));
$(".commentRepliesContainer").attr("data-comment-id",$(this).attr("data-comment-id"));	
$(".commentRepliesContainer").attr("data-actual-post-id",$(this).attr("data-actual-post-id"));	

if(typeof $(this).attr("data-pin-comment-to-top") == "undefined") {
getReplies($(this).attr("data-comment-id"),0);
}
else {
getReplies($(this).attr("data-comment-id"),0,$(this).attr("data-pin-comment-to-top"));	
}
});

// user wants to reply to a reply

$(document).on("click",".addReplyToReply",function(){

if(typeof $(this).attr("data-commenter-full-name") == "undefined" || typeof $(this).attr("data-commenter-id") == "undefined") {
return false;	
}

$("#replyToCommentTextarea").html("<a href='#modal1' class='replyToFullname  modal-trigger showUserModal view-user' data-user-id='" + $(this).attr("data-commenter-id") + "' data-reply-to='" + $(this).attr("data-commenter-id") + "'>" + $(this).attr("data-commenter-full-name") + "&nbsp;</a>");	
$("#replyToCommentTextarea").focus();
$("#replyToCommentTextarea").attr("data-state","1");
movePointerToEnd($("#replyToCommentTextarea").get(0));	
});




// when user presses the comment button.
$(document).on("click","#postCommentButton",function(){
if(typeof $(this).attr("data-actual-post-id") == "undefined" || $("#postCommentTextarea").find("#commentsModal .placeholder").length > 0 || 
$("#postCommentTextarea").attr("data-state") == "0" || $("#postCommentTextarea").html().trim().length < 1) {
return false;
}

addCommentToPost($(this).attr("data-actual-post-id"), $("#postCommentTextarea").html());
});


// when user presses the button to reply to a comment
$(document).on("click","#replyToCommentButton",function(){

if(typeof $(this).attr("data-comment-id") == "undefined" || $("#replyToCommentTextarea").find("#commentRepliesModal .placeholder").length > 0 || 
$("#replyToCommentTextarea").attr("data-state") == "0" || $("#replyToCommentTextarea").html().trim().length < 1) {
return false;	
}	

var isReplyTo;
if($("#replyToCommentTextarea").find("a").length > 0) {
isReplyTo = $("#replyToCommentTextarea").find("a").attr("data-reply-to");
$("#replyToCommentTextarea").find("a").remove();	
}

addReplyToComment( $(this).attr("data-comment-id"),$("#replyToCommentTextarea").html(),isReplyTo);
});


/* --- END comments and replies --- */



var openFullScreenFileViewTimeout;
var postSingleImageContainerObject;
var doubleClicked = false;

// when user's want to open a post's files in fullscreen
$(document).on("click",".postSingleImageContainer",function(){
if(doubleClicked == false) {
postSingleImageContainerObject = $(this);	
openFullScreenFileViewTimeout = setTimeout(function(){openFullScreenFileView(postSingleImageContainerObject.attr("data-image-path"));},300);
}
});


// when the user votes
$(document).on("doubletap",".postSingleImageContainer",function(){
	
doubleClicked = true;	
clearTimeout(openFullScreenFileViewTimeout);
setTimeout(function(){doubleClicked = false;},600);

var thisSinglePostObject = $(this).parents(".singlePost");
var voteOptionIndex = $(this).attr("data-option-index");

// show the votes for this post.	
showNewPostVotes(thisSinglePostObject,voteOptionIndex);	
reactToVote(thisSinglePostObject);
postVote(thisSinglePostObject,voteOptionIndex);
});



/* ----- END post activities ----- */





/* ----- chat related ----- */

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






// these two are used mainly by input elements so that when they are focused they don't look messy (because when they are focused the keyboard becomes visible which causes everything to resize)
$(document).on("focus","[data-onfocus-toggle]",function(){
$($(this).attr("data-onfocus-toggle")).hide();
});
$(document).on("focusout","[data-onfocus-toggle]",function(){
$($(this).attr("data-onfocus-toggle")).fadeIn();
});




$(document).on("click",".bottomTabsItem",function(){
$(".bottomTabsItem").removeClass("active");
$(this).addClass("active");
});
$(document).on("touchstart",".bottomTabsItem",function(){
$(this).addClass("bottomTabsItemActiveColor");
});
$(document).on("touchend",".bottomTabsItem",function(){
var thisItem = $(this);	
setTimeout(function(){thisItem.removeClass("bottomTabsItemActiveColor");},30);
});



});




