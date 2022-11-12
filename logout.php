<?php
	session_start();
	session_destroy();
	if (isset($_COOKIE['userID'])) {
		unset($_COOKIE['userID']); 
		setcookie('userID', null, -1, '/'); 
	}
	header("Location: index.php");
?>