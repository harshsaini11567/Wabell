const express = require('express');
const app = express();
const mysql = require('mysql');
const fs = require('fs');
const path = require('path');
const FormData = require('form-data');
const axios = require('axios');
const connection = mysql.createConnection({
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'wabell',
});
const port = 3000;
// const LARAVEL_API_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL2xvZ2luIiwiaWF0IjoxNzUzNDQzNDgwLCJleHAiOjE3NTYwMzU0ODAsIm5iZiI6MTc1MzQ0MzQ4MCwianRpIjoiN2Zrdmp1ZmtpMFNjSnlzWSIsInN1YiI6IjIiLCJwcnYiOiJlYzAwMmM5MmEyNTI2OWVlMmQxNzRiY2UwNzUzMDhlMzBlOTFkMWNmIn0.p67nhoAw_kSxQMqFR-X7NvgtO3usrD8_NfToGrXflCQ';
const LARAVEL_API_TOKEN = socket.handshake.auth.token;

const userSocketMap = {};

const server = app.listen(port , () => {
    console.log(`server is running on the port ${port}`);
    connection.connect();
});

const io = require('socket.io')(server , {
    cors: {
        origin: '*',
    }
});

io.on("connection", (socket) =>{
    console.log("User Connected", socket.id);
    // emit use to send events
    socket.emit("welcome" , "Welcome to the chat");

    socket.on("register", (userId) => {
        console.log(`User ${userId} registered on socket ${socket.id}`);
        userSocketMap[userId] = socket.id;
    });

    socket.on("message" ,(msgData) => {
        console.log("Incoming messages:", msgData);
        const form = new FormData();
        form.append('receiver_id', msgData.receiver_id);
        form.append('message', msgData.message || '');

        if (msgData.filePath) {
            const absolutePath = path.resolve(msgData.filePath);
            if (fs.existsSync(absolutePath)) {
                form.append('files[]', fs.createReadStream(absolutePath));
            } else {
                console.error('File not found:', absolutePath);
                return;
            }
        }

        axios.post('https://wabell.hipl-staging3.com/api/conversations/send-message', form, {
            headers: {
                ...form.getHeaders(),
                Authorization: `Bearer ${LARAVEL_API_TOKEN}`, // Send token if auth protected
                Accept: 'application/json',
            }
        })
        .then(response => {
            const messageData = response.data.data;
            console.log('Message sent and stored via Laravel', messageData);

            const receiverSocketId = userSocketMap[msgData.receiver_id];
            if (receiverSocketId) {
                io.to(receiverSocketId).emit("new_message", messageData);
                axios.get('https://wabell.hipl-staging3.com/api/conversations/has-read', {
                    headers: {
                        Authorization: `Bearer ${LARAVEL_API_TOKEN}`,
                        Accept: 'application/json',
                    }
                })
                .then(unreadRes => {
                    const isRead = unreadRes.data.data.is_read;

                    // Emit unread status to the receiver
                    io.to(receiverSocketId).emit("unread_update", {
                        conversation_id: messageData.conversation_id,
                        is_read: isRead
                    });
                })
                .catch(err => {
                    console.error('Failed to fetch unread status:', err.response?.data || err.message);
                })
                io.to(receiverSocketId).emit("unread_update", {
                    conversation_id: messageData.conversation_id,
                    total_unread: 1 
                });
            }
            socket.emit("message", response.data);
        })
        .catch(error => {
            console.error('Failed to send message:', error.response?.data || error.message);
        });
    }); 
    socket.on("disconnect", () => {
        console.log("User Disconnected", socket.id);
    });
});