var openedModals = [];

var marks_stack = [];

var z_index_stack;

var longpollingVar; 


function openModalCustom(modalId, callback) {

/* if modal is currently on top and it is open, then just return false. Useful in cases like 
when the user presses a tag in the tagPostsModal which refers to itself (for example i press the gollum tag when 
i am in the tagPostsModal modal for all posts with the gollum tag, without these conditional, the modal would be 
closed and then re-opened, which is inconsistent. */
if(openedModals.length > 0 && modalId == openedModals[openedModals.length - 1]) {
if(typeof callback == "function") {	
callback();	
}
return false;	
}

var is_not_already_opened = true;

var this_modal_modal_overlay = $(".modal-overlay[data-modal='" + modalId + "']");

for(var i = 0;i<openedModals.length;i++) {
if(openedModals[i] == modalId) {	
$("#" + modalId).attr("data-marked", $("#" + modalId).css("z-index"));
marks_stack.push({"modal_id": "#" + modalId, "z-index": $("#" + modalId).css("z-index")});
$("#" + modalId).hide();
$("#" + modalId).css("z-index", z_index_stack + 4);
$("#" + modalId).fadeIn(300);
var modal_overlay_new_zindex = z_index_stack + 3;
z_index_stack += 4;
is_not_already_opened = false;
break;
}
}


if(is_not_already_opened == true) {
$("#" + modalId).css("z-index", z_index_stack + 2);
var modal_overlay_new_zindex = z_index_stack + 1;
this_modal_modal_overlay.css("z-index", modal_overlay_new_zindex);
} else {
setTimeout(function(){
this_modal_modal_overlay.css("z-index", modal_overlay_new_zindex);
}, 300);	
}




openedModals.push(modalId);

if(typeof callback == "function") {	
callback();
}
		
}



function closeModal(modalId, callback) {

var this_modal_modal_overlay = $(".modal-overlay[data-modal=" + modalId + "]");

for(var i = 0;i<openedModals.length;i++) {
if( openedModals[i] == modalId) {
openedModals.splice(i,1);
break;
}	
}


// if only one element is remaining from the stack, then remove its data-marked attribute
for(var i = marks_stack.length - 1; i > -1; i--) {
if(marks_stack[i]["modal_id"] == ("#" + modalId)) {	
var zindex = marks_stack[i]["z-index"];
this_modal_modal_overlay.css("z-index", zindex-1);	
$("#" + modalId).animate({"top": "100%", "opacity": "0"}, 150, function(){
$("#" + modalId).css("opacity", "1");		
$("#" + modalId).css("z-index", zindex);		
$("#" + modalId).show();		
});		
marks_stack.splice(i,1);
if(typeof callback == "function") {	
callback();
}
return;
}	
}


$("#" + modalId).modal('close');

if(typeof callback == "function") {	
callback();
}
}


$(document).ready(function(){

// initialize the modals
$('.modal').modal({
inDuration: 300, // Transition in duration
outDuration: 150, // Transition out duration	
startingTop: "100%",
endingTop: "50%",	
ready:function(){
z_index_stack = parseFloat($(this).css("z-index"));
}
});

$(document).on("click",".modal-trigger",function(){
	
// we need to disable the button so the user cannot make multiple calls to the openModalCustom .	
$(this).css("pointer-events","none");
var thisModalTrigger = $(this);	
setTimeout(function(){thisModalTrigger.css("pointer-events","auto");},500);
	
// grab the modal's id
var modalId = ( typeof $(this).attr("data-target") != "undefined" ? $(this).attr("data-target") : $(this).attr("href").substr(1,this.length));

$(".modal-overlay").last().attr("data-modal", modalId);
$("#" + modalId).css("opacity", 1);

openModalCustom(modalId);
});


$(document).on("click",".modalCloseButton, .modal-overlay",function(){	
closeModal($(this).attr("data-modal"), function(){
/* See bugs.txt: bug 2 */	
if($(".modal.open").length < 1 && PROFILE_CONTAINER_ELEMENT.parents("#main_screen_user_profile").length < 1 && $("#bottomNav #bottom_nav_user_profile").hasClass("active")) {
$("#bottomNav #bottom_nav_user_profile").click();	
}
});
});


})