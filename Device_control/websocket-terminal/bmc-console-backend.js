const WebSocket = require('ws');
const net = require('net');
const url = require('url');

// HTTP WebSocket 服務器
const wsServer = new WebSocket.Server({
    host: '0.0.0.0',
    port: 8081
});


// WebSocket 連接處理函數
function handleConnection(ws, req) {
    const connectionTime = new Date();
    console.log(`WebSocket client connected at ${connectionTime}`);

    const requestUrl = url.parse(req.url, true);
    const telnetHost = requestUrl.query.host || '10.148.175.12';
    const telnetPort = parseInt(requestUrl.query.port) || 3002;

    const telnetClient = new net.Socket();
    telnetClient.connect(telnetPort, telnetHost, function() {
        console.log(`Connected to Telnet server at ${telnetHost}:${telnetPort} - Time: ${new Date().toString()}`);
    });

    telnetClient.on('data', function(data) {
        ws.send(data.toString());
    });

    ws.on('message', function incoming(message) {
        telnetClient.write(message);
    });

    ws.on('close', function close() {
        const disconnectTime = new Date();
        console.log(`WebSocket client disconnected - Telnet server at ${telnetHost}:${telnetPort} is disconnected at ${disconnectTime}`);
        telnetClient.end();
    });

    telnetClient.on('close', function() {
        console.log(`Telnet connection closed at ${new Date().toString()} - Telnet server at ${telnetHost}:${telnetPort} is disconnected`);
        ws.close();
    });

    telnetClient.on('error', function(err) {
        console.error(`Telnet connection error: ${err} - at ${new Date().toString()}`);
        ws.close();
    });

    ws.on('error', function(err) {
        console.error(`WebSocket connection error: ${err} - at ${new Date().toString()}`);
        telnetClient.end();
    });
}

// 為兩個服務器使用相同的處理邏輯
wsServer.on('connection', handleConnection);

console.log('WebSocket server (HTTP) running on port 8081');