<?php    
	if(isset($_COOKIE['userID'])){
		$_SESSION['name']=$_COOKIE['userID'];
        header("Location: home.php");
	}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Login</title>
        <link rel="stylesheet" type="text/css" href="stile.css?version=425">
    </head>
    <body>
        <p id='youtsapp'>YOUTSAPP</p>
        <form class='box' action='db/login.php' method='POST'>
            <h1>Login</h1>
            <input type='text' name='user' placeholder='Username o email' maxlength="32">
            <input type='password' name='pass' placeholder='Password' maxlength="16">
            <input type='submit' name='ok' value='Login'>
            <a href='signup.php'>Non sei iscritto? Iscriviti!</a>
        </form>
    </body>
</html>