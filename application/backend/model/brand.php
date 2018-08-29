<?php
namespace app\backend\model;
use think\Model;
class Brand extends Model{
       protected $table = 'brand';

       public function getrow($value='')
       {
              echo "string";
       }
}

