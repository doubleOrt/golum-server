
// will be set on document load. This element will contain a "data-user-id" attribute that points to the id of the logged_in user.
var BASE_USER_ID_HOLDER;

$(document).ready(function(){
 
BASE_USER_ID_HOLDER = $("#megaContainer");
	 	 
//hide the loading bar and show the document body
removeLoading($("#main_screen_main_posts_container"));
$("#showOnBodyLoad").show();

// we got 284 emojis in our emojis file, we need to append them all to our emojisContainerChild element.
for(var i = 0;i<285;i++) {
$(".emojisContainerChild").append("<img class='emoji' src='icons/emojis/" + i + ".svg' alt='Emoji' style='width:55px;height:55px;'/>");
}
 
 
 

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







$(".baseUserAvatarRotateDivs").each(function(){
//we are adding this because of pages where this script page is included but the page isn't a page where the user has logged in, otherwise there would be a userAvatarImageRotateDegree is not defined error.
if(typeof $(this).attr("data-rotate-degree") == "undefined") {
return false;
}
$(this).parent().css("transform","rotate(" + $(this).attr("data-rotate-degree") + "deg)");	
fitToParent("#" + $(this).attr("id"));
adaptRotateWithMargin($(this).find("img"),$(this).attr("data-rotate-degree"),false);
});










// when the user wants to go to their messages section (chat portals)
$(document).on("click",".getChatPortal",function(){
removeLoading($("#main_screen_main_posts_container"));	
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
thisPostElement.find('.favoritePost').find('i').html('bookmark');	
}	
else {
thisPostElement.find('.favoritePost').find('i').html('bookmark_border');	
}
}


});




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
$(document).on("doubletap",".postSingleImageContainer",function(event){
	
doubleClicked = true;	
clearTimeout(openFullScreenFileViewTimeout);
setTimeout(function(){doubleClicked = false;},600);

var thisSinglePostObject = $(this).parents(".singlePost");
var voteOptionIndex = $(this).attr("data-option-index");

if(thisSinglePostObject.find(".vote_holder .totalVotesNumber[data-user-vote='true']").parents(".vote_holder").attr("data-option-index") == voteOptionIndex) {
return false;	
}	

// show the votes for this post.	
showNewPostVotes(thisSinglePostObject,voteOptionIndex);	
reactToVote(thisSinglePostObject, event.pageX, event.pageY);
postVote(thisSinglePostObject,voteOptionIndex);
thisSinglePostObject.find(".post_images_container_bottom_overlay").animate({"height": "100%"}, 300);
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



/* main screens and their relation to the #bottomNav */

$(document).on("click", "[data-open-main-screen]" , function() {
$(".main_screen").removeClass("main_screen_active");
$($(this).attr("data-open-main-screen")).addClass("main_screen_active");
});


var non_activatable_item_class_name = "bottom_nav_static_item";
$(document).on("click",".bottomTabsItem",function(){
if(!$(this).hasClass(non_activatable_item_class_name)) { 	
$(".bottomTabsItem").removeClass("active");
$(this).addClass("active");
}
});
$(document).on("touchstart",".bottomTabsItem",function(){
$(this).addClass("bottomTabsItemActiveColor");
});
$(document).on("touchend",".bottomTabsItem",function(){
var thisItem = $(this);	
setTimeout(function(){thisItem.removeClass("bottomTabsItemActiveColor");},30);
});



});




