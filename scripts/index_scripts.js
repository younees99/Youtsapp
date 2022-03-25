function showResults(str) {
    if (str.length == 0) {
        document.getElementById("tableResults").innerHTML = "";
        return;
    } 
    else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) 
                loadSearch(JSON.parse(this.responseText))
            }
        xmlhttp.open("GET", "db/ajax_requests.php?name="+str+"&id="+session_id+"choice='search'", true);
        xmlhttp.send();
    }
}

function requestConversation(chat_type,chatID){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200)
            loadConversation(JSON.parse(this.responseText))
        }
    xmlhttp.open("GET", "db/ajax_requests.php?chat_type="+chat_type+"&chatID="+chatID+"&id="+session_id+"choice='conv'", true);
    xmlhttp.send();
}

function requestHeader(chat_type,chatID){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) 
            loadHeader(JSON.parse(this.responseText),chat_type)
        }
    xmlhttp.open("GET", "db/ajax_requests.php?chat_type="+chat_type+"&chatID="+chatID+"&id="+session_id+"choice='head'", true);
    xmlhttp.send();
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
    document.getElementById("createGroupForm").style='display: none;';
    document.getElementById("profileForm").style='display: none;';
}

function createGroup() {
    document.getElementById("overlay").style='display: block;';
    document.getElementById("searchForm").style='display: none;';
    document.getElementById("createGroupForm").style='display: block;';
    document.getElementById("profileForm").style='display: none;';
}

function close(){
    document.getElementById("overlay").style='display: none;';
}

function fileUploaded(input_file){
    var val_uploaded_image=input_file.value;
    var inizioNomeFile=val_uploaded_image.lastIndexOf("\\");
    val_uploaded_image=val_uploaded_image.substr(inizioNomeFile+1);
    document.getElementById("label_upload").innerHTML=val_uploaded_image;
}

function showOnlineUsers() {
        if(users_online){
            for (let index = 0; index < users_online.length; index++) {
                printLog(users_online[index],"online");
                if(users_online[index]==to_user_id)
                    printLastSeen(users_online[index],"online");
                }	
        }
}

function printLog(id,val_log) {
    var val_propic;
    var val_propic_from_list;
    var val_propic_from_chat;
    var propic=document.getElementById("propic");
    var propic_from_list=document.getElementById("propic_from_list"+id);
    var propic_from_chat=document.getElementsByClassName("propic_from_chat"+id);

    if(propic){
        if(val_log=="online")
            val_propic="#00ff33";					
        else
            val_propic="#191919";					
        if(id==to_user_id)					
            propic.style.border="solid 2.5px"+val_propic;						
    }

    if(propic_from_list){
        if(val_log=="online")
            val_propic_from_list="#00ff33";
        else
            val_propic_from_list="#333333";	
        propic_from_list.style.border="solid 2.5px"+val_propic_from_list;	
    }	

    if(propic_from_chat){
        for(var i=0;i<propic_from_chat.length;i++){
            if(val_log=="online")
                val_propic_from_chat="#00ff33";
            else
                val_propic_from_chat="#000000";	
            propic_from_chat[i].style.border="solid 2.5px"+val_propic_from_chat;
        }
    }

}			

function incrementPrintCountOnline(){
    var count= document.getElementById("count_online");
    if(count){
        var int_count= parseInt(count.textContent, 10)+1;
        count.innerHTML=int_count;
    }
}

function decrementPrintCountOnline(){
    var count= document.getElementById("count_online");
    if(count){
        var int_count= parseInt(count.textContent, 10)-1;
        count.innerHTML=int_count;
    }
}			

function updateScroll(){
    var messages=document.getElementById("messages");
    var output=document.getElementById("output");
    if(messages&&output){
        output.scrollTop = messages.offsetHeight;
    }
}

function printMessage(json){
    var message;
    var source;
    var icon='';
    var stampa=false;
    console.log(json.from_id);
    if(json.from_id==session_id){
        source="right";
        stampa=true;
    }
                    
    else
        source="left";
    
    message="<tr><td>";
    if(json.destination_type=='destination_group'){
        if(source=='left'){
            var url="src/profile_pictures/"+json.image_url;
            message+="<div class='propic_from_chat propic_from_chat"+json.from_id+"'";
            message+="style='background-image:url("+url+"); border:2.5px solid #00ff33'></div>";
        }
        if(json.to_id==to_group_id)
            stampa=true;            
    }
    else if(json.from_id == to_user_id)
            stampa=true;
            
    message+="<div class='"+source+"'>";
    if(source=='left'&&json.destination_type=='destination_group')
        message+="<p class='message_source'><b>"+json.from_username+"</b></p>";
    message+="<p class='message_value'>"+json.msg+"</p>";
    message+="<span class='time-"+source+"'>"+json.time+" </span>";
    message+=icon+"</div></td></tr>";
    if(source)
        if(stampa)
            document.getElementById("messages").innerHTML+=message;
}

function printPreview(json){
    var message;
    var source_type
    var source;
                
}

function sendMessage(){
    var chat_msg = inputmessage.value;
    if(chat_msg.length>1){
        websocket_server.send(
            JSON.stringify({
                'type':'chat',
                'from_id':session_id,
                'to_id':to_id,
                'chat_msg':chat_msg.trim(),
                'destination_type':destination_type
            })
        );
        inputmessage.value='';
        inputmessage.blur();
        updateScroll();
    }
}

// Websocket			
var websocket_server = new WebSocket("ws://"+server_name+":8080/");

websocket_server.onopen = function(e){
    document.getElementById("loading_div").style='display: none;';	
    document.getElementById("main_div").style='display: block;';	
    if(document.getElementById("propic"))
            document.getElementById("footer_form").style='display: flex;';	

    websocket_server.send(
        JSON.stringify({
            'type':'socket',
            'user_id':session_id
        })
    );
    showOnlineUsers();
    updateScroll();	
};

/*	
websocket_server.onerror = function(e) {
    window.location="error.php?error=conn";
}*/

//Printing a message when i recieve it
websocket_server.onmessage = function(e){
    var json = JSON.parse(e.data);
    switch(json.type){
        case 'chat':
            printMessage(json);
            //printPreview(json);
            updateScroll();
            break;
        
        case 'connected':
            var id=json.user_id;	
            printLog(id,"online");
            printLastSeen(id,"online");
            incrementPrintCountOnline();
            break;
        
        case 'disconnected':
            var id=json.user_id;
            printLog(id,"offline");
            printLastSeen(id,"offline");
            decrementPrintCountOnline();
            break;
        
        case 'typing':
            if(json.from_id == to_user_id){
                    if(json.source)
                        log.innerHTML=json.source+' is typing...';
                    
                    else
                        log.innerHTML='Is typing...';
                                            
            }
            break;
        
        case 'not_typing':
            if(json.from_id== to_user_id)
                log.innerHTML='Online';
            break;
        
        case 'date':
            var date="<tr><td align='center'><p class='print_date'>"+json.date+"</p></td></tr>";														
            document.getElementById("messages").innerHTML+=date;
            break;
    }
}

var inputmessage = document.getElementById("input_message");		
var button=document.getElementById("send_message");


//Events sending a message
if(inputmessage){
    inputmessage.addEventListener('keyup',function(e){
        if(!e.shiftKey){
            sendMessage();
            updateScroll();
        }
    });	
}	

if(button){
    button.addEventListener('click',function(e){
        sendMessage();
        button.blur();
    });	
}

        
// Events typing a message
if(inputmessage){
    inputmessage.addEventListener('focus',function(e){
        websocket_server.send(
            JSON.stringify({
                'type':'typing',
                'from_id':session_id,
                'to_id': to_user_id,
                'destination_type': destination_type
            })
        );				
    });
}

// Events stop typing a message
if(inputmessage){
    inputmessage.addEventListener('blur',function(e){
        websocket_server.send(
            JSON.stringify({
                'type':'not_typing',
                'from_id':session_id,
                'to_id':to_user_id,
                'destination_type':destination_type
            })
        );					
    });
}

function printLastSeen(id,val_log){	
    var log=document.getElementById("log");
    if(log){
        if(id==to_user_id){
        if(val_log=="online")			
            log.innerHTML="Online";
        else
            log.innerHTML=last_seen;		
        }	
    }				
}

function loadChats(jsonChats){
    var print_chats = "";    
    for(let chat of jsonChats){								
        chatID = chat.chatID;
        chat_type = chat.chat_type;	
        chatName = chat.chatName;	
        chatImage = "../src/profile_pictures/"+chat.chatImage;	
        mess_text=chat.mess_text;	
        date = new Date(chat.date_time)
        date_time= date.getHours() + ":" + date.getMinutes();	

        print_chats += "<li class='select_chat'>";
        print_chats += "<button class='select_chat'";
        print_chats += "onClick='requestConversation("+chat_type+","+chatID+")'>";
        print_chats += "<div class='select_chat'>";
        print_chats += "<div class='propic_from_list'";
        if(chat_type=='user')
            print_chats += "id='propic_from_list"+chatID+"'";
        print_chats += "style='background-image:url(\""+chatImage+"\");'>";
        print_chats += "</div> ";
        print_chats += "<p class='chat_name'>"+chatName+"</p>";
        print_chats += "<span class='mess_preview'>"+mess_text+"</span>";
        print_chats += "<span class='time_preview'>"+date_time+"</span>";
        print_chats += "</div></button></li>";
    }
    document.getElementById("chats").innerHTML = print_chats;
}


function loadConversation(jsonMessages){
    var print_conversation = "";
    for(let message of jsonMessages){

    }

    document.getElementById("output").innerHTML = this.responseText;
}

function loadHeader(json,chat_type){
    var print_conversation = "";
    if(chat_type == "user"){
        username = json.username;
        image_url = "../src/profile_pictures/" + json.image_url;
        is_typing = !!json.is_typing;
        is_online = json.is_online;
        last_seen = json.last_seen;
        
        print_conversation += '<button onClick="exitChat()" id="backButton">';
        print_conversation += '<i class="fa fa-chevron-left fa-2x" aria-hidden="true" ';
        print_conversation += 'style="color: white;padding-top: 10px;"></i></button>';
        print_conversation += "<div id='propic' style='background-image:url("+image_url;
        print_conversation += ");'></div><p id='username'>"+username+"</p><p id='log'>";
        if(is_typing)
            print_conversation += 'Is typing...';
        else
            print_conversation += last_seen;
        print_conversation += '</p>';
    }
    else{
        group_name = json.username;
        image_url = "../src/profile_pictures/" + json.image_url;
        online_users = json.online_users;
        
        print_conversation += '<button onClick="exitChat()" id="backButton">';
        print_conversation += '<i class="fa fa-chevron-left fa-2x" aria-hidden="true" ';
        print_conversation += 'style="color: white;padding-top: 10px;"></i></button>';
        print_conversation += "<div id='propic' style='background-image:url("+image_url;
        print_conversation += ");'></div><p id='username'>"+username+"</p><p id='log'>";
        print_conversation += 'Online users: <span id="count_online">'+online_users;
        print_conversation += '</span></p>';
    }

    document.getElementById("header_chat").innerHTML = this.responseText;
}

//function printLastSeen(dateTime){}
         
function loadSearch(jsonChats) {
    var print_chats = "";    
    for(let chat of jsonChats){								
        chatID = chat.chatID;
        chat_type = chat.chat_type;	
        chatName = chat.chatName;	
        chatImage = "../src/profile_pictures/"+chat.chatImage;	
        mess_text=chat.mess_text;	
        date = new Date(chat.date_time)
        date_time= date.getHours() + ":" + date.getMinutes();	

        print_chats += "<li class='select_chat'>";
        print_chats += "<button class='select_chat'";
        print_chats += "onClick='requestConversation("+chat_type+","+chatID+"); close();'>";
        print_chats += "<div class='select_chat'>";
        print_chats += "<div class='propic_from_list' ";
        print_chats += "style='background-image:url(\""+chatImage+"\");'>";
        print_chats += "</div> ";
        print_chats += "<p class='chat_name'>"+chatName+"</p>";
        print_chats += "</div></button></li>";
    }
    if(jsonChats.length==0)
        print_chats = 'No group or user found';
    document.getElementById("tableResults").innerHTML = print_chats;
}