<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/21
 * Time: 10:52
 */
namespace swoole;
use \Swoole\WebSocket;

class WebsocketTool {
    public $server;
    public $ip;


    public function __construct() {
        $this->ip = $this->get_ip();
        $this->server = new Server("0.0.0.0", 9501);   //初始化server服务器
        $this->run();
    }

    public function run(){
        if(empty($this->server)){
            die('server 为空！');
        }
        //注册开启事件
        $this->server->on('open', function ($server, $request) {
            echo "server: handshake success with fd{$request->fd}\n";
            //$server->push($request->fd,json_encode(['fd'=>$request->fd]));
        });

        //注册接收消息事件
        $this->server->on('message', function ($server, $frame) {
            echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
            $server->push($frame->fd, "this is server");
        });

        //注册连接关闭事件
        $this->server->on('close', function ($ser, $fd) {
            echo "client {$fd} closed\n";
        });

        //注册http请求事件
        $this->server->on('request', function ($request, $response) {
            // 接收http请求从get获取message参数的值，给用户推送
            // $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
            foreach ($this->server->connections as $fd) {
                // 需要先判断是否是正确的websocket连接，否则有可能会push失败
                if ($this->server->isEstablished($fd)) {
                    $this->server->push($fd, $request->get['message']);
                }
            }
        });

        //注册redis服务启动事件
        $this->server->on('workerstart',function($serv,$id){
//            $redis = new \Redis();
//            $redis->connect('127.0.0.1', 6379);
//            $serv->redis = $redis;      //将redis注册到server中
        });

        //注册接消息事件
        $this->server->on('receive',function($serv,$id){

        });

        $this->server->start();
    }

    public function get_ip() {
        $ch = curl_init('http://tool.huixiang360.com/zhanzhang/ipaddress.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $a = curl_exec($ch);
        preg_match('/\[(.*)\]/', $a, $ip);
        if(!empty($ip[1])){
            return $ip[1];
        }else{
            return false;//获取本机外网ip失效
        }
    }


}


new WebsocketTool();