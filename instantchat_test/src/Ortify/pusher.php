<?php
namespace Ortify;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;


class Pusher implements WampServerInterface {    


/* note that the "type" field in a message we send back to the client is to distinguish
   between messages that are made in reponse to a published websocket by the user and 
   messages that the user does not request themselves. So, 0 is for messages that are a response
   to a user's publish while 1 is for messages that are not a response, such as when we send 
   a user a message to alert them that there is a new message.
   */

	private $connection;

	public function __construct() {
		include_once "/../../../components/db_connection.php";
		$this->connection = $con;
	}
	
/**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();
	protected $connections_and_user_ids = array();

    public function onSubscribe(ConnectionInterface $conn, $topic) {
        $this->subscribedTopics[$topic->getId()] = $topic;
    }

    /**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onBlogEntry($entry) {
        $data = json_decode($entry, true);
		
		$message_topic = "chat_" . $data["chat_id"];
		
        // handle broadcasting this message to those who are viewing this chat at the moment.
        if (array_key_exists($message_topic, $this->subscribedTopics)) {
		$topic = $this->subscribedTopics[$message_topic];
        // re-send the data to all the clients subscribed to that category
        $topic->broadcast($data);
        }
		
		for($i = 0; $i < count($data["chatter_ids"]); $i++) {
			$user_topic = "user_" . $data["chatter_ids"][$i];
			if(array_key_exists($user_topic, $this->subscribedTopics)) {
				$this->subscribedTopics[$user_topic]->broadcast(json_encode([
				"type" => "1", 
				"data" => [
					"chat_id" => $data["chat_id"], 
					"sender_id" => $data["sender_info"]["id"]]
				]));
			}
		}

    }

	
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {
    }
    public function onOpen(ConnectionInterface $conn) {
    }
    public function onClose(ConnectionInterface $conn) {
		
		foreach($this->subscribedTopics as $topic) {
			if($topic->getIterator()->contains($conn)) {
				$topic_id = $topic->getId();
				if(substr($topic_id, 0, 4) === "user") {
					$user_id = explode("_",$topic_id)[1];
					if(filter_var($user_id, FILTER_VALIDATE_INT) !== false) {
						$this->update_user_last_seen($user_id);
					}
					if(isset($this->subscribedTopics["user_state_" . $user_id])) {
						$this->subscribedTopics["user_state_" . $user_id]->broadcast(json_encode(["current_state" => "Just went offline"]));
					}
				}
				$topic->getIterator()->detach($conn);
				if(count($topic->getIterator()) < 1) {
					unset($this->subscribedTopics[$topic_id]);
				}
			}
		}
		
    }
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
    }
	
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
			
		if(count($event) >= 3 && filter_var($event[2], FILTER_VALIDATE_INT) !== false) {
			// if event type is 0, then it means that the client is querying a user's online/offline state
			if($event[0] === 0) {
				if(substr($event[1], 0, 4) === "user") {
					$user_id = explode("_",$event[1])[1];
					if(filter_var($user_id, FILTER_VALIDATE_INT) !== false) {
						$is_user_online = $this->is_user_online($event[1], $user_id);
						if($is_user_online === true) {
							$user_state_arr = json_encode([
							"type" => "0", 
							"data" => [
								"request_type" => "0", 
								"request_id" => $event[2],
								"user_id" => $user_id, 
								"current_state" => "Online"
								]
							]);
						}
						else {
							$user_state_arr = json_encode([
							"type" => "0", 
							"data" => [
								"request_type" => "0", 
								"request_id" => $event[2],
								"user_id" => $user_id, 
								"current_state" => $is_user_online
								]
							]);
						}
						echo var_dump($user_state_arr);
						// send the online/offline state back to the client
						$conn->event($topic, $user_state_arr); 
					}
				}
			}
		}
		
		if(count($event) >= 2) {
			// event 2 means that we want to send a "online now" message to all the people who are want to receive it.
			if($event[0] === 2) {
				if(isset($this->subscribedTopics[$event[1]])) {
					$this->subscribedTopics[$event[1]]->broadcast(json_encode(["current_state" => "Online"]));
				}
			}
		}

	}
	
    public function onError(ConnectionInterface $conn, \Exception $e) {
    }
	
	
	public function is_user_online($user_channel_id, $user_id) {
		if (array_key_exists($user_channel_id, $this->subscribedTopics)) {
			return true;			
		}
		else {
			$get_user_last_seen_prepared = $this->connection->prepare("select last_seen from users where id = :user_id");
			$get_user_last_seen_prepared->execute([":user_id" => $user_id]);
			return last_online($get_user_last_seen_prepared->fetch()[0]);
		}
	}

	public function update_user_last_seen($user_id) {
	$this->connection->prepare("update users set last_seen = :last_seen where id = :user_id")->execute([":last_seen" => time(), ":user_id" => $user_id]);
	}
	
}


function last_online($time) {
		
global $JUST_WENT_OFFLINE_STRING;		
		
if($time == 0) {
return $time;	
}
	
$time = intval($time);	

date_default_timezone_set("Asia/Baghdad");
		
$today = new \DateTime(); // This object represents current date/time
$today->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$match_date = \DateTime::createFromFormat( "Y-m-d H:i", date("Y-m-d H:i",$time));
$match_date->setTime( 0, 0, 0 ); // reset time part, to prevent partial comparison

$diff = $today->diff( $match_date );
$diffDays = (integer)$diff->format( "%R%a" ); // Extract days count in interval

if(time() - $time < 120) {# we say he was online that number of minutes ago.
return "Just went offline";
}	
else if($diffDays == 0) {
return "Last Online At " . date("H:i", $time);	
}
else if($diffDays == -1) {
return "Last Online Yesterday At " . date("H:i",$time);	
} 
else if(time() - $time < 604800){
return "Last Online On " . date("l",$time);	
}
else {
return "Last Online On " . date("j, M Y",$time);		
}
}
