<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ extends BaseController
{
    public function index(){
//        $this->AMQPStream2();
        $this->AMQPStream3();
//        $this->AMQPStream4();
    }

    public function AMQPStream2(){
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('hello', false, false, false, false);

        $msg = new AMQPMessage('Hello World???');
        $channel->basic_publish($msg, '', 'hello');

        echo " [x] Sent 'Hello World!!!'\n";

        $channel->close();
        $connection->close();
    }
    // 工作队列
    public function AMQPStream3(){
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
        $channel->queue_declare('task_queue', false, true, false, false);

        $msgStr = str_pad('task',rand(5,9),'.').date("Y-m-d H:i:s");

        $msg = new AMQPMessage($msgStr,array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT) );# 使消息持久化
        $channel->basic_publish($msg, '', 'task_queue');

        echo " [x] Sent 'Hello World!!!'\n";

        $channel->close();
        $connection->close();
    }
    // 订阅 使用交换机 fanout
    public function AMQPStream4(){
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->exchange_declare('logs', 'fanout', false, false, false);
//        $channel->queue_declare('task_queue', false, true, false, false);

        $msgStr = str_pad('task',rand(5,9),'.').date("Y-m-d H:i:s");

        $msg = new AMQPMessage($msgStr,array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT) );# 使消息持久化
        $channel->basic_publish($msg, 'logs');
        echo " [x] Sent '{$msgStr}'\n";

        $channel->close();
        $connection->close();
    }

    public function AMQPStream()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        // 创建通道
        $channel = $connection->channel();

        $channel->queue_declare('hello', false, false, false, false);

        $channel->exchange_declare('vckai_exchange', 'direct', false, false, false);

        // 绑定消息交换机和队列
        $channel->queue_bind('hello', 'vckai_exchange');

        $msg = new AMQPMessage('Hello World!', ['delivery_mode' => AMQPMessage::DELIVERY_MODE_NON_PERSISTENT]);

        $channel->basic_publish($msg, 'vckai_exchange', 'hello');

        echo " [x] Sent 'Hello World!'\n";
    }
}
