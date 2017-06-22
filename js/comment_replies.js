

// we use this variable to prevent multiple calls to "get_comment_replies.php", otherwise there would be duplicate replies, be sure to set this variable to true whenever you call "getReplies()", and set it to false after a successful return in the function.
var repliesPreventMultipleCalls = false;

var repliesIntervalVar;
var repliesIntervalObject;

function getReplies(commentId,lastReplyId,pinReplyToTop) {

var dataObj = {};
dataObj["comment_id"] = commentId;
dataObj["last_reply_id"] = lastReplyId;

if(typeof pinReplyToTop != "undefined") {
dataObj["pin_comment_to_top"] = pinReplyToTop;	
}

$.get({
url:"components/get_comment_replies.php",
data:dataObj,
success:function(data) {	

console.log(data);

var dataArr = JSON.parse(data);

$(".commentRepliesContainer").append(dataArr[0]);	
$("#totalNumberOfReplies").html("(" + dataArr[1] + ")");
$("#totalNumberOfReplies").attr("data-total-number",dataArr[1]);


$("#commentRepliesModal .avatarRotateDiv").each(function(){
$(this).css("transform","rotate(" + $(this).attr("data-rotate-degree") + "deg)");	
fitToParent("#" + $(this).find(".commenterAvatarImages").attr("id"));
adaptRotateWithMargin($(this).find("img"),$(this).attr("data-rotate-degree") ,false);
});

$('#commentRepliesModal .actualCommentComment').readmore({speed: 500,collapsedHeight:100, moreLink: '<a href="#" class="readMore" style="font-size:12px;color:#aaaaaa;position:relative;top:-4px;">Read More</a>',
lessLink: '<a href="#" class="readLess" style="font-size:12px;color:#aaaaaa;position:relative;top:-4px;">Close</a>'});


// initialize dropdowns	
$('#commentRepliesModal .dropdown-button').dropdown({
inDuration: 300,
outDuration: 225,
constrain_width: false, // Does not change width of dropdown to that of the activator
hover: false, // Activate on hover
gutter: 0, // Spacing from edge
belowOrigin: true, // Displays dropdown below the button
alignment: 'left', // Displays dropdown with edge aligned to the left of button
stopPropagation:true
}
);	


repliesPreventMultipleCalls = false;


repliesIntervalObject = new EnhancedInterval(repliesIntervalVar,60000,function(){

var commentIdsArr = [];

$("#commentRepliesModal .singleComment").each(function(){
commentIdsArr.push($(this).attr("data-actual-comment-id"));
});

if(commentIdsArr.length < 1) {
return false;	
}

$.get({
url:"components/comment_activities.php",
data:{"reply_ids":commentIdsArr},
success:function(data) {
var dataArr = JSON.parse(data);
for(var i = 0;i<dataArr.length;i++) {
$("#commentRepliesModal .singleComment[data-actual-comment-id=" + dataArr[i][0] + "] .postCommentActions").html(dataArr[i][1]);
}
}	
});

});

repliesIntervalObject.intervalFunction();

}	
});

}



function validateReplyLength(reply) {
if(reply.length > 800) {
return false;
}
else {
return true;	
}
}






function addReplyToComment(commentId, reply, isReplyTo) {

if(typeof commentId == "undefined" || typeof reply == "undefined") {
return false;	
}

if(validateReplyLength(reply) == false) {
Materialize.toast("Comments Cannot Be Longer Than 800 Characters, Currently At " + reply.length,4000,"red");	
return false;
}


var dataArr = {};

dataArr["comment_id"] = commentId;
dataArr["reply"] = reply;
if(typeof isReplyTo != "undefined") {
dataArr["is_reply_to"] = isReplyTo;	
}
 
$.post({
url:"components/add_reply_to_comment.php",
data:dataArr,
success: function(data) {

var dataArr = JSON.parse(data);

$(".commentRepliesContainer").prepend(dataArr[0]);
eval(dataArr[1]);

setNewNumber($("#totalNumberOfReplies"),"data-total-number",true,true,"");

$("#commentRepliesModal .avatarRotateDiv").each(function(){
$(this).css("transform","rotate(" + $(this).attr("data-rotate-degree") + "deg)");	
fitToParent("#" + $(this).find(".commenterAvatarImages").attr("id"));
adaptRotateWithMargin($(this).find("img"),$(this).attr("data-rotate-degree") ,false);
});


// initialize dropdowns	
$('#commentRepliesModal .dropdown-button').dropdown({
inDuration: 300,
outDuration: 225,
constrain_width: false, // Does not change width of dropdown to that of the activator
hover: false, // Activate on hover
gutter: 0, // Spacing from edge
belowOrigin: true, // Displays dropdown below the button
alignment: 'left', // Displays dropdown with edge aligned to the left of button
stopPropagation:true
}
);	


$('#commentRepliesModal .actualCommentComment').readmore({speed: 500, moreLink: '<a href="#" class="readMore" style="font-size:12px;color:#aaaaaa;position:relative;top:-4px;">Read More</a>',
lessLink: '<a href="#" class="readLess" style="font-size:12px;color:#aaaaaa;position:relative;top:-4px;">Close</a>'});

repliesIntervalObject.intervalFunction();
}	
});
	
}




$(document).ready(function(){


$(".commentRepliesContainer").scroll(function(){

if(($(this)[0].scrollHeight - ($(this).scrollTop() + $(this).outerHeight()) < 100) && repliesPreventMultipleCalls == false) {
getReplies($(this).attr("data-comment-id"),$("#commentRepliesModal .singleComment:last-child").attr("data-comment-id"));
repliesPreventMultipleCalls = true;
}

});


/* upvoting and downvoting */

$(document).on("click","#commentRepliesModal .upvoteOrDownvote",function(){

if(typeof $(this).attr("data-upvote-or-downvote") == "undefined") {
return false;	
}

if(typeof $(this).parents(".postCommentActions").attr("data-comment-id") == "undefined") {
return false;	
}

var thisUpvotesObject = $(this).parent().find(".upvoteOrDownvote[data-upvote-or-downvote=upvote]");
var thisDownvotesObject = $(this).parent().find(".upvoteOrDownvote[data-upvote-or-downvote=downvote]");
var thisUpvotesNumberObject = $(this).parent().find(".commentUpvotes");
var thisDownvotesNumberObject = $(this).parent().find(".commentDownvotes");

$.post({
url:"components/upvote_downvote_reply.php",
data:{
"reply_id":$(this).parents(".postCommentActions").attr("data-comment-id"),
"type":$(this).attr("data-upvote-or-downvote")
},
success:function(data) {
thisUpvotesObject.removeClass('upvoteOrDownvoteActive');
thisDownvotesObject.removeClass('upvoteOrDownvoteActive');	
eval(data);	
}	
});

});


/* deleting replies */

var deleteReplyTimeout;
var deleteReplyButton;
$(document).on("touchstart","#commentRepliesModal .singleComment",function(){

deleteReplyButton = $(this).find(".deleteCommentButton");

deleteReplyTimeout = setTimeout(function(){
deleteReplyButton.dropdown('open');
},1200);
}).on("touchend touchmove",".singleComment",function(){
clearTimeout(deleteReplyTimeout);	
});


$(document).on("click",".deleteComment",function(){

if(typeof $(this).attr("data-comment-id") == "undefined") {
return false;	
}	

var thisCommentObject = $(this).parents(".singleComment");

deleteReply($(this).attr("data-comment-id"),function(){thisCommentObject.fadeOut('fast',function(){ $(this).remove();});});

setNewNumber($("#totalNumberOfReplies"),"data-total-number",false,true,"");	

});

function deleteReply(replyId, callback) {

$.post({
url:"components/delete_reply.php",
data:{"reply_id":replyId},
success:function(data) {
if(data == "1") {
if(typeof callback != "undefined") {
callback();	
}
}	
}
});
	
}



});