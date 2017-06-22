
// this variable has one job: prevent users from making multiple calls for the exact same data, for example an impatient user might tap one of the buttons that calls this function twice, what would happen is, the first call would be normal, but the second call, because it was made at the same time the first call was made, would request the exact same data from the php file, so what do you think would happen when both these calls get their data ? they both process the data, and you end up with a duplicate for each post, to avoid this we prevent the user from making calls when this variable is set to true, and we set it to true everytime a call is made, and we don't set it back to false until the call returns.
var blockCallsToGetPosts = false;


function getPosts(urlOfFile,dataObject,markUpProcessor,appendMarkUpTo,appendMarkUpToFromMainScreen,markUpProcessorCallback) {		

if(blockCallsToGetPosts == false) {	

$.get({
url:urlOfFile,
data:dataObject,
success:function(data) {	
// if the ajax call actually returned something.
if(data != "") {
markUpProcessor(data,appendMarkUpTo,appendMarkUpToFromMainScreen,markUpProcessorCallback);

if(dataObject.last_post_id == 0) {
appendMarkUpTo.scrollTop(0);	
}

} 

blockCallsToGetPosts = false;
}
});

blockCallsToGetPosts = true;	
}

}


function markUpProcessor(data,appendMarkUpTo,callback) {

// these variables will have to be bound to this object so that we can use them in our callback function, also any variable that you use in the callback function has to be bound to this object first.
this.dataArr = JSON.parse(data);	
this.appendMarkUpTo = appendMarkUpTo;
	
	
// append the posts to the container of the posts, then hide the loading, and finally show the container of the posts.	
appendMarkUpTo.append(dataArr[0]);
hideLoading();
appendMarkUpTo.show();	


// process the avatars
$(".loadPostComponents .avatarRotateDiv").each(function(){
$(this).css("transform","rotate(" + $(this).attr("data-rotate-degree") + "deg)");	
fitToParent("#" + $(this).find(".posterAvatarImages").attr("id"));
adaptRotateWithMargin($(this).find("img"),$(this).attr("data-rotate-degree") ,false);
});


// initialize the materialize dropdowns	
$('.loadPostComponents .dropdown-button').dropdown({inDuration: 300,outDuration: 225,constrain_width: false,hover: false,gutter: 0,belowOrigin: true,alignment: 'left', stopPropagation: true});	

var postIdsArr = [];
$(".loadPostComponents").each(function(){
postIdsArr.push($(this).attr("data-actual-post-id"));
});

$.get({
url:"components/post_activities.php",
data:{"post_ids":postIdsArr},
success:function(data) {
var dataArr = JSON.parse(data);
for(var i = 0;i<dataArr.length;i++) {
$(".singlePost[data-actual-post-id=" + dataArr[i][0] + "]").find(".postDate").html(dataArr[i][1]);
$(".singlePost[data-actual-post-id=" + dataArr[i][0] + "]").find(".postFavoritesNum").html(dataArr[i][2]);
$(".singlePost[data-actual-post-id=" + dataArr[i][0] + "]").find(".postFavoritesNum").attr("data-total-number",dataArr[i][2]);
$(".singlePost[data-actual-post-id=" + dataArr[i][0] + "] .postButtonsContainer").html(dataArr[i][3]);
}
}	
});

/*
// for updating the post activities every 120000 ms (2 mins)
postsIntervalVar = new EnhancedInterval(whateverPostsIntervalVar,120000,function(){
});
whateverPostsIntervalObject.intervalFunction();
*/

getVotedPostsVotesMarkup();

// check if the callback parameter has an actual callback function, if so, call it.
if(typeof callback != "undefined") {
callback();
}

$(".loadPostComponents").removeClass("loadPostComponents");
}
