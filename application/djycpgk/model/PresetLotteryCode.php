<?php
namespace app\djycpgk\model;

use app\home\model\LotteryConfig;
use think\Model;
use think\Db;

class PresetLotteryCode extends Model {
    public function  getCaiZhongAttr($value,$data){ //累积中奖

       if (!$ss = LotteryConfig::where('type',$data['type'])->find()['name']){
           return '未知彩种';
       }
       return $ss;
    }
    public function  getZhaungTaiAttr($value,$data){ //累积中奖
//        lottery_code
        if (!$ss = Db::table('lottery_code')->where('type',$data['type'])->where('expect',$data['expect'])->find()){
            return 0;
        }
        return 1;
    }
}