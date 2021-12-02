<?php
	session_start();
	if(isset($_COOKIE['userID'])){
		$_SESSION['name']=$_COOKIE['userID'];
	}
	else if(!isset($_SESSION['name']))
		header("Location: login.php");
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
	    <link rel="stylesheet" type="text/css" href="style/style.css?version=567">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> 
	</head>
	<body>		
		<div id="loading_div" style='display:block;' class='box'>
			<i class="fa fa-circle-o-notch fa-spin fa-3x" aria-hidden="true"></i>
			<p style="font-size: 28px;">Connection...</p>
		</div>
		<div id='main_div' style='display: none;'>
				<div id='left_main_div'>
					<div id='side_menu'>
						<header class='header_chats' id='header_chats'> 
							<a href='index.php' style='color:white; text-decoration:none;'>
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
												FROM Friends F 
													JOIN Users U
														ON F.userID=U.userID
													LEFT JOIN Users FU
														ON FU.userID=friendID
													LEFT JOIN Messages M
														ON F.last_message=M.messageID
												WHERE U.userID='$_SESSION[name]'
											UNION
											SELECT	G.groupID AS chatID,
													group_name AS chatName,
													G.image_url AS chatImage,
													mess_text,
													date_time,
													'group' AS chat_type
												FROM Groups_users GU
													JOIN Users U
														ON GU.userID=U.userID
													LEFT JOIN Groups G
														ON GU.groupID=G.groupID 
													LEFT JOIN Messages M
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
											if(isset($row['mess_text'])){
												$mess_text=htmlspecialchars($row['mess_text']);
											}
											$date_time='';
											if(isset($row['date_time'])){
												$date_time=$row['date_time'];
												$date_time=date('H:i', strtotime($date_time));
											}
											echo "<tr><td class='select_chat'>
													<a class='select_chat'
													href='$_SERVER[PHP_SELF]?$chat_type=$chatID'>
														<div class='select_chat'>";
														echo"<div class='propic_from_list'";
														if($chat_type=='userID')
															echo" id='propic_from_list$chatID'";
														echo"style='background-image:url(".
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
						<footer class='footer_chats' id='footer_chats'>
							<button class='iconbtn' onclick='search()'>
								<i class="fa fa-plus fa-2x" aria-hidden="true"></i>
							</button>
							<button class='iconbtn' onclick='createGroup()'>
								<i class="fa fa-users fa-2x" aria-hidden="true"></i>
							</button>
							<button class='iconbtn'>
								<i class="fa fa-moon-o fa-2x" aria-hidden="true"></i>
							</button>
						</footer>
					</div>
				</div>		
					<div class='right_main_div' id='right_main_div'>
						<div id='main'>			
							<?php
								function printLastSeen($db){	
									if(isset($_GET['userID'])){
										$query="SELECT last_seen FROM Users WHERE userID='$_GET[userID]';";
										$result=$db->query($query);
										$row=$result->fetchArray();
										$last_seen=$row['last_seen'];
										$year=date('Y', strtotime($last_seen));
										$current_year=date('Y');
										$month=date('F', strtotime($last_seen));
										$current_month=date('F');
										$day=date('l', strtotime($last_seen));
										$current_day=date('l');
										if($year!=$current_year)
											$print_last_seen=date('l j F Y', strtotime($last_seen));
										elseif ($month!=$current_month) 
											$print_last_seen=date('l j F', strtotime($last_seen));
										elseif ($day!=$current_day) 
											$print_last_seen=date('l j H:i', strtotime($last_seen));										
										else
											$print_last_seen="Today at ".date('H:i', strtotime($last_seen));										
										$print_last_seen="Last seen: ".$print_last_seen;
										return $print_last_seen;
									}									
								}

								echo"<header id='header_chat'>";
								$chatID='';								
								if(isset($_GET['userID'])){									
									$query="SELECT 
												nickname,image_url,is_typing,is_online
											FROM 
												Users U
												JOIN 
													Friends F
														ON
															U.userID=F.userID
											WHERE 
												friendID='$_SESSION[name]'
											AND
												U.userID='$_GET[userID]';";
									$result=$db->query($query)->fetchAll();	
									$conta=0;								
									foreach($result as $row) {
										$nickname=$row['nickname'];
										$is_typing=$row['is_typing'];
										$image_url="src/profile_pictures/".$row['image_url'];
										echo'<a href="index.php" id="backButton">
											<i class="fa fa-chevron-left fa-2x" aria-hidden="true" style="color: white; 
											padding-top: 10px;"></i>
											</a>';
										echo"<div id='propic' style='background-image:url(".$image_url.");'></div>
										<p id='nickname'>$nickname</p>
										<p id='log'>";
										if($is_typing=='1')
											echo"Is typing...";
										else
											echo printLastSeen($db);
										echo"</p>";
										$conta++;
									}	
									if($conta>0)
										$chatID='userID';	
									else{								
										$query="SELECT 
													nickname,image_url,is_online
												FROM 
													Users U
												WHERE
													U.userID='$_GET[userID]';";
										$result=$db->query($query)->fetchAll();	
										$conta=0;								
										foreach($result as $row) {
											$nickname=$row['nickname'];
											$image_url="src/profile_pictures/".$row['image_url'];
											echo'<a href="index.php" id="backButton">
												<i class="fa fa-chevron-left fa-2x" aria-hidden="true" style="color: white; 
												padding-top: 10px;"></i>
												</a>';
											echo"<div id='propic' style='background-image:url(".$image_url.");'></div>
											<p id='nickname'>$nickname</p>";
										}	
									}		
								}

								elseif(isset($_GET['groupID'])){									
									$query="SELECT 
												group_name,image_url
											FROM 
												Groups G
												JOIN 
													Groups_users GU
														ON
															GU.groupID=G.groupID
											WHERE 
												GU.userID='$_SESSION[name]'
											AND
												G.groupID='$_GET[groupID]';";
									$result=$db->query($query)->fetchAll();
									foreach($result as $row) {
										$group_name=$row['group_name'];
										$image_url="src/profile_pictures/".$row['image_url'];
										echo'<a href="index.php" id="backButton">
											<i class="fa fa-chevron-left fa-2x" aria-hidden="true" style="color: white;
											padding-top: 10px;"></i>
											</a>';
										echo"<div id='propic' style='background-image:url(".$image_url.");'></div>
										<p id='nickname'>$group_name</p>
										<p id='log'> Online users:
										<span id='count_online'>";
										$result=$db->query("SELECT COUNT(*) as online_users FROM Users WHERE is_online='1' AND userID!='$_SESSION[name]';")->fetchArray();
										echo $result['online_users'];										
										echo"</span>
										</p>";
									}				
									$chatID='groupID';				
								}
								echo"</header>";
								echo"<div id='output' class='output right_main_td'>
										<table id='messages' width='100%'>";
									if($chatID=='userID'){
										$query="SELECT * 
													FROM Messages M
														JOIN
															Users U 
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
													FROM Messages M
														JOIN
															Users U 
															ON
																U.userID=M.source_user
													WHERE 
														destination_group='$_GET[groupID]'
												ORDER BY date_time;";
									}
									if($chatID!=''){
										$result=$db->query($query)->fetchAll();
										$month_day='';
										if(count($result)>0){
											foreach($result as $row){
												$data=strtotime($row['date_time']);
												$ore_min=date("H:m",$data);
												if($month_day!=date("F d",$data)){
													setlocale(LC_TIME,'ita');
													$month_day=date("F d",$data);
													echo"<tr><td align='center'><p class='print_date'>$month_day</p></td></tr>";
												}
												$message=htmlspecialchars(str_replace("/n","<br>",$row['mess_text']));
												if($row['source_user']==$_SESSION['name']){
													echo "
													<tr><td>
														<div class='right'>".
																"<p class='message_value'>".$message."</p> 
																<span class='time-right'>".$ore_min."</span>";
														echo"</div>
													</td></tr>";
												}											
												else{
													echo "<tr><td>";
													if($chatID=='groupID'){
														$userID=$row['userID'];
														$image_url="src/profile_pictures/".$row['image_url'];
														$is_online=$row['is_online'];
														$border="#000000";
														if($is_online)
															$border="";
														echo"
														<div class='propic_from_chat propic_from_chat$userID'
															style='background-image:url(".
																$image_url.");'>
														</div> ";
													}	
													echo "<div class='left'>";
													if($chatID=='groupID')
														echo "<p class='message_source'><b>".$row['nickname']."</b></p> ";
													echo "<p class='message_value'>".$message."</p> 
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
										echo"<p class='print_text right_main_td' align='center'>Select a chat to start a conversation!<p>";
									echo"</table></div>";
									echo "<footer class='send_form right_main_td' id='footer_form' style='display:none'>		
										<button id='send_emoji' style='display:block; float:left;' class='footer_btn'><i class='fa fa-smile-o fa-2x'></i></button>						
											<textarea name='msg' placeholder='Write a message...' id='input_message'></textarea>
											<button id='send_attachment' class='footer_btn'><i class='fa fa-paperclip fa-2x'></i></button>
											<button id='send_message' class='footer_btn'><i class='fa fa-send fa-2x'></i></button>
										</footer>";											
									
							?>
						</div>
					</div>
					<?php					 
						if(isset($_GET['groupID'])||isset($_GET['userID'])){
							echo"<style>
								.right_main_div{
									display: block;
								}
							</style>";
						}	

					?>
            <div class='overlay' id='overlay'>
                <button onclick='close()' id='closeBtn' style='background-color: Transparent; border:none; float: right'>
                    <i class="fa fa-times fa-3x" aria-hidden="true" style='color: white;'></i>
				</button>   
					<div class='box' id='searchForm'>
							<h2>Add someone or a group</h2>
							<input type='text' name='search' placeholder='Insert the username or the group name' onkeyup="showResults(this.value)">
							<table id='tableResults' width='100%'>
							</table>
					</div>
					<form class='box' action='' id='createGroupForm' method='POST'  enctype="multipart/form-data">
							<h2>Create a new group</h2>
							<input type='text' name='name' placeholder='Insert the new group name'>
							<input type='text' name='tag' placeholder='Insert the new group tag'>
           	 				<input id="file-upload" type="file" name="uploaded_image" accept="image/*" onchange="fileUploaded(this)">
							<label for="file-upload" id='label_upload' class='buttons_index'>
								Upload group photo! <i class="fa fa-upload" aria-hidden="true"></i>
							</label>
							<input type='submit' class='buttons_index' name='create' value='Create'>
					</form>  
					<form class='box' action='' id='profileForm' method='POST'  enctype="multipart/form-data">
						<?php
							$user = $db->query("SELECT * FROM Users WHERE userID='$_SESSION[name]';")->fetchArray();
							echo'<h2>Hi '.$user['nickname'].' (@'.$user['username'].') </h2>							
							<a href="logout.php" class="iconlink">
								<i class="fa fa-sign-out fa-2x" aria-hidden="true" ></i>
							</a>';
						?>							
					</form>  
					
            </div>  
		
		<script>
			document.getElementById("closeBtn").addEventListener("click", close);
			var users_online = '<?php
                                $users_online='';
                                $result=$db->query("SELECT U.userID 
                                                        FROM Users U  
                                                            JOIN Friends F
                                                                ON U.userID=F.userID
                                                        WHERE is_online='1';")->fetchAll();
                                foreach ($result as $row) {
                                    $users_online.=$row['userID'].",";
                                }
                                $users_online=substr($users_online,0,-1);
                                echo $users_online;
                            ?>';

			var to_user_id = '<?php
									if(isset($_GET['userID']))
										echo $_GET['userID'];
									else
										echo"-1";
								?>';

			var to_group_id = '<?php
                            if(isset($_GET['groupID'])) 
								echo $_GET['groupID'];
                            else 
								echo"-1";
                        ?>';	

			var session_id = '<?php 
								echo"$_SESSION[name]"
							?>';

			var to_id = '<?php 
                            if(isset($_GET['userID'])) 
                                echo $_GET['userID'];
                            elseif(isset($_GET['groupID']))
                                echo $_GET['groupID'];
                            else
                                echo"-1"
                        ?>';
			
			var destination_type = '<?php
										if(isset($_GET['userID']))
											echo "destination_user";
										elseif(isset($_GET['groupID']))
											echo "destination_group";
										else
											echo "-1";
                                    ?>';
			
			var server_name = '<?php echo $_SERVER['SERVER_NAME']?>';

			function printLastSeen(id,val_log){	
				var log=document.getElementById("log");
				if(log){
					if(id==to_user_id){
					if(val_log=="online")			
						log.innerHTML="Online";
					else
						log.innerHTML="<?php
											echo printLastSeen($db);
										?>";		
					}	
				}				
			}
		</script>
		<script src="fg-emoji-picker/fgEmojiPicker.js"></script>
		<script src="scripts/index_scripts.js"></script>
		
		<?php
			$db->close();
		?>
	</body>
</html>
