<?php

var_dump(SIGTERM);exit;

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
            echo "SIGCHLD child died\r\n";
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

//注意：
//1、 pcntl_waitpid 是阻塞函数，虽然可以监听指定的pid，但是是阻塞的。监听了a就不能监听其他的子进程，只有等a返回信号后才行。
//2、 pcntl_wait 也是阻塞的，但是他可以同时监听所有子进程，只要任意子进程有信号，就立即返回，返回值就是子进程的pid

$child = [];
$t =  5;

for ($i=0; $i < 5; $i++) { 
	$pid = pcntl_fork();
	$t--;

	if ($pid == -1) {
		echo 'fork failed';exit;
	}elseif ($pid) {
		echo "im farther, child=".$pid."\r\n";
		// pcntl_wait($status);
		$child[] = $pid;
		
	}else{
		sleep($t);
		exit;
	}
}

foreach ($child as $key => $pid) {
	$re = pcntl_wait($status);
	var_dump($re, $pid, $status);
}



