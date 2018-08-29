<?php
namespace app\backend\controller;
use think\Session;
use app\backend\controller\Base;
use think\Db;
use think\request;

use think\Paginator;
class Order extends Base
{
    public function order_list($order_no="")
    {
        $res = Db::table("order_main")->where('order_no','like',"%{$order_no}%")->order('order_time desc')->select();
        $arr = [];
        foreach ($res as $k => $v) {
            $arr[] = $v['user_id'];
        }
        foreach ($arr as $k => $v) {
            $user = Db::table("user")->field('user_name')->where(['user_id' => $v])->find();
            $res[$k]['user_name'] = $user['user_name'];
        }
        //收获地址
        $arr1 = [];
        foreach ($res as $k => $v) {
            $arr1[] = $v['address_id'];
        }
        foreach ($arr1 as $k => $v) {
            $user = Db::table("address")->field('address_region')->where(['address_id' => $v])->find();
            $res[$k]['address_region'] = $user['address_region'];
        }
        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);

        $this->assign(['res' => $res]);
        return $this->fetch('order_list');
    }
    //删除订单
    public function del(){
        $order_no = request::instance()->get('order_no');
        $result = Db::table("order_main")->where(['order_no'=>$order_no])->delete();
        if($result){
            echo  1;
        }
    }
    //修改订单
    public function order_upd(){
        $order_no =request::instance()->get('order_no');
        $data =  Db::table("order_main")->where(['order_no'=>$order_no])->find();
       //print_r($data);die;
        $order_id = $data['order_id'];
        $yundan_no = $data['yundan_no'];
//        $data['wuliu']= "圆通";

       // echo $new_no;die;
       if($data['wuliu']="圆通快递"){
           $new_no="sy".date('YmdHis').intval(rand(10000,90000));

       }else if($data['wuliu']="申通快递"){
           $new_no="st".date('YmdHis').intval(rand(10000,90000));

       }else if($data['wuliu']="中通快递"){
           $new_no="zt".date('YmdHis').intval(rand(10000,90000));

       }else if($data['wuliu']="韵达快递"){
           $new_no="yd".date('YmdHis').intval(rand(10000,90000));
       }else{
           $new_no="sf".date('YmdHis').intval(rand(10000,90000));

       }
        $new_no = empty($data['yundan_no'])?$new_no:$data['yundan_no'];
       $address_id = $data['address_id'];
        $user_id =$data['user_id'];
        $user = Db::table("user")->field('user_name')->where(['user_id' => $user_id])->find();
        $address = Db::table("address")->field('address_region')->where(['address_id' => $address_id])->find();
        //echo $new_no;die;
        $this->assign([
            'new_no'=>$new_no,
            "data"=>$data,
            "user"=>$user,
            "address"=>$address
            ]);
        $admin_name =  Session::get('user');
        $this->assign("admin_name",$admin_name);
        return $this->fetch("order_upd");
   }

    public function order_upd_do(){
      $data = input('post.');
      if($data['status']==3){
          $save['yundan_no'] =$data['yundan'];
          $add['content']="已从仓库发货";
      }else{
          $add['content']="客户已签收";
      }

        $add['order_addtime']=date('Y-m-d H:i:s');
        $add['order_id']=$data['order_id'];
      Db::table('yungen')->insert($add);
      $save['order_status']=$data['status'];
      $ress=Db::table('order_main')->where(['order_id'=>$data['order_id']])->update($save);

      if($ress){
         $res['msg']=1;
         if($data['status']==3){
             $res['dd']='待收货';
         }else{
             $res['dd']='已收货';
         }
      }else{
          $res['msg']=0;
      }
        exit(json_encode($res,true));
}

}
