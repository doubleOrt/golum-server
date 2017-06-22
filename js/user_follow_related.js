
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


