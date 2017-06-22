

// we use this variable to prevent multiple calls to "get_post_comments.php", otherwise there would be duplicate comments, be sure to set this variable to true whenever you call "getComments()", and set it to false after a successful return in the function.
var commentsPreventMultipleCalls = false;

var commentsIntervalVar; 
var commentsIntervalObject;

function getComments(postId,lastCommentId,pinCommentToTop) {

var dataObj = {};
dataObj["post_id"] = postId;
dataObj["last_comment_id"] = lastCommentId;

if(typeof pinCommentToTop != "undefined") {
dataObj["pin_comment_to_top"] = pinCommentToTop;	
}

$.get({
url:"components/get_post_comments.php",
data:dataObj,
success:function(data) {	
console.log(data);	

var dataArr = JSON.parse(data);

$(".postCommentsContainer").append(dataArr[0]);	
$("#totalNumberOfComments").html("(" + dataArr[1] + ")");
$("#totalNumberOfComments").attr("data-total-number",dataArr[1]);


$("#commentsModal .avatarRotateDiv").each(function(){
$(this).css("transform","rotate(" + $(this).attr("data-rotate-degree") + "deg)");	
fitToParent("#" + $(this).find(".commenterAvatarImages").attr("id"));
adaptRotateWithMargin($(this).find("img"),$(this).attr("data-rotate-degree") ,false);
});


$('#commentsModal .actualCommentComment').readmore({
speed: 500,
collapsedHeight:100, 
moreLink: '<a href="#" class="readMore" style="font-size:12px;color:#aaaaaa;position:relative;top:-4px;">More...</a>',
lessLink: '<a href="#" class="readLess" style="font-size:12px;color:#aaaaaa;position:relative;top:-4px;">Less</a>'
});


// initialize dropdowns	
$('#commentsModal .dropdown-button').dropdown({
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

commentsIntervalObject = new EnhancedInterval(commentsIntervalVar,60000,function(){

var commentIdsArr = [];

$("#commentsModal .singleComment").each(function(){
commentIdsArr.push($(this).attr("data-actual-comment-id"));
});


if(commentIdsArr.length < 1) {
return false;	
}


$.get({
url:"components/comment_activities.php",
data:{"comment_ids":commentIdsArr},
success:function(data) {
var dataArr = JSON.parse(data);
for(var i = 0;i<dataArr.length;i++) {
$("#commentsModal .singleComment[data-actual-comment-id=" + dataArr[i][0] + "] .postCommentActions").html(dataArr[i][1]);
}
}	
});

});

commentsIntervalObject.intervalFunction();


commentsPreventMultipleCalls = false;
}	
});

}


function validateCommentLength(comment) {
if(comment.length > 800) {
return false;
}
else {
return true;	
}
}



$(document).ready(function() {


$(document).on("click",".postCommentTextarea",function(){
$(this).find(".placeholder").remove();	
$(this).attr("data-state","1");
});

$(document).on("focusout",".postCommentTextarea",function(){
if($(this).html().trim() == "") {
$(this).html("<span class='placeholder' style='color:#aaaaaa'>" + $(this).attr("data-placeholder-html") + "</span>");
$(this).attr("data-state","0");
}	
});


$(".postCommentsContainer").scroll(function(){
if(($(this)[0].scrollHeight - ($(this).scrollTop() + $(this).outerHeight()) < 100) && commentsPreventMultipleCalls == false) {
getComments($(this).attr("data-actual-post-id"),$("#commentsModal .singleComment:last-child").attr("data-comment-id"));
commentsPreventMultipleCalls = true;
}
});

function addCommentToPost(postId, comment) {

if(typeof postId == "undefined" || typeof comment == "undefined") {
return false;	
}

if(validateCommentLength(comment) == false) {
Materialize.toast("Comments Cannot Be Longer Than 800 Characters, Currently At " + comment.length,4000,"red");	
return false;	
}

// the post element that is the parent of this comment.
var commentSinglePostElement = $(".singlePost [data-actual-post-id=" + $(this).attr("data-actual-post-id") + "]");

$.post({
url:"components/add_comment_to_post.php",
data:{
"post_id": postId,
"comment": comment
},
success: function(data) {

var dataArr = JSON.parse(data);

$(".postCommentsContainer").prepend(dataArr[0]);
eval(dataArr[1]);

setNewNumber($("#totalNumberOfComments"),"data-total-number",true,true,"");
if(commentSinglePostElement.length > 0) {
// update the comments button's comment number element
setNewNumber(commentSinglePostElement.find(".commentButtonCommentsNumber"),"data-total-number",true,true,"");
}

$("#commentsModal .avatarRotateDiv").each(function(){
$(this).css("transform","rotate(" + $(this).attr("data-rotate-degree") + "deg)");	
fitToParent("#" + $(this).find(".commenterAvatarImages").attr("id"));
adaptRotateWithMargin($(this).find("img"),$(this).attr("data-rotate-degree") ,false);
});


// initialize dropdowns	
$('#commentsModal .dropdown-button').dropdown({
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

$('#commentsModal .actualCommentComment').readmore({speed: 500, moreLink: '<a href="#" class="readMore" style="font-size:12px;color:#aaaaaa;position:relative;top:-4px;">Read More</a>',
lessLink: '<a href="#" class="readLess" style="font-size:12px;color:#aaaaaa;position:relative;top:-4px;">Close</a>'});

commentsIntervalObject.intervalFunction();

}	
});
	
}


/* upvoting and downvoting */

$(document).on("click","#commentsModal .upvoteOrDownvote",function(){

if(typeof $(this).attr("data-upvote-or-downvote") == "undefined" || typeof $(this).parents(".postCommentActions").attr("data-comment-id") == "undefined") {
return false;	
}

var thisUpvotesObject = $(this).parent().find(".upvoteOrDownvote[data-upvote-or-downvote=upvote]");
var thisDownvotesObject = $(this).parent().find(".upvoteOrDownvote[data-upvote-or-downvote=downvote]");
var thisUpvotesNumberObject = $(this).parent().find(".commentUpvotes");
var thisDownvotesNumberObject = $(this).parent().find(".commentDownvotes");


$.post({
url:"components/upvote_downvote_comment.php",
data:{
"comment_id":$(this).parents(".postCommentActions").attr("data-comment-id"),
"type":$(this).attr("data-upvote-or-downvote")
},
success:function(data) {
thisUpvotesObject.removeClass('upvoteOrDownvoteActive');
thisDownvotesObject.removeClass('upvoteOrDownvoteActive');
	
eval(data);	
}	
});

});



/* deleting comments */


// want to show them the delete button (a dropdown actually) after they hold (longpress) the comment
var deleteCommentTimeout;
$(document).on("touchstart","#commentsModal .singleComment",function(){

var deleteCommentButton = $(this).find(".deleteCommentButton");

deleteCommentTimeout = setTimeout(function(){
deleteCommentButton.dropdown('open');
},1200);
}).on("touchend touchmove",".singleComment",function(){
clearTimeout(deleteCommentTimeout);	
});



// when a user presses the delete comment button
$(document).on("click",".deleteComment",function(){

if(typeof $(this).attr("data-comment-id") == "undefined") {
return false;	
}	

var thisCommentObject = $(this).parents(".singleComment");

// the post element that is the parent of this comment.
var commentSinglePostElement = $(".singlePost [data-actual-post-id=" + thisCommentObject.parents(".postCommentsContainer").attr("data-actual-post-id") + "]");

deleteComment($(this).attr("data-comment-id"),function(){thisCommentObject.fadeOut('fast',function(){$(this).remove();});});

setNewNumber($("#totalNumberOfComments"),"data-total-number",false,true,"");

if(commentSinglePostElement.length > 0) {
// update the comments button's comment number element
setNewNumber(commentSinglePostElement.find(".commentButtonCommentsNumber"),"data-total-number",false,true,"");
}

});

function deleteComment(commentId, callback) {

$.post({
url:"components/delete_comment.php",
data:{"comment_id":commentId},
success:function(data) {
if(data == 1) {
if(typeof callback != "undefined") {	
callback();	
}
}
}
});
	
}


});