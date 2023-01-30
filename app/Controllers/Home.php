<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function redis()
    {
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->select(6);
        try{
            $res = $redis->LPUSH('list',' '.date("Y-m-d H:i:s"));
        }catch(Exception $e){
            $res = $e->getMessage();
        }
        return json_encode(['success' => !is_string($res),'result'=>$res]);
    }

    public function rand()
    {
        $nameList = ['张三1', '张三2', '王五', '王五1', '王五2', '刘六',];
        $rand = array_rand($nameList);
        return json_encode(['name' => $nameList[$rand]]);
    }

    public function lottery()
    {
        $nameList = ['一支笔', '一块橡皮', '一条狗'];
        $rand = array_rand($nameList);
        return json_encode(['name' => $nameList[$rand]]);
    }
}
