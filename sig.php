<?php

//ticks可用pcntl_signal_dispatch()替换


// SIGTERM【kill命令】比如代码中使用posix_kill($pid, SIGTERM)，注：posix_kill函数是发信号的意思

// SIGINT ctrl+c【键盘发出】

// SIGHUB reload【一般从终端发出】

// SIGSTOP ctrl+z 【键盘发出】

// SIGCHLD  子进程死掉，父进程会收到此信号


pcntl_signal(SIGTERM, "signal_handler");
pcntl_signal(SIGINT, "signal_handler");

function signal_handler($signal)
{
	echo 333;
	switch($signal)
	{
		case SIGTERM:
			print "Caught SIGTERM\n";
			exit;
		case SIGINT:
			print "Caught SIGINT, eg:ctrl + c\n";
			exit;
		default:
			echo "default \r\n";
			exit;

	}
}

//检测用pcntl_signal注册的函数
if (function_exists(pcntl_signal_dispatch())) {
	pcntl_signal_dispatch();
}else{
	declare(ticks = 1);
}

while (1) {
	# code...
}


