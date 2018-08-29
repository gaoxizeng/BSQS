<?php
namespace app\backend\controller;
use think\Session;
use app\backend\controller\Base;
class Systems extends Base{
    public function System(){
        return $this->fetch('System');
    }
}

