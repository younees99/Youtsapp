<?php    	
	//Creating database for first time use	
	$link = mysqli_connect("127.0.0.1", "root");

    // Check connection
    if (!$link)
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    

    // Creating a database
    // The database is called youtsapp
    $sql = "CREATE DATABASE youtsapp";
	mysqli_query($link,$sql);
    
    include 'db.php';
    $db = new db();
        
?>