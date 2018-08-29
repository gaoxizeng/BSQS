<?php
namespace app\backend\model;
use think\Db;
class Log extends \think\Model{
 public function select_log(){
     $res = Db::table("log")->order('log_id desc')->select();
     return $res;
 }
 public function page($count,$page,$total_page){
     $url = $_SERVER['PHP_SELF'];
     $index = $page==1 ?"首页":"<a href='{$url}?page=1'>首页</a>";
     $last = $page == $total_page ? "尾页" : "<a href='{$url}?page={$total_page}'>尾页</a>";
     $prev_page = $page-1<1?1:$page-1;
     $next_page = $page+1>$total_page?$total_page:$page+1;
     $prev = ($page == 1) ? "上一页" : "<a href='{$url}?page={$prev_page}'>上一页</a>";
     $next = ($page == $total_page) ? "下一页" : "<a href='{$url}?page={$next_page}'>下一页</a>";
     $str = "当前共{$count}个记录,共{$total_page}页,当前是第{$page}页";
     return $str.'&nbsp;&nbsp;'.$index.'&nbsp;'.$prev.'&nbsp;'.$next.'&nbsp;'.$last;
 }

 public function pages($page,$key,$num)
 {
        $sql = "select * from log where admin_name like '%{$key}%'";
        $res = Db::query($sql);

        return ceil(count($res)/$num);
 }
}