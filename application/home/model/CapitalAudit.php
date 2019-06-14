<?php
namespace app\home\model;
use think\Model;

class CapitalAudit extends Model
{
    public  function  getUserNameAttr($value,$data){ //获取用户名称
        return User::where('id',$data['user_id'])->find()['username'];
    }

    public  function  getNomoneyAttr($value,$data){ //获取用户名称
        return User::where('id',$data['user_id'])->find()['money'];
    }
}
