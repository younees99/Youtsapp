<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Sign-up</title>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" type="text/css" href="stile.css?version=25">
    </head>
    <body>
        <form class='box' name='mioForm' action='db/signupdatabase.php' method='POST' onsubmit='return validaForm(this);'  enctype="multipart/form-data">
            <h1>Sign-up</h1>
            <input type='text' name='user' onchange="checkUser(this);" placeholder='Username'>
            <input type='text' name='nickname' onchange="checkNickname(this);" placeholder='Nickname'>
            <input type='text' name='email' onchange="checkEmail(this);" placeholder='Email'>
            <input type='password' name='pass' onchange="checkPassword(this);" placeholder='Password'>
            <input type='password' name='conf_pass' onchange="checkPassword(this);" placeholder='Confirm password'>
            <label for="file-upload" id='label_upload' class='buttons_index'>
                Upload your photo! <i class="fa fa-upload" aria-hidden="true"></i>
            </label>
            <input id="file-upload" type="file" name="uploaded_image" accept="image/*"/ onchange="fileUploaded(this)">
            <input type='submit' name='ok' value='Iscriviti' class='buttons_index'>            
            <a href='index.php' class="buttons_index">Sei già iscritto? Accedi!</a>
        </form>
        <script>

            function validaForm(form){       
                user=form["user"];
                nickname=form["nickname"];
                email=form["email"];
                pass=form["pass"];
                conf_pass=form["conf_pass"];     
                valid_form=false;   

                valid_user=checkUser(user);

                valid_name=checkNickname(nome);

                valid_email=checkEmail(email);
                
                valid_pass=checkPassword(pass);

                equal_pass=checkEqualPass(pass,conf_pass);

                valid_form=valid_user&&checkNickname&&valid_email&&valid_pass&&equal_pass;

                return valid_form;
            }

            function checkNickname(nickname){
                check=validNickame(nickname.value);      
                colorBorders(check,nickname);
                return check;
            }

            function checkUser(user){
                check=validUser(user.value);               
                colorBorders(check,user);
                return check;
            }

            function validUser(user) {
                var re= new RegExp("^[A-Za-z][A-Za-z0-9_-]{4,32}$");
                return re.test(user);
            }

            function validNickame(nome){
                var re= new RegExp("^[A-Za-z][A-Za-z0-9_ ]{1,100}$");
                return re.test(nome);
            }

            function checkEqualPass(pass,conf_pass){
                check=true;  
                if(pass.value.length>0){                                              
                    if(pass.value==conf_pass.value){
                        check=true;
                        colorBordersBlue(pass);
                        colorBordersBlue(conf_pass); 
                    }
                    else{
                        colorBordersRed(pass);
                        colorBordersRed(conf_pass);  
                        alert("Attention! The password must be equal");
                        check=false;
                    }
                } 
                
                return check;
            }


            function checkPassword(pass){
                valida=passwordValida(pass.value);               
                colorBorders(valida,pass);
                return valida;
            }

            function passwordValida(password) {
                var re= new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*\.\,\'])(?=.{8,})");
                console.log(re.test(password));
                return re.test(password);
            }

            function checkEmail(email){
                valida=emailValida(email.value);
                colorBorders(valida,email);
                return valida;
            }

            function emailValida(email) {
                const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }

            function colorBorders(bool,campo){
                if(!bool)
                    colorBordersRed(campo);
                else
                    colorBordersBlue(campo);
            }

            
            function colorBordersRed(campo) {
                campo.style.borderColor="red";
            }

            
            function colorBordersBlue(campo) {
                campo.style.borderColor="#3498db";
            }


            
            function controllaCampoVuoto(campo){
                vuoto=false;
                if(campo.value.length==0)
                    vuoto=true;
                colorBorders(vuoto);
                return vuoto;
            }

            function fileUploaded(input_file){
                var val_uploaded_image=input_file.value;
                var inizioNomeFile=val_uploaded_image.lastIndexOf("\\");
                val_uploaded_image=val_uploaded_image.substr(inizioNomeFile+1);
                document.getElementById("label_upload").innerHTML=val_uploaded_image;
            }
        </script>
    </body>
</html>