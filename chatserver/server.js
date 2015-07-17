
var express = require( 'express' );
var http = require( 'http' );

var app = express();
// var server = http.createServer( app );

//var io = socket.listen( server );

var server = app.listen(3001, function() {
  console.log('cool stuff on: 3001');
})

var io = require('socket.io')(server);

var count = 0 ;


var users = {}; // to store user data;
var socketIDs  = {};


io.sockets.on( 'connection', function( socket ) {
	// console.log(socket);
 //    console.log(socket.username);
    console.log( "New client !"+socket.id );
    count++ ; // to show current number of user active chatting.
    console.log( "current live sockets number: "+count);


    //type variable in socketData
    //0 - request message; 
    //1 - regular chat message;
    //2 - typing message;
    //3 - stop typing;
    //4 - user online;
    //5 - user away;
    socket.on('chat_request', function(data){
		console.log(data);	
		// console.log(data['type']);
		
		if ( isValidate(data) && data['type'] == 0 && data['sender'] != "") { 
			//store user data
			if ( users[data['sender']] ){
				socketIDs[socket.id] = data['sender']  ;
				users[data['sender']]['connections'] =  users[data['sender']]['connections'] + 1;
				console.dir(users);
				console.dir(socketIDs);
			}
			else {
				console.log('testing');
				socketIDs[socket.id] = data['sender']  ;
				users[data['sender']] = { 'userID': data['sender'], 'status':1, 'connections':1    };
				console.dir(users);
				console.dir(socketIDs);
				//users.push(data['sender']) = { userID:data['sender'], status:1, socketID: socketIDs.push([socket.id:0])  } ; 
			}
			 

			//open socket for the requester
		  	//socket.on(data['sender'], function(data) {
		  	//socket.emit('chat_request', "request-ok" ); //ack response	
		  	socket.on(data['question'], function(data) {
				
				console.log('received something:');				
				console.log(data);

				if ( isValidate(data) && data['type'] == 1 ) {
				 	//regular chat message
		        	//console.log("ready to send");
					socket.broadcast.emit( data['question'], data);
				}
				else if ( isValidate(data) && data['type'] == 2 ){
				 	//typeing notification
				 	socket.broadcast.emit( data['question'], data ) ;

				}
				else if ( isValidate(data) && data['type'] == 3 ){
				 	//stop typeing notification
				 	socket.broadcast.emit( data['question'], data ) ;

				}
				else if ( isValidate(data) && data['type'] == 4 ){
				 	//inactive/away notification
				 	socket.broadcast.emit( data['question'], data ) ;

				}
				else if ( isValidate(data) && data['type'] == 5 ){
				 	//inactive/away notification
				 	socket.broadcast.emit( data['question'], data ) ;

				}


			}); 				
		
		}		
    });


	socket.on('reconnect', function(data){
		console.log('.....reonnected!!!!');

	}); 

    socket.on('disconnect', function(data){
    	count--;
    	console.log(socket.id+" has disconnected!");
    	console.log( "current live sockets number after: "+count);
    	
    	if ( users[socketIDs[socket.id]] && users[socketIDs[socket.id]]['connections'] > 1){
    		users[socketIDs[socket.id]]['connections'] -= 1;
    		delete socketIDs[socket.id];
    	}
    	else {
    		if ( users[socketIDs[socket.id]] ) delete users[socketIDs[socket.id]];
    		delete socketIDs[socket.id];
    	}

    	console.dir(users);
		console.dir(socketIDs);
    	

    });
		

});

//--
function isValidate(data){

    	if (data['type'] != undefined && data['sender'] != undefined && data['receiver'] != undefined ){
    		return true;
    	}
    	else return false;
}



