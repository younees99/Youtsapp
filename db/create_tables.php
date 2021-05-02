<?php
    include 'config.php';

    //Creating USERS table
    $query='create table if not exists users(        
        userID INT(255) primary key auto_increment NOT NULL,
        username VARCHAR(255) NOT NULL,
        nickname VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        image_url VARCHAR(255),
        last_seen timestamp NOT NULL
    )engine=InnoDB';
    $db->query($query);

    //Creating GROUPS table
    $query='create table if not exists groups( 
        groupID INT(255) primary key auto_increment NOT NULL,
        founded timestamp NOT NULL,
        name VARCHAR(255) NOT NULL,
        image_url VARCHAR(255)
    )engine=InnoDB';
    $db->query($query);
    
    //Creating GROUPS_USER table
    $query="create table if not exists groups_user(
        group_userID INT(255) primary key auto_increment NOT NULL,
        since timestamp NOT NULL,
        value ENUM('Member','Admin'),
        groupID INT(255),
        userID INT(255),
        foreign key (userID) references users(userID),
        foreign key (groupID) references groups(groupID)
    )engine=InnoDB";
    $db->query($query);

    //Creating MESSAGES table
    $query='create table if not exists messages( 
        messageID INT(255) primary key auto_increment NOT NULL,
        data timestamp NOT NULL,
        text VARCHAR(255) NOT NULL,
        source INT(255) NOT NULL,
        destination_user INT(255),
        destination_group INT(255),
        stato ENUM("Sent","Recieved","Read") NOT NULL,
        foreign key (source) references users(userID),  
        foreign key (destination_user) references users(userID),
        foreign key (destination_group) references groups(groupID)
    )engine=InnoDB';
    $db->query($query);
 
    //Creating FRIENDS table
    $query="create table if not exists friends(
        friendshipID INT(255) primary key auto_increment NOT NULL,
        since timestamp NOT NULL,
        friend1ID INT(255) NOT NULL,
        friend2ID INT(255) NOT NULL,
        foreign key (friend1ID) references users(userID),  
        foreign key (friend2ID) references users(userID)
    )engine=InnoDB";
    $db->query($query);  
?>