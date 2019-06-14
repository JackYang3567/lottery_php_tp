<?php
namespace app\prize\controller;

class Ks extends Lottery
{
  public $data_chat;
  public $prize_code_type;

  public function prize(){
    $this->actionPrize();
  }

  public function action($key1,$key2,$value,$money,$other,$odds){
    $key_chat = $key1;
    $return_data = [
      'code' => 0,
      'num' => 0
    ];

    $offical = ['official_hz','official_3thtx','official_3thdx','official_3bth','official_3lhtx','official_2lhfx','official_2lhdx','official_2bth'];
    if(in_array($key1,$offical))
    {
        return $this->paijiangOfficial($key1,$key2,$value,$money,$other,$odds);
    }
    switch ($key1) {
      case 'zh':
        $this->data_chat = array_sum($this->prize_code);
        break;
    }
    $this->prize_code_type = [
      'bz' => ($this->prize_code[0] == $this->prize_code[1] && $this->prize_code[1] == $this->prize_code[2]),
      'sz' => (function($prize_code){
        sort($prize_code);
        $return_data = false;
        if(($prize_code[0] + 1) == $prize_code[1] && ($prize_code[1] + 1) == $prize_code[2]){
          $return_data = true;
        }
        return $return_data;
      })($this->prize_code),
      'dz' => ($this->prize_code[0] == $this->prize_code[1] || $this->prize_code[1] == $this->prize_code[2] || $this->prize_code[2] == $this->prize_code[0]),
      'bs' => (function($prize_code){
        sort($prize_code);
        $return_data = false;
        if(($prize_code[0] + 1) == $prize_code[1] || ($prize_code[1] + 1) == $prize_code[2]){
          $return_data = true;
        }
        return $return_data;
      })($this->prize_code),
    ];

    if($this->rule()[$key_chat][$key2]($value)){
      $return_data['code'] = 1;
      $return_data['num'] = $money * $odds;
    }
    return $return_data;
  }

  public function rule(){
    return [
      'xt' => [
        // 开奖号码全部相同,则中奖
        'bz' => function($value){
          if($this->prize_code_type['bz']){
            return true;
          }else{
            return false;
          }
        },
        // 开奖号码的个位十位百位数字能相连，不分顺序。
        'sz' => function($value){
          if($this->prize_code_type['sz']){
            return true;
          }else{
            return false;
          }
        },
        // 开奖号码有两个号码相等,不包括豹子
        'dz' => function($value){
          if(!$this->prize_code_type['bz'] && $this->prize_code_type['dz']){
            return true;
          }else{
            return false;
          }
        },
        // 中奖号码的个位十位百位任意两位数字相连，不分顺序。（不包括顺子、对子。）
        'bs' => function($value){
          if(!$this->prize_code_type['sz'] && !$this->prize_code_type['dz'] && $this->prize_code_type['bs']){
            return true;
          }else{
            return false;
          }
        },
        // 不包括豹子、对子、顺子、半顺的所有中奖号码
        'zl' => function($value){
          if(!$this->prize_code_type['bz'] && !$this->prize_code_type['dz'] && !$this->prize_code_type['sz'] && !$this->prize_code_type['bs']){
            return true;
          }else{
            return false;
          }
        },
      ],
      'zh' => [
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
        'da' => function($value){
          if($this->data_chat > 10){
            return true;
          }else{
            return false;
          }
        },
        'x' => function($value){
          if($this->data_chat < 11){
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
        }
      ]
    ];
  }

  public function paijiangOfficial($key1,$key2,$value,$money,$other,$odds)
  {
      $re = [
          'code' => 0,
          'num' => 0
      ];
      if($key1 == 'official_hz' && $key2 == 'official_hz')
      {
          $sum = $this->prize_code[0] + $this->prize_code[1] + $this->prize_code[2];
          if(in_array($sum,$value['code'][0]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_hz' && $key2 == 'official_dxds')
      {
          $sum = $this->prize_code[0] + $this->prize_code[1] + $this->prize_code[2];
          $codeNum = 0;
          if($sum > 10 && in_array('da',$value['code'][0]))
          {
              $codeNum += 1;
          }
          if($sum <= 10 && in_array('x',$value['code'][0]))
          {
              $codeNum += 1;
          }
          if($sum % 2 == 1 && in_array('dan',$value['code'][0]))
          {
              $codeNum += 1;
          }
          if($sum % 2 === 0 && in_array('s',$value['code'][0]))
          {
              $codeNum += 1;
          }
          if($codeNum > 0)
          {
              $re['code'] = 1;
              $re['num'] = $codeNum * ($money * $odds);
          }
      }

      if($key1 == 'official_3thtx' && $key2 == 'official_3thtx')
      {
          if($this->prize_code[0] == $this->prize_code[1] && $this->prize_code[1] == $this->prize_code[2] && $value['code'][0][0] == 'tx')
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_3thdx' && $key2 == 'official_3thdx')
      {
          $th = $this->prize_code[0].$this->prize_code[1].$this->prize_code[2];
          if(in_array($th,$value['code'][0]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_3bth' && $key2 == 'official_3bth')
      {
          if($this->prize_code[0] != $this->prize_code[1] && $this->prize_code[1] != $this->prize_code[2] && $this->prize_code[0] != $this->prize_code[2] && in_array($this->prize_code[0],$value['code'][0]) && in_array($this->prize_code[1],$value['code'][0]) && in_array($this->prize_code[2],$value['code'][0]))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_3lhtx' && $key2 == 'official_3lhtx')
      {
          if($this->prize_code[0] + 1 == $this->prize_code[1] && $this->prize_code[2] - 1 == $this->prize_code[1] && $value['code'][0][0] == 'tx')
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_2lhfx' && $key2 == 'official_2lhfx')
      {
          if(($this->prize_code[0] == $this->prize_code[1] && $this->prize_code[1] != $this->prize_code[2] && in_array($this->prize_code[0].$this->prize_code[1],$value['code'][0])) || ($this->prize_code[0] != $this->prize_code[1] && $this->prize_code[1] == $this->prize_code[2] && in_array($this->prize_code[1].$this->prize_code[2],$value['code'][0])))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_2lhdx' && $key2 == 'official_2lhdx')
      {
          $arr = [];
          for ($i=0;$i<count($value['code'][0]);$i++)
          {
              for ($j=0;$j<count($value['code'][1]);$j++)
              {
                  array_push($arr,$value['code'][0][$i].$value['code'][1][$j]);
                  array_push($arr,$value['code'][1][$j].$value['code'][0][$i]);
              }
          }
          $code = $this->prize_code[0].$this->prize_code[1].$this->prize_code[2];
          if(in_array($code,$arr))
          {
              $re['code'] = 1;
              $re['num'] = 1 * ($money * $odds);
          }
      }

      if($key1 == 'official_2bth' && $key2 == 'official_2bth')
      {
          $arr = [];
          for($i=0;$i<count($value['code'][0]);$i++)
          {
              for($j=0;$j<count($value['code'][0]);$j++)
              {
                  if($value['code'][0][$i] != $value['code'][0][$j])
                  {
                      array_push($arr,$value['code'][0][$i].$value['code'][0][$j]);
                  }
              }
          }

          $codeNum = 0;
          if($this->prize_code[0] != $this->prize_code[1] && in_array($this->prize_code[0].$this->prize_code[1],$arr))
          {
              $key = array_search($this->prize_code[0].$this->prize_code[1],$arr);
              if(isset($key)){
                  unset($arr[$key]);
              }
              $codeNum += 1;
          }
          if($this->prize_code[1] != $this->prize_code[2] && in_array($this->prize_code[1].$this->prize_code[2],$arr))
          {
              $key = array_search($this->prize_code[1].$this->prize_code[2],$arr);
              if(isset($key)){
                  unset($arr[$key]);
              }
              $codeNum += 1;
          }
          if($this->prize_code[0] != $this->prize_code[2] && in_array($this->prize_code[0].$this->prize_code[2],$arr))
          {
              $key = array_search($this->prize_code[0].$this->prize_code[2],$arr);
              if(isset($key)){
                  unset($arr[$key]);
              }
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
