
/* this function makes sure that an image always fits its parent div (just like background-size:cover, except we couldn't add the background-size
cover here because its an img). Make sure that the image is FULLY LOADED before you call this function */
function fitToParent(ref) {

//if element is undefined, return false.
if($(ref).length == 0) {
return false;	
}

if(ref == "#userAvatarImage") {
console.log(ref + "\n" + $(ref).prop("naturalWidth") + "\n" + $(ref).prop("naturalHeight"));
}

$(ref).css({"width":"auto","height":"auto"});
if($(ref).prop("naturalWidth") >= $(ref).prop("naturalHeight")) {
$(ref).css({"min-height":"100%","max-height":"100%","min-width":"100%","max-width":"none"});
}
else {
$(ref).css({"min-width":"100%","max-width":"100%","min-height":"100%","max-height":"none"});
}
}



/* adds some css so that you can use margin-top and margin-left on rotated elements correctly, without this everything would be reversed, for example margin-left
would become margin-bottom, and so on, depending on the rotate angle */
function adaptRotateWithMargin(elem,rotationDegree,changeRotate) {

if(rotationDegree == 90) {
elem.parent().css({"left":"100%","top":"0%"});elem.css({"top":"auto","bottom":"0","left":"0"});
}
else if(rotationDegree == 180) {
elem.parent().css({"left":"100%","top":"100%"});elem.css({"top":"auto","bottom":"0","right":"0","left":"auto"});
}
else if(rotationDegree == 270) {
elem.parent().css({"left":"0%","top":"100%"});elem.css({"top":"0","bottom":"auro","right":"0","left":"auto"});
}
else {
elem.parent().css({"left":"0%","top":"0%"});elem.css({"top":"0","left":"0"});
}

if(changeRotate == true) {
$(elem).parent().parent().css({"margin-left":"0","margin-top":"0"});	
}

}


/* this function we will use to get the rotate css value */
function getRotationDegrees(obj) {
var matrix = obj.css("-webkit-transform") ||
obj.css("-moz-transform")    ||
obj.css("-ms-transform")     ||
obj.css("-o-transform")      ||
obj.css("transform");
if(matrix !== 'none') {
var values = matrix.split('(')[1].split(')')[0].split(',');
var a = values[0];
var b = values[1];
var angle = Math.round(Math.atan2(b, a) * (180/Math.PI));
} else { var angle = 0; }
return (angle < 0) ? angle + 360 : angle;
}






// for giving a preview of an image before it is uploaded
function imagePreview(input,elem,background_or_src) {

var reader = new FileReader();

reader.onload = function (e) {
if(background_or_src == "background") {
elem.css('background', "url(" + e.target.result + ")");
elem.css('background-size', "cover");
elem.css('background-position', "center center");
}
if(background_or_src == "src") {
elem.attr('src', e.target.result);
fitToParent("#shareNewImagesContainer .col:nth-child(" + (elem.parent().index() + 1) + ") img");
elem.css({"top":"50%","left":"50%","transform":"translate(-50%,-50%);"});
}
}

reader.readAsDataURL(input);
}


// takes an element that has a numeric attribute (which has to be passed to this function as well), and returns that attribute's value incremented or decremented by 1.
function setNewNumber(elem, totalNumberAttrName, isIncrement, addParenthesis, extraString) {
var currentNum = elem.attr(totalNumberAttrName);	
var newNum = parseFloat(currentNum) + (isIncrement == true ? 1 : -1);	
if(addParenthesis == true) {
elem.html("(" + newNum + extraString + ")");
}
else {
elem.html(newNum + extraString);	
}	
elem.attr(totalNumberAttrName,newNum);
return newNum;	
}




// just a function useful for taking the pointer to the end of content-editables when focusing them in, because the default is the beginning. (usually you will need function when you programatically modify the innerHTML of one of these content-editable divs)
function movePointerToEnd(el) {
el.focus();
if (typeof window.getSelection != "undefined"
&& typeof document.createRange != "undefined") {
var range = document.createRange();
range.selectNodeContents(el);
range.collapse(false);
var sel = window.getSelection();
sel.removeAllRanges();
sel.addRange(range);
} else if (typeof document.body.createTextRange != "undefined") {
var textRange = document.body.createTextRange();
textRange.moveToElementText(el);
textRange.collapse(false);
textRange.select();
}
}


/* takes an image as a parameter and its size limit as a parameter, checks if it is either "JPEG", "JPG", "PNG" or "GIF", 
along with some other checks and if the image doesn't pass one of these checks, it will return the corresponding message. */
function checkImageFile(inputElement,sizeLimit) {

if(typeof inputElement[0].files[0] != "undefined") {

if(inputElement[0].files[0]["type"] != "image/jpeg" && inputElement[0].files[0]["type"] != "image/jpg" && inputElement[0].files[0]["type"] != "image/png" && inputElement[0].files[0]["type"] != "image/gif") {
return "Image Type Must Be Either \"JPEG\", \"JPG\", \"PNG\" Or \"GIF\" !";
}
if(inputElement[0].files[0]["size"] >= sizeLimit) {	
return "Image Size Must Be Smaller Than " + (sizeLimit / 1000000) + "MB";
}
if(inputElement[0].files[0]["size"] < 1) {	
return "Sorry, There Is Something Wrong With Your Picture";
}

}
// if image is undefined
else {
return "Sorry, There Is Something Wrong With Your Picture";	
}

return true;
}




// enhanced intervals we use for getting comment dates every 2 mins for example.	
function EnhancedInterval(intervalVar,intervalTime,callback) {
clearInterval(intervalVar);	
intervalVar = setInterval(callback,intervalTime);	
this.intervalFunction = callback;
}	


// takes a value and an array, returns true if the value is in the array, and false if it is not.
function checkIfValueIsInArray(val,arr) {
for(var i = 0;i<arr.length;i++) {
if(arr[i] == val) {
return true;	
}	
}
return false;	
}


// shows the loading spinner
function showLoading() {
$("#preloader").show();	
}
// hides the loading spinner.
function hideLoading() {
$("#preloader").hide();	
}


/* we make a call to this function whenever the user wants to see different types of posts (favorites, my posts, posts by tag, etc...), you are supposed to make a call 
to this function directly when the invoker element is clicked. and please don't confuse this with deleting posts, this one is merely a client side function that removes html elements */
function emptyAllPostsContainer() {
$("#allPostsContainer").html("");
}




function logOut() {

// when user presses the logout button 
$.get({
url:"components/logout.php",
success:function(data) {
window.location.href = "login_and_sign_up.html";
}	
});
	
}

