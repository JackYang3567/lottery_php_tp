<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use app\home\model\Betting;
use app\home\model\User;

class ReturnMoney extends Controller
{
	private $post_data;
	private $lottery_config;
	private $betting;

	public function initialize() {
		$post_data = input('param.');
		if(empty($post_data) || !isset($post_data['expect']) || !isset($post_data['type'])){
			$this->error('没有明确返点的目标');
    	}
    	$lottery_config = Db::table('lottery_config')->field('name,return')->where([ 'type'=>$post_data['type'] ])->find();
    	if(empty($lottery_config)){
    		$this->error('没有找到这个游戏的配置');
    	}
      if(empty($lottery_config['return'])){
        $this->error($lottery_config['name'] . ' 没有设置返点');
      }
      $lottery_config['return'] = json_decode($lottery_config['return'],true);
      if($lottery_config['return']['switch'] == 0){
        $this->error($lottery_config['name'] . ' 没有开启返点');
      }
      if($lottery_config['return']['chazhi'] < 0){
        $this->error($lottery_config['name'] . ' 返点率设置不正确');
      }
      $where = [
        'type' => $post_data['type'],
        'expect' => [ 'expect','in',[0,$post_data['expect']] ],
        //'state' => [ 'state','in',[1,3] ]
      ];
      //这里是港彩 B盘 才返水
      if($post_data['type'] == 21){
        $where['other'] = 1;
      }
    	$betting = Db::table('betting')->field('id,user_id,money,expect,state,win,explain')->where($where)->select();
      	if(empty($betting)){
      		$this->error($lottery_config['name'] . ' '. $post_data['expect'] . ' 期没有投注');
      	}
      	$this->betting = $betting;
      	$this->post_data = $post_data;
      	$this->lottery_config = $lottery_config;
	}

  public function back(){
  	$return_data = [
  		'code' => 0,
  		'msg' => '返点失败'
  	];
    // 操作用户的数据
    $user_data = [];
    // 投注表更新的数据
    $betting_data = [];

    //print_r($this->betting);
  	foreach ($this->betting as $value) {
      // 判断是否可以享受反水福利,如果无法享受则跳过
      if( User::get($value['user_id'])->group == 1){
        continue;
      }
      // 这里是追号处理
      if($value['expect'] == 0){
        $zhui = Db::table('betting_zhui')->field('state')->where([ 'betting_id'=>$value['id'],'expect'=>$this->post_data['expect'] ])->find();
        // 如果追号没有追这期，或者这期中奖了，则跳过这个单子返水
        if(empty($zhui) || $zhui['win'] == 0){
          continue;
        }
        $zhui_count = Db::table('betting_zhui')->where([ 'betting_id'=>$value['id'] ])->count();
      }else{
        // 如果是自购，这个单子中奖了，跳过这个单子返水
        if($value['win'] > 0){
          continue;
        }
      }
      // 这里是自购
  		if($value['money'] > 0){
        $is_add_data = [
          'uid' => $value['user_id'],
          'type' => 11,
          'explain' => $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期返水'
        ];
        if($value['expect'] == 0){ // 这里是自购追号
          $is_add_data['money'] = round($value['money'] / $zhui_count,2);
        }else{
          $is_add_data['money'] = $value['money'];
        }
        $is_add_data['money'] = round($is_add_data['money'] / 100 * $this->lottery_config['return']['chazhi'],2);
        $user_data[] = $is_add_data;
        $betting_data[] = [ 'id'=>$value['id'],'win'=>$value['win']+$is_add_data['money'],'explain'=>$value['explain'] . $is_add_data['explain'] . $is_add_data['money'] . '元;' ];
      }else{ // 这里是合买
        $sum_all = Db::table('betting_he')->field('all')->where([ 'betting_id'=>$value['id'] ])->find()['all'];
        if($value['expect'] == 0){ // 这里是合买追号
          $sum_money = round($sum_all / $zhui_count,2);
        }else{
          $sum_money = $sum_all;
        }
        // 退还的总额
        $sum_money = round($sum_money / 100 * $this->lottery_config['return']['chazhi'],2);
        $betting_data[] = [ 'id'=>$value['id'],'win'=>$value['win']+$is_add_data['money'],'explain'=>$value['explain'] . $is_add_data['explain'] . $is_add_data['money'] . '元;' ];
        $gen = Db::table('betting_gen')->field('user_id,money')->where([ 'betting_id'=>$value['id'] ])->select();
        foreach ($gen as $value1) {
          $bl = round($value1['money'] / ($sum_all / 100),5);
          $user_data[] = [
            'uid' => $value1['user_id'],
            'money' => round($sum_money / 100 * $bl,2),
            'type' => 11,
            'explain' => $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期返水'
          ];
        }
      }
  	}
    // print_r($user_data);
    // 进行所有返水资金操作
    if(count($user_data)){
      $user_action = moneyAction($user_data);
      if($user_action['code']){
        (new Betting)->saveAll($betting_data,true);
        $return_data['code'] = 1;
        $return_data['msg'] = $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期已经全部返水';
      }else{
        $return_data['msg'] = $user_action['msg'];
      }
    }else{
      $return_data['msg'] = $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期没有返水';
    }
    return $return_data;
  }
}
