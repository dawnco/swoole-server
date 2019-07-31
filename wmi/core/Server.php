<?php
/**
 * @author WhoAmI
 * @date   2019-07-18
 */

namespace wmi\core;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\Server\Task;
use Swoole\WebSocket\Frame;
use wmi\lib\Log;

class Server {
    protected $_server = null;
    protected $_host   = "0.0.0.0";
    protected $_port   = 9501;

    public function __construct() {
        $host          = Config::get("server.host");
        $port          = Config::get("server.port");
        $this->_server = new \Swoole\http\Server($host, $port);
        Log::console("Listen $host:$port");
        $this->_server->set([
            'worker_num'            => Config::get("server.worker_num"),
            //'dispatch_mode'         => 4,
            //'task_worker_num'          => 1,
            'task_enable_coroutine' => true,
            'daemonize'             => Config::get("server.daemonize"),
            'backlog'               => 128,
            'user'                  => 'www',
            'group'                 => 'www',
            'pid_file'              => ROOT . '/data/server.pid',
            'log_file'              => ROOT . '/data/log/app.log',
            'log_level'             => 1,
        ]);
    }

    public function start() {
        $this->_server->on('start', [$this, "onStart"]);
        $this->_server->on('workerStart', [$this, "onWorkerStart"]);
        $this->_server->on('connect', [$this, "onConnect"]);
        $this->_server->on('request', [$this, "onRequest"]);

        // Worker正常退出或错误退出时，关闭连接池，释放连接
        $this->_server->on('WorkerStop', [$this, "onWorkerStop"]);
        $this->_server->on('WorkerError', [$this, "onWorkerError"]);
        $this->_server->on('Shutdown', [$this, "onShutdown"]);

        $this->_server->start();
    }

    public function onStart() {
        cli_set_process_title("wmi_http_server");
        Log::console("Server start");
    }

    public function onWorkerStart(\Swoole\http\Server $server, int $worker_id) {
        Config::reload();
        include ROOT . "/vendor/autoload.php";
        PoolManager::init();
        new Error();
        Log::console("Worker start", $worker_id);
    }

    public function onConnect(\Swoole\http\Server $server, int $fd, int $reactorId) {
        //Log::console("worker id", $server->worker_id, 'reactor id', $reactorId);
    }

    public function onRequest(Request $request, Response $response) {
        $req = new \wmi\core\Request($request);
        $res = new \wmi\core\Response($response);
        Dispatcher::resolve($req, $res);
    }

    public function onWorkerStop(\Swoole\Server $server, int $worker_id) {
        Log::info("worker stop", $worker_id);
        PoolManager::close();
    }

    public function onShutdown() {
        Log::info("Server shutdown");
    }


    public function onWorkerError(\Swoole\http\Server $serv, int $worker_id, int $worker_pid, int $exit_code, int $signal) {
        PoolManager::close();
        $msg = "worker_id $worker_id, worker_pid $worker_pid, exit_code $exit_code, signal $signal";
        Log::error("worker error $msg");
    }

}