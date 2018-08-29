<?php
namespace app\backend\model;
use think\Db;
class Admin extends \think\Model{
    public function admin_select(){
      $res = Db::table("admin")->select();
      return $res;
    }

    //修改密码
    public function up_pwd($admin_id){
    $data =  Db::table("admin")->where(['admin_id'=>"$admin_id"])->find();
    return $data;
    }
    public function updata_pwd($admin_id,$new_pwd,$admin_pwd){
      $res =  Db::table("admin")->where("admin_id",$admin_id)->update(["admin_pwd"=>"$new_pwd"]);
      //print_r($res);die;
      return $res;
    }
    //验证唯一
    public function check_name($admin_name){
      $res = Db::table("admin")->where("admin_name",$admin_name)->find();
      return $res;

    }

}

