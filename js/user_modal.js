
// the imagepath referred to by this variable will be used for the background image of users who have not uploaded a background image of their own yet.
var DEFAULT_USER_PROFILE_BACKGROUND_IMAGE = "icons/default_user_profile_background_image.png";

// this var will block any calls to the "userModalGet.php" file as soon as a call is made, and will re-allow these calls after the call succeeds.
var userModalShouldServerSide = true;	

// first parameter is the id of the user, second parameter is the element that the markup for that user should be added to, and the last function is a callback that must have 1 parameter (because we pass to it an array of info related to the requested user).
function getUser(userId, callback) {
	
// set this to false to prevent weird things from occuring when the user activates edit mode on his profile, and then goes to another user's profile without deactivating their edit mode.
editModeActive = false;

if(userModalShouldServerSide == true) {

userModalShouldServerSide = false;

$.get({
url:"components/userModalGet.php",
data:{"user_id":userId},
success:function(data,status){					  

var dataArr = JSON.parse(data);

// gives us an object called "info" that is populated with info about this user.
eval(dataArr[0]);	


// if the user has not uploaded a background image, set the "background" field to the default background image.
(info["background"] == "" ? info["background"] = DEFAULT_USER_PROFILE_BACKGROUND_IMAGE : "");


if(callback.length > 0) {
// grab from the dataArr the info variable that contains this user's info.
callback(info);	
}


userModalShouldServerSide = true;
}
});


}
	
}



function getUserModalTags(userId) {

$.get({
data:{"user_id":userId},
url:"components/get_user_modal_tags.php",
success:function(data){
$(".tagsContainer").html(JSON.parse(data)[0]);	
}
});

}
	
	

	
// these variables are necessary for our user modal infos changing. we make a call to the "user_modal_variables.php" page on document.ready to set their values.
var changeInfosGetObjDefaults;
var changeInfosGetObj;

$(document).ready(function(){	
	

		  
//push every avatar image that shows the base user's avatar into this array, we will later update its source when a user changes his profile picture until the page is refreshed.
var avatarImagesArray = [{ref:"#userAvatar",type:"img"},{ref:"#userAvatarImage",type:"img"}];

var userInfosChangedOr = false;

/* takes care of the avatar rotating */				
$(document).on("click","#rotateAvatarButton",function(){
var currentRotationDegree = getRotationDegrees($("#userAvatarImage").parent());
var newDegree = currentRotationDegree != 270 ? currentRotationDegree + 90 : 0;

$(".baseUserAvatarRotateDivs").each(function(){
$(this).attr("data-rotate-degree",newDegree);	
$(this).css("transform","rotate(" + newDegree + "deg)");
adaptRotateWithMargin($(this).find("img"),newDegree,true);	
});
changeInfosGetObj[$(this).attr("data-change-name")] = getRotationDegrees($("#userAvatarImage").parent());
});				



/* takes care of the avatar repositioning */ 
$(document).on("click",".repositionAvatar",function(){
/* underneath, there are some reposition adapting methods or algorithms, yes, since these are called stuff programmers don't want to explain. but they 
mostly prevent elements from going out of the avatar circle while repositioning. */
changeInfosGetObj["avatar_positions"] = [0,0];
// all this variable and conditionals do is determine the direction the user wants to reposition the avatar to
var repositionDirection = $(this).attr("data-direction");
var userAvatarCoordinates = $("#userAvatarImage")[0].getBoundingClientRect();
if(repositionDirection == "up") {
if(parseFloat($("#userAvatarImage").parent().parent().css("margin-top").replace("px","")) > (document.getElementById("userAvatarImage").getBoundingClientRect().height - 120) * -1) {
$("#userAvatarImage").parent().parent().animate({"margin-top":"-=10px"},100);
}
else {
$("#userAvatarImage").parent().parent().animate({"margin-top":"-=" + document.getElementById("userAvatarImage").getBoundingClientRect().width - 110 + "px"},100);
}
changeInfosGetObj["avatar_positions"][0] = Math.round((parseFloat($("#userAvatarImage").parent().parent().css("margin-top").replace("px",""))/110)*100);
}
if(repositionDirection == "down") {
if(parseFloat($("#userAvatarImage").parent().parent().css("margin-top").replace("px","")) < -10) {
$("#userAvatarImage").parent().parent().animate({"margin-top":"+=10px"},100);
}
else {
$("#userAvatarImage").parent().parent().animate({"margin-top":"0px"},100);	
}
changeInfosGetObj["avatar_positions"][0] = Math.round((parseFloat($("#userAvatarImage").parent().parent().css("margin-top").replace("px",""))/110)*100);
}
if(repositionDirection == "left") {
if(parseFloat($("#userAvatarImage").parent().parent().css("margin-left").replace("px","")) > (document.getElementById("userAvatarImage").getBoundingClientRect().width - 120) * -1) {
$("#userAvatarImage").parent().parent().animate({"margin-left":"-=10px"},100);
}
else {
$("#userAvatarImage").parent().parent().animate({"margin-left":"-=" + document.getElementById("userAvatarImage").getBoundingClientRect().width - 110 + "px"},100);	
}
changeInfosGetObj["avatar_positions"][1] = Math.round((parseFloat($("#userAvatarImage").parent().parent().css("margin-left").replace("px",""))/110)*100);
}
if(repositionDirection == "right") {
if(parseFloat($("#userAvatarImage").parent().parent().css("margin-left").replace("px","")) < -10) {
$("#userAvatarImage").parent().parent().animate({"margin-left":"+=10px"},100);		
}			
else {
$("#userAvatarImage").parent().parent().animate({"margin-left":"0px"},100);					
}
changeInfosGetObj["avatar_positions"][1] = Math.round((parseFloat($("#userAvatarImage").parent().parent().css("margin-left").replace("px",""))/110)*100);
}
});





/* takes care of adding userModalChangeAbles to the changeInfosGetObj object */		
$(document).on("change",".userModalChangeAbles",function(event){
event.stopPropagation();
changeInfosGetObj[$(this).attr("data-change-name")] = $(this).val();
});					


/* save changes */
$(document).on("click","#editProfileButton",function(){

if(editModeActive == true) {

//this loop takes care of emptying variables that were not changed.
for(var prop in changeInfosGetObj) {
//if it's an array, we want to do a different check than the one we do if it's a simple variable.
if(changeInfosGetObj[prop].constructor === Array) {
if(changeInfosGetObj[prop][0] == changeInfosGetObjDefaults[prop][0] && changeInfosGetObj[prop][1] == changeInfosGetObjDefaults[prop][1]) {
changeInfosGetObj[prop] = "";
}
else {
userInfosChangedOr = true;	
}
}
else {
if(changeInfosGetObj[prop] == changeInfosGetObjDefaults[prop]) {
changeInfosGetObj[prop] = "";	
}
else if(changeInfosGetObj[prop] !== ""){
userInfosChangedOr = true;	
}
}
}

/* if any infos were changed */
if(userInfosChangedOr == true) {
$.get({
url:"components/change_infos.php",
data:changeInfosGetObj,
success:function(data,status){
eval(data);				
}
});
}

}
});



var editModeActive = false;

/* takes care of enabling and disabling edit mode. (profile editing) */
$(document).on("click","#editProfileButton",function(){
$("#userModalInfoSee").slideToggle();
$("#userModalInfoChangers").slideToggle();
$("#rotateAvatarButton").fadeToggle();
$("#repositionAvatarDiv").fadeToggle();
if(editModeActive == false) {
$("#editProfileButton i").html("done");
$("#userAvatarImage").css("opacity",".8");
$(".tagsContainer").hide();
editModeActive = true;
}
else {
$("#editProfileButton i").html("mode_edit");
$("#userAvatarImage").css("opacity","1");
$(".tagsContainer").show();
editModeActive = false;
}
});




// start related to changing the avatar picture ........................							


//click the file input whenever the  div is clicked.
$(document).on("click",".changeAvatarContainer",function(e){
$("#changeAvatarInput").click();
});

//if you don't call this then the above snippet will cause an error "maximum call stack size exceeded" because clicking this element will cause the parent to be clicked and then the parent's click causes the child's,etc...
$(document).on("click","#changeAvatarInput",function(e){
e.stopPropagation();
});



$(document).on("mouseover",".userModalAvatarImage",function(){
// if user is not in edit mode, then on hovering the avatar we show him the change avatar div.
if(editModeActive == false) {
$(".changeAvatarContainer").show();
}
});


// whenever the user mouseouts the user avatar pic, we hide the change profile div.						
$(document).on("mouseout",".userModalAvatarImage",function(){
$(".changeAvatarContainer").hide();
});

// end related to avatar picture ..........................






//whenever a user selects an image to set it as their new avatar, do an ajax query to upload it.  
$(document).on("change","#changeAvatarInput",function(){
var avatar_filetype = $("#changeAvatarInput")[0].files[0]["type"];
var avatar_size = $("#changeAvatarInput")[0].files[0]["size"];
if(avatar_filetype == "image/jpeg" || avatar_filetype == "image/jpg" || avatar_filetype == "image/png" || avatar_filetype == "image/gif") {
//check if file is smaller than 5mb
if(avatar_size < 5000000) {					
if(avatar_size > 1) {
var data = new FormData();
data.append('new_avatar', $("#changeAvatarInput")[0].files[0]);
$.post({
url: 'components/change_avatar.php',
data: data,
cache: false,
contentType: false,
processData: false,
success: function(data){
eval(data);
}
}); 
/* without this, everytime the cancel button is pressed instead of the open button after each avatar picture change, it will throw an error 
"cannot read property type of undefined", another solution to this bug would be to check if the file[0] is undefined at the beginning of this function. */
$("#changeAvatarInput").val("");
}
else {
Materialize.toast("Sorry, There Is Something Wrong With Your Picture",4000,"red");
}
}
//if file is larger than 5mb
else {
Materialize.toast("Image Size Must Be Smaller Than 5MB",6000,"red");
}
}
// if file is not jpeg, jpg, png or gif
else {
Materialize.toast("Image Type Must Be Either \"JPEG\", \"JPG\", \"PNG\" Or \"GIF\" !",6000,"red");
}
});











$(document).on("click","#changeBackgroundButton",function() {
$("#newBackgroundInput").click();
});

$(document).on("change","#newBackgroundInput",function(){
setNewBackground($(this));
});


function setNewBackground(inputElement) {

var background_filetype = inputElement[0].files[0]["type"];
var background_size = inputElement[0].files[0]["size"];

if(background_filetype == "image/jpeg" || background_filetype == "image/jpg" || background_filetype == "image/png" || background_filetype == "image/gif") {
//check if file is smaller than 5mb
if(background_size < 5000000) {					
if(background_size > 1) {
var data = new FormData();
data.append('new_background', inputElement[0].files[0]);
$.post({
url: 'components/change_background.php',
data: data,
cache: false,
contentType: false,
processData: false,
success: function(data){
eval(data);	
}
}); 
/* without this, everytime the cancel button is pressed instead of the open button after each avatar picture change, it will throw an error 
"cannot read property type of undefined", another solution to this bug would be to check if the file[0] is undefined at the beginning of this function. */
inputElement.val("");
}
else {
Materialize.toast("Sorry, There Is Something Wrong With Your Picture",4000,"red");
}
}
//if file is larger than 5mb
else {
Materialize.toast("Image Size Must Be Smaller Than 5MB",6000,"red");
}
}
// if file is not jpeg, jpg, png or gif
else {
Materialize.toast("Image Type Must Be Either \"JPEG\", \"JPG\", \"PNG\" Or \"GIF\" !",6000,"red");
}
	
}


});
