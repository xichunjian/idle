<?php
namespace idle;

use Workerman\Worker;
use Workerman\Connection\TcpConnection;

class Http
{
    public function run()
    {
        ini_set('display_errors', 'on');
        error_reporting(E_ALL);

        Worker::$onMasterReload = function () {
            if (function_exists('opcache_get_status')) {
                if ($status = opcache_get_status()) {
                    if (isset($status['scripts']) && $scripts = $status['scripts']) {
                        foreach (array_keys($scripts) as $file) {
                            opcache_invalidate($file, true);
                        }
                    }
                }
            }
        };

        Worker::$pidFile = '/workman.pid';
        Worker::$stdoutFile = '/stdout.log'; 
        Worker::$logFile = '/workerman.log';
        Worker::$eventLoopClass = '';
        TcpConnection::$defaultMaxPackageSize = 10 * 1024 * 1024;

        $worker = new Worker('http://0.0.0.0:8787');
        // $propertyMap = [
        //     'name',
        //     'count',
        //     'user',
        //     'group',
        //     'reusePort',
        //     'transport',
        //     'protocol',
        // ];
        // foreach ($propertyMap as $property) {
        //     if (isset($config[$property])) {
        //         $worker->$property = $config[$property];
        //     }
        // }
        $worker->count = 8;
        $worker->name = 'idle';
        // $worker->protocol = 'Workerman\\Protocols\\Http';
        $worker->transport = 'tcp';
        $worker->user = 'www-data';
        $worker->reusePort= false;
        $worker->onWorkerStart = function ($worker) {
            //     require_once base_path() . '/support/bootstrap.php';
            //     $app = new \Webman\App(config('app.request_class', Request::class), Log::channel('default'), app_path(), public_path());
            //     $worker->onMessage = [$app, 'onMessage'];
            // call_user_func([$app, 'onWorkerStart'], $worker);
            $worker->onMessage = function (TcpConnection $connection, $request) {
                $connection->send('hello');
            };
        };

        Worker::runAll();
    }
}
