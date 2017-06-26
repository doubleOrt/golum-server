


function logOut() {

// when user presses the logout button 
$.get({
url:"components/logout.php",
success:function(data) {
window.location.href = "login_and_sign_up.html";
}	
});
	
}

$(document).ready(function() {
	 
// call the logOut() function whenever the user clicks something with the .log_out class.
$(document).on("click",".log_out",function(){
logOut();
});

});