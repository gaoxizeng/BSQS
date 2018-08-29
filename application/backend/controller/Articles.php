<?php
namespace app\backend\controller;
use think\Session;
use app\backend\controller\Base;
use think\Db;
use think\Paginator;
class Articles extends Base{ 

    // 列表展示
    public function article_list($keywords=''){
        $name=$this->se_user();
        if (!$data= Db::name('article')->where('title','like',"%{$keywords}%")->order('id desc')->paginate(2)) {
            return $this->fetch('article_list',['res'=>'','admin_name'=>$name,'page'=>'','keywords'=>$keywords]);
        }
        $page = $data->render();
        return $this->fetch('article_list',['res'=>$data,'admin_name'=>$name,'page'=>$page,'keywords'=>$keywords]);
    }
    // 添加
    public function article_add(){
        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);
        return $this->fetch('article_add');

    }
    public function article_add_do(){
        $post = $_POST;
        $time = date('Y-m-d H:i:s');
        $post['add_time'] = $time;
        $data = Db::table('article')->insert($post);
        if($data){
            $admin_name =  Session::get('user');
            if($admin_name=="admin"){
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"超级管理员添加文章:添加成功",
                ]);
            }else{
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"管理员添加文章:添加成功",
                ]);
            }

            $this->success('添加成功','articles/article_list');
        }
        else {
            $this->error('添加失败','articles/article_add');
        }

    }
    // 编辑
    public function article_update(){
        $id = $_GET['id'];
        $list = Db::table('article')->where('id',$id)->find();
        // var_dump($list);
        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);
        $this->assign('res',$list);
        return $this->fetch('article_update');
    }
    public function se_user($value='')
    {
        $admin_name =  Session::get('user');
        return $admin_name;
    }
    public function article_update_do(){
        $post = $_POST;
        $id = $post['id'];
        $title = $post['title'];
        $content = $post['content'];
        $link = $post['link'];
        $post['add_time'] = date('Y-m-d H:i:s');
        $add_time = $post['add_time'];
        unset($post['id']);
        $data = Db::table('article')->where('id', $id)->update([
            'title' => $title,
            'content'=>$content,
            'add_time'=>$add_time,
            'link'=>$link
            ]);
        if($data){
            $admin_name =  Session::get('user');
            if($admin_name=="admin"){
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"超级管理员修改文章:修改成功",
                ]);
            }else{
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"管理员修改文章:修改成功",
                ]);
            }
            $this->success('修改成功','articles/article_list');
        }
        else {
            $this->error('修改失败','articles/article_update');
        }
    }
    // 删除
    public function article_del(){
       $id = $_GET['id'];
       $list  = Db::table('article')->where('id',$id)->delete();
       if($list){
           $admin_name =  Session::get('user');
           if($admin_name=="admin"){
               $res1 = Db::table("log")->insert([
                   "admin_name"=>$admin_name,
                   "log_time"=>date("Y-m-d H:i:s"),
                   "content"=>"超级管理员删除文章:删除成功",
               ]);
           }else{
               $res1 = Db::table("log")->insert([
                   "admin_name"=>$admin_name,
                   "log_time"=>date("Y-m-d H:i:s"),
                   "content"=>"管理员删除文章:删除成功",
               ]);
           }
            $this->success('删除成功','articles/article_list');
        }
        else {
            $this->error('删除失败','articles/article_del');
        }
    }

}


