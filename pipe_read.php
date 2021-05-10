<?php


$path = './pipefile/g.pipe';

if (!file_exists($path)) {
	if (!posix_mkfifo($path, 0666)) {
		echo "create pipe failed \r\n";exit;
	}
}


plog("ready to fopen");
$fd = fopen($path, 'r');
plog("fopen over, ready to fread");

// stream_set_blocking($fd, 0);
echo fread($fd, 20) . "\r\n";
plog("fread over , done");


function plog($string){
	echo sprintf("【%s】, %s", date('H:i:s'), $string . "\r\n");
}