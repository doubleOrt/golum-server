$(document).ready(function() {


/* make the search button a toggle button for the #searchForUserRow */
$("#searchForUserCollapserButton").click(function(){
$("#searchForUserRow").toggle();

// if we are trying to show the search input, then focus on it and set the icon of the button used to show this input a "close" icon.
if($("#searchForUserRow").css("display") != "none") { 
$("#searchForUser").focus();
$("#searchForUserCollapserButton").html("<i class='material-icons'>close</i>");
}
//when ever you hide the search textbox, set the textbox's value to empty, and empty the innerHTML of the #resultsColumn , and reset the #searchForUserCollapserButton's icon to the "search" icon.
else {
$("#searchResultsContainer").hide();
$("#searchForUser").val("");
$("#resultsColumn").html("");
$("#searchForUserCollapserButton").html("<i class='material-icons'>search</i>");
}
});


// when users search for other users
$("#searchForUser").keyup(function(){

// if empty, toggle #searchResultsContainer
if($(this).val() == "") {
$("#searchResultsContainer").hide();		
return false;
}

$("#searchResultsContainer").css("display","block"); 

$.get({
url: 'components/search.php',
data: {search_value:$("#searchForUser").val()},
type: "get",
success: function(data){

var dataArr = JSON.parse(data);

$("#searchResultsContainer").css("height","calc(100% - 103px)");
$("#resultsColumn").css("height","100%");
$("#resultsColumn").html(dataArr[0]);


}
}); 

});



});