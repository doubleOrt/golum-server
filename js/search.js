$(document).ready(function() {

// when users search for other users
$("#searchForUser").keyup(function(){

// if empty, toggle #searchResultsContainer
if($(this).val() == "") {
$("#resultsColumn").html("<div class='emptyNowPlaceholder'><i class='material-icons'>search</i><br>Please type in something in the search box :)</div>")	
return false;
}

$.get({
url: 'components/search.php',
data: {
search_value:$("#searchForUser").val()
},
type: "get",
success: function(data){

var data_arr = JSON.parse(data);

}
}); 

});



});