<?php



$pid = pcntl_fork();
switch ($pid) {
	case -1:
		echo 'fork error';
		break;
	case 0:
		$pid = posix_getpid();
		setProcessTitle("elf worker php");
		echo "im child sleep 5s, current pid={$pid}\r\n";
		sleep(5);
		echo "child over\r\n";
		break;
	
	default:
		$ppid = posix_getpid();
		setProcessTitle("elf master php");
		echo "im farther current pid={$ppid}, return child's pid={$pid}\r\n";
		sleep(10);
		echo "farther over\r\n";
		break;
}

echo "overover \r\n";


/**
 * 此方法对linux并且在php cli模式下有效，mac os必须加sudo启动cli才有效.
 */
function setProcessTitle($title)
{
	if (function_exists('cli_set_process_title')) {
		@cli_set_process_title($title);
	}elseif (function_exists('setproctitle')) {
		@setproctitle($title);
	}
}
