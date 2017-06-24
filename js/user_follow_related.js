


// when users want to see the people they are following
function getBaseUserFollowings(markUpTarget) {
	
$.get({
url:"components/get_followings.php",
type:"get",
success: function(data) {
markUpTarget.html(data);
}
});
	
}

// when users want to see people who follow them
function getBaseUserFollows(markUpTarget) {

$.get({
url:"components/get_following_me.php",
type:"get",
success: function(data) {
markUpTarget.html(data);
}
});
	
}



/* call this function when users want to follow or unfollow other users, takes the target user's id and 
a callback function with at least one parameter as arguments.  will return 0 if the user is now followed, 
and 1 if they are now unfollowed. */
function addOrRemoveContact(userId, callback) {

if(typeof userId == "undefined") {
return false;	
}

$.get({
url:"components/add_remove_contacts.php",
data:{user_id:userId},
type:"get",
success: function(data) {

if(data != "") {
// if the callback has a parameter that we can pass the data to	
if(callback.length > 0) {	
callback(data);
}
}

}
});
	
}



/* call this function when users want to block or unblock other users, takes the target user's id and 
a callback function with at least one parameter as arguments.  will return 0 if the user is now blocked, 
and 1 if they are now unblocked. */
function blockOrUnblockUser(userId, callback) {
$.get({
url:"components/block_user.php",
data:{"user_id":userId},
success:function(data) {
if(data != "") {
// if the callback has a parameter that we can pass the data to	
if(callback.length > 0) {	
callback(data);
}
}
}	
});
}


// call this function to set the user's follows number on the profile section.
function set_user_profile_follows_num(follows_num) {
$('#userModalFollowedBy').html("<span class='userFollowsNum'>" + follows_num + "</span> " + (follows_num != 1 ? " Followers" : " Follower"));
}




$(document).ready(function(){
	
	
// when a user wants to see their contacts
$(document).on("click",".contactsButton",function(){
getBaseUserFollowings($("#contactsModalContentChild"));
});

// when a user wants to see people who follow them
$(document).on("click",".followingMeButton",function(){	
getBaseUserFollows($("#followingMeModalContentChild"));
});






// when users want to follow/unfollow another user 
$(document).on("click","#user_profile_follow_button",function(){

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

// if newState is 0, user pressed the follow button and they are now following their target
if(newState == "0") {	
$('#user_profile_follow_button').html('Unfollow');
set_user_profile_follows_num(parseFloat($('.userFollowsNum').html()) + 1);
}
// user just unfollowed the target user
else if(newState == "1") {
$('#user_profile_follow_button').html('Follow');
set_user_profile_follows_num(parseFloat($('.userFollowsNum').html()) - 1);
}
	
}

});



// used when user blocks or unblocks contacts.
$(document).on("click","#user_profile_block_button",function(){
	
if($(this).attr("data-user-id") == "undefined") {
return false;	
}	
	
blockOrUnblockUser($(this).attr("data-user-id"),blockOrUnblockUserCallback);

function blockOrUnblockUserCallback(newState) {
// the user is now blocked	
if(newState == "0") {
Materialize.toast('User Blocked, Tap Button To Unblock',3000,'green');	
$("#user_profile_block_button").html("Unblock");	
$("#user_profile_block_button").attr("data-current-state","1");	
$("#user_profile_follow_button").html("Follow");
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

$("#user_profile_follow_button").css({"pointer-events":"none","opacity":".5"});
$(".modalFooterButtonReverse.blockUser").html("Unblock");
}	
else if(newState == "1") {
Materialize.toast('User Unblocked, Tap Button To Block',3000,'green');	
$("#user_profile_block_button").html("Block");	
$("#user_profile_block_button").attr("data-current-state","0");		
$("#user_profile_follow_button").css({"pointer-events":"auto","opacity":"1"});
$(".modalFooterButtonReverse.blockUser").html("Block");	
}
}

});

/* ----- END follows and blocks ----- */
	
	
});