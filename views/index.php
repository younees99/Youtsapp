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

	//Getting the IDs of online users
	$users_online=$db->query("SELECT U.userID 
							FROM Users U  
								JOIN Friends F
									ON U.userID=F.userID
							WHERE is_online='1';")->fetchAll();

	$userID = "-1" ;
	if(isset($_GET['userID']))
		$userID = $_GET['userID'];
	
	$groupID = "-1" ;
	if(isset($_GET['groupID']))
		$groupID = $_GET['groupID'];
	
	$toID = "-1";
	if(isset($_GET['userID'])) 
		$toID = $_GET['userID'];

	elseif(isset($_GET['groupID']))
		$toID = $_GET['groupID'];

	$destination_type = " ";	
	if(isset($_GET['userID']))
		$destination_type = "destination_user";

	elseif(isset($_GET['groupID']))
		$destination_type = "destination_group";
	//Query to fetch the chat list for the user
	$query="SELECT	friendID AS chatID,
					FU.username AS chatName,
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
	$chats = $db->query($query)->fetchAll();
	

	$result = $db->query("SELECT * FROM Users WHERE userID='$_SESSION[name]';")->fetchArray();
	$username = $result['username'];				
	

?>
<!DOCTYPE html>
<html>
	<head>
		<title>Home</title>
	    <link rel="stylesheet" type="text/css" href="../style/style.css?version=888">
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
						<ul id='chats'></ul>												
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
							<header id='header_chat'></header>
							<div id='output' class='output right_main_td'>
								<ul id='messages'>
								</ul>
								<p class='print_text' id='no_message'>There is no message yet!<br>Start a coversation!<p>
								<p class='print_text' id='select_chat'>Select a chat to start a conversation!<p>
							</div>
							<?php	
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
														$image_url="../src/profile_pictures/".$row['image_url'];
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
														echo "<p class='message_source'><b>".$row['username']."</b></p> ";
													echo "<p class='message_value'>".$message."</p> 
															<span class='time-left'>".$ore_min."</span>
													</div>
														</td></tr>";
												}
																						
											}
										}					
										else
											echo"<p class='print_text'>There is no message yet!<br>Start a coversation!<p>";
										echo"</div>";
									}									
									else
										echo"<p class='print_text right_main_td' align='center'>Select a chat to start a conversation!<p>";
									echo"</div>";							
									
							?>
							<footer class='send_form right_main_td' id='footer_form' style='display:none'>		
								<button id='send_emoji' style='display:block; float:left;' class='footer_btn'><i class='fa fa-smile-o fa-2x'></i></button>						
								<textarea name='msg' placeholder='Write a message...' id='input_message'></textarea>
								<button id='send_attachment' class='footer_btn'><i class='fa fa-paperclip fa-2x'></i></button>
								<button id='send_message' class='footer_btn'><i class='fa fa-send fa-2x'></i></button>
							</footer>
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
							<ul id='tableResults'>
							</ul>
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
						<h2>Hi <?php echo $username; ?> </h2>	
						<a href="../logout.php" class="iconlink">
							<i class="fa fa-sign-out fa-2x" aria-hidden="true" ></i>
						</a>						
					</form>  
					
            </div>  
		
		<script>
			var users_online = <?php echo json_encode($users_online); ?>;
			var to_user_id = <?php echo json_encode($userID); ?>;
			var to_group_id = <?php echo json_encode($groupID); ?>;	
			var session_id = <?php echo json_encode($_SESSION["name"])?>;
			var to_id = <?php echo json_encode($toID); ?>;			
			var destination_type = <?php echo json_encode($destination_type); ?>;			
			var server_name = <?php echo json_encode($_SERVER['SERVER_NAME'])?>;
			var chats = <?php echo json_encode($chats); ?>;
			var last_seen = <?php echo json_encode(printLastSeen($db)); ?>;
			document.getElementById("closeBtn").addEventListener("click", close);

		</script>
		<script src="../scripts/index_scripts.js?t=8"></script>
		<script>
			loadChats(chats);
		</script>
		<?php
			$db->close();
		?>
	</body>
</html>
