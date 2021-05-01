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
                switch ($_GET['error']) {
                    case 'user':
                        echo"<p style='color:white'>
                                Account not found!
                            </p>
                            <a href='signup.php' id='iscriviti'>
                                Non sei iscritto? Iscriviti!
                            </a>";
                        break;
                        
                    case 'file':
                        echo"<p style='color:white'>
                                File not uploaded!
                            </p>";
                        break;

                    case'pass':
                        echo"<p style='color:white'>
                                Wrong credentials!
                            </p>";
                        break;

                    case'conn':
                        echo"<p style='color:white'>
                                Server avialable!
                            </p>";
                        break;

                    case 'db':
                        echo"<p style='color:white'>";
                        if(isset($_GET['tabella']))
                            echo"Sql error: $_GET[tabella]:<br>";
                        echo"      $_GET[erroredb]
                            </p>";
                        break;
                }
                echo"
                        <a href='index.php' class='link_index' id='accedi'>
                        Login!
                        </a>";
            ?>
            
        </div>
    </body>
</html>