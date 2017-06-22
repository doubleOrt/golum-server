
function addTagsToUserById(tag,callback) {
	
$.post({
data:{"tag":tag},	
url:"components/add_tags.php",
success:function(data) {
callback(data);
}
});

}

function removeTagsFromUserById(tag,callback) {

$.post({
data:{"tag":tag},	
url:"components/remove_tags.php",
success:function(data) {
callback(data);
}
});

}




