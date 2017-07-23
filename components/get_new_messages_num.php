<?php
//we make a call to this page whenever the user opens the sidebar to tell them the number of new messages they got.

require_once "common_requires.php";
require_once "logged_in_importants.php";

$echo_arr = [[]];

$new_messages_num_prepared = $con->prepare("select count(id) from messages where read_yet = 0 and message_from != :base_user_id and chat_id in (select id from (select *, case SUBSTRING_INDEX(chatter_ids, '-', 1) when :base_user_id then SUBSTRING_INDEX(chatter_ids, '-', -1) else SUBSTRING_INDEX(chatter_ids, '-', 1) end as chat_recipient from chats where chatter_ids like concat('%', :base_user_id, '%')) t1 where chat_recipient not in (SELECT SUBSTRING_INDEX(user_ids, '-', -1) as blocked_user FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', 1) = :base_user_id) and chat_recipient not in (SELECT SUBSTRING_INDEX(user_ids, '-', 1) as blocker FROM blocked_users WHERE SUBSTRING_INDEX(user_ids, '-', -1) = :base_user_id) and chat_recipient not in (SELECT user_id from account_states))");
$new_messages_num_prepared->execute([":base_user_id" => $_SESSION["user_id"]]);
$new_messages_num = $new_messages_num_prepared->fetch()[0];

array_push($echo_arr[0], $new_messages_num);

echo json_encode($echo_arr);



?>