<?php

$ip = '127.0.0.1';
$port = '9893';
$readData = NULL;
$sendData = 'hello client';
$len = 10;
$lenTimes = 3;


$socket = create($ip, $port);


for ($i=0; $i < 1; $i++) { 
	$pid = pcntl_fork();
	if ($pid > 0) {
		//father

	}else if($pid == 0){
		//child
		doWhile($socket, $readData, $sendData, $len);
	}else{
		plog("fork failed");
	}
}

while (1) {
	// sleep(3);
	plog("waiting for child...");

	$pid = pcntl_wait($status);

	plog("child->[$pid]died");
}


function create($ip, $port){
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
	return $socket;
}


function doWhile($socket, $readData, $sendData, $len){

	$clients = [$socket];

	$write = NULL;
	$except = NULL;

	while (true) {

		plog("begin loop...");
		// sleep(1);
		$read = $clients;


		// echo "11111111 begin select read\r\n";
		// var_dump($read);
		// echo "\r\n";

		// sleep(1);
		//注意socket_select()的第四个参数，
		//阻塞时间，>0表示阻塞多少秒后返回，=0表示不阻塞立即返回，=null表示一直阻塞
		var_dump($read);
		if (socket_select($read, $write, $except, null) < 1) {
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

		$data = '';

		foreach ($read as $read_socket) {
			// echo "4444444 for in read 4444444\r\n";
			//@todo 如果客户端发送的内容长度大于socket_read接受的$len，$read_socket需要读取多次，次数为（发送长度/$len），但是并不知道客户端的发送长度。
			//解决方案1：客户端和服务器在上一层规定一个结束符
			//解决方案2：把socket_read放入while，直到读取失败，但socket_read是阻塞的，会出问题
			//解决方案3：同2，设置socket为非阻塞即可，同理上面的socket_select，由socket_read来判断是否可读。

			//@todo 写event-select(add, del, stop, loop)
			socket_set_nonblock($read_socket);
			while ($tmp = socket_read($read_socket, $len, PHP_BINARY_READ)) {
				$data .= $tmp;
			};

			if (empty($data)) {
				//someone client close
				$key = array_search($read_socket, $clients);
				unset($clients[$key]);

				onClientClose($read_socket);
				continue;
			}
			
			onRecvClientMsg($read_socket, $data);

			$wr = "回发消息:($data)";
			socket_write($read_socket, $wr);
			plog($wr);
			foreach ($clients as $send_socket) {
				//群发
				// $qunf = "群发消息:($data)";
				// socket_write($send_socket, $qunf);
			}

		}

		// if ($write) {
		// 	foreach ($write as $write_socket) {
		// 		$wr = "我是服务器，已收到你的信息，";
		// 		socket_write($write_socket, $wr);
		// 		onSendClientMsg($write_socket, $wr);
		// 	}
		// }

	}

	

}


echo "\r\nend\r\n";

function onClientConnect($socket, $clients)
{
	socket_write($socket, "there are " . (count($clients) - 1) . " clients connected to the server, my pid=".posix_getpid());
	plog("收到新连接，发送成功");
	socket_getpeername($socket, $ip, $port);
	plog("new client connected: {$ip}:{$port}");
}

function onRecvClientMsg($socket, $data)
{
	plog("读取成功:{$data}");
}

function onSendClientMsg($socket, $data)
{
	plog("发送成功:{$data}");
}

function onClientClose($socket)
{
	socket_getpeername($socket, $ip, $port);
	plog("客户端断开连接: {$ip}:{$port}");
}

function getSocketErrorStr()
{
	return socket_strerror(socket_last_error());
}

function plog($string = ''){
	echo sprintf("【%s】[%s]%s", date('H:i:s'), posix_getpid() , $string . "\r\n");
}


