<?php
	session_start();
	include"config.php";

	$user=$_POST['user'];
	$pass=$_POST['pass'];  
	$user_escape=mysqli_real_escape_string($conn_database,$user);

	$query="
		SELECT * FROM utente WHERE user='$user_escape' OR email='$user_escape';"; 
		
	$risultato=$conn_database->query($query);
	$riga=$risultato->fetch_assoc();
	$pass_criptata=crypt(md5($pass),md5($riga['user']));

	if(!$riga)
		header("Location: ../error.php?errore=user");
  	
	else if (mysqli_real_escape_string($conn_database,$pass_criptata)!=$riga['password']){
		header("Location: ../error.php?errore=pass");
	}

	else{
		$_SESSION['name']=$riga['userID'];
		header("Location:../home.php");
	}
?>