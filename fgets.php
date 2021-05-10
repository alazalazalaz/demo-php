<?php

// while (!feof(STDIN)) {
// 	$content = fgets(STDIN, 1024);
// 	echo $content;
// }



require_once './reactor_select.php';

/**
* 
*/
class Test
{
	
	public function t($args)
	{
		echo " im t\r\n";
		var_dump($args);
	}
}
$callbackObj = new Test();

$eventObj = ReactorSelect::getInstance();

$eventObj->add('so', ReactorSelect::EV_READ, [$callbackObj, 't'], ['111']);

$eventObj->loop();
sleep(3);


$eventObj->add('so', ReactorSelect::EV_READ, [$callbackObj, 't'], ['333']);

$eventObj->loop();


