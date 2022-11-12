var available_tag = true;

function validateForm(form){       
    user=form["user"];
    nickname=form["nickname"];
    email=form["email"];
    pass=form["pass"];
    conf_pass=form["conf_pass"];     
    valid_form=false;   

    valid_user=checkUser(user);

    valid_nickname=checkNickname(nickname);

    valid_email=checkEmail(email);
    
    valid_pass=checkPassword(pass);

    equal_pass=checkEqualPass(pass,conf_pass);

    valid_form=valid_user&&valid_email&&valid_pass&&equal_pass&&valid_nickname&&available_tag;

    return valid_form;
}

function checkUser(user){
    var check = true;   
    if(!validUser(user.value)){
        check = false;
        document.getElementById("username_not_valid").style.display = "block";
    }            
    else{
        check = true;
        document.getElementById("username_not_valid").style.display = "none";
    }
    colorBorders(check,user);
    return check;
}

function showAvialibility(user,json) {
    console.log(json);
    var is_taken = json.is_taken;
    check = !is_taken;
    if(is_taken)
        document.getElementById("unavialable_username").style.display = "block";        
    else
        document.getElementById("unavialable_username").style.display = "none";

    colorBorders(user);
    available_tag = check;
}

function validUser(user) {
    var re= new RegExp("^[A-Za-z][A-Za-z0-9_-]{5,32}$");
    return re.test(user);
}

function avialableTag(user) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) 
            showAvialibility(user,this.responseText);
        }
    xmlhttp.open("GET", "../db/ajax_requests.php?tag="+user.value+"&choice=available_tag", true);
    xmlhttp.send();
}

function checkNickname(nickname){
    check=validNickname(nickname.value);               
    colorBorders(check,nickname);
    return check;
}

function validNickname(nickname) {
    var valid = true;
    nickname = nickname.trim();
    if(nickname.length<6 || nickname>30)
        valid = false;
    if(valid)
        document.getElementById("nickname_not_valid").style.display="none";
    else
        document.getElementById("nickname_not_valid").style.display="block";
    return valid;
}

function checkEqualPass(pass,conf_pass){
    check=true;                                               
    if(pass.value==conf_pass.value)
        showPasswordNotEqual(check);
    else{  
        check=false;
        showPasswordNotEqual(check);
    }
    colorBorders(check,pass);    
    colorBorders(check,conf_pass);    
    return check;
}

function samePassword(conf_pass_field){
    pass_field = document.getElementById("pass");
    checkEqualPass(pass_field,conf_pass_field);    
}

function showPasswordNotEqual(same) {
    var spans = document.querySelector(".password_not_same");
    var display = "block";
    if(!same)   display = "none";
    for (let i = 0; i < spans.length; i++) {
        let span = spans[i];
        span.style.display = display;
    }
}

function checkPassword(pass){
    var valid=validPassword(pass.value);     
    if(valid)
        document.getElementById("password_not_valid").style.display="none";
    else
        document.getElementById("password_not_valid").style.display="block";
    colorBorders(valid,pass);
    return valid;
}

function validPassword(password) {
    var re= /^(?=.*[0-9])(?=.*[a-zA-Z])(?=.*[@#$.,;-_:%^&*]).{8,32}$/;
    return re.test(password);
}

function checkEmail(email){
    var valid=emailValida(email.value);
    colorBorders(valid,email);
    return valid;
}

function emailValida(email) {
    const re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

function colorBorders(bool,field){
    if(!bool)
        colorBordersRed(field);
    else
        colorBordersBlue(field);
}


function colorBordersRed(field) {
    field.style.borderColor="red";
}

function previewImage(input) {
    const [file] = input.files;
    profile_image = document.getElementById("profile_image");
    if (file) {
        profile_image.src = URL.createObjectURL(file);
    }
}

function colorBordersBlue(field) {
    field.style.borderColor="#3498db";
}