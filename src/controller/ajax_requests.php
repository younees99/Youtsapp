<?php
	session_start();
    include 'config.php';
    $choice = $_GET['choice'];
    $choice=$db->escapeString($choice);
    $query = '';
    switch ($choice) {
        case 'head':
            $id = $_SESSION['name'];
            $chatID = $_GET['chatID'];
            $chatID=$db->escapeString($chatID);
            $chat_type = $_GET['chat_type'];
            if($chat_type == "user"){									
                $query="SELECT 
                            U.userID,image_url,is_typing,is_online,last_seen
                            FROM 
                                Users U
                                JOIN 
                                    Friends F
                                        ON
                                            U.userID=F.userID
                            WHERE 
                                friendID='$id'
                            AND
                                U.userID='$chatID';";
            }
            elseif($chat_type="group"){
                $query="SELECT 
                            G.groupID,group_name,image_url, (
                                    SELECT COUNT(*) as online_users 
                                        FROM Users 
                                        WHERE 
                                            is_online='1' 
                                        AND
                                            userID!='$id'
                            ) AS online_users
                            FROM 
                                Groups G
                                JOIN 
                                    Groups_users GU
                                        ON
                                            GU.groupID=G.groupID
                            WHERE 
                                GU.userID='$id'
                            AND
                                G.groupID='$chatID';";
            }            
            break;

        case 'search':
            $id = $_SESSION['name'];
            $search = $_GET['name'];
            $search = $db->escapeString($search);
            $search = strtolower($search);
            $query="SELECT userID AS chatID,
                username AS chat_name,
                image_url,
                is_online,
                'user' AS chat_type
                FROM Users
            WHERE 
                LOWER(username) LIKE '$search%'
                AND userID!='$id'
            GROUP BY chatID
            UNION
            SELECT groupID AS chatID,
                    group_name AS chat_name,
                    image_url,
                    '0'AS is_online,
                    'group' AS chat_type
                FROM Groups
            WHERE 
                LOWER(group_name) LIKE '$search%'
            GROUP BY chatID;";
    
            break;
            case 'available_tag':
                $tag = $_GET['tag'];
                $tag = $db->escapeString($tag);
                $query = "SELECT COUNT(*) as is_taken
                        FROM(
                            SELECT userID
                                FROM
                                    Users U
                                WHERE
                                    U.username = '$tag'
                            UNION
                            SELECT groupID
                                FROM
                                    Groups G
                                WHERE
                                    G.group_name = '$tag'
                        )a;";
                break;
    }
    $result=$db->query($query)->fetchAll();
    echo json_encode($result);
?>