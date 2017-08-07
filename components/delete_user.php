<?php 

require_once "initialization.php";

function delete_user($user_id) {
global $SERVER_URL;

custom_pdo("delete from avatars where id_of_user = :user_id", [":user_id" => $user_id]);
custom_pdo("delete from backgrounds where id_of_user = :user_id", [":user_id" => $user_id]);
custom_pdo("delete from account_states where user_id = :user_id", [":user_id" => $user_id]);
custom_pdo("delete from blocked_users where user_ids like concat('%-', :user_id) or user_ids like concat(:user_id, '-%')", [":user_id" => $user_id]);
custom_pdo("delete from contacts where contact_of = :user_id", [":user_id" => $user_id]);
custom_pdo("delete from contacts where contact = :user_id", [":user_id" => $user_id]);
custom_pdo("delete from following_tags where id_of_user = :user_id", [":user_id" => $user_id]);
// delete all files sent by/to this user 
custom_pdo("delete from sent_files where chat_id in (select id from chats where chatter_ids like concat('%-', :user_id) or chatter_ids like concat(:user_id, '-%'))", [":user_id" => $user_id]);
// delete all reports by this user (logically, this shouldn't be so, but it probably doesn't matter so...)
custom_pdo("delete from post_reports where user_id = :user_id", [":user_id" => $user_id]);
// delete all reports to this user's posts
custom_pdo("delete from post_reports where post_id in (select id from posts where posted_by = :user_id)", [":user_id" => $user_id]);
// delete all comment upvotes/downvotes by this user 
custom_pdo("delete from comment_upvotes_and_downvotes where user_id = :user_id", [":user_id" => $user_id]);
// delete all comment upvotes/downvotes to this user's comments
custom_pdo("delete from comment_upvotes_and_downvotes where comment_id in (select id from post_comments where user_id = :user_id)", [":user_id" => $user_id]);
// delete all reply upvotes/downvotes by this user
custom_pdo("delete from reply_upvotes_and_downvotes where user_id = :user_id", [":user_id" => $user_id]);
// delete all reply upvotes/downvotes to this user's replies
custom_pdo("delete from reply_upvotes_and_downvotes where comment_id in (select id from comment_replies where user_id = :user_id)", [":user_id" => $user_id]);
// delete all notifications sent by/to this user
custom_pdo("delete from notifications where notification_from = :user_id or notification_to = :user_id", [":user_id" => $user_id]);
// delete all the rows from hidden_chats added by this user (hidden by this user)
custom_pdo("delete from hidden_chats where user_id = :user_id", [":user_id" => $user_id]);
// delete all the rows from hidden_chats that refer to a chat where this user is the second party
custom_pdo("delete from hidden_chats where chat_id in (select id from chats where chatter_ids like concat('%-', :user_id) or chatter_ids like concat(:user_id, '-%'))", [":user_id" => $user_id]);
// delete all this user's favorites
custom_pdo("delete from favorites where user_id = :user_id", [":user_id" => $user_id]);
// delete all favorites on this user's posts
custom_pdo("delete from favorites where post_id in (select id from posts where posted_by = :user_id)", [":user_id" => $user_id]);
// delete all replies by this user
custom_pdo("delete from comment_replies where user_id = :user_id", [":user_id" => $user_id]);
// delete all the replies to the entirety of this user's comments
custom_pdo("delete from comment_replies where comment_id in (select id from post_comments where user_id = :user_id)", [":user_id" => $user_id]);
// delete all this user's comments
custom_pdo("delete from post_comments where user_id = :user_id", [":user_id" => $user_id]);
// delete all comments to this user's posts
custom_pdo("delete from post_comments where post_id in (select id from posts where posted_by = :user_id)", [":user_id" => $user_id]);
// delete all votes by this user
custom_pdo("delete from post_votes where user_id = :user_id", [":user_id" => $user_id]);
// delete all votes to this user's posts
custom_pdo("delete from post_votes where post_id in (select id from posts where posted_by = :user_id)", [":user_id" => $user_id]);
// delete all posts posted by this user
custom_pdo("delete from posts where posted_by = :user_id", [":user_id" => $user_id]);
// delete all chats that involve this user 
custom_pdo("delete from chats where chatter_ids like concat('%-', :user_id) or chatter_ids like concat(:user_id, '-%')", [":user_id" => $user_id]);
// delete the user
custom_pdo("delete from users where id = :user_id", [":user_id" => $user_id]);

// deletes the user's dir and everything inside of it (Do note that this function does not work with URLs, only paths). 
deleteDir("../users/" . $user_id . "/");
}


?>