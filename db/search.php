<?php    
    include 'config.php';
    $search=$_GET['name'];
    $id=$_GET['id'];
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
    $result=$db->query($query)->fetchAll();
    if(count($result)>0){
            foreach ($result as $row) {
                $is_online=$row['is_online'];
                $result_type=$row['result_type'];
                $ID=$row['ID'];
                $tag=$row['tag'];
                $image_url="src/profile_pictures/".$row['image_url'];
                $type=$row['result_type'];
                echo "<tr><td>
                            <a href='home.php?";
                            if($result_type=='user')
                                echo "userID=".$ID;
                            else
                                echo "groupID=".$ID;
                            echo"' sty>
                                <div class='select_chat'>";
                                    echo"<div class='propic_from_list'";
                                    echo"style='background-image:url(".
                                                $image_url.");";
                                    if($result_type=='user'&&$is_online)
                                        echo"border: solid 2.5px #00ff33";
                                    echo"'>";
                                    echo"</div> 
                                        <p class='chat_name'>$tag</p>
                                    </div>
                                </div>
                            </a>											
                        </td></tr>";
            }
    }    
    else{
        echo"No group or user found";
    }
?> 