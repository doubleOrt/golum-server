<?php
# this page contains a function that returns the html for a letter avatar.

function letter_avatarize($str,$letter_size) {	
$avatar_first_letter = substr(strtoupper($str),0,1);

if($letter_size == "large") {	
$letter_avatar_font_size = "42px";	
}
if($letter_size == "medium") {
$letter_avatar_font_size = "22px";		
}
if($letter_size == "small") {
$letter_avatar_font_size = "16px";	
}
if($letter_size == "smaller") {
$letter_avatar_font_size = "14px";	
}


return "
<div class='letterAvatarsContainer'>
<div class='avatarFirstLetter' style='font-size:". $letter_avatar_font_size ." !important'>
". $avatar_first_letter ."
</div>
</div>";
	
}

?>