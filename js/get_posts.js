

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
$(".singlePost[data-actual-post-id=" + dataArr[i][0] + "]").find(".comments_number").attr("data-total-number", dataArr[i][2]);
set_post_comments_number_string($(".singlePost[data-actual-post-id=" + dataArr[i][0] + "]"), dataArr[i][2]);
$(".singlePost[data-actual-post-id=" + dataArr[i][0] + "]").find(".favoritePost i").html((dataArr[i][3] !== 0 ? "bookmark" : "bookmark_border"));
}
}	
});


getVotedPostsVotesMarkup();

// favorite if the callback parameter has an actual callback function, if so, call it.
if(typeof callback != "undefined") {
callback();
}

$(".loadPostComponents").removeClass("loadPostComponents");
}




function get_post_markup(data) {
		
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

var random_num1 = Math.floor(Math.random()*1000000);
var image_id = "image" + random_num1;

imagesContainerChildren += `
<div class='col ` + container_grids[x] + ` postSingleImageContainer' data-option-index='` + x + `' style='` + required_height + `' data-image-path='` + image_src + `'>
<img class='postSingleImageContainerImage' id='` + image_id + `' src='` + image_src + `' alt='Photo ` + x + `'/>
</div><!-- end .postSingleImageContainer -->
<div class='col ` + container_grids[x] + ` postSingleImageContainer vote_holder' data-option-index='` + x + `' style='` + required_height + `position:absolute;left:` + ((x == 1 || x == 3) ? "50%" : "0%") + `;top:` + ((x == 2 || x == 3) ? "50%" : "0%") + `' data-image-path='` + image_src + `'>
</div>
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
<div class='col l6 m6 s6 postSingleImageContainer vote_holder' data-option-index='` + 0 + `' style='` + required_height + `position:absolute;left:0%;top:0%;' data-image-path='` + image_src + `'>
</div>
<div class='col l6 m6 s6 postSingleImageContainer' data-option-index='0' data-image-path='` + image_src +`' style='height:100%;transform:translate(0,-100%);background:transparent'>
</div><!-- end .postSingleImageContainer -->
<div class='col l6 m6 s6 postSingleImageContainer vote_holder' data-option-index='` + 1 + `' style='` + required_height + `position:absolute;left:50%;top:0%;' data-image-path='` + image_src + `'>
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
return `<div class='singlePost loadPostComponents col l12 m12 s12' data-actual-post-id='` + data["post_id"] + `' data-post-type='` + data["post_type"] + `' data-poster-id='` + data["post_owner_info"]["id"] + `' data-positive-icon='favorite' data-negative-icon='close' data-already-voted='` + (data["base_user_already_voted"] == true ? "true" : "false") + `'>



<div class='postImagesContainer row'>

` + imagesContainerChildren + `

<div class='post_images_container_overlay'>
</div><!-- end .post_images_container_overlay -->

<div class='post_images_container_bottom'>
<div class='postButtonsContainer'>
<a href='#sendToFriendModal' class='btn btn-flat modal-trigger sendPostToFriend scaleItem' data-actual-post-id='` + data["post_id"] + `'><i class='material-icons'>send</i></a>
<a href='#commentsModal' class='btn btn-flat showPostComments modal-trigger scaleItem' data-actual-post-id='` + data["post_id"] + `'><i class='material-icons'>comment</i></a>
<a href='#' class='btn btn-flat favoritePost scaleItem' data-actual-post-id='` + data["post_id"] + `'><i class='material-icons'>bookmark</i></a>
</div><!-- end .postButtonsContainer -->

<div class='posterInfoMegaContainer'><!-- contains the user's avatar, their fullname and the time of the post -->

<div class='avatarContainer posterAvatarContainer scaleItem'>
<div class='avatarContainerChild posterAvatarContainerChild showUserModal modal-trigger' data-user-id='` + data["post_owner_info"]["id"] + `' data-target='user_modal'>
<div class='rotateContainer' style='margin-top:` + data["post_owner_info"]["avatar_positions"][0] + `%;margin-left:` + data["post_owner_info"]["avatar_positions"][1] + `%;'>
<div class='avatarRotateDiv ` + (data["posted_by_base_user"] == true ? "baseUserAvatarRotateDivs" : "") + `' data-rotate-degree='` + data["post_owner_info"]["avatar_rotate_degree"] + `' style='transform:rotate(` + data["post_owner_info"]["avatar_rotate_degree"] + `deg)'>
<img id='` + avatar_id + `' class='avatarImages posterAvatarImages' src='` + (data["post_owner_info"]["avatar_picture"] != "" ? data["post_owner_info"]["avatar_picture"] : LetterAvatar(poster_full_name, 120)) + `' alt='Image'/>
</div><!-- end .avatarRotateDiv -->
</div><!-- end .rotateContainer -->
</div><!-- end .avatarContainerChild -->
</div><!-- end .avatarContainer -->

<div class='posterInfoChild'>
<a href='#modal1' class='commonLink showUserModal modal-trigger' data-target='user_modal' data-user-id='` + data["post_owner_info"]["id"] + `'>` + poster_full_name + `</a>
<div class='post_views'><span class='views_number_container'>` + data["post_views"] + `</span> Views</div>
</div><!-- end .posterInfoChild -->

</div><!-- end .posterInfoMegaContainer -->

</div><!-- end .post_images_container_bottom -->
</div><!-- end .postImagesContainer -->

<div class='postBottomContainer row'>

<ul id='postSettings` + random_num + `' class='dropdown-content'>
` + (data["posted_by_base_user"] == true ? `<li class='deletePost' data-actual-post-id='` + data["post_id"] + `'><a href='#!' class='waves-effect waves-lightgrey'>Delete</a></li>` : "<li class='reportPost' data-actual-post-id='" + data["post_id"] + "'><a href='#!' class='waves-effect waves-lightgrey'>Report</a></li>") + `
</ul>

<div class='post_text_container'>

<div class='postTitle scaleItem'>
` +   handle_tags(data["post_title"]) + `
</div><!-- end .postTitle -->

<div class='post_text_container_child'>
<a class='post_comments_number showPostComments modal-trigger opacityChangeOnActive' href='#commentsModal' data-actual-post-id='` + data["post_id"] + `'>View all <span class='comments_number_container'><span class='comments_number' data-total-number='0'></span></span> Comments...</a>
<div class='postDate'></div>
</div><!-- end .post_text_container_child -->

</div><!-- end .post_text_container -->

<a class='postSettingsButton dropdown-button opacityChangeOnActive' href='#' data-activates='postSettings` + random_num + `'><i class='material-icons'>more_vert</i></a>

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


function get_post_comments_number_string(number_of_comments) {
return ("View " + (number_of_comments != 1 ? "all" : "") + " <span class='comments_number_container'><span class='comments_number' data-total-number='" + number_of_comments + "'>" + (number_of_comments != 0 ? number_of_comments : "") + "</span></span> Comment" + (number_of_comments != 1 ? "s" : "") + "...");	
}

function set_post_comments_number_string(post_element, number_of_comments) {
post_element.find(".post_comments_number").html(get_post_comments_number_string(number_of_comments));
}





$(document).ready(function(){
	
	
MAIN_SCREEN_POSTS_CONTAINER = $("#allPostsContainer");
MAIN_SCREEN_POSTS_SCROLLER = $("#main_screen_main_posts_container");
	
	
	
// add the disappear-when-scrolling-down-appear-when-scrolling-up effect:

register_to_do_things_on_scroll(MAIN_SCREEN_POSTS_SCROLLER, 1000, 60, 60, function(){
$("#main_posts_container_back_to_top").addClass("scale-out");	
}, function(){
$("#main_posts_container_back_to_top").removeClass("scale-out");
}, function(){
$("#main_posts_container_back_to_top").addClass("scale-out");
});

register_to_do_things_on_scroll($("#tagPostsContainer"), 1000, 60, 60, function(){
$("#tag_posts_modal_back_to_top").addClass("scale-out");	
}, function(){
$("#tag_posts_modal_back_to_top").removeClass("scale-out");
}, function(){
$("#tag_posts_modal_back_to_top").addClass("scale-out");
});

register_to_do_things_on_scroll($("#userPostsContainer"), 1000, 60, 60, function(){
$("#user_posts_modal_back_to_top").addClass("scale-out");	
}, function(){
$("#user_posts_modal_back_to_top").removeClass("scale-out");
}, function(){
$("#user_posts_modal_back_to_top").addClass("scale-out");
});

register_to_do_things_on_scroll($("#favorite_posts_container"), 1000, 60, 60, function(){
$("#favorite_posts_modal_back_to_top").addClass("scale-out");	
}, function(){
$("#favorite_posts_modal_back_to_top").removeClass("scale-out");
}, function(){
$("#favorite_posts_modal_back_to_top").addClass("scale-out");
});



// the back-to-top buttons:
$(document).on("click", "[data-back-to-top-target]", function(){
$($(this).attr("data-back-to-top-target")).animate({"scrollTop": 0}, 400);	
});




	
	
/* ----- getting posts ----- */

$(document).on("click",".getPosts",function(){
emptyAllPostsContainer();
showLoading(MAIN_SCREEN_POSTS_CONTAINER, "60%");
getPosts("components/get_posts.php",{"row_offset":0},function(data_arr){
markUpProcessor(data_arr,MAIN_SCREEN_POSTS_CONTAINER, "Your feed is so empty :(", function(){
MAIN_SCREEN_POSTS_CONTAINER.attr("data-contains-which-posts","posts");
MAIN_SCREEN_POSTS_CONTAINER.scrollTop(0);
removeLoading(MAIN_SCREEN_POSTS_CONTAINER);
});	
});		
});

// making a call to this function on page load since the user is presented with their feed as soon as they login.
getPosts("components/get_posts.php",{"row_offset":0},function(data_arr){
markUpProcessor(data_arr,MAIN_SCREEN_POSTS_CONTAINER, "Nothing here :(", function(){MAIN_SCREEN_POSTS_CONTAINER.attr("data-contains-which-posts","posts");MAIN_SCREEN_POSTS_CONTAINER.scrollTop(0);});	
});		


/* when a user wants to see the featured posts */
$(document).on("click",".getFeaturedPosts",function(){
emptyAllPostsContainer();
showLoading(MAIN_SCREEN_POSTS_CONTAINER, "60%");
getPosts("components/get_featured_posts.php",{"row_offset":0},function(data_arr){
markUpProcessor(data_arr,MAIN_SCREEN_POSTS_CONTAINER, "We don't know why there is nothing here either :(", function(){
MAIN_SCREEN_POSTS_CONTAINER.attr("data-contains-which-posts","featuredPosts");
MAIN_SCREEN_POSTS_CONTAINER.scrollTop(0);
removeLoading(MAIN_SCREEN_POSTS_CONTAINER);
});	
});		
});

/* when a user wants to see their favorited posts */
$(document).on("click",".getMyFavoritePosts",function(){	
emptyAllPostsContainer();
showLoading(MAIN_SCREEN_POSTS_CONTAINER, "60%");
getPosts("components/get_my_favorite_posts.php",{"row_offset":0},function(data_arr){
markUpProcessor(data_arr,MAIN_SCREEN_POSTS_CONTAINER, "After you favorite some posts, you should look for them here :)" , function(){
MAIN_SCREEN_POSTS_CONTAINER.attr("data-contains-which-posts","favoritePosts");
MAIN_SCREEN_POSTS_CONTAINER.scrollTop(0);
removeLoading(MAIN_SCREEN_POSTS_CONTAINER);
});	
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
showLoading($("#singlePostsContainer"), "50%");
getPosts("components/get_single_post.php",{"post_id":$(this).attr("data-actual-post-id")},function(data_arr){
markUpProcessor(data_arr,$("#singlePostsContainer"), "We don't know why the post didn't appear either :(", function(){
removeLoading($("#singlePostsContainer"));	
});	
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

showLoading($("#userPostsContainer"), "50%");

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
markUpProcessor(data_arr[0], $("#userPostsContainer"), "This user does not have a single post, such a loser.", function(){
removeLoading($("#userPostsContainer"));	
});		
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







// when a user wants to view another's posts via clicking the "posts" button on their user modal 
$(document).on("click",".get_favorite_posts",function(){

if(typeof $(this).attr("data-user-id") == "undefined") {
return false;	
}


//empty the #favorite_posts_container of the last query's posts
$("#favorite_posts_container").html("");

showLoading($("#favorite_posts_container"), "50%");

$(favorite_posts_container).attr("data-user-id",$(this).attr("data-user-id"));
$("#favorite_posts_modal .navRightItemsMobile .follow_user").attr("data-user-id", $(this).attr("data-user-id"));

/* if base user is looking at their own favs, hide the "follow" button (because not hiding it would not make any sense), if they are looking 
at someone else's posts, show it. */
if($(this).attr("data-user-id") == BASE_USER_ID_HOLDER.attr("data-user-id")) {
$("#favorite_posts_modal .navRightItemsMobile .follow_user").hide();
}
else {
$("#favorite_posts_modal .navRightItemsMobile .follow_user").show();	
}

getPosts("components/get_favorite_posts.php",{"user_id": $(this).attr("data-user-id"), "row_offset": 0},function(data_arr){
markUpProcessor(data_arr[0], $("#favorite_posts_container"), "This user has not faved a single post, such a loser.", function(){
removeLoading($("#favorite_posts_container"));	
});		
$("#favorite_posts_modal .navRightItemsMobile .follow_user").html((data_arr[1] == 0 ? "Follow +" : "Unfollow"));
});
});
// user is infinite scrolling the user posts modal
$("#favorite_posts_container").scroll(function(){
if($(this).scrollTop() > ($(this)[0].scrollHeight - 650) && $(this).find(".singlePost").length > 0) {
getPosts("components/get_favorite_posts.php",{"user_id": $(this).attr("data-user-id"), "row_offset": $(this).find(".singlePost").length},function(data_arr){
markUpProcessor(data_arr[0], $("#favorite_posts_container"), "This user has not faved a single post, such a loser.");		
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

var tag = (typeof $(this).attr("data-tag") != "undefined" ? $(this).attr("data-tag") : $("#tagPostsModal").attr("data-tag")).toLowerCase();

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

showLoading($("#tagPostsContainer"), "55%");

// if the user is switching between the tabs
if($(this).parents(".tabs").length > 0) {
getPosts("components/get_tag_posts.php",{"row_offset":0,"tag": tag,"sort_posts_by":$("#tagPostsModal").attr("data-hot-or-new")},function(data_arr){	
markUpProcessor(data_arr[0],$("#tagPostsContainer"), "Nothing here :(", function(){
removeLoading($("#tagPostsContainer"));	
});	
});
}
// user is not switching between the tabs
else {
$("#tagPostsModal").attr("data-tag", tag);
$("#tagPostsModal .modal-header .modalHeaderFullName").html(tag);	
$("#tagPostsModal .navRightItemsMobile").find(".addTagFromTagPostsModal").attr("data-tag", tag);

getPosts("components/get_tag_posts.php",{"row_offset":0,"tag": tag,"sort_posts_by":$("#tagPostsModal").attr("data-hot-or-new")},function(data_arr){	
markUpProcessor(data_arr[0],$("#tagPostsContainer"), "Nothing here :(", function(){
removeLoading($("#tagPostsContainer"));	
});	
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
