<?php
	session_start();
	include "config.php";

	$user=$_POST['user'];
	$pass=$_POST['pass'];  
	$user_escape=$db->escapeString($user);

	$query="SELECT * FROM users WHERE username='$user_escape' OR email='$user_escape';"; 
		
	$result=$db->query($query);
	if($result->numRows()>0){
		$row=$result->fetchArray();
		$encrypted_pass=crypt(md5($pass),md5($row['username']));
		if($db->escapeString($encrypted_pass)!=$row['password'])
			header("Location: ../error.php?error=pass&pass=$row[password]&pass=$encrypted_pass");		
		else{
			$_SESSION['name']=$row['userID'];
			header("Location:../home.php");
		}
	}
	else
		header("Location: ../error.php?error=user");
	
?>