<?php
namespace app\prize\controller;
use think\Db;
use app\home\controller\Plan;
use app\home\model\Betting;
use app\home\model\BettingGen;
use app\home\model\BettingZhui;

class Lottery extends Common
{
  //post数据
  public $post_data;
  //彩种配置
  public $lottery_config;
  //官方彩种配置
  public $lottery_official_config;
  //开奖号（数组）
  public $prize_code;
  //开奖时间(六合彩使用)
  public $prize_code_time = 0;

  public function _initialize(){
    $this->post_data = input('param.');
    if(empty($this->post_data) || empty($this->post_data['expect']) || empty($this->post_data['type'])){
      $this->error('异常访问');
    }
    $lottery_config = Db::table('lottery_config')->field('basic_config,name,official')->where([ 'type'=>$this->post_data['type'] ])->find();
    $prize_code = Db::table('lottery_code')->field('content')->where([ 'type'=>$this->post_data['type'],'expect'=>$this->post_data['expect'] ])->find();
    if(empty($lottery_config)){
      $this->error('没有找到这个游戏数据');
    }else{
      (new Plan)->index($this->post_data['type'],$this->post_data['expect'],explode(',',$prize_code['content']));
      if(empty($prize_code)){
        $this->error($lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期没有开奖数据');
      }else{
        $lottery_config['basic_config'] = json_decode($lottery_config['basic_config'],true);
        $lottery_config['official'] = json_decode($lottery_config['official'],true);
        $this->lottery_config = $lottery_config;
		    if($this->post_data['type'] == 21){
          $this->prize_code_time = $prize_code['create_time'];
        }
        $this->prize_code = explode(',',$prize_code['content']);
      }
    }
    if(method_exists($this,'__initialize')){
      $this ->__initialize();
    }
  }

  public function getZhuSuSum($data){
    $zhushu = 0;
    foreach ($data as $value1) {
      foreach ($value1 as $value2 ) {
        $zhushu += $value2['num'];
      }
    }
    return $zhushu;
  }

  public function actionPrize(){
    $return_data = [
      'code' => 0,
      'msg' => $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期派奖失败'
    ];
    $data = Db::table('betting')->field('id,user_id,content,money,expect,win,other,explain,category')->json(['content'])->where([ 'type'=>$this->post_data['type'],'expect'=> ['expect','in',[0,$this->post_data['expect']]],'state'=>['state','in',[0,3]] ])->select();
    if(empty($data)){
      $return_data['msg'] = $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期没有投注数据';
    } else {
      // 投注表要操作的数据
      $update_data = [];
      // 用户表要操作的数据
      $user_data = [];
      // 跟单表要操作的数据
      $gen_data = [];
      // 聊天房间播报数据
      $room_data = [];
      $hm_set = '';
      foreach ($data as $key => $value) {
        $zhushu = $this->getZhuSuSum($value['content']);
        $win_sum = 0;
        $explain = '';
        $is_insert = true;
        $state = 1;
        $is_money = $value['money'] / $zhushu;
        $zhui_sum = 0;

        if($value['expect'] == 0){
          // 获得追号数据
          $zh_explain = Db::table('betting_zhui')->where([ 'betting_id'=>$value['id'],'expect'=>$this->post_data['expect'],'state'=>0 ])->field('stop')->find();
          if(empty($zh_explain)){ // 这里是追号中,没有追当前期和已经处理了当前期
            continue;
          }
          $zh_sy = Db::table('betting_zhui')->where([ 'expect'=> ['expect','>',$this->post_data['expect']],'betting_id'=>$value['id'],'state'=>0 ])->count();
          if($zh_sy){
            $state = 3;
          }
          $explain = (empty($value['explain']) ? ($this->post_data['expect'] . '期：') : ($value['explain'] . '$' . $this->post_data['expect'] . '期：'));
          // 这里是追号得总期数
          $zhui_sum = Db::table('betting_zhui')->where([ 'betting_id'=>$value['id'] ])->count();
        }
        if($value['money'] == 0){
          if(empty($hm_set)){
            $hm_set = Db::table('system_config')->field('value')->json(['value'])->where(['name'=>'hm_zh'])->find()['value'];
          }
          // 获得合买数据
          $hm = Db::table('betting_he')->where(['betting_id'=>$value['id']])->find();
          // 这里如果冻结资金小于保底的资金,则单子出错,跳过这个单子
          if($hm['bd'] > Db::table('user')->field('no_money')->where('id',$value['user_id'])->find()['no_money']){
            $update_data[] = [
               'id' => $value['id'],
               'state' => 0,
               'win' => 0,
               'explain' => '这个合买发起人的冻结资金出错,未处理这个合买单子'
             ];
             continue;
          }
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
          $is_money = $hm['all'] / $zhushu;
        }

        // 这里分别算出追的每一期的金额
        $zhui_sum && ($is_money = $is_money / $zhui_sum);


        foreach ($value['content'] as $key1 => $value1) {
          foreach ($value1 as $key2 => $value2) {
            try{
              $is_type = $value['category'] ? 'official' : 'basic_config';

              // print_r($value['category']);die();

              $is_prize = $this->action($key1,$key2,$value2,$is_money,(isset($value['other']) ? $value['other'] : false),$this->lottery_config[$is_type][$key1]['items'][$key2]['odds']);

//              dump($is_prize);die;
              if($is_prize['code']){
                $win_sum += $is_prize['num'];
                $explain .= $this->lottery_config[$is_type][$key1]['name'] . '-' . $this->lottery_config[$is_type][$key1]['items'][$key2]['name'] . '中奖:' . $is_prize['num'] . '元;';
                if($is_prize['num'] < 100)
                {
                    $is_insert = false;
                }
              }
            }catch (\Exception $e) {
              // print_r(123);die();
              $explain .= $e->getMessage();
                $is_insert = false;
//              die;
              $state = 0;
           	}
          }
        }
        if($value['expect'] == 0){

          // 更新追号的那一期状态和奖金
          Db::table('betting_zhui')->where([ 'betting_id'=>$value['id'],'expect'=>$this->post_data['expect'] ])->data([ 'state'=>1,'win'=>$win_sum ])->update();
        }

        if($win_sum > 0){
          $room_data[] = [
            'user_id' => 0,
            'content' => '恭喜玩家' . substr(getUserName($value['user_id']),0,3) . '*** 在' . $this->lottery_config['name'] . '游戏 ' . $explain,
            'create_time' => time()
          ];
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
              $bl = round($value3['money'] / ($hm['all'] / 100),5);
              $get_win = round($win_all / 100 * $bl,5);
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
                $win_all = floor($hm['all'] / $zhui_sum * $zh_sy);
                foreach ($gen as $value4) {
                  $bl = round($value4['money'] / ($hm['all'] / 100),5);
                  $get_win = round($win_all / 100 * $bl,5);
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
      if(count($user_data) > 0){
        $money_action = moneyAction($user_data);
        if($money_action['code']){
          // 进行中奖播报
            if($is_insert)
            {
                Db::table('chat_room')->insertAll($room_data);
            }
          // if($this->post_data['type'] == 21){
          //   $controller = new ReturnMoney;
          //   $controller->Back($this->post_data['expect'],21);
          // }
        }else{
          $return_data['msg'] = $money_action['msg'];
          print_r(json_encode($return_data));
          return;
        }
      }
      // 更新投注表数据状态
      count($update_data) && ((new Betting)->saveAll($update_data,true));
      // 更新跟单表跟单用户所得奖金
      count($gen_data) && ((new BettingGen)->saveAll($gen_data,true));
      $return_data['code'] = 1;
      $return_data['msg'] = $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期已经全部派奖';
    }
    print_r(json_encode($return_data));
    //print_r($return_data);
  }
}
