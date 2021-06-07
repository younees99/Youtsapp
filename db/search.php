<?php    
    include 'config.php';
    $search=$_GET['name'];
    $id=$_GET['id'];
    $search=$db->escapeString($search);
    $query="SELECT DISTINCT userID AS ID,
                    username AS tag,
                    nickname AS name,
                    image_url,
                    'user' AS result_type,
                    is_online                 
                FROM users

            WHERE 
                username LIKE '%$search%'
                AND userID!=$id
            UNION
            SELECT groupID AS ID,
                    grouptag AS tag,
                    group_name AS name,
                    image_url,
                    'group' AS result_type,
                    'nope' AS is_online
                FROM groups 
            WHERE 
                grouptag LIKE '%$search%';";
    $result=$db->query($query)->fetchAll();
    if(count($result)>0){
            foreach ($result as $row) {
                $is_online=$row['is_online'];
                $result_type=$row['result_type'];
                $ID=$row['ID'];
                $tag=$row['tag'];
                $name=$row['name'];
                $image_url="src/profile_pictures/".$row['image_url'];
                $type=$row['result_type'];
                echo "<tr><td>
                            <a href='home.php?";
                            if($result_type=='user')
                                echo "userID=".$ID;
                            else
                                echo "groupID=".$ID;
                            echo"'>
                                <div class='select_chat'>";
                                    echo"<div class='propic_from_list'";
                                    echo"style='background-image:url(".
                                                $image_url.");";
                                    if($result_type=='user'&&$is_online)
                                        echo"border: solid 2.5px #00ff33";
                                    echo"'>
                                        </div> 
                                        <p class='chat_name'>$name</p>
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