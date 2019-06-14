<?php
namespace app\home\model;
use think\Model;

class ApiGame extends Model
{
    public static function game($api){
        return self::field('id as code,name,api_id as list')->where('api_id','in',$api)->where('switch','=',1)->select();
    }
}
