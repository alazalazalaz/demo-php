<?php

$pipePath = './pipefile/a.pipe';

if (file_exists($pipePath)) {
	$fd = fopen($pipePath, 'r');
	echo fread($fd, 1);
}

echo "none";
exit;

if (!function_exists('posix_mkfifo')) {
	echo "方法不存在\r\n";
}

if (file_exists($pipePath)) {
	echo "file exist\r\n";exit;
}

if (!posix_mkfifo($pipePath, 0666)) {
	echo "mk failed\r\n";exit;
}else{
	echo "success\r\n";
}

$pipeFd = fopen($pipePath, 'w');

fwrite($pipeFd, 'test');

echo fread($pipeFd, 4);



