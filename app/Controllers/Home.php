<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function rand(){
        $nameList = ['张三1','张三2','王五','王五1','王五2','刘六',];
        $rand = array_rand($nameList);
        return json_encode(['name'=>$nameList[$rand]]);
    }

    public function lottery(){
        $nameList = ['一支笔','一块橡皮','一条狗'];
        $rand = array_rand($nameList);
        return json_encode(['name'=>$nameList[$rand]]);
    }
}
