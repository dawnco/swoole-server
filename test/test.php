<?php

function line() {
    echo date("H:i:s ");
    echo implode(" ", func_get_args());
    echo "\n";
}

function post($host, $port, $uri, $data) {
    $cli = new Swoole\Coroutine\Http\Client($host, $port);
    $cli->setHeaders([
        'Host'            => $host,
        "User-Agent"      => 'Chrome/49.0.2587.3',
        'Accept'          => 'text/html,application/xhtml+xml,application/xml',
        'Accept-Encoding' => 'gzip',
        'Connection'      => 'close',
    ]);
    $cli->set(['timeout' => -1]);
    $cli->post($uri, $data);
    if ($cli->statusCode != 200) {

        $statusCode = [
            -1 => '连接超时 服务器未监听端口或网络丢失',
            -2 => '请求超时 服务器未在规定的timeout时间内返回',
            -3 => '客户端请求发出后，服务器强制切断连接',
        ];

        line($host, $uri, "status code", $statusCode[$cli->statusCode] ?? $cli->statusCode, 'error code', $cli->errCode);
    }
    $body = $cli->body;
    $r = $cli->close();
    line("close", $r);
    //line($body);
}

function send($p) {
    $sn = post('192.168.0.11', 9999, '/', [
    //$sn = post('192.168.0.11', 80, '/api/login', [
        'p' => $p
    ]);
}

function test($p) {
    go(function () use ($p) {
        send($p);
    });
}

//while (true) {
//    sleep(1);
go(function () {
    $index = 0;
    while (true) {
        $index++;
        co::sleep(1);
        test($index . "-1");
        test($index . "-2");
        test($index . "-3");
        test($index . "-4");
        test($index . "-5");
    }
});

//}

