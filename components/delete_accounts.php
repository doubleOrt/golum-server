<?php 
#this account checks for all accounts that are awaiting a delete process and deletes those that have been waiting for more than 2 weeks


# select all rows from account_states where the row has been waiting to be deleted for more than 2 weeks (1209600 == 14 days).
$check_for_should_deletes_query = custom_pdo("select * from account_states where :time - time > 1209600 and type = 'delete'", [":time" => time()]);

while($check_for_should_deletes_row = $check_for_should_deletes_query->fetch()) {
custom_pdo("delete from users where id = :user_id limit 1", [":user_id" => $check_for_should_deletes_row["user_id"]]);
custom_pdo("delete from avatars where id_of_user = :user_id limit 1", [":user_id" => $check_for_should_deletes_row["user_id"]]);
custom_pdo("delete from account_states where user_id = :user_id limit 1", [":user_id" => $check_for_should_deletes_row["user_id"]]);
}

?>