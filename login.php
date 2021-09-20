<?php    
	if(isset($_COOKIE['userID'])){
		$_SESSION['name']=$_COOKIE['userID'];
        header("Location: index.php");
	}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Login</title>
        <link rel="stylesheet" type="text/css" href="style/style.css?version=25">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <p id='youtsapp'>YOUTSAPP</p>
        <form class='box' action='db/login.php' method='POST'>
            <h1>Login</h1>
            <input type='text' name='user' placeholder='Username or email' maxlength="32">
            <input type='password' name='pass' placeholder='Password' maxlength="16">
            <input type='submit' class='buttons_index' name='ok' value='Login'>
            <a href='signup.php' class='buttons_index'>Sign up!</a>
        </form>
    </body>
</html>