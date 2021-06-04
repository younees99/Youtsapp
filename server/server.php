<?php 
	set_time_limit(0);
	date_default_timezone_set('Europe/Rome');
	use Ratchet\MessageComponentInterface;
	use Ratchet\ConnectionInterface;
	use Ratchet\Server\IoServer;
	use Ratchet\Http\HttpServer;
	use Ratchet\WebSocket\WsServer;
	require_once '../vendor/autoload.php';
	require '../db/db.php';

	class Chat implements MessageComponentInterface {
		private $db;
		protected $clients;
		protected $users_ids = array();
		private $color;

		public function __construct() {
			$this->clients = new \SplObjectStorage;
			$this->db=new db("localhost","root","root");
			$this->db->query("UPDATE users SET is_online='0';");
			$this->db->query("UPDATE friends SET is_writing='0';");
			$this->db->query("UPDATE groups_users SET is_writing='0';");
		}

		//A user opens the connection with the socket
		public function onOpen(ConnectionInterface $conn) {
			$this->clients->attach($conn);		
		}

		//The user closes the connection with the socket
		public function onClose(ConnectionInterface $conn) {
			// Output
			$userID=$this->users_ids[$conn->resourceId];
			$result=$this->db->query("SELECT username FROM users WHERE userID='$userID';")->fetchArray();
			echo"$result[username] disconnected\n";
			$timestamp = date('Y-m-d H:i:s', strtotime("now"));
			$this->db->query("UPDATE users SET last_seen='$timestamp',is_online='0' WHERE userID='$userID';");
			$this->db->query("UPDATE friends SET is_writing='0' WHERE userID='$userID';");
			$this->db->query("UPDATE groups_users SET is_writing='0' WHERE userID='$userID';");

			//Send to the other users the disconnection information
			$query="SELECT friendID FROM friends WHERE userID='$userID';";
					$result=$this->db->query($query)->fetchAll();
					foreach ($result as $row) {
						$friendId=$row['friendID'];
						if(in_array($friendId, $this->users_ids)){
							$this->variableConn(
										array_search(
											$friendId,
											$this->users_ids
											)
										)->send(
											json_encode(
												array(
													"type"=>"disconnected",
													"user_id"=>$userID,
													"time"=>$timestamp
												)
											)						
										);	
						}
					}
			
			unset($this->users_ids[$conn->resourceId]);
			$this->clients->detach($conn);
		}

		public function variableConn($index){
			foreach ($this->clients as $client) {
				if($index==$client->resourceId){
					return $client;
					break;
				}
			}
		}

		public function onMessage(ConnectionInterface $from,  $data) {
			$from_id = $from->resourceId;
			$data = json_decode($data);
			$type = $data->type;
			switch ($type) {
				case 'chat':
					$from_id = $data->from_id;
					$chat_msg = $this->db->escapeString($data->chat_msg);
					$to_id= $data->to_id;
					$time= $data->time;
					$destination_type= $data->destination_type;	
					$query="INSERT INTO messages(mess_text,source_user,$destination_type) 
								VALUES ('$chat_msg','$from_id','$to_id');";
					$this->db->query($query);
					$last_id=$this->db->getInsertId();
					$query="UPDATE friends 
								SET last_message='$last_id' 
									WHERE 
										userID='$from_id' AND friendID='$to_id'
									OR
										userID='$to_id' AND friendID='$from_id';";			
					$this->db->query($query);
					$query="SELECT image_url FROM users WHERE userID='$from_id';";			
					$result=$this->db->query($query)->fetchAll();
					foreach ($result as $row)
						$image_url=$row['image_url'];
					$json_message=json_encode(
										array(
											"type"=>$type,
											"msg"=>$chat_msg,
											"from_id"=>$from_id,
											"to_id"=>$to_id,
											"destination_type"=>$destination_type,
											"image_url"=>$image_url,
											"time"=>$time
										)
									);
					
					//Send back the message to print it in live
					$from->send($json_message);	
					
					//If the destination is user and is online send him the message
					if($destination_type=='destination_user'){
						if(in_array($to_id, $this->users_ids)){
							$this->variableConn(
										array_search(
											$to_id,
											$this->users_ids
											)
										)->send(
											$json_message						
									);	
						}	
					}
					else{
						$query="SELECT userID FROM groups_users WHERE groupID='$to_id';";
						$result=$this->db->query($query)->fetchAll();
						foreach($result as $row){
							$userID=$row['userID'];
							if(in_array($userID, $this->users_ids)&&$userID!=$from_id){
								$this->variableConn(
											array_search(
												$userID,
												$this->users_ids
												)
											)->send(
												$json_message						
										);	
							}
						}
					}					
					break;

				case 'socket':
					$user_id = $data->user_id;
					$result=$this->db->query("SELECT username FROM users WHERE userID='$user_id';")->fetchArray();
					$this->users_ids[$from->resourceId]=$user_id;
					echo"$result[username]($user_id) just connected\n";
					$query="UPDATE users SET is_online='1' WHERE userID='$user_id';";
					$this->db->query($query);
					// Output
					$query="SELECT friendID FROM friends WHERE userID='$user_id';";
					$result=$this->db->query($query)->fetchAll();
					foreach ($result as $row) {
						$friendId=$row['friendID'];
						if(in_array($friendId, $this->users_ids)){
							$this->variableConn(
										array_search(
											$friendId,
											$this->users_ids
											)
										)->send(
											json_encode(
												array(
													"type"=>"connected",
													"user_id"=>$user_id
												)
											)						
										);	
						}
					}
					break;
						
				case 'writing':
					$from_id = $data->from_id;
					$to_id= $data->to_id;
					$query="UPDATE friends SET is_writing='1' WHERE userID='$from_id' AND friendID='$to_id';";
					$this->db->query($query);
					
					if(in_array($to_id, $this->users_ids)){
						$this->variableConn(array_search($to_id,$this->users_ids))->send(
									json_encode(
										array(
											"type"=>$type,
											"from_id"=>$from_id,
											"to_id"=>$to_id,
										)
									)
								);	
					}	
					break;

				case 'not_writing':
					$from_id = $data->from_id;
					$to_id= $data->to_id;
					$query="UPDATE friends SET is_writing='0' WHERE userID='$from_id' AND friendID='$to_id';";
					
					if(in_array($to_id, $this->users_ids)){
						$this->variableConn(array_search($to_id,$this->users_ids))->send(
									json_encode(
										array(
											"type"=>$type,
											"from_id"=>$from_id,
											"to_id"=>$to_id,
										)
									)
								);	
					}	
					break;
							
			}
		}

		public function onError(ConnectionInterface $conn, \Exception $e) {			
			$userID=$this->users_ids[$conn->resourceId];
			$result=$this->db->query("SELECT username FROM users WHERE userID='$userID';")->fetchArray();
			echo"$result[username] disconnected for an error: ".$e."\n";
		}
	}

	$server = IoServer::factory(
		new HttpServer(new WsServer(new Chat())),
		8080
	);

	echo"Server started!\n";
	$server->run();
	$db->close;
?>