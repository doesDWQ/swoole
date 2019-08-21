<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/21
 * Time: 15:12
 */
class WS {
    private $server = null;

    public function __construct()
    {
        $this->server = new Swoole\WebSocket\Server("0.0.0.0", 9501);
        $this->run();
    }
    private function run(){
        $this->server->on('open', function (Swoole\WebSocket\Server $server, $request) {
            $server->redis->set('websocket1',$request->fd);
            $server->push($request->fd,json_encode(['client_id'=>$request->fd]));
        });

        $this->server->on('message', function (Swoole\WebSocket\Server $server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            //$server->push($frame->fd, json_encode(['msg'=>'hello']));
        });

        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });


        $this->server->on('workerstart',function($serv,$id){
            $redis = new \Redis();
            $redis->connect('127.0.0.1', 6379);
            $serv->redis = $redis;      //将redis注册到server中
        });

        $this->server->start();
    }
}

new WS();


