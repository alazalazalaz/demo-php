<?php


// 注意：
// 1、创建管道，使用posix_mkfifo();
// 2、管道必须为一个进程写，另一个进程读。
// 3、对于一个存在的管道，进程A调用fopen($fd, 'w')，会阻塞，直到另一个进程B调用fopen($fd, 'r')，此时AB进程都继续往后执行， 然后B如果调用fread()，会阻塞（因为B是读，必须要等写完成），直到A调用fclose()或者A进程结束。
// 4、步骤3同理，进程B先调用fopen($fd, 'r'),会阻塞，直到另一个进程A调用fopen($fd, 'w')，B的fread()也会阻塞直到A的fclose()或者A进程结束。
// 5、流程图 https://www.processon.com/diagraming/5e099c49e4b0c1ff2117574e
// 6、如果是多个fopen($fd, 'w')单个fopen($fd, 'r')，read会阻塞到多个write全部写入完毕才能读出。

$path = './pipefile/g.pipe';


if (!file_exists($path)) {
	if (!posix_mkfifo($path, 0666)) {
		echo "create pipe failed \r\n";exit;
	}
}



plog("ready to fopen...");
$fd = fopen($path, 'w');
plog("fopen over.goto sleep 5s...");
sleep(5);
plog("sleep over, ready to fwrite1...");
var_dump(fwrite($fd, 'im write p1'));
plog("write over, goto sleep 5s...");

fclose($fd);
sleep(5);
// plog("sleep over, ready to fwrite2...");
// var_dump(fwrite($fd, 'im write p2'));
plog("done");

function plog($string){
	echo sprintf("【%s】, %s", date('H:i:s'), $string . "\r\n");
}