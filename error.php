<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Errore</title>
        <link rel="stylesheet" type="text/css" href="stile.css?version=">
    </head>
    <body>
        <div class='box'>
            <h1>Errore</h1>
            <?php
                switch ($_GET['errore']) {
                    case 'user':
                        echo"<p style='color:white'>
                                Account non esistente!
                            </p>
                            <a href='signup.php' id='iscriviti'>
                                Non sei iscritto? Iscriviti!
                            </a>";
                        break;
                        
                    case 'file':
                        echo"<p style='color:white'>
                                File immagine non caricato correttamente!
                            </p>";
                        break;

                    case'pass':
                        echo"<p style='color:white'>
                                Credenziali erratte!
                            </p>";
                        break;

                    case'conn':
                        echo"<p style='color:white'>
                                Server non raggiungibile!
                            </p>";
                        break;

                    case 'db':
                        echo"<p style='color:white'>";
                        if(isset($_GET['tabella']))
                            echo"Errore nella tabella $_GET[tabella]:<br>";
                        echo"      $_GET[erroredb]
                            </p>";
                        break;
                }
                echo"
                        <a href='index.php' id='accedi'>
                        Accedi!
                        </a>";
            ?>
            
        </div>
    </body>
</html>