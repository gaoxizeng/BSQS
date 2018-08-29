<?php 
namespace app\backend\controller;
use \think\Db;
use \think\Session;
class Brand extends \think\Controller{
	public function show($keywords='')
	{
		$name=$this->se_user();
        if (!$data= Db::name('brand')->where('brand_name','like',"%{$keywords}%")->order('brand_id desc')->paginate(2)) {
            //return $this->fetch('article_list',['res'=>'','admin_name'=>$name,'page'=>'','keywords'=>$keywords]);
        }
        $page = $data->render();
        return $this->fetch('brand_list',['res'=>$data,'admin_name'=>$name,'page'=>$page,'keywords'=>$keywords]);
	}

	public function brand_del($id)
	{
	    $name=$this->se_user();
		if (Db::table('brand')->where('brand_id',$id)->delete()) {
			  $this->success('删除成功', 'show');
		}
		
	}
    public function se_user($value='')
    {
        $admin_name =  Session::get('user');
        return $admin_name;
    }

    public function brand_update($id)
    {
    	if (\think\Request::instance()->isPost()){
    		Db::table('brand')->where('brand_id',$_POST['brand_id'])->setField('brand_name', $_POST['brand_name']);
              $this->success('更新成功', 'show');
    	}
        $name=$this->se_user();
    	$list=Db::table('brand')->where('brand_id',$id)->find();
    	return $this->fetch('brand_add',['data'=>$list,'admin_name'=>$name]);
    }

    public function brand_add($value='')
    {
          $name=$this->se_user();
        if (\think\Request::instance()->isPost()){
            Db::table('brand')->insert(['brand_name'=>$_POST['brand_name']]);
            $this->success('成功', 'show');
        }
        return $this->fetch('brand_add',['data'=>[],'admin_name'=>$name]);
    }


}



















 ?>