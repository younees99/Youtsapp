<?php
	session_start();
    include 'config.php';
    $id = $_SESSION['name'];
    $choice = $_GET['choice'];
    $choice=$db->escapeString($choice);
    $query = '';
    switch ($choice) {
        case 'head':
            $chatID = $_GET['chatID'];
            $chatID=$db->escapeString($chatID);
            $chat_type = $_GET['chat_type'];
            if($chat_type == "user"){									
                $query="SELECT 
                            username,image_url,is_typing,is_online,last_seen
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
                            group_name,image_url, (
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
            $search=$_GET['name'];
            $search=$db->escapeString($search);
            $query="SELECT U.userID AS ID,
                            username AS tag,
                            image_url,
                            is_online,
                            'user' AS result_type,
                            MAX(since) AS since
                        FROM Friends F 
                            LEFT JOIN Users U 
                                ON (U.userID=F.friendID)
                    WHERE 
                        username LIKE '%$search%'
                        AND U.userID!=$id
                    GROUP BY id
                    UNION
                    SELECT GU.groupID AS ID,
                            grouptag AS tag,
                            image_url,
                            '0'AS is_online,
                            'group' AS result_type,
                            MAX(since) AS since
                        FROM Groups G
                            LEFT JOIN Groups_users GU
                                ON (G.groupID=GU.groupID)
                    WHERE 
                        grouptag LIKE '%$search%'
                        AND GU.userID!=$id
                    GROUP BY id;";
    
            break;
    }
    $result=$db->query($query)->fetchAll();
    echo json_encode($result);
?>