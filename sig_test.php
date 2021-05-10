<?php

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

$pid = pcntl_fork();

if ($pid == -1) {
    echo 'fork failed';exit;
}elseif ($pid) {
    sleep(3);
    echo "im farther";
    exit;
    // posix_kill($pid, SIGTERM);
    // pcntl_wait($status);
    //注意：子进程执行posix_kill(getmypid(), SIGTERM);后，父进程会收到SIGCHLD，然后执行sig_func(SIGCHLD)
}else{
    sleep(5);
    echo "im child";

    // sleep(2);
    // posix_kill(getmypid(), SIGTERM);
    echo "111\r\n";
}

$a = $pid;
for (;;) { 
    echo $a . "\r\n";
    $a ++;
    sleep(1);
}


