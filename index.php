
<html>
<head>
<title>Chat with socket.io and node.js</title>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,user-scalable=no">
<meta name="google-signin-client_id" content="563502636110-om5gv8nng8hupjm517g8vsp7mv7834vs.apps.googleusercontent.com">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" type="text/css" />
<style type="text/css">
	body{background:#eee;font-family: arial;font-size: 14px;}
	*{margin:0;padding:0;}
	#ytPlay, #ytPause, #ytLoading{display:none;}
	#ytLoading{position:absolute;width:50px;height:50px;top:49%;left:49%;}

	#container{width:655px;padding:10px 0;height:500px;box-shadow:0 0 10px 4px #ddd;background:white;border-radius:5px;margin:50px auto;}
	#searchContainer{width:350px;border-right:1px solid #eee;height:500px;float:left;}
	#playerContainer{width:300px;height:500px;float:left;position:relative;}
	.topsearch{width:90%;height:60px;margin:0 auto;}
	.topsearch input{width:90%;font-size:18px;}
	input{border:0;padding:4px 6px; border-bottom:1px solid #eee;float:left;}
	
	ul,li{list-style: none;}
	#resultSearch{}
	#resultSearch ul{width:100%;height:400px;overflow-y:scroll;overflow-x:hidden;}
	#resultSearch li{width:90%;height:20px;float:left;padding:15px 5%;font-size: 12px;cursor:pointer;}
	#resultSearch li:nth-child(2n+1){background:#F9F9F9;}
	#resultSearch li:hover{background:#FFFBC9}
	#resultSearch li img{float:left;margin-right: 6px;margin-top:-5px;}

	.result_title{width:68%;float:left;height:20px;overflow:hidden;white-space: nowrap;text-overflow:ellipsis;}
	.result_duration{width:40px;float:right;height:20px;color:#999;}

	.topsearch button{border:0;background: white;color:#999;padding:4px 5px 0 5px;font-size: 16px}

	#playerContainer #ytVideo{width:100%;height:200px;}
</style>
</head>
<body>


<div class="g-signin2" data-onsuccess="onSignIn"></div>

<a href="#" onclick="signOut();">Sign out</a>

<div id="container">

	<?php
	$option = @$_GET['option'];
	switch ($option) {
		case 'add':
			include 'view.add.phtml';
			break;
		
		default:
			include 'view.home.phtml';
			break;
	}

	?>
</div>


<script src="https://apis.google.com/js/platform.js" async defer></script>
<script src="http://code.jquery.com/jquery-latest.min.js"></script>
<script src="js/youtube.js"></script>
<script src="js/player.js"></script>
<script src="http://localhost:3000/socket.io/socket.io.js"></script>

<script> 

function onReadyGoogleConnect(auth)
{
	console.log("inicio google connect");
	verifySession(auth.isSignedIn.get());
}
function onFailure(a){
	console.log("error: ",a);
}

function verifySession(isLogged){
	console.log("logeado? ", isLogged);
}
function signOut() {
	var auth2 = gapi.auth2.getAuthInstance();
	auth2.signOut().then(function () {
		alert('User signed out.');
	});
}

function onSignIn(googleUser) {
  var profile = googleUser.getBasicProfile();
  console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
  console.log('Name: ' + profile.getName());
  console.log('Image URL: ' + profile.getImageUrl());
  console.log('Email: ' + profile.getEmail());
  alert('connect successs');
  console.log(profile);
}



// gapi
gapi.load('auth2', function() 
{
	var auth2 = gapi.auth2.getAuthInstance();
	auth2.then(onReadyGoogleConnect, onFailure);
	auth2.isSignedIn.listen(verifySession);

});



var socket = io.connect("http://localhost:3000");
var time = null;
var video_actual = null;
var users = [{name:'jpmaster',anfitrion:true}];
var myplayer = new Player(document.getElementById('ytPlayer'));
	myplayer.init();
	myplayer.yt.onDuration(function(currentTime, totalTime){
	    var playerTimeDifference = (currentTime / totalTime) * 100;
	    console.log(playerTimeDifference);
	    socket.emit('youtube_time:emit', {current:currentTime,total:totalTime, video:video_actual});
	});


	$("#formSearchVideo").on('submit', function(e){
		e.preventDefault();
		var query =  $(this).find('#query').val();
		var youtube_key = "AIzaSyBMtaUJklrz3hy49XTMI1T8CxwWOx4CWR4";
	 	$.ajax({
	        url: 'https://www.googleapis.com/youtube/v3/search?key='+youtube_key+'&part=snippet&q='+query+'&maxResults=10&order=date',
	        // url: 'http://gdata.youtube.com/feeds/api/videos?vq='+query+'&max-results=5&alt=json-in-script',
	        type: 'get',
	        dataType:"jsonp",
	        success: function (data) {
	        	var html = '';
	        	$.each(data.items, function(i,value)
        		{
	        		var title = value.snippet.title;
            		var duracion = "03:33";
	           		var id = value.id.videoId;
	            	var thumbnailUrl = value.snippet.thumbnails.default.url;

	            	html+='<li data-id="'+id+'"> <img src="'+thumbnailUrl+'" width="35" height="30"> <span class="result_title">'+title+'</span><span class="result_duration"> '+duracion+'</span></li>';

	          });

	          $('#resultSearch>ul').html(html);
	        }
	      });
	
	});
	
	$("#resultSearch>ul").on('click', 'li', function(){
		var id = $(this).data('id');
		alert("se va agregar al playlist");
		myplayer.yt.api.loadVideoById(id, 0);
		socket.emit('youtube_loadvideo:emit', id);
		video_actual = id;

	});

	// on received
	socket.on('youtube_loadvideo:on', function(id){
		myplayer.yt.api.loadVideoById(id, 0);
	});
	socket.on('youtube_time:on', function(obj){
		console.log(obj);
		time = obj;
	});
	socket.on('user_welcome:on', function(status){
		setTimeout(function(){
			if(status){
				myplayer.yt.api.loadVideoById(time.video, time.current, true);
			}
		},5000);
		
	});


</script>

<script>
jQuery(function($) {
    
    var $messageForm = $('#send-message');
    var $messageBox = $('#message');
    var $chat = $('#messages');

    $messageForm.submit(function(e) {
        e.preventDefault();
        socket.emit('message:emit', $messageBox.val());
        $messageBox.val('');
    });

    socket.on('message:on', function(data) {
        $chat.append("<li>" + data + "</li>");
    });
});
</script>
