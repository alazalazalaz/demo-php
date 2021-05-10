<?php

require_once './reactor_select.php';

$ip = '127.0.0.1';
$port = 8990;
$forkNum = 1;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

socket_bind($socket, $ip, $port);

socket_listen($socket, 1);


class Tcp
{
	public $socket = '';
	public $eventHandle = '';

	public function __construct($socket, $eventHandle)
	{
		$this->socket = $socket;
		$this->eventHandle = $eventHandle;
	}
	public function connect($args)
	{
		echo "im newer connecting \r\n";
		//del掉socket
		$this->socket = socket_accept($this->socket);
		$this->eventHandle->add($this->socket, ReactorSelect::EV_READ, [$this, 'read'], []);
		//此时loop里面至少有n+1个socket，
		//1表示最初创建的用于监听是否有新连接上来的socket，n表示可读的socket
		//@todo 要不要Loop呢$this->eventHandle->loop();
		//@todo 执行onConnect()的回调
	}
	public function read($args = [])
	{
		echo " im read()\r\n";
		$len = 100;
		$data = '';
		socket_set_nonblock($this->socket);
		while ($tmp = socket_read($this->socket, $len, PHP_BINARY_READ)) {
			$data .= $tmp;
		};

		// $data = socket_read($this->socket, $len, PHP_BINARY_READ);

		echo "收到数据如下：$data\r\n";

		if (empty($data)) {
			//@todo 执行被动关闭socket
			//关闭socket
			$this->eventHandle->del($this->socket, ReactorSelect::EV_READ, [$this, 'del']);
			echo "已关闭socket\r\n";
		}else{
			//@todo 用于测试，收到后返回给客户端一个数据
			$this->eventHandle->add($this->socket, ReactorSelect::EV_WRITE, [$this, 'write'], [$data]);
		}

		//@todo 执行onRead($data)回调
	}
	public function write($args)
	{
		//@todo 主动执行onWrite($data)函数或者send函数之类的，底层调用此接口
		echo " im write()\r\n";
		socket_write($this->socket, json_encode($args));

		$this->eventHandle->del($this->socket, ReactorSelect::EV_WRITE, [$this, 'del']);
	}
	public function del()
	{
		//@todo 分为主动和被动，
		//主动调用时，底层调用此接口去socket_close($this->socket);
		//被动在read()里处理
	}
}

$obj = ReactorSelect::getInstance();

$tcpObj = new Tcp($socket, $obj);

for ($i=0; $i < $forkNum; $i++) { 
	$pid = pcntl_fork();
	if ($pid > 0) {
		//father

	}elseif ($pid == 0) {
		$pid = posix_getpid();
		echo "my pid :$pid\r\n";
		//child
		//添加一个事件
		
		$obj->add($tcpObj->socket, ReactorSelect::EV_READ, [$tcpObj, 'connect'], [$pid]);
		$obj->loop();
		exit;
	}
}

while (1) {
	sleep(2);
	// echo "im father\r\n";
}

