<html>
<head>
<title>Chat with socket.io and node.js</title>
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

<div id="container">
	<div id="searchContainer">
		<div class="topsearch">
			<form action="" id="formSearchVideo">
				<input type="text" id="query" placeholder="Buscan Cancion">
				<button> <i class="fa fa-search"></i> </button>
			</form>
		</div>
		<div id="resultSearch">
			<ul>
			</ul>
		</div>
	</div>
	<div id="playerContainer">
		<div id="ytPlayer">
			<video id="ytVideo"></video>
			<div class="ytControls">
				<a href="#play" id="ytPlay"><i class="fa fa-play"></i></a>
				<a href="#pause" id="ytPause"><i class="fa fa-pause"></i></a>
			</div>
			<div id="ytLoading"><i class="fa fa-circle-o-notch fa-spin"></i></div>
		</div>
	</div>
</div>

<hr>
<a href="#test" id="test">Test</a>
<hr>


<script src="http://code.jquery.com/jquery-latest.min.js"></script>
<script src="js/youtube.js"></script>
<script src="js/player.js"></script>
<script src="http://localhost:3000/socket.io/socket.io.js"></script>

<script> 
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
