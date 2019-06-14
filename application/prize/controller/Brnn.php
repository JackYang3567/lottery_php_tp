<?php
namespace app\prize\controller;
use app\home\controller\Lottery28;
use app\home\model\Betting;
use app\home\model\ChatRoom;
use think\Db;
class Brnn extends Lottery
{
  public function prize(){
    // //post数据
    // public $post_data;
    // //彩种配置
    // public $lottery_config;
    // //开奖号（数组）
    // public $prize_code;
    $return_data = [
      'code' => -1,
      'msg' => $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 派奖失败'
    ];
    //获取所有开奖类型
    $bingo = $this->winType();
    //查询本期所有投注内容
    $betting = Db::table('betting')
            ->field('id,user_id,money,content,explain,win')
            ->where('type','=',$this->post_data['type'])
            ->where('expect','=',$this->post_data['expect'])
            ->where('state','=',0)
            ->select();
            // print_r($betting);
    foreach ($betting as $key => &$value) {
      $demo = json_decode($value['content'],true);
      //每一注的金额
      $sig_money = round($value['money']/$this->betNum($demo),2);
      $win_all = 0;
      $win_sig = 0;
      foreach ($demo as $k => $v) {
        foreach ($v as $k1 => $v1) {
          if(in_array($k1,$bingo[$k])){
            $win_sig = round($sig_money * $this->lottery_config['basic_config'][$k]['items'][$k1]['odds'],2);
            $win_all += $win_sig;
            $value['explain'] .=  ( $this->lottery_config['basic_config'][$k]['name'].'-'. $this->lottery_config['basic_config'][$k]['items'][$k1]['name'].'中奖:'.$win_sig.'元;');
            $win_sig = 0;
          }
        }
      }
      if($win_all > 0){
        $is_ok = moneyAction([
          'uid' => $value['user_id'],
          'money' => $win_all,
          'type' => 3,
          'explain' => $this->lottery_config['name']
        ]);
        if($is_ok['code'] == 0){
          $value['explain'] = '派奖出错,未处理这个单子';
        } else {
          $value['state'] = 1;
          $value['win'] = $win_all;
          if(!isset($u_all[$value['user_id']])){
            $u_all[$value['user_id']] = Db::table('user')->field('username,type')->where(['id'=>$value['user_id']])->find();
          }
          $strl = mb_strlen($u_all[$value['user_id']]['username']);
          $s_n = $u_all[$value['user_id']]['type'] == 1 ? '试玩用户' :mb_substr($u_all[$value['user_id']]['username'],0,floor($strl/2) ).'***';
          $chat_r[] = [
            'user_id' => 0,
            'content' => '恭喜玩家'.$s_n.'在游戏'.$this->lottery_config['name'].'28中,投注'.$value['explain'],
            'create_time' => time(),
          ];
        }
      } else {
        $value['state'] = 1;
        $value['explain'] = '已结算';
      }
    }

    if((new Betting)->saveAll($betting)){
      if(isset($chat_r)){
        (new ChatRoom)->insertAll($chat_r);
      }
        $return_data['code'] = 1;
        $return_data['msg'] = $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期已经全部派奖';
    }
    print_r(json_encode($return_data));
    return;
  }
  //计算注数
  public function betNum($val){
    $count = 0;
    foreach ($val as $key => $value) {
      // print_r($val);die;
      $count += count($value);
    }
    return $count;
  }
  //给与开奖号码,返回所有中奖类型
  private function winType(){

   //开奖号循环获取格式化牌组
   $changePoker = [];
   foreach( $this->prize_code as $item ){
     $changePoker[] = Lottery28::parsePoker($item);
   }
   //将格式化后的划分为两组
   $listPoker = [array_slice($changePoker, 0, 5), array_slice($changePoker, 5)];
   //将开奖号码分成两组
   $openPoker = [array_slice($this->prize_code, 0, 5), array_slice($this->prize_code, 5)];
   //对比牛几
   $p0 = (new Lottery28)->getNnSize($listPoker[0]);
   $p1 = (new Lottery28)->getNnSize($listPoker[1]);
   //获取赢的一边的 key 0,1  和 赢家的牛牛
   $p_win = [];
   //获取赢一边的号码
   $code_win = '';
   //对比结果
   if ($p0 == $p1) {
     if(max($openPoker[0]) > max($openPoker[1])){
       $p_win = [0,$p0];
     }else{
       $p_win = [1,$p1];
     }
   } else {
     $p_win = [(($p0 > $p1) ? 0 : 1),(($p0 > $p1) ? $p0 : $p1) ];
   }

   //获取转换后的胜利的牌组
   $win_code = $listPoker[$p_win[0]];
   //开奖号获取中奖内容
   $bingo = [];
   //牛牛判断
   $narr = [
     '1' => 'niuyi',  //'牛一'
     '2' => 'niuer',  //'牛二'
     '3' => 'niusan', //'牛三'
     '4' => 'niusi',  //'牛四'
     '5' => 'niuwu',  //'牛五'
     '6' => 'niuliu', //'牛六'
     '7' => 'niuqi',  //'牛七'
     '8' => 'niuba',  //'牛八'
     '9' => 'niujiu', //'牛九'
     '10' => 'niuniu',//'牛牛'
     '11' => 'hsniu', //'花色牛'
   ];
   //规则判断
   $arr = ['yi','er','san','si','wu'];


   $bingo['nn'] = [($p_win[1] == -1 ? 'wuniu':$narr[$p_win[1]])];
   foreach ($arr as $key => $value) {
     $bingo[('pm' . $value)] = ['code_' . $win_code[$key][0]];
     $bingo[('sm' . $value)] = [];
     if($win_code[$key][0] <= 6){
       $bingo[('sm' . $value)][1] = 'x';
       $bingo[('sm' . $value)][3] = 'x';
     }else{
       $bingo[('sm' . $value)][1] = 'da';
       $bingo[('sm' . $value)][3] = 'd';
     }

     if($win_code[$key][0] % 2 == 0){
       $bingo[('sm' . $value)][2] = 's';
       $bingo[('sm' . $value)][3] .= 's';
     }else{
       $bingo[('sm' . $value)][2] = 'dan';
       $bingo[('sm' . $value)][3] .= 'd';
     }
   }
   $bingo['sf'] = [$p_win[0] == 0 ? 'lfs' : 'hfs'];
   return $bingo;
  }

}
