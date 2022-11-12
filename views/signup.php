<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Sign-up</title>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="../style/style.css?version=654">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body>
        <form class='box' name='mioForm' action='../db/signupdatabase.php' method='POST' enctype="multipart/form-data" onsubmit='return validateForm(this);'  >
            <h1>Sign-up</h1>
            <label for="file-upload" id='label_upload'>                	
                <div class="container">
                    <img id="profile_image" class='propic_from_form' src="../src/profile_pictures/blank_profile_picture.png"/>
                    <div class="overlay_propic">
                        <div class="loadPhoto">
                            <i class="fa fa-camera" aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </label><br>
            Upload your photo!
            <input id="file-upload" type="file" name="uploaded_image" accept="image/*" onchange="previewImage(this)">
            <input type='text' name='user' onchange="checkUser(this);" onchange="avialableTag(this);" placeholder='Username'>
            <p id="unavialable_username" class="error">Unavialable username*</p>
            <p id="username_not_valid" class="error">Username must start with a letter, contain more than 6 and less than 32 characters*</p>
            <input type='text' name='nickname' onchange="checkNickname(this);" placeholder='Nickname'>
            <p id="nickname_not_valid" class="error">Nickname must start with a letter, contain more than 6 and less than 32 characters*</p>
            <input type='text' name='email' onchange="checkEmail(this);" placeholder='Email'>
            <p id="email_not_valid" class="error">Wrong e-mail format</p>
            <input type='password' id='pass' name='pass' onchange="checkPassword(this);" placeholder='Password'>
            <p id="password_not_valid" class="error">Password must have at least eight characters, at least one uppercase letter, one lowercase letter, one number and one special character</p>
            <p class="error password_not_same">Passwords don't match</p>
            <input type='password' name='conf_pass' onkeyup="samePassword(this);" placeholder='Confirm password'>   
            <p class="error password_not_same">Passwords don't match</p>        
            <input type='submit' name='ok' value='Sign-up' class='buttons_index'> 
            <a href='index.php' class="buttons_index">Login!</a>
        </form>
        <script src="../scripts/signup_control.js?t=444"></script>
    </body>
</html>