function ValidateItem(ref,regEx,onWrong) {
// a reference to the element (document.getElementById for example)	
this.ref = ref;
// a regex to test the above element's value against
this.regEx = regEx;
// the message that should be toasted to the page when the value does not match the regex
this.onWrong = onWrong;	

this.validate = function() {
if(this.regEx.test(this.ref.value) == false) {
this.ref.style.borderBottom = "1px solid red";		
Materialize.toast(this.onWrong, 5000,"red")
return false;	
}
/* this is necessary to override the red borders, e.g you have 2 form elements you click submit, they're both false, now they have red borders, you correct one, 
click on submit again, now without this the border would still be red, but with this the border's going to be green. */
else {
this.ref.style.borderBottom = "1px solid #42dc12";
return true;	
}		
}

}




$(document).ready(function(){
	
var check_new_password;	
		
var forgot_password_username;	
var reset_code;


$(document).on("click","#forgot_password_username_button",function(){

forgot_password_username = $("#forgot_password_username").val();

$(this).addClass("fixedButtonDisabled");
var thisButtonObject = $(this);
	
$.get({
url:"components/forgot_password.php",
data:{"user_name":$("#forgot_password_username").val()},
success:function(data) {
eval(data);	
thisButtonObject.removeClass("fixedButtonDisabled");
}
});
	
});

$(document).on("click","#password_reset_code_button",function(){

reset_code = $("#password_reset_code").val();

$(this).addClass("fixedButtonDisabled");
var thisButtonObject = $(this);

$.get({
url:"components/forgot_password.php",
data:{
"user_name":forgot_password_username,
"reset_code":$("#password_reset_code").val()
},
success:function(data) {
eval(data);	
check_new_password = new ValidateItem(document.getElementById("new_password_input"),/^(?=.*[A-Za-z])(?=.*\d)(?=.*([$@$!%*#?& ]*))[A-Za-z\d($@$!%*#?& )*]{8,50}$/i,"Password Must Contain At Least 1 Digit And Must Be Between 8-50 Characters, Special Characters And Spaces Are Optional");
thisButtonObject.removeClass("fixedButtonDisabled");
}
});

});
	
	
	
$(document).on("click","#new_password_button",function(){

if(check_new_password.validate() == false) {
return;	
}

$(this).addClass("fixedButtonDisabled");
var thisButtonObject = $(this);

$.get({
url:"components/forgot_password.php",
data:{
"user_name":forgot_password_username,
"reset_code":reset_code,
"new_password":$("#new_password_input").val()
},
success:function(data) {
eval(data);	
thisButtonObject.removeClass("fixedButtonDisabled");
}
});

});
	
	
	
});