<?php
	include __DIR__.'\config.php';
    //Creating user table
    $query='create table if not exists users(        
        userID INT(255) primary key auto_increment NOT NULL,
        username VARCHAR(255) NOT NULL,
        nickname VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        image_url VARCHAR(255),
        last_seen timestamp NOT NULL
    )engine=InnoDB';
    $conn_database->query($query) or die ("Error creating user table: ".$conn_database->error);  

    //Creating rooms table
    $query='create table if not exists rooms( 
        roomID INT(255) primary key auto_increment NOT NULL,
        founded timestamp NOT NULL,
        name VARCHAR(255) NOT NULL,
        image_url VARCHAR(255),
        messageID INT(255) NOT NULL
    )engine=InnoDB';
    $conn_database->query($query) or die ("Error creating rooms table: ".$conn_database->error); 

    //Creating messages table
    $query='create table if not exists messages( 
        messageID INT(255) primary key auto_increment NOT NULL,
        data timestamp NOT NULL,
        text VARCHAR(255) NOT NULL,
        source INT(255) NOT NULL,
        destination INT(255) NOT NULL,
        stato ENUM("sent","recieved","read") NOT NULL,
        foreign key (source) references users(userID),  
        foreign key (destination) references rooms(roomID)
    )engine=InnoDB';
    $conn_database->query($query) or die ("Error creating messages table: ".$conn_database->error);  

    //Creating role table for rooms
    $query='create table if not exists roles(
        roleID INT(255) primary key auto_increment NOT NULL,
        value ENUM("membro","amministratore") NOT NULL,
        room INT(255) NOT NULL,
        user INT(255) NOT NULL, 
        foreign key (room) references rooms(roomID),
        foreign key (user) references users(userID)
    )engine=InnoDB';
    $conn_database->query($query) or die ("Error creating roles table: ".$conn_database->error);  
 
    //Creating group friends
    $query="create table if not exists friends(
        friendshipID INT(255) primary key auto_increment NOT NULL,
        since timestamp NOT NULL,
        friend1ID INT(255) NOT NULL,
        friend2ID INT(255) NOT NULL,
        foreign key (friend1ID) references users(userID),  
        foreign key (friend2ID) references users(userID)
    )engine=InnoDB";
    $conn_database->query($query) or die ("Error creating friends table: ".$conn_database->error);  
?>