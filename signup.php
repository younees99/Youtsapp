<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Registrazione</title>
        <link rel="stylesheet" type="text/css" href="stile.css?version=80">
    </head>
    <body>
        <form class='box' name='mioForm' action='db/signupdatabase.php' method='POST' onsubmit='return validaForm(this);'  enctype="multipart/form-data">
            <h1>Iscriviti</h1>
            <input type='text' name='user' onchange="controllaUser(this);" placeholder='Username'>
            <input type='text' name='nickname' onchange="controllaNickname(this);" placeholder='Nickname'>
            <input type='text' name='email' onchange="controllaEmail(this);" placeholder='Email'>
            <input type='password' name='pass' onchange="controllaPassword(this);" placeholder='Password'>
            <input type='password' name='rip_pass' onchange="controllaPassword(this);" placeholder='Ripeti la password'>
            <label for="file-upload" id='label_carica'>
                Carica il tuo file
            </label>
            <input id="file-upload" type="file" name="immagine" accept="image/*"/ onchange="fileCaricato(this)">
            <input type='submit' name='ok' value='Iscriviti'>            
            <a href='index.php' class="link_index">Sei già iscritto? Accedi!</a>
        </form>
        <script>

            function validaForm(form){       
                user=form["user"];
                nickname=form["nickname"];
                email=form["email"];
                pass=form["pass"];
                rip_pass=form["rip_pass"];     
                form_valido=false;   

                user_valido=controllaUser(user);

                nome_valido=controllaNickname(nome);

                email_valida=controllaEmail(email);
                
                pass_valida=controllaPassword(pass);

                pass_uguali=controllaPasswordUguali(pass,rip_pass);

                form_valido=user_valido&&controllaNickname&&email_valida&&pass_valida&&pass_uguali;

                return form_valido;
            }

            function controllaNickname(nickname){
                controllo=nicknameValido(nickname.value);      
                coloraBordi(controllo,nickname);
                return controllo;
            }

            function controllaUser(user){
                controllo=userValido(user.value);               
                coloraBordi(controllo,user);
                return controllo;
            }

            function userValido(user) {
                var re= new RegExp("^[A-Za-z][A-Za-z0-9]{4,32}$");
                return re.test(user);
            }

            function nicknameValido(nome) {
                var re= new RegExp("^[A-Za-z][A-Za-z0-9_ ]{1,100}$");
                return re.test(nome);
            }

            function controllaPasswordUguali(pass,rip_pass){
                controllo=true;  
                if(pass.value.length>0){                                              
                    if(pass.value==rip_pass.value){
                        controllo=true;
                        coloraBordiBlu(pass);
                        coloraBordiBlu(rip_pass); 
                    }
                    else{
                        coloraBordiRosso(pass);
                        coloraBordiRosso(rip_pass);  
                        alert("le password non combaciano");
                        controllo=false;
                    }
                } 
                
                return controllo;
            }

            //Funzione che controlla se la password inserita rispetta le seguenti condizioni:
            //lunghezza tra 8 e 16 caratteri
            //almeno un numero
            //almeno una lettera minuscola
            //almeno una lettera maiuscola
            function controllaPassword(pass){
                valida=passwordValida(pass.value);               
                coloraBordi(valida,pass);
                return valida;
            }

            //Funzione che cerca lettere maiuscole in una stringa
            function passwordValida(password) {
                var re= new RegExp("^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*\.\,\'])(?=.{8,})");
                console.log(re.test(password));
                return re.test(password);
            }

            //Funzione che controlla se il campo email è pieno e l'email è valida
            function controllaEmail(email){
                valida=emailValida(email.value);
                coloraBordi(valida,email);
                return valida;
            }

            //Funzione che rispetti le seguente condizioni: stringa@stringa.stringa
            function emailValida(email) {
                const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }

            //Funzione che in base al valore della booleana 'bool' colora di rosso o del colore predefinito i bordi del campo n questione
            function coloraBordi(bool,campo){
                if(!bool)
                    coloraBordiRosso(campo);
                else
                    coloraBordiBlu(campo);
            }

            //Funzione che colora i bordi di rosso
            function coloraBordiRosso(campo) {
                campo.style.borderColor="red";
            }

            //Funzione che colora i bordi di rosso
            function coloraBordiBlu(campo) {
                campo.style.borderColor="#3498db";
            }


            //Funzione che controlla che un campo sia vuoto e richiama la funzione coloraBordi
            function controllaCampoVuoto(campo){
                vuoto=false;
                if(campo.value.length==0)
                    vuoto=true;
                coloraBordi(vuoto);
                return vuoto;
            }

            function fileCaricato(input_file){
                var valImmagine=input_file.value;
                var inizioNomeFile=valImmagine.lastIndexOf("\\");
                valImmagine=valImmagine.substr(inizioNomeFile+1);
                document.getElementById("label_carica").innerHTML=valImmagine;
                console.log("file caricato");
            }
        </script>
    </body>
</html>