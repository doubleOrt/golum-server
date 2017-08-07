<?php

require_once "initialization.php";

$echo_arr = [];

$new_notifications_num_prepared = $con->prepare("select count(id) from (select *, (count(*) - 1) as and_others from (select * from notifications) t1 where notification_to = :base_user_id and read_yet = 0 group by type, extra, read_yet) t3 where notification_from not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and notification_from not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and notification_from not in (SELECT user_id from account_states)");
$new_notifications_num_prepared->execute([":base_user_id" => $GLOBALS["base_user_id"]]);
$new_notifications_num = $new_notifications_num_prepared->fetch()[0];

array_push($echo_arr, $new_notifications_num);

echo json_encode($echo_arr);


?>