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
        is_online BOOLEAN NOT NULL DEFAULT 0
    )engine=InnoDB';
    $db->query($query);

    //Creating GROUPS table
    $query='CREATE TABLE IF NOT EXISTS Groups( 
        groupID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        grouptag VARCHAR(255) NOT NULL,
        founded TIMESTAMP NOT NULL,
        group_name VARCHAR(255) NOT NULL,
        image_url VARCHAR(255),
        last_message INT(255)
    )engine=InnoDB';
    $db->query($query);

    //Creating MESSAGES table
    $query='CREATE TABLE IF NOT EXISTS Messages( 
        messageID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        date_time TIMESTAMP NOT NULL,
        mess_text VARCHAR(255) NOT NULL,
        source_user INT(255),
        destination_user INT(255),
        destination_group INT(255),
        mess_status ENUM("sent","recieved","read") NOT NULL,
        FOREIGN KEY (source_user) REFERENCES Users(userID),  
        FOREIGN KEY (destination_user) REFERENCES Users(userID),
        FOREIGN KEY (destination_group) REFERENCES Groups(groupID)
    )engine=InnoDB';
    $db->query($query);

    //Adding charset for emojis
    $query='ALTER TABLE Messages CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
    $db->query($query);
    
    $query='ALTER TABLE Messages MODIFY mess_text TEXT CHARSET utf8mb4;';
    $db->query($query);


    //Adding last_message foreign key in group table
    $query='ALTER TABLE  Groups
        ADD CONSTRAINT  FOREIGN KEY (last_message) REFERENCES Messages(messageID)';
    $db->query($query);
    
    //Adding  foreign keys in messages

    //Creating GROUPS_USER table
    $query="CREATE TABLE IF NOT EXISTS Groups_users(
        group_userID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        since TIMESTAMP NOT NULL,
        user_role ENUM('member','admin'),
        groupID INT(255),
        userID INT(255),
        is_typing BOOLEAN NOT NULL DEFAULT 0,
        FOREIGN KEY (userID) REFERENCES Users(userID),
        FOREIGN KEY (groupID) REFERENCES Groups(groupID)
    )engine=InnoDB";
    $db->query($query);
 
    //Creating FRIENDS table
    $query="CREATE TABLE IF NOT EXISTS Friends(
        friendshipID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        since TIMESTAMP NOT NULL,
        userID INT(255) NOT NULL,
        friendID INT(255) NOT NULL,
        last_message INT(255),
        is_typing BOOLEAN NOT NULL DEFAULT 0,
        FOREIGN KEY (userID) REFERENCES Users(userID),  
        FOREIGN KEY (friendID) REFERENCES Users(userID),
        FOREIGN KEY (last_message) REFERENCES Messages(messageID)
    )engine=InnoDB";
    $db->query($query);  

    //Creating global group    
    $query="INSERT INTO Groups (group_name,image_url,grouptag) VALUES('Global','global_group.png','global');";
    $db->query($query);  

    //Creating admin account
    $query="INSERT INTO Users (username,nickname,password,email,image_url) VALUES
        (
            'admin',
            'Admin',
            '79e6c925cc97fca2de5bf975565647e3',
            'admin@youtsapp.it',
            'king_crown'
        );";
    // password: SDjn45$%43
    $db->query($query);  

    $query="INSERT INTO Groups_users(user_role,groupID,userID) VALUES (
        'admin',
        '1',
        '1'
    );";
    $db->query($query);  
?>