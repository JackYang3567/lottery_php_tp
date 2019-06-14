<?php
namespace app\home\model;
use think\Model;

class User extends Model
{
  public function getLogAttr($value, $data)
  {
    return LoginLog::getNewByUserId($data['id']);
  }

  public function getNameAttr($value, $data)
  {
    $strl = mb_strlen($data['username']);
    return ( $data['type'] == 1 ? '试玩用户' :(mb_substr($data['username'],0,floor($strl/2)).'**') );
  }

  public static function AllUserIdGet(){
    return self::where('type','<>',1)
                ->where('status','=',0)
                ->where('group','=',0) //如果不是巴登ab分组时，则取消注释
                ->column('id');  
  }
}
