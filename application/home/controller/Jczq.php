<?php
namespace app\home\controller;
use think\Db;
use app\home\model\Betting;

class Jczq extends Common
{
  public $post_data;

  public function _initialize(){
    $this->post_data = input('post.');
    if(method_exists($this,'__initialize')){
      $this ->__initialize();
    }
  }

  public function betting(){
    $return_data = [
      'code' => 0,
      'msg' => '投注失败'
    ];
    $user = $this->checkLogin();
    if(!$user['code']){
      $return_data['msg'] = $user['msg'];
      return $return_data;
    }
    $user = $user['data'];
    if(empty($this->post_data) || !isset($this->post_data['betting']) || !isset($this->post_data['basic'])){
      return $return_data;
    }
    $betting = json_decode($this->post_data['betting'],true);
    $basic = json_decode($this->post_data['basic'],true);
    $zhushu = 1; // 这里是注数 验证接口、金额验证接口
    foreach ($betting as $value) {
      foreach ($value['data'] as $value1) {
        $is_time = Db::table('football_list')->field('over_time')->where([ 'order_id'=>$value1['id'] ])->find();
        if($is_time < time()){
          $return_data['msg'] = '您选择的比赛已经截止下注了';
          return $return_data;
        }
      }
    }
    $sum_money = $zhushu * $basic['money'];
    if($basic['hm']['buy_money'] > 0){ // 合买
        if($user['type'] == 1){
          $return_data['msg'] = '试玩会员不能发起合买';
          return $return_data;
        }
        if($user['money'] < ($basic['hm']['bd'] + $basic['hm']['buy_money'])){
          $return_data['msg'] = '您的余额不足';
          return $return_data;
        }
        $chat_data = Db::table('system_config')->field('value')->where(['name'=>'hm_zh'])->find();
        $chat_data = json_decode(($chat_data ? $chat_data['value'] : []),true);
        if($sum_money < $chat_data['total']){
          $return_data['msg'] = '合买总金额至少' . $chat_data['total'];
          return $return_data;
        }
        if($chat_data['zg'] > 0 && floor($basic['hm']['buy_money'] / ($sum_money / 100)) < $chat_data['zg']){
          $return_data['msg'] = '自购至少认购' . ceil($sum_money / 100 * $chat_data['zg']);
          return $return_data;
        }
        if($chat_data['bd'] > 0 && floor($basic['hm']['bd'] / (($sum_money - $basic['hm']['buy_money']) / 100))  < $chat_data['bd']){
          $return_data['msg'] = '保底至少保底' . ceil(($sum_money - $basic['hm']['buy_money']) / 100 * $chat_data['bd']);
          return $return_data;
        }
        if($chat_data['tc_num'] > 0 && $basic['hm']['tc'] > $chat_data['tc_num']){
          $return_data['msg'] = '提成不能大于奖金的' . $chat_data['tc_num'] . '%';
          return $return_data;
        }
        $is_money = 0;
        $out_money = $basic['hm']['buy_money'];
    }
    else{
      if($user['money'] < $sum_money){
        $return_data['msg'] = '您的余额不足';
        return $return_data;
      }
      $is_money = $sum_money;
      $out_money = $sum_money;
    }
    $user_data = [];
    $model = new Betting;
    $model->startTrans();
    try {
      // 投注表添加数据
      $model->save([
        'user_id' => $user['id'],
        'content' => $this->post_data['betting'],
        'money' => $is_money,
        'expect' => '',
        'type' => 35,
        'state' => 0,
        'create_time' => time()
      ]);
      if($basic['hm']['buy_money'] > 0){
        switch ($basic['hm']['open']) {
          case '完全公开':
            $is_open = 0;
            break;
          case '仅跟单人可见':
            $is_open = 1;
            break;
          case '截止后公开':
            $is_open = 2;
            break;
          case '完全保密':
            $is_open = 3;
            break;
          default:
            $is_open = 0;
            break;
        }
        $model->he()->save([
          'all' => $sum_money,
          'buy' => $basic['hm']['buy_money'],
          'open' => $is_open,
          'bd' => $basic['hm']['bd'],
          'tc' => $basic['hm']['tc'],
          'explain' => (empty($basic['hm']['explain']) ? null : $basic['hm']['explain'])
        ]);
        $model->gen()->save([
          'user_id' => $user['id'],
          'money' => $basic['hm']['buy_money'],
          'main' => 1
        ]);
        $user_data [] = [ 'uid'=>$user['id'],'money'=>$basic['hm']['bd'],'type'=>12,'explain'=>'合买保底冻结' ];
      }
      $user_data[] = [ 'uid'=>$user['id'],'money'=>$out_money,'type'=>0,'explain'=>'下注' ];
      $is_action = moneyAction($user_data);
      if($is_action['code'] == 0){
        throw new \Exception($is_action['msg']);
      }
      $model->commit();
      $return_data['code'] = 1;
      $return_data['msg'] = '已成功投注';
    } catch (\Exception $e) {
      $return_data['msg'] = $e->getMessage();
      $model->rollback();
    }
    return $return_data;
  }
}