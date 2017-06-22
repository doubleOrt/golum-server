var currentZindexStack = 1000;

var openedModals = [];

/* we can't use the currentZindexStack for this variable's purpose because the above variable, we decrement every time a modal is closed, everything would be allright if we didn't 
decrement it and instead of the below var, used (currentZindexStack - 650), but since we do decrement it, we use the below variable that is not decremented on each modal close 
to track the number of modals the user opens, whenever a .modal-trigger is pressed, the below var is incremented, and we then use it to guess the correct id of the materialize 
modal overlays. */ 
var modalsOpened = 0;

var longpollingVar; 

function openModalCustom(modalId,currentZindexStack1,modalsOpened1) {

$("#" + modalId).modal('open');

for(var i = 0;i<openedModals.length;i++) {
if(openedModals[i] == modalId) {
$("#" + modalId).hide();
$("#" + modalId).fadeIn();	
}
}


$("#" + modalId).css("z-index",currentZindexStack1);
$("#materialize-modal-overlay-" + modalsOpened1).css("z-index",currentZindexStack1 - 1);
$("#materialize-modal-overlay-" + modalsOpened1).attr("data-modal",modalId);

setTimeout(function(){$("#" + modalId).css("opacity","1");},500);


modalsOpened1++;
currentZindexStack1++;	
modalsOpened++;
currentZindexStack++;	


if(checkIfValueIsInArray(modalId,openedModals) == false) {
openedModals.push(modalId);
}

	
}



function closeModal(modalId) {
currentZindexStack--;	

for(var i = 0;i<openedModals.length;i++) {
if( "#" + openedModals[i] == modalId) {
openedModals.splice(i,1);
break;	
}	
}

$(modalId).modal('close');
}