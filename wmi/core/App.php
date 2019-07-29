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
use Swoole\http\Server;
use wmi\lib\Log;

class App {
    protected $_server = null;
    protected $_host   = "0.0.0.0";
    protected $_port   = 9501;

    public function __construct() {
        $host          = Config::get("app.host");
        $port          = Config::get("app.port");
        $this->_server = new Server($host, $port);
        Log::console("Listen $host:$port");
        $this->_server->set([
            'worker_num'               => 1,
            //'task_worker_num'          => 1,
            'task_enable_coroutine'    => true,
            //'daemonize'                => true,
            'backlog'                  => 128,
            'heartbeat_check_interval' => 60,
            'heartbeat_idle_time'      => 180,
            'user'                     => 'www',
            'group'                    => 'www',
            'pid_file'                 => ROOT . '/data/server.pid',
            'log_file'                 => ROOT . '/data/log/app.log',
            'log_level'                => 1,
        ]);
    }

    public function start() {
        $this->_server->on('start', [$this, "onStart"]);
        $this->_server->on('workerStart', [$this, "onWorkerStart"]);
        $this->_server->on('request', [$this, "onRequest"]);

        //$this->_server->on('task', [$this, 'onTask']);
        //$this->_server->on('finish', [$this, 'onFinish']);


        // Worker正常退出或错误退出时，关闭连接池，释放连接
        $this->_server->on('WorkerStop', [$this, "onWorkerStop"]);
        $this->_server->on('WorkerError', [$this, "onWorkerError"]);

        $this->_server->start();
    }

    public function onStart() {
        cli_set_process_title("wmi_http_server");
        Log::console("Server start");
    }

    public function onWorkerStart(Server $server, int $worker_id) {
        Config::reload();
        include ROOT . "/vendor/autoload.php";
        PoolManager::init();
        new Error();
        Log::console("Worker start");
    }

    public function onRequest(Request $request, Response $response) {
        $req = new \wmi\core\Request($request);
        $res = new \wmi\core\Response($response);
        Dispatcher::resolve($req, $res);
    }

    public function onWorkerStop() {
        PoolManager::close();
    }

    public function onWorkerError(Server $serv, int $worker_id, int $worker_pid, int $exit_code, int $signal) {
        PoolManager::close();
        $msg = "worker_id $worker_id, worker_pid $worker_pid, exit_code $exit_code, signal $signal";
        Log::error("worker error $msg");
    }

    public function onTask(Server $serv, Task $task) {
        //来自哪个`Worker`进程
        $task->worker_id;
        //任务的编号
        $task->id;
        //任务的类型，taskwait, task, taskCo, taskWaitMulti 可能使用不同的 flags
        $task->flags;
        //任务的数据
        $task->data;
        co::sleep(2);
        Log::debug("onTask worker_id: $task->worker_id, id: $task->id, flags: $task->flags");

        //完成任务，结束并返回数据
        $task->finish([123, 'hello']);
    }

    public function onFinish(Server $serv, $task_id, $data) {
        Log::debug("onFinish task_id: $task_id");
    }


}