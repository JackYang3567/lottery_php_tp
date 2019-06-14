<?php
namespace app\prize\controller;
use app\home\controller\LotteryL;
use app\home\controller\Lottery28;
class Lhc extends Lottery
{
  public $data_chat;
  public $prize_num;
  public $prize_code_type;
  //六合彩平特肖专用 本命年将会减少一定赔率
  public $prize_num_bm = 0;
  public $prize_code_bm = '';

  public function __initialize(){
    $this->prize_code_type = [];
    foreach ($this->prize_code as $value) {
      $this->prize_code_type[] = LotteryL::codeType($value,substr($this->post_data['expect'],0,4),$this->prize_code_time);
    }
  }

  public function prize(){
    $this->actionPrize();
  }

  /**
   *  将数组中的字符串值全部转换成数值类型
   * @param array $data
   * @return array
   */
  public function arrayStrInt($data){
    $re = [];
    foreach($data as $vo){
      $re[] = (int)$vo;
    }
    return $re;
  }
  /** 如果六合彩做官方玩法时，这里获得赔率时，要在判断下 **/
  public function action($key1,$key2,$value,$money,$disc,$odds_num){
    $key_chat = $key2;
    $this->prize_num = 1;
    $return_data = [
      'code' => 0,
      'num' => 0
    ];
    switch ($key1) {
      case 'tm':
        $this->data_chat = $this->prize_code[6];
        break;
      case 'dp':
      case 'pm':
        $this->data_chat = [
          $this->prize_code[0],
          $this->prize_code[1],
          $this->prize_code[2],
          $this->prize_code[3],
          $this->prize_code[4],
          $this->prize_code[5]
        ];
        break;
      case 'ptx':
        //获取本命年的属性
        $bit = lotteryL::codeType(1,date("Y"));
        $this->prize_code_bm = $bit['zodiac'][0];
      case 'zx':
        $this->data_chat = [
          $this->prize_code_type[0]['zodiac'][0],
          $this->prize_code_type[1]['zodiac'][0],
          $this->prize_code_type[2]['zodiac'][0],
          $this->prize_code_type[3]['zodiac'][0],
          $this->prize_code_type[4]['zodiac'][0],
          $this->prize_code_type[5]['zodiac'][0],
          $this->prize_code_type[6]['zodiac'][0]
        ];
        break;
      case 'tmsx':
      case 'dxzt':
        $this->data_chat = $this->prize_code_type[6]['zodiac'][0];
        break;
      case 'wx':
        $this->data_chat = $this->prize_code_type[6]['five'][0];
        break;
      case 'zh':
        $this->data_chat = array_sum($this->prize_code);
        break;
      case 'hs':
        $this->data_chat = floor($this->prize_code[6] / 10) + ($this->prize_code[6] % 10);
        break;
      case 'ptbz':
        $key_chat = 'bz';
        switch ($key2) {
          case 'wbz':
            $this->data_chat = 5;
            break;
          case 'lbz':
            $this->data_chat = 6;
            break;
          case 'qbz':
            $this->data_chat = 7;
            break;
          case 'bbz':
            $this->data_chat = 8;
            break;
          case 'jbz':
            $this->data_chat = 9;
            break;
          case 'sbz':
            $this->data_chat = 10;
            break;
          case 's1bz':
            $this->data_chat = 11;
            break;
          case 's2bz':
            $this->data_chat = 12;
            break;
          case 's3bz':
            $this->data_chat = 13;
            break;
          case 's4bz':
            $this->data_chat = 14;
            break;
          case 's5bz':
            $this->data_chat = 15;
            break;
        }
        break;
    }
    //初始化本命中奖注数
    $this->prize_num_bm = 0;
    if($this->rule()[$key1][$key_chat]($value)){
      $return_data['code'] = 1;
      $return_data['num'] = 0;
      if($this->prize_num > 0){
        $odds = ($this->post_data['type'] == 21 ?  $this->lottery_config['basic_config'][$key1]['items'][$key2]['odds'][$disc] : $this->lottery_config['basic_config'][$key1]['items'][$key2]['odds']);
        $return_data['num'] += $this->prize_num * ($money * $odds);
      }
      //特别玩法->本命属性算法
      if($this->prize_num_bm > 0){
        $odds = ($this->post_data['type'] == 21 ?  $this->lottery_config['basic_config'][$key1]['items'][$key2]['oddsb'][$disc] : $this->lottery_config['basic_config'][$key1]['items'][$key2]['oddsb']);
        $return_data['num'] +=$this->prize_num_bm * ($money * $odds);
      }
    }
    return $return_data;
  }

  public function rule(){
    return [
    'bbb' => [
        'rdd' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'red' && $this->prize_code[6] > 24 && $this->prize_code[6] != 49 && $this->prize_code[6] % 2){
            return true;
          }else{
            return false;
          }
        },
        'rds' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'red' && $this->prize_code[6] > 24 && $this->prize_code[6] != 49 && $this->prize_code[6] % 2 == 0){
            return true;
          }else{
            return false;
          }
        },
        'rxdan' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'red' && $this->prize_code[6] < 25 && $this->prize_code[6] != 49 && $this->prize_code[6] % 2){
            return true;
          }else{
            return false;
          }
        },
        'rxs' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'red' && $this->prize_code[6] < 25 && $this->prize_code[6] != 49 && $this->prize_code[6] % 2 == 0){
            return true;
          }else{
            return false;
          }
        },
        'bdd' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'blue' && $this->prize_code[6] > 24 && $this->prize_code[6] != 49 && $this->prize_code[6] % 2){
            return true;
          }else{
            return false;
          }
        },
        'bds' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'blue' && $this->prize_code[6] > 24 && $this->prize_code[6] != 49 && $this->prize_code[6] % 2 == 0){
            return true;
          }else{
            return false;
          }
        },
        'bxd' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'blue' && $this->prize_code[6] < 25 && $this->prize_code[6] != 49 && $this->prize_code[6] % 2){
            return true;
          }else{
            return false;
          }
        },
        'bxs' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'blue' && $this->prize_code[6] < 25 && $this->prize_code[6] != 49 && $this->prize_code[6] % 2 == 0){
            return true;
          }else{
            return false;
          }
        },
        'gdd' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'green' && $this->prize_code[6] > 24 && $this->prize_code[6] != 49 && $this->prize_code[6] % 2){
            return true;
          }else{
            return false;
          }
        },
        'gds' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'green' && $this->prize_code[6] > 24 && $this->prize_code[6] != 49 && $this->prize_code[6] % 2 == 0){
            return true;
          }else{
            return false;
          }
        },
        'gxdan' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'green' && $this->prize_code[6] < 25 && $this->prize_code[6] != 49 && $this->prize_code[6] % 2){
            return true;
          }else{
            return false;
          }
        },
        'gxs' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'green' && $this->prize_code[6] < 25 && $this->prize_code[6] != 49 && $this->prize_code[6] % 2 == 0){
            return true;
          }else{
            return false;
          }
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
          if($this->data_chat > 174){
            return true;
          }else{
            return false;
          }
        },
        'x' => function($value){
          if($this->data_chat < 175){
            return true;
          }else{
            return false;
          }
        },
      ],
      'dp' => [
        'code_1' => function($value){
          if(in_array(1,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_2' => function($value){
          if(in_array(2,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_3' => function($value){
          if(in_array(3,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_4' => function($value){
          if(in_array(4,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_5' => function($value){
          if(in_array(5,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_6' => function($value){
          if(in_array(6,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_7' => function($value){
          if(in_array(7,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_8' => function($value){
          if(in_array(8,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_9' => function($value){
          if(in_array(9,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_10' => function($value){
          if(in_array(10,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_11' => function($value){
          if(in_array(11,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_12' => function($value){
          if(in_array(12,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_13' => function($value){
          if(in_array(13,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_14' => function($value){
          if(in_array(14,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_15' => function($value){
          if(in_array(15,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
         'code_16' => function($value){
           if(in_array(16,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_17' => function($value){
           if(in_array(17,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_18' => function($value){
           if(in_array(18,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_19' => function($value){
           if(in_array(19,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_20' => function($value){
           if(in_array(20,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_21' => function($value){
           if(in_array(21,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_22' => function($value){
           if(in_array(22,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_23' => function($value){
           if(in_array(23,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_24' => function($value){
           if(in_array(24,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_25' => function($value){
           if(in_array(25,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_26' => function($value){
           if(in_array(26,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_27' => function($value){
           if(in_array(27,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_28' => function($value){
           if(in_array(28,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_29' => function($value){
           if(in_array(29,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_30' => function($value){
           if(in_array(30,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
        'code_31' => function($value){
          if(in_array(31,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_32' => function($value){
          if(in_array(32,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_33' => function($value){
          if(in_array(33,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_34' => function($value){
          if(in_array(34,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_35' => function($value){
          if(in_array(35,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_36' => function($value){
          if(in_array(36,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_37' => function($value){
          if(in_array(37,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_38' => function($value){
          if(in_array(38,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_39' => function($value){
          if(in_array(39,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_40' => function($value){
          if(in_array(40,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_41' => function($value){
          if(in_array(41,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_42' => function($value){
          if(in_array(42,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_43' => function($value){
          if(in_array(43,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_44' => function($value){
          if(in_array(44,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_45' => function($value){
          if(in_array(45,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
         'code_46' => function($value){
           if(in_array(46,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
         'code_47' => function($value){
           if(in_array(47,$this->data_chat)){
             return true;
           }else{
             return false;
           }
         },
        'code_48' => function($value){
          if(in_array(48,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        },
        'code_49' => function($value){
          if(in_array(49,$this->data_chat)){
            return true;
          }else{
            return false;
          }
        }
      ],
      'tm' => [
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
         },
         'code_28' => function($value){
           if($this->data_chat == 28){
             return true;
           }else{
             return false;
           }
         },
         'code_29' => function($value){
           if($this->data_chat == 29){
             return true;
           }else{
             return false;
           }
         },
         'code_30' => function($value){
           if($this->data_chat == 30){
             return true;
           }else{
             return false;
           }
         },
        'code_31' => function($value){
          if($this->data_chat == 31){
            return true;
          }else{
            return false;
          }
        },
        'code_32' => function($value){
          if($this->data_chat == 32){
            return true;
          }else{
            return false;
          }
        },
        'code_33' => function($value){
          if($this->data_chat == 33){
            return true;
          }else{
            return false;
          }
        },
        'code_34' => function($value){
          if($this->data_chat == 34){
            return true;
          }else{
            return false;
          }
        },
        'code_35' => function($value){
          if($this->data_chat == 35){
            return true;
          }else{
            return false;
          }
        },
        'code_36' => function($value){
          if($this->data_chat == 36){
            return true;
          }else{
            return false;
          }
        },
        'code_37' => function($value){
          if($this->data_chat == 37){
            return true;
          }else{
            return false;
          }
        },
        'code_38' => function($value){
          if($this->data_chat == 38){
            return true;
          }else{
            return false;
          }
        },
        'code_39' => function($value){
          if($this->data_chat == 39){
            return true;
          }else{
            return false;
          }
        },
        'code_40' => function($value){
          if($this->data_chat == 40){
            return true;
          }else{
            return false;
          }
        },
        'code_41' => function($value){
          if($this->data_chat == 41){
            return true;
          }else{
            return false;
          }
        },
        'code_42' => function($value){
          if($this->data_chat == 42){
            return true;
          }else{
            return false;
          }
        },
        'code_43' => function($value){
          if($this->data_chat == 43){
            return true;
          }else{
            return false;
          }
        },
        'code_44' => function($value){
          if($this->data_chat == 44){
            return true;
          }else{
            return false;
          }
        },
        'code_45' => function($value){
          if($this->data_chat == 45){
            return true;
          }else{
            return false;
          }
        },
         'code_46' => function($value){
           if($this->data_chat == 46){
             return true;
           }else{
             return false;
           }
         },
         'code_47' => function($value){
           if($this->data_chat == 47){
             return true;
           }else{
             return false;
           }
         },
        'code_48' => function($value){
          if($this->data_chat == 48){
            return true;
          }else{
            return false;
          }
        },
        'code_49' => function($value){
          if($this->data_chat == 49){
            return true;
          }else{
            return false;
          }
        }
      ],
      'tmsx' => [
        'shu' => function($value){
          if($this->data_chat == '鼠'){
            return true;
          }else{
            return false;
          }
        },
        'niu' => function($value){
          if($this->data_chat == '牛'){
            return true;
          }else{
            return false;
          }
        },
        'hu' => function($value){
          if($this->data_chat == '虎'){
            return true;
          }else{
            return false;
          }
        },
        'tu' => function($value){
          if($this->data_chat == '兔'){
            return true;
          }else{
            return false;
          }
        },
        'long' => function($value){
          if($this->data_chat == '龙'){
            return true;
          }else{
            return false;
          }
        },
        'she' => function($value){
          if($this->data_chat == '蛇'){
            return true;
          }else{
            return false;
          }
        },
        'ma' => function($value){
          if($this->data_chat == '马'){
            return true;
          }else{
            return false;
          }
        },
        'yang' => function($value){
          if($this->data_chat == '羊'){
            return true;
          }else{
            return false;
          }
        },
        'hou' => function($value){
          if($this->data_chat == '猴'){
            return true;
          }else{
            return false;
          }
        },
        'ji' => function($value){
          if($this->data_chat == '鸡'){
            return true;
          }else{
            return false;
          }
        },
        'gou' => function($value){
          if($this->data_chat == '狗'){
            return true;
          }else{
            return false;
          }
        },
        'zhu' => function($value){
          if($this->data_chat == '猪'){
            return true;
          }else{
            return false;
          }
        },
      ],
      'dxds' => [
        'da' => function($value){
          if($this->prize_code[6] > 24 && $this->prize_code[6] != 49){
            return true;
          }else{
            return false;
          }
        },
        'x' => function($value){
          if($this->prize_code[6] < 25){
            return true;
          }else{
            return false;
          }
        },
        'dan' => function($value){
          if($this->prize_code[6] % 2 && $this->prize_code[6] != 49){
            return true;
          }else{
            return false;
          }
        },
        's' => function($value){
          if($this->prize_code[6] % 2 || $this->prize_code[6] == 49){
            return false;
          }else{
            return true;
          }
        }
      ],
      'tswf' => [
        'jx' => function($value){
          if($this->prize_code_type[6]['animal'][0] == '家禽'){
            return true;
          }else{
            return false;
          }
        },
        'yx' => function($value){
          if($this->prize_code_type[6]['animal'][0] == '野兽'){
            return true;
          }else{
            return false;
          }
        },
        'tx' => function($value){
          if($this->prize_code_type[6]['tdsx'][0] == '天肖'){
            return true;
          }else{
            return false;
          }
        },
        'dx' => function($value){
          if($this->prize_code_type[6]['tdsx'][0] == '地肖'){
            return true;
          }else{
            return false;
          }
        }
      ],
      'ts' => [
        'lt' => function($value){
          if($this->prize_code[6] < 10){
            return true;
          }else{
            return false;
          }
        },
        'yt' => function($value){
          if($this->prize_code[6] > 9 && $this->prize_code[6] < 20){
            return true;
          }else{
            return false;
          }
        },
        'et' => function($value){
          if($this->prize_code[6] > 19 && $this->prize_code[6] < 30){
            return true;
          }else{
            return false;
          }
        },
        'sant' => function($value){
          if($this->prize_code[6] > 29 && $this->prize_code[6] < 40){
            return true;
          }else{
            return false;
          }
        },
        'sit' => function($value){
          if($this->prize_code[6] > 40){
            return true;
          }else{
            return false;
          }
        },
      ],
      'ws' => [
        'lw' => function($value){
          if($this->prize_code[6] % 10 == 0){
            return true;
          }else{
            return false;
          }
        },
        'yw' => function($value){
          if($this->prize_code[6] % 10 == 1){
            return true;
          }else{
            return false;
          }
        },
        'ew' => function($value){
          if($this->prize_code[6] % 10 == 2){
            return true;
          }else{
            return false;
          }
        },
        'sanw' => function($value){
          if($this->prize_code[6] % 10 == 3){
            return true;
          }else{
            return false;
          }
        },
        'siw' => function($value){
          if($this->prize_code[6] % 10 == 4){
            return true;
          }else{
            return false;
          }
        },
        'ww' => function($value){
          if($this->prize_code[6] % 10 == 5){
            return true;
          }else{
            return false;
          }
        },
        'liuw' => function($value){
          if($this->prize_code[6] % 10 == 6){
            return true;
          }else{
            return false;
          }
        },
        'qw' => function($value){
          if($this->prize_code[6] % 10 == 7){
            return true;
          }else{
            return false;
          }
        },
        'bw' => function($value){
          if($this->prize_code[6] % 10 == 8){
            return true;
          }else{
            return false;
          }
        },
        'jw' => function($value){
          if($this->prize_code[6] % 10 == 9){
            return true;
          }else{
            return false;
          }
        },
        'da' => function($value){
          if($this->prize_code[6] % 10 > 4){
            return true;
          }else{
            return false;
          }
        },
        'x' => function($value){
          if($this->prize_code[6] % 10 < 5){
            return true;
          }else{
            return false;
          }
        },
        'dan' => function($value){
          if(($this->prize_code[6] % 10) % 2){
            return true;
          }else{
            return false;
          }
        },
        's' => function($value){
          if(($this->prize_code[6] % 10) % 2){
            return false;
          }else{
            return true;
          }
        },
      ],
      'wx' => [
        'jin' => function($value){
          if($this->data_chat == '金'){
            return true;
          }else{
            return false;
          }
        },
        'mu' => function($value){
          if($this->data_chat == '木'){
            return true;
          }else{
            return false;
          }
        },
        'shui' => function($value){
          if($this->data_chat == '水'){
            return true;
          }else{
            return false;
          }
        },
        'huo' => function($value){
          if($this->data_chat == '火'){
            return true;
          }else{
            return false;
          }
        },
        'tu' => function($value){
          if($this->data_chat == '土'){
            return true;
          }else{
            return false;
          }
        }
      ],
      'hs' => [
        'dan' => function($value){
          if($this->data_chat % 2 && $this->prize_code_type[6] != 49){
            return true;
          }else{
            return false;
          }
        },
        's' => function($value){
          if($this->data_chat % 2 && $this->prize_code_type[6] != 49){
            return false;
          }else{
            return true;
          }
        },
        'da' => function($value){
          if($this->data_chat > 6 && $this->prize_code_type[6] != 49){
            return true;
          }else{
            return false;
          }
        },
        'x' => function($value){
          if($this->data_chat < 7 && $this->prize_code_type[6] != 49){
            return true;
          }else{
            return false;
          }
        },
      ],
      'pm' => [
        'p2z2' => function($value){
          //新算法
          $this->prize_num = 0;
          //选择的n个组合
          $zuhe = Lottery28::strand($value['code'],2);
          //循环组合
          foreach($zuhe as $vo){
            $num = 0;
            foreach($vo as $vo1){
              if(in_array($vo1,$this->data_chat)){
                $num++;
              }
            }
            //如果两个都出现过则派奖+1
            if($num == 2){
              $this->prize_num++;
            }
          }
          if($this->prize_num > 0){
            return true;
          }else{
            return false;
          }

          //旧算法
          // foreach ($value['code'] as $value1) {
          //   $num = 0;
          //   foreach ($this->data_chat as $value2) {
          //     if($value1 == $value2){
          //       $num++;
          //       if($num > 1){
          //         return true;
          //       }
          //     }
          //   }
          // }
          // return false;

        },
        'p3z3' => function($value){
          //新算法
          $this->prize_num = 0;
          //选择的n个组合
          $zuhe = Lottery28::strand($value['code'],3);
          //循环组合
          foreach($zuhe as $vo){
            $num = 0;
            foreach($vo as $vo1){
              if(in_array((int)$vo1,$this->data_chat)){
                $num++;
              }
            }
            //如果都出现过则派奖+1
            if($num == 3){
              $this->prize_num++;
            }
          }
          if($this->prize_num > 0){
            return true;
          }else{
            return false;
          }
        },
        'p3z2' => function($value){
          //新算法
          $this->prize_num = 0;
          //选择的n个组合
          $zuhe = Lottery28::strand($value['code'],3);
          //循环组合
          foreach($zuhe as $vo){
            $num = 0;
            foreach($vo as $vo1){
              if(in_array($vo1,$this->data_chat)){
                $num++;
                if($num == 2){
                  break;
                }
              }
            }
            //如果出现过两个则派奖+1
            if($num == 2){
              $this->prize_num++;
            }
          }
          if($this->prize_num > 0){
            return true;
          }else{
            return false;
          }
        }
      ],
      'dxzt' => [
        'sxzt' => function($value){
          // print_r($value);
          //新算法
          $this->prize_num = 0;
          //选择的n个组合
          $zuhe = Lottery28::strand($value['code'],4);
          // print_r($zuhe);die();
          //循环组合
          foreach($zuhe as $vo){
            if(in_array($this->data_chat,$vo)){
              $this->prize_num++;
            }
          }
          if($this->prize_num > 0){
            return true;
          }else{
            return false;
          }
        },
        'wxzt' => function($value){
          //新算法
          $this->prize_num = 0;
          //选择的n个组合
          $zuhe = Lottery28::strand($value['code'],5);
          //循环组合
          foreach($zuhe as $vo){
            if(in_array($this->data_chat,$vo)){
              $this->prize_num++;
            }
          }
          if($this->prize_num > 0){
            return true;
          }else{
            return false;
          }
        },
        'lxzt' => function($value){
          //新算法
          $this->prize_num = 0;
          //选择的n个组合
          $zuhe = Lottery28::strand($value['code'],6);
          //循环组合
          foreach($zuhe as $vo){
            if(in_array($this->data_chat,$vo)){
              $this->prize_num++;
            }
          }
          if($this->prize_num > 0){
            return true;
          }else{
            return false;
          }
        },
      ],
      'ptx' => [
        'pt1x' => function($value){
          //新算法
          $this->prize_num = 0;
          foreach ($value['code'] as $vo1) {
            if(in_array($vo1,$this->data_chat)){
              if($this->prize_code_bm == $vo1){
                $this->prize_num_bm++;
              }else{
                $this->prize_num++;
              }
              //  if()
            }
          }
          // print_r($this->prize_num_bm);die();
          if($this->prize_num > 0 || $this->prize_num_bm > 0){
            return true;
          }else{
            return false;
          }

          //旧算法
          // $num = 0;
          // foreach ($value['code'] as $value1) {
          //   if(in_array($value1,$this->data_chat)){
          //     $num++;
          //     if($num > 0){
          //       return true;
          //     }
          //   }
          // }
          // return false;

        },
        'pt2x' => function($value){
          //新算法
          $this->prize_num = 0;
          //选择的生肖进行2个组合
          $zuhe = Lottery28::strand($value['code'],2);
          //循环组合
          foreach($zuhe as $vo){
            $num = 0;
            foreach($vo as $vo1){
              if(in_array($vo1,$this->data_chat)){
                $num++;
              }
            }
            //如果两个都出现过则派奖+1
            if($num == 2){
              if(in_array($this->prize_code_bm,$vo)){
                $this->prize_num_bm++;
              }else{
                $this->prize_num++;
              }
            }
          }
          if($this->prize_num > 0 || $this->prize_num_bm > 0){
            return true;
          }else{
            return false;
          }
          //旧算法
          // $num = 0;
          // foreach ($value['code'] as $value1) {
          //   if(in_array($value1,$this->data_chat)){
          //     $num++;
          //     if($num > 1){
          //       return true;
          //     }
          //   }
          // }
          // return false;
        },
        'pt3x' => function($value){
          //新算法
          $this->prize_num = 0;
          //选择的生肖进行N个组合
          $zuhe = Lottery28::strand($value['code'],3);
          //循环组合
          foreach($zuhe as $vo){
            $num = 0;
            foreach($vo as $vo1){
              if(in_array($vo1,$this->data_chat)){
                $num++;
              }
            }
            //如果都出现过则派奖+1
            if($num == 3){
              if(in_array($this->prize_code_bm,$vo)){
                $this->prize_num_bm++;
              }else{
                $this->prize_num++;
              }
            }
          }
          if($this->prize_num > 0 || $this->prize_num_bm > 0){
            return true;
          }else{
            return false;
          }
        },
        'pt4x' => function($value){
          //新算法
          $this->prize_num = 0;
          //选择的生肖进行N个组合
          $zuhe = Lottery28::strand($value['code'],4);
          //循环组合
          foreach($zuhe as $vo){
            $num = 0;
            foreach($vo as $vo1){
              if(in_array($vo1,$this->data_chat)){
                $num++;
              }
            }
            //如果都出现过则派奖+1
            if($num == 4){
              if(in_array($this->prize_code_bm,$vo)){
                $this->prize_num_bm++;
              }else{
                $this->prize_num++;
              }
            }
          }
          if($this->prize_num > 0 || $this->prize_num_bm > 0){
            return true;
          }else{
            return false;
          }
        },
        'pt5x' => function($value){
          //新算法
          $this->prize_num = 0;
          //选择的生肖进行N个组合
          $zuhe = Lottery28::strand($value['code'],5);
          //循环组合
          foreach($zuhe as $vo){
            $num = 0;
            foreach($vo as $vo1){
              if(in_array($vo1,$this->data_chat)){
                $num++;
              }
            }
            //如果都出现过则派奖+1
            if($num == 5){
              if(in_array($this->prize_code_bm,$vo)){
                $this->prize_num_bm++;
              }else{
                $this->prize_num++;
              }
            }
          }
          if($this->prize_num > 0 || $this->prize_num_bm > 0){
            return true;
          }else{
            return false;
          }
        }
      ],
      'ptw' => [
        'pt1w' => function($value){
          $prize = 0;
          foreach ($value['code'] as $value1) {
            $num = 0;
            foreach ($this->prize_code as $value2) {
              if($value1 == ($value2 % 10)){
                $num++;
                break;
              }
            }
            if($num > 0){
              $prize++;
              if($prize > 1){
                $this->prize_num++;
              }
            }
          }
          return $prize ? true : false;
        },
        'pt2w' => function($value){
          //新算法
          $this->prize_num = 0;
          //数组进行N个组合
          $zuhe = Lottery28::strand($value['code'],2);
          //尾数
          $weishu = [];
          foreach($this->prize_code as $item){
            $weishu[] = $item % 10;
          }
          //循环组合
          foreach($zuhe as $vo){
            $num = 0;
            foreach($vo as $vo1){
              if(in_array($vo1,$weishu)){
                $num++;
              }
            }
            //如果都出现过则派奖+1
            if($num == 2){
              $this->prize_num++;
            }
          }
          if($this->prize_num > 0){
            return true;
          }else{
            return false;
          }

          //旧算法
          // $num_sum = 0;
          // foreach ($value['code'] as $value1) {
          //   $num = 0;
          //   foreach ($this->prize_code as $value2) {
          //     if($value1 == ($value2 % 10)){
          //       $num++;
          //       if($num > 0){
          //         $num_sum++;
          //         break;
          //       }
          //     }
          //   }
          // }
          // if($num_sum > 1){
          //   return true;
          // }else{
          //   return false;
          // }
        },
        'pt3w' => function($value){
          //新算法
          $this->prize_num = 0;
          //数组进行N个组合
          $zuhe = Lottery28::strand($value['code'],2);
          //尾数
          $weishu = [];
          foreach($this->prize_code as $item){
            $weishu[] = $item % 10;
          }
          //循环组合
          foreach($zuhe as $vo){
            $num = 0;
            foreach($vo as $vo1){
              //如果号码 存在于尾数中则$num+1
              if(in_array($vo1,$weishu)){
                $num++;
              }
            }
            //如果都出现过则派奖+1
            if($num == 3){
              $this->prize_num++;
            }
          }
          if($this->prize_num > 0){
            return true;
          }else{
            return false;
          }

          // $num_sum = 0;
          // foreach ($value['code'] as $value1) {
          //   $num = 0;
          //   foreach ($this->prize_code as $value2) {
          //     if($value1 == ($value2 % 10)){
          //       $num++;
          //       if($num > 0){
          //         $num_sum++;
          //         break;
          //       }
          //     }
          //   }
          // }
          // if($num_sum > 2){
          //   return true;
          // }else{
          //   return false;
          // }
        },
        'pt4w' => function($value){
          //新算法
          $this->prize_num = 0;
          //数组进行N个组合
          $zuhe = Lottery28::strand($value['code'],4);
          //尾数
          $weishu = [];
          foreach($this->prize_code as $item){
            $weishu[] = $item % 10;
          }
          //循环组合
          foreach($zuhe as $vo){
            $num = 0;
            foreach($vo as $vo1){
              //如果号码 存在于尾数中则$num+1
              if(in_array($vo1,$weishu)){
                $num++;
              }
            }
            //如果都出现过则派奖+1
            if($num == 4){
              $this->prize_num++;
            }
          }
          if($this->prize_num > 0){
            return true;
          }else{
            return false;
          }
        },
      ],
      'ptbz' => [
        'bz' => function($value){
          // 新算法
          $this->prize_num = 0;
          $zuhe = Lottery28::strand($value['code'],$this->data_chat);
          //循环组合
          foreach($zuhe as $key=>$vo){
            // 不中数与开奖号是否有交集
            $rs = array_intersect($vo,$this->arrayStrInt($this->prize_code));
            // 如果没有交集则中奖
            if(count($rs) == 0){
             $this->prize_num++;
            }
          }
          if($this->prize_num > 0){
            return true;
          }else{
            return false;
          }
          // 旧算法
          // $num = 0;
          // foreach ($value['code'] as $value1) {
          //   if(!in_array($value1,$this->prize_code)){
          //     $num++;
          //     if($num >= $this->data_chat){
          //       return true;
          //     }
          //   }
          // }
          // return false;
        }
      ],
      'zx' => [
        'ex' => function($value){
          if(count(array_unique($this->data_chat)) == 2){
            return true;
          }else{
            return false;
          }
        },
        'sanx' => function($value){
          if(count(array_unique($this->data_chat)) == 3){
            return true;
          }else{
            return false;
          }
        },
        'six' => function($value){
          if(count(array_unique($this->data_chat)) == 4){
            return true;
          }else{
            return false;
          }
        },
        'wx' => function($value){
          if(count(array_unique($this->data_chat)) == 5){
            return true;
          }else{
            return false;
          }
        },
        'lx' => function($value){
          if(count(array_unique($this->data_chat)) == 6){
            return true;
          }else{
            return false;
          }
        },
        'qx' => function($value){
          if(count(array_unique($this->data_chat)) == 7){
            return true;
          }else{
            return false;
          }
        },
        'dan' => function($value){
          if(count(array_unique($this->data_chat)) % 2){
            return true;
          }else{
            return false;
          }
        },
        's' => function($value){
          if(count(array_unique($this->data_chat)) % 2){
            return false;
          }else{
            return true;
          }
        },
      ],
      'sb' => [
        'r' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'red'){
            return true;
          }else{
            return false;
          }
        },
        'b' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'blue'){
            return true;
          }else{
            return false;
          }
        },
        'g' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'green'){
            return true;
          }else{
            return false;
          }
        }
      ],
      'bb' => [
        'hd' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'red' && $this->prize_code[6] > 24 && $this->prize_code[6] != 49){
            return true;
          }else{
            return false;
          }
        },
        'hx' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'red' && $this->prize_code[6] < 25){
            return true;
          }else{
            return false;
          }
        },
        'hdan' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'red' && $this->prize_code[6] % 2 && $this->prize_code[6] != 49){
            return true;
          }else{
            return false;
          }
        },
        'hs' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'red' && $this->prize_code[6] % 2 == 0 && $this->prize_code[6] != 49){
            return true;
          }else{
            return false;
          }
        },
        'ld' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'blue' && $this->prize_code[6] > 24 && $this->prize_code[6] != 49){
            return true;
          }else{
            return false;
          }
        },
        'lx' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'blue' && $this->prize_code[6] < 25){
            return true;
          }else{
            return false;
          }
        },
        'ldan' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'blue' && $this->prize_code[6] % 2 && $this->prize_code[6] != 49){
            return true;
          }else{
            return false;
          }
        },
        'ls' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'blue' && $this->prize_code[6] % 2 == 0 && $this->prize_code[6] != 49){
            return true;
          }else{
            return false;
          }
        },
        'nd' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'green' && $this->prize_code[6] > 24 && $this->prize_code[6] != 49){
            return true;
          }else{
            return false;
          }
        },
        'nx' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'green' && $this->prize_code[6] < 25){
            return true;
          }else{
            return false;
          }
        },
        'ndan' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'green' && $this->prize_code[6] % 2 && $this->prize_code[6] != 49){
            return true;
          }else{
            return false;
          }
        },
        'ns' => function($value){
          if($this->prize_code_type[6]['wave'][1] == 'green' && $this->prize_code[6] % 2 == 0 && $this->prize_code[6] != 49){
            return true;
          }else{
            return false;
          }
        }
      ]
    ];
  }
}
