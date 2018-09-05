<?php
namespace app\frontend\controller;
use think\Controller;
use think\Db;
use think\Session;
use think\Cookie;
use think\Request;
use app\frontend\model\goods;
class Index extends Controller{
	// 商品首页
	public function index(){
		/*
			郭诗慧
			茶叶百科
		 */
		$data = Db::table('article')->select();
        $content = Db::table('article')->where("is_status",2)->select();
        //是否热销
        $res = Db::table("goods")->where(['is_hot' => 1, 'is_del' => 0])->order(['add_time' => 'desc'])->limit(8)->select();
        //print_r($res);die;
        //是否新品
        $new_goods = Db::table("goods")->where(['is_new' => 1, 'is_del' => 0])->order(['add_time' => 'desc'])->limit(8)->select();
        //推荐茶叶
        $refer =  Db::table('article')->where(['is_status' => 2])->select();
//        print_r($refer);die;
        //巴山系列
        $ba = Db::table("goods")->where(['brand_id' => 1, 'is_del' => 0])->order(['add_time' => 'desc'])->limit(8)->select();
        $slide = Db::table('show_pic')->select();
        $refer =  Db::table('article')->where(['is_status' => 2])->select();
        $path = [];
        foreach ($slide as $key => $v) {
            $path[] = $v['path'];
        }

       
       // $arr=$this->ordering($cat,0);//这是分类
     

        $redis=new \Redis();  
        $redis->connect("127.0.0.1",6379); 
        $cat=$redis->get('cat');
            if($cat){
                    $cat=json_decode($cat,true);
             
            }else{
              $cat=Db::table('cat')->where(["is_del"=>0])->select();
              $cat = $this->diguisan($cat);
              $datas = json_encode($cat,true);
              $redis->set("cat",$datas);
              $redis->expireAt("cat",time()+60*60*12);
            
            }
       
        $cat_name = [];
        foreach ($cat as $key => $val) {
            $cat_name[$key]["cat_name"] = $val['cat_name'];
            $cat_name[$key]["cat_id"] = $val['cat_id'];
        }
        // echo "<pre>";
        // var_dump($cat_name);die;
         $user_nickname =   Session::get("user_nickname");
        $user_id =   Session::get("user_id");
        $this->assign([
            'list' => $data,
            'res' => $res,
            "new_goods" => $new_goods,
            "path" => $path,
            "ba" => $ba,
            "cat" => $cat,
            "content" => $content,
            'refer'=>$refer,
             "user_id"=>$user_id,
             "user_nickname"=>$user_nickname
        ]);
      
        // echo $user_nickname;die;
       // echo $user_id;die;
     
        return $this->fetch('index');
    }

    //首页对商品的查询
    public function selectgoods(Request $request)
    {
        $post=$request->post("where");
        $str=preg_split("/(?<!^)(?!$)/u",$post);
        $where="";
        foreach ($str as $k => $v){
            $where.="`goods_name` like '%$v%' or ";
        }
        $string=rtrim($where," or");
        $Good=new goods();
        $redisArr=$Good->getGoods($post);
        if($redisArr==false)
        {
            $redisArr=$Good->selectGoods($string,$post);
        }
        $array["cat_json"]=json_decode($redisArr[$post."cat_json"],true);//该这是你所点击分类的子级分类
        $array["goods_json"]=json_decode($redisArr[$post."goods_json"],true);//这是所分类的所有的商品
        $array["brand_json"]=json_decode($redisArr[$post."brand"],true);//这是所有的品牌
        $this->assign([
          "cat"=>$array["cat_json"],
          "goods"=>$array['goods_json'],
          "brand"=>$array["brand_json"]
          ]);
        return $this->fetch('product_list');
//        echo "<pre>";
//        var_dump($redisArr);
    }
    public function diguisan($arr, $path = 0)
    {
        $data = array();
        foreach ($arr as $key => $val) {
            //如果当前的父级id是上条记录的权限id
            if ($val['parent_id'] == $path) {

                $data[$key] = $val;
                $data[$key]['son'] = $this->diguisan($arr, $val['cat_id']);
            }
        }
        return $data;
        $cat=Db::table('cat')->where(["is_del"=>0])->select();
//        $cat_name=[];
//        foreach ($cat as $key => $val) {
//        	$cat_name[]=$val['cat_name'];
//        }
        $arr=$this->ordering($cat,0);//这是分类
       // echo "<pre>";
       // var_dump($arr);die;
        $this->assign([
            'list'=>$data,
            'res'=>$res,
            "new_goods"=>$new_goods,
            "path"=>$path,
            "cat"=>$arr,
            "content"=>$content
        ]);
		return $this->fetch('index');
	}


    //无限极分类
    public function ordering($arr,$parentid)
    {
        $array=array();
        foreach($arr as $k=>$v)
        {
            if($v['parent_id']==$parentid)
            {
                $array[$k] = $v;
                $array[$k]['child'] = $this->ordering($arr,$v['cat_id']);
            }
        }
        return $array;
    }
	public function product_list(Request $request){
      $carId=$request->get('catid');
        $array=$this->redis($carId);
//        echo "<pre>";
       //var_dump($array["cat_json"]);die;
        $this->assign(["cat"=>$array["cat_json"],"goods"=>$array['goods_json'],"brand"=>$array["brand_json"]]);
    return $this->fetch('product_list');
  }


   //通过商品种类查找商品
    public function goods_cat(Request $request)
    {
        $typeId=$request->get("typeid");//这是商品的种类
        $id=$request->get("id");//这是商品的品牌
        $catId=$request->get("catid");//这是顶级的分类
        $arr=$this->redis($catId);
        $array=array();
        if($typeId==""&&$id=="")
        {
            $array=$arr;
            $this->assign(["cat"=>$array["cat_json"],"goods"=>$array['goods_json'],"brand"=>$array["brand_json"]]);
            return $this->fetch('product_list');
        }
        else{
            foreach($arr['goods_json'] as $k=>$v)
            {

                if($typeId!=""&&$id!="")
                {
                    if($typeId==$v['cat_id']&&$id==$v["brand_id"])
                    {
                        $array[]=$v;
                    }
                }
                else
                {
                    if($id!="")
                    {
                        if($id==$v['brand_id'])
                        {
                            $array[]=$v;
                        }
                    }
                    if($typeId!="")
                    {
                        if($typeId==$v['cat_id'])
                        {
                            $array[]=$v;
                        }
                    }
                }
            }
            $this->assign(["cat"=>$arr["cat_json"],"goods"=>$array,"brand"=>$arr["brand_json"]]);
            return $this->fetch('product_list');
        }
    }
    //这是在控制器里从redis里获取数据
    public function redis($catId)
    {

        $goods=new goods();
        $arr=$goods->getGoods($catId);
        if($arr==false)
        {
            $arr=$goods->addRedis($catId);
        }
        $array["cat_json"]=json_decode($arr[$catId."cat_json"],true);//该这是你所点击分类的子级分类
        $array["goods_json"]=json_decode($arr[$catId."goods_json"],true);//这是所分类的所有的商品
        $array["brand_json"]=json_decode($arr[$catId."brand"],true);//这是所有的品牌
        return $array;
    }
   // 立即购买
  public function lijiorder(){
    $user_id = session::get("user_id");
    $sku_id = input('post.sku_id');
    $num = input('post.num');
    Db::startTrans(); //启动事务
    try {
        $post['order_no']='BSQS'.date('YmdHis').rand('1000','9999');
        $post['order_time']=date('Y-m-d H:i:s');
        $post['user_id']=$user_id;
        $sku = Db::table('sku')->where('sku_id',$sku_id)->find();
        $post['goods_valued'] = $sku['goods_price'];
        $post['price'] = $sku['yuan_price'];
        $order_main = Db::table('order_main')->insert($post);
        if($order_main){
            $lastaddid = Db::name('order_main')->getLastInsID();
            $order['order_main_id']=$lastaddid;
            $order['sku_id'] = $sku_id;
            $order['goods_num'] = $num;
            Db::table('order')->insert($order);
            Db::table('sku')->where(['sku_id'=>$sku_id])->setDec('stock',$num);
            Db::table('sku')->where(['sku_id'=>$sku_id])->setInc('sell',$num);
        }
        Db::commit();
        $reg['msg']=666;
        $reg['addid']=$lastaddid;
        exit(json_encode($reg,true));
    }  catch (\PDOException $e) {
        Db::rollback(); //回滚事务
        $reg['msg']=101;
        exit(json_encode($reg,true));
    }
  }
	//团购
	public function group_buy(){
		return $this->fetch();
	}
	// 订单
  public function orderadd(){
    $user_id =   session::get("user_id");
    $data = input('post.');
    $data['cart_ids']=explode('-',$data['cart_ids']);
    $data['goods_info_num']=explode('-',$data['goods_info_num']);
    //print_r($data);die;
     Db::startTrans(); //启动事务
       try {
          $post['order_no']='BSQS'.date('YmdHis').rand('1000','9999');
          $post['order_time']=date('Y-m-d H:i:s');
          $post['price']=$data['jie_price'];
          $post['goods_valued']=$data['price_sum'];
          $post['user_id']=$user_id;
          Db::table('order_main')->insert($post);
           $lastaddid = Db::name('order_main')->getLastInsID();
          foreach ($data['cart_ids'] as $key => $value) {

            $datas=Db::table('goods_cart')->field('sku_id')->where(['cart_id'=>$value])->find();
            $order['order_main_id']=$lastaddid;
            $order['goods_num']=$data['goods_info_num'][$key];
            $order['sku_id']=$datas['sku_id'];
            Db::table('order')->insert($order);
            Db::table('sku')->where(['sku_id'=>$datas['sku_id']])->setDec('stock',$data['goods_info_num'][$key]);
            Db::table('sku')->where(['sku_id'=>$datas['sku_id']])->setInc('sell',$data['goods_info_num'][$key]);
            Db::table('goods_cart')->where(['cart_id'=>$value])->update(['is_del' => 1]);
          }

            
            Db::commit();
            $reg['msg']=666;
            $reg['addid']=$lastaddid;
            exit(json_encode($reg,true));
            
            //提交事务
        } catch (\PDOException $e) {
            Db::rollback(); //回滚事务
            $reg['msg']=101;
            exit(json_encode($reg,true));

      }
  } 
  //支付宝支付
  public function shaxiang(){
    $post=input('post.');
    $res=Db::table('order_main')->where('order_id',$post['order_id'])->update(['pay' => $post['pay'],'wuliu'=>$post['wuliu'],'order_address_word'=>$post['order_address_word'],'address_id'=>$post['address_id']]);
    Session::set('order_id',$post['order_id']);
    if($res){
      //商户订单号
      //订单名称
      //付款金额
      //商品描述
      return 666;
      
    }else{
      return 101;
    }
  }
  public function pay(){
    $orderdata=Db::table('order_main')->where('order_id',Session::get('order_id'))->find();
    
       $_POST['WIDout_trade_no']=$orderdata['order_no'];
      $_POST['WIDsubject']="巴山雀舌茶叶";
      $_POST['WIDtotal_amount']=$orderdata['goods_valued'];
      $_POST['WIDbody']="";
       include_once $_SERVER['DOCUMENT_ROOT'].'/alipay/wappay/pay.php';
  }
  //修改订单状态
  public function zhifuok(){

     $res=Db::table('order_main')->where('order_id',Session::get('order_id'))->update(['order_status'=>2]);
     if($res){
      return $this->redirect('index/index');
     }
  }
	public function orders(){
     $order_id=input('get.order_id');
     //价钱
     $ordershows=Db::table('order_main')->where(['order_id'=>$order_id])->find();
     $shihui=intval($ordershows['price'])-intval($ordershows['goods_valued']);
     //商品展示
      $ordershow=Db::table('order')->where(['order_main_id'=>$order_id])->select();
      foreach ($ordershow as $key => $value) {
         $goodsdata =Db::table('sku')->field('goods_id,goods_price,yuan_price,attr_id,spec_id')->where(['sku_id'=>$value['sku_id']])->find();
         $goodss=Db::table('goods')->field('goods_name,goods_img')->where(['goods_id'=>$goodsdata['goods_id']])->find();
         $ordershow[$key]['goods_img']=$goodss['goods_img'];
         $ordershow[$key]['goods_name']=$goodss['goods_name'];
         $ordershow[$key]['yuan_price']=$goodsdata['yuan_price'];
         $ordershow[$key]['goods_price']=$goodsdata['goods_price'];
        
         $ordershow[$key]['sum_price']= intval($goodsdata['goods_price'])*$value['goods_num'];
         $ordershow[$key]['attr_id']= explode(',',$goodsdata['attr_id']);
         $ordershow[$key]['spec_id']=explode(',',$goodsdata['spec_id']);
         foreach ($ordershow[$key]['attr_id'] as $k => $val) {
           $attr = Db::table('attr')->field('attr_name')->where(['attr_id'=>$val])->find();
           $spec = Db::table('attr')->field('attr_name')->where(['attr_id'=>$ordershow[$key]['spec_id'][$k]])->find();
           $ordershow[$key]['attrname'][$k]=$attr['attr_name'].':'.$spec['attr_name'];
         }
         $ordershow[$key]['attrname']=implode('<br>',$ordershow[$key]['attrname']);

      }

     //print_r($ordershow);die;
	   $user_id =  session::get("user_id");
	   if($user_id==''){
	       $this->success('请先登录','login/login');
       }

     $addressone=Db::table('address')->where(['user_user_id'=>$user_id,'is_default'=>1])->find();
	   $address=Db::table('address')->where(['user_user_id'=>$user_id])->select();
     //print_r($address);die;

	    $this->assign([
        'address'=>$address,
        'addressone'=>$addressone,
        'ordershow'=>$ordershow,
        'shihui'=>$shihui,
        'price'=>$ordershows['price'],
        'goods_valued'=>$ordershows['goods_valued']
        ]);
     return $this->fetch('orders');
	}
  public function findaddress(){
    $address_id = input('post.address_id');
    $addressone=Db::table('address')->where(['address_id'=>$address_id])->find();
    exit(json_encode($addressone,true));
  }



  public function xihuan(){
    $cart_id = $_GET['id'];
    $goods_cart = Db::table('goods_cart')->where('cart_id',$cart_id)->find();
    $post['goods_id'] = $goods_cart['goods_id'];
    $post['user_id'] = Session::get('user_id');
    $data = Db::table('collect')->where('goods_id', $post['goods_id'])->where('user_id',$post['user_id'])->find();
    if($data){
      echo 2;
    }
    $collect = Db::table('collect')->insert($post);
    if($collect){
      Db::table('goods')->where('goods_id', $post['goods_id'])->setInc('num');
      return $collect;
    }
  }

  //加入购物车
  public function shopping_cart(){
    //echo dirname ( __FILE__ ).DIRECTORY_SEPARATOR;die;
      $datas=Db::table('goods_cart')

    ->alias('c')->join('sku s','c.sku_id = s.sku_id')
    ->join('goods g','s.goods_id = g.goods_id')
    ->field('g.goods_name,g.goods_img,s.goods_price,s.yuan_price,c.goods_num,c.cart_id')
    ->where(['c.user_id'=>Session::get('user_id'),'c.is_del'=>0])
    ->select();
    //print_r($);die;

    $this->assign(['list'=>$datas]);
    return $this->fetch('shopping_cart');
  }
  public function delshopping(){
    $id = $_GET['id'];
    $data = Db::table('goods_cart')->where('cart_id',$id)->update(['is_del'=>1]);
    if($data){
      echo 0;
    }
    else {
      echo 2;
    }
  }
  //产品详细介绍购买
  public function shoucang(){
    $post['goods_id'] = $_POST['goods_id'];
    $post['user_id'] = Session::get('user_id');
    $collect = Db::table('collect')->insert($post);
    if($collect){
      Db::table('goods')->where('goods_id', $post['goods_id'])->setInc('num');
      return $collect;
    }
  }
  public function quxiao(){
    $post['goods_id'] = $_POST['goods_id'];
    $post['user_id'] = Session::get('user_id');
    $collect = Db::table('collect')->where('user_id',$post['user_id'])->where('goods_id',$post['goods_id'])->delete();
    if($collect){
      Db::table('goods')->where('goods_id', $post['goods_id'])->setDec('num');
      Db::table('goods')->where('goods_id', $post['goods_id'])->setDec('num');
      return $collect;
    }
  }
  //同步商品价格
  public function skujia(){
    $sku_id = input('post.sku_id');
    $sku=Db::table('sku')->field('goods_price,yuan_price')->where('sku_id', $sku_id)->find();
    exit(json_encode($sku));
  }
	// 介绍购买
	public function detailed(){
    $user_id = Session::get('user_id');
         $goods_id =   $_GET['goods_id'];
          $goods_sum = Db::table('sku')->where(['goods_id'=>$goods_id])->sum('stock');
          $goods_sell= Db::table('sku')->where(['goods_id'=>$goods_id])->sum('sell');
          //print_r($goods_sum);die;
          $datas=Db::table('sku')
          ->alias('s')->join('goods g','s.goods_id = g.goods_id')
          ->join('attr a','s.attr_id = a.attr_id')
          ->field('g.goods_name,spec_id,s.attr_id,s.stock,s.sell,s.sku_id,s.goods_price,s.yuan_price')
          ->where(['s.goods_id'=>$goods_id])
          ->select();

         foreach ($datas as $key => $value) {
           $datas[$key]['spec_id'] = explode(',',$datas[$key]['spec_id']);
            $datas[$key]['attr_id'] = explode(',',$datas[$key]['attr_id']);

         }

        foreach ($datas as $key => $value) {
             foreach ($datas[$key]['spec_id'] as $k => $v) {
               $attrs=Db::table('attr')->where('attr_id',$datas[$key]['spec_id'][$k])->find();
               $datas[$key]['spec_id'][$k] = $attrs['attr_name'];
                $attrs=Db::table('attr')->where('attr_id',$datas[$key]['attr_id'][$k])->find();
               $datas[$key]['attr_id'][$k] = $attrs['attr_name'];
             }
             
            }

        foreach ($datas as $key => $value) {
          foreach ($datas[$key]['spec_id'] as $k => $v) {
               $datas[$key]['attr_and_spec'][$k] = $datas[$key]['attr_id'][$k].':'.$v;
               
             }$datas[$key]['show'] = implode(',',$datas[$key]['attr_and_spec']);

        }


        // print_r($datas);die;
   //print_r($datas);die;
       /* $this->assign("list",$data);*/

	    $res =  Db::table('goods')->where(['goods_id'=>$goods_id])->find();
	    $goods_img=$res['goods_img'];//商品图片
	    $arr=Db::table('goods')
	    ->alias('g')
	    ->join('album a','g.goods_id=a.goods_id')
	    ->join('goods_photo p','a.album_id=p.album_id')
	    ->where('g.goods_id',$goods_id)
	    ->select();
	   //print_r($res);die;
	    $str ='';
	    foreach ($arr as $key => $value) {
	    	$str .= ','.$value['photo'];
	    }
	    $datass=$goods_img.$str;
	    $data=explode(',',$datass);


        $datass = $goods_img . $str;
        $data = explode(',', $datass);



        //用户浏览记录
        //print_r($_COOKIE);die;

        $history =  isset($_COOKIE['history'])?$_COOKIE['history']:array();

        if(empty($history)){
            //没有浏览记录
            $his = $goods_id;//给cookie 历史记录赋值的数据
            $history_arr=array();//展示的历史数据 空数据 无历史记录
        }else{
            //有浏览记录
            $item = explode("|",$history);//字符串分割数组

            $history_arr = $item;//赋值给历史数据
            foreach($item as $k=>$v){
                if($v ==$goods_id){
                    unset ($item[$k]);
                }
            }
            //所有浏览商品的id
            $his = $goods_id."|".implode("|",$item); //数组拼接字符串

        }
        setcookie("history",$his);
        $list = array();
//        print_r($list);die;
        if(!empty($history_arr)){
            foreach($history_arr as $k=>$v){
                if(empty($v)){
                    continue;
                }
               $broser =  Db::table("goods")->where("goods_id",$v)->find();
              //print_r($broser);die;
                $list[$k]['goods_id'] = $broser['goods_id'];
                $list[$k]['goods_name'] = $broser['goods_name'];
                $list[$k]['goods_img'] = $broser['goods_img'];
                $list[$k]['goods_price'] = $broser['goods_price'];
                $list[$k]['yuan_price'] = $broser['yuan_price'];

            }
         //  print_r($list);die;
        }



      
      $collect = Db::table('collect')->where('user_id',$user_id)->where('goods_id',$goods_id)->find();
	    $this->assign([
	    	'res'=>$res,
	    	'data'=>$data,
	    	'datas'=>$datas,
	    	'goods_sum'=>$goods_sum,
	    	'goods_sell'=>$goods_sell,
        'list'=>$list,
        'user_id'=>$user_id,
        'select'=>$collect,
        'goods_id'=>$goods_id,
        'user_id'=>$user_id
	    	]);
	    // $this->assign("datas",$datas);
		return $this->fetch('detailed');
   }

     public function goods_caradd(){
       $user_id = Session::get('user_id');
       //echo $user_id
       $goods_id=Db::table('sku')->field('goods_id')->where('sku_id',input('post.sku_id'))->find();
       $sku_id = input('post.sku_id');
       $cart = Db::table('goods_cart')->where('is_del','0')->where('goods_id',$goods_id['goods_id'])->where('user_id',$user_id)->where('sku_id',$sku_id)->find();
       // var_dump($cart);die;
       if(empty($cart)){
          

             $res=Db::table('goods_cart')->insert(
              ['user_id'=>$user_id,
               'goods_num'=>input('post.num'),
               'sku_id'=>input('post.sku_id'),
               'goods_id'=>$goods_id['goods_id'],
               'cart_time'=>date('Y-m-d H:i:s'),
              ]);
       
       }
       else {

        // echo 0;die;
     

       
          $goods_num = $cart['goods_num'];
          $num = $goods_num+input('post.num');

          $res = Db::table('goods_cart')
            ->where('goods_id',$goods_id['goods_id'])
            ->where('sku_id',$sku_id)
            ->update(['goods_num'=>$num]);
       }
       if($res){
          if(empty($user_id)){
            echo 2;die;
          }
          else {
            echo 1;die;
          }
       }else{
        echo 0;die;
       }
    }

    //产品详细介绍购买
    public function product_detailed()
    {
        return $this->fetch("product_Detailed");
    }

   //购物车加号
    public  function addup(){
          $goods_num = $_GET['goods_num'];
	      $new_num = $goods_num+1;
           return $new_num;
    }
    //购物车减号
    public  function down()
    {
        $goods_num = $_GET['goods_num'];
        $new_num = $goods_num - 1;
        return $new_num;
    }
}