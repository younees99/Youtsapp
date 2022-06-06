var global_to_id;
var global_destination_type;
var users_typing_per_group;
var online_users_per_group;

//AJAX functions
function requestResults(search) {
    if (search.length == 0) {
        document.getElementById("tableResults").innerHTML = "";
        return;
    } 
    else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) 
                showRequest(JSON.parse(this.responseText))
            }
        xmlhttp.open("GET", "../db/ajax_requests.php?name="+search+"&choice=search", true);
        xmlhttp.send();
    }
}

function requestHeader(chat_type,chatID){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) 
            showHeader(JSON.parse(this.responseText),chat_type)
        }
    xmlhttp.open("GET", "../db/ajax_requests.php?chat_type="+chat_type+"&chatID="+chatID+"&choice=head", true);
    xmlhttp.send();
}




function loadChat(jsonChat){
    var print_chats = "";    
    for(let chat of jsonChat){								
        chatID = chat.chatID;
        chat_type = chat.chat_type;	
        chatName = chat.chatName;	
        is_online = Boolean(parseInt(chat.is_online));  
        chatImage = "../src/profile_pictures/"+chat.chatImage;	
        mess_text=chat.mess_text;	
        date = new Date(chat.date_time)
        date_time= date.getHours() + ":" + date.getMinutes();	        
        print_chats += "<li>";
        print_chats += "<button class='select_chat'";
        print_chats += 'onClick=\'showConversation("'+chat_type+'","'+chatID+'");';
        print_chats += 'requestHeader("'+chat_type+'","'+chatID+'");\'>';
        print_chats += "<div class='select_chat'>";
        print_chats += "<div class='propic_from_list'";
        if(chat_type=='user')
            print_chats += "id='propic_from_list"+chatID+"'";
        print_chats += "style='background-image:url(\""+chatImage+"\");";
        border = "#333333";
        if(chat_type=='user'&&is_online==true)
            border = "#00ff33";
        print_chats += "border:2.5px solid "+border+"'></div>";
        print_chats += "<p class='chat_name'>"+chatName+"</p>";
        print_chats += "<span class='mess_preview'>"+mess_text+"</span>";
        print_chats += "<span class='time_preview'>"+date_time+"</span>";
        print_chats += "</div></button></li>";
    }
    document.getElementById("chats").innerHTML = print_chats;
}

function printMessage(message){
    mess_text= message.mess_text.replace("\\n","\n");
    chat_type = message.destination_type;
    from_id = message.from_id;
    destination = message.to_id;
    time = message.time;
    messages_id = message.messages_id;
    print_message = '';

    if(from_id == session_id){
        print_message += "<tr><td><div id='message_"+messages_id+"' class='right'> <p class='message_value'>"+mess_text+"</p>";
        print_message += '<i class="fa fa-circle-o circle_unchecked unread_'+chat_type+'_'+destination+'" aria-hidden="true"></i>';   
        print_message += "<span class='time-right'>"+time+"</span></div></td></tr>";             
    } 

    else{
        destination = from_id;
        print_message += "<tr><td>";
        if(chat_type == 'group'){
            destination = to_id;
            user = message.from_id;
            username = message.from_nickname;
            var image_url = "../src/profile_pictures/"+message.image_url;
            print_message += "<div class='propic_from_chat propic_from_chat"+user+"' ";
            print_message += "style='background-image:url(" +image_url+");";
            print_message += "border:2.5px solid rgb(0, 255, 51)'></div>";
        }
        print_message += "<div id='message_"+messages_id+"' class='left'>";
        if(chat_type == 'group')
            print_message += "<p class='message_source'><b>"+username+"</b></p>";
        print_message += "<p class='message_value'>"+mess_text+"</p>";
        print_message += "<span class='time-left'>"+time+"</span>";
        print_message += '</div></td></tr>';
    }
    document.getElementById("chat"+chat_type+"_"+destination).innerHTML+=print_message;
    if(chat_type == global_destination_type && from_id == global_to_id){
        if(global_destination_type == "user")   
            readMessages(chat_type,from_id);
        else
            readMessages(chat_type,message.to_id);
    }

    var today_span = document.getElementById("today_span_"+chat_type+"_"+destination);
    if(today_span){
        today_span.style.display="inline-block";
        today_span.removeAttribute('id');
    }
}

function showConversation(chat_type,chatID){
    global_to_id = chatID;
    global_destination_type = chat_type;

    document.getElementById("main").style.display="block";

    document.getElementById("messages").style.display="table";    
    document.getElementById("select_chat_alert").style.display='none';        
    var chats = document.getElementsByClassName("chat");

    for(chat of chats){
        chat.style.display = "none";
    } 
    document.getElementById("chat"+chat_type+"_"+chatID).style.display = "table";
    document.getElementById("footer_form").style.display='flex';	
    document.getElementById("output").style.overflowY='scroll';
    

    console.log("Selected "+chat_type+" id "+chatID);
    var intFrameWidth = window.innerWidth;
    if(intFrameWidth<704)
        document.getElementById("right_main_div").style.display = 'block';   
    updateScroll();
    removeBadge(chat_type,chatID);
    readMessages(chat_type,chatID);
    
}

function showHeader(jsonHeader,chat_type){
    var print_header = "";
    for(let json of jsonHeader){
        if(chat_type == "user"){
            nickname = json.nickname;
            image_url = "../src/profile_pictures/" + json.image_url;
            is_typing = Boolean(parseInt(json.is_typing));
            is_online = Boolean(parseInt(json.is_online));
            last_seen = json.last_seen;

            print_header += '<button onClick="exitChat()" id="backButton" class="headerButtons">';
            print_header += '<i class="fa fa-chevron-left fa-2x" aria-hidden="true" ';
            print_header += 'style="color: white;padding-top: 10px;"></i></button>';
            print_header += "<div id='propic' style='background-image:url("+image_url+");";
            if(is_online)
                print_header += "border:2.5px solid #00ff33";
            print_header += "'></div>";
            print_header += "<table id='header_table'><tr><td>";
            print_header += "<p id='nickname'>"+nickname+"</p></td></tr><tr><td><p id='log'>";
            if(is_typing)
                print_header += 'Is typing...';
            else if(is_online)
                print_header += "Online";
            else
                print_header += dateTimeToText(last_seen);
            print_header += '</p></td></tr></table>';
        }
        else{
            group_name = json.group_name;
            image_url = "../src/profile_pictures/" + json.image_url;
            online_users = json.online_users;
            
            print_header += '<button onClick="exitChat()" id="backButton" class="headerButtons">';
            print_header += '<i class="fa fa-chevron-left fa-2x" aria-hidden="true" ';
            print_header += 'style="color: white;padding-top: 10px;"></i></button>';
            print_header += "<div id='propic' style='background-image:url("+image_url;
            print_header += ");'></div>";
            print_header += `<table id='header_table'><tr><td><p id='nickname'>${group_name}</p></td></tr>`;
            print_header += "<tr><td><p id='log'>";
            print_header += `Online users: <span id="count_online">${online_users}`;
            print_header += `</span></p></td></tr></table>`;
        }
    }
    document.getElementById("header_chat").innerHTML = print_header;
    document.getElementById("header_chat").style = 'block';
    
}

function previewImage(input) {
    const [file] = input.files;
    profile_image = document.getElementById("profile_image");
    if (file) {
        profile_image.src = URL.createObjectURL(file);
    }
}

function showRequest(jsonChats) {
    var print_chats = "";    
    for(let chat of jsonChats){								
        chatID = chat.chatID;
        chat_type = chat.chat_type;	
        chat_name = chat.chat_name;	
        chat_tag = chat.chat_tag;	
        chat_image = "../src/profile_pictures/"+chat.image_url;	
        mess_text = chat.mess_text;	

        print_chats += "<li class='select_chat'>";
        print_chats += "<button class='select_chat'";
        print_chats += 'onClick=\'showConversation("'+chat_type+'","'+chatID+'");';
        print_chats += 'requestHeader("'+chat_type+'","'+chatID+'");';
        print_chats += 'closeOverlay(); '
        print_chats += 'document.getElementById("input_search").value = "";';
        print_chats += 'document.getElementById("tableResults").innerHTML = "";\'>';
        print_chats += "<div class='select_chat'>";
        print_chats += "<div class='propic_from_list' ";
        print_chats += "style='background-image:url(\""+chat_image+"\");'>";
        print_chats += "</div> ";
        print_chats += "<p class='chat_name'>"+chat_name+"</p>";
        print_chats += "<span class='mess_preview'>"+chat_tag+"</span>";
        print_chats += "</div></button></li>";
    }
    if(jsonChats.length==0)
        print_chats = 'No group or user found';
    document.getElementById("tableResults").innerHTML = print_chats;
}


function openProfileMenu(){
    document.getElementById("overlay").style='display: block;';
    document.getElementById("searchForm").style='display: none;';
    document.getElementById("createGroupForm").style='display: none;';
    document.getElementById("profileForm").style='display: block;';
}

function search(){
    document.getElementById("overlay").style='display: block;';
    document.getElementById("searchForm").style='display: block;';
    document.getElementById("input_search").focus();
    document.getElementById("createGroupForm").style='display: none;';
    document.getElementById("profileForm").style='display: none;';
}

function createGroup() {
    document.getElementById("overlay").style='display: block;';
    document.getElementById("searchForm").style='display: none;';
    document.getElementById("createGroupForm").style='display: block;';
    document.getElementById("profileForm").style='display: none;';
}

function exitChat(){
    var intFrameWidth = window.innerWidth;
    document.getElementById("main").style.display="flex";
    if(intFrameWidth<704)
        document.getElementById("right_main_div").style.display = 'none';
    document.getElementById("left_main_div").style.display='block';
    document.getElementById("messages").style.display="none";
    document.getElementById("select_chat_alert").style.display='block';
    document.getElementById("footer_form").style.display='none';	
    document.getElementById("output").style.overflowY='hidden';
    document.getElementById("header_chat").style.display='none';   
    global_to_id = 0;
    global_destination_type = 0;
}

function closeOverlay(){
    document.getElementById("overlay").style.display = 'none';   
}

function fileUploaded(input_file){
    var val_uploaded_image=input_file.value;
    var inizioNomeFile=val_uploaded_image.lastIndexOf("\\");
    val_uploaded_image=val_uploaded_image.substr(inizioNomeFile+1);
    document.getElementById("label_upload").innerHTML=val_uploaded_image;
}

function boolToColor(online){
    if(online)
        val_propic="#00ff33";					
    else
        val_propic="#191919";
    return val_propic;
}     

function turnLedStatus(id,online) {
    var val_propic = boolToColor(online);
    var propic=document.getElementById("propic");

    if(propic){				
        if(id==global_to_id)					
            propic.style.border="solid 2.5px"+val_propic;						
    }	
}	

function turnLedStatusList(id,online){
    var val_propic = boolToColor(online);
    var propic_from_chat=document.getElementsByClassName("propic_from_chat"+id);
    var propic_from_list=document.getElementById("propic_from_list"+id);

    if(propic_from_chat){
        for(var i=0;i<propic_from_chat.length;i++){
            propic_from_chat[i].style.border="solid 2.5px"+val_propic;
        }
    }

    propic_from_list.style.border="solid 2.5px"+val_propic;	
}

function incrementPrintCountOnline(id){
    var count= document.getElementById("count_online");
    if(count != null && global_to_id == id){
        var int_count= parseInt(count.textContent, 10)+1;
        count.innerHTML=int_count;
    }
}

function decrementPrintCountOnline(id){
    var count= document.getElementById("count_online");
    if(count != null && global_to_id == id){
        var int_count= parseInt(count.textContent, 10)-1;
        count.innerHTML=int_count;
    }
}			

function updateScroll(){
    var messages=document.getElementById("messages");
    var output=document.getElementById("output");
    var footer_form=document.getElementById("footer_form");
    if(messages&&output){
        output.scrollTop = messages.offsetHeight - footer_form.offsetHeight;
    }
}

function printTyping(type,is_typing,message){
    source = message.from_nickname;
    from_id = message.from_id;
    to_id = message.to_id;
    source_id = from_id;

    if(type == "group")
        source_id = to_id;

    if(type == "group" && is_typing==true)
        document.getElementById("is_typing_"+type+"_"+source_id).innerHTML=source+" is typing...";

    if(is_typing){ 
        document.getElementById("is_typing_"+type+"_"+source_id).style.display="block";
        document.getElementById("mess_preview_"+type+"_"+source_id).style.display="none";
    }
    else{
        document.getElementById("is_typing_"+type+"_"+source_id).style.display="none";
        document.getElementById("mess_preview_"+type+"_"+source_id).style.display="block";
    }  


}

function printPreview(message){
    var mess_text = message.mess_text;
    var time = message.time;
    var source_type = message.destination_type;
    var source_id = message.from_id;     
    var destination_id = message.to_id;
    var mess_source = message.from_nickname+": ";

    if(source_id == session_id)
        mess_source = "You: "; 
    if(source_type == 'user'){
        if(source_id == session_id){
            li_chat = '#chat_'+source_type+'_'+destination_id;
            mess_preview_span = "mess_preview_"+source_type+"_"+destination_id;
            time_preview_span = "time_preview_"+source_type+"_"+destination_id;
        }    
        else{
            mess_source = ""; 
            li_chat = '#chat_'+source_type+'_'+source_id;
            mess_preview_span = "mess_preview_"+source_type+"_"+source_id;
            time_preview_span = "time_preview_"+source_type+"_"+source_id;
        }
    }
    else{          
        li_chat = '#chat_'+source_type+'_'+destination_id;
        mess_preview_span = "mess_preview_"+source_type+"_"+destination_id;
        time_preview_span = "time_preview_"+source_type+"_"+destination_id;
    }

    document.getElementById(mess_preview_span).innerHTML = mess_source+mess_text;
    document.getElementById(time_preview_span).innerHTML = time;

    moveLiToTop(li_chat);
}

function moveLiToTop(li_chat){    
    ul = document.querySelector('#chats');
    child = ul.querySelector(li_chat);
    ul.removeChild(child);
    ul.insertBefore(child,ul.firstChild);
}

function incrementBadgeCount(message){
    var source_type = message.destination_type;
    var source_id = message.from_id;     
    var destination_id = message.to_id;
    var badge_span_id = "unread_mess_"+source_type+"_"+source_id;
    var badge_span = document.getElementById(badge_span_id);

    if(source_type == "group")
        badge_span_id = "unread_mess_"+source_type+"_"+destination_id;

    if(source_type == "group")
        source_id = destination_id;
        
    if(source_id != global_to_id || source_type != global_destination_type){
        var int_count= parseInt(badge_span.textContent, 10)+1;
        badge_span.innerHTML=int_count;
        if(badge_span.style.display == "none")
            badge_span.style.display = "inline";
    }
    
}

function updateHeaderStatus(online,time){
    var log = document.getElementById("log");
    if(log){
        if(online)
                log.innerHTML = "Online";
        else
            log.innerHTML = dateTimeToText(time);
    }   
}

function updateHeaderTyping(is_typing,json){
    var log = document.getElementById("log");
    var nickname = json.from_nickname;
    if(global_destination_type == "user" && global_to_id == json.from_id){
        if(is_typing)
            log.innerHTML = "is typing...";
        else
            log.innerHTML = "Online";
    }
    /*else if(global_destination_type == "group" && global_to_id == json.to_id){
        if(is_typing)
            log.innerHTML = nickname + "is typing...";
        else
            log.innerHTML = "Online";
    }*/
}





// Websocket			
var websocket_server = new WebSocket("ws://"+server_name+":8080/");


function sendMessage(){
    var inputmessage = document.getElementById("input_message");
    var mess_text = inputmessage.value;
    if(mess_text.length>1){
        websocket_server.send(
            JSON.stringify({
                'type':'chat',
                'from_id':session_id,
                'to_id':global_to_id,
                'mess_text':mess_text.trim(),
                'destination_type':global_destination_type
            })
        );
        inputmessage.value='';
        inputmessage.blur();
        updateScroll();
    }
}

function readMessages(chat_type,chatID){
    websocket_server.send(
        JSON.stringify({
            'type':'read',
            'from_id':session_id,
            'to_id':chatID,
            'destination_type':chat_type
        })
    )
}
websocket_server.onopen = function(e){
    document.getElementById("loading_div").style='display: none;';	
    document.getElementById("main_div").style='display: block;';	

    websocket_server.send(
        JSON.stringify({
            'type':'socket',
            'user_id':session_id
        })
    );
    updateScroll();	
};


websocket_server.onerror = function(e) {
    window.location="error.php?error=conn";
}


websocket_server.onmessage = function(e){
    var json = JSON.parse(e.data);
    console.log(json); 
    switch(json.type){
        case 'chat':
            printMessage(json);
            printPreview(json);
            incrementBadgeCount(json);
            updateScroll();
            break;
        
        case 'connected':
            var id=json.user_id;	
            if(global_destination_type == 'user')
                turnLedStatus(id,true);      
                if(id == global_to_id)
                    updateHeaderStatus(false,json.time); 
            else if(global_destination_type == 'group')            
                incrementPrintCountOnline(id);
            turnLedStatusList(id,true);
            break;
        
        case 'disconnected':
            var id = json.user_id;
            if(global_destination_type == 'user')
                turnLedStatus(id,false);  
                if(id == global_to_id)
                    updateHeaderStatus(false,json.time); 
            else if(global_destination_type == 'group')     
                decrementPrintCountOnline(id);
            turnLedStatusList(id,false);
            break;
        
        case 'typing': 
            var id = json.from_id;
            var type = json.destination_type;  
            printTyping(type,true,json);
            if(global_destination_type != '')
                updateHeaderTyping(true, json);
            break;
        
        case 'not_typing':
            var id = json.from_id;
            var type = json.destination_type;
            if(id== global_to_id && type == global_destination_type && global_destination_type == 'user')
                log.innerHTML='Online';   
            printTyping(type,false,json);
            if(global_destination_type != '')
                updateHeaderTyping(false, json);
            break;

        case 'read':
            var type = json.destination_type;
            var to_id = json.from_id;
            if(type == "group")
                to_id = json.to_id;
            checkMessages(type,to_id);
            break;
    }
}

function checkMessages(type,to_id){
    var unchecked = document.querySelectorAll(".unread_"+type+"_"+to_id);
    for (let i=0;i<unchecked.length;i++){
        unchecked[i].className = "fa fa-check-circle-o circle_checked";
    }
}

function removeBadge(type,to_id){
    document.getElementById("unread_mess_"+type+"_"+to_id).style.display='none';
    document.getElementById("unread_mess_"+type+"_"+to_id).innerHTML='0';
}

function checkAndSend(e){
    if(e.key === "Enter" && !e.shiftKey){
        sendMessage();
        updateScroll();
    }
}

function typingMessage() {
    json = JSON.stringify({
                'type':'typing',
                'from_id':session_id,
                'to_id': global_to_id,
                'destination_type': global_destination_type
            });
    websocket_server.send(json);
}

function notTypingMessage() {
    json = JSON.stringify({
        'type':'not_typing',
        'from_id':session_id,
        'to_id':global_to_id,
        'destination_type':global_destination_type
    });
    websocket_server.send(json);	     
}
    
	

function dateTimeToText(last_seen){
    var print_text;
    var date_time = new Date(last_seen);
    var today = new Date();
    var day = today.getDate() + "";
    var month = (today.getMonth());
    var year = today.getFullYear() + "";
    var hour = today.getHours() + "";
    var minutes = today.getMinutes() + "";

    var months = [ "January", "February", "March", "April", "May", "June", 
           "July", "August", "September", "October", "November", "December" ];
    
    day = checkZero(day);
    month = checkZero(month);
    year = checkZero(year);
    hour = checkZero(hour);
    minutes = checkZero(minutes);

    diffTime = Math.abs(today - date_time);
    diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); // milliseconds * seconds * minutes * hours


    if(date_time.getFullYear()<year){
        print_text = "Last seen "+day+" "+months[month]+" "+year;
    }
    else if(diffDays>2){
        print_text = "Last seen "+day+" "+months[month]+" at "+hour+":"+minutes;
    }
    else if(diffDays==1){
        print_text = "Last seen yesterday at "+hour+":"+minutes;
    }
    else{
        print_text = "Last seen today at "+hour+":"+minutes;
    }
    
    return print_text;
}

function checkZero(data){
    if(data.length == 1)
        data = "0" + data;    
    return data;
}