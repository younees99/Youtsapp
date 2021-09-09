<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Errore</title>
        <link rel="stylesheet" type="text/css" href="style.css?version=78">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div class='box'>
            <h1>Error</h1>
            <?php
                switch ($_GET['error']) {
                    case 'user':
                        echo"<p style='color:white'>
                                Account not found!
                            </p>
                            <a href='signup.php' id='iscriviti' class='buttons_index'>
                                Sign-up!
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
                                Connection failed!
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
                        <a href='index.php' class='buttons_index' id='log_in'>
                        Try again!
                        </a>";
            ?>
            
        </div>
    </body>
</html>