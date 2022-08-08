const express = require('express');



const app = express();





const server = require('http').createServer(app);





const io = require('socket.io')(server, {

    cors: { origin: "*"}

});





io.on('connection', (socket) => {

    console.log('connection');

    socket.on('johnRoomToServer', (room) => {

        socket.data.room = room;

        socket.join(room);
        console.log(io.sockets.adapter.rooms.get(room).size);

    });

    socket.on('AuctionToServer', (data) => {

        io.to(data.room).emit('AuctionToClient', data.data);

        // io.sockets.emit('AuctionToClient', message);

        // socket.broadcast.emit('AuctionToClient', message);

    });





    socket.on('disconnect', (reason) => {

        io.in(socket.id).socketsLeave(socket.data.room);

        console.log('Disconnect');

    });

});



server.listen(3001, () => {

    console.log('Server is running');

});





