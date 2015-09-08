var io = require('socket.io')('3000');

io.on('connection', function(socket)
{
    console.log('a user connected');

    socket.emit('user_welcome:on', true);

    socket.on('message:emit', function(msg)
    {
        console.log('message: ' + msg);
        socket.broadcast.emit("message:on", msg);
    });

    socket.on('disconnect', function ()
    {
        io.emit('user disconnected');
    });
    
    socket.on('youtube_loadvideo:emit', function(id)
    {
        socket.broadcast.emit("youtube_loadvideo:on", id);
    });  

    socket.on('youtube_time:emit', function(obj)
    {
        socket.broadcast.emit("youtube_time:on", obj);
    });
    socket.on('disconnect', function ()
    {
        io.emit('user disconnected');
    });
});

console.log("port: 3000");