const WebSocket = require('ws');
const net = require('net');
const url = require('url');
const wss = new WebSocket.Server({ port: 8081 }); // WebSocket 指定port

wss.on('connection', function connection(ws, req) {
    const connectionTime = new Date(); // 記錄連接時間
    console.log(`WebSocket client connected at ${connectionTime}`);

    // 解析 WebSocket 連接請求的 URL，以獲取 Telnet 主機和端口參數
    const requestUrl = url.parse(req.url, true);
    const telnetHost = requestUrl.query.host || '10.148.174.70'; // 預設主機 ip
    const telnetPort = parseInt(requestUrl.query.port, 10) || 10; // 預設端口
	//創建服務
    const telnetClient = new net.Socket();
    telnetClient.connect(telnetPort, telnetHost, function() {
        console.log(`Connected to Telnet server at ${telnetHost}:${telnetPort} - Time: ${new Date().toString()}`);
    });
	//連接成功
    telnetClient.on('data', function(data) {
        ws.send(data.toString());
    });
	//websocket 收到訊息顯示在telnet server
    ws.on('message', function incoming(message) {
        telnetClient.write(message);
    });
	//websocket端斷連並顯示
    ws.on('close', function close() {
        const disconnectTime = new Date(); // 記錄斷開連接時間
        console.log(`WebSocket client disconnected - Telnet server at ${telnetHost}:${telnetPort} is disconnected at ${disconnectTime}`);
        telnetClient.end();
    });
	//telnet server端關閉並顯示
    telnetClient.on('close', function() {
        console.log(`Telnet connection closed at ${new Date().toString()} - Telnet server at ${telnetHost}:${telnetPort} is disconnected`);
        ws.close();
    });
	//telnet server連接錯誤會顯示
    telnetClient.on('error', function(err) {
        console.error(`Telnet connection error: ${err} - at ${new Date().toString()}`);
        ws.close();
    });
	//websocket 連結錯誤會顯示
    ws.on('error', function(err) {
        console.error(`WebSocket connection error: ${err} - at ${new Date().toString()}`);
        telnetClient.end();
    });
});
