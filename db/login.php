<?php
	session_start();
	include"config.php";

	$user=$_POST['user'];
	$pass=$_POST['pass'];  
	$user_escape=mysqli_real_escape_string($conn_database,$user);

	$query="SELECT * FROM users WHERE username='$user_escape' OR email='$user_escape';"; 
		
	$result=$conn_database->query($query);
	if($result){
		$row=$result->fetch_assoc();
		$encrypted_pass=crypt(md5($pass),md5($row['username']));
		if (mysqli_real_escape_string($conn_database,$encrypted_pass)!=$row['password'])
			header("Location: ../error.php?error=pass&pass=$row[password]&pass=$pass");
		
		else{
			$_SESSION['name']=$row['userID'];
			header("Location:../home.php");
		}
	}
	else
		header("Location: ../error.php?error=user");
	
?>