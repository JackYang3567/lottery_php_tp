<?php
namespace app\prize\controller;

class Xync extends Lottery
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
      case 'dlq':
        $this->data_chat = $this->prize_code[5];
        $key_chat = 'wei';
        break;
      case 'dqq':
        $this->data_chat = $this->prize_code[6];
        $key_chat = 'wei';
        break;
      case 'dbq':
        $this->data_chat = $this->prize_code[7];
        $key_chat = 'wei';
        break;
      case 'zh':
        $this->data_chat = array_sum($this->prize_code);
        break;
    }
    if($this->rule()[$key_chat][$key2]($value)){
      $return_data['code'] = 1;
      $return_data['num'] = $this->prize_num * ($money * $odds);
    }

    if($key_chat == 'zh' && ($key2 == 'da' || $key2 == 'x') && $this->data_chat == 84){
      $return_data['code'] = 1;
      $return_data['num'] = $money;
    }

    return $return_data;
  }

  public function rule(){
    return [
      'zh' => [
        'dan' => function(){
          if($this->data_chat % 2){
            return true;
          }else{
            return false;
          }
        },
        's' => function(){
          if($this->data_chat % 2){
            return false;
          }else{
            return true;
          }
        },
        'da' => function(){
          if($this->data_chat > 84){
            return true;
          }else{
            return false;
          }
        },
        'x' => function(){
          if($this->data_chat < 84){
            return true;
          }else{
            return false;
          }
        },
        'wd' => function(){
          if(($this->data_chat % 10) > 4){
            return true;
          }else{
            return false;
          }
        },
        'wx' => function(){
          if(($this->data_chat % 10) < 5){
            return true;
          }else{
            return false;
          }
        },
        'l' => function(){
          if($this->prize_code[0] > $this->prize_code[7]){
            return true;
          }else{
            return false;
          }
        },
        'hu' => function(){
          if($this->prize_code[0] < $this->prize_code[7]){
            return true;
          }else{
            return false;
          }
        }
      ],
      'wei' => [
        'dan' => function(){
          if($this->data_chat % 2){
            return true;
          }else{
            return false;
          }
        },
        's' => function(){
          if($this->data_chat % 2){
            return false;
          }else{
            return true;
          }
        },
        'da' => function(){
          if($this->data_chat > 10){
            return true;
          }else{
            return false;
          }
        },
        'x' => function(){
          if($this->data_chat < 11){
            return true;
          }else{
            return false;
          }
        },
         'wd' => function(){
           if(($this->data_chat % 10) > 4){
             return true;
           }else{
             return false;
           }
         },
         'wx' => function(){
           if(($this->data_chat % 10) < 5){
             return true;
           }else{
             return false;
           }
         },
        'code_1' => function(){
          if($this->data_chat == 1){
            return true;
          }else{
            return false;
          }
        },
        'code_2' => function(){
          if($this->data_chat == 2){
            return true;
          }else{
            return false;
          }
        },
        'code_3' => function(){
          if($this->data_chat == 3){
            return true;
          }else{
            return false;
          }
        },
        'code_4' => function(){
          if($this->data_chat == 4){
            return true;
          }else{
            return false;
          }
        },
        'code_5' => function(){
          if($this->data_chat == 5){
            return true;
          }else{
            return false;
          }
        },
        'code_6' => function(){
          if($this->data_chat == 6){
            return true;
          }else{
            return false;
          }
        },
        'code_7' => function(){
          if($this->data_chat == 7){
            return true;
          }else{
            return false;
          }
        },
        'code_8' => function(){
          if($this->data_chat == 8){
            return true;
          }else{
            return false;
          }
        },
        'code_9' => function(){
          if($this->data_chat == 9){
            return true;
          }else{
            return false;
          }
        },
         'code_10' => function(){
           if($this->data_chat == 10){
             return true;
           }else{
             return false;
           }
         },
        'code_11' => function(){
          if($this->data_chat == 11){
            return true;
          }else{
            return false;
          }
        },
         'code_12' => function(){
           if($this->data_chat == 12){
             return true;
           }else{
             return false;
           }
         },
         'code_13' => function(){
           if($this->data_chat == 13){
             return true;
           }else{
             return false;
           }
         },
         'code_14' => function(){
           if($this->data_chat == 14){
             return true;
           }else{
             return false;
           }
         },
         'code_15' => function(){
           if($this->data_chat == 15){
             return true;
           }else{
             return false;
           }
         },
         'code_16' => function(){
           if($this->data_chat == 16){
             return true;
           }else{
             return false;
           }
         },
         'code_17' => function(){
           if($this->data_chat == 17){
             return true;
           }else{
             return false;
           }
         },
        'code_18' => function(){
          if($this->data_chat == 18){
            return true;
          }else{
            return false;
          }
        },
        'code_19' => function(){
          if($this->data_chat == 19){
            return true;
          }else{
            return false;
          }
        },
        'code_20' => function(){
          if($this->data_chat == 20){
            return true;
          }else{
            return false;
          }
        }
      ]
    ];
  }
}
