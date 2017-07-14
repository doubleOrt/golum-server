<?php

require_once "initialization.php";

$echo_arr = [];

$new_notifications_num = dataQuery("select count(id) from (select *, (count(*) - 1) as and_others, (select count(id) from blocked_users where user_ids = concat(notification_to, '-', notification_from)) as user_blocked_by_base_user from (select * from notifications) t1 where notification_to = :user_id and read_yet = '0' group by type, extra, read_yet) t3 where user_blocked_by_base_user = '0'", [":user_id" => $_SESSION["user_id"]])[0][0];

array_push($echo_arr, $new_notifications_num);

echo json_encode($echo_arr);


?>