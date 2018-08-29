<?php
namespace app\backend\controller;
use think\Session;
use app\backend\controller\Base;
use think\Request;
use think\Db;
class Slides extends Base{
    public function slide_list($page='',$id=''){
    	if (Request::instance()->isPost()){
    	    $file= request()->file('show_img');
            if ($file==null) {
                return $this->success("请选择图片","slides/slide_list");
            }
    	    $info = $file->move(ROOT_PATH . 'public' . DS .'backend'.DS. 'uploads');
    		if ($info) {
    			$path_info='/backend/uploads/'.$info->getSaveName();
    			$path_info=str_replace('\\', '/', $path_info);
    			$data=$_POST;
    			extract($data);
    			$exec=DB::execute("INSERT INTO show_pic values(null,'$show_name','$path_info','$show_link','$sort')");
    			if ($exec==1) {
                    $admin_name =  Session::get('user');
                    if($admin_name=="admin"){
                        $res1 = Db::table("log")->insert([
                            "admin_name"=>$admin_name,
                            "log_time"=>date("Y-m-d H:i:s"),
                            "content"=>"超级管理员添加幻灯片:添加成功",
                        ]);
                    }else{
                        $res1 = Db::table("log")->insert([
                            "admin_name"=>$admin_name,
                            "log_time"=>date("Y-m-d H:i:s"),
                            "content"=>"管理员添加幻灯片:添加成功",
                        ]);
                    }
    		        return $this->select();
    			}else{
    			 
    		        return $this->select();
    			}                 
    		}else{
    			echo $info->getError();
    		}
    	}else{
    		return $this->select($page,$id);
    	}
       
    }

    public function select($value='')
    {
    	$res = Db::name('show_pic')->order('sort desc')->paginate(3);
    	$page=$res->render();
    	return $this->fetch('slide_list',['data'=>$res,'page'=>$page]);
    }


    public function slide_update($id)
    {
        if (Request::instance()->isPost()){
        	$data=$_POST;
    		extract($data);
    	    $file= request()->file('show_img');
    	    if ($file==null) {
    	    	$sql="UPDATE show_pic set `show_name`='$show_name',`path`='$path_info',`show_link`='$show_link',`sort`='$sort' where show_id=$_GET[id]";
                $exec=DB::execute($sql);
    	    }else{
                if (is_file('.' .$path_info)) {
                    $del=unlink ('.' .$path_info);
                }   
    	    	 $info = $file->move(ROOT_PATH . 'public' . DS .'backend'.DS. 'uploads');
                 $path='/backend/uploads/'.$info->getSaveName();
    			 $path=str_replace('\\', '/', $path);
    			 $sql="UPDATE show_pic set `show_name`='$show_name',`path`='$path',`show_link`='$show_link',`sort`='$sort' where show_id=$_GET[id]";
    			$exec=DB::execute($sql);
    	    }
    	        if ($exec>=0) {
                    $admin_name =  Session::get('user');
                    if($admin_name=="admin"){
                        $res1 = Db::table("log")->insert([
                            "admin_name"=>$admin_name,
                            "log_time"=>date("Y-m-d H:i:s"),
                            "content"=>"超级管理员修改幻灯片:修改成功",
                        ]);
                    }else{
                        $res1 = Db::table("log")->insert([
                            "admin_name"=>$admin_name,
                            "log_time"=>date("Y-m-d H:i:s"),
                            "content"=>"管理员修改幻灯片:修改成功",
                        ]);
                    }
    		        return $this->select();
    			}else{
    	
    		        return $this->select();
    			}  
        }else{
        	$update=Db::table('show_pic')->where('show_id',$id)->find();
    	    $res = Db::name('show_pic')->order('sort desc')->paginate(3);
    	    $page=$res->render();
    	    return $this->fetch('slide_list',['data'=>$res,'page'=>$page,'update'=>$update]);
        }

    }

    public function slide_delete($page,$id)
    {
    	$arr=Db::table('show_pic')->where('show_id',$id)->find();
        if (is_file('.' .$arr['path'])) {
            unlink ('.' .$arr['path']);
        }
    

    	$res=Db::table('show_pic')->where('show_id',$id)->delete();
    	if ($res) {
            $admin_name =  Session::get('user');
            if($admin_name=="admin"){
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"超级管理员删除幻灯片:删除成功",
                ]);
            }else{
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"管理员删除幻灯片:删除成功",
                ]);
            }
            $this->success("删除成功","slides/slide_list");


    	}else{
            $this->success("删除失败","slides/slide_list");
    	}
    }

}

