<?php
namespace app\prize\controller;

class Ssc extends Lottery
{
  public $data_chat;
  public $prize_num;

  public function prize(){
    $this->actionPrize();
  }

  public function action($key1,$key2,$value,$money){
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
      default :
          $is_re = $this->paijiangOfficial($key1,$key2,$value,$money);
          return $is_re;
          break;
    }
    if($this->rule()[$key_chat][$key2]($value)){
      $return_data['code'] = 1;
      $return_data['num'] = $this->prize_num * ($money * $this->lottery_config['basic_config'][$key1]['items'][$key2]['odds']);
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

  public function paijiangOfficial($key1,$key2,$value,$money)
  {
      $re = [
          'code' => 0,
          'num' => 0
      ];
      if($key1 == 'official_wx' && $key2 == 'official_fushi')
      {
          $peidui = [];
          foreach ($value['code'] as $k=>$v)
          {
              if($v && isset($this->prize_code[$k]) && in_array($this->prize_code[$k],$v))
              {
                  array_push($peidui,1);
              }
              else
              {
                  array_push($peidui,0);
              }
          }
          if(array_sum($peidui) == 5)
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_wx' && $key2 == 'official_danshi')
      {
          $zhongjiangzhushi = 0;
          foreach ($value['code'] as $k=>$v)
          {
              if($v === $this->prize_code)
              {
                  $zhongjiangzhushi += 1;
              }
          }
          if($zhongjiangzhushi >= 1)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangzhushi * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_sx' && $key2 == 'official_q4fushi')
      {
          $peidui = [];
          foreach ($value['code'] as $k => $v)
          {
              if ($v && isset($this->prize_code[$k]) && in_array($this->prize_code[$k], $v))
              {
                  array_push($peidui, 1);
              } else {
                  array_push($peidui, 0);
              }
          }
          if (array_sum($peidui) == 4)
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_sx' && $key2 == 'official_q4danshi')
      {
          $zhongjiangzhushi = 0;
          foreach ($value['code'] as $k=>$v)
          {
              if($v === array_slice($this->prize_code,0,4))
              {
                  $zhongjiangzhushi += 1;
              }
          }
          if($zhongjiangzhushi >= 1)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangzhushi * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_sx' && $key2 == 'official_h4fushi')
      {
          $peidui = [];
          foreach ($value['code'] as $k => $v)
          {
              if ($v && isset($this->prize_code[$k]) && in_array($this->prize_code[$k], $v))
              {
                  array_push($peidui, 1);
              } else {
                  array_push($peidui, 0);
              }
          }
          if (array_sum($peidui) == 4)
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_sx' && $key2 == 'official_h4danshi')
      {
          $zhongjiangzhushi = 0;
          foreach ($value['code'] as $k=>$v)
          {
              if($v === array_slice($this->prize_code,1,4))
              {
                  $zhongjiangzhushi += 1;
              }
          }
          if($zhongjiangzhushi >= 1)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangzhushi * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_rxwf' && $key2 == 'official_r3z6')
      {
          $weizhi = $value['code'][5];
          //我购买的位置对应的开奖号码
          $weizhi_code = [];
          $weizhiArr = [];
          foreach ($weizhi as $k=>$w)
          {
              switch ($w)
              {
                  case 'w':
                      $weizhi_code[0] = $this->prize_code[0];
                      break;
                  case 'q':
                      $weizhi_code[1] = $this->prize_code[1];
                      break;
                  case 'b':
                      $weizhi_code[2] = $this->prize_code[2];
                      break;
                  case 's':
                      $weizhi_code[3] = $this->prize_code[3];
                      break;
                  case 'g':
                      $weizhi_code[4] = $this->prize_code[4];
                      break;
              }
          }
          //获取买对位置可能中间的组合
          for ($i=0;$i<=count($weizhi_code)-3;$i++)
          {
              for($j=$i+1;$j<=count($weizhi_code)-2;$j++)
              {
                  for($k=$j+1;$k<=count($weizhi_code)-1;$k++)
                  {
                      if(isset($weizhi_code[$i]) && isset($weizhi_code[$j]) && isset($weizhi_code[$k]))
                      {
                          if($weizhi_code[$i] != $weizhi_code[$j] && $weizhi_code[$i] != $weizhi_code[$k] && $weizhi_code[$j] != $weizhi_code[$k])
                          {
                              $new = $weizhi_code[$i].$weizhi_code[$j].$weizhi_code[$k];
                              $weizhiArr[] = $new;
                          }
                      }
                      else
                      {
                          continue;
                      }
                  }
              }
          }
          //我买的球的组合
          $maizhong = 0;
          foreach ($weizhiArr as $w)
          {
              if(in_array($w[0],$value['code'][0]) && in_array($w[1],$value['code'][0]) && in_array($w[2],$value['code'][0]))
              {
                  $maizhong += 1;
              }
          }

          if($maizhong > 0)
          {
              $re['code'] = 1;
              $re['num'] = $maizhong * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_rxwf' && $key2 == 'official_r3fushi')
      {
          $peidui = [];
          foreach ($value['code'] as $k=>$v)
          {
              if($v && isset($this->prize_code[$k]) && in_array($this->prize_code[$k],$v))
              {
                  array_push($peidui,1);
              }
              else
              {
                  array_push($peidui,0);
              }
          }
          $he = array_sum($peidui);
          if($he >= 3)
          {
              $num = ($he * ($he - 1) * ($he - 2))/6;
              $re['code'] = 1;
              $re['num'] = $num * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_rxwf' && $key2 == 'official_r2fushi')
      {
          $peidui = [];
          foreach ($value['code'] as $k=>$v)
          {
              if($v && isset($this->prize_code[$k]) && in_array($this->prize_code[$k],$v))
              {
                  array_push($peidui,1);
              }
              else
              {
                  array_push($peidui,0);
              }
          }
          $he = array_sum($peidui);
          if($he >= 2)
          {
              $num = ($he * ($he - 1))/2;
              $re['code'] = 1;
              $re['num'] = $num * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_rxwf' && $key2 == 'official_r3z3')
      {
          $weizhi = $value['code'][5];
          //我购买的位置对应的开奖号码
          $weizhi_code = [];
          $weizhiArr = [];
          foreach ($weizhi as $k=>$w)
          {
              switch ($w)
              {
                  case 'w':
                      $weizhi_code[0] = $this->prize_code[0];
                      break;
                  case 'q':
                      $weizhi_code[1] = $this->prize_code[1];
                      break;
                  case 'b':
                      $weizhi_code[2] = $this->prize_code[2];
                      break;
                  case 's':
                      $weizhi_code[3] = $this->prize_code[3];
                      break;
                  case 'g':
                      $weizhi_code[4] = $this->prize_code[4];
                      break;
              }
          }
          //获取买对位置可能中间的组合
          for ($i=0;$i<=count($weizhi_code)-3;$i++)
          {
              for($j=$i+1;$j<=count($weizhi_code)-2;$j++)
              {
                  for($k=$j+1;$k<=count($weizhi_code)-1;$k++)
                  {
                      if(isset($weizhi_code[$i]) && isset($weizhi_code[$j]) && isset($weizhi_code[$k]))
                      {
                          $new = $weizhi_code[$i].$weizhi_code[$j].$weizhi_code[$k];
                          $weizhiArr[] = $new;
                      }
                      else
                      {
                          continue;
                      }
                  }
              }
          }
          //我买的球的组合
          $all_zu_he = [];
          for ($i=0;$i<=count($value['code'][0])-1;$i++)
          {
              for($j=0;$j<=count($value['code'][0])-1;$j++)
              {
                  if($i != $j)
                  {
                      $all_zu_he[] = $value['code'][0][$i].$value['code'][0][$j].$value['code'][0][$j];
                      $all_zu_he[] = $value['code'][0][$i].$value['code'][0][$i].$value['code'][0][$j];
                      $all_zu_he[] = $value['code'][0][$i].$value['code'][0][$j].$value['code'][0][$i];
                  }
              }
          }

          $zhongjiangCount = 0;
          foreach ($weizhiArr as $w)
          {
              if(in_array($w,$all_zu_he))
              {
                  $zhongjiangCount += 1;
              }
          }
          $re['code'] = 1;
          $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
      }

      if($key1 == 'official_rxwf' && $key2 == 'official_r4fushi')
      {
          $peidui = [];
          foreach ($value['code'] as $k => $v)
          {
              if ($v && isset($this->prize_code[$k]) && in_array($this->prize_code[$k], $v))
              {
                  array_push($peidui, 1);
              } else {
                  array_push($peidui, 0);
              }
          }
          if (array_sum($peidui) == 4)
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
          if (array_sum($peidui) == 5)
          {
              $re['code'] = 1;
              $re['num'] = 5 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_longhuhe')
      {
          $zhongjiang = false;
          if($this->prize_code[0] > $this->prize_code[4])
          {
              if(in_array('l',$value['code'][5]))
              {
                  $zhongjiang = true;
              }
          }
          else if($this->prize_code[0] < $this->prize_code[4])
          {
              if(in_array('h',$value['code'][5]))
              {
                  $zhongjiang = true;
              }
          }
          else
          {
              if(in_array('he',$value['code'][5]))
              {
                  $zhongjiang = true;
              }
          }
          if($zhongjiang)
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }

      }

      if($key1 == 'official_bz' && $key2 == 'official_baozi')
      {
          $zhongjiangCount = 0;
          if($this->isBaoZi($this->prize_code,'q3') && in_array('q3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }
          if($this->isBaoZi($this->prize_code,'z3') && in_array('z3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }
          if($this->isBaoZi($this->prize_code,'h3') && in_array('h3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }

          if($zhongjiangCount >= 1)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_bz' && $key2 == 'official_shunzi')
      {
          $zhongjiangCount = 0;
          if($this->isShunZi($this->prize_code,'q3') && in_array('q3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }
          if($this->isShunZi($this->prize_code,'z3') && in_array('z3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }
          if($this->isShunZi($this->prize_code,'h3') && in_array('h3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }

          if($zhongjiangCount >= 1)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_bz' && $key2 == 'official_duizi')
      {
          $zhongjiangCount = 0;
          if($this->isDuiZi($this->prize_code,'q3') && in_array('q3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }
          if($this->isDuiZi($this->prize_code,'z3') && in_array('z3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }
          if($this->isDuiZi($this->prize_code,'h3') && in_array('h3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }

          if($zhongjiangCount >= 1)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_bz' && $key2 == 'official_banshun')
      {
          $zhongjiangCount = 0;
          if(!$this->isShunZi($this->prize_code,'q3') && !$this->isDuiZi($this->prize_code,'q3') && $this->isBanShun($this->prize_code,'q3') && in_array('q3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }
          if(!$this->isShunZi($this->prize_code,'z3') && !$this->isDuiZi($this->prize_code,'z3') && $this->isBanShun($this->prize_code,'z3') && in_array('z3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }
          if(!$this->isShunZi($this->prize_code,'h3') && !$this->isDuiZi($this->prize_code,'h3') && $this->isBanShun($this->prize_code,'h3') && in_array('h3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }
          if($zhongjiangCount >= 1)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_bz' && $key2 == 'official_zaliu')
      {
          $zhongjiangCount = 0;
          if(!$this->isBaoZi($this->prize_code,'q3') && !$this->isShunZi($this->prize_code,'q3') && !$this->isDuiZi($this->prize_code,'q3') && !$this->isBanShun($this->prize_code,'q3') && in_array('q3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }
          if(!$this->isBaoZi($this->prize_code,'z3') && !$this->isShunZi($this->prize_code,'z3') && !$this->isDuiZi($this->prize_code,'z3') && !$this->isBanShun($this->prize_code,'z3') && in_array('z3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }
          if(!$this->isBaoZi($this->prize_code,'h3') && !$this->isShunZi($this->prize_code,'h3') && !$this->isDuiZi($this->prize_code,'h3') && !$this->isBanShun($this->prize_code,'h3') && in_array('h3',$value['code'][5]))
          {
              $zhongjiangCount += 1;
          }
          if($zhongjiangCount >= 1)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }
      //前三
      if($key1 == 'official_q3' && $key2 == 'official_fushi')
      {
          if(in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][1]) && in_array($this->prize_code[2],$value['code'][2]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_q3' && $key2 == 'official_danshi')
      {
          $zhongjiangCount = 0;
          foreach ($value['code'] as $v)
          {
              if($v[0] == $this->prize_code[0] && $v[1] == $this->prize_code[1] && $v[2] == $this->prize_code[2])
              {
                  $zhongjiangCount += 1;
              }
          }
          if($zhongjiangCount > 0)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_q3' && $key2 == 'official_zu3')
      {
          //我买的球的组合
          $all_zu_he = [];
          for ($i=0;$i<=count($value['code'][0])-1;$i++)
          {
              for($j=0;$j<=count($value['code'][0])-1;$j++)
              {
                  if($i != $j)
                  {
                      $all_zu_he[] = $value['code'][0][$i].$value['code'][0][$j].$value['code'][0][$j];
                      $all_zu_he[] = $value['code'][0][$i].$value['code'][0][$i].$value['code'][0][$j];
                      $all_zu_he[] = $value['code'][0][$i].$value['code'][0][$j].$value['code'][0][$i];
                  }
              }
          }

          if(in_array($this->prize_code[0].$this->prize_code[1].$this->prize_code[2],$all_zu_he))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_q3' && $key2 == 'official_zu6')
      {
          if(in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][0]) && in_array($this->prize_code[2],$value['code'][0]) && $this->prize_code[0] != $this->prize_code[1] && $this->prize_code[1] != $this->prize_code[2] && $this->prize_code[0] != $this->prize_code[2])
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }

      }
      //中三
      if($key1 == 'official_z3' && $key2 == 'official_fushi')
      {
          if(in_array($this->prize_code[3],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][1]) && in_array($this->prize_code[2],$value['code'][2]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_z3' && $key2 == 'official_danshi')
      {
          $zhongjiangCount = 0;
          foreach ($value['code'] as $v)
          {
              if($v[0] == $this->prize_code[1] && $v[2] == $this->prize_code[2] && $v[2] == $this->prize_code[3])
              {
                  $zhongjiangCount += 1;
              }
          }
          if($zhongjiangCount > 0)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_z3' && $key2 == 'official_zu3')
      {
          //我买的球的组合
          $all_zu_he = [];
          for ($i=0;$i<=count($value['code'][0])-1;$i++)
          {
              for($j=0;$j<=count($value['code'][0])-1;$j++)
              {
                  if($i != $j)
                  {
                      $all_zu_he[] = $value['code'][0][$i].$value['code'][0][$j].$value['code'][0][$j];
                      $all_zu_he[] = $value['code'][0][$i].$value['code'][0][$i].$value['code'][0][$j];
                      $all_zu_he[] = $value['code'][0][$i].$value['code'][0][$j].$value['code'][0][$i];
                  }
              }
          }

          if(in_array($this->prize_code[3].$this->prize_code[1].$this->prize_code[2],$all_zu_he))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_z3' && $key2 == 'official_zu6')
      {
          if(in_array($this->prize_code[3],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][0]) && in_array($this->prize_code[2],$value['code'][0]) && $this->prize_code[3] != $this->prize_code[1] && $this->prize_code[1] != $this->prize_code[2] && $this->prize_code[3] != $this->prize_code[2])
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }

      }
      //后三
      if($key1 == 'official_h3' && $key2 == 'official_fushi')
      {
          if(in_array($this->prize_code[3],$value['code'][0]) && in_array($this->prize_code[4],$value['code'][1]) && in_array($this->prize_code[2],$value['code'][2]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_h3' && $key2 == 'official_danshi')
      {
          $zhongjiangCount = 0;
          foreach ($value['code'] as $v)
          {
              if($v[0] == $this->prize_code[2] && $v[1] == $this->prize_code[3] && $v[2] == $this->prize_code[4])
              {
                  $zhongjiangCount += 1;
              }
          }
          if($zhongjiangCount > 0)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_h3' && $key2 == 'official_zu3')
      {
          //我买的球的组合
          $all_zu_he = [];
          for ($i=0;$i<=count($value['code'][0])-1;$i++)
          {
              for($j=0;$j<=count($value['code'][0])-1;$j++)
              {
                  if($i != $j)
                  {
                      $all_zu_he[] = $value['code'][0][$i].$value['code'][0][$j].$value['code'][0][$j];
                      $all_zu_he[] = $value['code'][0][$i].$value['code'][0][$i].$value['code'][0][$j];
                      $all_zu_he[] = $value['code'][0][$i].$value['code'][0][$j].$value['code'][0][$i];
                  }
              }
          }

          if(in_array($this->prize_code[3].$this->prize_code[4].$this->prize_code[2],$all_zu_he))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_h3' && $key2 == 'official_zu6')
      {
          if(in_array($this->prize_code[3],$value['code'][0]) && in_array($this->prize_code[4],$value['code'][0]) && in_array($this->prize_code[2],$value['code'][0]) && $this->prize_code[3] != $this->prize_code[4] && $this->prize_code[4] != $this->prize_code[2] && $this->prize_code[3] != $this->prize_code[2])
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }

      }

      if($key1 == 'official_q2' && $key2 == 'official_zxzh')
      {
          $sum = $this->prize_code[0] + $this->prize_code[1];
          if(in_array($sum,$value['code'][0]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_q2' && $key2 == 'official_zxfushi')
      {
          if(in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][1]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_q2' && $key2 == 'official_zxdanshi')
      {
          $zhongjiangCount = 0;
          foreach ($value['code'] as $v)
          {
              if($v[0] == $this->prize_code[0] && $v[1] == $this->prize_code[1])
              {
                  $zhongjiangCount += 1;
              }
          }
          if($zhongjiangCount > 0)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      //后二
      if($key1 == 'official_h2' && $key2 == 'official_zxzh')
      {
          $sum = $this->prize_code[3] + $this->prize_code[4];
          if(in_array($sum,$value['code'][0]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_h2' && $key2 == 'official_zxfushi')
      {
          if(in_array($this->prize_code[3],$value['code'][3]) && in_array($this->prize_code[4],$value['code'][4]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_h2' && $key2 == 'official_zxdanshi')
      {
          $zhongjiangCount = 0;
          foreach ($value['code'] as $v)
          {
              if($v[0] == $this->prize_code[3] && $v[1] == $this->prize_code[4])
              {
                  $zhongjiangCount += 1;
              }
          }
          if($zhongjiangCount > 0)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_yx' && $key2 == 'official_fushi')
      {
          $zhongjiangCount = 0;
          foreach ($value['code'] as $k=>$v)
          {
              if($v && isset($this->prize_code[$k]) && in_array($this->prize_code[$k],$v))
              {
                  $zhongjiangCount += 1;
              }
          }
          if($zhongjiangCount > 0)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_dxds' && $key2 == 'official_zh')
      {
          $sum = $this->prize_code[0] + $this->prize_code[1] +$this->prize_code[2] +$this->prize_code[3] +$this->prize_code[4];
          $zhuangtai = $value['code'][0];
          $maiRe = [];
          $maiRe['dx'] = $sum >= 23 ? 'da' : 'x';
          $maiRe['ds'] = $sum%2 ? 'dan' : 's';
          $zhongjiangCount = 0;
          if(in_array($maiRe['dx'],$zhuangtai))
          {
              $zhongjiangCount += 1;
          }
          if(in_array($maiRe['ds'],$zhuangtai))
          {
              $zhongjiangCount += 1;
          }
          if($zhongjiangCount > 0)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_dxds' && $key2 == 'official_q3')
      {
          $maiRe = [];
          $maiRe[0]['dx'] = $this->prize_code[0] >= 5 ? 'da' : 'x';
          $maiRe[0]['ds'] = $this->prize_code[0]%2 ? 'dan' : 's';
          $maiRe[1]['dx'] = $this->prize_code[1] >= 5 ? 'da' : 'x';
          $maiRe[1]['ds'] = $this->prize_code[1]%2 ? 'dan' : 's';
          $maiRe[2]['dx'] = $this->prize_code[2] >= 5 ? 'da' : 'x';
          $maiRe[2]['ds'] = $this->prize_code[2]%2 ? 'dan' : 's';
          $zhongjiangCount = [0,0,0];

          foreach ($maiRe as $k=>$v)
          {
              if(in_array($maiRe[$k]['dx'],$value['code'][$k]))
              {
                  $zhongjiangCount[$k] += 1;
              }
              if(in_array($maiRe[$k]['ds'],$value['code'][$k]))
              {
                  $zhongjiangCount[$k] += 1;
              }
          }
          $all = $zhongjiangCount[0] * $zhongjiangCount[1] * $zhongjiangCount[2];
          if($all > 0)
          {
              $re['code'] = 1;
              $re['num'] = $all * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_dxds' && $key2 == 'official_q2')
      {
          $maiRe = [];
          $maiRe[0]['dx'] = $this->prize_code[0] >= 5 ? 'da' : 'x';
          $maiRe[0]['ds'] = $this->prize_code[0]%2 ? 'dan' : 's';
          $maiRe[1]['dx'] = $this->prize_code[1] >= 5 ? 'da' : 'x';
          $maiRe[1]['ds'] = $this->prize_code[1]%2 ? 'dan' : 's';
          $zhongjiangCount = [0,0];

          foreach ($maiRe as $k=>$v)
          {
              if(in_array($maiRe[$k]['dx'],$value['code'][$k]))
              {
                  $zhongjiangCount[$k] += 1;
              }
              if(in_array($maiRe[$k]['ds'],$value['code'][$k]))
              {
                  $zhongjiangCount[$k] += 1;
              }
          }
          $all = $zhongjiangCount[0] * $zhongjiangCount[1];
          if($all > 0)
          {
              $re['code'] = 1;
              $re['num'] = $all * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_dxds' && $key2 == 'official_h3')
      {
          $maiRe = [];
          $maiRe[3]['dx'] = $this->prize_code[3] >= 5 ? 'da' : 'x';
          $maiRe[3]['ds'] = $this->prize_code[3]%2 ? 'dan' : 's';
          $maiRe[4]['dx'] = $this->prize_code[4] >= 5 ? 'da' : 'x';
          $maiRe[4]['ds'] = $this->prize_code[4]%2 ? 'dan' : 's';
          $maiRe[2]['dx'] = $this->prize_code[2] >= 5 ? 'da' : 'x';
          $maiRe[2]['ds'] = $this->prize_code[2]%2 ? 'dan' : 's';
          $zhongjiangCount = ['3'=>0,'4'=>0,'2'=>0];

          foreach ($maiRe as $k=>$v)
          {
              if(in_array($maiRe[$k]['dx'],$value['code'][$k]))
              {
                  $zhongjiangCount[$k] += 1;
              }
              if(in_array($maiRe[$k]['ds'],$value['code'][$k]))
              {
                  $zhongjiangCount[$k] += 1;
              }
          }
          $all = $zhongjiangCount[3] * $zhongjiangCount[4] * $zhongjiangCount[2];
          if($all > 0)
          {
              $re['code'] = 1;
              $re['num'] = $all * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_dxds' && $key2 == 'official_h2')
      {
          $maiRe = [];
          $maiRe[3]['dx'] = $this->prize_code[3] >= 5 ? 'da' : 'x';
          $maiRe[3]['ds'] = $this->prize_code[3]%2 ? 'dan' : 's';
          $maiRe[4]['dx'] = $this->prize_code[4] >= 5 ? 'da' : 'x';
          $maiRe[4]['ds'] = $this->prize_code[4]%2 ? 'dan' : 's';
          $zhongjiangCount = [0,0];

          foreach ($maiRe as $k=>$v)
          {
              if(in_array($maiRe[$k]['dx'],$value['code'][$k]))
              {
                  $zhongjiangCount[$k] += 1;
              }
              if(in_array($maiRe[$k]['ds'],$value['code'][$k]))
              {
                  $zhongjiangCount[$k] += 1;
              }
          }
          $all = $zhongjiangCount[3] * $zhongjiangCount[4];
          if($all > 0)
          {
              $re['code'] = 1;
              $re['num'] = $all * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_bdwd' && $key2 == 'official_q3ym')
      {
          $zhongjiangCount = 0;
          if(in_array($this->prize_code[0],$value['code'][0]))
          {
              $zhongjiangCount += 1;
          }
          if(in_array($this->prize_code[1],$value['code'][0]))
          {
              $zhongjiangCount += 1;
          }
          if(in_array($this->prize_code[2],$value['code'][0]))
          {
              $zhongjiangCount += 1;
          }
          if($zhongjiangCount > 0)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_bdwd' && $key2 == 'official_z3ym')
      {
          $zhongjiangCount = 0;
          if(in_array($this->prize_code[3],$value['code'][0]))
          {
              $zhongjiangCount += 1;
          }
          if(in_array($this->prize_code[1],$value['code'][0]))
          {
              $zhongjiangCount += 1;
          }
          if(in_array($this->prize_code[2],$value['code'][0]))
          {
              $zhongjiangCount += 1;
          }
          if($zhongjiangCount > 0)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }

      if($key1 == 'official_bdwd' && $key2 == 'official_h3ym')
      {
          $zhongjiangCount = 0;
          if(in_array($this->prize_code[3],$value['code'][0]))
          {
              $zhongjiangCount += 1;
          }
          if(in_array($this->prize_code[4],$value['code'][0]))
          {
              $zhongjiangCount += 1;
          }
          if(in_array($this->prize_code[2],$value['code'][0]))
          {
              $zhongjiangCount += 1;
          }
          if($zhongjiangCount > 0)
          {
              $re['code'] = 1;
              $re['num'] = $zhongjiangCount * ($money * $this->lottery_config['official'][$key1]['items'][$key2]['odds']);
          }
      }
      return $re;
  }

    //判断是否是豹子
    public function isBaoZi($arr,$type)
    {
        if($type == 'q3')
        {
            if($arr[0] == $arr[1] && $arr[1] == $arr[2])
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        if($type == 'z3')
        {
            if($arr[3] == $arr[1] && $arr[1] == $arr[2])
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        if($type == 'h3')
        {
            if($arr[3] == $arr[4] && $arr[4] == $arr[2])
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
    //判断是否是顺子
    public function isShunZi($arr,$type)
    {
        if($type == 'q3')
        {
            $q3Arr = [$arr[0],$arr[1],$arr[2]];
            sort($q3Arr);
            $code1 = $q3Arr[0] != 9 ? $q3Arr[0] + 1 : 0;
            $code2 = $q3Arr[1];
            $code3 = $q3Arr[2] > 0 ? $q3Arr[2] - 1 : 9;

            if(($code1 == $code2 && $code2 == $code3) || ($q3Arr[0] == 0 && $q3Arr[1] == 1 && $q3Arr[2] == 9))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        if($type == 'z3')
        {

            $q3Arr = [$arr[1],$arr[2],$arr[3]];
            sort($q3Arr);
            $code1 = $q3Arr[0] != 9 ? $q3Arr[0] + 1 : 0;
            $code2 = $q3Arr[1];
            $code3 = $q3Arr[2] > 0 ? $q3Arr[2] - 1 : 9;
            if(($code1 == $code2 && $code2 == $code3))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        if($type == 'h3')
        {
            $q3Arr = [$arr[2],$arr[3],$arr[4]];
            sort($q3Arr);
            $code1 = $q3Arr[0] != 9 ? $q3Arr[0] + 1 : 0;
            $code2 = $q3Arr[1];
            $code3 = $q3Arr[2] > 0 ? $q3Arr[2] - 1 : 9;
            if(($code1 == $code2 && $code2 == $code3))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    //判断是否是对子
    public function isDuiZi($arr,$type)
    {
        if($type == 'q3')
        {
            if(($arr[0] == $arr[1] && $arr[1] != $arr[2]) || (($arr[0] != $arr[1] && $arr[1] == $arr[2])) || ($arr[0] == $arr[2] && $arr[1] != $arr[2]))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        if($type == 'z3')
        {
            if(($arr[1] == $arr[2] && $arr[2] != $arr[3]) || (($arr[1] != $arr[2] && $arr[2] == $arr[3])) || ($arr[3] == $arr[1] && $arr[3] != $arr[2]))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        if($type == 'h3')
        {
            if(($arr[2] == $arr[3] && $arr[3] != $arr[4]) || (($arr[2] != $arr[3] && $arr[3] == $arr[4])) || ($arr[2] == $arr[4] && $arr[3] != $arr[2]))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }

    //判断是否是半顺
    public function isBanShun($arr,$type)
    {
        if($type == 'q3')
        {
            $q3Arr = [$arr[0],$arr[1],$arr[2]];
            sort($q3Arr);
            $code1 = $q3Arr[0] != 9 ? $q3Arr[0] + 1 : 0;
            $code2 = $q3Arr[1];
            $code3 = $q3Arr[2] > 0 ? $q3Arr[2] - 1 : 9;

            if($code1 == $code2 || $code2 == $code3)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        if($type == 'z3')
        {
            $q3Arr = [$arr[1],$arr[2],$arr[3]];
            sort($q3Arr);
            $code1 = $q3Arr[0] != 9 ? $q3Arr[0] + 1 : 0;
            $code2 = $q3Arr[1];
            $code3 = $q3Arr[2] > 0 ? $q3Arr[2] - 1 : 9;

            if($code1 == $code2 || $code2 == $code3)
            {
                return true;
            }
            else
            {
                return false;
            }

        }
        if($type == 'h3')
        {
            $q3Arr = [$arr[2],$arr[3],$arr[4]];
            sort($q3Arr);
            $code1 = $q3Arr[0] != 9 ? $q3Arr[0] + 1 : 0;
            $code2 = $q3Arr[1];
            $code3 = $q3Arr[2] > 0 ? $q3Arr[2] - 1 : 9;
            if($code1 == $code2 || $code2 == $code3)
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
}
