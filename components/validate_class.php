<?php

class ValidateItem {
public $value;	
public $reg_ex;	
public $on_wrong;	

public function __construct($value,$regex,$on_wrong) {
$this->value = $value;	
$this->reg_ex = $regex;	
$this->on_wrong = $on_wrong;	
}

public function validate() {
if(preg_match($this->reg_ex,$this->value) == false) {
return false;
}
else {
return true;
}			
}

public function on_wrong() {
return $this->on_wrong;	
}

}

?>