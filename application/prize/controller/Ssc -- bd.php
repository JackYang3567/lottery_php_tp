<?php
namespace app\prize\controller;

class Ssc extends Lottery
{
  public $data_chat;
  public $prize_num;

  public function prize(){
    $this->actionPrize();
  }

  public function action($key1,$key2,$value,$money,$other,$odds){
    $key_chat = $key1;
    $this->prize_num = 1;
    $return_data = [
      'code' => 0,
      'num' => 0
    ];
    switch ($key1) {
      case 'qs':
        $this->data_chat = [ $this->prize_code[0],$this->prize_code[1],$this->prize_code[2] ];
        $key_chat = 'qs_zs_hs';
        break;
      case 'zs':
        $this->data_chat = [ $this->prize_code[1],$this->prize_code[2],$this->prize_code[3] ];
        $key_chat = 'qs_zs_hs';
        break;
      case 'hs':
        $this->data_chat = [ $this->prize_code[2],$this->prize_code[3],$this->prize_code[4] ];
        $key_chat = 'qs_zs_hs';
        break;
      case 'zh':
        $this->data_chat = array_sum($this->prize_code);
        break;
      case 'dyq':
        $this->data_chat = $this->prize_code[0];
        $key_chat = 'wei';
        break;
      case 'deq':
        $this->data_chat = $this->prize_code[1];
        $key_chat = 'wei';
        break;
      case 'dsanq':
        $this->data_chat = $this->prize_code[2];
        $key_chat = 'wei';
        break;
      case 'dsiq':
        $this->data_chat = $this->prize_code[3];
        $key_chat = 'wei';
        break;
      case 'dwq':
        $this->data_chat = $this->prize_code[4];
        $key_chat = 'wei';
        break;
    }
    if($this->rule()[$key_chat][$key2]($value)){
      $return_data['code'] = 1;
      $return_data['num'] = $this->prize_num * ($money * $odds);
    }
    return $return_data;
  }

  public function rule(){
    return [
      'zh' => [
        // 大：根据相应单项投注的第一球特 ~ 第五球特开出的球号大於或等於23为特码大
        'da' => function($value){
          if($this->data_chat > 22){
            return true;
          }else{
            return false;
          }
        },
        'x' => function($value){
          // 小：根据相应单项投注的第一球特 ~ 第五球特开出的球号小於或等於22为特码小。
          if($this->data_chat < 23){
            return true;
          }else{
            return false;
          }
        },
        'dan' => function($value){
          if($this->data_chat % 2){
            return true;
          }else{
            return false;
          }
        },
        's' => function($value){
          if($this->data_chat % 2){
            return false;
          }else{
            return true;
          }
        },
        'l' => function($value){
          // 龙：开出之号码第一球（万位）的中奖号码大于第五球（个位）的中奖号码，如出和局为打和。如 第一球开出4 第五球开出2；第一球开出9 第五球开出8；第一球开出5 第五球开出1...中奖为龙。
          if($this->prize_code[0] > $this->prize_code[4]){
            return true;
          }else{
            return false;
          }
        },
        'hu' => function($value){
          // 虎：开出之号码第一球（万位）的中奖号码小于第五球（个位）的中奖号码，如出和局为打和。如 第一球开出7 第五球开出9；第一球开出5 第五球开出8...中奖为虎。
          if($this->prize_code[0] < $this->prize_code[4]){
            return true;
          }else{
            return false;
          }
        },
        'he' => function($value){
            // 和：开出之号码第一球（万位）的中奖号码等于第五球（个位）的中奖号码，则中奖
          if($this->prize_code[0] == $this->prize_code[4]){
            return true;
          }else{
            return false;
          }
        },
      ],
      'wei' => [
        'da' => function($value){
          if($this->data_chat > 4){
            return true;
          }else{
            return false;
          }
        },
        'x' => function($value){
          if($this->data_chat < 5){
            return true;
          }else{
            return false;
          }
        },
        'dan' => function($value){
          if($this->data_chat % 2){
            return true;
          }else{
            return false;
          }
        },
        's' => function($value){
          if($this->data_chat % 2){
            return false;
          }else{
            return true;
          }
        },
        'code_0' => function($value){
          if($this->data_chat == 0){
            return true;
          }else{
            return false;
          }
        },
        'code_1' => function($value){
          if($this->data_chat == 1){
            return true;
          }else{
            return false;
          }
        },
        'code_2' => function($value){
          if($this->data_chat == 2){
            return true;
          }else{
            return false;
          }
        },
        'code_3' => function($value){
          if($this->data_chat == 3){
            return true;
          }else{
            return false;
          }
        },
        'code_4' => function($value){
          if($this->data_chat == 4){
            return true;
          }else{
            return false;
          }
        },
        'code_5' => function($value){
          if($this->data_chat == 5){
            return true;
          }else{
            return false;
          }
        },
        'code_6' => function($value){
          if($this->data_chat == 6){
            return true;
          }else{
            return false;
          }
        },
        'code_7' => function($value){
          if($this->data_chat == 7){
            return true;
          }else{
            return false;
          }
        },
        'code_8' => function($value){
          if($this->data_chat == 8){
            return true;
          }else{
            return false;
          }
        },
        'code_9' => function($value){
          if($this->data_chat == 9){
            return true;
          }else{
            return false;
          }
        },

      ],
      'qs_zs_hs' => [

        'bz' => function($value){
          // 豹子：中奖号码的百位千位万位数字都相同。----如中奖号码为000、111、999等，中奖号码的个位十位百位数字相同，则投注豹子者视为中奖，其它视为不中奖。
          if($this->data_chat[0] == $this->data_chat[1] && $this->data_chat[1] == $this->data_chat[2] && $this->data_chat[2] == $this->data_chat[0]){
            return true;
          }else{
            return false;
          }
        },
        'sz' => function($value){
          // 顺子：中奖号码的百位千位万位数字都相连，不分顺序。（数字9、0、1相连）----如中奖号码为123、901、321、546等，中奖号码个位十位百位数字相连，则投注顺子者视为中奖，其它视为不中奖。
          $data = $this->data_chat;
          sort($data);
          if((($data[0] + 1) == $data[1] && ($data[1] + 1) == $data[2]) || ($data[0] == 0 && $data[1] == 1 && $data[2] == 9)){
            return true;
          }else{
            return false;
          }
        },
        'dz' => function($value){
          // 对子：中奖号码的百位千位万位任意两位数字相同。（不包括豹子）----如中奖号码为001，112、696，中奖号码有两位数字相同，则投注对子者视为中奖，其它视为不中奖。如果开奖号码为豹子,则对子视为不中奖。如中奖号码为001，112、696，中奖号码有两位数字相同，则投注对子者视为中奖，其它视为不中奖。
          if(!($this->data_chat[0] == $this->data_chat[1] && $this->data_chat[1] == $this->data_chat[2] && $this->data_chat[2] == $this->data_chat[0]) && ($this->data_chat[0] == $this->data_chat[1] || $this->data_chat[1] == $this->data_chat[2] || $this->data_chat[2] == $this->data_chat[0])){
            return true;
          }else{
            return false;
          }
        },
        'bs' => function($value){
          // 半顺：中奖号码的百位千位万位任意两位数字相连，不分顺序。----如中奖号码为125、540、390、706，中奖号码有两位数字相连，则投注半顺者视为中奖，其它视为不中奖。如果开奖号码为顺子、对子,则半顺视为不中奖。--如中奖号码为123、901、556、233，视为不中奖。
          $data = $this->data_chat;
          sort($data);
          if( !((($data[0] + 1) == $data[1] && ($data[1] + 1) == $data[2]) || ($data[0] == 0 && $data[1] == 1 && $data[2] == 9)) && $data[0] != $data[1] && $data[1] != $data[2] && $data[2] != $data[0] && (($data[0] + 1) == $data[1] || ($data[1] + 1) == $data[2] || ($data[0] == 0 && $data[1] == 1 && $data['2'] == 9))){
            return true;
          }else{
            return false;
          }
        },
      ],
      'lm' => [
        'q2zhixuan' => function($value){
          if(in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][1])){
            return true;
          }else{
            return false;
          }
        },
        'q2zhuxuan' => function($value){
          if(in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][0])){
            return true;
          }else{
            return false;
          }
        },
        'h2zhixuan' => function($value){
          if(in_array($this->prize_code[3],$value['code'][0]) && in_array($this->prize_code[4],$value['code'][1])){
            return true;
          }else{
            return false;
          }
        },
        'h2zhuxuan' => function($value){
          if(in_array($this->prize_code[3],$value['code'][0]) && in_array($this->prize_code[4],$value['code'][0])){
            return true;
          }else{
            return false;
          }
        },
        'q3zx' => function($value){
          if(in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][1]) && in_array($this->prize_code[2],$value['code'][2])){
            return true;
          }else{
            return false;
          }
        },
        'q3zl' => function($value){
          if(in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][0]) && in_array($this->prize_code[2],$value['code'][0]) && $this->prize_code[0] != $this->prize_code[1] && $this->prize_code[1] != $this->prize_code[2] && $this->prize_code[2] != $this->prize_code[0]){
            return true;
          }else{
            return false;
          }
        },
        'z3zx' => function($value){
          if(in_array($this->prize_code[1],$value['code'][0]) && in_array($this->prize_code[2],$value['code'][1]) && in_array($this->prize_code[3],$value['code'][2])){
            return true;
          }else{
            return false;
          }
        },
        'z3zl' => function($value){
          if(in_array($this->prize_code[1],$value['code'][0]) && in_array($this->prize_code[2],$value['code'][0]) && in_array($this->prize_code[3],$value['code'][0]) && $this->prize_code[1] != $this->prize_code[2] && $this->prize_code[2] != $this->prize_code[3] && $this->prize_code[3] != $this->prize_code[1]){
            return true;
          }else{
            return false;
          }
        },
        'h3zx' => function($value){
          if(in_array($this->prize_code[2],$value['code'][0]) && in_array($this->prize_code[3],$value['code'][1]) && in_array($this->prize_code[4],$value['code'][2])){
            return true;
          }else{
            return false;
          }
        },
        'h3zl' => function($value){
          if(in_array($this->prize_code[2],$value['code'][0]) && in_array($this->prize_code[3],$value['code'][0]) && in_array($this->prize_code[4],$value['code'][0]) && $this->prize_code[2] != $this->prize_code[3] && $this->prize_code[3] != $this->prize_code[4] && $this->prize_code[4] != $this->prize_code[2]){
            return true;
          }else{
            return false;
          }
        },
        'q3zs' => function($value){
          if(in_array($this->prize_code[0],$value['code'][0]) && (($this->prize_code[0] == $this->prize_code[1] && in_array($this->prize_code[2],$value['code'][0])) || ($this->prize_code[1] == $this->prize_code[2] && in_array($this->prize_code[2],$value['code'][0])) || ($this->prize_code[0] == $this->prize_code[2] && in_array($this->prize_code[1],$value['code'][0])))){
            return true;
          }else{
            return false;
          }
        },
        'z3zs' => function($value){
          if(in_array($this->prize_code[1],$value['code'][0]) && (($this->prize_code[1] == $this->prize_code[2] && in_array($this->prize_code[3],$value['code'][0])) || ($this->prize_code[2] == $this->prize_code[3] && in_array($this->prize_code[3],$value['code'][0])) || ($this->prize_code[1] == $this->prize_code[3] && in_array($this->prize_code[2],$value['code'][0])))){
            return true;
          }else{
            return false;
          }
        },
        'h3zs' => function($value){
          if(in_array($this->prize_code[2],$value['code'][0]) && (($this->prize_code[2] == $this->prize_code[3] && in_array($this->prize_code[4],$value['code'][0])) || ($this->prize_code[3] == $this->prize_code[4] && in_array($this->prize_code[4],$value['code'][0])) || ($this->prize_code[2] == $this->prize_code[4] && in_array($this->prize_code[3],$value['code'][0])))){
            return true;
          }else{
            return false;
          }
        },

      ],
      'qw' => [

        'yffs' => function($value){
          $this->prize_num = 0;
          foreach ($value['code'][0] as $value1) {
            foreach ($this->prize_code as $value2) {
              if($value1 == $value2){
                $this->prize_num++;
                break;
              }
            }
          }
          if($this->prize_num){
            return true;
          }else{
            return false;
          }
        },
        'hscs' => function($value){
          $this->prize_num = 0;
          foreach ($value['code'][0] as $value1) {
            $num = 0;
            foreach ($this->prize_code as $value2) {
              if($value1 == $value2){
                $num++;
                if($num > 1){
                  $this->prize_num++;
                  break;
                }
              }
            }
          }
          if($this->prize_num){
            return true;
          }else{
            return false;
          }
        },
        'sxbx' => function($value){
          $this->prize_num = 0;
          foreach ($value['code'][0] as $value1) {
            $num = 0;
            foreach ($this->prize_code as $value2) {
              if($value1 == $value2){
                $num++;
                if($num > 2){
                  $this->prize_num++;
                  break;
                }
              }
            }
          }
          if($this->prize_num){
            return true;
          }else{
            return false;
          }
        },
        'sjfc' => function($value){
          $this->prize_num = 0;
          foreach ($value['code'][0] as $value1) {
            $num = 0;
            foreach ($this->prize_code as $value2) {
              if($value1 == $value2){
                $num++;
                if($num > 3){
                  $this->prize_num++;
                  break;
                }
              }
            }
          }
          if($this->prize_num){
            return true;
          }else{
            return false;
          }
        },

      ],
      'sh' => [
        'zd' => function($value){
          foreach ($this->prize_code as $value1) {
            $num = 0;
            foreach ($this->prize_code as $value2) {
              if($value1 == $value2){
                $num++;
              }
              if($num > 3){
                return true;
              }
            }
          }
          return false;
        },
        'hl' => function($value){
          foreach ($this->prize_code as $value1) {
            $num = 0;$array = $this->prize_code;
            foreach ($this->prize_code as $key2 => $value2) {
              if($value1 == $value2){
                $num++;
                array_splice($array,$key2,1);
              }
              if($num == 3){
                if($array[0] == $array[1] && $array[1] != $value1){
                  return true;
                }else{
                  return false;
                }
              }
            }
          }
          return false;
        },
        'sz' => function($value){
          $data = $this->prize_code;
          sort($data);
          if(($data[0] + 1) == $data[1] && ($data[1] + 1) == $data[2] && ($data[2] + 1) == $data[3] && ($data[3] + 1) == $data[4]){
            return true;
          }else{
            return false;
          }
        },
        'st' => function($value){
          foreach ($this->prize_code as $value1) {
            $num = 0;$array = $this->prize_code;
            foreach ($this->prize_code as $key2 => $value2) {
              if($value1 == $value2){
                $num++;
                array_splice($array,$key2,1);
              }
              if($num == 3){
                if($array[0] != $array[1] && $array[0] != $value1 && $array[1] != $value1){
                  return true;
                }else{
                  return false;
                }
              }
            }
          }
          return false;
        },
        'nd' => function($value){
          foreach ($this->prize_code as $value1) {
            $num = 0;
            $array = $this->prize_code;
            foreach ($this->prize_code as $key2 => $value2) {
              if($value1 == $value2){
                $num++;
                array_splice($array,$key2,1);
              }
              if($num == 2){
                if(($array[0] == $array[1] && $array[1] != $value1) || ($array[1] == $array[2] && $array[1] != $value1) || ($array[0] == $array[2] && $array[2] != $value1)){
                  return true;
                }else{
                  return false;
                }
              }
            }
          }
        },
        'dd' => function($value){
          //单对新算法
          if( count( array_unique($this->prize_code) ) == 4 ){
            return true;
          }
          return false;
          
          // foreach ($this->prize_code as $value1) {
          //   $num = 0;$array = $this->prize_code;
          //   foreach ($this->prize_code as $key2 => $value2) {
          //     if($value1 == $value2){
          //       $num++;
          //       array_splice($array,$key2,1);
          //     }
          //     if($num == 2){
          //       if($array[0] != $array[1] && $array[1] != $array[2] && $array[0] != $array[2] && $array[0] != $value2 && $array[1] != $value2 && $value2 != $array[2]){
          //         return true;
          //       }else{
          //         return false;
          //       }
          //     }
          //   }
          // }
        },
        'sp' => function($value){
          $data = $this->prize_code;
          sort($data);
          if($data[0] != $data[1] && $data[1] != $data[2] && $data[2] != $data[3] && $data[3] != $data[4] && $data[0] != $data[4] && !(($data[0] + 1) == $data[1] && ($data[1] + 1) == $data[2] && ($data[2] + 1) == $data[3] && ($data[3] + 1) == $data[4])){
            return true;
          }else{
            return false;
          }
        },
      ],
      'nn' => [
        'n1' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data == 1){
            return true;
          }else{
            return false;
          }
        },
        'n2' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data == 2){
            return true;
          }else{
            return false;
          }
        },
        'n3' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data == 3){
            return true;
          }else{
            return false;
          }
        },
        'n4' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data == 4){
            return true;
          }else{
            return false;
          }
        },
        'n5' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data == 5){
            return true;
          }else{
            return false;
          }
        },
        'n6' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data == 6){
            return true;
          }else{
            return false;
          }
        },
        'n7' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data == 7){
            return true;
          }else{
            return false;
          }
        },
        'n8' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data == 8){
            return true;
          }else{
            return false;
          }
        },
        'n9' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data == 9){
            return true;
          }else{
            return false;
          }
        },
        'nn' => function($value){
          $data = $this->nnTool();
          if(isset($data) && is_numeric($data) && $data % 10 == 0){
            return true;
          }else{
            return false;
          }
        },

      ],
      'nnsm' => [

        'nda' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data > 5){
            return true;
          }else{
            return false;
          }
        },
        'nx' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data < 6){
            return true;
          }else{
            return false;
          }
        },
        'ndan' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data % 2){
            return true;
          }else{
            return false;
          }
        },
        'ns' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data && $data % 2 == 0){
            return true;
          }else{
            return false;
          }
        },
        'wn' => function($value){
          $data = $this->nnTool();
          if(isset($data) && $data){
            return false;
          }else{
            return true;
          }
        },

      ]
    ];
  }

  public function nnTool(){
    $prize_code = $this->prize_code;
    foreach ($this->combination($prize_code,3) as $value) {
      $data = array_sum($value);
      if($data % 10 == 0){
        foreach ($value as $value1) {
          $key = array_search($value1,$prize_code);
          if(isset($key)){
            array_splice($prize_code,$key,1);
          }
        }
        return array_sum($prize_code) % 10;
      }
    }
    return false;
  }
}
