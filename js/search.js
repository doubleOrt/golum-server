
/* this is the element that you want to be used to contain the search results, this one is also the one that should be scrolled, 
since we are capturing infinite scrolling on this element.	
the element won't exist until the document is ready, so we set this variable to the actual contianer of the elements then.	*/
var SEARCH_RESULTS_CONTAINER_ELEMENT;

$(document).ready(function() {
	
var prevent_multiple_calls_to_search_for_user = false;	
	

SEARCH_RESULTS_CONTAINER_ELEMENT = $("#resultsColumn");

// the search box used by users to search for other users or tags
$("#searchForUser").keyup(function(e){

// if empty, toggle #searchResultsContainer
if($(this).val().trim() == "") {
SEARCH_RESULTS_CONTAINER_ELEMENT.html("<div class='emptyNowPlaceholder'><i class='material-icons'>search</i><br>Please type in something in the search box :)</div>")	
return false;
}	
			
// empty the SEARCH_RESULTS_CONTAINER_ELEMENT of the last search's markup
SEARCH_RESULTS_CONTAINER_ELEMENT.html("");

if(prevent_multiple_calls_to_search_for_user == false) {			
prevent_multiple_calls_to_search_for_user = true;			
search_for_user($(this).val() , 0 , search_for_user_callback);
}
	
});


// infinite scrolling
SEARCH_RESULTS_CONTAINER_ELEMENT.scroll(function(){
if(prevent_multiple_calls_to_search_for_user == false) {	
if($(this).scrollTop() > ($(this)[0].scrollHeight - 650) && $(this).find(".searchResultRow").length > 0) {	
prevent_multiple_calls_to_search_for_user = true;
search_for_user( $("#searchForUser").val() , $(this).find(".searchResultRow").length , search_for_user_callback);
}
}
});




function search_for_user_callback(data) {

// if the user is not infinite scrolling and there have been no results, add a placeholder div to tell the user there have been no results.
if( parseFloat(data[1]) < 1 && SEARCH_RESULTS_CONTAINER_ELEMENT.find(".searchResultRow").length < 1) {
SEARCH_RESULTS_CONTAINER_ELEMENT.html("<div class='emptyNowPlaceholder'><i class='material-icons'>error</i><br>Sorry, there were no results for your search term :(</div>")	
return false;	
} 

for(var i = 0;i < data[0].length; i++) {

SEARCH_RESULTS_CONTAINER_ELEMENT.append(
generate_search_result_user_row_markup( 
data[0][i]["id"], 
data[0][i]["first_name"], 
data[0][i]["last_name"],
data[0][i]["user_name"], 
{"avatar": data[0][i]["avatar"] , "avatar_positions": data[0][i]["avatar_positions"] , "avatar_rotate_degree": (data[0][i]["avatar_rotate_degree"] != "" ? data[0][i]["avatar_rotate_degree"] : 0 )}
)
);

}


}


function search_for_user(search_term , row_offset , callback) {

$.get({
url: 'components/search.php',
data: {
"search_value": search_term,
"row_offset": row_offset
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






});