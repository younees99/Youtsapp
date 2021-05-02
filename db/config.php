<?php    	
	include 'db.php';
	$host="localhost";
	$username="root";
	$password="root";
	$database="youtsapp";
	/*
	$conn_database=new mysqli($host,$username,$password) or die ("Error during connection: ".$conn_database->error);
	$query="create database if not exists $database;";
	$conn_database->query($query)or die ("Error during creation: ".$conn_database->error);
	$conn_database->query("use $database;")or die ("Error during usage: ".$conn_database->error);
	*/
	$db=new db("localhost","root","root","youtsapp");
	$query="create database if not exists $database;";
	$db->query($query);
	$db->query("use $database;");
?>