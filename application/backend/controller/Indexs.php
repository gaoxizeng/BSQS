<?php
namespace app\backend\controller;
use app\backend\model\admin;
use think\Session;
use think\Request;
use think\Db;
use app\backend\controller\Base;
class Indexs extends Base{
   
    public function index(){
        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);
        return $this->fetch('index');
    }

    //修改密码
    public function up_pwd(){
        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);
        return $this->fetch('up_pwd');
    }
    public function up_pwd1(){
        $obj = new admin();
        $admin_id = Session::get('id');
        $admin_pwd = md5($_POST['admin_pwd']);
        $res =  $obj->up_pwd($admin_id);
       //var_dump($res);
        if($admin_pwd==$res['admin_pwd']){

            echo "1";
        }else{
            echo "3";
        }
    }
    public function group_buy(){
      $admin_name =  Session::get('user');
      $this->assign("admin_name",$admin_name);
      return $this->fetch('group_buy');
    }
    public function up_pwd2(){
        $obj = new admin();
        $new_pwd = md5($_POST['new_pwd']);
        $admin_pwd = md5($_POST['admin_pwd']);
        $admin_id = Session::get('id');
        $res = $obj->updata_pwd($admin_id,$new_pwd,$admin_pwd);
        //print_r($res);die;
       if($res){
           $admin_name =  Session::get('user');
           if($admin_name=="admin"){
               $res1 = Db::table("log")->insert([
                   "admin_name"=>$admin_name,
                   "log_time"=>date("Y-m-d H:i:s"),
                   "content"=>"超级管理员修改密码:修改密码成功",
               ]);
           }else{
               $res1 = Db::table("log")->insert([
                   "admin_name"=>$admin_name,
                   "log_time"=>date("Y-m-d H:i:s"),
                   "content"=>"管理员修改密码:修改密码成功",
               ]);
           }
           $this->success("修改成功,请重新登录","login/login");
       }else{
           $this->error("修改失败","indexs/up_pwd");
       }
    }
}

