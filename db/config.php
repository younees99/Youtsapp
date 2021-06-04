<?php    	
	include 'db.php';
	$host="localhost";
	$username="root";
	$password="root";
	$database="youtsapp";
	
	$db=new db("localhost","root","root","youtsapp");
	//if(!$db->setCharset("utf8mb4"))
		//echo"Error loading utf8mb4 charset";
?>