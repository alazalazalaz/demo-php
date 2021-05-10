<?php

$ip = '127.0.0.1';
$port = '9892';
$readData = NULL;
$sendData = 'hello client';
$len = 4096;

// error_reporting(0);

/**
 * 备注：
 1、socket_select(&$read, &$write, $except, 0)(非阻塞，所以需要while(true))

 此函数返回<1表示未轮询到有变动的socket,
 如果有socket变动，会返回>1的值，

$read为需要监听的socket，数组格式,两种socket都可以被select监听
(socket_create()创建的socket可以被监听判断是否有新client进来，
socket_accept()创建的socket可以被监听判断对方的写入)

其中,socket_select函数有回调后(即监听的socket有变动后)，$read变量会被改写为一个数组(数组内容为变动的socket， 数组元素个数具体为1还是变动的socket个数有待测试)，
此数组元素都是变动的socket，有可能是新连接进来的client，有可能是之前的msg source socket(即socket_accept创建返回的socket)，所以每次变动就需要去判断处理。

 1.1、如果是新client进来，此$read中的元素为最初server创建的socket，需要做$clients[] = $newsock = socket_accept($socket);类似这样的处理...
 1.2、否则则是msg source socket，直接读取内容即可。
 1.3、下面代码最初启动服务时，
 >1$read最初是1个socket(socket_create创建的)，
 >2有一个client连接进来时，sokcet_select监听到$read里的socket可以读写，$read变为[$socket]和初始值不变
 >3步骤2执行新增client的操作，新增socket_accept的操作(此时的accept不阻塞，因为select已经监听到可读写了)
 >4$read和$clients被新增一个socket（此socket是可读写的source）
 >5继续回到socket_select()，$read内容为[$socket, $client1_socket]
 >6如果client1发消息给服务器，则$client1_socket会被监听到，$read会变为[$client1_socket]...执行读写。。
 >7如果client2进来，$socket会被监听到，$read会变为[$socket]...执行新增。。
 */

// @todo 看能优化此代码不

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

$clients = [$socket];

$write = NULL;
$except = NULL;

while (true) {

	echo "begin loop\r\n";
	sleep(1);
	$read = $clients;


	// echo "11111111 begin select read\r\n";
	// var_dump($read);
	// echo "\r\n";

	
	if (socket_select($read, $write, $except, 0) < 1) {
		continue;
	}

	// echo "2222222 after select read\r\n";
	// var_dump($read);
	// echo "\r\n";
	if (in_array($socket, $read)) {
		// echo "333333 first socket in read 333333\r\n";
		$clients[] = $newsock = socket_accept($socket);
		
		onClientConnect($newsock, $clients);

		$key = array_search($socket, $read);
		unset($read[$key]);
		//因为下面的foreach是读取客户端发送的内容，也就是说下面read的socket必须是可读的socket资源，所以必须把初始化的socket删掉。
	}

	foreach ($read as $read_socket) {
		// echo "4444444 for in read 4444444\r\n";
		$data = socket_read($read_socket, $len, PHP_BINARY_READ);

		if (empty($data)) {
			//someone client close
			$key = array_search($read_socket, $clients);
			unset($clients[$key]);

			onClientClose($read_socket);
			continue;
		}
			
		onRecvClientMsg($read_socket, $data);

		foreach ($clients as $send_socket) {
			
		}

	}

}

echo "\r\nend\r\n";

function onClientConnect($socket, $clients)
{
	socket_write($socket, "there are " . (count($clients) - 1) . " clients connected to the server\r\n");
	socket_getpeername($socket, $ip, $port);
	echo "new client connected: {$ip}:{$port}\r\n";
}

function onRecvClientMsg($socket, $data)
{
	echo "recv content:{$data}\r\n";
}

function onClientClose($socket)
{
	socket_getpeername($socket, $ip, $port);
	echo "client close: {$ip}:{$port}\r\n";
}

function getSocketErrorStr()
{
	return socket_strerror(socket_last_error());
}