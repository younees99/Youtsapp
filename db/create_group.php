<?php
    include "config.php";
    $name=$db->escapeString($_POST['name']);
    $tag=$db->escapeString($_POST['tag']);
    $tmp_name=$_FILES['uploaded_image']['tmp_name'];
    $name=$_FILES['uploaded_image']['name'];

    if(!file_exists($tmp_name)||!is_uploaded_file($tmp_name)){
        $file_name="blank_profile_picture.png";
    }

    else{
        $valid_ext=array("gif", "jpeg", "jpg", "png");
        $tmp=explode(".",$name);
        $ext = end($tmp);
        if(in_array($ext,$valid_ext)){        
            $number=rand(100000,999999);
            $file_name=$user."_".$number.".".$ext;         
            move_uploaded_file($tmp_name,"../src/profile_pictures/$file_name");    
        }
        else{
            header("Location: ../error.php?errore=file");
        }
    }

    $query="INSERT INTO groups(grouptag,group_name,image_url) VALUES
                ('$name',
                '$tag',
                '$file_name');
    ";
    $db->query($query);   
    $last_id=$db->getInsertId();  
    header("Location:../home.php?groupID=$last_id");
?>