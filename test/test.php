<?php

function line() {
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
    ]);
    $cli->set(['timeout' => 5]);
    $cli->post($uri, $data);
    if ($cli->statusCode != 200) {
        line($host, $uri, "status code", $cli->statusCode);
    }
    $body = $cli->body;
    $cli->close();
    line($body);
}

function send($token) {

    $sn = post('192.168.0.11', 9999, '/', [
        'gameCode'    => 'qznn',
        'gameRoomId'  => '6',
        "gamePlayId"  => '4',
        "money"       => random_int(10, 30),
        "mines"       => '',
        "paypassword" => '222222',
        "token"       => $token
    ]);
}

function test() {
    $send  = "17749931078";
    $token = send($send);
}

//while (true) {
//    sleep(1);
go(function () {
    while (true) {
        co::sleep(0.01);
        test();
        test();
        test();
        test();
        test();
        test();
    }
});

//}

