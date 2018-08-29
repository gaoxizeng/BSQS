<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 2018/8/9
 * Time: 15:19
 */

namespace app\backend\model;
use think\Db;
class Goods extends \think\Model{
    public function cate_show(){
     $sql=" select *,CONCAT(path,'-',cat_id) as newpath from cat ORDER BY newpath ";
     return Db::query($sql);
    }
}