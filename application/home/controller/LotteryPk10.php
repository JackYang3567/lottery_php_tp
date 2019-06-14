<?php
namespace app\home\controller;
use think\Db;

class LotteryPk10 extends Lottery
{
  public function listCount($arr) {
      // $sarr = [[]];
      // $result = [];
      // foreach($arr as $v){
      //   $tarr = [];
      //   for ($i = 0; $i < count($sarr);$i++){
      //     for($j = 0 ;$j < count($v); $j++){

      //       if(!isset($sarr[$i])){
      //         $sarr[$i] = [];
      //       }
      //       // var_dump($sarr);
      //       // echo '||';
      //       // print_r($i);
      //       // echo '--';
      //       if(!in_array($v[$j],$sarr[$i])){
      //         $tarr[] = $sarr[$i] = array_push($sarr[$i],$v[$j]);
      //         // print_r($tarr);die();
     
      //         // print_r(123);die();
      //         // array_push($tarr,());
      //       }
      //     }
      //   }
      //   $sarr = $tarr;
      // }
      // return count($sarr);
  }

  public function rule(){
    return [
      'official_dwd_fs' => function($code){
        $zhushu = 0;
        for($i=0;$i<10;$i++){
          if(isset($code[$i])){
            $zhushu += count($code[$i]);
          }else{
            return 0;
          }
        }
        return $zhushu;
      },
      'official_cqs_fs' => function($code){
        if(!isset($code[0]) || count($code[0]) == 0){
          return 0;
        }
        if(!isset($code[1]) || count($code[1]) == 0){
          return 0;
        }
        if(!isset($code[2]) || count($code[2]) == 0){
          return 0;
        }
        $zhushu = $this->listCount($code);// count($code[0])*count($code[1])*count($code[2]);
        return $zhushu;
      },
      'official_cqe_fs' => function($code){
        if(!isset($code[0]) || count($code[0]) == 0){
          return 0;
        }
        if(!isset($code[1]) || count($code[1]) == 0){
          return 0;
        }
        $zhushu = $this->listCount($code);
        return $zhushu;
      },
      'official_cgj_fs' => function($code){
        if(!isset($code[0])){
          return 0;
        }
        $zhushu = count($code[0]);
        if(!isset($code[0]) || $zhushu == 0){
          return 0;
        }
        return $zhushu;
      },
      'official_gyh_dxds' => function($code){
        if(!isset($code[0])){
          return 0;
        }
        $zhushu = count($code[0]);
        if($zhushu < 1 || $zhushu > 4){
          return 0;
        }
        return $zhushu;
      },
      'official_gyh_h' => function($code){
        if(!isset($code[0])){
          return 0;
        }
        $arr = [];
        for($i=1;$i<11;$i++){
          $arr[] = $i;
        }
        $arr2 = $this->arrangement($arr,2);
        $zhushu = 0;
        foreach($code[0] as $value){
          foreach($arr2 as $value1){
            if(array_sum($value1) == $value){
              $zhushu ++;
            }
          }
        }
      },
      'official_lhd' => function($code){
        $zhushu = count($code[0]);
        if(!isset($code[0]) || !$zhushu){
          return 0;
        }else if($zhushu < 1 || $zhushu > 2){
          return 0;
        }
        return $zhushu;
      }
    ];
  }

  public function verification($key1,$key2,$code)
  {
    switch ($key2) {
      case 'official_lhd_gj':
      case 'official_lhd_yj':
      case 'official_lhd_jj':
        $key2 = 'official_lhd';
        break;
    };
    // print_r($code);
    // $rs = $this->rule()[$key2]($code);
    // print_r($rs);die();
    return $this->rule()[$key2]($code);
  }

  /**
   * 官方玩法投注入口
   */
  public function officialBetting(){
    // print_r($this->post_data);die();
    $return_data = [
      'code' => 1,
      'msg' => ''
    ];
    $data = json_decode($this->post_data['data'],true);
    $bet_num = count($data);
    if($bet_num > 10){
      $return_data['code'] = -1;
      $return_data['msg'] = '错误';
    }else{
      $bet = [];
      $basi = [];
      $is_ok = 0; //成功几单
      foreach( $data as $k => $value ){
        // betting:{"zh":{"dan":{"code":"","num":1}}}
        // basic:{"zh":{"expect_s":["20181201026"],"stop":false},"hm":{"open":"完全公开","buy_money":0,"bd":0,"tc":0},"money":50}
        // type:2
        // $this->post_data['betting'];
        // $bet[$value['key1']] = [];
        $bet[$value['key1']][$value['key2']] = [ 'code' => $value['content'],'num' => $value['num'] ];
        $basi = [
          'zh' => [ 'expect_s' => [ $this->post_data['expect'] ],'stop' => false ],
          'hm' => ['open' => '完全公开','buy_money'=>0,'bd'=>0,'tc'=>0],
          'money' => $value['single'],
        ];
        $this->post_data['betting'] = json_encode($bet);
        $this->post_data['basic'] = json_encode($basi);
        // print_r($this->post_data);die();
        $rs = $this->bettingAction([
          'official_dwd_fs',
          // 'official_cqs_fs',
          // 'official_cqe_fs',
          'official_cgj_fs',
          'official_gyh_dxds',
          'official_gyh_h',
          'official_lhd'
        ]);
        if($rs['code'] > 0){
          $is_ok++;
        }else{
          // print_r($rs);
          $return_data['code'] = -1;
          $return_data['msg'] = '总共'.$bet_num.'单,成功'.$is_ok.'单';
          return $return_data;
        }
      }
      $return_data['code'] = 1;
      $return_data['msg'] = '投注成功';
      return $return_data;
    }
  }

  public function betting()
  {
    // print_r($this->post_data);die();
    // 这里传入需要验证的玩法(可以是大玩法key,也可小玩法key)
    return $this->bettingAction([
      'official_dwd_fs',
      'official_cqs_fs',
      'official_cqe_fs',
      'official_cgj_fs',
      'official_gyh_dxds',
      'official_gyh_h',
      'official_lhd'
    ]);
  }
}
