


<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js" ></script>
<script src="https://cdn.socket.io/socket.io-1.0.0.js"></script>

<script type="text/javascript">

	//socket setup
	var TYPING_TIMER_LENGTH = 1000; // 1ms
	var connected = false;
	var typing = false;
	var lastTypingTime;

	if ( socket ){
		console.log('test socket true');
		//socket.emit("chat_request", "test" );
	}
	else {
		var socket = io.connect('http://10.0.0.228:3001');
		connected = true;
		
	}	
		
	var userID = "<?php echo Yii::app()->session['uid'] ?>";  
	var receID = "<?php echo $receiver['id'] ?>";             
	var qID    = "<?php echo $question['id'] ?>";
	
    //send chat request to server 	
    if ( connected ) 
    	//socket.emit('chat_request', {type:0, sender: userID , receiver: receID, message:"", time: (new Date).getTime() }  );
    	socket.emit('chat_request', socketPayload(0));
    
    //open its own socket based on the question id.
    socket.on( qID, function(data) {
    //socket.on(userID, function(data){
    	console.log('received incoming message');
    	//when received messages, check and then display that message
    	if ( data['type'] != undefined && data['type'] == 1 ){
    		//display the received chat message
    		//$('#message-display').append( "<div class='message'>testing, this is received</div>" );
    		$( '.hidden-message .sender'  ).text( data['sender'] );
	    	$( '.hidden-message  .content'  ).text( data['message'] );
	    	$( '.hidden-message  .time'  ).text( data['time'] );
	    	var $div = $('.hidden-message');
	    	var $tmp = $div.clone().prop('class', 'message' );
	    	$('#message-display').append($tmp);

    	}
    	else if (  data['type'] != undefined && data['type'] == 2 && data['sender'] != userID ) {

    		//$("input#inputMessage").attr("placeholder", data['message']);
    		$('div#hidden-typing-notice').text(data['message']);
    	}
    	else if (  data['type'] != undefined && data['type'] == 3  && data['sender'] != userID) {

    		//$("input#inputMessage").attr("placeholder", "");
    		$('div#hidden-typing-notice').text("");

    	}

    })

	//alert("hi");
	$(document).ready(function() {

		$('#chat-form').on('submit', function (e) {
			var formData = $(this).serializeArray();    	
    		sendMessage(formData);

    		$('#inputMessage').val("");
    		//e.stopPropagation();
    		return false;
		});

		$('input#inputMessage').on('input', function (e) {
			
    		updateTyping();

		});

		$('input#inputMessage').on('click', function (e) {
			//$('input#inputMessage').focus();
    		updateTyping();
		});

	});

	

    function sendMessage(formData){
    	console.log(' send message');
    	if ( formData[0]['name'] == 'type') {
    		//
    	}

    	var unixTime = (new Date).getTime() ;
    	//var dataToSend = { type: formData[0]['value'], sender: formData[1]['value'], receiver: formData[2]['value'], message: formData[3]['value'], time: unixTime };
    	
    	$( '.hidden-message .sender'  ).text( formData[1]['value'] );
    	$( '.hidden-message  .content'  ).text( formData[3]['value'] );
    	$( '.hidden-message  .time'  ).text( unixTime );
    	var $div = $('.hidden-message');
    	var $tmp = $div.clone().prop('class', 'message' );
    	$('#message-display').append($tmp);

    	//send via socket
    	//socket.emit('chat_message', dataToSend);
    	if ( connected ) 
    		socket.emit(qID, socketPayload(1, formData, unixTime) );
    		//socket.emit(formData[1]['value'], dataToSend);
    	//to stop typing
    	if ( connected ) 
    		socket.emit(qID, socketPayload(3) );
    		//socket.emit(userID, {type: 3, sender:userID, receiver:receID, message: "stop typing", time: ""} );
    	typing = false;
    	//
    }

    //************************************
    //0 - request message; 
    //1 - regular chat message;
    //2 - typing message;
    //3 - stop typing;
    //still constructing...
    function socketPayload(typeNum, formData, unixTime){
    	if ( typeNum == 0 ) {
    		return {type:0, question: qID, sender: userID , receiver: receID, message:"", time: (new Date).getTime() } ;
    	}
    	else if ( typeNum == 1 ){
    		return { type: formData[0]['value'], question: qID, sender: formData[1]['value'], receiver: formData[2]['value'], message: formData[3]['value'], time: unixTime };
    	}
    	else if ( typeNum == 2 ) {
    		return {type: 2, question: qID, sender:userID, receiver:receID, message: userID+" is typing...", time: ""}; 
    	}
    	else if ( typeNum == 3) {
    		return {type: 3, question: qID,  sender:userID, receiver:receID, message: "stop typing", time: "" }; 
    	}
    }

    //***************************************************************************
    //active and inactive feature to save resouces on our node server, so it can be more scale
	var IDLE_TIMEOUT = 300; //seconds
	var _idleSecondsCounter = 0;
	document.onclick = function() {
	    _idleSecondsCounter = 0;
	};
	document.onmousemove = function() {
	    _idleSecondsCounter = 0;
	};
	document.onkeypress = function() {
	    _idleSecondsCounter = 0;
	};
	window.setInterval(CheckIdleTime, 1000);

	function CheckIdleTime() {
	    _idleSecondsCounter++;

	    //handle overflow
	    if ( _idleSecondsCounter == Number.MAX_VALUE) _idleSecondsCounter = IDLE_TIMEOUT + 1;

	    var oPanel = document.getElementById("SecondsUntilExpire");
	    if (oPanel){
	    	oPanel.innerHTML = (IDLE_TIMEOUT - _idleSecondsCounter) + "";
	    }
	        
	    if (_idleSecondsCounter >= IDLE_TIMEOUT) {
	        // alert("Time expired!");
	        // document.location.href = "logout.html";
	        //socket = "";

	        if ( connected ){
	        	// socket.disconnect();
	            // console.log(socket);

	         socket.io.disconnect();
	            connected = false;
	        }
	        
	    }
	    else{
	    	
	    	if ( !connected ) {
	    		console.log(socket.io);
	    		socket.io.reconnect();
	    		connected = true;
	    		if ( connected ) 
	    			socket.emit ( 'chat_request', socketPayload(0));
	    			//socket.emit('chat_request', {type:0, sender: userID , receiver: receID, message:"", time: (new Date).getTime() }  );

	    		//socket.socket.reconnect();
	    		
	    	}
	    	
	    }
	}



	//*************************************************************
    //-----------------------------
    // Keyboard events
    // $(document).keydown(function(e) {
    //     //console.log(e);
       
    //     //if the user pressed 'D'
    //     if(e.keyCode == 13) {
    //             console.log('caught');
    //     }
    // })

    


    // Updates the typing event
	function updateTyping () {
	 	if (connected) {
	 		if (!typing) {
	 			typing = true;
	 			//socket.emit('typing');	 			
	 			if ( connected ) 
	 				socket.emit( qID, socketPayload(2) );
	 				//socket.emit(userID, {type: 2, sender:userID, receiver:receID, message: userID+" is typing...", time: ""} );
	 		}
	 		lastTypingTime = (new Date()).getTime();

	 		setTimeout(function () {
	 			var typingTimer = (new Date()).getTime();
	 			var timeDiff = typingTimer - lastTypingTime;
	 			if (timeDiff >= TYPING_TIMER_LENGTH && typing) {
	 				//socket.emit('stop typing');
	 				if ( connected ) 
	 					socket.emit( qID, socketPayload(3) );
	 					//socket.emit(userID, {type: 3, sender:userID, receiver:receID, message: "stop typing", time: ""} );
	 				typing = false;
	 			}
	 		}, TYPING_TIMER_LENGTH);
	 	}
	}


</script>




<div class="container">
	<h1>Question Page #</h1>
	<div class="row-fluid">
		<div class=" col-md-8 col-xs-8" >


			<p id="SecondsUntilExpire" style="background:yellow;"></P>
			<div id="message-display">

				<div class="message">
					<p class="question"> Question </P>
				</div>

				

			</div>

			
			<div class ="hidden-message">
					<p class="sender">   </p>
					<p class="time">  </p>
					<p class="content">  </p>
			</div>




			<div id="hidden-typing-notice"></div>
		
		</div>



	</div>


		

		<form id="chat-form" action="POST">
		
		 <div class="form-group" >	    	
		    
		    <input type="hidden" class="reset" name="type" value="1">
		    <input type="hidden" class="reset" name="userID" value="<?= Yii::app()->session['uid'] ?>">
		    <input type="hidden" class="reset" name="receID" value="<?= $receiver['id'] ?>">
		    <input class="form-control" class="reset" id='inputMessage' name="message" value="">
		    <button type="submit" id ="message-button" class="btn btn-default pull-right">Send</button>
		    
		 </div>
		 
		 

	</from>



</div>
 




