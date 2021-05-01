<?php 
	set_time_limit(0);
	date_default_timezone_set('Europe/Rome');
	use Ratchet\MessageComponentInterface;
	use Ratchet\ConnectionInterface;
	use Ratchet\Server\IoServer;
	use Ratchet\Http\HttpServer;
	use Ratchet\WebSocket\WsServer;
	require_once '../vendor/autoload.php';
	include '../db/db.php';

	class Chat implements MessageComponentInterface {
		private $db;
		protected $clients;
		protected $users_ids = array();
		private $color;

		public function __construct() {
			$this->clients = new \SplObjectStorage;
			$this->db=new db("localhost","root","root","youtsapp");
		}

		//Un utente apre la connessione col socket
		public function onOpen(ConnectionInterface $conn) {
			$this->clients->attach($conn);		
		}

		//Un utente chiude la connessione col socket
		public function onClose(ConnectionInterface $conn) {
			// Output
			$userID=$this->users_ids[$conn->resourceId];
			$risultato=$this->db->query("SELECT user FROM utente WHERE userID='$userID';")->fetchArray();
			echo"$risultato[user] si è disconnesso\n";
			$timestamp = date('Y-m-d H:i:s', strtotime("now"));
			$this->db->query("UPDATE utente SET ultimo_accesso='$timestamp' WHERE userID='$userID';");

			//Invio agli altri utenti che si è disconnesso
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

		public function variabileConn($index){
			$conta=0;
			foreach ($this->clients as $client) {
				if($index==$client->resourceId){
					return $client;
					break;
				}
			}
		}

		public function stampaUtenti($utenti_online,$user_id){
			$stampa="";
			foreach($utenti_online as $utente){
				if($utente!=$user_id)
					$stampa.=$utente.",";
			}
			$stampa=substr($stampa,0,-1);
			return $stampa;
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
					$json_message=json_encode(
										array(
											"type"=>$type,
											"msg"=>$chat_msg,
											"from_id"=>$from_id,
											"to_id"=>$to_id,
											"time"=>$time
										)
									);
					if(in_array($to_id, $this->users_ids)){
						$this->variabileConn(
									array_search(
										$to_id,
										$this->users_ids
										)
									)->send(
										$json_message						
								);	
					}

					$from->send($json_message);		
					$query="INSERT INTO messaggio(testo,sorgente,destinazione) VALUES ('$chat_msg','$from_id','$to_id');";
					$this->db->query($query);
					$risultato=$this->db->query("SELECT user FROM utente WHERE userID='$from_id';")->fetchArray();
					$from=$risultato['user'];
					$risultato=$this->db->query("SELECT user FROM utente WHERE userID='$to_id';")->fetchArray();
					$to=$risultato['user'];
					echo"$from($from_id) ha inviato un messaggio a $to($to_id)\n";
					break;

				case 'socket':
					$user_id = $data->user_id;
					$risultato=$this->db->query("SELECT user FROM utente WHERE userID='$user_id';")->fetchArray();
					$this->users_ids[$from->resourceId]=$user_id;
					echo"$risultato[user]($user_id) si è connesso\n";
					$from->send(
								json_encode(
									array(
										"type"=>"utenti_online",
										"utenti_online"=>$this->stampaUtenti($this->users_ids,$user_id)
									)
								)
							);	
					
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
						$this->variabileConn(array_search($to_id,$this->users_ids))->send(
									json_encode(
										array(
											"type"=>$type,
											"from_id"=>$from_id,
											"to_id"=>$to_id,
										)
									)
								);	
					}	

					$risultato=$this->db->query(
						"SELECT user FROM utente WHERE userID='$from_id';"
						)->fetchArray();
					$from=$risultato['user'];
					$risultato=$this->db->query(
						"SELECT user FROM utente WHERE userID='$to_id';"
						)->fetchArray();
					$to=$risultato['user'];
					echo"$from($from_id) sta scrivendo a $to($to_id)\n";
					break;

				case 'not_writing':
					$from_id = $data->from_id;
					$to_id= $data->to_id;
					
					if(in_array($to_id, $this->users_ids)){
						$this->variabileConn(array_search($to_id,$this->users_ids))->send(
									json_encode(
										array(
											"type"=>$type,
											"from_id"=>$from_id,
											"to_id"=>$to_id,
										)
									)
								);	
					}	

					$risultato=$this->db->query(
						"SELECT user FROM utente WHERE userID='$from_id';"
						)->fetchArray();
					$from=$risultato['user'];

					$risultato=$this->db->query(
						"SELECT user FROM utente WHERE userID='$to_id';"
						)->fetchArray();
					$to=$risultato['user'];

					echo"$from($from_id) non sta più scrivendo a $to($to_id)\n";
					break;
							
			}
		}

		public function onError(ConnectionInterface $conn, \Exception $e) {
			$conn->close();
			$utente="Client";
			if(isset($this->users_ids[$this->indexConn($conn)]))
				$utente=$users_ids[$conn->resourceId];
			echo " disconnesso causa errore\n";
		}
	}

	$server = IoServer::factory(
		new HttpServer(new WsServer(new Chat())),
		8080
	);

	echo"Server in esecuzione!\n";
	$server->run();
	$db->close;
?>