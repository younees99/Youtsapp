<?php
    include "config.php";
    $user=$db->escapeString($_POST['user']);
    $nickname=$db->escapeString($_POST['nickname']);
    $email=$db->escapeString($_POST['email']);
    $pass=$db->escapeString($_POST['pass']);
    $tmp_name=$_FILES['uploaded_image']['tmp_name'];
    $name=$_FILES['uploaded_image']['name'];

    if(!file_exists($tmp_name)||!is_uploaded_file($tmp_name)){
        $file_name="blank_profile_picture.png";
    }

    else{
        $valid_ext=array("gif", "jpeg", "jpg", "png");
        $ext = end(explode(".",$name));
        if(in_array($ext,$valid_ext)){        
            $number=rand(100000,999999);
            $no_spaces_nick=str_replace(" ","",$nickname);
            $file_name=$user."_".$no_spaces_nick."_".$number.".".$ext;
            move_uploaded_file($tmp_name,"../src/immagini_profilo/$file_name");    
        }
        else{
            header("Location: ../error.php?errore=file");
        }
    }

    $query="INSERT INTO users(username,nickname,password,email,image_url) VALUES
                ('$user',
                '$nickname',
                MD5('$pass'),
                '$email',
                '$file_name');
    ";
    $db->query($query);   
    $last_id=$db->getInsertId(); 
    $query="INSERT INTO groups_users (user_role,groupID,userID) VALUES
            (
                'member',
                '1',
                '$last_id'
            );";
    $db->query($query);    
    header("Location:../index.php");
?> 
