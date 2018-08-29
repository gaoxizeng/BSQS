<?php
namespace app\backend\controller;
use think\Db;
use app\backend\model\log;
use think\Session;
use app\backend\controller\Base;
class Logs extends Base{
    public function log(){
        $obj =  new log();
        $res = $obj->select_log();

        $page = isset($_GET['page'])?$_GET['page']:1;
        $size = 5;

        $count = count($res);

        $total_page  = ceil($count/$size);
        $offset = ($page-1)*$size;
        $sql = "select * from log order by log_id desc limit $offset,$size";
        $res = Db::query($sql);

        $url =  $obj->page($count,$page,$total_page);
        $this->assign('url',$url);

        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);

        $url =  $obj->page($count,$page,$total_page);
        $this->assign('url',$url);
        $this->assign("res",$res);
        return $this->fetch('log');
    }

    public function search($value='')
    {
        $page = $_POST["page"];
        $key = $_POST["key"];
        $num = 5;
        $tiao = ($page-1)*$num; 
        $sql = "select * from log where admin_name like '%{$key}%' ORDER BY log_id DESC limit {$tiao},{$num}";
        $res = Db::query($sql);


        if (!$res) {
            return 33;
        }
        return $res;
    }

}

