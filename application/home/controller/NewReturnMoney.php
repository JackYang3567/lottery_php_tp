<?php
namespace app\home\controller;
use Illuminate\Support\Debug\Dumper;
use think\Controller;
use think\Db;
use app\home\model\User;

class newReturnMoney extends Controller
{
	private $config;

	public function initialize() {
  	$config = Db::table('system_config')->field('value')->where('name','return_config')->find();
  	if(empty($config)){
  		$this->error('没有找到返水设置');
  	}
    $config = json_decode($config['value'],true);
    if($config['switch']['value'] == 0){
      $this->error('没有开启返点功能');
    }
  	$this->config = $config;
	}

  public function back(){

  	$return_data = [
  		'code' => 0,
  		'msg' => '没有返水用户'
  	];

//    if($this->config['return_time']['value'] == 0){
//      // 当天返点 23:59:59 30秒的时间差
//      if(date('H:i:s') < '23:59:30'){
//        $return_data['msg'] = '返点方式为当天返点,没有到当天返点时间,返点失败';
//        return $return_data;
//      }
//      $begin_time = strtotime(date('Y-m-d 00:00:00'));
//    }else if($this->config['return_time']['value'] == 1){
//      // 每周周一返点 00:00:00 30秒的时间差
//      if(date("w") <> 1 || date('H:i:s') > '00:00:30'){
//        $return_data['msg'] = '返点方式为每周返点,没有到每周一,或者已经过了每周一凌晨的返点时间';
//        return $return_data;
//      }
//      $begin_time = time() - (24 * 60 * 60) * 7;
//    }
      $begin_time = strtotime(date('Y-m-d 00:00:00'));
    $where = [
      'create_time' => [ 'create_time','>',$begin_time ]
    ];
    if($this->config['return_type']['value'] == 0){
      // 按会员充值返点
      $where['type'] = [ 'type','in',[ 2,7 ] ];
      $data = Db::table('capital_detail')->field('user_id,sum(money),0 as ruturn_type')->where($where)->group('user_id')->select() ?? [];

    }else if($this->config['return_type']['value'] == 1){
      // 按会员下注流水来返点
      $where['type'] = [ 'type','in',$this->config['return_type']['rule'][1][1] ];
      $data = Db::table('betting')->field('user_id,sum(money) as sum_money,0 as ruturn_type')->where($where)->group('user_id')->select() ?? [];
      // 这里是游戏返水
      $where['type'] = [ 'type','in',[ 0,1,52,53,54,55 ] ];
      $data = array_merge($data,Db::table('betting')->field('user_id,sum(money) as sum_money,1 as ruturn_type')->where($where)->group('user_id')->select() ?? []);
     //棋牌返水
      $map =[  'game_end_time' => [ 'game_end_time','>',$begin_time ] ];

      $qipai_dlx = $this->config['return_type']['rule'][1][2];
      $qipai =Db::table('api_betting')->field('user_id,sum(cell_score) as sum_money,2 as ruturn_type')->where('api_id','in',$qipai_dlx)->where($map)->group('user_id')->select() ?? [];
      $data = array_merge($data,$qipai);
    }
    // 操作用户的数据
    $user_data = [];
    // 返水发送消息
    $user_msg = [];

    foreach ($data as $value) {
      // 判断是否可以享受反水福利,如果无法享受则跳过
      if(User::get($value['user_id'])->group == 1){
        continue;
      }
      // 这里根据会员资金明细判断会员是否已经返过点了,如果在统计的时间段有返点，则跳过这个会员返点

      if(Db::table('capital_detail')->field('id')->where([ 
        'user_id' => $value['user_id'],
        'create_time' => [ 'create_time','>',$begin_time ],
        'type' => 11
      ])->find()){
        continue;
      }
      // 这里根据设置的返点值来获得返点比例
      $bl = 0;
      if($this->config['return_rule']['value'] == 0){

            $bl = $this->config['return_rule']['rule'][0][1];

      }else if($this->config['return_rule']['value'] == 1){
        // 这里是，如果是按照条件区间返水，判断当前 总额在设置的那个区间，来得到返点比例

          foreach($this->config['return_rule']['rule'][1][1][$this->config['return_type']['value']] as $value1){
          if($value['sum_money'] > $value1['min'] && $value['sum_money'] < $value1['max']){
            $bl = $value['ruturn_type']>0 ? $value1['value1'] : $value1['value'];
          }
        }
      }
      if($bl){
          if ( $value['ruturn_type']==0){
              $miaoshu = '彩票';
          }elseif ($value['ruturn_type']==1){
              $miaoshu ='游戏';
          }elseif ($value['ruturn_type']==2){
              $miaoshu ='棋牌游戏';
          }
        $user_data[] = [
          'uid' => $value['user_id'],
          'money' => round(($value['sum_money'] / 100 * $bl),2),
          'type' => 11,
//          'explain' => $value['ruturn_type'] ? '获得游戏返水' : '获得彩票投注返水',
          'explain' => '获取'.$miaoshu.'返水'
        ];
        $user_msg[] = [
          'user_id' => $value['user_id'],
          'title' => '返水到账通知',
          'content' => '您在' . $miaoshu . '中,获得返水' . round(($value['sum_money'] / 100 * $bl),2) . '元,已经到账至您的账户中,请注意查收',
          'state' => 0,
          'create_time' => time()
        ];
      }
    }
    $count = count($user_data);
    if($count){
      $money_action = moneyAction($user_data);
      if($money_action['code'] && Db::table('user_message')->insertAll($user_msg)){
        $return_data['code'] = 1;
        $return_data['msg'] = '返水成功,共计返水' . $count . '个用户';
      } else {
        $return_data['msg'] = $money_action['msg'];
      }
    }
    return $return_data;
  }
}
