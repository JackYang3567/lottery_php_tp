<?php
namespace app\prize\controller;

class PK10 extends Lottery
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
      case 'gyhz':
      case 'official_gyh':
        $this->data_chat = $this->prize_code[0] + $this->prize_code[1];
        break;
      case 'gj':
        $this->data_chat = [ $this->prize_code[0],$this->prize_code[9] ];
        $key_chat = 'pm_all';
        break;
      case 'yj':
        $this->data_chat = [ $this->prize_code[1],$this->prize_code[8] ];
        $key_chat = 'pm_all';
        break;
      case 'd3m':
        $this->data_chat = [ $this->prize_code[2],$this->prize_code[7] ];
        $key_chat = 'pm_all';
        break;
      case 'd4m':
        $this->data_chat = [ $this->prize_code[3],$this->prize_code[6] ];
        $key_chat = 'pm_all';
        break;
      case 'd5m':
        $this->data_chat = [ $this->prize_code[4],$this->prize_code[5] ];
        $key_chat = 'pm_all';
        break;
      case 'd6m':
        $this->data_chat = [ $this->prize_code[5],$this->prize_code[4] ];
        $key_chat = 'pm_all';
        break;
      case 'd7m':
        $this->data_chat = [ $this->prize_code[6],$this->prize_code[3] ];
        $key_chat = 'pm_all';
        break;
      case 'd8m':
        $this->data_chat = [ $this->prize_code[7],$this->prize_code[2] ];
        $key_chat = 'pm_all';
        break;
      case 'd9m':
        $this->data_chat = [ $this->prize_code[8],$this->prize_code[1] ];
        $key_chat = 'pm_all';
        break;
      case 'd10m':
        $this->data_chat = [ $this->prize_code[9],$this->prize_code[0] ];
        $key_chat = 'pm_all';
        break;
    }

    $arr = ['da','x','dan','s'];
    if($key1 == 'gyhz' && in_array($key2,$arr) && $this->data_chat == 11)
    {
        $return_data['code'] = 1;
        $return_data['num'] = $this->prize_num * ($money * 1);
    }
    else
    {
        if($this->rule()[$key_chat][$key2]($value)){
            $return_data['code'] = 1;
            $return_data['num'] = $this->prize_num * ($money * $odds);
        }
    }
    return $return_data;
  }

  public function rule(){
    return [
      'official_dwd' => [
        'official_dwd_fs' => function($bet){
          $prize_num = 0;
          foreach ($this->prize_code as $key => $value) {
            if(in_array($value,$bet['code'][$key])){
              $prize_num ++;
              if($prize_num > 1){
                $this->prize_num ++;
              }
            }
          }
          if($prize_num){
            return true;
          }else{
            return false;
          }
        }
      ],
      'official_cqs' => [
        'official_cqs_fs' => function($bet){
          $prize_num = 0;
          for($i=0;$i<3;$i++){
            if(in_array($this->prize_code[$i],$bet['code'][$i])){
              $prize_num ++;
            }
          }
          if($prize_num == 3){
            return true;
          }else{
            return false;
          }
        },
        'official_cqs_ds' => function($bet){
          // nothing
        }
      ],
      'official_cqe' => [
        'official_cqe_fs' => function($bet){
          $prize_num = 0;
          for($i=0;$i<2;$i++){
            if(in_array($this->prize_code[$i],$bet['code'][$i])){
              $prize_num ++;
            }
          }
          if($prize_num == 2){
            return true;
          }else{
            return false;
          }
        },
        'official_cqe_ds' => function($bet){
          // nothing
        }
      ],
      'official_cgj' => [
        'official_cgj_fs' => function($bet){
          if(in_array($this->prize_code[0],$bet['code'][0])){
            return true;
          }else{
            return false;
          }
        }
      ],
      'official_gyh' => [
        'official_gyh_dxds' => function($bet){
          $code_type = [];
          if($this->data_chat > 5){
            $code_type[] = 0;
          }
          else if ($this->data_chat < 6) {
            $code_type[] = 1;
          }
          
          if ($this->data_chat % 2) {
            $code_type[] = 2;
          }
          else if ($this->data_chat % 2 == 0) {
            $code_type[] = 3;
          }
          $prize_num = 0;
          foreach ($bet['code'][0] as $value) {
            if(in_array($value,$code_type)){
              $prize_num ++;
              if($prize_num > 1){
                $this->prize_num ++;
              }
            }
          }
          if($prize_num){
            return true;
          }else{
            return false;
          }
        },
        'official_gyh_h' => function($bet){
          if(in_array($this->data_chat,$bet['code'][0])){
            return true;
          }else{
            return false;
          }
        }
      ],
      'official_lhd' => [
        'official_lhd_gj' => function($bet){
          if($this->prize_code[0] > $this->prize_code[9]){
            $chat_data = 0;
          }else{
            $chat_data = 1;
          }
          if(in_array($chat_data,$bet['code'][0])){
            return true;
          }else{
            return false;
          }
        },
        'official_lhd_yj' => function($bet){
          if($this->prize_code[1] > $this->prize_code[8]){
            $chat_data = 0;
          }else{
            $chat_data = 1;
          }
          if(in_array($chat_data,$bet['code'][0])){
            return true;
          }else{
            return false;
          }
        },
        'official_lhd_jj' => function($bet){
          if($this->prize_code[2] > $this->prize_code[7]){
            $chat_data = 0;
          }else{
            $chat_data = 1;
          }
          if(in_array($chat_data,$bet['code'][0])){
            return true;
          }else{
            return false;
          }
        }
      ],
      'gyhz' => [
        'da' => function(){
          if($this->data_chat > 11){
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
        'ds' => function(){
          if($this->data_chat > 11 && $this->data_chat % 2 == 0){
            return true;
          }else{
            return false;
          }
        },
        'dd' => function(){
          if($this->data_chat > 11 && $this->data_chat % 2){
            return true;
          }else{
            return false;
          }
        },
        'xs' => function(){
          if($this->data_chat < 11 && $this->data_chat % 2 == 0){
            return true;
          }else{
            return false;
          }
        },
        'xd' => function(){
          if($this->data_chat < 11 && $this->data_chat % 2){
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
        }
      ],
      'pm_all' => [
        'da' => function(){
          if($this->data_chat[0] > 5){
            return true;
          }else{
            return false;
          }
        },
        'x' => function(){
          if($this->data_chat[0] < 6){
            return true;
          }else{
            return false;
          }
        },
        'dan' => function(){
          if($this->data_chat[0] % 2){
            return true;
          }else{
            return false;
          }
        },
        's' => function(){
          if($this->data_chat[0] % 2){
            return false;
          }else{
            return true;
          }
        },
        'l' => function(){
          if($this->data_chat[0] > $this->data_chat[1]){
            return true;
          }else{
            return false;
          }
        },
        'hu' => function(){
          if($this->data_chat[0] > $this->data_chat[1]){
            return false;
          }else{
            return true;
          }
        },
        'code_1' => function(){
          if($this->data_chat[0] == 1){
            return true;
          }else{
            return false;
          }
        },
        'code_2' => function(){
          if($this->data_chat[0] == 2){
            return true;
          }else{
            return false;
          }
        },
        'code_3' => function(){
          if($this->data_chat[0] == 3){
            return true;
          }else{
            return false;
          }
        },
        'code_4' => function(){
          if($this->data_chat[0] == 4){
            return true;
          }else{
            return false;
          }
        },
        'code_5' => function(){
          if($this->data_chat[0] == 5){
            return true;
          }else{
            return false;
          }
        },
        'code_6' => function(){
          if($this->data_chat[0] == 6){
            return true;
          }else{
            return false;
          }
        },
        'code_7' => function(){
          if($this->data_chat[0] == 7){
            return true;
          }else{
            return false;
          }
        },
        'code_8' => function(){
          if($this->data_chat[0] == 8){
            return true;
          }else{
            return false;
          }
        },
        'code_9' => function(){
          if($this->data_chat[0] == 9){
            return true;
          }else{
            return false;
          }
        },
        'code_10' => function(){
          if($this->data_chat[0] == 10){
            return true;
          }else{
            return false;
          }
        }
      ]
    ];
  }
}
