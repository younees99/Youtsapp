var to_id;
var destination_type;

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
    if(document.getElementById("chats"))
        document.getElementById("chats").innerHTML = print_chats;
}

function printMessage(message){
    console.log(message);
    mess_text= message.mess_text.replace(/(\r\n|\r|\n)/g, '<br>');
    chat_type = message.destination_type;
    from_id = message.from_id;
    to_id = message.to_id;
    time = message.time;
    print_message = '';

    if(from_id == session_id){
        print_message += "<tr><td><div class='right'> <p class='message_value'>"+mess_text+"</p>";
        print_message += "<span class='time-right'>"+time+"</span></div></td></tr>";
    }    
    else{
        print_message += "<tr><td>";
        if(chat_type == 'group'){
            user = message.from_id;
            username = message.from_username;
            var image_url = "../src/profile_pictures/"+message.image_url;
            print_message += "<div class='propic_from_chat propic_from_chat"+user+"' ";
            print_message += "style='background-image:url(" +image_url+");";
            print_message += "border:2.5px solid #333333'></div>";
        }
        print_message += "<div class='left'>";
        if(chat_type == 'group')
            print_message += "<p class='message_source'><b>"+username+"</b></p>";
        print_message += "<p class='message_value'>"+mess_text+"</p>";
        print_message += "<span class='time-right'>"+time+"</span></div></td></tr>";
    }
    document.getElementById("chat"+chat_type+"_"+to_id).innerHTML+=print_message;
}

function showConversation(chat_type,chatID){
    if(document.getElementById("messages") != null)
        document.getElementById("messages").style.display="table";
    
    if(document.getElementById("select_chat_alert") != null)
        document.getElementById("select_chat_alert").style.display='none';
        
    if(document.getElementsByClassName("chat") != null)
        var chats = document.getElementsByClassName("chat");
    for(chat of chats){
        chat.style.display = "none";
    } 
    if(document.getElementById("chat"+chat_type+"_"+chatID)!= null)
        document.getElementById("chat"+chat_type+"_"+chatID).style.display = "table";
    if(document.getElementById("footer_form")!= null)
        document.getElementById("footer_form").style.display='flex';	
    if(document.getElementById("output")!= null)
        document.getElementById("output").style.overflowY='scroll';
    to_id = chatID;
    destination_type = chat_type;
    var intFrameWidth = window.innerWidth;
    if(intFrameWidth<704){
        if(document.getElementById("left_main_div") != null)
            document.getElementById("left_main_div").style.display = 'none';
        if(document.getElementById("right_main_div") != null)
            document.getElementById("right_main_div").style.display = 'block';
    }    
    updateScroll();
}

function showHeader(jsonHeader,chat_type){
    var print_header = "";
    for(let json of jsonHeader){
        if(chat_type == "user"){
            username = json.username;
            image_url = "../src/profile_pictures/" + json.image_url;
            is_typing = Boolean(parseInt(json.is_typing));
            is_online = Boolean(parseInt(json.is_online));
            last_seen = json.last_seen;
            
            print_header += '<button onClick="exitChat()" id="backButton">';
            print_header += '<i class="fa fa-chevron-left fa-2x" aria-hidden="true" ';
            print_header += 'style="color: white;padding-top: 10px;"></i></button>';
            print_header += "<div id='propic' style='background-image:url("+image_url+");";
            if(is_online)
                print_header += "border:2.5px solid #00ff33";
            print_header += "'></div><p id='username'>"+username+"</p><p id='log'>";
            if(is_typing)
                print_header += 'Is typing...';
            else if(is_online)
                print_header += "Online";
            else
                print_header += last_seen;
            print_header += '</p>';
        }
        else{
            group_name = json.group_name;
            image_url = "../src/profile_pictures/" + json.image_url;
            online_users = json.online_users;
            
            print_header += '<button onClick="exitChat()" id="backButton">';
            print_header += '<i class="fa fa-chevron-left fa-2x" aria-hidden="true" ';
            print_header += 'style="color: white;padding-top: 10px;"></i></button>';
            print_header += "<div id='propic' style='background-image:url("+image_url;
            print_header += ");'></div><p id='username'>"+group_name+"</p><p id='log'>";
            print_header += 'Online users: <span id="count_online">'+online_users;
            print_header += '</span></p>';
        }
    }
    if(document.getElementById("header_chat") != null){
        document.getElementById("header_chat").innerHTML = print_header;
        document.getElementById("header_chat").style = 'block';
    }
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
        chatName = chat.chatName;	
        chatImage = "../src/profile_pictures/"+chat.chatImage;	
        mess_text = chat.mess_text;	

        print_chats += "<li class='select_chat'>";
        print_chats += "<button class='select_chat'";
        print_chats += 'onClick=\'showConversation("'+chat_type+'","+chatID+"); closeOverlay();';
        print_chats += 'requestHeader("'+chat_type+'","'+chatID+'");\'>';
        print_chats += "<div class='select_chat'>";
        print_chats += "<div class='propic_from_list' ";
        print_chats += "style='background-image:url(\""+chatImage+"\");'>";
        print_chats += "</div> ";
        print_chats += "<p class='chat_name'>"+chatName+"</p>";
        print_chats += "</div></button></li>";
    }
    if(jsonChats.length==0)
        print_chats = 'No group or user found';
    if(document.getElementById("tableResults") != null)
        document.getElementById("tableResults").innerHTML = print_chats;
}

function openProfileMenu(){
    if(document.getElementById("overlay") != null)
        document.getElementById("overlay").style='display: block;';
    if(document.getElementById("searchForm") != null)
        document.getElementById("searchForm").style='display: none;';
    if(document.getElementById("createGroupForm") != null)
        document.getElementById("createGroupForm").style='display: none;';
    if(document.getElementById("profileForm") != null)
        document.getElementById("profileForm").style='display: block;';
}

function search(){
    if(document.getElementById("overlay") != null)
        document.getElementById("overlay").style='display: block;';
    if(document.getElementById("searchForm") != null)
        document.getElementById("searchForm").style='display: block;';
    if(document.getElementById("createGroupForm") != null)
        document.getElementById("createGroupForm").style='display: none;';
    if(document.getElementById("profileForm") != null)
        document.getElementById("profileForm").style='display: none;';
}

function createGroup() {
    if(document.getElementById("overlay") != null)
        document.getElementById("overlay").style='display: block;';
    if(document.getElementById("searchForm") != null)
        document.getElementById("searchForm").style='display: none;';
    if(document.getElementById("createGroupForm") != null)
        document.getElementById("createGroupForm").style='display: block;';
    if(document.getElementById("profileForm") != null)
        document.getElementById("profileForm").style='display: none;';
}

function exitChat(){
    var intFrameWidth = window.innerWidth;
    if(intFrameWidth<704)
        if(document.getElementById("right_main_div") != null)
            document.getElementById("right_main_div").style.display = 'none';

    if(document.getElementById("left_main_div") != null)
        document.getElementById("left_main_div").style.display='block';
    
    if(document.getElementById("messages") != null)
        document.getElementById("messages").style.display="none";
    
    if(document.getElementById("select_chat_alert") != null)
        document.getElementById("select_chat_alert").style.display='block';

    if(document.getElementById("footer_form")!= null)
        document.getElementById("footer_form").style.display='none';	

    if(document.getElementById("output")!= null)
    document.getElementById("output").style.overflowY='hidden';

    if(document.getElementById("header_chat")!= null)
        document.getElementById("header_chat").style.display='none';

    
}

function closeOverlay(){
    if(document.getElementById("overlay") != null)
        document.getElementById("overlay").style='display: none;';
}

function fileUploaded(input_file){
    var val_uploaded_image=input_file.value;
    var inizioNomeFile=val_uploaded_image.lastIndexOf("\\");
    val_uploaded_image=val_uploaded_image.substr(inizioNomeFile+1);
    if(document.getElementById("label_upload") != null)
        document.getElementById("label_upload").innerHTML=val_uploaded_image;
}

function printLog(id,online) {
    var val_propic;
    var val_propic_from_list;
    var val_propic_from_chat;
    if(document.getElementById("propic"))
        var propic=document.getElementById("propic");
    if(document.getElementById("propic_from_list"+id))
        var propic_from_list=document.getElementById("propic_from_list"+id);
    if(document.getElementsByClassName("propic_from_chat"+id))
        var propic_from_chat=document.getElementsByClassName("propic_from_chat"+id);

    if(propic){
        if(online)
            val_propic="#00ff33";					
        else
            val_propic="#191919";					
        if(id==to_id)					
            propic.style.border="solid 2.5px"+val_propic;						
    }

    if(propic_from_list){
        if(online)
            val_propic_from_list="#00ff33";
        else
            val_propic_from_list="#333333";	
        propic_from_list.style.border="solid 2.5px"+val_propic_from_list;	
    }	

    if(propic_from_chat){
        for(var i=0;i<propic_from_chat.length;i++){
            if(online)
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



function printPreview(message){
    var mess_text = message.mess_text;
    var source_type = message.destination_type;
    var source_id = message.from_id;     
    if(document.getElementById("chat_"+source_type+"_"+source_id) != null)
        document.getElementById("chat_"+source_type+"_"+source_id)
}

function sendMessage(){
    var mess_text = inputmessage.value;
    console.log(destination_type);
    if(mess_text.length>1){
        websocket_server.send(
            JSON.stringify({
                'type':'chat',
                'from_id':session_id,
                'to_id':to_id,
                'mess_text':mess_text.trim(),
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

//Printing a message when i recieve it
websocket_server.onmessage = function(e){
    var json = JSON.parse(e.data);
    console.log(json); 
    console.log(to_id+"\n"+destination_type);    
    switch(json.type){
        case 'chat':
            printMessage(json);
            //printPreview(json);
            updateScroll();
            break;
        
        case 'connected':
            var id=json.user_id;	
            printLog(id,true);
            printLastSeen(id,true);
            incrementPrintCountOnline();
            break;
        
        case 'disconnected':
            var id=json.user_id;
            printLog(id,false);
            printLastSeen(id,false);
            decrementPrintCountOnline();
            break;
        
        case 'typing': 
            if(json.from_id == to_id && json.destination_type == destination_type){
                log.innerHTML='Online';
                console.log("c");           
            }
            break;
        
        case 'not_typing':
            if(json.from_id== to_id && json.destination_type == destination_type)
                log.innerHTML='Online';
            break;
        
        case 'date':
            var date="<td><tr><p class='print_date'>"+json.date+"</p></td></tr>";														
            document.getElementById("messages").innerHTML+=date;
            break;
    }
}

var inputmessage = document.getElementById("input_message");		
var button=document.getElementById("send_message");


//Events sending a message
if(inputmessage){
    inputmessage.addEventListener('keyup',function(e){
        if(e.key === "Enter" && !e.shiftKey){
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
        json = JSON.stringify({
                    'type':'typing',
                    'from_id':session_id,
                    'to_id': to_id,
                    'destination_type': destination_type
                });
        websocket_server.send(json);	
        console.log(json);			
    });
}

// Events stop typing a message
if(inputmessage){
    inputmessage.addEventListener('blur',function(e){
        json = JSON.stringify({
            'type':'not_typing',
            'from_id':session_id,
            'to_id':to_id,
            'destination_type':destination_type
        });
        websocket_server.send(json);		
        console.log(json);			
    });
}

function printLastSeen(id,online){	
    var log=document.getElementById("log");
    if(log){
        if(id==to_id){
        if(online)			
            log.innerHTML="Online";
        else
            log.innerHTML=last_seen;		
        }	
    }				
}