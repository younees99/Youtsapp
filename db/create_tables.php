<?php
	include __DIR__.'\config.php';
    //Creating user table
    $query='create table if not exists user(        
        userID INT(255) primary key auto_increment NOT_NULL,
        username VARCHAR(255) NOT_NULL,
        nickname VARCHAR(255) NOT_NULL,
        password VARCHAR(255) NOT_NULL,
        email VARCHAR(255) NOT_NULL,
        image_url VARCHAR(255),
        last_seen timestamp NOT_NULL
    )engine=InnoDB';
    $conn_database->query($query);  

    //Creating messages table
    $query='create table if not exists messages( 
        messageID INT(255) primary key auto_increment NOT_NULL,
        data timestamp NOT_NULL,
        text VARCHAR(255) NOT_NULL,
        source INT(255) NOT_NULL,
        destination INT(255) NOT_NULL,
        stato ENUM("sent","recieved","read") NOT_NULL,
        foreign key (source) references users(userID),  
        foreign key (destination) references users(roomID), 
    )engine=InnoDB';
    $conn_database->query($query);

    //Creating role table for rooms
    $query="create tabelle if not exists roles(
        roleID INT(255) primary key auto_increment NOT_NULL,
        value ENUM('membro','amministratore') NOT_NULL,
        room INT(255) NOT_NULL,
        user INT(255) NOT_NULL, 
        foreign key (room) references room(roomID),
        foreign key (user) references user(userID)
    )engine=InnoDB";
    $conn_database->query($query);

    //Creating rooms table
    $query='create table if not exists rooms( 
        roomsID INT(255) primary key auto_increment NOT_NULL,
        founded timestamp NOT_NULL,
        name VARCHAR(255) NOT_NULL,
        image_url VARCHAR(255),
        userID INT(255) NOT_NULL,
        messageID INT(255) NOT_NULL,
        foreign key (userID) references users(userID),  
        foreign key (messageID) references messages(messageID)
    )engine=InnoDB';
    $conn_database->query($query);

    //Creating group friends
    $query="create table if not exists friends(
        friendshipID INT(255) primary key auto_increment NOT_NULL,
        since timestamp NOT_NULL,
        friend1ID INT(255) NOT_NULL,
        friend2ID INT(255) NOT_NULL,
        foreign key (friend1ID) references users(userID),  
        foreign key (friend2ID) references users(userID)
    )engine=InnoDB";
    $conn_database->query($query);
?>