<?php
namespace app\prize\controller;
use think\Db;
use app\home\model\Betting;
use app\home\model\ChatRoom;

class Pc28 extends Common
{

  //post数据
  public $post_data;
  //彩种配置
  public $lottery_config;
  //开奖号（数组）
  public $prize_code;

  public function _initialize(){

    $this->post_data = input('param.');
    if(empty($this->post_data) || empty($this->post_data['expect']) || empty($this->post_data['type'])){
      $this->error('异常访问');
    }
    $lottery_config = Db::table('lottery_config')->field('basic_config,name')->where([ 'type'=>$this->post_data['type'] ])->find();
    if($this->post_data['type'] == 26){
      $this->post_data['type1'] = 2;
    }else if($this->post_data['type'] == 27){
      $this->post_data['type1'] = 12;
    }else{
      $this->post_data['type1'] = $this->post_data['type'];
    }
    $prize_code = Db::table('lottery_code')->field('content')->where([ 'type'=>$this->post_data['type1'],'expect'=>$this->post_data['expect'] ])->find();

    if(in_array($this->post_data['type'],[26,27])){
      $prize_code['content'] = array_slice(explode(',',$prize_code['content']),0,3);
      $prize_code['content'] = join(',',$prize_code['content']);
    }
    if(empty($lottery_config)){
      $this->error('没有找到这个游戏数据');
    }else{
      if(empty($prize_code)){
        $this->error($lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期没有开奖数据');
      }else{
        $lottery_config['basic_config'] = json_decode($lottery_config['basic_config'],true);
        $this->lottery_config = $lottery_config;

        $this->prize_code = explode(',',$prize_code['content']); //开奖号码

      }
    }
    // if(method_exists($this,'__initialize')){
    //   $this ->__initialize();
    // }
  }

  public function prize(){
    // return $this->post_data;
    $bet_change = [
      'a' => '大',
      'b' => '小',
      'c' => '单',
      'd' => '双',
      'ac' => '大单',
      'ad' => '大双',
      'bc' => '小单',
      'bd' => '小双',
      'max' => '极大',
      'min' => '极小',
      'green' => '绿',
      'blue' => '蓝',
      'red' => '红',
      'yellow' => '豹子',
    ];
    //获取数据库投注内容与倍数 $odds
    foreach ($this->lottery_config['basic_config'] as $key => $value) {

      foreach ($value['items'] as $k=> $v) {
        $odds[is_numeric($v['name']) ? $v['name'] : array_search($v['name'],$bet_change)] = $v['odds']; //倍数
      }
    }

    //查询出当期所有投注数据
    $data = Db::table('betting')
          ->field('id,user_id,content,money,other')
          ->where([ 'type'=>$this->post_data['type'],'expect'=>$this->post_data['expect'],'state'=>0 ])
          ->select();
    $return_data = [
      'code' => 0,
      'msg' => $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期派奖失败'
    ];
    if(empty($data)){
      $return_data['msg'] = $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期没有投注数据';
    } else {
      //开奖属性 $winning
      $code_num = array_sum($this->prize_code);
      $winning[0] = $code_num;   //单点
      $winning[1] = $winning[0] <= 13 ? 'b':'a';    //大小
      $winning[2] = $winning[0] % 2 == 0 ? 'd':'c'; //单双
      $winning[3] = ($winning[1].$winning[2]);      //组合
      $winning[4] = in_array($winning[0],[1,2,7,8,12,13,18,19,23,24]) ? 'red' : (in_array($winning[0],[3,4,9,10,14,15,20,25,26]) ? 'blue' : 'green');//波色
      $winning[5] = $winning[0] <= 5 ? 'min' : ($winning[0] >= 22 ? 'max':''); //极值

      $winning[6] = ($this->prize_code[0] == $this->prize_code[1]&&$this->prize_code[1] == $this->prize_code[2]) ? 'yellow' :''; //豹子
       // print_r($winning);
      $explain = '';
      //缓存用户信息
      $u_all = [];
      foreach ($data as $key => $value) {
        $value['content'] = json_decode($value['content'],true);
        //print_r($value);
        $state = 1;
        $win = 0;   //单个中奖
        $Allwin = 0; //总中奖
        $explain = '';
       
        foreach ($value['content'] as $k => $v) {
          if(in_array($v['code'],$winning) && !empty($v['code'])){ //是否中奖
            
            if(!is_numeric($v['code']) && in_array($code_num,[0,13,14,27])){//投注不是数字 且开了0，13，14，27
              
              //特殊开特殊号码时
              if($code_num == 13 || $code_num == 14){
                if(in_array($v['code'],['a','b','c','d'])){
                  if($v['money'] > 10000){
                    $Allwin += $v['money'];
                    $explain .= (is_numeric($v['code'])?$v['code']:$bet_change[$v['code']]).' '.'中奖'.':'.$v['money'].'元'.';';
                    continue;
                  }else{
                    $Allwin += number_format(($v['money']*($odds[$v['code']][$value['other']]-0.5)),2,'.','');//大小单双要减去0.5倍  原本两倍
                    $explain .= (is_numeric($v['code'])?$v['code']:$bet_change[$v['code']]).' '.'中奖'.':'.$win.'元'.';';
                    continue;
                  }
                }else if( in_array($v['code'],['ac','ad','bc','bd']) ){
                  $Allwin += $v['money'];
                  $explain .= (is_numeric($v['code'])?$v['code']:$bet_change[$v['code']]).' '.'中奖'.':'.$v['money'].'元'.';';
                  continue;
                }
              }
              
              if( in_array($v['code'],['red','blue','green']) ){//特殊号码波色皆输
                $win = 0;
              }else{
                $win = number_format(($v['money']*$odds[$v['code']][$value['other']]),2,'.','');
                $explain .= (is_numeric($v['code'])?$v['code']:$bet_change[$v['code']]).' '.'中奖'.':'.$win.'元'.';';
              }
            }else{
              $win = number_format(($v['money']*$odds[$v['code']][$value['other']]),2,'.','');

              $explain .= (is_numeric($v['code'])?$v['code']:$bet_change[$v['code']]).' '.'中奖'.':'.$win.'元'.';';
            }
            $Allwin += $win;
          }
        }
        if($Allwin > 0){
          $arr = Db::table('lottery_config')->column('name','type');
          // print_r($arr[$this->post_data['type']].'pc28');

          $is_ok = moneyAction([
            'uid' => $value['user_id'],
            'money' => $Allwin,
            'type' => 3,
            'explain' => ($arr[$this->post_data['type']].'pc28')
          ]);
          // print_r($is_ok);return;
          if($is_ok['code'] == 0){//判断是否成功派奖
            $state = 0;
            $explain = '派奖出错,未处理这个单子';
          }
        }
        // print_r($win);
        $save[] = [
          'id' => $value['id'],
          'state' => $state,
          'win' => $Allwin,
          'explain' => ($explain ? $explain:'已结算'),
          ];

        if($Allwin > 0 && $is_ok['code'] != 0){
          if(!isset($u_all[$value['user_id']])){
            $u_all[$value['user_id']] = Db::table('user')->field('username,type')->where(['id'=>$value['user_id']])->find();
          }
          $strl = mb_strlen($u_all[$value['user_id']]['username']);
          $s_n = $u_all[$value['user_id']]['type'] == 1 ? '试玩用户' :mb_substr($u_all[$value['user_id']]['username'],0,floor($strl/2) ).'***';
          $chat_r[] = [
            'user_id' => 0,
            'content' => '恭喜玩家'.$s_n.'在游戏'.$arr[$this->post_data['type']].'中,投注'.$explain,
            'create_time' => time(),
          ];
        }
      }
      // print_r($save);

      if((new betting)->saveAll($save)){
        if(isset($chat_r)){
          (new ChatRoom)->insertAll($chat_r);
        }
          $return_data['code'] = 1;
          $return_data['msg'] = $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期已经全部派奖';
      }
    }
    print_r(json_encode($return_data));
  }
}
