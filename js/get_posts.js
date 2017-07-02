

// value will be set on document load.
var MAIN_SCREEN_POSTS_CONTAINER;
// MAIN_SCREEN_POSTS_SCROLLER has to be different than MAIN_SCREEN_POSTS_CONTAINER, because of the "itemsWithImages". 
var MAIN_SCREEN_POSTS_SCROLLER;


// this variable has one job: prevent users from making multiple calls for the exact same data, for example an impatient user might tap one of the buttons that calls this function twice, what would happen is, the first call would be normal, but the second call, because it was made at the same time the first call was made, would request the exact same data from the php file, so what do you think would happen when both these calls get their data ? they both process the data, and you end up with a duplicate for each post, to avoid this we prevent the user from making calls when this variable is set to true, and we set it to true everytime a call is made, and we don't set it back to false until the call returns.
var blockCallsToGetPosts = false;


function getPosts(urlOfFile,dataObject,callback) {

if(blockCallsToGetPosts == false) {	

$.get({
url:urlOfFile,
data:dataObject,
success:function(data) {	

// if the ajax call actually returned something.
if(data != "") {
		
var data_arr = JSON.parse(data);	

if(typeof callback == "function") {
callback(data_arr);	
}

} 

blockCallsToGetPosts = false;
}
});

blockCallsToGetPosts = true;	
}

}


function markUpProcessor(data, appendMarkUpTo, empty_message, callback) {

// these variables will have to be bound to this object so that we can use them in our callback function, also any variable that you use in the callback function has to be bound to this object first.
this.dataArr = data;	
this.appendMarkUpTo = appendMarkUpTo;


if(dataArr.length < 1 && appendMarkUpTo.find(".singlePost").length < 1) {
appendMarkUpTo.html("<div class='emptyNowPlaceholder'><i class='material-icons'>info</i><br>" + empty_message + "</div>")	
}



// append the posts to the container of the posts, then hide the loading, and finally show the container of the posts.	
for(var i = 0; i < data.length; i++) {	
appendMarkUpTo.append(get_post_markup(dataArr[i]));
}
hideLoading();
appendMarkUpTo.show();	


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


getVotedPostsVotesMarkup();

// check if the callback parameter has an actual callback function, if so, call it.
if(typeof callback != "undefined") {
callback();
}

$(".loadPostComponents").removeClass("loadPostComponents");
}






function get_post_markup(data , requested_by) {
		
var poster_full_name = data["post_owner_info"]["first_name"] + " " +  data["post_owner_info"]["last_name"];

var random_num = Math.floor(Math.random() * 1000000);	
var avatar_id = "avatar" + random_num;


var required_height = (data["post_type"] == 3 || data["post_type"] == 4 ? "height: 50%;" : "height: 100%;"); 

// logic for determining the column classes
var container_grids = [];
// if the post is a 2-card/4-card post:
if(data["post_type"] % 2 == 0) {
for(x = 0; x < data["post_type"]; x++) {
container_grids[x] = "l6 m6 s6";	
}
}
// if it is a 1-card/3-card post:
else {
for(x = 0; x < data["post_type"]; x++) {
if(x != (data["post_type"] - 1)) {	
container_grids[x] = "l6 m6 s6";	
}
else {
container_grids[x] = "l12 m12 s12";		
}
}	
}



var imagesContainerChildren = "";	


if(data["post_type"] != 1) {	
for(x = 0; x < data["post_type"]; x++) {
	
var image_src = "posts/" + data["post_id"] + "-" + x + "." + data["post_file_types"][x];

var image_id = "image" + random_num;

imagesContainerChildren += `
<div class='col ` + container_grids[x] + ` postSingleImageContainer' data-option-index='` + x + `' style='` + required_height + `' data-image-path='` + image_src + `'>
<img class='postSingleImageContainerImage' id='` + image_id + `' src='` + image_src + `' alt='Photo ` + x + `'/>
</div><!-- end .postSingleImageContainer -->
<script>

$('#` + image_id + `').on('load',function(){	
fitToParent('#` + image_id + `');	
});

</script>
`;
}	
}
// need to bend some rules and such for those type 1 posts. (the ones that you can like or dislike instead of choose)
else {

image_src = "posts/" + data["post_id"] + "-0." + data["post_file_types"][0];

var image_id = "image" + random_num;

imagesContainerChildren += `
<div class='col ` + container_grids[0] + ` postSingleImageContainer' style='` + required_height + `' data-image-path='` + image_src + `'>
<img class='postSingleImageContainerImage' id='` + image_id + `' src='` + image_src + `' alt='Photo 0'/>
</div><!-- end .postSingleImageContainer -->
<div class='col l6 m6 s6 postSingleImageContainer' data-option-index='0' data-image-path='` + image_src +`' style='height:100%;transform:translate(0,-100%);background:transparent'>
</div><!-- end .postSingleImageContainer -->
<div class='col l6 m6 s6 postSingleImageContainer' data-option-index='1' data-image-path='` + image_src +`' style='height:100%;transform:translate(0,-100%);background:transparent'>
</div><!-- end .postSingleImageContainer -->
<script>

$('#` + image_id + `').on('load',function(){	
fitToParent('#` + image_id + `');	
});

</script>
`;
}

	
/* this .loadPostComponents class is just so we can distinguish between already loaded classes and the newly loaded so we don't load post components for posts that we already have those
components, we remove this class from a post immediately after we have loaded its components */
return `<div class='singlePost loadPostComponents ` + requested_by + ` col l12 m12 s12' data-actual-post-id='` + data["post_id"] + `' data-post-type='` + data["post_type"] + `' data-poster-id='` + data["post_posted_by"] + `' data-positive-icon='` + (data["post_type"] != 1 ? "check" : "thumb_up") + `' data-negative-icon='` + (data["post_type"] != 1 ? "close" : "thumb_down") + `' data-already-voted='` + (data["base_user_already_voted"] == true ? "true" : "false") + `'>
<div class='postTop'>
<div class='postTitle scaleItem'>
` +   handle_tags(data["post_title"]) + `
</div><!-- end .postTitle -->
<a href='#' class='postSettingsButton dropdown-button opacityChangeOnActive' data-activates='postSettings` + random_num + `'><i class='material-icons'>more_vert</i></a>
</div><!-- end .postTop -->

<div class='postImagesContainer row'>
` + imagesContainerChildren + `
</div>

<div class='postBottomContainer row'>

<ul id='postSettings` + random_num + `' class='dropdown-content'>
<li class='reportPost' data-actual-post-id='` + data["post_id"] + `'><a href='#!' class='waves-effect waves-lightgrey'>Report</a></li>
` + (data["posted_by_base_user"] == true ? `<li class='deletePost' data-actual-post-id='` + data["post_owner_info"]["id"] + `'><a href='#!' class='waves-effect waves-lightgrey'>Delete</a></li>` : "") + `
</ul>



<div class='postActionsContainer'>

<div class='postButtonsContainer'>
</div><!-- end .postButtonsContainer -->


<div class='posterInfoMegaContainer'><!-- contains the user's avatar, their fullname and the time of the post -->

<div class='avatarContainer posterAvatarContainer'>
<div class='avatarContainerChild posterAvatarContainerChild showUserModal modal-trigger' data-user-id='` + data["post_owner_info"]["id"] + `' data-target='user_modal'>
<div class='rotateContainer' style='margin-top:` + data["post_owner_info"]["avatar_positions"][0] + `%;margin-left:` + data["post_owner_info"]["avatar_positions"][1] + `%;'>
<div class='avatarRotateDiv ` + (data["posted_by_base_user"] == true ? "baseUserAvatarRotateDivs" : "") + `' data-rotate-degree='` + data["post_owner_info"]["avatar_rotate_degree"] + `' style='transform:rotate(` + data["post_owner_info"]["avatar_rotate_degree"] + `deg)'>
<img id='` + avatar_id + `' class='avatarImages posterAvatarImages' src='` + (data["post_owner_info"]["avatar_picture"] != "" ? data["post_owner_info"]["avatar_picture"] : LetterAvatar(poster_full_name, 120)) + `' alt='Image'/>
</div><!-- end .avatarRotateDiv -->
</div><!-- end .rotateContainer -->
</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->

<div class='posterInfoChild'>
<a href='#modal1' class='commonLink showUserModal' data-user-id='` + data["post_owner_info"]["id"] + `' data-open-main-screen='#main_screen_user_profile'>` + poster_full_name + `</a>
<div class='postDate'></div><!-- end .postDate -->
</div><!-- end .posterInfoChild -->
</div><!-- end .posterInfoMegaContainer -->

</div>

</div><!-- end .postBottomContainer -->
</div><!-- end .singlePost -->
<script>
$('#` + avatar_id + `').on('load',function(){	
fitToParent('#` + avatar_id + `');	
adaptRotateWithMargin($(this), ` + data["post_owner_info"]["avatar_rotate_degree"] + `,false);
});
</script>
`;	
}




$(document).ready(function(){
	
	
MAIN_SCREEN_POSTS_CONTAINER = $("#allPostsContainer");
MAIN_SCREEN_POSTS_SCROLLER = $("#main_screen_main_posts_container");
	
	
/* ----- getting posts ----- */

$(document).on("click",".getPosts",function(){
emptyAllPostsContainer();
getPosts("components/get_posts.php",{"row_offset":0},function(data_arr){
markUpProcessor(data_arr,MAIN_SCREEN_POSTS_CONTAINER, "Your feed is so empty :(", function(){MAIN_SCREEN_POSTS_CONTAINER.attr("data-contains-which-posts","posts");MAIN_SCREEN_POSTS_CONTAINER.scrollTop(0);});	
});		
});

// make a call to this function on page load
getPosts("components/get_posts.php",{"row_offset":0},function(data_arr){
markUpProcessor(data_arr,MAIN_SCREEN_POSTS_CONTAINER, "Nothing here :(", function(){MAIN_SCREEN_POSTS_CONTAINER.attr("data-contains-which-posts","posts");MAIN_SCREEN_POSTS_CONTAINER.scrollTop(0);});	
});		


/* when a user wants to see the featured posts */
$(document).on("click",".getFeaturedPosts",function(){
emptyAllPostsContainer();
getPosts("components/get_featured_posts.php",{"row_offset":0},function(data_arr){
markUpProcessor(data_arr,MAIN_SCREEN_POSTS_CONTAINER, "We don't know why there is nothing here either :(", function(){MAIN_SCREEN_POSTS_CONTAINER.attr("data-contains-which-posts","featuredPosts");MAIN_SCREEN_POSTS_CONTAINER.scrollTop(0);});	
});		
});

/* when a user wants to see their favorited posts */
$(document).on("click",".getMyFavoritePosts",function(){	
emptyAllPostsContainer();
getPosts("components/get_my_favorite_posts.php",{"row_offset":0},function(data_arr){
markUpProcessor(data_arr,MAIN_SCREEN_POSTS_CONTAINER, "After you favorite some posts, you should look for them here :)" , function(){MAIN_SCREEN_POSTS_CONTAINER.attr("data-contains-which-posts","favoritePosts");MAIN_SCREEN_POSTS_CONTAINER.scrollTop(0);});	
});		
});


/* user is scrolling the page content */

MAIN_SCREEN_POSTS_SCROLLER.scroll(function(){

if(MAIN_SCREEN_POSTS_CONTAINER.css("display") != "none") {	

if(($(this)[0].scrollHeight - ($(this).scrollTop() + $(this).outerHeight()) < 650) && blockCallsToGetPosts == false) {
	
var allPostsContainerContainsWhichPosts = MAIN_SCREEN_POSTS_CONTAINER.attr("data-contains-which-posts");

if(allPostsContainerContainsWhichPosts == "posts") {
getPosts("components/get_posts.php",{"row_offset":$("#allPostsContainer .singlePost").length},function(data_arr){
markUpProcessor(data_arr,MAIN_SCREEN_POSTS_CONTAINER, "Your feed is so empty :(", function(){MAIN_SCREEN_POSTS_CONTAINER.attr("data-contains-which-posts","posts")});	
});		
}	
else if(allPostsContainerContainsWhichPosts == "featuredPosts") {
getPosts("components/get_featured_posts.php",{"row_offset":$("#allPostsContainer .singlePost").length},function(data_arr){
markUpProcessor(data_arr,MAIN_SCREEN_POSTS_CONTAINER, "We don't know why there is nothing here either :(" ,function(){MAIN_SCREEN_POSTS_CONTAINER.attr("data-contains-which-posts","featuredPosts")});	
});		
}	
else if(allPostsContainerContainsWhichPosts == "favoritePosts") {
getPosts("components/get_my_favorite_posts.php",{"row_offset":$("#allPostsContainer .singlePost").length},function(data_arr){
markUpProcessor(data_arr,MAIN_SCREEN_POSTS_CONTAINER, "After you favorite some posts, you shall find them here :P" ,function(){MAIN_SCREEN_POSTS_CONTAINER.attr("data-contains-which-posts","favoritePosts")});	
});		
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
// empty #singlePostsContainer
$("#singlePostsContainer").html("");
getPosts("components/get_single_post.php",{"post_id":$(this).attr("data-actual-post-id")},function(data_arr){
markUpProcessor(data_arr,$("#singlePostsContainer"), "We don't know why the post didn't appear either :(");	
});		
});





// when a user wants to view another's posts via clicking the "posts" button on their user modal 
$(document).on("click",".getUserPosts",function(){

if(typeof $(this).attr("data-user-id") == "undefined") {
return false;	
}

/* if the element that was clicked has a "data-first-name" attribute set, it means that the .modalHeaderFullName of the 
#userPostsModal should be set to that "data-first-name" (which should be the name of the user that the modal is being opened for). 
If it is not set, than just use the default. */
var modal_label = (typeof $(this).attr("data-first-name") != "undefined" ? $(this).attr("data-first-name") : "Posts");
$("#userPostsModal .modalHeaderFullName").html(modal_label);

//empty the #userPostsContainer of the last query's posts
$("#userPostsContainer").html("");

$("#userPostsContainer").attr("data-user-id",$(this).attr("data-user-id"));
$("#userPostsModal .navRightItemsMobile .follow_user").attr("data-user-id", $(this).attr("data-user-id"));

/* if base user is looking at their own posts, hide the "follow" button (because not hiding it would not make any sense), if they are looking 
at someone else's posts, show it. */
if($(this).attr("data-user-id") == BASE_USER_ID_HOLDER.attr("data-user-id")) {
$("#userPostsModal .navRightItemsMobile .follow_user").hide();
}
else {
$("#userPostsModal .navRightItemsMobile .follow_user").show();	
}

getPosts("components/get_user_posts.php",{"user_id": $(this).attr("data-user-id"), "row_offset": 0},function(data_arr){
markUpProcessor(data_arr[0], $("#userPostsContainer"), "This user does not have a single post, such a loser.");		
$("#userPostsModal .navRightItemsMobile .follow_user").html((data_arr[1] == 0 ? "Follow +" : "Unfollow"));
});
});
// user is infinite scrolling the user posts modal
$("#userPostsContainer").scroll(function(){
if($(this).scrollTop() > ($(this)[0].scrollHeight - 650) && $(this).find(".singlePost").length > 0) {
getPosts("components/get_user_posts.php",{"user_id": $(this).attr("data-user-id"), "row_offset": $(this).find(".singlePost").length},function(data_arr){
markUpProcessor(data_arr[0], $("#userPostsContainer"), "This user does not have a single post, such a loser.");		
});
}
});


/*
// when users want to view posts that match their search terms
$(document).on("click",".openGetPostsByTitleModal",function(){

if(typeof $(this).attr("data-search-value") == "undefined") {
return false;	
}

//empty the #tagPostsContainer of the last query's posts
$("#postsByTitleContainer").html("");

$("#postsByTitleContainer").attr("data-search-value",$(this).attr("data-search-value"));

// set the #getPostsByTitleModal's title to the name of the tag.
$("#getPostsByTitleModal .modal-header .modalHeaderFullName").html("'" + $(this).attr("data-search-value") + "'");

getPosts("components/get_posts_by_title_search.php",{"row_offset":0,"search_value":$(this).attr("data-search-value")},markUpProcessor,$("#postsByTitleContainer"));		

});
// infinite scrolling postByTitle
$("#postsByTitleContainer").scroll(function(){
if($(this).scrollTop() > ($(this)[0].scrollHeight - 650) && blockCallsToGetPosts == false && $(this).find(".singlePost").length > 0) {
getPosts("components/get_posts_by_title_search.php",{"row_offset":$(this).find(".singlePost:last-child").attr("data-post-id"),"search_value":$(this).attr("data-search-value")},markUpProcessor,$("#postsByTitleContainer"));		
}
});

*/



$(document).on("click",".getTagPosts",function(e){	

if(typeof $(this).attr("data-tag") == "undefined" && typeof $("#tagPostsModal").attr("data-tag") == "undefined") {
return false;	
}

var tag = (typeof $(this).attr("data-tag") != "undefined" ? $(this).attr("data-tag") : $("#tagPostsModal").attr("data-tag"));

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
getPosts("components/get_tag_posts.php",{"row_offset":0,"tag": tag,"sort_posts_by":$("#tagPostsModal").attr("data-hot-or-new")},function(data_arr){	
markUpProcessor(data_arr[0],$("#tagPostsContainer"), "Nothing here :(");	
});
}
// user is not switching between the tabs
else {
$("#tagPostsModal").attr("data-tag", tag);
$("#tagPostsModal .modal-header .modalHeaderFullName").html(tag);	
$("#tagPostsModal .navRightItemsMobile").find(".addTagFromTagPostsModal").attr("data-tag", tag);

getPosts("components/get_tag_posts.php",{"row_offset":0,"tag": tag,"sort_posts_by":$("#tagPostsModal").attr("data-hot-or-new")},function(data_arr){	
markUpProcessor(data_arr[0],$("#tagPostsContainer"), "Nothing here :(");	
// add the "follow tag" button which should be in dataArr[1]
$("#tagPostsModal .navRightItemsMobile").find(".addTagFromTagPostsModal").attr("data-current-state", data_arr[1]);
$("#tagPostsModal .navRightItemsMobile").find(".addTagFromTagPostsModal").html(data_arr[1] == 0 ? "Follow +" : "Unfollow");
});

}

});
// infinite scrolling tagPosts
$("#tagPostsContainer").scroll(function(){
if(($(this)[0].scrollHeight - ($(this).scrollTop() + $(this).outerHeight()) < 650) && $(this).find(".singlePost").length > 0) {

getPosts("components/get_tag_posts.php",{"row_offset":$("#tagPostsModal .singlePost").length,"tag":$("#tagPostsModal").attr("data-tag"),"sort_posts_by":$("#tagPostsModal").attr("data-hot-or-new")},function(data_arr){
markUpProcessor(data_arr[0], $("#tagPostsContainer"), "Nothing here :(");	
});
		
}
});



/* ----- END the reign of getting posts ----- */



	
	
});
