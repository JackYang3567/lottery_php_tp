<?php
namespace app\home\controller;
use think\Db;

class LotteryBrnn extends Lottery
{
  public function rule(){
    // return [
    //   'zx6' => function($code){
    //     $len = count($code[0]);
    //     return $this->combinationBasic($len,3);
    //   },
    //   'zx3' => function($code){
    //     $len = count($code[0]);
    //     return $this->combinationBasic($len,2) * 2;
    //   },
    //   'zhix3' => function($code){
    //     $zhushu = 1;
    //     for($i = 0;$i < 3; $i++){
    // 			$zhushu *= count($code[$i]);
    // 		}
    //     return $zhushu;
    //   }
    // ];
  }

  /**
   * 此方法单独验证注数是否正确
   */
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
    return true;
  }

  /**
   * 投注入口
   */
  public function betting(){
    // 这里传入需要验证的玩法，传入key1并且是数组形式
    return $this->bettingAction();
  }
}
