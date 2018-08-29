<?php
namespace app\backend\controller;
use think\Session;
use think\Request;
use app\backend\controller\Base;
use think\Db;
use app\backend\model\goods;
use \Think\Upload;
class goodss extends Base
{
    //展示商品等级
    public function goods_category(){
         $obj = new goods();
         $cat_list = $obj->cate_show();
        $admin_name =  Session::get('user');
       
        $data=Db::table('cat')->where('is_del','0')->select();
        $datas = $this->digui($data);
        //sprint_r($datas);die;
        $this->assign("admin_name",$admin_name);
        $this->assign(['list'=>$datas]);
         $this->assign([
            
             'cat_list'=>$cat_list,
            ]);
        return $this->fetch('goods_category');
    }
    //商品分类删除
     public function cat_del(){
         $cat_id = input('post.cat_id');
         
         $data=Db::table('cat')->where('parent_id',$cat_id)->where('is_del','0')->select();
        // var_dump($data);
         if($data){
          echo 101;die;
         }else{
          $res=Db::table('cat')->where('cat_id',$cat_id)->update(['is_del' =>1]);
          if($res){
          echo 666;die;
          }else{
          echo 102;die;
          }
         }
     }
    
    //商品展示
    public function goods_list(){
        $cats=Db::table('cat')->where('is_del','0')->select();
        $datas = $this->digui($cats);
        $admin_name =  Session::get('user');
      
        $data =  Db::table("goods")
            ->alias('g')
            ->join("brand b","g.brand_id=b.brand_id")
            ->join("cat c","g.cat_id=c.cat_id")
            ->where("g.is_del","eq",0)
            ->order('goods_id desc')
            ->paginate(3);
        $page=$data->render();
        $this->assign(['cats'=>$datas]);
        $this->assign("admin_name",$admin_name);
        $this->assign(['list'=>$data,"page"=>$page ]);
        return $this->fetch('goods_list');
    }

    //商品删除
    public function list_del()
    {
        $get = input('get.goodId');
        $bool=Db::table("goods")->where("goods_id","eq",$get)->update(['is_del' =>1]);
        if($bool)
        {
            $admin_name =  Session::get('user');
            if($admin_name=="admin"){
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"超级管理员删除商品:删除成功",
                ]);
            }else{
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"管理员删除商品:删除成功",
                ]);
            }
            $this->success("删除成功",url("goodss/goods_list"),2);
        }
        else{
            $this->error("删除失败",url("goodss/goods_list"),2);
        }
    }
    //商品批删
    public function goods_del()
    {
        $str="";
        $post=input("post.goodsId");
        $goodsId=explode(",",$post);
        Db::startTrans();
        try{
            foreach ($goodsId as $k)
            {
                Db::table("goods")->where("goods_id","eq",$k)->update(['is_del' =>1]);
            }
            // 提交事务
            Db::commit();
            echo json_encode(["content"=>"更改成功","error"=>1]);
        } catch (\Exception $e) {

            // 回滚事务
            Db::rollback();
            echo json_encode(["content"=>$e,"error"=>0]);
        }
    }
    //这是商品批量修改
    public  function  goods_up()
    {
        $str="";
        $post=input("post.goodsId");
        $catId=input("post.cat_type");
        $goodsId=explode(",",$post);
        Db::startTrans();
        try{
            foreach ($goodsId as $k)
            {
                Db::table("goods")->where("goods_id","eq",$k)->update(['cat_id' =>$catId]);
            }
            $admin_name =  Session::get('user');
            if($admin_name=="admin"){
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"超级管理员新增分类:增加成功",
                ]);
            }else{
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"管理员新增分类:增加成功",
                ]);
            }
            Db::commit();//事物的提交

            echo json_encode(["content"=>"删除成功","error"=>1]);
        }catch(\Exception $e){

            Db::rollback();//事物的回滚
            echo json_encode(["content"=>$e,"error"=>0]);
        }
    }

    //商品回收站
      public function goods_recycle_bin(){
        
       
         $admin_name =  Session::get('user');
         $data=Db::table('goods')->alias('g')->join('cat c','g.cat_id = c.cat_id')->where('g.is_del',1)->select();
         $this->assign("admin_name",$admin_name);
         $this->assign(['list'=>$data]);
         return $this->fetch('goods_recycle_bin');
    }


    //商品修改
    public function goods_update(){
      $data = input('post.');
      $goods_id = $data['goods_id'];
      unset($data['goods_id']);
      foreach ($data as $key => $value) {}
      $goods_data=Db::table('goods')->field(''.$key.'')->where('goods_id',$goods_id)->find();
      if($goods_data[$key]==$value){echo 101;die;}
      $res=Db::table('goods')->where('goods_id',$goods_id)->update([$key =>$value]);
      $ress=$res?666:102;
      echo $ress;die;

    }
    //商品筛选
    public function search(){
       $post = input('post.');
      
       if(!empty($post['cat_id'])){
         $map['c.cat_id']  = ['=',$post['cat_id']];
       }
       $map['g.goods_name']  = ['like','%'.$post['goods_name'].'%'];
      
       $data=Db::table('goods')->alias('g')->join('cat c','g.cat_id = c.cat_id')->where($map)->select();
       foreach ($data as $key => $value) {
           $data[$key]['is_new']  = $data[$key]['is_new']==1?'是':'否';
           $data[$key]['is_hot']  = $data[$key]['is_hot']==1?'是':'否';
           $data[$key]['is_put']  = $data[$key]['is_put']==1?'是':'否';
          
       }
      // print_r($data);die;
       if($data){
         $datas['msg']=1;
         $datas['list']=$data;
        exit(json_encode($datas));die;
       }else{
         $datas['msg']=0;
         exit(json_encode($datas,JSON_UNESCAPED_UNICODE));die;

       }
     }

 

 
    public function goods_add_category()
    {
        $admin_name =  Session::get('user');
        $data=Db::table('cat')->where('is_del','0')->select();
        $datas = $this->digui($data);
        $this->assign(['list'=>$datas]);
        $this->assign("admin_name",$admin_name);
        return $this->fetch('goods_add_category');

    }


    /*商品sku展示*/
    public function skushow(){
        $goods_id = input('get.goods_id');
        //print_r($_SERVER['PATH_INFO']);die;
        $hah=preg_replace('/[^\d]/i','',$_SERVER['PATH_INFO']);
        //echo $hah;
        $goods_id = empty($goods_id)?$hah:$goods_id;
          $data=Db::table('sku')
          ->alias('s')->join('goods g','s.goods_id = g.goods_id')
          ->join('attr a','s.attr_id = a.attr_id')
          ->field('g.goods_name,spec_id,s.attr_id,s.stock,s.sell,s.sku_id,s.yuan_price,s.goods_price')
          ->where(['s.goods_id'=>$goods_id])
          ->select();
         foreach ($data as $key => $value) {
           $data[$key]['spec_id'] = explode(',',$data[$key]['spec_id']);
            $data[$key]['attr_id'] = explode(',',$data[$key]['attr_id']);

         }
        foreach ($data as $key => $value) {
             foreach ($data[$key]['spec_id'] as $k => $v) {
               $attrs=Db::table('attr')->where('attr_id',$data[$key]['spec_id'][$k])->find();
               $data[$key]['spec_id'][$k] = $attrs['attr_name'];
                $attrs=Db::table('attr')->where('attr_id',$data[$key]['attr_id'][$k])->find();
               $data[$key]['attr_id'][$k] = $attrs['attr_name'];
             }
             
            }
        foreach ($data as $key => $value) {
          foreach ($data[$key]['spec_id'] as $k => $v) {
               $data[$key]['attr_and_spec'][$k] = $data[$key]['attr_id'][$k].':'.$v;
             }
        }

        $admin_name =  Session::get('user');
       /* $this->assign("list",$data);*/
        $this->assign("goods_id",$goods_id);
        $this->assign("data",$data);
        $this->assign("admin_name",$admin_name);
        return $this->fetch('skushow');
    }
    /*结束*/
    public function skuadd(){
        $goods_id = input('get.goods_id');
          $hah=preg_replace('/[^\d]/i','',$_SERVER['PATH_INFO']);
        //echo $hah;
        $goods_id = empty($goods_id)?$hah:$goods_id;
        $admin_name =  Session::get('user');
        $data=Db::table('attr')->select();
        $data = $this->diguisan($data);
        $goods_name=Db::table('goods')->field('goods_name')->where('goods_id',$goods_id)->find();
        //print_r($goods_name);die;
        $this->assign(['list'=>$data]);
        $this->assign(['goods_name'=>$goods_name['goods_name']]);
        $this->assign("admin_name",$admin_name);
        $this->assign("goods_id",$goods_id);
        return $this->fetch('skuadd');
    }
    //添加sku
    public function skuadding(){
       $data = input('post.');

       foreach ($data['attr_spec_id'] as $key => $value) {
           if(empty($data['attr_spec_id'][$key])){
            unset($data['attr_spec_id'][$key]);
            unset($data['attr_id'][$key]);
           }
       }
       $data['attr_id'] = implode(',',$data['attr_id']);
       $data['spec_id'] = implode(',',$data['attr_spec_id']);
        
        $res=Db::table('sku')->insert([
          'goods_id'=>$data['goods_id'],
          'attr_id'=>$data['attr_id'],
          'spec_id'=>$data['spec_id'],
          'stock'=>$data['stock'],
            'goods_price'=>$data['goods_price'],
            'yuan_price'=>$data['yuan_price']

          ]);
     /*   echo '<pre>';
       print_r($res);die;*/
         if($res){
            //设置成功后跳转页面的地址，默认的返回页面是$_SERVER['HTTP_REFERER']
             $admin_name =  Session::get('user');
             if($admin_name=="admin"){
                 $res1 = Db::table("log")->insert([
                     "admin_name"=>$admin_name,
                     "log_time"=>date("Y-m-d H:i:s"),
                     "content"=>"超级管理员新增sku:增加成功",
                 ]);
             }else{
                 $res1 = Db::table("log")->insert([
                     "admin_name"=>$admin_name,
                     "log_time"=>date("Y-m-d H:i:s"),
                     "content"=>"管理员新增sku:增加成功",
                 ]);
             }
           
             $this->success('新增货品成功',url("goodss/skushow",['goods_id'=>$data['goods_id']]));die;
        } else {
            //错误页面的默认跳转页面是返回前一页，通常不需要设置
            $this->error('新增失败');
        }
    
    }
    //二级联动商品属性
    public function searchattr(){
      $attr_id = input('post.attr_id');
      $data=Db::table('attr')->where('parent_id',$attr_id)->select();

       if($data){
         $datas['msg']=1;
         $datas['list']=$data;
        exit(json_encode($datas));die;
       }else{
         $datas['msg']=0;
         exit(json_encode($datas,JSON_UNESCAPED_UNICODE));die;
       }

    }
    public function goods_add_categorying(){
        $post = input('post.');
        $data = ['cat_name' => $post['cat_name'],'parent_id' => $post['parent_id'],'cat_addtime'=>date('Y-m-d H:i:s')];
        if(empty($post['cat_name'])){
             $this->error('请填写分类名称');die;
        }
        $res=Db::table('cat')->insert($data);
         if($res){
            //设置成功后跳转页面的地址，默认的返回页面是$_SERVER['HTTP_REFERER']
             $admin_name =  Session::get('user');
             if($admin_name=="admin"){
                 $res1 = Db::table("log")->insert([
                     "admin_name"=>$admin_name,
                     "log_time"=>date("Y-m-d H:i:s"),
                     "content"=>"超级管理员新增分类:增加成功",
                 ]);
             }else{
                 $res1 = Db::table("log")->insert([
                     "admin_name"=>$admin_name,
                     "log_time"=>date("Y-m-d H:i:s"),
                     "content"=>"管理员新增分类:增加成功",
                 ]);
             }
            $this->success('新增分类成功', 'goodss/goods_category');
        } else {
            //错误页面的默认跳转页面是返回前一页，通常不需要设置
            $this->error('新增失败');
        }
      
    }
    //数组
       public function digui($arr,$path=0,$flag=1){

       static $data=array();
        foreach($arr as $key=>$val){
            //如果当前的父级id是上条记录的权限id
            if($val['parent_id']==$path){
                $val['f']=$flag;
                $data[]=$val;
                $this->digui($arr,$val['cat_id'],$flag+1);   
            }
        }
        return $data;
    }
    //商品添加展示
    public function goods_add(){
        $datass=Db::table('cat')->where('is_del','0')->select();
        $data=Db::table('attr')->select();
        $brand = Db::table('brand')->select();
        $datas = $this->digui($datass);
        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);
        $this->assign(['list'=>$datas,"brand"=>$brand]);
        return $this->fetch('goods_add');
      }
    //商品添加
     public function goods_adding(){
         $post = input('post.');
         $brand_id= Request::instance()->post("brand_id");
//         $res1 =  Db::table("goods")
//             ->alias('a')
//             ->join("brand b","a.brand_id=b.brand_id")
//             ->where(['a.brand_id'=>$brand_id])
//             ->select();
       //   print_r($res1);die;
         Db::table("goods")->insert(['brand_id'=>$brand_id]);
         $file = $_FILES['goods_img'];
         $goods_img=$this->movefiles($file);
         ///echo count($post);die;
         //print_r($post);die;
         $data = ['goods_name' => $post['goods_name'],
                  'cat_id' => $post['cat_id'],
                  'goods_price' => $post['goods_price'],
                  'add_time'=>date('Y-m-d H:i:s'),
                  'goods_info' => $post['goods_info'],
                  'is_new' => $post['is_new'],
                  'is_hot' => $post['is_hot'],
                  'is_put' => $post['is_put'],
                  'goods_img' => $goods_img,
                  'brand_id'=>$brand_id];
        $res=Db::table('goods')->insert($data);

         $userId = Db::name('goods')->getLastInsID();

         if($res){
            //设置成功后跳转页面的地址，默认的返回页面是$_SERVER['HTTP_REFERER']
             $admin_name =  Session::get('user');
             if($admin_name=="admin"){
                 $res1 = Db::table("log")->insert([
                     "admin_name"=>$admin_name,
                     "log_time"=>date("Y-m-d H:i:s"),
                     "content"=>"超级管理员新增商品:增加成功",
                 ]);
             }else{
                 $res1 = Db::table("log")->insert([
                     "admin_name"=>$admin_name,
                     "log_time"=>date("Y-m-d H:i:s"),
                     "content"=>"管理员新增商品:增加成功",
                 ]);
             }

            $this->success('新增商品成功', url('goodss/skuadd',['goods_id'=>$userId]));

        } else {
            //错误页面的默认跳转页面是返回前一页，通常不需要设置
            $this->error('新增失败');
        }
    }
    //单文件上传
    public function movefiles($file){
            move_uploaded_file($file["tmp_name"],"uploads/" .$file["name"]);
            return "uploads/" .$file["name"];
    }

     //相册展示
    public function goods_photo($keywords='')
    {
        $name=$this->se_user();
        if (!$data= Db::name('album')->where('album_name','like',"%{$keywords}%")->order('album_id desc')->paginate(2)) {
            return $this->fetch('goods_photo',['res'=>'','admin_name'=>$name,'page'=>'','keywords'=>$keywords]);
        }
        $page = $data->render(); 

        return $this->fetch('goods_photo',['res'=>$data,'admin_name'=>$name,'page'=>$page,'keywords'=>$keywords]);
    }
    //添加相册
    public function add_img()
    {
        $res= Db::table('goods')->select();
        $admin_name =  Session::get('user');
        $arr=[];
        foreach ($res as $key => $value) {
            $arr[]=$value;
        }
        $this->assign('list',$arr);
        $this->assign("admin_name", $admin_name);
        return $this->fetch('add_img');
    }

    public function img_add_do()
    {
        $arr['album_name'] = $_POST['album_name'];
        $arr['goods_id']=$_POST['goods_name'];
        $file = $_FILES['photo'];
        if ($file['name']=="") { 
          $this->error('请选择上传文件'); 
        } 
        $suffix= substr($file['name'],-4);
        $tmp_name=$file['tmp_name'];
        $newname= md5(rand(0,999)).$suffix;
        $newpath= "./uploads/".$newname;
        move_uploaded_file($tmp_name,$newpath);
        $arr['album_img'] = $newpath;
        // var_dump($arr['album_img']);die;
        $res =Db::table('album')->insert($arr);
        $admin_name =  Session::get('user');

        if($res){
            if($admin_name=="admin"){
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"超级管理员添加相册:添加成功",
                ]);
            }else{
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"管理员添加相册:添加成功",
                ]);
            }
            return $this->success('添加相册成功',url('goodss/goods_photo'));
        }else{
            return $this->success('添加相册失败',url('goodss/img_add'));
        }
    }
    //相册列表
     public function tupian(){
        $album_id = $_GET['album_id'];
        $res=Db::table('goods_photo')
        // ->alias('p')
        // ->join('album a','p.album_id=a.album_id')
        ->field('photo')
        ->where(['album_id'=>$album_id])
        ->select();
        $data=[];
        foreach ($res as $key => $value) {
          foreach ($value as $k => $v) {
              $data[]=$v;
          }
        }
        $str ='';
        foreach ($data as $key => $value) {
            $str .=','. $value;
        }
        $rest=explode(',',$str);
        unset($rest[0]);
        $admin_name =  Session::get('user');
        $this->assign("admin_name", $admin_name);
        $this->assign('list',$rest);
        $this->assign('id',$album_id);
        return $this->fetch('tupian');  
     }

    public function se_user($value='')
    {
        $admin_name =  Session::get('user');
        return $admin_name;
    }
     //相册里添加图片
    public function goods_add_photos(){
        $photo=$_FILES['photo'];
        $arr['album_id']=$_POST['album_id'];
        $tmp = $photo['tmp_name'];
        $filename = $photo['name'];
        if($filename[0]==''){
          $this->error('请选择上传文件');
        }
        $str='';
        foreach($filename as $k=>$v){
            $newpath=substr($v,-4);
            $newname=rand(1,9999).$newpath;
            $newfile="./uploads/".$newname;
            $str.=",".$newfile;
            move_uploaded_file($tmp[$k],$newfile);        
         }
        $arr['photo']= ltrim($str,',');
        $res =   Db::table('goods_photo')->insert($arr);
        $admin_name =  Session::get('user');
        if($res){
            if($admin_name=="admin"){
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"超级管理员上传图片:上传成功",
                ]);
            }else{
                $res1 = Db::table("log")->insert([
                    "admin_name"=>$admin_name,
                    "log_time"=>date("Y-m-d H:i:s"),
                    "content"=>"管理员上传图片:上传成功",
                ]);
            }
            return $this->success('图片上传成功');
        }else{
            return $this->success('图片上传失败');
        }
    }


    //商品规格列表
    public function goods_spec_list(){
        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);
        return $this->fetch('goods_spec_list');
    }
    //添加规格
    public function add_spec(){
        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);
        return $this->fetch('add_spec');

    }

    public function goods_attr_list(){
        $admin_name =  Session::get('user');
        $data=Db::table('attr')->select();
        $data = $this->diguisan($data);
        //print_r($data);die;
        $this->assign(['list'=>$data]);
        $this->assign("admin_name",$admin_name);
        return $this->fetch('goods_attr_list');
    }
      public function diguisan($arr,$path=0){
       $data=array();
        foreach($arr as $key=>$val){
            //如果当前的父级id是上条记录的权限id
            if($val['parent_id']==$path){
               
                $data[$key]=$val;
                $data[$key]['son']=$this->diguisan($arr,$val['attr_id']);
                
            }
        }
        return $data;
    }
    //添加商品属性界面
    public function add_attr(){
        $admin_name =  Session::get('user');
        $data=Db::table('attr')->select();
        $datas=$this->diguitwo($data);
        $this->assign(['list'=>$datas]);
        $this->assign("admin_name",$admin_name);
        return $this->fetch('add_attr');

    }
    //递归er 只要顶级夫级
      public function diguitwo($arr,$path=0){
       static $data=array();
        foreach($arr as $key=>$val){
            //如果当前的父级id是上条记录的权限id
            if($val['parent_id']==$path){
               
                $data[]=$val;
                
                
            }
        }
        return $data;
    }
    public function add_attring(){
      $post = input('post.');
      if($post['attr_name']==null){
        $this->error('商品名称不能为空');
      }
      //print_r($post);die;
        $data = ['attr_name' => $post['attr_name'],
                 'parent_id' => $post['parent_id']
               ];
        $res=Db::table('attr')->insert($data);
         if($res){
            //设置成功后跳转页面的地址，默认的返回页面是$_SERVER['HTTP_REFERER']
             $admin_name =  Session::get('user');
             if($admin_name=="admin"){
                 $res1 = Db::table("log")->insert([
                     "admin_name"=>$admin_name,
                     "log_time"=>date("Y-m-d H:i:s"),
                     "content"=>"超级管理员新增商品属性:增加成功",
                 ]);
             }else{
                 $res1 = Db::table("log")->insert([
                     "admin_name"=>$admin_name,
                     "log_time"=>date("Y-m-d H:i:s"),
                     "content"=>"管理员新增商品属性:增加成功",
                 ]);
             }
            $this->success('新增商品属性成功', 'goodss/goods_attr_list');
        } else {
            //错误页面的默认跳转页面是返回前一页，通常不需要设置
            $this->error('商品属性失败');
        }
    }
}

