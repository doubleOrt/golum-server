<?php 
#this account checks for all accounts that are awaiting a delete process and deletes those that have been waiting for more than 2 weeks


# select all rows from account_states where the row has been waiting to be deleted for more than 2 weeks (1209600 == 14 days).
$check_for_should_deletes_query = $con->query("select * from account_states where ". time() ." - time > 1209600 and type = 'delete'");

while($check_for_should_deletes_row = $check_for_should_deletes_query->fetch()) {
$con->exec("delete from users where id = ". $check_for_should_deletes_row["user_id"] ." limit 1");
$con->exec("delete from avatars where id_of_user = ". $check_for_should_deletes_row["user_id"]);
$con->exec("delete from account_states where user_id = ". $check_for_should_deletes_row["user_id"] ." limit 1");
}

?>