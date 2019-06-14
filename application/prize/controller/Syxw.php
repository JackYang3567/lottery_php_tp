<?php
namespace app\prize\controller;

class Syxw extends Lottery
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
      default:
         $re = $this->paijiangOfficial($key1,$key2,$value,$money,$other,$odds);
         return $re;
         break;
    }
    if($this->rule()[$key_chat][$key2]($value)){
      $return_data['code'] = 1;
      $return_data['num'] = $this->prize_num * ($money * $odds);
    }
    // 总和开出的5个号码之和大于30为大，小于30为小；30为和局，退还本金。
    // 位置开出号码大于或等于6为大，开出号码小于或等于5为小；开出11为和局，退还本金。
    if(($key_chat == 'zh' && ($key2 == 'da' || $key2 == 'x') && $this->data_chat == 30) || ($key_chat == 'wei' && ($key2 == 'da' || $key2 == 'x') && $this->data_chat == 11)){
      $return_data['code'] = 1;
      $return_data['num'] = $money;
    }
    return $return_data;
  }

  public function rule(){
    return [
      'wei' => [
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
        'code_10' => function($value){
          if($this->data_chat == 10){
            return true;
          }else{
            return false;
          }
        },
        'code_11' => function($value){
          if($this->data_chat == 11){
            return true;
          }else{
            return false;
          }
        },
        'da' => function($value){
          if($this->data_chat > 5 && $this->data_chat != 11){
            return true;
          }else{
            return false;
          }
        },
        'x' => function($value){
          if($this->data_chat < 6){
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
      ],
     'zh' => [
        'da' => function($value){
          if($this->data_chat > 30){
            return true;
          }else{
            return false;
          }
        },
        'x' => function($value){
          if($this->data_chat < 30){
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
      ],

      'bzwf' => [
        'q1zx' => function($value){
          if(in_array($this->prize_code[0],$value['code'][0])){
            return true;
          }else{
            return false;
          }
        },
        'q2zx' => function($value){
          if(in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][1])){
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
        'rx1' => function($value){
          $this->prize_num = 0;
          foreach ($value['code'][0] as $value) {
            if(in_array($value,$this->prize_code)){
              $this->prize_num++;
            }
          }
          if($this->prize_num){
            return true;
          }
          $this->prize_num = 1;
          return false;
        },
        'rx2' => function($value){
          $this->prize_num = 0;
          foreach ($this->combination($value['code'][0],2) as $value1) {
            $num = 0;
            foreach ($value1 as $value2) {
              if(in_array($value2,$this->prize_code)){
                $num++;
              }
            }
            if($num > 1){
              $this->prize_num += 1;
            }
          }
          if($this->prize_num){
            return true;
          }
          $this->prize_num = 1;
          return false;
        },
        'rx3' => function($value){
          $this->prize_num = 0;
          foreach ($this->combination($value['code'][0],3) as $value1) {
            $num = 0;
            foreach ($value1 as $value2) {
              if(in_array($value2,$this->prize_code)){
                $num++;
              }
            }
            if($num > 2){
              $this->prize_num++;
              continue;
            }
          }
          if($this->prize_num){
            return true;
          }
          $this->prize_num = 1;
          return false;
        },
        'rx4' => function($value){
          $this->prize_num = 0;
          foreach ($this->combination($value['code'][0],4) as $value1) {
            $num = 0;
            foreach ($value1 as $value2) {
              if(in_array($value2,$this->prize_code)){
                $num++;
              }
            }
            if($num > 3){
              $this->prize_num++;
              continue;
            }
          }
          if($this->prize_num){
            return true;
          }
          $this->prize_num = 1;
          return false;
        },
        'rx5' => function($value){
          $this->prize_num = 0;
          foreach ($this->combination($value['code'][0],5) as $value1) {
            $num = 0;
            foreach ($value1 as $value2) {
              if(in_array($value2,$this->prize_code)){
                $num++;
              }
            }
            if($num > 4){
              $this->prize_num++;
              continue;
            }
          }
          if($this->prize_num){
            return true;
          }
          $this->prize_num = 1;
          return false;
        },
        'rx6' => function($value){
          $this->prize_num = 0;
          foreach ($this->combination($value['code'][0],6) as $value1) {
            $num = 0;
            foreach ($value1 as $value2) {
              if(in_array($value2,$this->prize_code)){
                $num++;
              }
            }
            if($num > 4){
              $this->prize_num++;
              continue;
            }
          }
          if($this->prize_num){
            return true;
          }
          $this->prize_num = 1;
          return false;
        },
        'rx7' => function($value){
          $this->prize_num = 0;
          foreach ($this->combination($value['code'][0],7) as $value1) {
            $num = 0;
            foreach ($value1 as $value2) {
              if(in_array($value2,$this->prize_code)){
                $num++;
              }
            }
            if($num > 4){
              $this->prize_num++;
              continue;
            }
          }
          if($this->prize_num){
            return true;
          }
          $this->prize_num = 1;
          return false;
        },
        'rx8' => function($value){
          $this->prize_num = 0;
          foreach ($this->combination($value['code'][0],8) as $value1) {
            $num = 0;
            foreach ($value1 as $value2) {
              if(in_array($value2,$this->prize_code)){
                $num++;
              }
            }
            if($num > 4){
              $this->prize_num++;
              continue;
            }
          }
          if($this->prize_num){
            return true;
          }
          $this->prize_num = 1;
          return false;
        },
      ]
    ];
  }

  public function paijiangOfficial($key1,$key2,$value,$money,$other,$odds)
  {
      $re = [
          'code' => 0,
          'num' => 0
      ];
      if($key1 == 'official_rx' && $key2 == 'official_1z1')
      {
          $codeNum = 0;
          foreach ($value['code'][0] as $v)
          {
              if(in_array($v,$this->prize_code))
              {
                  $codeNum += 1;
              }
          }
          $re['code'] = 1;
          $re['num'] = $codeNum * ($money * $odds);
      }

      if($key1 == 'official_rx' && $key2 == 'official_2z2')
      {
          //我买的号码任选二个组合
          $maiAll = $this->combination($value['code'][0],2);
          $codeNum = 0;
          foreach ($maiAll as $value)
          {
              $num = 0;
              foreach ($value as $v)
              {
                  if(in_array($v,$this->prize_code))
                  {
                      $num += 1;
                  }
              }
              if($num > 1)
              {
                  $codeNum += 1;
              }
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_rx' && $key2 == 'official_3z3')
      {
          //我买的号码任选二个组合
          $maiAll = $this->combination($value['code'][0],3);
          $codeNum = 0;
          foreach ($maiAll as $value)
          {
              $num = 0;
              foreach ($value as $v)
              {
                  if(in_array($v,$this->prize_code))
                  {
                      $num += 1;
                  }
              }
              if($num > 2)
              {
                  $codeNum += 1;
              }
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_rx' && $key2 == 'official_4z4')
      {
          //我买的号码任选二个组合
          $maiAll = $this->combination($value['code'][0],4);
          $codeNum = 0;
          foreach ($maiAll as $value)
          {
              $num = 0;
              foreach ($value as $v)
              {
                  if(in_array($v,$this->prize_code))
                  {
                      $num += 1;
                  }
              }
              if($num > 3)
              {
                  $codeNum += 1;
              }
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_rx' && $key2 == 'official_6z5')
      {
          //我买的号码任选二个组合
          $maiAll = $this->combination($value['code'][0],6);
          $codeNum = 0;
          foreach ($maiAll as $value)
          {
              $num = 0;
              foreach ($value as $v)
              {
                  if(in_array($v,$this->prize_code))
                  {
                      $num += 1;
                  }
              }
              if($num > 4)
              {
                  $codeNum += 1;
              }
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_rx' && $key2 == 'official_7z5')
      {
          //我买的号码任选二个组合
          $maiAll = $this->combination($value['code'][0],7);
          $codeNum = 0;
          foreach ($maiAll as $value)
          {
              $num = 0;
              foreach ($value as $v)
              {
                  if(in_array($v,$this->prize_code))
                  {
                      $num += 1;
                  }
              }
              if($num > 4)
              {
                  $codeNum += 1;
              }
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_rx' && $key2 == 'official_8z5')
      {
          //我买的号码任选二个组合
          $maiAll = $this->combination($value['code'][0],8);
          $codeNum = 0;
          foreach ($maiAll as $value)
          {
              $num = 0;
              foreach ($value as $v)
              {
                  if(in_array($v,$this->prize_code))
                  {
                      $num += 1;
                  }
              }
              if($num > 4)
              {
                  $codeNum += 1;
              }
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_dwd' && $key2 == 'official_fs')
      {
          $codeNum = 0;
          foreach ($value['code'] as $k=>$v)
          {
              if(in_array($this->prize_code[$k],$v))
              {
                  $codeNum += 1;
              }
          }

          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_3m' && $key2 == 'official_q3zhifs')
      {
          if(in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][1]) && in_array($this->prize_code[2],$value['code'][2]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_3m' && $key2 == 'official_q3zufs')
      {
          if(in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][0]) && in_array($this->prize_code[2],$value['code'][0]) && $this->prize_code[0] != $this->prize_code[1] && $this->prize_code[1] != $this->prize_code[2] && $this->prize_code[0] != $this->prize_code[2])
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_3m' && $key2 == 'official_z3fs')
      {
          if(in_array($this->prize_code[3],$value['code'][3]) && in_array($this->prize_code[1],$value['code'][1]) && in_array($this->prize_code[2],$value['code'][2]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_3m' && $key2 == 'official_z3zx')
      {
          if(in_array($this->prize_code[3],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][0]) && in_array($this->prize_code[2],$value['code'][0]) && $this->prize_code[3] != $this->prize_code[1] && $this->prize_code[1] != $this->prize_code[2] && $this->prize_code[3] != $this->prize_code[2])
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_3m' && $key2 == 'official_h3fs')
      {
          if(in_array($this->prize_code[3],$value['code'][3]) && in_array($this->prize_code[4],$value['code'][4]) && in_array($this->prize_code[2],$value['code'][2]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_3m' && $key2 == 'official_h3zx')
      {
          if(in_array($this->prize_code[3],$value['code'][0]) && in_array($this->prize_code[4],$value['code'][0]) && in_array($this->prize_code[2],$value['code'][0]) && $this->prize_code[3] != $this->prize_code[4] && $this->prize_code[4] != $this->prize_code[2] && $this->prize_code[3] != $this->prize_code[2])
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_3m' && $key2 == 'official_q3zhids')
      {
          $codeNum = 0;
          foreach ($value['code'] as $v)
          {
              if($v[0] == $this->prize_code[0] && $v[1] == $this->prize_code[1] && $v[2] == $this->prize_code[2])
              {
                  $codeNum += 1;
              }
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_3m' && $key2 == 'official_z3ds')
      {
          $codeNum = 0;
          foreach ($value['code'] as $v)
          {
              if($v[0] == $this->prize_code[1] && $v[1] == $this->prize_code[2] && $v[2] == $this->prize_code[3])
              {
                  $codeNum += 1;
              }
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_3m' && $key2 == 'official_h3ds')
      {
          $codeNum = 0;
          foreach ($value['code'] as $v)
          {
              if($v[0] == $this->prize_code[2] && $v[1] == $this->prize_code[3] && $v[2] == $this->prize_code[4])
              {
                  $codeNum += 1;
              }
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_2m' && $key2 == 'official_q2zhifs')
      {
          if(in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][1]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_2m' && $key2 == 'official_q2zufs')
      {
          if(in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][0])  && $this->prize_code[0] != $this->prize_code[1])
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_2m' && $key2 == 'official_h2fs')
      {
          if(in_array($this->prize_code[3],$value['code'][3]) && in_array($this->prize_code[4],$value['code'][4]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_2m' && $key2 == 'official_h2zx')
      {
          if(in_array($this->prize_code[3],$value['code'][0]) && in_array($this->prize_code[4],$value['code'][0])  && $this->prize_code[4] != $this->prize_code[3])
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_2m' && $key2 == 'official_q2zhids')
      {
          $codeNum = 0;
          foreach ($value['code'] as $v)
          {
              if($v[0] == $this->prize_code[0] && $v[1] == $this->prize_code[1])
              {
                  $codeNum += 1;
              }
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_2m' && $key2 == 'official_h2ds')
      {
          $codeNum = 0;
          foreach ($value['code'] as $v)
          {
              if($v[0] == $this->prize_code[3] && $v[1] == $this->prize_code[4])
              {
                  $codeNum += 1;
              }
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_bdw' && $key2 == 'official_q3ymbdw')
      {
          $codeNum = 0;
          if(in_array($this->prize_code[0],$value['code'][0]))
          {
              $codeNum += 1;
          }
          if(in_array($this->prize_code[1],$value['code'][0]))
          {
              $codeNum += 1;
          }
          if(in_array($this->prize_code[2],$value['code'][0]))
          {
              $codeNum += 1;
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_bdw' && $key2 == 'official_z3')
      {
          $codeNum = 0;
          if(in_array($this->prize_code[3],$value['code'][0]))
          {
              $codeNum += 1;
          }
          if(in_array($this->prize_code[1],$value['code'][0]))
          {
              $codeNum += 1;
          }
          if(in_array($this->prize_code[2],$value['code'][0]))
          {
              $codeNum += 1;
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_bdw' && $key2 == 'official_h3')
      {
          $codeNum = 0;
          if(in_array($this->prize_code[3],$value['code'][0]))
          {
              $codeNum += 1;
          }
          if(in_array($this->prize_code[4],$value['code'][0]))
          {
              $codeNum += 1;
          }
          if(in_array($this->prize_code[2],$value['code'][0]))
          {
              $codeNum += 1;
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }
      return $re;
  }
}
