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

			//Send to the other users the disconnection information
			foreach($this->clients as $client){					
				if($conn!=$client){		
					$client->send(
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
					$chat_msg = $data->chat_msg;
					$to_id= $data->to_id;
					$time= $data->time;
					$destination_type= $data->destination_type;
					$json_message=json_encode(
										array(
											"type"=>$type,
											"msg"=>$chat_msg,
											"from_id"=>$from_id,
											"to_id"=>$to_id,
											"destination_type"=>$destination_type,
											"time"=>$time
										)
									);
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

					$from->send($json_message);		
					$query="INSERT INTO messages(mess_text,source,$destination_type) VALUES ('$chat_msg','$from_id','$to_id');";
					$this->db->query($query);
					$result=$this->db->query("SELECT username FROM users WHERE userID='$from_id';")->fetchArray();
					$from=$result['username'];
					$result=$this->db->query("SELECT username FROM users WHERE userID='$to_id';")->fetchArray();
					$to=$result['username'];
					echo"$from($from_id) sent a message to $to($to_id)\n";
					break;

				case 'socket':
					$user_id = $data->user_id;
					$result=$this->db->query("SELECT username FROM users WHERE userID='$user_id';")->fetchArray();
					$this->users_ids[$from->resourceId]=$user_id;
					echo"$result[username]($user_id) just connected\n";
					$query="UPDATE users SET is_online='1' WHERE userID='$user_id';";
					$this->db->query($query);
					// Output
					foreach($this->clients as $client)					
						if($from!=$client)						
							$client->send(
								json_encode(
									array(
										"type"=>"connected",
										"user_id"=>$user_id
									)
								)
							);
					break;
						
				case 'writing':
					$from_id = $data->from_id;
					$to_id= $data->to_id;
					
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

					$result=$this->db->query(
						"SELECT username FROM users WHERE userID='$from_id';"
						)->fetchArray();
					$from=$result['username'];
					$result=$this->db->query(
						"SELECT username FROM users WHERE userID='$to_id';"
						)->fetchArray();
					$to=$result['username'];
					echo"$from($from_id) is writing to $to($to_id)\n";
					break;

				case 'not_writing':
					$from_id = $data->from_id;
					$to_id= $data->to_id;
					
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

					$result=$this->db->query(
						"SELECT username FROM users WHERE userID='$from_id';"
						)->fetchArray();
					$from=$result['username'];

					$result=$this->db->query(
						"SELECT username FROM users WHERE userID='$to_id';"
						)->fetchArray();
					$to=$result['username'];

					echo"$from($from_id) non writing to $to($to_id) anymore\n";
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