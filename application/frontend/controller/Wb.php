<?php
namespace app\frontend\controller;
use think\Controller;
use think\Db;
use think\Session;
use think\Request;
class Wb extends Controller{

	public function index(Request $request){
		//获取access_token
        $code=$request->get('code');
        // $client_id="3725009620";//App Key
        // $client_secret="61fcfdea2621b27446d91fdcb84f5e87";//App Secret
       	$token_url= "https://api.weibo.com/oauth2/access_token";
       	$arr=[
       		"client_id"=>"3725009620",
       		"client_secret"=>"61fcfdea2621b27446d91fdcb84f5e87",
       		"grant_type"=>"authorization_code",
       		"code"=>$code,
       		"redirect_uri"=>"http://114.116.80.12/frontend/wb/index"
       	];
       	$token_res=json_decode($this->request_post($token_url,$arr),true);
       	$token=$token_res['access_token'];
       	//var_dump($token_res);die;
       	//根据access_token获取用户uid
       	$u_url="https://api.weibo.com/oauth2/get_token_info";
       	$u_res= json_decode($this->request_post($u_url,['access_token'=>$token]),true);
       	$uid= $u_res['uid'];

    	   Session::set('uid',$uid);
        $res=Db::table('user')->where('uid',$uid)->find();
      if($res){
        Session::set('user_name',$res['user_name']);
        Session::set('user_id',$res['user_id']);
        $this->success('登录成功','index/index');
      }else{
        $this->error('你还未绑定，请去完善个人信息',"http://114.116.80.12/frontend/wb/zhuce");
      }
    }   


    public function zhuce(){
        return $this->fetch('zhuce');
    }

    public function zhu_do(){
        $arr=$_POST;
        unset($arr['user_upwd']);
        $arr['uid']=Session::get('uid');
        $res=Db::table('user')->insert($arr);
        if($res){
          $this->success('绑定成功','index/index');
        }else{
          $this->error('绑定失败');
        }
    }

    //curl post方式
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
    //curl get方式
    function request_get($url = '') {
	   	if (empty($url)) {
	       	return false;
	   	}
	        
	   	$postUrl = $url;
	   	$ch = curl_init();//初始化curl
	   	curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
	   	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
	   	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
	   	$data = curl_exec($ch);//运行curl
	   	curl_close($ch);
	   	return $data;
    }
}

