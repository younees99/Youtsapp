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
        $want_to_upload = true;
        $valid_ext=array("gif", "jpeg", "jpg", "png");
        $tmp=explode(".",$name);
        $ext = end($tmp);
        if(in_array($ext,$valid_ext)){        
            $number=rand(100000,999999);
            $file_name=$user."_".$number.".".$ext; 

            if(!$_FILES['uploaded_image']['tmp_name']){
                echo "File not uploaded!";
                header("Location: ../views/error.php?error=file");
            }

            else{                
                if(move_uploaded_file($tmp_name, __DIR__."../src/profile_pictures/$file_name")){
                    echo "File uploaded!"; 
                    $uploaded = true;
                }
                else{
                    echo "File not uploaded!";
                    header("Location: ../views/error.php?error=file");
                }
            }
        }        
        else{
            echo"Errore estensione";
            header("Location: ../views/error.php?error=ext");
        }
    } 
    
    $query="INSERT INTO Users(username,nickname,password,email,image_url) VALUES
                ('$user',
                '$nickname',
                MD5('$pass'),
                '$email',
                '$file_name');
            ";
    $db->query($query);   
    $last_id=$db->getInsertId(); 
    $query="INSERT INTO Groups_users (user_role,groupID,userID) VALUES
            (
                'member',
                '1',
                '$last_id'
            );";
    $db->query($query); 
    //header("Location:../index.php");
?> 
