<?php

$ip = '127.0.0.1';
$port = '8892';
$readData = NULL;
$sendData = 'hello client';
$len = 4096;

// error_reporting(0);

/**
 * 备注：
 1、最基本的server-client socket模型
 2、当有第二个client连接上来时，第一个client会被服务器close掉。也就是说此server只能同时连接一个client。
 3、socket_accept是阻塞的，直到有client连接上来才返回
 4、socket_read是阻塞的，直到对方有数据过来才返回，空字符串不会返回。如果返回为空，表示对方断开连接
 */

//创建socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (!$socket) {
	var_dump(getSocketErrorStr());exit;
}

//绑定
if (!socket_bind($socket, $ip, $port)) {
	var_dump(getSocketErrorStr());exit;
};

//监听
if (!socket_listen($socket, 1)) {
	var_dump(getSocketErrorStr());exit;
}

//accept会阻塞
while (true) {
	if (!($msgSource = socket_accept($socket))) {
		echo "accept failed\r\n";
	}else{
		echo "accept success\r\n";
		//socket_read会阻塞
		$readData = socket_read($msgSource, $len, PHP_BINARY_READ);
		echo sprintf("server recv:%s\r\n", $readData);

		socket_write($msgSource, $sendData);
		echo sprintf("send send:%s\r\n", $sendData);
	}

	sleep(1);
	echo "while loop\r\n";
}


echo "\r\nend\r\n";

function getSocketErrorStr()
{
	return socket_strerror(socket_last_error());
}