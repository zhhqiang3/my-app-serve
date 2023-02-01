<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class Deal extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'MessageQueue';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'redis:deal';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = '';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'command:name [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
//        $this->redis();
        $this->consumerRabbitMq();
//        $this->consumerRabbitMq2();
//        $this->consumerRabbitMq3();
//        $this->consumerRabbitMq4();

    }
    public function redis(){
        $redis = new \Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->select(6);
        echo "状态: " . $redis->ping();
        while(true) {
            try{
                $value = $redis->LPOP('list');
                //这里进行业务处理
                if($value){
                    print_r($value);
                    echo "\n";
                }
            }catch(Exception $e){
                echo $e->getMessage();
            }
            //1秒钟执行一次
            sleep(1);
        }
    }
    public function consumerRabbitMq()
    {
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        // 创建通道
        $channel = $connection->channel();

        // 设置消费者（Consumer）客户端同时只处理一条队列
        // 这样是告诉RabbitMQ，再同一时刻，不要发送超过1条消息给一个消费者（Consumer），直到它已经处理了上一条消息并且作出了响应。这样，RabbitMQ就会把消息分发给下一个空闲的消费者（Consumer）。
        $channel->basic_qos(0, 1, false);

        // 同样是创建路由和队列，以及绑定路由队列，注意要跟publisher的一致
        // 这里其实可以不用，但是为了防止队列没有被创建所以做的容错处理
        $channel->queue_declare('hello', false, true, false, false);
        $channel->exchange_declare('vckai_exchange', 'direct', false, true, false);
        $channel->queue_bind('hello', 'vckai_exchange','hello');

        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        // 消息处理的逻辑回调函数
        $callback = function($msg) {
            echo " [x] Received ", $msg->body, "\n";
            // 手动确认ack，确保消息已经处理
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            if ($msg->body === 'quit') {
                $msg->delivery_info['channel']->basic_cancel($msg->delivery_info['consumer_tag']);
            }
        };
        $channel->basic_consume('hello', '', false, false, false, false, $callback);


        // 阻塞队列监听事件
        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();

    }
    // hello world
    public function consumerRabbitMq2(){
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('hello', false, false, false, false);

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
        };

        $channel->basic_consume('hello', '', false, true, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        echo "closed!!!";
    }
    // 工作队列
    public function consumerRabbitMq3(){
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('task_queue', false, true, false, false);

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            sleep(substr_count($msg->body, '.'));
            echo " [x] Done", "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        #翻译时注：只有consumer已经处理并确认了上一条message时queue才分派新的message给它
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('task_queue', '', false, false, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        echo "closed!!!";
    }
    // 发布-订阅
    public function consumerRabbitMq4(){
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

//        $channel->queue_declare('task_queue', false, true, false, false);
        $channel->exchange_declare('logs', 'fanout', false, false, false);
        list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);
        $routing_key = 'black';
        $channel->queue_bind($queue_name, 'logs',$routing_key);

        echo " [*] Waiting for messages. To exit press CTRL+C\n";

        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
//            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        #翻译时注：只有consumer已经处理并确认了上一条message时queue才分派新的message给它
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
        echo "closed!!!";
    }
}

