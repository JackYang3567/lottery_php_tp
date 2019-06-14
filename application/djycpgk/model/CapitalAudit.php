<?php
namespace app\djycpgk\model;

use think\Model;

class CapitalAudit extends Model {

    public static function getCapitalList($type){
        $ks = strtotime(date("Y-m-d"),time());
        $js =strtotime(date("Y-m-d"),time())+60*60*24;

        return self::where('create_time', 'between', [$ks, $js])->where('state', 1)->where('type', $type)->count();
    }
    public  function  getUserNameAttr($value,$data){ //获取用户名称
        return User::where('id',$data['user_id'])->find()['username'];
    }

    public  function  getNomoneyAttr($value,$data){ //获取用户名称
        return User::where('id',$data['user_id'])->find()['money'];
    }
}