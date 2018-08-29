<?php
namespace app\frontend\controller;
use think\Controller;
use think\Db;
use think\Session;
use think\Cookie;
class Login extends Controller{
	/*
		郭诗慧
	 */
	// 登录
	public function login(){
		return $this->fetch('login');
	}
	public function login_do(){
		$user_name  = $_POST['user_name'];
		$user_pwd = $_POST['user_pwd'];
		$list  = Db::table('user')->where('user_name',$user_name)->where('user_pwd',$user_pwd)->find();
		if(!empty($list)){
			$user_id = $list['user_id'];
			Session::set('user_name',$user_name);
			Session::set('user_id',$user_id);
			$list = Db::table('userinfo')->where('user_id',$user_id)->find();
			if($list){
					$user_nickname = $list['user_nickname'];
					$newPath = $list['headportrait'];
                    Session::set('user_id',$user_id);
               		Session::set('user_nickname',$user_nickname);
					Session::set('newPath',$newPath);
			}
			$this->success('登陆成功','index/index');
		}
		else {
			$this->error('登陆失败','login/login');
		}
	}
	// 注册
	public function regist(){
		return $this->fetch('regist');
	}
	public function regist_do(){
		if(request()->isPost()){
            $data = $_POST;
            $captcha = $data['captcha'];
            unset($data['captcha']);
            unset($data['user_upwd']);
            // var_dump($data);
            if(!captcha_check($captcha)) {
                // 校验失败
                $this->error('验证码不正确','login/regist');
            }
            else {
            	  $list  = Db::table('user')->insert($data);
            	  if($list){
            	  	$this->success('注册成功','login/login');
            	  }
            	  else{
            	  	$this->error('注册失败','login/regist');
            	  }
            }
		}
	}

	public function telnote(){
		$tel=$_GET['tel'];
		$url="http://v.juhe.cn/sms/send";
		$num=rand(100000,999999);
		Cookie::set('num',$num,3600);
		$arr=[
			'mobile'=>$tel,
			'tpl_id'=>'92953',
			'tpl_value'=>"#code#=$num",
			'key'=>'0bd6f8dbad532e208d3de9b8008b9038'
		];
		$res=json_decode($this->request_post($url,$arr),true);
		if($res['error_code']==0){
			echo 1;
		}else{
			echo 0;
		}
	}

	public function telnote_do(){
		$code=$_GET['code'];
		$arr['user_name']=$_GET['tel'];
		$arr['user_pwd']=$_GET['pwd'];
		$num=Cookie::get('num');
		if($code==$num){
			$res=Db::table('user')->where('user_name',$arr['user_name'])->select();
			if(!empty($res)){
				return 2;
			}else{
				$rest=Db::table('user')->insert($arr);
				if($rest){
					return 1;
				}else{
					return 0;
				}
			}
		}else{
			$this->error('您输入的验证码不正确','login/regist');
		}	
	}

	function request_post($url = '', $param = '') {
	    if (empty($url) || empty($param)) {
	        return false;
	    }
	    $postUrl = $url;
	    $curlPost = http_build_query($param);
	    $ch = curl_init();//初始化curl
	    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
	    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
	    $data = curl_exec($ch);//运行curl
	    curl_close($ch);
	    return $data;
    }

}