<?php
	session_start();
	if(isset($_COOKIE['userID'])){
		$_SESSION['name']=$_COOKIE['userID'];
	}
	else if(!isset($_SESSION['name']))
		header("Location: index.php");
	else{
		$cookie_name = "userID";
		$cookie_value = $_SESSION['name'];
		setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 giorno	
	}
	include 'db/config.php';
	date_default_timezone_set('Europe/Rome');
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Home</title>
	    <link rel="stylesheet" type="text/css" href="stilehome.css?version=37">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	</head>
	<body>		
		<table class='tabella_principale'>
			<tr>
				<td style="width:30%;">
					<div id='menu_laterale'>
						<header style="display:inline-block">
							<p class='youtsapp'>Youtsapp</p>
						</header>
						<div style="height: 88%;">
							<table id='chats'>
									<?php
										$query="SELECT userID,nickname,url_immagine FROM utente;";
										$result=$conn_database->query($query);
										$conta=0;					
										if($result->num_rows>0){
											while($row=$result->fetch_assoc()){							
												if($row['userID']!=$_SESSION['name']){
													$nickname=$row['nickname'];
													$url_immagine="src/immagini_profilo/".$row['url_immagine'];	
													echo "<tr><td>
															<a class='seleziona_chat'
															href='$_SERVER[PHP_SELF]?userID=$row[userID]'>
															<div class='seleziona_chat' id='utente]'>
																<div id='propic_elenco' 
																	style='background-image:url(".
																			$url_immagine.");'>
																</div> ".
																$nickname.
															"</div>".
															"</a>											
														</td></tr>";
													$conta--;
												}
												else
													$conta++;
											}
										}
										if($conta>0){
											echo"<tr><td>Non ci sono altri membri</td></tr>";
										}
									?>
							</table>
						</div>
						
						<footer>
							<a href="logout.php" class='esci'>
								<i class="fa fa-sign-out" aria-hidden="true"></i>
							</a>	
						</footer>
							
					</div>
				</td>
				<td align="center">
					<div id='main'>					
							<?php
								if(isset($_GET['userID'])){
									echo"<header id='header_chat'>";									
									$query="SELECT nickname,url_immagine,ultimo_accesso FROM utente WHERE userID='$_GET[userID]';";
									$result=$conn_database->query($query);
									while ($row=$result->fetch_assoc()) {
										$nickname=$row['nickname'];
										$url_immagine="src/immagini_profilo/".$row['url_immagine'];
										echo"<div id='propic' style='background-image:url(".$url_immagine.");'></div>
										<p id='nickname'>$nickname</p>
										<p id='stato'>Online</p>";
									}
										

									echo"</header>
									<div class='output'>
										<table id='messaggi' width='100%'>";
									$query="SELECT * FROM messaggio WHERE (sorgente='$_SESSION[name]' OR destinazione='$_SESSION[name]') AND (sorgente='$_GET[userID]' OR destinazione='$_GET[userID]') ORDER BY data;";
									$result=$conn_database->query($query);
									$mese_giorno='';
									if($result->num_rows>0){
										while($row=$result->fetch_assoc()){
											$data=strtotime($row['data']);
											$ore_min=date("H:m",$data);
											if($mese_giorno!=date("F d",$data)){
												setlocale(LC_TIME,'ita');
												$mese_giorno=date("F d",$data);
												echo"<tr><td align='center'><p class='print_text'>$mese_giorno</p></td></tr>";
											}
											if($row['sorgente']==$_SESSION['name']){
												echo "<tr><td>
												<div class='right'>".
														"<p class='testo_messaggio'>".$row['testo']."</p> 
														<span class='time-right'>".$ore_min."</span>
														<i class='fa fa-check-circle-o' aria-hidden='true'></i>
												</div>
													</td></tr>";
											}											
											else{
												echo "<tr><td>
												<div class='left'>".
														"<p class='testo_messaggio'>".$row['testo']."</p> 
														<span class='time-left'>".$ore_min."</span>
												</div>
													</td></tr>";
											}
																					
										}
									}
									else
										echo"<p class='print_text'>Nessun messaggio! Inizia una coversazione!<p>";
									echo"</table></div>";
									$conn_database->close();		
								}		

							?>						
							
							<?php
								if(isset($_GET['userID'])){
									$destinazione=$_GET['userID'];
									echo "<footer class='form_invio'>									
											<textarea name='msg' placeholder='Scrivi un messaggio...' id='inputmessaggio'></textarea>
											<button id='invia_messaggio' class='invia'><i class='fa fa-send'></i></button>
										</footer>";
								}						
							?>
					</div>
				</td>
		</tr>
		</table>
		<script>
			// Websocket
			function updateScroll(){
				if(document.getElementById("output")){
					var output=document.getElementById("output");
					output.scrollTop = output.scrollHeight;
				}
			}

			updateScroll();
			var websocket_server = new WebSocket("ws://192.168.1.251:8080/");
			
			websocket_server.onopen = function(e) {
				websocket_server.send(
					JSON.stringify({
						'type':'socket',
						'user_id':<?php echo "'$_SESSION[name]'"; ?>
					})
				);
			};
				
			websocket_server.onerror = function(e) {
				//window.location="error.php?errore=conn";
			}

			//Printing a message when i recieve it
			websocket_server.onmessage = function(e)
			{
				var messaggi= document.getElementById("messaggi");
				var stato = document.getElementById("stato");
				var json = JSON.parse(e.data);
				switch(json.type){

					case 'utenti_online':
						var id_online=JSON.parse(json.utenti);
						console.log(id_online);
						break;
					
					/*case'nuovo_utente':
						var div_utente=JSON.parse(json.lista);
						var stampa="";
						stampa+=lista.forEach(stampaUtente);
						users.innerHTML+=stampa; 
						break;*/
					case 'chat':
						messaggi.innerHTML+=stampaMessaggio(json);
						break;
					
					case 'connected':
						var id=json.user_id;						
						document.getElementById("propic").style.border="solid 2.5px #00ff33";
						document.getElementById("propic_elenco").style.border="solid 2.5px #00ff33";
						break;
					
					case 'disconnected':
						var id=json.user_id;						
						document.getElementById("propic").style.border="solid 2.5px #191919";
						document.getElementById("propic_elenco").style.border="solid 2.5px #333333";
						break;
					
					case 'writing':
						stato.innerHTML='Sta scrivendo...';
						break;
					
					case 'not_writing':
						stato.innerHTML='Online';
						break;
				}
			}
			//Funzione che stampa il messaggio ricevuto come json
			function stampaMessaggio(json){
				var messaggio;
				var sorgente;
				var icona='';
				if(json.from_id==<?php echo"'$_SESSION[name]'"?>){
					sorgente="right";
					icona="<i class='fa fa-circle-o' aria-hidden='true'></i>";
				}
				
				else
					sorgente="left";
				
				messaggio="<tr><td><div class='"+sorgente+"'><p class='testo_messaggio'>"+json.msg+"</p><span class='time-"+sorgente+"'>"+json.time+"</span>"+icona+"</div></td></tr>";
				return messaggio;
			}

			//Funzione che formatta il messaggio per json
			var textarea = document.getElementById("inputmessaggio");
			function inviaMessaggio(){
				var chat_msg = textarea.value;
				websocket_server.send(
					JSON.stringify({
						'type':'chat',
						'from_id':<?php echo "'$_SESSION[name]'"; ?>,
						'to_id':<?php 
									if(isset($_GET['userID'])) 
										echo"'$_GET[userID]'";
									else
										echo"'-1'"?>,
						'chat_msg':chat_msg.trim(),
						'time': <?php $time=date('H:i');
									  echo"'$time'"?>
					})
				);
				textarea.value='';
				textarea.blur();
				updateScroll();
			}
			
			// Evento che invia un messaggio
			if(textarea){
				textarea.addEventListener('keyup',function(e){
					if(e.keyCode==13 && !e.shiftKey){
						inviaMessaggio();
						updateScroll();
					}
				});	
			 }
			

			var pulsante=document.getElementById("invia_messaggio");
			if(pulsante){
				pulsante.addEventListener('click',function(e){
					inviaMessaggio();
				});	
			}
			


			// Events writing a message
			if(textarea){
				textarea.addEventListener('focus',function(e){
					websocket_server.send(
						JSON.stringify({
							'type':'writing',
							'from_id':<?php echo "'$_SESSION[name]'"; ?>,
							'to_id':<?php 
										if(isset($_GET['userID'])) 
											echo"'$_GET[userID]'";
										else
											echo"'-1'"?>,
						})
					);					
				});
			}

			// Events stop writing a message
			var textarea = document.getElementById("inputmessaggio");
			if(textarea){
				textarea.addEventListener('blur',function(e){
					websocket_server.send(
						JSON.stringify({
							'type':'not_writing',
							'from_id':<?php echo "'$_SESSION[name]'"; ?>,
							'to_id':<?php 
										if(isset($_GET['userID'])) 
											echo"'$_GET[userID]'";
										else
											echo"'-1'"?>,
						})
					);					
				});
			}
		</script>
	</body>
</html>
