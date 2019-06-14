<?php
namespace app\home\controller;
use think\Db;

class LotteryP3d extends Lottery
{
  public function rule(){
    return [
      'zx6' => function($code){
        $len = count($code[0]);
        return $this->combinationBasic($len,3);
      },
      'zx3' => function($code){
        $len = count($code[0]);
        return $this->combinationBasic($len,2) * 2;
      },
      'zhix3' => function($code){
        $zhushu = 1;
        for($i = 0;$i < 3; $i++){
    			$zhushu *= count($code[$i]);
    		}
        return $zhushu;
      }
    ];
  }

  public function verification($key1,$key2,$code){
    return $this->rule()[$key2]($code);
  }

  /**
   * 输入注单的所有验证,如果有验证写入此处！！验证通过为true
   * @param string $key1  投注key1值
   * @param string $key2  
   * @param string $code  投注组合的值
   * @param array $config   对应彩种数据结构
   * @return blooean 是否验证通过
   */
  public function checkAll($key1,$key2,$code,$config){
    if($key1 == 'lm'){
      foreach($code as $vo1){
        foreach($vo1 as $vo2){
          if( !is_int((int)$vo2) || $vo2 > 9  || $vo2 < 0 ){
            return false;
          }
        }
      }
    }

    return true;
  }

  public function betting(){
    // 这里传入需要验证的玩法
    return $this->bettingAction(['lm']);
  }

  // 如果彩种没有规律，在这里处理倒计时和期数
  // return ['expect' => '','time' => '']
  public function nowData(){
  }
}
