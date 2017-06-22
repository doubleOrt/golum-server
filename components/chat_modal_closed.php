<?php

require_once "common_requires.php";

$shmid = $_SESSION["user_id"] . "" . 4; 
$shm = shmop_open($shmid, 'c', 0777, 1024);
shmop_write($shm, str_to_nts("none"), 0);
shmop_close($shm);


?>