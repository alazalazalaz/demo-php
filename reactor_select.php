<?php

/**
* reactor-select
*/
class ReactorSelect
{
	
	/**
	 * 可读事件
	 */
	CONST EV_READ = 0;

	/**
	 * 可写事件
	 */
	CONST EV_WRITE = 1;

	/**
	 * 异常
	 */
	CONST EV_EXCEPTION = 2;

	/**
	 * 可读事件列表
	 */
	private $readList = [];
	private $readFd = [];

	/**
	 * 可写事件列表
	 */
	private $writeList = [];
	private $writeFd = [];

	/**
	 * 异常事件列表
	 */
	private $exceptionList = [];
	private $exceptionFd = [];

	/**
	 * 对象自身
	 */
	private static $selfObj = '';


	function __construct()
	{
		

	}

	public static function getInstance()
	{
		if (empty(self::$selfObj)) {
			self::$selfObj = new self();
		}

		return self::$selfObj;
	}


	public function add($fd, $evType, array $callback, array $args = [])
	{
		switch ($evType) {
			case self::EV_READ:
				$this->readList[intval($fd)][$evType] = [$callback, $args];
				$this->readFd[intval($fd)] = $fd;
				break;

			case self::EV_WRITE:
				$this->writeList[intval($fd)][$evType] = [$callback, $args];
				$this->writeFd[intval($fd)] = $fd;
				break;

			case self::EV_EXCEPTION:
				$this->exceptionList[intval($fd)][$evType] = [$callback, $args];
				$this->exceptionFd[intval($fd)] = $fd;
				break;

			default:
				# code...
				break;
		}
		
	}

	public function del($fd, $evType, array $callback, array $args = [])
	{
		switch ($evType) {
			case self::EV_READ:
				unset($this->readFd[$fd]);
				unset($this->readList[intval($fd)][$evType]);
				break;
			case self::EV_WRITE:
				unset($this->writeFd[$fd]);
				unset($this->writeList[intval($fd)][$evType]);
				break;
			case self::EV_EXCEPTION:
				unset($this->exceptionFd[$fd]);
				unset($this->exceptionList[intval($fd)][$evType]);
				break;
			
			default:
				# code...
				break;
		}
	}

	public function loop()
	{
		while (true) {
			$read = $this->readFd;
			$write = $this->writeFd;
			$exception = $this->exceptionFd;

			$selectNum = socket_select($read, $write, $exception, null);
			if ($selectNum === false) {
				var_dump("socket_select失败：", socket_strerror(socket_last_error()));
			}

			if ( $selectNum < 1) {
				continue;
			}
			

// @todo 整理代码，响应客户端的输入，实现tcp的创建、读取、发送、关闭
			if ($read) {
				foreach ($read as $readFd) {
					sleep(1);
					echo "select=>loop=>foreach read \r\n";
					list($callback, $args) = $this->readList[intval($readFd)][self::EV_READ];
					call_user_func_array($callback, $args);

					var_dump($this->readFd);

				}
			}

			if ($write) {
				foreach ($write as $writeFd) {
					list($callback, $args) = $this->writeList[intval($writeFd)][self::EV_WRITE];
					call_user_func_array($callback, $args);
				}
			}

			echo "循环结束 $selectNum \r\n";
		}
	}

	public function get()
	{
		return $this->readList;
	}
}


?>