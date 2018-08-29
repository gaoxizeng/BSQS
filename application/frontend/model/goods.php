<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/8/14
 * Time: 16:15
 * content:使用redis实现商品缓存
 */
namespace app\frontend\model;
use think\cache\driver\Redis;
use think\Model;
use think\Db;
class Goods extends Model{
    public $redis;
    public function __construct()
    {
        $this->redis=new \Redis();
        $this->redis->connect("127.0.0.1",6379);        
    }
    //从缓存中获取商品信息
    public function getGoods($catId)
    {
        $redis=$this->redis;
        return $redis->hGetAll($catId."cat");
        $boll=$this->redis->flushall();//清除所有的缓存
    }
    //想缓存添加添加商品信息
    public function addRedis($carId)
    {
        $where['is_del'] = 0;
        if(!empty($carId)) $where['parent_id'] = $carId;
        $arr=Db::table("cat")->where($where)->field('cat_id,cat_name,parent_id')->select();
        $cat_json=json_encode($arr);//这是分类的子级改成json的形式
        $redis=$this->redis;
        $car_id=array();
        foreach($arr as $k=>$v)
        {
            $car_id[]=$v["cat_id"];
        }
        $goods=Db::table("goods")->where('cat_id','in',$car_id)->select();//这是获取所在分类下的所有的商品
        $goods_json=json_encode($goods);
//        echo "<pre>";
        $brand=Db::table("brand")->where(["is_status"=>1])->select();
        $brand_json=json_encode($brand);//这是品牌的数据
//        var_dump($brand_json);die;
        //这是用redis的hash的类型存储数据
        $bool=$redis->hMset($carId."cat",[$carId."cat_json"=>$cat_json,$carId."goods_json"=>$goods_json,$carId."brand"=>$brand_json]);//这是将子级的分类春出到redis
        $redis->expireAt($carId."cat",time()+60*60*12);//这是定义有效时间
        return $array=$redis->hGetAll($carId."cat");//返回从缓存里获取到的数据
//        exit;

    }
    //这是过商品的搜索时对商品你的添加到缓存里
    public function selectGoods($where,$end){
        $goods=Db::table("goods")->where($where)->select();//这是符合条件的商品
        $cat=Db::table("cat")->where("parent_id","<>",0)->select();//这是所有的商品分类
        $brand=Db::table("brand")->select();//这是品牌
        $goods_json=json_encode($goods);
        $cat_json=json_encode($cat);
        $brand_json=json_encode($brand);
        $redis=$this->redis;
        $bool=$redis->hMset($end."cat",[$end."cat_json"=>$cat_json,$end."goods_json"=>$goods_json,$end."brand"=>$brand_json]);//这是将子级的分类春出到redis
        $redis->expireAt($end."cat",time()+60*60*12);//这是定义有效时间
//        echo "<pre>";
//        var_dump($goods_json);
        return $array=$redis->hGetAll($end."cat");//返回从缓存里获取到的数据
    }

    public function __destruct()
    {
        if($this->redis)
        {
            $this->redis->close();
        }
    }
}