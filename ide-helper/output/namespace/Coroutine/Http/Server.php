<?php

namespace Swoole\Coroutine\Http;

class Server
{

    public $fd = -1;

    public $host = null;

    public $port = -1;

    public $ssl = false;

    public $settings = null;

    public $errCode = 0;

    public $errMsg = '';

    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    /**
     * @return mixed
     */
    public function set(array $settings)
    {
    }

    /**
     * @return mixed
     */
    public function handle($pattern, callable $callback)
    {
    }

    /**
     * @return mixed
     */
    public function onAccept()
    {
    }

    /**
     * @return mixed
     */
    public function start()
    {
    }

    /**
     * @return mixed
     */
    public function shutdown()
    {
    }


}
