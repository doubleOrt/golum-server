<?php
# just some shm facilitations, the first 2 make it easier to overwrite written shms, and the other two make a 5 line long chore a 1 function call.


function str_from_mem($value) {
$i = strpos($value, "\0");
if ($i === false) {
return $value;
}
$result =  substr($value, 0, $i);
return $result;
}

function str_to_nts($value) {
return "$value\0";
}


function read_shm($id) {
$shmop = shmop_open($id,"c",0777,1024);
$val = str_from_mem(shmop_read($shmop, 0, shmop_size($shmop)));	
shmop_close($shmop);
return $val;
}

function write_shm($id,$val) {
$shm = shmop_open($id, 'c', 0777, 1024);
shmop_write($shm, str_to_nts($val), 0);
shmop_close($shm);	
}

function delete_shm($id,$size) {
$shm = shmop_open($id, 'c', 0777, $size);
unset($shm);
shmop_delete($shm);
shmop_close($shm);	
}



?>