<?php 
	namespace app\backend\controller;
	use think\Session;
	use think\Controller;
	use think\Db;
    use think\Request;
	class Base extends Controller{
	    //这是判断是是否是登录
		public function _initialize(){
             $res=Session::get('user');
   
	       if(empty(Session::get('user'))){
	           $this->success("请先登录","login/login");
	       }
            if($res !="admin"){
                $this->is_power();
            }
	    }

	    //判断用户的权限
        public function is_power()
        {

            $userId=Session::get("id");
            $bool="";
            $powerId=Db::table("user_powe")->where("user_id","eq",$userId)->select();

            if($powerId==false)
            {
                $this->error("请申请权限",url("Login/login"));
            }
            foreach ($powerId as $k=>$v)
            {
                $pId[]=$v['power_id'];
            }
            $power=Db::table("access")->where('access_id','in',$pId)->select();//这是获取当前用户的权限
            $request=Request::instance();
            $controllerName=$request->controller();
            $po=$controllerName;
            foreach ($power as $k=>$v)
            {
                $arr[]=$v['access_url'];
            }
            if($po!="Indexs" && !in_array($po,$arr))
            {
                $this->error("请申请权限",url("indexs/index"),3);
            }


        }
	}