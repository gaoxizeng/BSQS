<?php
namespace app\backend\controller;
use app\backend\model\admin;
use think\Session;
use think\Db;
use think\view;
use app\backend\controller\Base;
use think\Request;
class Admins extends Base{
    public function _initialize()
    {
        $userId=Session::get("id");//这是用户的id
        if($userId!=1)
        {
            $this->success("你无该权限",url("indexs/index"),2);
        }
    }
    //添加管理员
    public function admin_add(){
        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);
        return $this->fetch("admin_add");
    }
    //添加管理员后台
    public function admin_add_do(Request $request){
        $obj = new admin();
        $post=$request->post();
        unset($post['confirm_pwd'],$post['token'],$post['submit']);
        $post["add_time"]=date("Y-m-d H:i:s");
        $post["admin_pwd"]=md5($post["admin_pwd"]);
        $str=Db::name("admin")->insert($post);//这是添加用户        
        if($str)
        {
            $admin_name =  Session::get('user');
            if($admin_name=="admin"){
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"超级管理员添加管理员:添加成功",
                ]);
            }else{
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"管理员添加管理员:添加成功",
                ]);
            }
            $this->success("添加成功","admins/admin_list");
        }
        else{
            
            $this->error("添加失败","admins/admin_add");
        }
    }
    //添加权限
    public function power(Request $request)
    {
        $post=$request->post();
        unset($post["token"],$post["submit"]);
        $bool=Db::table("access")->insert($post);
        if($bool)
        {
            $admin_name =  Session::get('user');
            $admin_name=="admin";
            $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"超级管理员新增权限：新增权限成功",
                ]);
            $this->success("新增权限成功",url("admins/admin_rbac"),2);
        }
        else
        {
            $this->success("新增权限失败",url("admins/admin_rbac"),2);
        }

    }
    
    //管理员列表
    public function admin_list(){
        $obj = new admin();
        $res = $obj->admin_select();
        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);
        $this->assign("res",$res);
        return $this->fetch('admin_list');
    }
    //验证唯一
    public function check_name(){
       // echo 123;
        $obj = new admin();
        $admin_name = $_POST['admin_name'];
        $res =  $obj->check_name($admin_name);
      //  print_r($res);die;
        if($res){
            echo "2";// 用户名已存在
        }else{
            echo '3'; //可以使用
        }
    }
    //权限的添加
    public function admin_rbac(){
        $admin_name =  Session::get('user');
        $power=Db::table("access")->where("p_id","eq",0)->select();
        // var_dump($power);
        $pow=Db::table("access")->select();//获取所有的权限
        $user=Db::table("admin")->select();
        return view('admin_rbac',["power"=>$power,"pow"=>$pow,"user"=>$user,'admin'=>$admin_name]);
    }
    //分配权限
    public function rbac_do(Request $request)
    {
        $post=$request->post();
        unset($post['token'],$post['submit']);
        $userId=$post['user_id'];
        $str="";
        Db::startTrans();
        try{
            $db=Db::table("user_powe")->where("user_id","eq",$userId)->select();
            if($db)
            {
                Db::table("user_powe")->where("user_id","eq",$userId)->delete();
            }
            foreach($post['power'] as $k)
            {
                Db::table("user_powe")->insert(["user_id"=>$userId,"power_id"=>$k]);
            }
            // 提交事务
            Db::commit();
            $str=1;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $str=2;
        }
        if($str==2)
        {
            $this->error("添加失败","admins/admin_rbac");
        }
        else{
            $this->success("添加成功","admins/admin_rbac");
        }

        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);
        return $this->fetch('admin_rbac');
    }
    //管理员是否启用
    public function is_use(Request $request){
        $post=$request->post();
        $list=Db::table('admin')->where('admin_id',$post['admin_id'])->find();
        if($list['admin_name']=="admin"){
            $this->error('超级管理员不能禁用',url("admins/admin_list"));
        }else{
            $res=Db::table("admin")->where('admin_id',$post['admin_id'])->update(['is_use'=>$post['is_use']]);
            echo $res;
        }
        
    }

    //管理员删除
    public function del(Request $request){
        $admin_id=$request->get('id');
        $list=Db::table('admin')->where('admin_id',$admin_id)->find();
        if($list['admin_name']=="admin"){
            $this->error('超级管理员不能删除',url("admins/admin_list"));
        }else{
            $res=Db::table('admin')->where('admin_id',$admin_id)->delete();
            if($res){
                $admin_name =  Session::get('user');
                if($admin_name=="admin"){
                    $res1 = Db::table("log")->insert([
                        "admin_name"=>$admin_name,
                        "log_time"=>date("Y-m-d H:i:s"),
                        "content"=>"超级管理员删除管理员:删除成功",
                    ]);
                }else{
                    $res1 = Db::table("log")->insert([
                        "admin_name"=>$admin_name,
                        "log_time"=>date("Y-m-d H:i:s"),
                        "content"=>"管理员删除管理员:删除成功",
                    ]);
                }
                $this->success('删除成功',url("admins/admin_list"));
            }else{
                $this->error('删除失败',url("admins/admin_list"));
            }
        }
    }
}

