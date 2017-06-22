<?php
require_once "common_requires.php";
require_once "logged_in_importants.php";





$echo_this = "";
	
$all_contacts_arr = $con->query("select users.id, first_name, last_name, user_name, gender, avatar_picture, contact from contacts left join users on contacts.contact_of = users.id where contact = ". $_SESSION["user_id"] ." order by first_name asc")->fetchAll();

$last_letter = "a";


$gender_info_arr = [["totalNumber" => 0,"icon" => "icons/male.png"],["totalNumber" => 0,"icon" => "icons/female.png"]];

for($x = 0;$x < count($all_contacts_arr);$x++) {		

if($all_contacts_arr[$x]["gender"] == "male") {
$gender_info_arr[0]["totalNumber"] += 1;
}
else if($all_contacts_arr[$x]["gender"] == "female") {
$gender_info_arr[1]["totalNumber"] += 1;	
}

$single_letter = substr($all_contacts_arr[$x]["first_name"],0,1);
$single_letter_id = "singleLetter" . $single_letter;

# if the first letter of this user's first name is different from the first letter of the last user's first name, that means the reign of the last letter has ended and we have a new letter so we need to provide the necessary markup for new letter starts.
if($single_letter != $last_letter) {
$echo_this .= "<div class='singleLetterContainer row'>
<div class='singleLetter col l1 m1 s2' id='".$single_letter_id."'>".strtoupper($single_letter)."</div>
</div>
<div class='oneLetterRowsContainer myHorizontalCardStyle cardStyles' data-letter-element='#".$single_letter_id."'>";
}

// check if the user in the current iteration has disabled or requested to delete his account, if so then skip this iteration.
if($con->query("SELECT * FROM account_states where user_id = ".$all_contacts_arr[$x]["contact"])->fetch()[0] != "") {
continue;	
}	
	

$avatar_arr = $con->query("SELECT * FROM avatars WHERE id_of_user = ".$all_contacts_arr[$x]["id"]." order by id desc limit 1")->fetch();	
$avatar_positions = explode(",",$avatar_arr["positions"]);	
	
$uniq_id = rand(10000,10000000);	
	
$echo_this .=  "
<script>

	$('#".$uniq_id."').on('load',function(){
		$(this).parent().css('transform','rotate(' + ". ($avatar_arr["rotate_degree"] != "" ? $avatar_arr["rotate_degree"] : 0) ." + 'deg)');
		fitToParent($(this));
		adaptRotateWithMargin($(this),". ($avatar_arr["rotate_degree"] != "" ? $avatar_arr["rotate_degree"] : 0) .",false);
	});
	
	
	Waves.attach('#contact".$all_contacts_arr[$x]["id"]."', ['waves-block']);
	Waves.init();
	
</script>

<div class='contactsSingleRow row modal-trigger view-user showUserModal' id='contact".$all_contacts_arr[$x]["id"]."' data-target='modal1' data-user-id='".$all_contacts_arr[$x]["id"]."'>

<div class='col l2 m3 s3 contactsAvatarRow'>
<div class='contactsAvatarContainer'>
". 
($all_contacts_arr[$x]["avatar_picture"] == "" ? letter_avatarize($all_contacts_arr[$x]["first_name"],"medium") : "
<div class='contactsAvatarRotateContainer rotateContainer' style='margin-top:".$avatar_positions[0]."%;margin-left:".$avatar_positions[1]."%;'>
<div class='contactsAvatarRotateDiv'>
<img id='".$uniq_id."' class='avatarImages' src='".$all_contacts_arr[$x]["avatar_picture"]."' alt='Avatar'/>
</div>
</div>")
 ."
</div><!-- end .contactsAvatarContainer -->
</div><!-- end .contactsAvatarRow -->

<div class='col l6 m5 s5 contactsInfosContainer'>
<div class='contactsInfosContainerChild'>
<div class='contactsFullName'><span class='contactsFullNameText'>". htmlspecialchars($all_contacts_arr[$x]["first_name"] . " " . $all_contacts_arr[$x]["last_name"]) ."</span></div>
<div class='contactsUserName'>@". htmlspecialchars($all_contacts_arr[$x]["user_name"]) ."</div>
</div>
</div><!-- end .contactsInfosContainer -->

<div class='col l2 m2 s2 removeContactContainer'>
<button class='addOrRemoveContact removeContact waves-effect waves-grey' data-user-id='".$all_contacts_arr[$x]["id"]."'>
<i class='material-icons deleteContactButton'>delete</i>
</button>
</div>

</div><!-- end .contactsSingleRow -->";

# if the first letter of this user's first name is different from the first letter of the last user's first name, that means the reign of the last letter has ended and we have a new letter so we need to provide the necessary markup for new letter ends.
if($single_letter != $last_letter) { 
$echo_this .=  "</div><!-- end .oneLetterRowsContainer -->";
}

$last_letter = $single_letter;
}


$contacts_modal_top = "<div class='contactsModalTop'>
<div class='contactsModalSingleElement cardStyles'><img src='". $gender_info_arr[0]["icon"] ."' alt='Male'/><span class='contactsModalSingleElementMainText'>". (count($all_contacts_arr) > 0 ? (($gender_info_arr[0]["totalNumber"] / count($all_contacts_arr)) * 100) : 0) ."%</span></div>
<div class='contactsModalSingleElement cardStyles'><img src='". $gender_info_arr[1]["icon"] ."' alt='Female'/><span class='contactsModalSingleElementMainText'>". (count($all_contacts_arr) > 0 ? (($gender_info_arr[1]["totalNumber"] / count($all_contacts_arr)) * 100) : 0) ."%</span></div>
</div><!-- end .contactsModalTop -->";


if(count($all_contacts_arr) < 1) {
// we use this div as a placeholder when a user has no contacts and opens the contacts modal to tell them that they have no contacts.
echo $contacts_modal_top . "<div class='emptyNowPlaceholder'>
<i class='material-icons'>info</i>
<br>
After You Follow some users, They Will Appear Here
</div>";	
}
else {
echo $contacts_modal_top . $echo_this;	
}

unset($con);


?>