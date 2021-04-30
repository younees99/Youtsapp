<?php
    include("config.php");
    $user=$_POST['user'];
    $nickname=$_POST['nickname'];
    $email=$_POST['email'];
    $pass=$_POST['pass'];
    $tmp_name=$_FILES['immagine']['tmp_name'];
    $name=$_FILES['immagine']['name'];

    if(!file_exists($tmp_name)||!is_uploaded_file($tmp_name)){
        $nome_file="blank_profile_picture.png";
    }

    else{
        $ext_accettate=array("gif", "jpeg", "jpg", "png");
        $ext = end(explode(".",$name));
        if(in_array($ext,$ext_accettate)){        
            $numero=rand(100000,999999);
            $no_spaces_nick=str_replace(" ","",$nickname);
            $nome_file=$user."_".$no_spaces_nick."_".$numero.".".$ext;
            move_uploaded_file($tmp_name,"../src/immagini_profilo/$nome_file");    
        }
        else{
            header("Location: ../error.php?errore=file");
        }
    }

    $pass_criptata = crypt(md5($pass),md5($user)); // cripto la password 

    $query="
        insert into utente(user,nickname,password,email,url_immagine) values
            ('".mysqli_real_escape_string($conn_database,$user)."',
             '".mysqli_real_escape_string($conn_database,$nickname)."',
             '".mysqli_real_escape_string($conn_database,$pass_criptata)."',
             '$email',
             '$nome_file');
    ";

    mysqli_query($conn_database,$query)or die ("Qualcosa è andato storto!".$conn_database->error);
    header("Location:../index.php");
?> 
