const express = require('express');
const app = express();

const http = require('http'); // ✅ HTTP ONLY
const socketIo = require('socket.io');
const mysql = require('mysql');
const fs = require('fs');
const path = require('path');
const FormData = require('form-data');
const axios = require('axios');
require('dotenv').config();

/* =======================
   DATABASE (UNCHANGED)
   ======================= */
const connection = mysql.createConnection({
    host: process.env.DB_HOST,
    database: process.env.DB_DATABASE,
    user: process.env.DB_USERNAME,
    password: process.env.DB_PASSWORD,
});

/* =======================
   HTTP SERVER (IMPORTANT)
   ======================= */
const server = http.createServer(app);

/* =======================
   SOCKET.IO
   ======================= */
const io = socketIo(server, {
    cors: {
        origin: "*",
        methods: ["GET", "POST"]
    }
});

/* =======================
   GLOBAL STATE
   ======================= */
const userSocketMap = {};
const typingTimers = {};
const activeChatMap = {};

// Laravel API
const apiUrl = process.env.SOCKET_URL || 'https://admin.wabell.sa';

/* =======================
   AUTH MIDDLEWARE
   ======================= */
io.use(async (socket, next) => {
    console.log("[AUTH] Handshake:", socket.handshake.auth);

    const token = socket.handshake.auth?.token;
    if (!token) {
        return next(new Error("Authentication error: Token required"));
    }

    try {
        const res = await axios.get(`${apiUrl}/api/user`, {
            headers: { Authorization: `Bearer ${token}` }
        });

        if (!res.data?.data?.id) {
            return next(new Error("Authentication error: Invalid user"));
        }

        socket.user = res.data.data;
        socket.userId = socket.user.id;
        socket.LARAVEL_API_TOKEN = token;

        console.log(`[AUTH OK] userId=${socket.userId}`);
        next();

    } catch (err) {
        console.error("[AUTH FAIL]", err.response?.data || err.message);
        next(new Error("Authentication error"));
    }
});

/* =======================
   CONNECTION
   ======================= */
io.on("connection", (socket) => {
    console.log(`[CONNECT] socketId=${socket.id}, userId=${socket.userId}`);

    const userId = socket.userId;
    if (!userId) {
        socket.disconnect();
        return;
    }

    // Register socket
    if (!userSocketMap[userId]) userSocketMap[userId] = [];
    if (!userSocketMap[userId].includes(socket.id)) {
        userSocketMap[userId].push(socket.id);
    }

    io.emit("user_online", { user_id: userId, status: true });
    socket.emit("welcome", "Welcome to the chat");

    /* =======================
       UNREAD INIT
       ======================= */
    (async () => {
        try {
            const unreadRes = await axios.get(`${apiUrl}/api/conversations/has-read`, {
                headers: {
                    Authorization: `Bearer ${socket.LARAVEL_API_TOKEN}`,
                    Accept: 'application/json',
                }
            });
            socket.emit("unread_update", {
                is_read: unreadRes.data?.data?.is_read
            });
        } catch (e) {
            console.error("[INIT] unread error", e.message);
        }
    })();

    /* =======================
       REGISTER (OPTIONAL)
       ======================= */
    socket.on("register", (passedUserId) => {
        const uid = passedUserId || socket.userId;
        if (!userSocketMap[uid]) userSocketMap[uid] = [];
        if (!userSocketMap[uid].includes(socket.id)) {
            userSocketMap[uid].push(socket.id);
        }
        io.emit("user_online", { user_id: uid, status: true });
    });

    socket.on("get_online_users", () => {
        socket.emit("online_users", Object.keys(userSocketMap));
    });

    /* =======================
       ACTIVE CHAT
       ======================= */
    socket.on("active_chat", async (data) => {
        if (typeof data === "string") data = JSON.parse(data);

        if (data?.chatting_with) {
            activeChatMap[userId] = data.chatting_with;

            try {
                await axios.post(
                    `${apiUrl}/api/conversations/${data.conversation_id}/read`,
                    null,
                    { headers: { Authorization: `Bearer ${socket.LARAVEL_API_TOKEN}` } }
                );
            } catch {}
        } else {
            delete activeChatMap[userId];
        }
    });

    /* =======================
       MESSAGE
       ======================= */
    socket.on("message", async (raw) => {
        try {
            const msg = typeof raw === "string" ? JSON.parse(raw) : raw;

            const form = new FormData();
            form.append("receiver_id", String(msg.receiver_id));
            form.append("message", msg.message || "");

            if (msg.filePath) {
                const abs = path.resolve(msg.filePath);
                if (fs.existsSync(abs)) {
                    form.append("files[]", fs.createReadStream(abs));
                }
            }

            const res = await axios.post(
                `${apiUrl}/api/conversations/send-message`,
                form,
                {
                    headers: {
                        ...form.getHeaders(),
                        Authorization: `Bearer ${socket.LARAVEL_API_TOKEN}`
                    }
                }
            );

            const receiverSockets = userSocketMap[msg.receiver_id] || [];
            receiverSockets.forEach(id => {
                io.to(id).emit("new_message", res.data);
            });

            socket.emit("message_sent", res.data);

        } catch (e) {
            console.error("[MESSAGE ERROR]", e.message);
            socket.emit("error", "Failed to send message");
        }
    });

    /* =======================
       TYPING
       ======================= */
    socket.on("typing", (data) => {
        if (typeof data === "string") data = JSON.parse(data);
        const receiverId = data.receiver_id;

        activeChatMap[userId] = receiverId;

        const sockets = userSocketMap[receiverId] || [];
        sockets.forEach(id => {
            io.to(id).emit("typing", { sender_id: userId, is_typing: true });
        });

        const key = `${receiverId}_${userId}`;
        clearTimeout(typingTimers[key]);
        typingTimers[key] = setTimeout(() => {
            sockets.forEach(id => {
                io.to(id).emit("typing", { sender_id: userId, is_typing: false });
            });
            delete typingTimers[key];
        }, 3000);
    });

    socket.on("stop_typing", (data) => {
        if (typeof data === "string") data = JSON.parse(data);
        const receiverId = data.receiver_id;

        const sockets = userSocketMap[receiverId] || [];
        sockets.forEach(id => {
            io.to(id).emit("typing", { sender_id: userId, is_typing: false });
        });
    });

    /* =======================
       DISCONNECT
       ======================= */
    socket.on("disconnect", () => {
        console.log(`[DISCONNECT] socketId=${socket.id}, userId=${userId}`);

        for (const uid in userSocketMap) {
            userSocketMap[uid] = userSocketMap[uid].filter(id => id !== socket.id);
            if (userSocketMap[uid].length === 0) {
                delete userSocketMap[uid];
                io.emit("user_online", { user_id: uid, status: false });
            }
        }

        delete activeChatMap[userId];
    });
});

/* =======================
   START SERVER
   ======================= */
const port = 3002;
server.listen(port, '127.0.0.1', () => {
    console.log(`Socket.IO running on http://127.0.0.1:${port}`);
});