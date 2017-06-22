<?php
#we make a call to this page everytime the settings modal is opened, and we show the user the confirm email code if we have to.


require_once "common_requires.php";
require_once "logged_in_importants.php";

if(is_numeric($user_info_arr["activated"])) {
echo "<div class='col l12 m12 s12 row confirmEmailContainer smallFormContainer smallFormTopAndShadowed'>

<div class='input-field col l8 m8 s8'>
<input id='confirmation_code' name='confirmation_code' type='text'>
<label for='confirmation_code'>Confirmation Code</label>
</div>

<div class='input-field col l4 m4 s4 smallFormButtonContainer'>
<a href='#' id='confirmEmail' class='waves-effect waves-ff3333 btn commonButton'>Confirm</a>
</div>

</div>";
}


?>