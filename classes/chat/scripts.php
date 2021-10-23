<script type="text/javascript">
$(document).ready(function(){

    setInterval(function(){

        // NAVIGATION/FRIENDS LIST RELATED        
        fetchData();

        // CHAT RELATED
        updateMessages();
        updateChatProfileStatus();
        initChangeReadStatus();
     }, 1000);

    $('#alerts').click(function() {
        $('.alert').css('animation', 'slideToTop .5s linear 0s 1 normal forwards');
        $(this).children().fadeOut(1000);
    });

    // Submit a friend request via post function, returns data 
    addFriend = function () {
        var vfriendRequest = $("#friendRequest").val();
        var alert = '';

        $.post("./classes/chat/addFriend.php", // url of the page on server
        { // Data Sending With Request To Server
            friendRequest:vfriendRequest //msg being sent
        },
        function(response,status){
            var response = JSON.parse(response);
            alert += `
            <div class="alert ${response['type']}">
                <span class="closeAlert">&times;</span>
                ${response['message']}
            </div>`
            $('#alerts').html(alert);
            $("#friendRequestForm")[0].reset(); 
        });
        
    }

    // if "enter key" is press while typing in message input
    $('#friendRequest').on('keypress', function (e) {
         if(e.which === 13){
            e.preventDefault(); // needed to prevent page refresh
            //Disable textbox to prevent multiple submit
            $(this).attr("disabled", "disabled");
            addFriend(); // forward msg to submitMsg function   
            //Enable the textbox again if needed
            $(this).removeAttr("disabled");
         }
    });

    // Send message through post call via click
    $("#submitFriendRequest").click(function(){
        addFriend();
    });

    //////////////////////////////////////

    // UPDATE PROFILE STATUS
    fetchData = function () {
        $.ajax({
            type:'POST',
            url:'./classes/chat/fetchData.php',
            success:function(data){
                
                var myProfile = '';
                var incomingRequests = '';
                var friendsList = '';
                var friends = JSON.parse(data);

                // Refresh my profile stats
                myProfile += `
                <div class="myProfile">
                    <div class="friendInfo">

                        <div style="background:${friends['account']['status_color']}">
                            <img width="40" src="./uploads/no_pic.jpg">
                        </div>
                        <div>${friends['account']['username']}</div>
                    </div>
                      
                    <div class="topNav">
                        <button id="navSettings"><i class="fa fa-cog" aria-hidden="true"></i>
                        </button>
                        <button id="navLogout"><i class="fa fa-sign-out" aria-hidden="true"></i>
                        </button>
                    </div>
                    
                </div>`;
                $('#myProfile').html(myProfile);
                
                // Navigation links
                $("#navSettings").click(function(){
                    $('.settingsContainer').css('display','flex');
                    $('.chatContainer').css('display','none');
                });
                $("#navLogout").click(function(){
                    window.location = "./classes/logout.php";
                });

                // Display incoming friend requests
                $.each(friends['requests'], function(k, v) {
                    incomingRequests += `
                    <div class="friendRequest">
                        <p>${v.username} sent a friend request</p>
                        <div>
                            <form method="post" >
                                <input type="hidden" id="sender_id" name="sender_id" value="${v.sender_id}">
                                
                            </form>
                            <button id="acceptRequest" name="accept" class="requestButton green">Accept</button>
                            <button id="declineRequest" name="decline" class="requestButton error">Decline</button>
                        </div>
                    </div>`;
                });
                $('#incomingRequests').html(incomingRequests);
                // Respond to a friend request via post function, returns data 
                requestResponse = function (response) {
                    var vsender_id = $("#sender_id").val();
                    var vresponse = response;
                    //var vdecline = response;
                    var alert = '';

                    $.post("./classes/chat/friendRequest.php", // url of the page on server
                    { // Data Sending With Request To Server
                        sender_id:vsender_id, //msg being sent
                        response:vresponse
                    },
                    function(data,status){
                        var data = JSON.parse(data);
                        alert += `
                        <div class="alert ${data['type']}">
                            <span class="closeAlert">&times;</span>
                            ${data['message']}
                        </div>`
                        $('#alerts').html(alert);
                    });    
                }

                // Send message through post call via click
                $("#acceptRequest").click(function(){
                    requestResponse('accept');
                });
                $("#declineRequest").click(function(){
                    requestResponse('decline');
                });

                if(friends['friends'] != null) {
                    // Insert a list of friends
                    $.each(friends['friends'], function(k, v) {
                        var chatSession = [friends['account']];
                        chatSession[1] = friends['friends'][k];
                        var object = encodeURIComponent(JSON.stringify(chatSession));
              
                        friendsList += `
                        <div>
                            <button class="start_chat" data-object="${object}">
                                <div class="friendInfo">
                                    <div style="background:${v.status_color}">
                                        <img width="40" src="./uploads/no_pic.jpg">
                                    </div>
                                <div class="friendListUsername">
                                    ${v.username}
                                </div>
                            </button>
                        </div>`;

                    });         
                    $('#friendList').html(friendsList);
                } else {
                    $('#friendList').html('');
                }
                // INITIATE CHAT SESSION
                $(".start_chat").click(function(){
                    var object = $(this).data('object');
                    object = JSON.parse(decodeURIComponent(object));
                    $(".chatContainer").css('display','flex');
                    $(".settingsContainer").css('display','none');
                    initChat(object);
                    changeReadStatus(object[0]['id'],object[1]['id']);
                });
            }
        });
    }
    
// CHAT RELATED
    // INITIALIZE CHAT BOX
    function initChat(objects) {
        var updateObjects = encodeURIComponent(JSON.stringify(objects));

        var chatSession = `
        <div class="top_of_chat padding">
            <div id="profileStatus" data-objects="${updateObjects}">          
                     
            </div>
            
        </div>

        <div class="chat" id="chat">
            <div class="chat_history padding" id="chatContainer" data-objects="${updateObjects}">
                <span>&nbsp</span>
            </div>      
        </div>

        <div class="msg_container">
            <form id="form" method="post">
                <input hidden type="text" name="sender_id" id="sender_id" value="${objects[0]['id']}">
                <input hidden type="text" name="receiver_id" id="receiver_id" value="${objects[1]['id']}">
                
                <input type="text" class="msg_input" name="msg" id="msg" placeholder="Message the chat">
                
            </form>
            <button id="submit" class="msg_submit"><i class="fa fa-paper-plane"></i></button>
        </div>
        `;
        $('.chatContainer').html(`${chatSession}`);
        scrollToBottom('chatContainer');
        fetchChatMessages(objects[1]['id'],objects[0]['id']);
        fetchChatProfileStatus(objects[1]['id']);
    }

    // FETCH CHAT PROFILE STATUS
    function fetchChatProfileStatus(chat_userid) {
        $.ajax({
            url:"./classes/chat/getChatProfileStatus.php",
            method:"POST",
            data:{chat_userid:chat_userid},
            success:function(data){
                var data = JSON.parse(data);
                var profile = '';
                var alert = '';
                if(data == null) {
                    alert += `
                    <div class="alert error">
                        <span class="closeAlert">&times;</span>
                        You do not have access to chat with this user
                    </div>`
                    $('#alerts').html(alert);
                    $('.chatContainer').empty();
                    $('.settingsContainer').css('display','flex');
                    $('.chatContainer').css('display','none');
                } else {

                    profile += `
                    <div class="friendInfo" id="friendInfo">
                        <div style="background:${data['profile']['status_color']}">
                            <img width="40" src="./uploads/no_pic.jpg">
                        </div>
                        <div>
                            ${data['profile']['username']}${data['profile']['active_now']}
                        </div>
                    </div>
                    <div class="topNav">
                        <form method="post">
                            <input hidden type="text" name="uid" id="uidToRemove" value="${data['profile']['id']}">
                        </form>
                        <button id="removeFriend" class="removeFriend error">Remove friend</button>
                    </div>
                    `;
                    $('#profileStatus').html(profile);
                    
                    // Remove friend
                    $("#removeFriend").click(function(){
                        
                        
                        var uid = $("#uidToRemove").val();
                        
                        if(!uid==''){
                            $.post("./classes/chat/removeFriend.php", // url of the page on server
                            { // Data Sending With Request To Server
                                uid:uid //msg being sent
                            },
                            function(response,status){
                                var response = JSON.parse(response);
                                alert += `
                                <div class="alert ${response['type']}">
                                    <span class="closeAlert">&times;</span>
                                    ${response['message']}
                                </div>`
                                $('#alerts').html(alert);
                                fetchData();
                                $('.chatContainer').empty();
                                $('.settingsContainer').css('display','flex');
                                $('.chatContainer').css('display','none');
                            })
                        }
                    })
                }     
            }
        })
    }
    //////////////////////

    // UPDATE CHAT PROFILE STATUS EVERY SECOND 
    function updateChatProfileStatus() {
        $('#profileStatus').each(function(){
            var objects = $(this).attr("data-objects");
            object = JSON.parse(decodeURIComponent(objects));
            fetchChatProfileStatus(object[1]['id']);

        })
    };
    ////////////////////////

    // SCROLL TO BOTTOM TO LATEST MESSAGES
    function scrollToBottom (id) {
        var div = document.getElementById(id);
        div.scrollTop = div.scrollHeight - div.clientHeight;
    };
    /////////////////////

    // FETCH CHAT HISTORY
    function fetchChatMessages(sender_id, receiver_id) {
        $.ajax({
            url:"./classes/chat/getChatMessages.php",
            method:"POST",
            data:{sender_id:sender_id,receiver_id:receiver_id},
            success:function(data){
                var data = JSON.parse(data);
                var messages = '';
                if(data != null) {
                    $.each(data['messages'], function(k, v) {
                        if(v['sessionUid'] == v['messageUid']) {
                            messages += `
                            <div class="bubbleContainer">
                                <div class="bubble right">
                                    <p>${v['message']}</p>
                                </div>
                            </div>
                            `;
                        } else {
                            messages += `
                            <div class="bubbleContainer">
                                <div>
                                    <img src="./uploads/no_pic.jpg">
                                </div>
                                <div class="bubble left">
                                    <p>${v['messageUser']}: ${v['message']}</p>
                                </div>
                            </div>
                            `;
                        };
                        $('.chat_history').html(messages);
                    })
                }
            }
        })
        
    };
    //////////////////////

    // UPDATE CHAT EVERY SECOND 
    function updateMessages() {
        $('.chat_history').each(function(){
            var objects = $(this).attr("data-objects");
            object = JSON.parse(decodeURIComponent(objects));
            fetchChatMessages(object[0]['id'],object[1]['id']);

        });
    };
    ////////////////////////

    // UPDATE UNREAD MESSAGE TO READ
    function initChangeReadStatus() {
        if($('.chatContainer').css('display') == 'flex') {
            $('.chat_history').each(function(){
                var objects = $(this).attr("data-objects");
                object = JSON.parse(decodeURIComponent(objects));
                changeReadStatus(object[0]['id'],object[1]['id']);

            });
        };
    };
    changeReadStatus = function (uid, chat_uid) {
        $.ajax({    // make an ajax call to getChatMessages.php
            type:'POST', // needed even though we are using sessions in the php script
            url:'./classes/chat/changeReadStatus.php',
            data:{uid:uid,chat_uid:chat_uid},
        });
    };
    //////////////////////////

    // Submit a chat message
    submitMsg = function () {
        var alert = '';
        var msg = $("#msg").val();
        var sender_id = $("#sender_id").val();
        var receiver_id = $("#receiver_id").val();

        $.post("./classes/chat/sendMessage.php", // url of the page on server
        { // Data Sending With Request To Server
            msg:msg, //msg being sent
            sender_id:sender_id,
            receiver_id:receiver_id
        },
        function(response,status){     
            var response = JSON.parse(response);
            if(response != null) {
                alert += `
                <div class="alert ${response['type']}">
                    <span class="closeAlert">&times;</span>
                    ${response['message']}
                </div>`
                $('#alerts').html(alert)
            }
        });
        $("#form")[0].reset();
        scrollToBottom('chatContainer');
    }

    // if "enter key" is press while typing in message input
    $(document).on('keypress', '.msg_input', function (e) {
         if(e.which === 13){
            //Disable textbox to prevent multiple submit
            $('#msg').attr("disabled", "disabled");
            submitMsg(); // forward msg to submitMsg function   
            //Enable the textbox again if needed
            $('#msg').removeAttr("disabled");
         }
    });

    // Send message through post call via click
    $(document).on('click', '.msg_submit', function(){
        submitMsg();
    }); 
    ///////////////////

});
</script>