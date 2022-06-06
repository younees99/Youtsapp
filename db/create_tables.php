<?php
    include 'config.php';

    //Creating USERS table
    $query="CREATE TABLE IF NOT EXISTS `Users`(        
        userID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        username VARCHAR(255) NOT NULL,
        nickname VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        image_url VARCHAR(255),
        last_seen TIMESTAMP NOT NULL,
        is_online BOOLEAN NOT NULL DEFAULT 0
    )engine=InnoDB";
    $db->query($query);

    //Creating GROUPS table
    $query="CREATE TABLE IF NOT EXISTS `Groups`( 
        groupID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        grouptag VARCHAR(255) NOT NULL,
        founded TIMESTAMP NOT NULL,
        group_name VARCHAR(255) NOT NULL,
        image_url VARCHAR(255),
        last_message INT(255)
    )engine=InnoDB";
    $db->query($query);

    //Creating MESSAGES table
    $query="CREATE TABLE IF NOT EXISTS `Messages`( 
        messageID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        date_time TIMESTAMP NOT NULL,
        mess_text TEXT CHARSET utf8mb NOT NULL,
        source_user INT(255),
        destination_user INT(255),
        destination_group INT(255),
        FOREIGN KEY (source_user) REFERENCES Users(userID),  
        FOREIGN KEY (destination_user) REFERENCES Users(userID),
        FOREIGN KEY (destination_group) REFERENCES Groups(groupID)
    )engine=InnoDB";
    $db->query($query);

    //Adding charset for emojis
    $query="ALTER TABLE `Messages` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";
    $db->query($query);

    //Adding last_message foreign key in group table
    $query="ALTER TABLE  `Groups`
        ADD CONSTRAINT  FOREIGN KEY (last_message) REFERENCES `Messages` (messageID)";
    $db->query($query);

    //Creating GROUPS_USER table
    $query="CREATE TABLE IF NOT EXISTS `Groups_users`(
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
    $query="CREATE TABLE IF NOT EXISTS `Friends`(
        friendshipID INT(255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        since TIMESTAMP NULL,
        userID INT(255) NOT NULL,
        friendID INT(255) NOT NULL,
        last_message INT(255),
        is_typing BOOLEAN NOT NULL DEFAULT 0,
        FOREIGN KEY (userID) REFERENCES Users(userID),  
        FOREIGN KEY (friendID) REFERENCES Users(userID),
        FOREIGN KEY (last_message) REFERENCES Messages(messageID)
    )engine=InnoDB";
    $db->query($query);  

    //Creating MESSAGES_USERS table to save which users saw a message in a group
    //When a users sees a message his and the message ids will be saved here
    $query = "CREATE TABLE IF NOT EXISTS `Messages_users`(
        message_userID INT (255) PRIMARY KEY AUTO_INCREMENT NOT NULL,
        userID INT(255),
        messageID INT(255),
        date_read TIMESTAMP,
        FOREIGN KEY (userID) REFERENCES Users(userID),  
        FOREIGN KEY (messageID) REFERENCES Messages(messageID)
    )engine=InnoDB";

    //Creating global group    
    $query="INSERT INTO `Groups` (group_name,image_url,grouptag) VALUES('Global','global_group.png','global');";
    $db->query($query);  

    //Creating admin account
    $query="INSERT INTO `Users` (username,password,email,image_url) VALUES
        (
            'admin',
            '21232f297a57a5a743894a0e4a801fc3',
            'admin@youtsapp.com',
            'king_crown.jpg'
        );";
    // password: admin

    $db->query($query);  

    $query="INSERT INTO `Groups_users`(user_role,groupID,userID) VALUES (
        'admin',
        '1',
        '1'
    );";
    $db->query($query);  
?>