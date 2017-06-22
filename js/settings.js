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



var default_first_name;
var default_last_name;
var default_user_name;
var default_email_address;

var defaultCheckObject;

$.get({
url:"components/user_modal_variables.php",
success:function(data) {
eval(data);
$("#change_first_name").val(default_first_name);
$("#change_last_name").val(default_last_name);
$("#change_user_name").val(default_user_name);
$("#add_email").val(default_email_address);

//update the fields
Materialize.updateTextFields();

defaultCheckObject = {
"change_first_name":{
"value": default_first_name,
"regexHandler": check_change_first_name
},	
"change_last_name":{
"value": default_last_name,
"regexHandler": check_change_last_name
},	
"change_user_name":{
"value": default_user_name,
"regexHandler": check_change_user_name
},	
"change_password":{
"value": "",
"regexHandler": check_change_password
},	
"add_email":{
"value": default_email_address,
"regexHandler": check_add_email
}
};

}	
});	




var check_change_first_name = new ValidateItem(document.getElementById("change_first_name"),/^[a-zA-Z\s]{3,18}$/i,"First Name Must Only Contain Letters And Spaces And Must Be Longer Than 3 And Shorter Than 18 Characters");
var check_change_last_name = new ValidateItem(document.getElementById("change_last_name"),/^[a-zA-Z\s]{3,18}$/i,"Last Name Must Only Contain Letters And Spaces And Must Be Longer Than 3 And Shorter Than 18 Characters");
var check_change_user_name = new ValidateItem(document.getElementById("change_user_name"),/^([a-zA-Z]+[0-9 ]*){6,36}$/i,"Username Must Be A Combination Of Letters, Numbers And Spaces And Muse Be Between 6-36 Characters In Length");
var check_change_password = new ValidateItem(document.getElementById("change_password"),/^(?=.*[A-Za-z])(?=.*\d)(?=.*([$@$!%*#?& ]*))[A-Za-z\d($@$!%*#?& )*]{8,50}$/i,"Password Must Contain At Least 1 Digit And Must Be Between 8-50 Characters, Special Characters And Spaces Are Optional");
var check_add_email = new ValidateItem(document.getElementById("add_email"),/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/,"Your Email Address Is Invalid");
var check_current_password = new ValidateItem(document.getElementById("current_password"),/^(?=.*[A-Za-z])(?=.*\d)(?=.*([$@$!%*#?& ]*))[A-Za-z\d($@$!%*#?& )*]{8,50}$/i,"Wrong Password");


$("#change_first_name, #change_last_name, #change_user_name, #change_password, #add_email").on("change",function(){


for(var prop in defaultCheckObject) {
if($("#" + prop).val() != defaultCheckObject[prop]["value"]) {
if(defaultCheckObject[prop]["regexHandler"] != undefined) {	
if(defaultCheckObject[prop]["regexHandler"].validate() == false) {
break;
}
}
}
}


});


$("#deactivateButton").click(function(){
$("#deactivateOrDelete").val("deactivate");
});


$("#deleteButton").click(function(){
$("#deactivateOrDelete").val("delete");
});


$("#cancelChanges").click(function(){
$("#deactivateOrDelete").val("");
});


$(document).on("click","#saveChanges",function(){
if(check_current_password.validate() == true) {

$.get({
url: 'components/change_settings.php',
data: {
"current_password":$("#current_password").val(),
"change_first_name":$("#change_first_name").val(),
"change_last_name":$("#change_last_name").val(),
"change_user_name":$("#change_user_name").val(),
"change_password":$("#change_password").val(),
"add_email":$("#add_email").val(),
"deactivate_or_delete":$("#deactivateOrDelete").val()
},
type: "get",
success:function(data,status) {


var dataArr = JSON.parse(data);

eval(dataArr[0]);

defaultCheckObject['change_first_name'].value = $("#change_first_name").val();
defaultCheckObject['change_last_name'].value = $("#change_last_name").val();
defaultCheckObject['change_user_name'].value = $("#change_user_name").val();
if(defaultCheckObject["add_email"].value != $("#add_email").val() && $(".confirmEmailContainer").length == 0) {

// if user has requested us to link his account with an email address and we have sent him a confirmation code, show him the enter confirmation code form.
$.get({
url:"components/show_confirmation_code_form.php",
success:function(data) {	
$("#accountContainerRow").prepend(data);
}	
});	

}

defaultCheckObject['add_email'].value = $("#add_email").val();

$(".baseUserFullNameContainers").html(defaultCheckObject['change_first_name'].value + " " + defaultCheckObject['change_last_name'].value);
$(".baseUserUserNameContainers").html(defaultCheckObject['change_user_name'].value);
$("#current_password").val("");
$("#change_password").val("");

}
});
}

});



/* we want to prevent the default action for linkless links. */
$("a[href='#']").click(function(event){
event.preventDefault();
});




	
	
	
	
	
$(document).on("click","#confirmEmail",function(){

var confirmation_code = $("#confirmation_code").val().trim();

var regex = /^\d+$/i;

if(regex.test(confirmation_code) == false) {
Materialize.toast("Confirmation Code Must Contain Numbers Only",3000,"red");		
return;
}
else {
$.get({
url:"components/confirm_email.php",
data:{
"confirmation_code":confirmation_code
},
success:function(data) {
eval(data);	
}	
});
}
});
	

})




