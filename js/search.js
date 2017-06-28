/* this is the element that you want to be used to contain the search results, this one is also the one that should be scrolled, 
since we are capturing infinite scrolling on this element.	
the element won't exist until the document is ready, so we set this variable to the actual contianer of the elements then.	*/
var SEARCH_USERS_RESULTS_CONTAINER;
var SEARCH_TAGS_RESULTS_CONTAINER;
var SEARCH_BOX;
var SEARCH_TABS_STATE_HOLDER;


var prevent_multiple_calls_to_search_for_user = false;	
var prevent_multiple_calls_to_search_for_tag = false;	


function search_section_tabs_changed() {

var active_tab = SEARCH_TABS_STATE_HOLDER.attr("data-active-tab");

// user switched to the PEOPLE tab
if(active_tab == "0") {
SEARCH_TAGS_RESULTS_CONTAINER.hide();
SEARCH_USERS_RESULTS_CONTAINER.show();
}
// user switched to the TAGS tab
else if(active_tab == "1"){
SEARCH_USERS_RESULTS_CONTAINER.hide();	
SEARCH_TAGS_RESULTS_CONTAINER.show();
}

// if the search box is currently empty, we just have to toggle the tab divs.
if(SEARCH_BOX.val().trim() != "") {

if(active_tab == "0") {
// empty the SEARCH_USERS_RESULTS_CONTAINER of the last search's markup
SEARCH_USERS_RESULTS_CONTAINER.html("");
search_for_user(SEARCH_BOX.val() , 0 , search_for_user_callback);
}
else if(active_tab == "1") {
// empty the SEARCH_TAGS_RESULTS_CONTAINER of the last search's markup	
SEARCH_TAGS_RESULTS_CONTAINER.html("");
search_for_tag(SEARCH_BOX.val() , 0 , search_for_tag_callback);	
}

}

}






$(document).ready(function() {
	
SEARCH_USERS_RESULTS_CONTAINER = $("#search_users_results_column");	
SEARCH_TAGS_RESULTS_CONTAINER = $("#search_tags_results_column");	
SEARCH_BOX = $("#searchForUser");
SEARCH_TABS_STATE_HOLDER = $("#search_container");	
	
	
$(document).on("click", "#search_tabs .tab", function() {
SEARCH_TABS_STATE_HOLDER.attr("data-active-tab", $(this).attr("data-tab-index"));	
search_section_tabs_changed();
});
	
	
	
// the search box used by users to search for other users or tags
SEARCH_BOX.keyup(function(e){

// if empty, toggle #searchResultsContainer
if($(this).val().trim() == "") {
SEARCH_USERS_RESULTS_CONTAINER.html("<div class='emptyNowPlaceholder'><i class='material-icons'>search</i><br>Please type in something in the search box :)</div>")	
SEARCH_TAGS_RESULTS_CONTAINER.html("<div class='emptyNowPlaceholder'><i class='material-icons'>search</i><br>Please type in something in the search box :)</div>")	
return false;
}	

var active_tab = SEARCH_TABS_STATE_HOLDER.attr("data-active-tab");
if(active_tab == "0") {	
// empty the SEARCH_USERS_RESULTS_CONTAINER of the last search's markup
SEARCH_USERS_RESULTS_CONTAINER.html("");
search_for_user($(this).val() , 0 , search_for_user_callback);
}
else if(active_tab == "1") {
// empty the SEARCH_TAGS_RESULTS_CONTAINER of the last search's markup	
SEARCH_TAGS_RESULTS_CONTAINER.html("");
search_for_tag($(this).val() , 0 , search_for_tag_callback);
}

});
// infinite scrolling
SEARCH_USERS_RESULTS_CONTAINER.scroll(function(){
if($(this).scrollTop() > ($(this)[0].scrollHeight - 650) && $(this).find(".searchResultRow").length > 0) {	
search_for_user( SEARCH_BOX.val() , $(this).find(".searchResultRow").length , search_for_user_callback);
}
});
// infinite scrolling
SEARCH_TAGS_RESULTS_CONTAINER.scroll(function(){
if($(this).scrollTop() > ($(this)[0].scrollHeight - 650) && $(this).find(".searchResultRow").length > 0) {	
search_for_tag( SEARCH_BOX.val() , $(this).find(".searchResultRow").length , search_for_tag_callback);
}
});


});










function search_for_user(search_term , offset , callback) {
	
if(prevent_multiple_calls_to_search_for_user != false) {	
return false;
}


prevent_multiple_calls_to_search_for_user = true;

$.get({
url: 'components/search_users.php',
data: {
"search_value": search_term,
"row_offset": offset
},
success: function(data){

var data_arr = JSON.parse(data);

if(typeof callback == "function") {
callback(data_arr);
}

prevent_multiple_calls_to_search_for_user = false;

}
}); 
	
}

function search_for_user_callback(data) {

// if the user is not infinite scrolling and there have been no results, add a placeholder div to tell the user there have been no results.
if( data.length < 1 && SEARCH_USERS_RESULTS_CONTAINER.find(".searchResultRow").length < 1) {
SEARCH_USERS_RESULTS_CONTAINER.html("<div class='emptyNowPlaceholder'><i class='material-icons'>error</i><br>Sorry, there were no results for your search term :(</div>")	
return false;	
} 


for(var i = 0;i < data.length; i++) {
SEARCH_USERS_RESULTS_CONTAINER.append(
generate_search_result_user_row_markup( 
data[i]["id"], 
data[i]["first_name"], 
data[i]["last_name"],
data[i]["user_name"], 
{"avatar": data[i]["avatar"] , "avatar_positions": data[i]["avatar_positions"] , "avatar_rotate_degree": (data[i]["avatar_rotate_degree"] != "" ? data[i]["avatar_rotate_degree"] : 0 )}
)
);

}

}


function search_for_tag(search_term , offset , callback) {
	
if(prevent_multiple_calls_to_search_for_tag != false) {	
return false;
}

// remove a potential hashtag that a user would add since they assume that they should insert a hashtag before their search term since it's a tag search.
search_term[0] != "#" ? search_term : search_term.substring(1, search_term.length);

prevent_multiple_calls_to_search_for_tag = true;

$.get({
url: 'components/search_tags.php',
data: {
"search_value": search_term,
"row_offset": offset
},
success: function(data){
console.log(data);
var data_arr = JSON.parse(data);

if(typeof callback == "function") {
callback(data_arr);
}

prevent_multiple_calls_to_search_for_tag = false;
}
}); 
	
}



function search_for_tag_callback(data) {

// if the user is not infinite scrolling and there have been no results, add a placeholder div to tell the user there have been no results.
if( data.length < 1 && SEARCH_TAGS_RESULTS_CONTAINER.find(".searchResultRow").length < 1) {
SEARCH_TAGS_RESULTS_CONTAINER.html("<div class='emptyNowPlaceholder'><i class='material-icons'>error</i><br>Sorry, there were no results for your search term :(</div>")	
return false;	
} 

for(var i = 0;i < data.length; i++) {
SEARCH_TAGS_RESULTS_CONTAINER.append(
generate_search_result_tag_row_markup( 
data[i]["tag"], 
data[i]["total_posts"], 
data[i]["total_followers"],
data[i]["sample_image_path"],
data[i]["current_state"]
)
);
}
	
}




// used to generate the markup for user rows when a user searches for other rows
// avatar_object must be in this format: {"avatar": "xyz" , "avatar_positions" : [x,y] , "avatar_rotate_degree" : x} 
function generate_search_result_user_row_markup(user_id, first_name, last_name, user_name,  avatar_object) {

var full_name = first_name + " " + last_name;

// need the avatar to have an id so that we can identify it successfully, fit it to its parent and then find its .searchResultRow parent so we can initialize the special waves on it.
var result_row_element_id = "searchResultRow" + user_id;

var user_row_markup = `

<div id='` + result_row_element_id + `' class='row searchResultRow showUserModal' data-user-id='` + user_id + `' data-open-main-screen='#main_screen_user_profile'>

<div class='col l1 m1 s2'>

<div class='searchResultAvatarContainer avatarContainer'>
<div class='avatarContainerChild'>
<div class='rotateContainer' style='position:relative;transform:none;display:inline-block;width:100%;height:100%;margin-top: ` + avatar_object["avatar_positions"][0] + `%;margin-left:` + avatar_object["avatar_positions"][1] + `%;'>
<div class='avatarRotateDiv' style='transform: rotate(` + avatar_object["avatar_rotate_degree"] + `deg);'>
<img class='searchResultAvatar avatarImages' src='` + (avatar_object["avatar"] != "" ? avatar_object["avatar"] : LetterAvatar(first_name , 60)) + `' alt='Avatar Picture'/>
</div><!-- end .avatarRotateDiv -->
</div><!-- end .rotateContainer -->
</div><!-- end avatarContainerChild -->
</div><!-- end .avatarContainer -->

</div>

<div class='col l10 m11 s10 searchResultInfosContainer'>

<div class='searchResultNamesContainer'>
<div class='searchResultFullName flow-text'>` + full_name + `</div>
<div class='searchResultUserName flow-text'>@` + user_name + `</div>
</div>

</div><!-- end .searchResultInfosContainer -->

<script>

	$("#` + result_row_element_id + `").find('.searchResultAvatar').on('load',function(){
		fitToParent($(this));
		adaptRotateWithMargin($(this), ` + avatar_object["avatar_rotate_degree"] + `,false);
	});
	
	Waves.attach( "#` + result_row_element_id + `" , ['waves-block']);
	Waves.init();

</script>
</div><!-- end .searchResultRow -->`;
	
return user_row_markup;
	
}





function generate_search_result_tag_row_markup(tag, total_posts, total_followers, sample_image_path, current_state) {

return `<div class='row tag_search_result_row searchResultRow getTagPosts modal-trigger' data-tag='` + tag + `' data-target='tagPostsModal'>

<div class='col l1 m1 s3 tag_sample_image_container'>


<div class='tag_sample_image' style='background:url(\"` + sample_image_path + `\"); background-size:cover; background-position:center;'>
</div>

</div>

<div class='col l9 m9 s5 searchResultInfosContainer'>
<div class='searchResultNamesContainer'>
<div class='searchResultFullName flow-text'>` + tag + `</div>
<div class='searchResultUserName flow-text'>` + total_followers + (total_followers != 1 ? " Followers" : " Follower") + `</div>
<div class='searchResultUserName flow-text'>` + total_posts + (total_posts != 1 ? " Posts" : " Post") + `</div>
</div>
</div><!-- end .searchResultInfosContainer -->

<div class='col l2 m2 s4 tag_row_button_container skewScaleItem'>
<a href='#' class='tag_follow_button myBackground opacityChangeOnActive addTagFromTagPostsModal stopPropagationOnClick' data-tag='` + tag + `' data-current-state='` + current_state + `'>` + (current_state == 0 ? "Follow +" : "Unfollow") + `</a>
</div>

</div><!-- end .searchResultRow -->`;
}