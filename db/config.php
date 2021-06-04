<?php    	
	include 'db.php';
	$host="localhost";
	$username="root";
	$password="root";
	$database="youtsapp";
	
	$db=new db("localhost","root","root","youtsapp");
	
	$db->query("SET NAMES utf8mb4");
?>