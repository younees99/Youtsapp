<?php
	//starting session and setting coockies
	session_start();
	if(isset($_COOKIE['userID']))
		$_SESSION['name']=$_COOKIE['userID'];
	
	else if(!isset($_SESSION['name']))
		header("Location: login.php");
	else{
		$cookie_name = "userID";
		$cookie_value = $_SESSION['name'];
		setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/"); // 86400 = 1 day	
	}

	//including dbms connection
	include '../db/config.php';
	date_default_timezone_set('Europe/Berlin');

	//Query to fetch the chat list for the user's friends and groups
	$query="SELECT *
				FROM(
				SELECT	friendID AS chatID,
						FU.nickname AS chatName,
						FU.image_url AS chatImage,
						mess_text,
						date_time,
						M.source_user AS last_message_source_id,
						FU.is_online,
						is_typing,
						'user' AS chat_type,
						(SELECT COUNT(*) 
							FROM Messages M1
								JOIN
									Users U1
									ON
										U1.userID=M1.source_user
							WHERE 
								(source_user='$_SESSION[name]' OR destination_user='$_SESSION[name]') 
								AND 
								(source_user=chatID OR destination_user=chatID)
								AND is_read=0)
						AS count_unread,
						(SELECT nickname
							FROM Users
								WHERE
									userID=last_message_source_id)
						AS last_message_source

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
					M.source_user AS last_message_source_id,
					'0' AS is_online,
					'0' AS is_typing,
					'group' AS chat_type,
					(SELECT COUNT(*) 
						FROM Messages M
							JOIN
								Users U 
								ON
									U.userID=M.source_user
						WHERE 
							destination_group=chatID
							and is_read=0)
					AS count_unread,
					(SELECT nickname
						FROM Users
							WHERE
								userID=last_message_source_id)
					AS last_message_source

				FROM Groups_users GU
					JOIN Users U
						ON GU.userID=U.userID
					LEFT JOIN Groups G
						ON GU.groupID=G.groupID 
					LEFT JOIN Messages M
						ON G.last_message=M.messageID
				WHERE U.userID='$_SESSION[name]'
				) a
			ORDER BY date_time DESC;";			
	$result = $db->query($query);
	$chats = $result->fetchAll();

	//Query to fetch all messages from all chats of the user	
	function queryConversation($chat_type,$chatID,$db,$id){
		if($chat_type=="user"){
			$query="SELECT *,
						ROW_NUMBER() OVER(ORDER BY date_time ASC) AS message_number
							FROM Messages M
								JOIN
									Users U 
									ON
										U.userID=M.source_user
							WHERE 
								(source_user='$id' OR destination_user='$id') 
								AND 
								(source_user='$chatID' OR destination_user='$chatID') 
					ORDER BY date_time;";
		}
		elseif($chat_type=="group"){
			$query="SELECT * ,
						ROW_NUMBER() OVER(ORDER BY date_time ASC) AS message_number
							FROM Messages M
								JOIN
									Users U 
									ON
										U.userID=M.source_user
							WHERE 
								destination_group='$chatID'
					ORDER BY date_time;";
		}
		$result = $db->query($query);
		return $result->fetchAll();		
	}
	

	//Query to fetch the user's data
	$result = $db->query("SELECT * FROM Users WHERE userID='$_SESSION[name]';");
	$user_data = $result->fetchArray();	
	

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Home</title>
	    <link rel="stylesheet" type="text/css" href="../style/style.css?version=113">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> 
		<script>
			var session_id = <?php echo json_encode($_SESSION["name"])?>;		
			var server_name = <?php echo json_encode($_SERVER['SERVER_NAME'])?>;
			var user_data = <?php echo json_encode($user_data); ?>;
		</script>
	</head>
	<body>		
		<div id="loading_div" style='display:none;' class='box'>
			<i class="fa fa-circle-o-notch fa-spin fa-2x" aria-hidden="true"></i>
			<p style="font-size: 28px;">Connection...</p>
		</div>
		<div id='main_div' style='display: block;'>
			<div id='left_main_div'>
				<header class='header_chats' id='header_chats'> 
					<button class='iconbtn' onclick='exitChat()'>
						<p class='youtsapp'>
							Youtsapp
						</p>
					</button>
					<button class='iconbtn' onclick='openProfileMenu()'>
						<i class="fa fa-user-circle-o fa-2x" aria-hidden="true" style="float:right"></i>
					</button>
				</header>
				<ul id='chats'>
					<?php
						foreach($chats as $chat):						
							$chatID = $chat["chatID"];
							$chat_type = $chat["chat_type"];	
							$chatName = $chat["chatName"];	
							$is_online = $chat["is_online"];
							$is_typing = $chat["is_typing"];
							$count_unread = $chat["count_unread"];
							$last_message_source = $chat["last_message_source"];
							$last_message_source_id = $chat["last_message_source_id"];
							$chat_image = "../src/profile_pictures/".$chat["chatImage"];
							$mess_text = $chat["mess_text"];
							$date_time = strtotime($chat['date_time']);
							$time = date("H:i",$date_time);
							$date = date("d/m",$date_time);
							$year = date("Y",$date_time);
							$full_date = date("d/m/Y",$date_time);
							$today = time();
					?>
						<li id='chat_<?=$chat_type;?>_<?=$chatID;?>'>
							<button class='select_chat' 
									onClick='showConversation("<?=$chat_type;?>","<?=$chatID;?>"); 
											requestHeader("<?=$chat_type;?>","<?=$chatID;?>");
											readMessages("<?=$chat_type;?>","<?=$chatID;?>");'>
									
								<div class='propic_from_list'
								<?php
									if($chat_type=='user'):
								?>
								id='propic_from_list<?=$chatID;?>'
								<?php
									endif;
								?>
								style='background-image:url("<?=$chat_image;?>");
								<?php
								$border = "#333333";
								if($chat_type=='user' && $is_online==true)
									$border = "#00ff33";
								?>
								border:2.5px solid <?=$border;?>'></div>
								<table class='chat_table'>
									<tr>
										<td>
										<p class='chat_name'><?=$chatName;?></p>
										</td>
										<td width='38px'>
											<span class='time_preview'  id='time_preview_<?=$chat_type;?>_<?=$chatID;?>'>
											<?php
												$print_time = '';
												if($date == date("d/m",$today))
													$print_time = $time;
												elseif($year == date("Y",$today))
													$print_time = $date;
												else
													$print_time = $full_date;
												echo $print_time;
											?></span>
										</td>
									</tr>
									<tr>
										<td>
											<?php
												$mess_preview_span = 'block';
												$is_typing_span = 'none';
												if($is_typing==true){													
													$mess_preview_span = 'none';
													$is_typing_span = 'block';
												}
												$mess_source = "";
												if($last_message_source_id == $_SESSION['name'])
													$mess_source = "You: ";
												else if($chat_type == "group")
													$mess_source = $last_message_source.": ";
											?>
											<span class='mess_preview'  id='mess_preview_<?=$chat_type;?>_<?=$chatID;?>' style='display: <?= $mess_preview_span;?>;'><?=$mess_source.$mess_text;?></span>
											<span class='mess_preview'  id='is_typing_<?=$chat_type;?>_<?=$chatID;?>' style='display: <?= $is_typing_span;?>; color: #2287d9;'>is typing...</span>
										</td>
										<td>
										<span class="button__badge" id='unread_mess_<?=$chat_type;?>_<?=$chatID;?>'
										<?php
											if($count_unread == 0):
										?>
											style='display: none;'
										<?php
											endif;
										?>
										><?=$count_unread;?></span>
										</td>
									</tr>
								</table>									
							</button>
						</li>
					<?php		
						endforeach;
					?>
				</ul>												
				<footer class='footer_chats' id='footer_chats'>
					<button class='iconbtn' onclick='search()'>
						<i class="fa fa-plus fa-2x" aria-hidden="true"></i>
					</button>
					<button class='iconbtn' onclick='createGroup()'>
						<i class="fa fa-users fa-2x" aria-hidden="true"></i>
					</button>
					<button class='iconbtn'>
						<i class="fa fa-paint-brush fa-2x" aria-hidden="true"></i>
					</button>
				</footer>
			</div>	
			<div class='right_main_div' id='right_main_div'>
				<div id='main'>		
					<header id='header_chat'></header>
					<div id='output' class='output right_main'>
						<table id='messages'>
							<?php foreach($chats as $chat): 
								$month_day= '';
								$chatID = $chat["chatID"];
								$chat_type = $chat["chat_type"];
								$messages = queryConversation($chat_type,$chatID,$db,$_SESSION['name']); ?>								
							<tbody id="chat<?= $chat_type;?>_<?= $chatID;?>" class="chat">
								<?php foreach($messages as $message): 
										$date_time = strtotime($message['date_time']);
										$time = date("H:i",$date_time);	
										$date = date("d/m",$date_time);
										$full_date = date("d/m/Y",$date_time);
										$source_user = $message['source_user'];	
										$message_number = $message['message_number'];	
										$mess_text = $message['mess_text'];
										$is_read = $message['is_read'];

									?>
									<?php if($month_day!=date("F d",$date_time)):
										$month_day=date("F d",$date_time);?>
										<tr><td align='center'><p class='print_date'><?= $month_day?></p></td></tr>
									<?php endif;?>
									<?php
									$mess_text=htmlspecialchars(str_replace("/n","<br>",$mess_text));
									if($source_user==$_SESSION['name']):?>
										<tr><td>
											<div id='message_<?= $message_number;?>' class='right'>
													<p class='message_value'><?=$mess_text;?></p> 
													<span class='time-right'><?=$time?></span>
													<?php
														if($is_read):
													?>
														<i class="fa fa-check-circle-o" aria-hidden="true"></i>
													<?php
														else:
													?>
														<i class="fa fa-circle-o unread_<?=$chat_type?>_<?=$chatID?>" aria-hidden="true"></i>
													<?php
														endif;
													?>
											</div>
										</td></tr>
									<?php else: ?>
										<tr><td>
											<?php
												$nickname=$message["nickname"];
												$image_url="../src/profile_pictures/".$message["image_url"];
												$is_online=$message['is_online'];
												$border="#333333";
												if($is_online) 
													$border="#00ff33";
												if($chat_type=='group'):
											?>													
											<div class='propic_from_chat propic_from_chat<?=$chatID?>'
												style='background-image:url(<?=$image_url?>);border:2.5px solid <?= $border?>'>
											</div>
											<?php endif;?>	
											<div id='message_<?= $message_number;?>' class='left'>	
												<?php if($chat_type=='group'): ?>						
													<p class='message_source'><b><?=$nickname?></b></p>
												<?php endif; ?>
												<p class='message_value'><?=$mess_text?></p> 
												<span class='time-left'><?=$time?></span>
											</div>
										</td></tr>
											<?php
										endif;
									?>
								<?php endforeach; ?>
							</tbody>
							<?php endforeach; ?>
						</table>
					</div>
					<footer class='send_form right_main' id='footer_form' style='display:none'>		
						<button id='send_emoji' style='display:block; float:left;' class='footer_btn'><i class='fa fa-smile-o fa-2x'></i></button>						
						<textarea name='msg' placeholder='Write a message...' id='input_message' ></textarea>
						<button id='send_attachment' class='footer_btn'><i class='fa fa-paperclip fa-2x'></i></button>
						<button id='send_message' class='footer_btn'><i class='fa fa-send fa-2x' onclick='sendMessage(); this.blur();'></i></button>
					</footer>
					<p class='print_text' id='no_message' style='display : none;'>There is no message yet!<br>Start a coversation!</p>
					<p class='print_text' id='select_chat_alert'>Select a chat to start a conversation!</p>
				</div>
			</div>
            <div class='overlay' id='overlay'>				               
				<div class='box' id='searchForm'>
					<button id='closeBtn' style='background-color: Transparent; border:none; float: right' onclick='closeOverlay()'>
						<i class="fa fa-times fa-2x" aria-hidden="true" style='color: white;'></i>
					</button>   
					<h2>Add someone or a group</h2>
					<input type='text' name='search' id='input_search' placeholder='Insert the username or the group name' onkeyup="requestResults(this.value)">
					<ul id='tableResults'>
					</ul>
				</div>

				<form class='box' action='' id='createGroupForm' method='POST'  enctype="multipart/form-data">
					<button id='closeBtn' style='background-color: Transparent; border:none; float: right' onclick='closeOverlay()'>
						<i class="fa fa-times fa-2x" aria-hidden="true" style='color: white;'></i>
					</button>   
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
					<button id='closeBtn' style='background-color: Transparent; border:none; float: right' onclick='closeOverlay()'>
							<i class="fa fa-times fa-2x" aria-hidden="true" style='color: white;'></i>
						</button>   
					<table id='profile_table'>
						<tr><th colspan='2'>Profile</th></tr>
						<tr><td colspan='2'>
							<label for="profile_image_input" id='label_upload'>						
								<img id="profile_image" class='propic_from_list' style="float: center;" src="../src/profile_pictures/<?= $user_data['image_url'];?>"/>
							</label>
						</td></tr>
						<tr>
							<td>
								<i class="fa fa-at fa-2x" aria-hidden="true"></i>
							</td>
							<td>
								Username:<br>
								<?= $user_data["username"];?>
							</td>
							<td>
								<i class="fa fa-pencil" aria-hidden="true"></i>
							</td>
						</tr>
						<tr>
							<td>
								<i class="fa fa-user fa-2x" aria-hidden="true"></i>
							</td>
							<td>
								Nickname:<br>
								<?= $user_data["nickname"];?>
							</td>
							<td>
								<i class="fa fa-pencil" aria-hidden="true"></i>
							</td>
						</tr>		
						<tr>
							<td>
								<i class="fa fa-envelope-o fa-2x" aria-hidden="true"></i>
							</td>
							<td>
								Email:<br>
								<?= $user_data["email"];?>
							</td>
							<td>
								<i class="fa fa-pencil" aria-hidden="true"></i>
							</td>
						</tr>
						<tr>
							<td>
								<i class="fa fa-lock fa-2x" aria-hidden="true"></i>
							</td>
							<td>
								Change password
							</td>
							<td>
								<i class="fa fa-pencil" aria-hidden="true"></i>
							</td>
						</tr>
						<tr><td>
							<a href="../logout.php" class="iconlink">
								<i class="fa fa-sign-out fa-2x" aria-hidden="true" ></i>
							</a>	
						</td></tr>							
					</table>
					<input accept="image/*" name="uploaded_image" type='file' id="profile_image_input" onchange="previewImage(this)"/>						
				</form> 
					
            </div>  
			
		<script src="../scripts/index_scripts.js?t=104"></script>
		<!--<script src="../fg-emoji-picker/fgEmojiPicker.js"></script>
		<script>
			const emojiPicker = new FgEmojiPicker({
				trigger: ['send_emoji'],
				removeOnSelection: false,
				closeButton: true,
				position: ['top', 'right'],
				preFetch: true,
				insertInto: document.querySelector('input_message'),
				emit(obj, triggerElement) {
					console.log(obj, triggerElement);
				}
			});
		</script>-->
		<?php
			$db->close();
		?>
	</body>
</html>
