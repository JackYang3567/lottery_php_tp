<?php
namespace app\home\model;
use think\Model;

class CapitalDetail extends Model
{
  // /**
  //  * 获取指定彩种 指定时间段 指定用户 的下注与赢钱情况
  //  * @param array $lottery  需要查询的彩种数组
  //  * @param array $time 0是开始时间  1是结束时间
  //  * @param number $uid 用户id
  //  * @return array 0是投注 1是中奖 2是撤单
  //  */
  // public static function negative($lottery,$time,$uid){
  //   //查询0下注 3中奖 6退款 
  //   $rs = self::query('select sum(money) many,type from capital_detail where (`type`=0 or `type`=3 or `type`=6) and `user_id`='.$uid.' and `create_time`>='.$time[0].' and `create_time`<='.$time[1].' group by `type`');
  //   print_r($rs);die();
  //   $bit = [0,0,0];
  //   if(!empty($rs)){
  //     foreach ($rs as $v) {
  //       if($v['type'] == 0) {
  //         $redata[0] += abs($v['many']);
  //       } elseif ($v['type'] == 3) {
  //         $redata[1] += abs($v['many']);
  //       } else {
  //         $redata[2] += abs($v['many']);
  //       }
  //     }
  //   }
  //   return $bit;
  //   // foreach ($variable as $key => $value) {
  //   //   // code...
  //   // }
  // }
}
