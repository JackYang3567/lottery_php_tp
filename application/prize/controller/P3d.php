<?php
namespace app\prize\controller;

class P3d extends Lottery
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
      case 'bw':
        $this->data_chat = $this->prize_code[0];
        $key_chat = 'wei';
        break;
      case 'sw':
        $this->data_chat = $this->prize_code[1];
        $key_chat = 'wei';
        break;
      case 'gw':
        $this->data_chat = $this->prize_code[2];
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
    return  [
      'wei' => [
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
        }
      ],
      'lm' => [
        'zx3' => function($value){
          if(in_array($this->prize_code[0],$value['code'][0]) && (($this->prize_code[0] == $this->prize_code[1] && in_array($this->prize_code[2],$value['code'][0])) || ($this->prize_code[1] == $this->prize_code[2] && in_array($this->prize_code[2],$value['code'][0])) || ($this->prize_code[0] == $this->prize_code[2] && in_array($this->prize_code[1],$value['code'][0])))){
            return true;
          }else{
            return false;
          }
        },
        'zx6' => function($value){
          $num = 0;
          foreach ($value['code'][0] as $value) {
            if(in_array($value,$this->prize_code)){
              $num++;
            }
          }
          if($num > 2){
            return true;
          }else{
            return false;
          }
        },
        'zhix3' => function($value){
          foreach ($value['code'] as $key1 => $value1) {
            $num = 0;
            foreach ($value1 as $value2) {
              if($value2 == $this->prize_code[$key1]){
                $num++;
                break;
              }
            }
            if($num < 1){
              return false;
            }
          }
          return true;
        }
      ],
      'zh' => [
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
        'da' => function($value){
          if($this->data_chat > 13){
            return true;
          }else{
            return false;
          }
        },
        'x' => function($value){
          if($this->data_chat < 14){
            return true;
          }else{
            return false;
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
        'code_12' => function($value){
          if($this->data_chat == 12){
            return true;
          }else{
            return false;
          }
        },
        'code_13' => function($value){
          if($this->data_chat == 13){
            return true;
          }else{
            return false;
          }
        },
        'code_14' => function($value){
          if($this->data_chat == 14){
            return true;
          }else{
            return false;
          }
        },
        'code_15' => function($value){
          if($this->data_chat == 15){
            return true;
          }else{
            return false;
          }
        },
        'code_16' => function($value){
          if($this->data_chat == 16){
            return true;
          }else{
            return false;
          }
        },
        'code_17' => function($value){
          if($this->data_chat == 17){
            return true;
          }else{
            return false;
          }
        },
        'code_18' => function($value){
          if($this->data_chat == 18){
            return true;
          }else{
            return false;
          }
        },
        'code_19' => function($value){
          if($this->data_chat == 19){
            return true;
          }else{
            return false;
          }
        },
        'code_20' => function($value){
          if($this->data_chat == 20){
            return true;
          }else{
            return false;
          }
        },
        'code_21' => function($value){
          if($this->data_chat == 21){
            return true;
          }else{
            return false;
          }
        },
        'code_22' => function($value){
          if($this->data_chat == 22){
            return true;
          }else{
            return false;
          }
        },
        'code_23' => function($value){
          if($this->data_chat == 23){
            return true;
          }else{
            return false;
          }
        },
        'code_24' => function($value){
          if($this->data_chat == 24){
            return true;
          }else{
            return false;
          }
        },
        'code_25' => function($value){
          if($this->data_chat == 25){
            return true;
          }else{
            return false;
          }
        },
        'code_26' => function($value){
          if($this->data_chat == 26){
            return true;
          }else{
            return false;
          }
        },
        'code_27' => function($value){
          if($this->data_chat == 27){
            return true;
          }else{
            return false;
          }
        }
      ]
    ];
  }
}
