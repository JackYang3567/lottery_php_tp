<?php
namespace app\home\controller;
use think\Db;

class LotteryS extends Lottery
{
  public function rule(){
    return [
      'h2zhuxuan' => function($code){
        $len = count($code[0]);
        return ($len >= 2 && $len <= 10) ? ($len * ($len - 1) / 2) : 0;
      },
      'q3zl' => function($code){
        $len = count($code[0]);
        return $this->combinationBasic($len,3);
      },
      'q3zs' => function($code){
        $len = count($code[0]);
        return $this->combinationBasic($len,2) * 2;
      },
      'q2zhixuan' => function($code){
    		$zhushu = 1;
    		for($i = 0;$i < 2; $i++){
    			$zhushu *= count($code[$i]);
    		}
        return $zhushu;
      },
      'q3zx' => function($code){
        $zhushu = 1;
        for($i = 0;$i < 3; $i++){
    			$zhushu *= count($code[$i]);
    		}
        return $zhushu;
      },
      'qw' => function($code){
        return count($code[0]);
      }
    ];
  }

  public function verification($key1,$key2,$code){
    switch ($key2) {
      case 'q2zhuxuan':
        $key2 = 'h2zhuxuan';
        break;
      case 'z3zl':
      case 'h3zl':
        $key2 = 'q3zl';
        break;
      case 'z3zs':
      case 'h3zs':
        $key2 = 'q3zs';
        break;
      case 'h2zhixuan':
        $key2 = 'q2zhixuan';
        break;
      case 'z3zx':
      case 'h3zx':
        $key2 = 'q3zx';
        break;
    };
    switch ($key1) {
      case 'qw':
        $key2 = 'qw';
        break;
    }
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
    if(in_array($key1,['lm','qw'])){
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

    return $this->bettingAction(['lm','qw']);
  }

  // 如果彩种没有规律，在这里处理倒计时和期数
  // return ['expect' => '','time' => '']
  public function nowData(){
  }
}
