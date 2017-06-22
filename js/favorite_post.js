function favoritePost(postId, callback) {

if(typeof postId == "undefined") {
return false;	
}

$.post({
url:"components/favorite_post.php",
data:{"post_id":postId},
success:function(data) {
if(typeof callback != "undefined") {	
// if function has a parameter that handles the argument we pass to it.
if(callback.length > 0) {
eval(data);	
callback(favorited);
}
}
}	
});
	
}