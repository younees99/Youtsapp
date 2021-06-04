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
	    <link rel="stylesheet" type="text/css" href="stilehome.css?version=1234">
	    <link rel="stylesheet" type="text/css" href="stile.css?version=548">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	</head>
	<body>		
		<div id="loading_div" style='display:block;' class='box'>
			<i class="fa fa-circle-o-notch fa-spin fa-3x" aria-hidden="true"></i>
			<p style="font-size: 28px;">Connection...</p>
		</div>
		<table id='main_table' style='display: none;'>
			<tr>
				<td id='left_main_td'>
					<div id='side_menu'>
						<header class='header_chats'>
							<a href='home.php' style='color:white; text-decoration:none;'>
								<p class='youtsapp'>
									Youtsapp
								</p>
							</a>
							<button class='iconbtn' onclick='openProfileMenu()'>
								<i class="fa fa-user-circle-o fa-2x" aria-hidden="true" style="float:right"></i>
							</button>
						</header>
						<table id='chats'>
								<?php
									$query="SELECT	friendID AS chatID,
												 	FU.nickname AS chatName,
													FU.image_url AS chatImage,
													mess_text,
													date_time,
													'user' AS chat_type
												FROM friends F 
													JOIN users U
														ON F.userID=U.userID
													LEFT JOIN users FU
														ON FU.userID=friendID
													LEFT JOIN messages M
														ON F.last_message=M.messageID
												WHERE U.userID='$_SESSION[name]'
											UNION
											SELECT	G.groupID AS chatID,
													group_name AS chatName,
													G.image_url AS chatImage,
													mess_text,
													date_time,
													'group' AS chat_type
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
										foreach($result as $row){		
											if($row['chat_type']=='user')
												$chat_type='userID';
											else
												$chat_type='groupID';										
											$chatID=$row['chatID'];
											$chatName=$row['chatName'];
											$chatImage="src/profile_pictures/".$row['chatImage'];	
											$mess_text='Start a conversation';
											if(isset($row['mess_text']))
												$mess_text=$row['mess_text'];
											$date_time='';
											if(isset($row['date_time'])){
												$date_time=$row['date_time'];
												$date_time=date('H:i', strtotime($date_time));
											}
											$chat_ID=$chat_type."ID";
											echo "<tr><td>
													<a class='select_chat'
													href='$_SERVER[PHP_SELF]?$chat_type=$chatID'>
														<div class='select_chat'>
															<div class='propic_from_list' id='propic_from_list$chatID'
																style='background-image:url(".
																	$chatImage.");'>
															</div> 
															<p class='chat_name'>$chatName</p>
															<span class='mess_preview'>$mess_text</span>
															<span class='time_preview'>$date_time</span>
														</div>
													</a>											
												</td></tr>";
											
										}										
										
								?>
						</table>												
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
				<td id='right_main_td'>
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

								echo"<header id='header_chat'>";
								$chatID='';								
								if(isset($_GET['userID'])){									
									$query="SELECT 
												nickname,image_url,is_writing,is_online
											FROM 
												users U
												JOIN 
													friends F
														ON
															U.userID=F.userID
											WHERE 
												friendID='$_SESSION[name]'
											AND
												U.userID='$_GET[userID]';";
									$result=$db->query($query)->fetchAll();
									foreach($result as $row) {
										$nickname=$row['nickname'];
										$is_writing=$row['is_writing'];
										$image_url="src/profile_pictures/".$row['image_url'];
										echo'<button onclick="back()" id="backButton">
											<i class="fa fa-chevron-left fa-2x" aria-hidden="true" style="color: white"></i>
											</button>';
										echo"<div id='propic' style='background-image:url(".$image_url.");'></div>
										<p id='nickname'>$nickname</p>
										<p id='log'>";
										if($is_writing)
											echo"Is typing...";
										else
											echo printLastSeen($db);
										echo"</p>";
									}	
									$chatID='userID';				
								}

								elseif(isset($_GET['groupID'])){									
									$query="SELECT 
												group_name,image_url
											FROM 
												groups G
												JOIN 
													groups_users GU
														ON
															GU.groupID=G.groupID
											WHERE 
												GU.userID='$_SESSION[name]'
											AND
												G.groupID='$_GET[groupID]';";
									$result=$db->query($query)->fetchAll();
									foreach($result as $row) {
										$group_name=$row['group_name'];
										//$is_writing=$row['is_writing'];
										$image_url="src/profile_pictures/".$row['image_url'];
										echo'<button onclick="back()" id="backButton">
											<i class="fa fa-chevron-left fa-2x" aria-hidden="true" style="color: white"></i>
											</button>';
										echo"<div id='propic' style='background-image:url(".$image_url.");'></div>
										<p id='nickname'>$group_name</p>
										<p id='log'>";
										/*if($is_writing)
											echo"Is typing...";*/
										echo"</p>";
									}				
									$chatID='groupID';				
								}

								echo"</header>";
								echo"<div id='output' class='output'>
										<table id='messages' width='100%'>";
									if($chatID=='userID'){
										$query="SELECT * 
													FROM messages M
														JOIN
															users U 
															ON
																U.userID=M.source_user
													WHERE 
														(source_user='$_SESSION[name]' OR destination_user='$_SESSION[name]') 
														AND 
														(source_user='$_GET[userID]' OR destination_user='$_GET[userID]') 
												ORDER BY date_time;";
									}
									elseif($chatID=='groupID'){
										$query="SELECT * 
													FROM messages M
														JOIN
															users U 
															ON
																U.userID=M.source_user
													WHERE 
														destination_group='$_GET[groupID]'
												ORDER BY date_time;";
									}
									if($chatID!=''){
										$result=$db->query($query)->fetchAll();
										$month_day='';
										if(count($result)){
											foreach($result as $row){
												$data=strtotime($row['date_time']);
												$ore_min=date("H:m",$data);
												if($month_day!=date("F d",$data)){
													setlocale(LC_TIME,'ita');
													$month_day=date("F d",$data);
													echo"<tr><td align='center'><p class='print_date'>$month_day</p></td></tr>";
												}
												if($row['source_user']==$_SESSION['name']){
													echo "
													<tr><td>
														<div class='right'>".
																"<p class='message_value'>".$row['mess_text']."</p> 
																<span class='time-right'>".$ore_min."</span>";
																//echo"<i class='fa fa-check-circle-o' aria-hidden='true'></i>";
														echo"</div>
													</td></tr>";
												}											
												else{
													echo "<tr><td>";
													if($chatID=='groupID'){
														$userID=$row['userID'];
														$image_url="src/profile_pictures/".$row['image_url'];
														echo"
														<div class='propic_from_chat' id='propic_from_chat$userID'
															style='background-image:url(".
																$image_url.");'>
														</div> ";
													}	
													echo"<div class='left'>".
															"<p class='message_value'>".$row['mess_text']."</p> 
															<span class='time-left'>".$ore_min."</span>
													</div>
														</td></tr>";
												}
																						
											}
										}					
										else
											echo"<p class='print_text'>There is no message yet!<br>Start a coversation!<p>";
										echo"</table></div>";
									}									
									else
										echo"<p class='print_text'>Select a chat to start a conversation!<p>";
									echo"</table></div>";
							if(isset($_GET['userID'])||isset($_GET['groupID'])){
									echo "<footer class='send_form' id='footer_form' style='display:none;'>									
											<textarea name='msg' placeholder='Scrivi un messaggio...' id='input_message'></textarea>
											<button id='send_emoji' class='footer_btn'><i class='fa fa-smile-o fa-2x'></i></button>
											<button id='send_attachment' class='footer_btn'><i class='fa fa-paperclip fa-2x'></i></button>
											<button id='send_message' class='footer_btn'><i class='fa fa-send fa-2x'></i></button>
										</footer>";
								}				
							?>
					</div>
				</td>
			</tr>
		</table>
            <div class='overlay' id='overlay'>
                <button onclick='chiudi()' style='background-color: Transparent; border:none; float: right'>
                    <i class="fa fa-times fa-2x" aria-hidden="true" style='color: white;'></i>
					<form>
					</form>
                </button>      
            </div>  

		<script>	
			function showOnlineUsers() {
					var users_online='<?php
											$users_online='';
											$result=$db->query("SELECT U.userID 
																	FROM users U  
																		JOIN friends F
																			ON U.userID=F.userID
																	WHERE is_online='1';")->fetchAll();
											foreach ($result as $row) {
												$users_online.=$row['userID'].",";
											}
											$users_online=substr($users_online,0,-1);
											echo $users_online;
										?>';
					var array_users=users_online.split(",");
					if(array_users){
						for (let index = 0; index < array_users.length; index++) {
							printLog(array_users[index],"online");
							if(array_users[index]==<?php
														if(isset($_GET['userID']))
															echo $_GET['userID'];
														else
															echo"-1";
													?>)
								printLastSeen(array_users[index],"online");
							}	
					}
			}

			function printLog(id,val_log) {
				var val_propic;
				var val_propic_from_list;
				var val_propic_from_chat;
				var propic=document.getElementById("propic");
				var propic_from_list=document.getElementById("propic_from_list"+id);
				var propic_from_chat=document.getElementById("propic_from_chat"+id);
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
				if(propic_from_chat){
					if(val_log=="online")
						val_propic_from_chat="#00ff33";
					else
						val_propic_from_chat="black";	
					propic_from_list.style.border="solid 2.5px"+val_propic_from_chat;	
				}

			}			

			function printLastSeen(id,val_log){	
				var log=document.getElementById("log");
				if(log){
					if(id==<?php
									if(isset($_GET['userID']))
										echo $_GET['userID'];
									else
										echo"-1";
								?>){
					if(val_log=="online")			
						log.innerHTML="Online";
					else
						log.innerHTML="<?php
											echo printLastSeen($db);
										?>";		
					}	
				}				
			}			
			
			function updateScroll(){
				var messages=document.getElementById("messages");
				var output=document.getElementById("output");
				if(messages&&output){
					output.scrollTop = messages.offsetHeight;
				}
			}

			function printMessage(json){
				var message;
				var source;
				var icon='';

				if(json.from_id==<?php echo"'$_SESSION[name]'"?>){
					source="right";
					//icon="<i class='fa fa-circle-o' aria-hidden='true'></i>";
				}				
				else
					source="left";
				
				message="<tr><td>";
				if(json.from_id!=<?php echo "'$_SESSION[name]'";?>){
					var url="src/profile_pictures/"+json.image_url;
					message+="<div class='propic_from_chat' id='propic_from_chat$userID'";
					message+="style='background-image:url("+url+");'></div>";
				}
				message+="<div class='"+source+"'>";
				message+="<p class='message_value'>"+json.msg+"</p>";
				message+="<span class='time-"+source+"'>"+json.time+" </span>";
				message+=icon+"</div></td></tr>";
				document.getElementById("messages").innerHTML+=message;
			}

			function printPreview(json){
				var message;
				var source_type
				var source;
							
			}
			
			function sendMessage(){
				var chat_msg = textarea.value;
				if(chat_msg.length>1){
					websocket_server.send(
						JSON.stringify({
							'type':'chat',
							'from_id':<?php 
										echo "'$_SESSION[name]'"; 
									?>,
							'to_id':<?php 
										if(isset($_GET['userID'])) 
											echo"'$_GET[userID]'";
										elseif(isset($_GET['groupID']))
											echo"'$_GET[groupID]'";
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
					console.log(chat_msg);
					textarea.value='';
					textarea.blur();
					updateScroll();
				}
			}
	

			// Websocket			
			var websocket_server = new WebSocket("ws://<?php echo $_SERVER['SERVER_NAME'];?>:8080/");

			websocket_server.onopen = function(e){
				document.getElementById("loading_div").style='display: none;';	
				document.getElementById("main_table").style='display: block;';	
				document.getElementById("footer_form").style='display: block;';	
				websocket_server.send(
					JSON.stringify({
						'type':'socket',
						'user_id':<?php echo "'$_SESSION[name]'"; ?>
					})
				);
				showOnlineUsers();
				updateScroll();	
			};
				
			websocket_server.onerror = function(e) {
				window.location="error.php?error=conn";
			}

			//Printing a message when i recieve it
			websocket_server.onmessage = function(e){
				var json = JSON.parse(e.data);
				switch(json.type){
					case 'chat':
						printMessage(json);
						console.log(json);
						//printPreview(json);
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
							log.innerHTML='Is typing...';
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

			var textarea = document.getElementById("input_message");		
			var button=document.getElementById("send_message");

			//Events sending a message
			if(textarea){
				textarea.addEventListener('keyup',function(e){
					if(e.keyCode==13 && !e.shiftKey){
						sendMessage();
						updateScroll();
					}
				});	
			}	

			if(button){
				button.addEventListener('click',function(e){
					sendMessage();
					button.blur();
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
		<?php
			$db->close();
		?>
	</body>
</html>
