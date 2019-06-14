<?php
namespace app\home\model;
use think\Model;

class LoginLog extends Model
{
  static public function getNewByUserId($user_id)
  {
    return self::where('user_id', $user_id)->field('create_time')->order('create_time', 'DESC')->find()['create_time'];
  }
}
