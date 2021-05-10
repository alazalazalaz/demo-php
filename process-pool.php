<?php



function signal_handler($signal)
{
	switch($signal)
	{
		case SIGTERM:
			print "Caught SIGTERM\n";
			exit;
		case SIGINT:
			print "Caught SIGINT, eg:ctrl + c\n";
			exit;
		case SIGCHLD:
			print "Caught SIGCHLD\n";
			break;
		default:
			echo "default \r\n";
			exit;

	}
}



/**
* 
*/
class ProcessPool
{
	public $workerNum = 2;
	public $workerList = [];
	public $workerPidMap = [];
	
	function __construct($workerNum)
	{
		$this->workerNum = intval($workerNum);
		if ($this->workerNum <= 0) {
			throw new Exception("Error Processing Num:$workerNum", 1);
		}
	}

	public function createPool()
	{
		//创建初始进程
		for ($i=0; $i < $this->workerNum; $i++) { 
			$pid = pcntl_fork();
			if ($pid == -1) {
				throw new Exception("Error Processing Fork Child Process", 1);
			}elseif ($pid) {
				//father
				// $ppid = $pid;
				// $pid = posix_getpid();
				//存入队列
				$this->workerPidMap[$pid] = $pid;

			}else{
				//child
				$pid = posix_getpid();
				//进入worker初始状态

				$this->workerList[$pid] = new worker($pid);
				$this->workerList[$pid]->waitForWork();

				echo "worker退出...\r\n";
				exit();
				// exit或者posix_kill(getmypid(), SIGTERM);
			}
		}

		//维护进程的创建、销毁
		while (1) {
			//信号捕捉函数只需要放在主进程里面
			pcntl_signal(SIGINT, "signal_handler");
			pcntl_signal(SIGCHLD, 'signal_handler');

			//检测用pcntl_signal注册的函数
			if (function_exists(pcntl_signal_dispatch())) {
				pcntl_signal_dispatch();
			}else{
				declare(ticks = 1);
			}

			sleep(3);
			echo "父进程继续创建、销毁等...\r\n";



			//阻塞到有子进程结束
			pcntl_wait($status);
		}
		
	}
}



/**
* worker
*/
class Worker
{
	public $pid = '';
	public $workTimes = 0;//接客次数

	function __construct($pid)
	{
		$this->pid = $pid;
	}

	public function waitForWork()
	{
		echo "pid:".$this->pid." : waitForWork worker ,等待接客...\r\n";
		//创建管道
		Pipe::createPipe($this->pid);

		//阻塞在读取管道那里
		if (Pipe::read($this->pid)) {
			$this->workTimes++;
			//有任务到达
			echo "pid:".$this->pid." : begin do job 第" . ($this->workTimes) . "次接任务,接客完毕...\r\n";

			$this->waitForWork();
		}
	}
}

/**
* 管道相关
*/
class Pipe
{
	const PIPE_CONTENT = 1;
	const PIPE_LENGTH = 2;

	public static function createPipe($pid)
	{
		$path = './pipefile/'. $pid . '.pipe';
		if (!file_exists($path)) {
			if (!posix_mkfifo($path, 0666)) {
				throw new Exception("Creating Pipe failed\r\n", 1);
			}
			echo "创建管道，path=".$path."\r\n";
		}
	}

	public static function read($pid)
	{
		$path = './pipefile/'. $pid . '.pipe';
		$fd = fopen($path, 'r');
		$re = fread($fd, self::PIPE_LENGTH);
		fclose($fd);

		return $re;
	}

}


$obj = new ProcessPool(2);
$obj->createPool();



// while (1) {
// 	$a = trim(fgets(STDIN));

// 	echo $a;
// }
