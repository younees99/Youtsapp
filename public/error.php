<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Errore</title>
        <link rel="stylesheet" type="text/css" href="../style/style.css?version=78">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <div class='box'>
            <h1>Error</h1>
            <?php
                $href = "index.php";
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
                        $href='signup.php';
                        break;
                    
                    case 'ext':
                        echo"<p style='color:white'>
                                Extention not supported
                            </p>";
                        $href='signup.php';
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
                }
                echo"<a href='$href' class='buttons_index' id='log_in'>
                        Try again!
                    </a>";
            ?>
            
        </div>
    </body>
</html>