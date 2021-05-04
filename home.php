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
		setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day	
	}
	include 'db/config.php';
	date_default_timezone_set('Europe/Rome');
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Home</title>
	    <link rel="stylesheet" type="text/css" href="stilehome.css?version=125">
	    <link rel="stylesheet" type="text/css" href="stile.css?version=548">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	</head>
	<body>		
		<div id="loading_div" class='box'>
			<i class="fa fa-circle-o-notch fa-spin fa-3x" aria-hidden="true"></i>
			<p style="font-size: 28px;">Connection...</p>
		</div>
		<table id='main_table'>
			<tr>
				<td style="width:20%;">
					<div id='side_menu'>
						<header class='header_chats'>
							<p class='youtsapp'>Youtsapp</p>
							<button class='iconbtn' onclick='openProfileMenu()'>
								<i class="fa fa-user-circle-o fa-2x" aria-hidden="true" style="float:right"></i>
							</button>
						</header>
						<div class='side_div'>
							<table id='chats'>
									<?php
										$query="SELECT friendID,FU.nickname,FU.image_url,mess_text,date_time
													FROM friends F 
														JOIN users U
															ON F.userID=U.userID
														LEFT JOIN users FU
															ON FU.userID=friendID
														LEFT JOIN messages M
															ON F.last_message=M.messageID
													WHERE U.userID='$_SESSION[name]'
												UNION
												SELECT G.groupID,group_name,G.image_url,mess_text,date_time
													FROM groups_users GU
														JOIN users U
															ON GU.userID=U.userID
														LEFT JOIN groups G
															ON GU.groupID=G.groupID 
														LEFT JOIN messages M
															ON G.last_message=M.messageID
													WHERE U.userID='$_SESSION[name]'
												ORDER BY date_time DESC;";
										$result=$db->query($query)->fetchAll();
										if(count($result)>0){
											foreach($result as $row){											
												if(array_key_exists('friendID',$row)){
													$chat_type='user';
													$chat_name=$row['nickname'];
													$chat_ID_value=$row['friendID'];
												}	
												else if (array_key_exists('groupID',$row)){
													$chat_type='group';
													$chat_name=$row['group_name'];	
													$chat_ID_value=$row['groupID'];											
												}
												$image_url="src/profile_pictures/".$row['image_url'];	
												$mess_text=$row['mess_text'];
												$date_time=$row['date_time'];
												$chat_ID=$chat_type."ID";
												echo "<tr><td>
														<a class='select_chat'
														href='$_SERVER[PHP_SELF]?$chat_ID=$chat_ID_value'>
														<div class='select_chat'>
															<div class='propic_from_list' id='propic_from_list$chat_ID_value'
																style='background-image:url(".
																	$image_url.");'>
															</div> 
															<p class='chat_name'>$chat_name</p>
															<span class='mess_preview'>$mess_text</span>
															<span class='time-left'>$date_time</span>
														</div>".
														"</a>											
													</td></tr>";	
												}										
											}
										else{
											echo"<tr>
													<td>
														<p>You don't have any friend yet</p>
													</td>
												</tr>";
										}
									?>
							</table>
						</div>						
						<footer class='footer_chats'>
							<a href="logout.php" class='iconlink'>
								<i class="fa fa-sign-out fa-2x" aria-hidden="true" ></i>
							</a>
							<button class='iconbtn' onclick='function()'>
								<i class="fa fa-plus fa-2x" aria-hidden="true"></i>
							</button>
							<button class='iconbtn' onclick='function()'>
								<i class="fa fa-users fa-2x" aria-hidden="true"></i>
							</button>
						</footer>
							
					</div>
				</td>
				<td align="center">
					<div id='main'>					
							<?php
								function printLastSeen($db){	
									if(isset($_GET['userID'])){
										$query="SELECT last_seen FROM users WHERE userID='$_GET[userID]';";
										$result=$db->query($query);
										$row=$result->fetchArray();
										$last_seen=$row['last_seen'];
										$last_seen=date('M j Y g:i A', strtotime($last_seen));
										$last_seen="Last seen: ".$last_seen;
										return $last_seen;
									}	
								}

								if(isset($_GET['userID'])){
									echo"<header id='header_chat'>";									
									$query="SELECT nickname,image_url FROM users WHERE userID='$_GET[userID]';";
									$result=$db->query($query)->fetchAll();
									foreach($result as $row) {
										$nickname=$row['nickname'];
										$image_url="src/profile_pictures/".$row['image_url'];
										echo"<div id='propic' style='background-image:url(".$image_url.");'></div>
										<p id='nickname'>$nickname</p>
										<p id='log'>".printLastSeen($db)."</p>";
									}									

									echo"</header>
									<div class='output' id='output'>
										<table id='messages' width='100%'>";
									$query="SELECT * 
												FROM messages 
												WHERE 
													(source='$_SESSION[name]' OR destination_user='$_SESSION[name]') 
													AND 
													(source='$_GET[userID]' OR destination_user='$_GET[userID]') 
											ORDER BY date_time;";
									$result=$db->query($query)->fetchAll();
									$mese_giorno='';
									if(count($result)){
										foreach($result as $row){
											$data=strtotime($row['date_time']);
											$ore_min=date("H:m",$data);
											if($mese_giorno!=date("F d",$data)){
												setlocale(LC_TIME,'ita');
												$mese_giorno=date("F d",$data);
												echo"<tr><td align='center'><p class='print_text'>$mese_giorno</p></td></tr>";
											}
											if($row['source']==$_SESSION['name']){
												echo "<tr><td>
												<div class='right'>".
														"<p class='message_value'>".$row['mess_text']."</p> 
														<span class='time-right'>".$ore_min."</span>
														<i class='fa fa-check-circle-o' aria-hidden='true'></i>
												</div>
													</td></tr>";
											}											
											else{
												echo "<tr><td>
												<div class='left'>".
														"<p class='message_value'>".$row['mess_text']."</p> 
														<span class='time-left'>".$ore_min."</span>
												</div>
													</td></tr>";
											}
																					
										}
									}
									else
										echo"<p class='print_text'>No messages! Start a conversation<p>";
									echo"</table></div>";	
								}	

							?>						
							
							<?php
								if(isset($_GET['userID'])){
									$destination=$_GET['userID'];
									echo "<footer class='send_form'>									
											<textarea name='msg' placeholder='Scrivi un messaggio...' id='input_message'></textarea>
											<button id='send_message' class='send'><i class='fa fa-send'></i></button>
										</footer>";
								}				
							?>
					</div>
				</td>
			</tr>
		</table>
		<script>	
			showOnlineUsers();		

			// Websocket				
			var websocket_server = new WebSocket("ws://<?php echo $_SERVER['SERVER_NAME'];?>:8080/");
			websocket_server.onopen = function(e){
				websocket_server.send(
					JSON.stringify({
						'type':'socket',
						'user_id':<?php echo "'$_SESSION[name]'"; ?>
					})
				);
			};
				
			websocket_server.onerror = function(e) {
				window.location="error.php?error=conn";
			}

			function printLastSeen(id,val_log){	
				var log=document.getElementById("log");
				if(val_log=="online")			
					log.innerHTML="Online";
				else
					log.innerHTML="<?php
										echo printLastSeen($db);
									?>";
			}

			function printLog(id,val_log) {
				var val_propic
				var val_propic_from_list;
				var propic=document.getElementById("propic");
				var propic_from_list=document.getElementById("propic_from_list"+id);
				if(propic){
					if(val_log=="online")
						val_propic="#00ff33";					
					else
						val_propic="#191919";					
					if(id==<?php
								if(isset($_GET['userID']))
									echo $_GET['userID'];
								else
									echo"-1";
							?>)					
						propic.style.border="solid 2.5px"+val_propic;						
				}
				if(propic_from_list){
					if(val_log=="online")
						val_propic_from_list="#00ff33";
					else
						val_propic_from_list="#333333";	
					propic_from_list.style.border="solid 2.5px"+val_propic_from_list;	
				}

			}
			
			function updateScroll(){
				var messages=document.getElementById("messages");
				var output=document.getElementById("output");
				if(messages&&output){
					output.scrollTop = messages.offsetHeight;
				}
			}
			updateScroll();


			//Printing a message when i recieve it
			websocket_server.onmessage = function(e)
			{
				var messages= document.getElementById("messages");
				var log = document.getElementById("log");
				var json = JSON.parse(e.data);
				switch(json.type){
					case 'chat':
						messages.innerHTML+=printMessage(json);
						updateScroll();
						break;
					
					case 'connected':
						var id=json.user_id;	
						printLog(id,"online");
						printLastSeen(id,"online");
						break;
					
					case 'disconnected':
						var id=json.user_id;
						printLog(id,"offline");
						printLastSeen(id,"offline");
						break;
					
					case 'writing':
						if(json.from_id==<?php
												if(isset($_GET["userID"])) 
													echo"'$_GET[userID]'";
												else
													echo"-1";
										?>)
							log.innerHTML='Sta scrivendo...';
						break;
					
					case 'not_writing':
						if(json.from_id==<?php
												if(isset($_GET["userID"])) 
													echo"'$_GET[userID]'";
												else
													echo"-1";
										?>)
							log.innerHTML='Online';
						break;
				}
			}
			
			function printMessage(json){
				var message;
				var source_type
				var source;
				var icon='';
				if(json.from_id==<?php echo"'$_SESSION[name]'"?>){
					source="right";
					icon="<i class='fa fa-circle-o' aria-hidden='true'></i>";
				}
				
				else
					source="left";
				
				message="<tr><td><div class='"+source+"'><p class='message_value'>"+json.msg+"</p><span class='time-"+source+"'>"+json.time+"</span>"+icon+"</div></td></tr>";
				return message;
			}


			var textarea = document.getElementById("input_message");
			function inviaMessaggio(){
				var chat_msg = textarea.value;
				websocket_server.send(
					JSON.stringify({
						'type':'chat',
						'from_id':<?php 
									echo "'$_SESSION[name]'"; 
								?>,
						'to_id':<?php 
									if(isset($_GET['userID'])) 
										echo"'$_GET[userID]'";
									else
										echo"'-1'"
								?>,
						'chat_msg':chat_msg.trim(),
						'destination_type':<?php
											if(isset($_GET['userID']))
												echo "'destination_user'";
											elseif(isset($_GET['groupID']))
												echo "'destination_group'";
											else
												echo "'-1'";
											?>,
						'time': <?php 
									$time=date('H:i');
									 echo"'$time'"
								?>
					})
				);
				textarea.value='';
				textarea.blur();
				updateScroll();
			}
			
			
			if(textarea){
				textarea.addEventListener('keyup',function(e){
					if(e.keyCode==13 && !e.shiftKey){
						inviaMessaggio();
						updateScroll();
					}
				});	
			 }
			
			
			var pulsante=document.getElementById("send_message");
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
			var textarea = document.getElementById("input_message");
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

					
			function showOnlineUsers() {
				var array_utenti='<?php
										$users_online='';
										$result=$db->query("SELECT userID FROM users WHERE is_online='1';")->fetchAll();
										foreach ($result as $row) {
											$users_online.=$row['userID'].",";
										}
										$users_online=substr($users_online,0,-1);
										echo $users_online;
									?>';
					
				if(array_utenti!=''){
					for (let index = 0; index < array_utenti.length; index++) {
						printLog(array_utenti[index],"online");
						if(array_utenti[index]==<?php
													if(isset($_GET['userID']))
														echo $_GET['userID'];
													else
														echo"-1";
												?>)
							printLastSeen(id,"online");
						}	
				}
				else{
					printLog(array_utenti,"online");
						if(array_utenti==<?php
											if(isset($_GET['userID']))
												echo $_GET['userID'];
											else
												echo"-1";
										?>)
							printLastSeen(id,"online");
				}	
			}
		</script>
		<?php
			$db->close();
		?>
	</body>
</html>
