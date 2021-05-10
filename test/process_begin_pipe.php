<?php


$pid = 82435;
$path = './pipefile/' . $pid . '.pipe';
// var_dump(file_exists($path), $path);exit;
$fd = fopen($path, 'w');
if (fwrite($fd, 1)) {
	echo "写入pid".$pid."成功\r\n";
}else{
	echo "写入pid".$pid."失败\r\n";
}
