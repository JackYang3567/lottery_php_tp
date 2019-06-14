<?php
namespace app\home\model;
use think\Model;

class ApiConfig extends Model
{
  protected $table = 'api_config';
  public static function list(){
    $rs = self::field('id as list,name,content,scode')->where('switch','=',1)->order('sort','DESC')->select();
    if(empty($rs)){
      return '';
    }
    return $rs->append(['game']);
  }
  public function getGameAttr($value,$data){
    return [];
  }
}
