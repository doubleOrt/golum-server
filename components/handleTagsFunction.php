<?php

// this function takes a string and prepend/appends the required markup to make that raw tag into an actual tag with tag functionality and styles.
function handleTags($string, $elementAttributes) {	
return preg_replace("/(#\w+)/", "<span ". $elementAttributes .">$0</span>", $string);
}

?>