<?php
namespace app\djycpgk\model;

use think\Model;

class ApiBetting extends Model {
    public function getOperatorAttr($value,$data){ //运营商
        if (!$rs = ApiConfig::field('name')->where('id',$data['api_id'])->find()) {
            return '未知营商';
        }
        return $rs['name'];
    }
    public function getGameTypeAttr($value,$data){ //游戏类型
        if (!$rs = ApiGame::field('name')->where('king_id',$data['kind_id'])->find()) {
            return '未知游戏';
        }
        return $rs['name'];
    }

}