<?php
namespace app\frontend\controller;
use think\Controller;
use think\Db;
use think\Session;
use think\Cookie;
use think\Request;
use think\cache\driver\Redis;
class User extends  Controller{
	/*
		郭诗慧
	*/
	// 用户收货地址
	public function user_address(){
		$user_id =  Session::get('user_id');
		$list['newPath'] =  Session::get('newPath');
		$list['user_nickname'] =  Session::get('user_nickname');
		$area = Db::table('area')->where('parent_id',0)->select();
		$address =  Db::table('address')->where('user_user_id',$user_id)->select();
			$this->assign('area',$area);
			$this->assign('list',$list);
			$this->assign('address',$address);
		//设为默认
		$this->assign('area',$area);
		$this->assign('address',$address);
		$this->assign('list',$list);
		return $this->fetch('user_address');
	}
	public function province(){
        $id = $_POST['id'];
        // echo $id;die;
       	$area = Db::table('area')->where('parent_id',$id)->select(); 
        $province= json_encode($area,JSON_UNESCAPED_UNICODE);
        return $province;
    }
    public function city(){
        $id = $_POST['id'];
        // echo $id;die;
       	$city = Db::table('area')->where('parent_id',$id)->select();
       	// var_dump($city);die;
        $city= json_encode($city,JSON_UNESCAPED_UNICODE);
        return $city;
    }
    public function area(){
        $id = $_POST['id'];
        // echo $id;die;
       	$area = Db::table('area')->where('parent_id',$id)->select(); 
       	// var_dump($city);die;
        $area= json_encode($area,JSON_UNESCAPED_UNICODE);
        return $area;
    }
    public function add_address(){
    	//var_dump($_POST);die;
    	$user_id = Session::get('user_id');
		if(empty($user_id)){
			$this->error('请先登录','login/login');
		}
    	$country = Db::table('area')->where('region_id',$_POST['country'])->find();
    	$country_name = $country['region_name'];
    	$province = Db::table('area')->where('region_id',$_POST['province'])->find();
    	$province_name = $province['region_name'];
    	$city = Db::table('area')->where('region_id',$_POST['city'])->find();
    	$city_name = $city['region_name'];	
    	$area = Db::table('area')->where('region_id',$_POST['area'])->find();
    	/*$country = Db::table('area')->where('region_id',$country_id)->find();
    	$country_name = $country['region_name'];
    	$province = Db::table('area')->where('region_id',$province_id)->find();
    	$province_name = $province['region_name'];
    	$city = Db::table('area')->where('region_id',$city_id)->find();
    	$city_name = $city['region_name'];
    	$area = Db::table('area')->where('region_id',$area_id)->find();*/
    	$area_name = $area['region_name'];
    	$address_region = $country_name.$province_name.$city_name.$area_name;
    	unset($_POST['country']);
    	unset($_POST['province']);
    	unset($_POST['city']);
    	unset($_POST['area']);
    	$_POST['address_region'] = $address_region;
    	$_POST['user_user_id'] = $user_id;
    	//print_r($_POST);die;
    	$_POST['is_default']=isset($_POST['is_default'])?$_POST['is_default']:0;
    	$data = Db::table('address')->insert($_POST);
    	if($data){
    		$this->redirect('user/user_address');
    	}
    	else {
    		$this->redirect('user/user_address');
    	}
    	
    	// $res=Db::table('address')->insert($_POST);
    	// if($res){
    	// 	$this->success('添加地址成功','user/user_address');
    	// }else{
    	// 	$this->success('添加地址失败');
    	// }
    }
    public function del_address(){ 
    	$id = $_GET['id'];
    	$data = Db::table('address')->where('address_id',$id)->delete();
    	if($data){
    		$this->redirect('user/user_address');
    	}
    	else {
    		$this->redirect('user/user_address');
    	}
    }
	// 用户中心
	public function user_center(){
		$user_id =  Session::get('user_id');
		$list['user_nickname'] = Session::get('user_nickname');
		$list['newPath']= Session::get('newPath');
		$data = Db::table('userinfo')->where('user_id',$user_id)->find();
		$res = Db::table('collect')->where('user_id',$user_id)->select();
		$arr = [];
		foreach ($res as $key => $value) {
			$arr[] = $value['goods_id'];
		}
		$goods = Db::table('goods')->where('goods_id','in',$arr)->select();
		$this->assign('goods',$goods);
		$this->assign('list',$list);
		$this->assign('uname',$data['user_nickname']);
		return $this->fetch('user_Center');
	}
	// 用户收藏
	public function user_collect(){
		$list['newPath'] =  Session::get('newPath');
		$list['user_nickname'] =  Session::get('user_nickname');
		$user_id = Session::get('user_id');
		$collect = Db::table('collect')->where('user_id',$user_id)->select();
		$data = [];
		foreach ($collect as $key => $value) {
			$data[] = $value['goods_id'];
		}
		$data = implode(',', $data);
		$goods = Db::table('goods')->where('goods_id','in',$data)->paginate(3);
		$page = $goods->render();
		$this->assign('page',$page);
		$this->assign('goods',$goods);
		$this->assign('list',$list);
		return $this->fetch('user_Collect');
	}
	// 用户中心个人信息设置
	public function user_info(){
	    $redis=new \Redis();
        $redis->connect("127.0.0.1",6379); 

		$user_id = Session::get('user_id');
		$list['user_nickname'] = Session::get('user_nickname');
		$list['newPath']= Session::get('newPath');
		$data=$redis->get($user_id."info");
		if($data){
            $data=json_decode($data,true);

		}else{
			$data = Db::table('userinfo')->where('user_id',$user_id)->find();
			$datas = json_encode($data,true);
			$redis->set($user_id."info",$datas);
			$redis->expireAt($user_id."info",time()+60*60*12);
		
		}
		$this->assign('list',$list);
		$this->assign('data',$data);
		return $this->fetch('user_info');
	}
	public function jiasou(){
		$search = input('post.search');
	    $redis=new \Redis();
        $redis->connect("127.0.0.1",6379); 
        $data=$redis->get($search);
		if($data){
            $data=json_decode($data,true);
            echo 'redis';
            print_r($data);die;
		}else{
			$datas=Db::table('goods')
		    ->where('goods_name','like','%'.$search.'%')
		    ->where('is_del',0)
		    ->select();
			$datas = json_encode($datas,true);
			$redis->set('search',$datas);
			$redis->expireAt('search',time()+60*60*12);
		
		}

	}
	public function do_userinfo(){
		$user_id = Session::get('user_id');
		if(empty($user_id)){
			$this->error('请先登录','login/login');
		}
		$data = Db::table('userinfo')->where('user_id',$user_id)->find();
		if ($data) {
			// var_dump($_FILES['headportrait']);die;
			if($_FILES['headportrait']['name'] == ''){
				$list = Db::table('userinfo')->where('user_id', $user_id)->update($_POST);
				if($list){
					$user = Db::table('userinfo')->where('user_id',$user_id)->find();
					Session::set('user_nickname',$user['user_nickname']);
					Session::set('newPath',$user['headportrait']);
					$this->redirect('user/user_info');
				}
				else {
					$this->redirect('user/user_info');
				}
			}else {
				$file = $_FILES['headportrait'];
				// var_dump($file);die;
				$tmp_name = $file['tmp_name'];
				$name = $file['name'];
				$newPath = "./uploads/".$name;
				move_uploaded_file($tmp_name, $newPath);
				$_POST['headportrait'] = $newPath;
				$list = Db::table('userinfo')->where('user_id', $user_id)->update($_POST);
				if($list){
					$user = Db::table('userinfo')->where('user_id',$user_id)->find();
					Session::set('user_nickname',$user['user_nickname']);
					Session::set('newPath',$user['headportrait']);
					$this->redirect('user/user_info');
				}
				else {
					$this->redirect('user/user_info');
				}
			}	

		}
		else {
			$_POST['user_id'] = $user_id;
			$file = $_FILES['headportrait'];
			$tmp_name = $file['tmp_name'];
			$name = $file['name'];
			$newPath = "./uploads/".$name;
			move_uploaded_file($tmp_name, $newPath);
			$_POST['headportrait'] = $newPath;
			$data = Db::table('userinfo')->insert($_POST);
			if($data){
				$list = Db::table('userinfo')->where('user_id',$user_id)->find();
				Session::set('user_nickname',$list['user_nickname']);
				Session::set('newPath',$list['headportrait']);
				$this->redirect('user/user_info');
			}
			else {
				$this->redirect('user/user_info');
			}
		}
	}
	public function del_detailed(){
		$post['goods_id'] = $_GET['goods_id'];
	    $post['user_id'] = Session::get('user_id');
	    $collect = Db::table('collect')->where('user_id',$post['user_id'])->where('goods_id',$post['goods_id'])->delete();
	    if($collect){
	     	$del =  Db::table('goods')->where('goods_id', $post['goods_id'])->setDec('num');
	    	if($del){
	    		echo "<script>alert('取消成功');history.go(-1);</script>";
	    	}
	    	else {
	    		echo "<script>alert('取消失败');history.go(-1);</script>";
	    	}
	    }
	}
	public function user_nickname(){
		$user_nickname = $_POST['name'];
		$nick = Db::table('userinfo')->where('user_nickname',$user_nickname)->find();
		// var_dump($nick);die;
		if(!empty($nick)){
			echo 1;
		}
		else {
			echo 0;
		}
	}
	// 用户积分
	public function user_integral(){
		$list['user_nickname'] = Session::get('user_nickname');
		$list['newPath']= Session::get('newPath');
		$this->assign('list',$list);
		return $this->fetch('user_integral');
	}
	// 更改密码
	public function user_password(){
		$list['user_nickname'] = Session::get('user_nickname');
		$list['newPath']= Session::get('newPath');
		$this->assign('list',$list);
		return $this->fetch('user_Password');
	}
	public function user_pwd(){
		$user_id = Session::get('user_id');
		$pwd1 = $_POST['pwd1'];
		// echo $pwd1;die;
		$data = Db::table('user')->where('user_id',$user_id)->find();
		$user_pwd = $data['user_pwd'];
		if($pwd1 != $user_pwd){
			echo 1;
		}
		else {
			echo 0;
		}
	}
	public function updatepwd(){
		$user_id = Session::get('user_id');
		if(empty($user_id)){
			$this->error('请先登录','login/login');
		}
		$pwd2 = $_POST['pwd2'];
		$list = Db::table('user')->where('user_id',$user_id)->update(['user_pwd' => $pwd2]);
		if($list){
			Session::delete('user_id');
			Session::delete('user_name');
			$this->success('请重新登录','login/login');
		}
		else {
			$this->error('修改失败','user/user_Password');
		}
	}
	public function user_order(){
		$list['user_nickname'] = Session::get('user_nickname');
		$list['newPath']= Session::get('newPath');
		$user_id = Session::get('user_id');
		if(empty($user_id)){
			$this->error('请先登录','login/login');
		}
		$order_main = Db::table('order_main')->where('user_id',$user_id)->where('order_status',1)->order('order_time desc')->select();
		$data = [];
		foreach ($order_main as $key => $value) {
			$bb = Db::table('order')->where('order_main_id',$value['order_id'])->select();
			foreach ($bb as $key => $value) {
				$data[] = $value;
			}
		}
		foreach ($data as $key => $value) {
			$sku = Db::table('sku')->where('sku_id',$value['sku_id'])->find();
			$goods_data = Db::table('goods')->field('goods_name,goods_img')->where('goods_id',$sku['goods_id'])->find();
			$data[$key]['goods_name'] = $goods_data['goods_name'];
			$data[$key]['goods_img'] = $goods_data['goods_img'];
			$data[$key]['goods_price'] = $sku['goods_price'];
			$data[$key]['attr_id'] = explode(',',$sku['attr_id']);
			$data[$key]['spec_id'] = explode(',',$sku['spec_id']);
			foreach ($data[$key]['attr_id'] as $k => $v) {
				$attr_name = Db::table('attr')->field('attr_name')->where('attr_id',$v)->find();
				$spec_name = Db::table('attr')->field('attr_name')->where('attr_id',$data[$key]['spec_id'][$k])->find();
				$data[$key]['attrspac'][$k] = $attr_name['attr_name'].':'.$spec_name['attr_name'];
			}
			$data[$key]['attrspac'] = implode(',',$data[$key]['attrspac']);
			$data[$key]['sum'] = $data[$key]['goods_price'] * $data[$key]['goods_num'];
			$order_main = Db::table('order_main')->where('order_id',$value['order_main_id'])->find();
			$data[$key]['order_no'] = $order_main['order_no'];
			$data[$key]['order_time'] = $order_main['order_time'];
			$data[$key]['order_status'] = $order_main['order_status'];
			$data[$key]['order_type'] = $order_main['order_type'];
		}
		// var_dump($data);die;
		$this->assign('data',$data);
		$this->assign('list',$list);
		return $this->fetch('user_order'); 
	}
	public function user_order2(){
		$list['user_nickname'] = Session::get('user_nickname');
		$list['newPath']= Session::get('newPath');
		$user_id = Session::get('user_id');
		if(empty($user_id)){
			$this->error('请先登录','login/login');
		}
		$order_main = Db::table('order_main')->where('user_id',$user_id)->where('order_status',2)->order('order_time desc')->select();
		$data = [];
		foreach ($order_main as $key => $value) {
			$bb = Db::table('order')->where('order_main_id',$value['order_id'])->select();
			foreach ($bb as $key => $value) {
				$data[] = $value;
			}
		}
		foreach ($data as $key => $value) {
			$sku = Db::table('sku')->where('sku_id',$value['sku_id'])->find();
			$goods_data = Db::table('goods')->field('goods_name,goods_img')->where('goods_id',$sku['goods_id'])->find();
			$data[$key]['goods_name'] = $goods_data['goods_name'];
			$data[$key]['goods_img'] = $goods_data['goods_img'];
			$data[$key]['goods_price'] = $sku['goods_price'];
			$data[$key]['attr_id'] = explode(',',$sku['attr_id']);
			$data[$key]['spec_id'] = explode(',',$sku['spec_id']);
			foreach ($data[$key]['attr_id'] as $k => $v) {
				$attr_name = Db::table('attr')->field('attr_name')->where('attr_id',$v)->find();
				$spec_name = Db::table('attr')->field('attr_name')->where('attr_id',$data[$key]['spec_id'][$k])->find();
				$data[$key]['attrspac'][$k] = $attr_name['attr_name'].':'.$spec_name['attr_name'];
			}
			$data[$key]['attrspac'] = implode(',',$data[$key]['attrspac']);
			$data[$key]['sum'] = $data[$key]['goods_price'] * $data[$key]['goods_num'];
			$order_main = Db::table('order_main')->where('order_id',$value['order_main_id'])->find();
			$data[$key]['order_no'] = $order_main['order_no'];
			$data[$key]['order_time'] = $order_main['order_time'];
			$data[$key]['order_status'] = $order_main['order_status'];
			$data[$key]['order_type'] = $order_main['order_type'];
		}
		// var_dump($data);die;
		$this->assign('data',$data);
		$this->assign('list',$list);
		return $this->fetch('user_order'); 
	}
	public function user_order3(){
		$list['user_nickname'] = Session::get('user_nickname');
		$list['newPath']= Session::get('newPath');
		$user_id = Session::get('user_id');
		if(empty($user_id)){
			$this->error('请先登录','login/login');
		}
		$order_main = Db::table('order_main')->where('user_id',$user_id)->where('order_status','>',2)->order('order_time desc')->select();
		$data = [];
		foreach ($order_main as $key => $value) {
			$bb = Db::table('order')->where('order_main_id',$value['order_id'])->select();
			foreach ($bb as $key => $value) {
				$data[] = $value;
			}
		}
		foreach ($data as $key => $value) {
			$sku = Db::table('sku')->where('sku_id',$value['sku_id'])->find();
			$goods_data = Db::table('goods')->field('goods_name,goods_img')->where('goods_id',$sku['goods_id'])->find();
			$data[$key]['goods_name'] = $goods_data['goods_name'];
			$data[$key]['goods_img'] = $goods_data['goods_img'];
			$data[$key]['goods_price'] = $sku['goods_price'];
			$data[$key]['attr_id'] = explode(',',$sku['attr_id']);
			$data[$key]['spec_id'] = explode(',',$sku['spec_id']);
			foreach ($data[$key]['attr_id'] as $k => $v) {
				$attr_name = Db::table('attr')->field('attr_name')->where('attr_id',$v)->find();
				$spec_name = Db::table('attr')->field('attr_name')->where('attr_id',$data[$key]['spec_id'][$k])->find();
				$data[$key]['attrspac'][$k] = $attr_name['attr_name'].':'.$spec_name['attr_name'];
			}
			$data[$key]['attrspac'] = implode(',',$data[$key]['attrspac']);
			$data[$key]['sum'] = $data[$key]['goods_price'] * $data[$key]['goods_num'];
			$order_main = Db::table('order_main')->where('order_id',$value['order_main_id'])->find();
			$data[$key]['order_no'] = $order_main['order_no'];
			$data[$key]['order_time'] = $order_main['order_time'];
			$data[$key]['order_status'] = $order_main['order_status'];
			$data[$key]['order_type'] = $order_main['order_type'];
		}
		// var_dump($data);die;
		$this->assign('data',$data);
		$this->assign('list',$list);
		return $this->fetch('user_order'); 
	}
	public function user_xiangqing(){
		$list['user_nickname'] = Session::get('user_nickname');
		$list['newPath']= Session::get('newPath');
		$this->assign('list',$list);
		$order_id = $_GET['id'];
		$order_main = Db::table('order_main')->where('order_id',$order_id)->find();
		$yungen = Db::table('yungen')->where('order_id',$order_id)->select();
		// var_dump($yungen);die;
		$this->assign('order_main',$order_main);
		$this->assign('yungen',$yungen);
		return $this->fetch('user_xiangqing');
	}
}