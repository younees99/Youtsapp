<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Sign-up</title>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="../style/style.css?version=25">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <form class='box' name='mioForm' action='db/signupdatabase.php' method='POST' onsubmit='return validaForm(this);'  enctype="multipart/form-data">
            <h1>Sign-up</h1>
            <input type='text' name='user' onchange="checkUser(this);" placeholder='Username'>
            <input type='text' name='email' onchange="checkEmail(this);" placeholder='Email'>
            <input type='password' name='pass' onchange="checkPassword(this);" placeholder='Password'>
            <input type='password' name='conf_pass' onchange="checkPassword(this);" placeholder='Confirm password'>
            <label for="file-upload" id='label_upload' class='buttons_index'>
                Upload your photo! <i class="fa fa-upload" aria-hidden="true"></i>
            </label>
            <input id="file-upload" type="file" name="uploaded_image" accept="image/*" onchange="fileUploaded(this)">
            <input type='submit' name='ok' value='Sign-up' class='buttons_index'>            
            <a href='index.php' class="buttons_index">Login!</a>
        </form>
        <script src="scripts/signup_control.js"></script>
    </body>
</html>