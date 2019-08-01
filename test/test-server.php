<?php
/**
 * @author WhoAmI
 * @date   2019-08-01
 */

umask(0);
$pid = pcntl_fork();
if (-1 === $pid) {
    exit("process fork fail\n");
} elseif ($pid > 0) {
    exit(0);
}

// 将当前进程提升为会话leader
if (-1 === posix_setsid()) {
    exit("process setsid fail\n");
}

// 再次fork以避免SVR4这种系统终端再一次获取到进程控制
$pid = pcntl_fork();
if (-1 === $pid) {
    exit("process fork fail\n");
} elseif (0 !== $pid) {
    exit(0);
}


fclose(STDOUT);
fclose(STDERR);
$file   = __DIR__ . "/log.log";
$STDOUT = fopen($file, "a");
$STDERR = fopen($file, "a");

while (true) {
    sleep(1);
    echo posix_getpid();
    echo PHP_EOL;
}