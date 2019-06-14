<?php
namespace app\djycpgk\model;

use think\Model;

class Betting extends Model {
    public function getBettingCountAttr($value,$data)//获取投注 注数；
    {
        return count(json_decode($data['content'],true));
    }

}