<?php
    include("config.php");
    $user=$_POST['user'];
    $nickname=$_POST['nickname'];
    $email=$_POST['email'];
    $pass=$_POST['pass'];
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
    //Encrypting the password
    $pass_criptata = crypt(md5($pass),md5($user)); 

    $query="
        insert into users(username,nickname,password,email,image_url) values
            ('".mysqli_real_escape_string($conn_database,$user)."',
             '".mysqli_real_escape_string($conn_database,$nickname)."',
             '".mysqli_real_escape_string($conn_database,$pass_criptata)."',
             '$email',
             '$file_name');
    ";

    mysqli_query($conn_database,$query)or die ("Something went wrong!".$conn_database->error);
    header("Location:../index.php");
?> 
