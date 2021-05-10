<?php

if (function_exists(pcntl_signal_dispatch())) {
	pcntl_signal_dispatch();
}else{
	declare(ticks = 1);
}

pcntl_signal(SIGCHLD, 'sig_func');
pcntl_signal(SIGTERM, 'sig_func');
pcntl_signal(SIGINT, 'sig_func');

function sig_func($signal)
{	
	switch ($signal) {
		case SIGCHLD:
            echo "SIGCHLD child died\r\n";exit;
            break;
        case SIGTERM:
            echo "SIGTERM killed\r\n";exit;
            break;
        case SIGINT:
            echo "SIGINT ctrl - c\r\n";exit;
            break;
		
		default:
			# code...
			break;
	}

}

$pid = pcntl_fork();

if ($pid == -1) {
	echo 'fork failed';exit;
}elseif ($pid) {
	echo "im farther";
	pcntl_wait($status);
	//注意：pcntl_wait会阻塞，收到子进程的信号后会执行注册回调，如果没有注册则往下执行，遇到注册后立即执行回调。所以注册应该写在前面
	//子进程执行posix_kill(getmypid(), SIGTERM);后，父进程会收到SIGCHLD，然后执行sig_func(SIGCHLD)
	echo "ffff\r\n";
	
}else{
	sleep(2);
	echo "im child";

	sleep(2);
	posix_kill(getmypid(), SIGTERM);
	// exit;
	//注意：1、子进程正常退出或者exit退出或者异常退出，父进程都会收到SIGCHLD的信号
	//注意：posix_kill执行后，会检测是否已经注册了signal_dispatch（即是否已经执行过pcntl_signal_dispatch()或declare(ticks = 1)）如果执行过了，则立即进入注册的回调，如果没有，则继续执行posix_kill之后的代码，之后的代码如果遇到signal_dispatch则立即进入注册的回调。

	// exit;
}

