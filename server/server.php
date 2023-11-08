<?php 
	set_time_limit(0);
	date_default_timezone_set('Europe/Rome');
	use Ratchet\MessageComponentInterface;
	use Ratchet\ConnectionInterface;
	use Ratchet\Server\IoServer;
	use Ratchet\Http\HttpServer;
	use Ratchet\WebSocket\WsServer;
	require_once '../vendor/autoload.php';
	require '../db/config.php';

	class Chat implements MessageComponentInterface {
		private $db;
		protected $clients;
		protected $users_ids = array();

		public function __construct() {
			$this->clients = new \SplObjectStorage;
			$this->db=new db();
			$this->db->query("UPDATE Users SET is_online='0';");
			$this->db->query("UPDATE Friends SET is_typing='0';");
			$this->db->query("UPDATE Groups_users SET is_typing='0';");
		}		

		//Method that gets the connection variable from the user index
		public function variableConn($index){
			foreach ($this->clients as $client) {
				if($index==$client->resourceId){
					return $client;
					break;
				}
			}
		}

		//A user opens the connection with the socket
		public function onOpen(ConnectionInterface $conn) {
			$this->clients->attach($conn);		
		}

		//The user closes the connection with the socket
		public function onClose(ConnectionInterface $conn) {
			// Output
			$userID=$this->users_ids[$conn->resourceId];
			$result=$this->db->query("SELECT username FROM Users WHERE userID='$userID';")->fetchArray();
			echo"$result[username] disconnected\n";
			$timestamp = date('Y-m-d H:i:s', strtotime("now"));
			$this->db->query("UPDATE Users SET last_seen='$timestamp',is_online='0' WHERE userID='$userID';");
			$this->db->query("UPDATE Friends SET is_typing='0' WHERE userID='$userID';");
			$this->db->query("UPDATE Groups_users SET is_typing='0' WHERE userID='$userID';");

			//Query too fetch user ids of online friends and users with common groups
			$query = "SELECT
						U2.userID AS id
					FROM
						Users U1
					JOIN Groups_users GU ON
						U1.userID = GU.userID
					JOIN Users U2 ON
						U2.userID = GU.userID
					WHERE
						U1.userID = '$userID' AND U2.is_online = 1 AND U2.userID != U1.userID
					UNION
					SELECT
						F.friendID AS id
					FROM	
						Users U1
					JOIN Friends F ON
						U1.userID = F.userID
					JOIN Users U2 ON
						U2.userID = F.friendID
					WHERE
						U1.userID = '$userID' AND U2.is_online = 1;";
			$result = $this->db->query($query);
			$online_friends_group = $result->fetchAll();
			
			//Send to the other users the disconnection information	
			foreach ($online_friends_group as $row){
				$id_string = strval($row["id"]);
				$index=array_search($id_string,$this->users_ids);
				$this->variableConn($index)->send(
								json_encode(
									array(
										"type"=>"disconnected",
										"user_id"=>$userID,
										"time"=>$timestamp
									)
								)						
							);																
			}
			
			//Remove the disconnected user's id from the array of online users
			unset($this->users_ids[$conn->resourceId]);
			$this->clients->detach($conn);
		}

		//Method to send a Friend request
		public function sendRequest($last_id,$from_id,$to_id,$json_message){
			$query="INSERT INTO Friends (userID,friendID,last_message) VALUES(
						'$from_id',
						'$to_id',
						'$last_id'
					);";			
			$this->db->query($query);
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

		public function onMessage(ConnectionInterface $from,  $data) {
			$from_id = $from->resourceId;
			$data = json_decode($data);
			$type = $data->type;
			switch ($type) {
				case 'chat':
					$from_id = $data->from_id;
					$mess_text = $this->db->escapeString($data->mess_text);
					$to_id = $data->to_id;
					$time = date('H:i', strtotime("now"));
					$destination_type = $data->destination_type;						
					
					$query = "SELECT image_url,username FROM Users WHERE userID='$from_id';";			
					$result = $this->db->query($query);
					$row = $result->fetchArray();
					$image_url = $row['image_url'];
					$from_username = $row['username'];										

					$query="INSERT INTO Messages(mess_text,source_user,destination_$destination_type) 
								VALUES ('$mess_text','$from_id','$to_id');";
					$this->db->query($query);					
					$last_id=$this->db->getInsertId();

					$json_message=json_encode(
										array(
											"type"=>$type,
											"mess_text"=>$mess_text,
											"from_id"=>$from_id,
											"to_id"=>$to_id,
											"from_username"=>$from_username,
											"destination_type"=>$destination_type,
											"image_url"=>$image_url,
											"time"=>$time,
											"messages_id"=>$last_id
										)
									);


					if($destination_type=='user'){				
						$query="UPDATE Friends 
									SET last_message='$last_id' 
										WHERE 
											userID='$from_id' AND friendID='$to_id'
										OR
											userID='$to_id' AND friendID='$from_id';";			
						
							$this->db->query($query);	

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
						$query="UPDATE Groups 
									SET last_message='$last_id' 
										WHERE groupID='$to_id';";			
						$this->db->query($query);
						$query="SELECT userID FROM Groups_users WHERE groupID='$to_id';";
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
					
					//Send back the message to print it in live	
					$from->send($json_message);		

					break;

				case 'socket':
					$user_id = $data->user_id;
					echo "yo";
					$result=$this->db->query("SELECT username FROM Users WHERE userID='$user_id';")->fetchArray();
					$this->users_ids[$from->resourceId]=$user_id;
					echo"$result[username]($user_id) just connected\n";

					$query="UPDATE Users SET is_online='1' WHERE userID='$user_id';";
					$this->db->query($query);


					//Query to fetch all users typing to groups
					$query = "	SELECT
									'typing' AS 'type',
									U1.userID AS 'from_id',
									groupID AS 'to_id',
									U2.username AS 'from_username',
									'group' AS 'destination_type'
								FROM
									Users U1
									JOIN Groups_users GU ON
										U1.userID = GU.userID
									JOIN Users U2 ON
										U2.userID = GU.userID
								WHERE
									U1.userID= '$user_id'
									AND
									is_typing;";
					$users_typing = $this->db->query($query);
					



					//Send user connected information to online friends and online users with groups in common

					//Query too fetch user ids of online friends and users with common groups
					$query = "SELECT
								U2.userID AS id
							FROM
								Users U1
							JOIN Groups_users GU ON
								U1.userID = GU.userID
							JOIN Users U2 ON
								U2.userID = GU.userID
							WHERE
								U1.userID = '$user_id' AND U2.is_online = 1 AND U2.userID != U1.userID
							UNION
							SELECT
								F.friendID AS id
							FROM	
								Users U1
							JOIN Friends F ON
								U1.userID = F.userID
							JOIN Users U2 ON
								U2.userID = F.friendID
							WHERE
								U1.userID = '$user_id' AND U2.is_online = 1;";
					$result = $this->db->query($query);
					$online_users_per_group = $result->fetchAll();
					foreach ($online_users_per_group as $row){
						$id_string = strval($row["id"]);
						$index=array_search($id_string,$this->users_ids);
						$this->variableConn($index)->send(
										json_encode(
											array(
												"type"=>"connected",
												"user_id"=>$user_id
											)
										)						
									);																
					}
					break;

						
				case 'typing':
					$from_id = $data->from_id;					
					$to_id= $data->to_id;
					$destination_type= $data->destination_type;

					$query = "SELECT username FROM Users WHERE userID='$from_id';";			
					$result = $this->db->query($query);					
					$row = $result->fetchArray();

					$from_username = $row['username'];										

					if($destination_type=='user'){
						$query="UPDATE Friends SET is_typing='1' WHERE userID='$to_id' AND friendID='$from_id';";
						$this->db->query($query);	
						if(in_array($to_id, $this->users_ids)){
							$this->variableConn(
										array_search(
											$to_id,
											$this->users_ids
											)
										)->send(
											json_encode(
												array(
													"type"=>$type,
													"from_id"=>$from_id,
													"to_id"=>$to_id,
													"destination_type"=>$destination_type
												)
										)							
									);	
						}	
					}
					else{
						$query="UPDATE Groups_users SET is_typing='1' WHERE userID='$from_id' AND groupID='$to_id';";
						$this->db->query($query);	
						$query="SELECT userID FROM Groups_users WHERE groupID='$to_id';";
						$result=$this->db->query($query)->fetchAll();
						foreach($result as $row){
							$userID=$row['userID'];
							if(in_array($userID, $this->users_ids)&&$userID!=$from_id){
								$query="SELECT username FROM Users WHERE userID='$userID';";
								$result=$this->db->query($query)->fetchAll();
								$this->variableConn(
											array_search(
												$userID,
												$this->users_ids
												)
											)->send(
												json_encode(
													array(
														"type"=>$type,
														"from_id"=>$from_id,
														"to_id"=>$to_id,
														"from_username"=>$from_username,
														"destination_type"=>$destination_type
													)
											)						
										);	
							}
						}
					}		
					break;

				case 'not_typing':
					$from_id = $data->from_id;
					$to_id= $data->to_id;
					$destination_type= $data->destination_type;
					$query = "SELECT username FROM Users WHERE userID='$from_id';";			
					$result = $this->db->query($query);
					$row = $result->fetchArray();

					$query = "SELECT username FROM Users WHERE userID='$from_id';";			
					$result = $this->db->query($query);					
					$row = $result->fetchArray();

					$from_username = $row['username'];	

					if($destination_type=='user'){
						$query="UPDATE Friends SET is_typing='0' WHERE userID='$to_if' AND friendID='$from_id';";
						$this->db->query($query);	
						if(in_array($to_id, $this->users_ids)){
							$this->variableConn(
										array_search(
											$to_id,
											$this->users_ids
											)
										)->send(
											json_encode(
												array(
													"type"=>$type,
													"from_id"=>$from_id,
													"to_id"=>$to_id,
													"destination_type"=>$destination_type
												)
										)							
									);	
						}	
					}
					else{
						$query="UPDATE Groups_users SET is_typing='0' WHERE userID='$from_id' AND groupID='$to_id';";
						$this->db->query($query);	
						$query="SELECT userID FROM Groups_users WHERE groupID='$to_id';";
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
												json_encode(
													array(
														"type"=>$type,
														"from_id"=>$from_id,
														"to_id"=>$to_id,
														"from_username"=>$from_username,
														"destination_type"=>$destination_type
													)
											)						
										);	
							}
						}
					}			
					break;	
							
				case 'read':
					$from_id = $data->from_id;
					$to_id= $data->to_id;
					$destination = $data->destination_type;

					$destination_type = "destination_user";
					$query = "";
					if($destination == "user"){
						$query = "INSERT INTO Messages_read
									(
										userID,
										messageID,
										date_read
									)								
									SELECT 
										'$from_id', messageID, NOW()
									FROM
										Messages M
									JOIN
										Friends F
									ON
										M.destination_user = F.userID
									WHERE
										F.userID = '$from_id'
										AND
										F.friendID = '$to_id'
										AND
										NOT EXISTS (
											SELECT
												userID,
												messageID
											FROM 
												Messages_read
											WHERE
												userID = $from_id
												AND
												messageID = M.messageID
										);";
					}
					else{
						$query = "INSERT INTO Messages_read
									(
										userID,
										messageID,
										date_read
									)
									SELECT 
										'$from_id', messageID, NOW()
									FROM
										Messages M
									JOIN
										Groups G
									ON
										M.destination_group = G.groupID
									WHERE
										groupID = '$to_id'
										AND
										M.source_user != '$from_id'
										AND
										NOT EXISTS (
											SELECT
												userID,
												messageID
											FROM 
												Messages_read
											WHERE
												userID = $from_id
												AND
												messageID = M.messageID
										);";
					}
					
					$this->db->query($query);
					if($destination == "user"){
						if(in_array($to_id, $this->users_ids)){
							$this->variableConn(
										array_search(
											$to_id,
											$this->users_ids
											)
										)->send(
											json_encode(
												array(
													"type"=>$type,
													"from_id"=>$from_id,
													"to_id"=>$to_id,
													"destination_type"=>$destination
												)
										)								
									);	
						}	
					}
					else{						
						$query="SELECT userID FROM Groups_users WHERE groupID='$to_id';";
						$result=$this->db->query($query)->fetchAll();
						foreach($result as $row){
							$userID=$row['userID'];							
							if(in_array($to_id, $this->users_ids)){
								$this->variableConn(
											array_search(
												$to_id,
												$this->users_ids
												)
											)->send(
												json_encode(
													array(
														"type"=>$type,
														"from_id"=>$from_id,
														"to_id"=>$to_id,
														"destination_type"=>$destination_type
													)
											)								
										);	
							}	
						}
					}
					break;
			}
		}

		public function onError(ConnectionInterface $conn, \Exception $e) {			
			$userID=$this->users_ids[$conn->resourceId];
			echo $e;
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