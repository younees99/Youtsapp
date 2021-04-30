<?php
    include('db.php');
    $db=new db("localhost","root","root","youtsapp");
    //Creazione tabella utente
    $tabella="utente";
    $query='create table if not exists '.$tabella.'(        
        userID INT(100) primary key auto_increment,
        user VARCHAR(32),
        nickname VARCHAR(100),
        password VARCHAR(100),
        email VARCHAR(200),
        url_immagine VARCHAR(100),
        ultimo_accesso timestamp
    )engine=InnoDB';
    $db->query($query);

    //Creazione tabella messaggio
    $tabella="messaggio";
    $query='create table if not exists '.$tabella.'( 
        messID INT(100) primary key auto_increment,
        data timestamp,
        text VARCHAR(200),
        sorgente INT(100),
        destinazione INT(100),
        stato INT(1),
        foreign key (sorgente) references utente(userID),  
        foreign key (destinazione) references utente(userID)  
    )engine=InnoDB';
    $db->query($query);

    //Creazione tabella gruppo
    $tabella="gruppo";
    $query='create table if not exists '.$tabella.'( 
        groupID INT(100) primary key auto_increment,
        creazione timestamp,
        nome VARCHAR(50),
        url_immagine VARCHAR(100),
        utenteID INT(100),
        messaggioID INT(100),
        foreign key (utenteID) references utente(userID),  
        foreign key (messaggioID) references messaggio(messID)
    )engine=InnoDB';
    $db->query($query);
?>