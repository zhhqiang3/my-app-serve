<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

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
        //
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
}
