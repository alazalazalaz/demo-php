<?php

$path = './pipefile/g.pipe';



echo 111;
$fd = fopen($path, 'w');
echo 222;
var_dump(fwrite($fd, 'im write 2'.PHP_EOL));
echo 333;

