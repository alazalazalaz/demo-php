<?php

$ip = '127.0.0.1';
$port = '9893';
$readData = NULL;
$buf = "hello client\r\n";
$len = 4096;


$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if (!$socket) {
	var_dump(getSocketErrorStr());exit;
}

//连接
if (!socket_connect($socket, $ip, $port)) {
	var_dump(getSocketErrorStr());exit;
};

socket_getpeername($socket, $ip, $port);
plog("连接成功,对方服务信息: {$ip}:{$port}");
sleep(1);

$readSource = socket_read($socket, $len);
plog(sprintf("读取成功before:%s", $readSource));

plog("等待输入...\r\n");
while (!feof(STDIN)) {
	$msg = fread(STDIN, 1024);
	
	socket_write($socket, $msg);
	plog("发送成功($msg)");

	//socket_read()阻塞函数，对方发送空字符串，此函数不会响应(依旧阻塞)，此函数收到空串表示连接已断开
	$readSource = socket_read($socket, $len);
	plog(sprintf("读取成功:%s", $readSource));

	if (empty($readSource)) {
		plog("服务器断开连接");
		socket_close($socket);break;
	}
	
}



function plog($string = ''){
	echo sprintf("【%s】【%s】 %s", date('H:i:s'), posix_getpid() , $string . "\r\n");
}

