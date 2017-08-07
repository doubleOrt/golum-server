<?php
#this page receives an ajax call whenever a user wants to send a file in chat.

require_once "common_requires.php";
require_once "logged_in_importants.php";
require_once "file_upload_custom_functions.php";


$echo_arr = [[], 0];

$MAXIMUM_FILE_SIZE = 5000000;


if(isset($_FILES["the_file"]) && isset($_POST["chat_id"]) && filter_var($_POST["chat_id"], FILTER_VALIDATE_INT) !== false) {

//this is the path of the image after upload and before renaming the file
$upload_to = "../users/" . $GLOBALS["base_user_id"] . "/sentFiles/";

//the extension of the uploaded file 
$upload_pathinfo = strtolower(pathinfo($upload_to . basename($_FILES["the_file"]["name"]),PATHINFO_EXTENSION));

//what is going to be the id of the new avatar picture ? we get this by getting the id of the last row and adding 1 to it.
$what_id_query = custom_pdo("SELECT count(id) FROM sent_files where id_of_user = :base_user_id", [":base_user_id" => $GLOBALS["base_user_id"]])->fetch();
$what_id = $what_id_query[0] > 0 ? $what_id_query[0] + 1 : 1;

$storagePath = "../users/". $GLOBALS["base_user_id"] ."/sentFiles/"; // this is relative to this script, better use absolute path.
$new_name = $what_id;
$allowedMimes = array('image/png', 'image/jpg', 'image/gif', 'image/pjpeg', 'image/jpeg');

$upload_result = upload($_FILES["the_file"]["tmp_name"], $storagePath, $new_name, $allowedMimes, $MAXIMUM_FILE_SIZE);


// if upload failed
if(!is_array($upload_result) || count($upload_result) < 2 || $upload_result[0] !== true) {
$echo_arr[1] = $upload_result;
} 	
else {	

//this is the new path to the avatar.
$new_path = "users/". $GLOBALS["base_user_id"] ."/sentFiles/" . $what_id . "." . $upload_result[1];

//add a new row to the sent_files table, check if it is successful.
$insert_into_sent_files = custom_pdo("INSERT INTO sent_files (chat_id, id_of_user,path,date_of) values(:chat_id, :base_user_id, :new_path, :date_of)", [":chat_id" => $_POST["chat_id"], ":base_user_id" => $GLOBALS["base_user_id"], ":new_path" => $new_path, ":date_of" => date("Y/m/d H:i")]);

// insert a new row into the messages table, note that we insert the id of the sent file (from the sent_files table) into the "message" column instead of an actual message.
$insert_into_messages = custom_pdo("INSERT INTO messages (chat_id,message_from,message,date_of,message_type) values(:chat_id, :base_user_id, :sent_files_id, :date_of, 'file-message')", [":chat_id" => $_POST["chat_id"], ":base_user_id" => $GLOBALS["base_user_id"], ":sent_files_id" => $con->lastInsertId(), ":date_of" => date("Y/m/d H:i")]);

$message_id = $con->lastInsertId();

//if query was successful
if($insert_into_sent_files->rowCount() > 0 && $insert_into_messages->rowCount() > 0) {

custom_pdo("update chats set latest_activity = :time where id = :chat_id", [":time" => time(), ":chat_id" => $_POST["chat_id"]]);

$chat_id_arr = custom_pdo("select * from chats where id = :chat_id", [":chat_id" => $_POST["chat_id"]])->fetch();	
$chatter_ids_arr = explode("-",$chat_id_arr["chatter_ids"]);

$messager_arr = custom_pdo("select id,first_name,last_name,avatar_picture from  users where id = :base_user_id", [":base_user_id" => $GLOBALS["base_user_id"]])->fetch();
$messager_avatar_arr = custom_pdo("SELECT positions,rotate_degree FROM avatars WHERE id_of_user = :base_user_id order by id desc limit 1", [":base_user_id" => $GLOBALS["base_user_id"]])->fetch();

if($messager_avatar_arr[0] != "") {
$messager_avatar_rotate_degree = $messager_avatar_arr["rotate_degree"];
$messager_avatar_positions = explode(",",htmlspecialchars($messager_avatar_arr["positions"], ENT_QUOTES, "utf-8"));
}
else {
$messager_avatar_rotate_degree = 0;
$messager_avatar_positions = [0,0];	
}

array_push($echo_arr[0],[
"update_type" => "0",
"chat_id" => htmlspecialchars($_POST["chat_id"], ENT_QUOTES, "utf-8"),
"chatter_ids" => $chatter_ids_arr,
"message" => $SERVER_URL . $new_path,
"message_id" => $message_id,
"message_type" => 2,
"read_yet" => 0,
"time_string" => date("H:i"),
"message_sent_by_base_user" => 1,
"message_is_first_in_sequence" => 0,
"sender_info" => [
"id" => htmlspecialchars($messager_arr["id"], ENT_QUOTES, "utf-8"),
"first_name" => htmlspecialchars($messager_arr["first_name"], ENT_QUOTES, "utf-8"),
"last_name" => htmlspecialchars($messager_arr["last_name"], ENT_QUOTES, "utf-8"),
"avatar" => ($messager_arr["avatar_picture"] != "" ? (preg_match('/https?:\/\/[^ ]+?(?:\.jpg|\.png|\.gif)/', $messager_arr["avatar_picture"]) ? $messager_arr["avatar_picture"] : ($SERVER_URL . htmlspecialchars($messager_arr["avatar_picture"], ENT_QUOTES, "utf-8"))) : ""),
"avatar_rotate_degree" => htmlspecialchars($messager_avatar_rotate_degree, ENT_QUOTES, "utf-8"),
"avatar_positions" => $messager_avatar_positions
]
]);	


$socket_message = $echo_arr[0][0];
$socket_message["message_sent_by_base_user"] = 0;

// This is our new stuff
$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://localhost:5555");
$socket->send(json_encode($socket_message));
}
else {
$echo_arr[1] = "Something Went Wrong, Sorry :(";
die();		
}
}


}



echo json_encode($echo_arr);

unset($con);




?>