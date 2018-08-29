<?php
namespace app\backend\controller;
use \think\Db;
use \think\Session;
class Login extends \think\Controller{
    public function login(){
        return $this->fetch('login');
    }
    public function login_do(){
       $admin_name = $_POST['admin_name'];
       $admin_pwd = $_POST['admin_pwd'];
       $data = Db::table("admin")->where(['admin_name' =>$admin_name])->find();
       if($data['is_use']==1){
             if(empty($data)){
                 echo"3";die;
             }else{
                 if($data['admin_pwd'] == md5($admin_pwd)){
                       Db::startTrans();
                      try{
                      Session::set("id",$data['admin_id']); 
                      Session::set("user",$data['admin_name']); 
                      $res = Db::table("admin")->where(["admin_name"=>$admin_name])->update(["lastlogintime"=>date("Y-m-d H:i:s")]);
                     if($admin_name=="admin"){
                        $res1 = Db::table("log")->insert([
                            "admin_name"=>$admin_name,
                            "log_time"=>date("Y-m-d H:i:s"),
                            "content"=>"超级管理员登录:登录成功啦",
                        ]);
                     }else{
                        $res1 = Db::table("log")->insert([
                            "admin_name"=>$admin_name,
                            "log_time"=>date("Y-m-d H:i:s"),
                            "content"=>"管理员登录:登录成功啦",
                        ]);
                    }
                          // 提交事务
                          Db::commit();
                          echo "1";die;
                      } catch (\Exception $e) {
                          // 回滚事务
                          Db::rollback();
                          echo "2";die;
                      }
                     
                 }else{
                     echo "2";die;
                 }
             }
        }else{
            $this->error('此账号已禁用',url("login/login"));
        }
    }
    public function loginout(){
        Session::delete('user');
        return $this->redirect('login/login');
    }

}

