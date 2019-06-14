<?php
namespace app\prize\controller;
use think\Db;
use app\home\model\Betting;
use app\home\model\BettingGen;
use app\home\model\BettingZhui;

class Jczq extends Common
{
  //post数据
  public $post_data;
  //开奖号（数组）
  public $prize_code;

  public function _initialize(){
    $this->post_data = input('param.');
    if(empty($this->post_data) || empty($this->post_data['expect'])){
      $this->error('异常访问');
    }
    $prize_code = Db::table('lottery_code')->field('content')->where([ 'type'=>35,'expect'=>$this->post_data['expect'] ])->find();
    if(empty($prize_code)){
      $this->error('竞彩足球 ' . $this->post_data['expect'] . ' 期没有开奖数据');
    }else{
      $this->prize_code = json_decode($prize_code['content'],true);
    }
    if(method_exists($this,'__initialize')){
      $this ->__initialize();
    }
  }

  public function prize(){
    $return_data = [
      'code' => 0,
      'msg' => '竞彩足球 ' . $this->post_data['expect'] . ' 期派奖失败'
    ];
    $data = Db::table('betting')->field('id,user_id,content,money,expect,win,explain')->json(['content'])->where([ 'type'=>35,'state'=>['state','in',[0,3]] ])->select();
    if(empty($data)){
      $return_data['msg'] = '竞彩足球 ' . $this->post_data['expect'] . ' 期没有投注数据';
    } else {

      // 投注表要操作的数据
      $update_data = [];
      // 用户表要操作的数据
      $user_data = [];
      // 跟单表要操作的数据
      $gen_data = [];

      $hm_set = '';
      foreach ($data as $key => $value) {

        $win_sum = 0;
        $explain = '';
        $state = 1;
        $is_money = $value['money'] / $zhushu;
        $zhui_sum = 0;
        $zh_model = false;

        if($value['money'] == 0){
          if(empty($hm_set)){
            $hm_set = Db::table('system_config')->field('value')->json(['value'])->where(['name'=>'hm_zh'])->find()['value'];
          }
          // 获得合买数据
          $hm = Db::table('betting_he')->where(['betting_id'=>$value['id']])->find();
          if(floor(($hm['buy'] + $hm['bd']) / ($hm['all'] / 100)) < $hm_set['speed']){
            // 这里是合买进度没有达到设置的进度，进行撤单操作
            cheDan($value['id']);
            continue;
          }else{
            $back_money = $hm['bd'] + $hm['buy'] - $hm['all'];
            if($hm['buy'] < $hm['all']){ // 这里是保底投注处理
              $action1 = Db::table('betting_gen')->insert([
               'betting_id' => $value['id'],
               'user_id' => $value['user_id'],
               'money' => ($hm['bd'] - $back_money),
               'create_time' => time()
              ]);
              $action2 = Db::table('betting_he')->update([
                'betting_id' => $value['id'],
                'buy'=> ($hm['buy']+($hm['bd'] - $back_money))
              ]);
              if($action1 && $action2){
               $user_data[] = [
                 'uid' => $value['user_id'],
                 'money' => ($hm['bd'] - $back_money),
                 'type' => 14,
                 'explain' => '保底投注'
               ];
             }else{
               $update_data[] = [
                 'id' => $value['id'],
                 'state' => 0,
                 'win' => 0,
                 'explain' => '单子出错,未处理'
               ];
               continue;
             }
             if($back_money > 0){ // 这里是退还多余保底处理
               $user_data[] = [
                 'uid' => $value['user_id'],
                 'money' => $back_money,
                 'type' => 13,
                 'explain' => '保底退款'
               ];
              }
            }
          }
          if($value['expect'] == 0){
            $zhui_sum = Db::table('betting_zhui')->where([ 'betting_id'=>$value['id'] ])->count();
            $is_money = $hm['all'] / $zhui_sum / $zhushu;
          }else{
            $is_money = $hm['all'] / $zhushu;
          }
        }
        foreach ($value['content'] as $key1 => $value1) {
          foreach ($value1 as $key2 => $value2) {
            try{
              $is_prize = $this->action($key1,$key2,$value2,$is_money,(isset($value['other']) ? $value['other'] : false));
              if($is_prize['code']){
                $win_sum += $is_prize['num'];
                $explain .= $this->lottery_config['basic_config'][$key1]['name'] . '-' . $this->lottery_config['basic_config'][$key1]['items'][$key2]['name'] . '中奖:' . $is_prize['num'] . '元;';
              }
            }catch (\Exception $e) {
              $explain .= $e->getMessage();
              $state = 0;
           	}
          }
        }
        if($win_sum > 0){
          if($value['money'] == 0){ // 这里是合买分奖金操作
            $win_all = $win_sum;
            if($hm['tc'] > 0){
              // 这里处理提成
              $tc_money = round($win_sum / 100 * $hm['tc'],2);
              $user_data[] = [
                'uid' => $value['user_id'],
                'money' => $tc_money,
                'type' => 3,
                'explain' => '游戏合买中奖,提成收入'
              ];
              $win_all = $win_all - $tc_money;
            }
            // 获得跟单数据
            $gen = Db::table('Betting_Gen')->field('id,user_id,money')->where(['betting_id'=>$value['id']])->select();
            foreach ($gen as $value3) {
              $bl = round($value3['money'] / ($hm['all'] / 100),2);
              $get_win = round($win_all / 100 * $bl,2);
              $user_data[] = [
                'uid' => $value3['user_id'],
                'money' => $get_win,
                'type' => 3,
                'explain' => '游戏合买中奖分得奖金'
              ];
              $gen_data[] = [
                'id' => $value3['id'],
                'win' => $get_win
              ];
            }
            if($value['expect'] == 0){
              if($zh_sy && $zh_explain['stop']){ // 这里是合买追号中奖停止操作
                // 这里是追号得总期数
                $zhui_sum = Db::table('betting_zhui')->where([ 'betting_id'=>$value['id'] ])->count();
                $win_all = floor($hm['all'] / $zhui_sum * $zh_sy);
                foreach ($gen as $value4) {
                  $bl = floor($value4['money'] / ($hm['all'] / 100));
                  $get_win = floor($win_all / 100 * $bl);
                  $user_data[] = [
                    'uid' => $value4['user_id'],
                    'money' => $get_win,
                    'type' => 6,
                    'explain' => '游戏合买中奖停止追号退款'
                  ];
                }
                $state = 1;
              }
            }
          }else{
            $user_data[] = [
              'uid' => $value['user_id'],
              'money' => $win_sum,
              'type' => 3,
              'explain' => '游戏中奖'
            ];
            if($value['expect'] == 0){
              if($zh_sy && $zh_explain['stop']){ // 这里是自购追号中奖停止操作
                // 这里是追号得总期数
                $zhui_sum = $zhui_sum || Db::table('betting_zhui')->where([ 'betting_id'=>$value['id'] ])->count();
                $win_all = floor($value['money'] / $zhui_sum * $zh_sy);
                $user_data[] = [
                  'uid' => $value['user_id'],
                  'money' => $win_all,
                  'type' => 6,
                  'explain' => '游戏中奖停止追号退款'
                ];
                $state = 1;
              }
            }
          }
        }else{
          $explain .= '已结算;';
        }
        $update_data[] = [
          'id' => $value['id'],
          'state' => $state,
          'win' => $value['win'] + $win_sum,
          'explain' => $explain
        ];
      }
      // 进行所有资金操作
      count($user_data) && moneyAction($user_data);
      // 更新投注表数据状态
      count($update_data) && ((new Betting)->saveAll($update_data,true));
      // 更新跟单表跟单用户所得奖金
      count($gen_data) && ((new BettingGen)->saveAll($gen_data,true));
      // 这里如果是追号,更新追号得哪一期状态
      $zh_model && ($zh_model->data([ 'state'=>1 ])->update());
      $return_data['code'] = 1;
      $return_data['msg'] = $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期已经全部派奖';
    }
    print_r(json_encode($return_data));
    //print_r($return_data);
  }
}
