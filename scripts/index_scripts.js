document.getElementById("closeBtn").addEventListener("click", close);

function showResults(str) {
    if (str.length == 0) {
        document.getElementById("tableResults").innerHTML = "";
        return;
    } 
    else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) 
                document.getElementById("tableResults").innerHTML = this.responseText;
            }
        xmlhttp.open("GET", "db/search.php?name="+str+"&id=<?php echo $_SESSION['name']?>", true);
        xmlhttp.send();
    }
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
        var array_users=users_online.split(",");
        if(array_users){
            for (let index = 0; index < array_users.length; index++) {
                printLog(array_users[index],"online");
                if(array_users[index]==to_user_id)
                    printLastSeen(array_users[index],"online");
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
        message+="<p class='message_source'><b>"+json.from_nickname+"</b></p>";
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
        if(e.keyCode==13 && !e.shiftKey){
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
const emojiPicker = new FgEmojiPicker({
    trigger: ['button#send_emoji'],
    removeOnSelection: false,
    closeButton: true,
    dir: 'fg-emoji-picker/',
    position: ['top', 'left'],
    preFetch: true,
    insertInto: document.getElementById("input_message"),
    emit(obj, triggerElement) {
        console.log(obj, triggerElement);
    }
});