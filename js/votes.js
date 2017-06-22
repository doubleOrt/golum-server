


function postVote(postObject, voteOptionIndex) {

if(typeof postObject.attr("data-actual-post-id") == "undefined" || typeof postObject.attr("data-poster-id") == "undefined" || typeof postObject.attr("data-already-voted") == "undefined" || typeof voteOptionIndex == "undefined") {
return false;	
}
else {
	
var postId = postObject.attr("data-actual-post-id");
var posterId = postObject.attr("data-poster-id");
var voterHasAlreadyVoted = postObject.attr("data-already-voted");

$.post({
url:"components/vote.php",
data:{
"post_id": postId,
"poster_id": posterId,
"option_index": voteOptionIndex,
"already_voted": voterHasAlreadyVoted
},
success:function(data) {	
// so user can't vote twice.
postObject.attr("data-already-voted","true");
}	
});

}
	
}


function getVotedPostsVotesMarkup() {

$(".loadPostComponents").each(function(){	
if($(this).find(".selectedOptionContainer").length == 0 && $(this).find(".votesContainer").length == 0) {	
getVotesMarkup($(this),true,false);	
}
});

}


function getVotesMarkup(singlePostObject,doAnimations,reactToPost) {	

if(typeof singlePostObject == "undefined" || typeof singlePostObject.attr("data-actual-post-id") == "undefined" || typeof singlePostObject.attr("data-post-type") == "undefined" || typeof singlePostObject.attr("data-positive-icon") == "undefined" || typeof singlePostObject.attr("data-negative-icon") == "undefined") {
return "";
}

$.post({
url:"components/get_votes_markup.php",
data:{
"post_id":singlePostObject.attr("data-actual-post-id"),
"post_type":singlePostObject.attr("data-post-type"),
"positive_icon":singlePostObject.attr("data-positive-icon"),
"negative_icon":singlePostObject.attr("data-negative-icon")
},
success:function(data) {

var dataArr = JSON.parse(data);	

// remove markup if it already exists.
singlePostObject.find(".postSingleImageContainer .votesContainer").remove();

for(var i = 0;i<dataArr.length;i++) {	
singlePostObject.find(".postSingleImageContainer[data-option-index=" + dataArr[i][0] + "]").prepend(dataArr[i][1]);
if(doAnimations == true) {
singlePostObject.attr("data-post-type") == "3" || singlePostObject.attr("data-post-type") == "4" ? singlePostObject.find(".votesContainerChild").addClass("skewScaleItem") : singlePostObject.find(".votesContainerChild").addClass("skewScaleVerticallyCenteredItem");
}
}


// show the user's friends who voted 
getFriendsWhoVotedOnThisPost(singlePostObject,doAnimations);


if(singlePostObject.attr("data-already-voted") == "true") {
singlePostObject.find(".votesContainer").show();	

// if the user has already voted.
singlePostObject.find(".posterInfoMegaContainer").css("display","table");
}

}	
});
}





/* we do this to avoid relying on an ajax call to give us the new markup for the votes after the user votes, because it would be slower, so instead whenever the user votes, just pass
the .singlePost object and the option index of the user's vote to this function and it will handle everything. */
function showNewPostVotes(singlePostElement,userOptionIndex) {
	
singlePostElement.find(".votesContainer").show();

var oldVotesNewNum = parseFloat(singlePostElement.find(".postSingleImageContainer .totalVotesNumber[data-user-vote='true']").attr("data-votes-number")) - 1;
var newVotesNewNum = parseFloat(singlePostElement.find(".postSingleImageContainer[data-option-index='" + userOptionIndex + "']").find(".totalVotesNumber").attr("data-votes-number")) + 1;

setNewNumber(singlePostElement.find(".postSingleImageContainer .totalVotesNumber[data-user-vote='true']"),"data-votes-number",false,false," Vote"  + (oldVotesNewNum == 1 ? "" : "s"));	
singlePostElement.find(".postSingleImageContainer").find(".totalVotesNumber, .votesIcon").attr("data-user-vote","false");	
singlePostElement.find(".postSingleImageContainer[data-option-index='" + userOptionIndex + "']").find(".totalVotesNumber , .votesIcon").attr("data-user-vote","true");	
setNewNumber(singlePostElement.find(".postSingleImageContainer .totalVotesNumber[data-user-vote='true']"),"data-votes-number",true,false," Vote" + (newVotesNewNum == 1 ? "" : "s"));	

if(singlePostElement.attr("data-post-type") != "1") {
singlePostElement.find(".votesIcon i").html("close");	
singlePostElement.find(".totalVotesNumber[data-user-vote='true']").parent().find(".votesIcon i").html("check");	
} 

var allVotesNumber = 0;
singlePostElement.find(".votesContainer").each(function(){
// just for the worst case scenario. we check if the data-votes-number actually exists.
if(typeof $(this).find(".totalVotesNumber").attr("data-votes-number") != "undefined") {
allVotesNumber += parseFloat($(this).find(".totalVotesNumber").attr("data-votes-number"));
}	
});

// now recalculate the percentages.
singlePostElement.find(".votesContainer").each(function(){
// just for the worst case scenario. we check if the data-votes-number actually exists.
if(typeof $(this).find(".totalVotesNumber").attr("data-votes-number") != "undefined") {
$(this).find(".totalVotesPercentage").html(Math.round((parseFloat($(this).find(".totalVotesNumber").attr("data-votes-number")) / allVotesNumber) * 100) + "%");
// give a new height to the votesLine.
var newVotesLineHeight = Math.round(parseFloat($(this).find(".totalVotesNumber").attr("data-votes-number")) / allVotesNumber) * parseFloat($(this).find(".votesLineContainer").attr("data-max-height")) + "px";
$(this).find(".votesLineContainer").css("height",newVotesLineHeight);
}	
});

// re-style majorities and minorities
singlePostElement.find(".votesIcon, .votesLine").removeClass("majorityVoteBackgroundColor").addClass("minorityVoteBackgroundColor");
var majorityVotes = getMajorityVoteIndex(singlePostElement);
for(var i = 0;i < majorityVotes.length;i++) {
singlePostElement.find(".postSingleImageContainer[data-option-index='" + majorityVotes[i] + "'] .votesIcon, .postSingleImageContainer[data-option-index='" + majorityVotes[i] + "'] .votesLine").removeClass("minorityVoteBackgroundColor").addClass("majorityVoteBackgroundColor");	
}


}



// gives you an array that contains the index of the majority vote(s) in a post.
function getMajorityVoteIndex(singlePostElement) {

var majorityVotes = [];

singlePostElement.find(".postSingleImageContainer").each(function(){
if(typeof $(this).attr("data-option-index") != "undefined") {
var thisTotalVotesNumber = $(this).find(".totalVotesNumber").attr("data-votes-number");
var thisIsMajority = true;
singlePostElement.find(".postSingleImageContainer").each(function(){
var thatTotalVotesNumber = $(this).find(".totalVotesNumber").attr("data-votes-number");
if(thisTotalVotesNumber < thatTotalVotesNumber) {
thisIsMajority = false;
}
});	
if(thisIsMajority == true) {
majorityVotes.push(parseFloat($(this).attr("data-option-index")));	
}
}
});

return majorityVotes;
}





/* pass a single post element, it will tell you if the user's vote falls in the minority, average or majority category based on the percentage of the total votes of the user's option 
relative to the total votes on the post */
function isVoteMajorityOrMinorityOrAverage(singlePostElement) {

var totalVotes = 0;
var userVoteTotalVotes = 0;

var counter = 0;


singlePostElement.find(".postSingleImageContainer").each(function(){
if(typeof $(this).attr("data-option-index") != "undefined") {
var totalVotesElement = $(this).find(".totalVotesNumber");
totalVotes += parseFloat(totalVotesElement.attr("data-votes-number"));	
if(totalVotesElement.attr("data-user-vote") == "true") {
userVoteTotalVotes = totalVotesElement.attr("data-votes-number");
}
counter++;	
}
});
	
	
var userOptionPercentageOfAllVotes = (userVoteTotalVotes / totalVotes) * 100;	
	
return (userOptionPercentageOfAllVotes > 40 ? (userOptionPercentageOfAllVotes > 60 ? 1 : 3) : 2);		

}


// adds those fancy emoji scaling up effect upon voting
function reactToVote(postObject) {

var value = isVoteMajorityOrMinorityOrAverage(postObject);

if(value == 1) {	
postObject.find(".postImagesContainer").append("<div class='postReaction postReactionAnimation'><img src='icons/emojis/14.svg' alt='Trendy'/><br><div class='postReactionText'>Trendy</div></div>");	
}
else if(value == 2) {
postObject.find(".postImagesContainer").append("<div class='postReaction postReactionAnimation'><img src='icons/emojis/73.svg' alt='Trendy'/><br><div class='postReactionText'>Grumpy</div></div>");	
}
else if(value == 3) {
postObject.find(".postImagesContainer").append("<div class='postReaction postReactionAnimation'><img src='icons/emojis/85.svg' alt='Trendy'/><br><div class='postReactionText'>Average</div></div>");		
}
	
}




// get the friends who have voted on this post.
function getFriendsWhoVotedOnThisPost(singlePostObject,scaleModalAnimation) {

$.get({
url:"components/friends_who_voted_on_this.php",
data:{"post_id":singlePostObject.attr("data-actual-post-id")},
success:function(data) {

var dataArr = JSON.parse(data);

for(var i = 0;i<dataArr.length;i++) {
singlePostObject.find(".postSingleImageContainer[data-option-index=" + dataArr[i][0] + "] .votesContainer").find(".friendsWhoVotedThis").remove();	
singlePostObject.find(".postSingleImageContainer[data-option-index=" + dataArr[i][0] + "] .votesContainer").append(dataArr[i][1]);
}

if(scaleModalAnimation == true) {
singlePostObject.find(".friendsWhoVotedThisChild").addClass(".scaleItem");	
}

}	
});
	
}