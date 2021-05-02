<?php
    include 'config.php';

    //Creating USERS table
    $query='CREATE TABLE IF NOT EXISTS Users(        
        userID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        username VARCHAR(255) NOT NULL,
        nickname VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        image_url VARCHAR(255),
        last_seen TIMESTAMP NOT NULL,
        is_online BOOLEAN NOT NULL
    )engine=InnoDB';
    $db->query($query);

    //Creating GROUPS table
    $query='CREATE TABLE IF NOT EXISTS Groups( 
        groupID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        founded TIMESTAMP NOT NULL,
        group_name VARCHAR(255) NOT NULL,
        image_url VARCHAR(255),
        last_message INT(255)
    )engine=InnoDB';
    $db->query($query);

    //Creating MESSAGES table
    $query='CREATE TABLE IF NOT EXISTS Messages( 
        messageID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        data TIMESTAMP NOT NULL,
        text VARCHAR(255) NOT NULL,
        source INT(255) NOT NULL,
        destination_user INT(255),
        destination_group INT(255),
        stato ENUM("Sent","Recieved","Read") NOT NULL,
        FOREIGN KEY (source) REFERENCES users(userID),  
        FOREIGN KEY (destination_user) REFERENCES Users(userID),
        FOREIGN KEY (destination_group) REFERENCES Groups(groupID)
    )engine=InnoDB';
    $db->query($query);

    //Adding column last_message in group table
    $query='ALTER TABLE  Groups
        ADD CONSTRAINT  FOREIGN KEY (last_message) REFERENCES messages(messageID)';
    $db->query($query);
    
    //Creating GROUPS_USER table
    $query="CREATE TABLE IF NOT EXISTS Groups_users(
        group_userID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        since TIMESTAMP NOT NULL,
        user_role ENUM('Member','Admin'),
        groupID INT(255),
        userID INT(255),
        FOREIGN KEY (userID) REFERENCES Users(userID),
        FOREIGN KEY (groupID) REFERENCES Groups(groupID)
    )engine=InnoDB";
    $db->query($query);
 
    //Creating FRIENDS table
    $query="CREATE TABLE IF NOT EXISTS Friends(
        friendshipID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        since TIMESTAMP NOT NULL,
        friend1ID INT(255) NOT NULL,
        friend2ID INT(255) NOT NULL,
        last_message INT(255),
        FOREIGN KEY (friend1ID) REFERENCES Users(userID),  
        FOREIGN KEY (friend2ID) REFERENCES Users(userID),
        FOREIGN KEY (last_message) REFERENCES Messages(messageID)
    )engine=InnoDB";
    $db->query($query);  
?>