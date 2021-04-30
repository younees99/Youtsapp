<?php    
	$host="localhost";
	$username="root";
	$password="root";
	$database="youtsapp";

	$conn_database=new mysqli($host,$username,$password) or die (header("Location: errore.php?errore=").$conn_database->error);
	$query="create database if not exists $database;";
	$conn_database->query($query)or die (header("Location: errore.php?errore=").$conn_database->error);
	$conn_database->query("use $database;")or die (header("Location: errore.php?errore=").$conn_database->error);
?>