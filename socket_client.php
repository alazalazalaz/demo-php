<?php


$ip = '127.0.0.1';
$port = '8892';
$buf = '';
$len = 4096;

//创建
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (!$socket) {
	var_dump(getSocketErrorStr());exit;
}

//连接
if (!socket_connect($socket, $ip, $port)) {
	var_dump(getSocketErrorStr());exit;
};

sleep(1);

//发送
socket_write($socket, $buf);

while (true) {
	//socket_read()阻塞函数，对方发送空字符串，此函数不会响应(依旧阻塞)，此函数收到空串表示连接已断开
	$readSource = socket_read($socket, $len);
	echo sprintf("client recv:%s\r\n", $readSource);	
	if (empty($readSource)) {
		echo "server disconnected close client\r\n";
		socket_close($socket);break;
	}
	
	sleep(1);
	echo "while loop\r\n";
}





echo "end\r\n";

function getSocketErrorStr()
{
	return socket_strerror(socket_last_error());
}

