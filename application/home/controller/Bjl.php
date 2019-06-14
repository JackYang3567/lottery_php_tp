<?php
namespace app\home\controller;
use think\Controller;
use app\home\controller\Lhd;
use think\Db;

class Bjl extends Common
{
  Public function _initialize(){
    $rs = Db::table('system_config')->where('name','=','web_open')->find();
    if($rs['value'] == 0){
        $this->redirect('/home/error');
    }
  }
  public function index(){
    echo $this->fetch('index');
  }
  public function room(){
    // print_r($this->checkLogin());
    echo $this->fetch('room');
  }
  //百家乐 接受数据方法
  public function baiJiaBet(){
    $data = input('post.');
    $return_data = [
      'Code'=>1,
      'Message'=>'投注成功'
    ];
    // Code:504
    // Message:"发牌超过80局后,只能投注龙虎和，请等待重新开局后，再投注其他";
    // 查询当前期号 进行判定
    // $rs = (new Game)->expectLHD();
    // // print_r($rs);
    // if($rs['IssueNo'] != $data['IssueNo']){
    //   $return_data['Code'] = -1;
    //   $return_data['Message'] = '期号错误';
    // }else if($rs['State'] == 'Close'){
    //   $return_data['Code'] = -2;
    //   $return_data['Message'] = '封盘';
    // }

    $user = $this->checkLogin();
    if($user['code'] == 0){
      $return_data = [
        'Code'=>-1,
        'Message'=>'请先登录'
      ];
    }else{
      // 查询当前
      $rs = Lhd::expectLHD(2);
      // print_r($rs);
      if($rs['IssueNo'] != $data['IssueNo']){

        $return_data['Code'] = -1;
        $return_data['Message'] = '期号错误';
      }else if($rs['State'] == 'Close'){
        $return_data['Code'] = -2;
        $return_data['Message'] = '封盘';
      }
      $chg = [
        'Xian' => 'xian',
        'Zhuang' => 'zhuang',
        'XianDui' => 'xd',
        'WanMeiDui' => 'wmdz',
        'RenYiDui' => 'rydz',
        'ZhuangDui' => 'zd',
        'Xiao' => 'xiao',
        'He' => 'he',
        'Da' => 'da',
      ];

       $content = [];
      // // print_r($data['IssueNo']);
      $insert = [
        'user_id' => $user['data']['id'],
        'other'=> $data['Desk'],
        'expect'=> $data['IssueNo'],
        'create_time' => time(),
        'content' => '',
        'money' => 0,
        'type' => 0,
      ];
      foreach ($data as $key => $value) {
        if(isset($chg[$key])){
          $content[$chg[$key]] = $value;//$value;
          $insert['money'] += $value;
        }
      }
      // print_r($return_data['Code']);die;
      $insert['content'] = json_encode($content);
      if($return_data['Code'] > 0){
        $text['uid'] = $insert['user_id'];
        $text['money'] = $insert['money'];
        $text['type'] = 0;
        // $text['explain'] = 'pc28('.$data['lottery'].')bet';
        Db::startTrans();
        try{
           $state = Db::table('betting')->insert($insert);
           if(moneyAction($text)['code'] && $state){
           }else{
             throw 'error';
           }
           Db::commit();
        }catch (\Exception $e) {
           Db::roollback();
        }
      }
    }
    return $return_data;
  }

  //百家乐走势图
  public function bjlLuzi(){
    $data = input('post.');
    $exp = Db::table('lottery_code')->where('type','=','0')->order('expect','DESC')->find();
    if(!empty($exp)){
      $exp['content'] = json_decode($exp['content'],true);
      $count = $exp['content']['count'] >= 60 ? 60 :$exp['content']['count'];
    }else{
      $count = 60;
    }

    $list = Db::table('lottery_code')->where('type','=',0)->limit($count)->order('expect','DESC')->select();
    $rs = [];
    foreach ($list as $key => $value) {
      $value['content'] = json_decode($value['content'],true);
      $num = [];
      $flower = [];
      $xian = 0;
      $zhuang = 0;
      // print_r($value['content']['code']);die;
      foreach ($value['content']['code'] as $k => $v) {
          preg_match_all("/\d+/s",$v, $zx_num);
          if( ($k >= 4 && $zx_num[0][0] != 0) || $k < 4 ){
            if($k%2 == 0){
              $xian += ($zx_num[0][0] >= 10 ? 0 : $zx_num[0][0]);
            }else{
              $zhuang += ($zx_num[0][0] >= 10 ? 0 : $zx_num[0][0]);
            }
            $num[] = $zx_num[0][0];
            $flower[] = substr($v, -1);
          }
      }
      $rs[$key] = [
          'DaXiao'=>"Xiao",
          'ForeCast'=>"111",
          'IsXianDui'=>0,
          'IsXianWanMeiDui'=>0,
          'IsZhuangDui'=>0,
          'IsZhuangWanMeiDui'=>0,
          'IssueNo'=>$value['expect'],
          'Xian'=> ($xian % 10),
          'Zhuang'=>($zhuang % 10),
      ];
      //判断大小
      if(count($num) >= 5){
        $rs[$key]['DaXiao'] = "Da";
      }
      //判断闲对
      if( ($num[0] == $num[2]) || (in_array($num[0],[1,11,12,13]) &&  in_array($num[2],[1,11,12,13])) ){
        $rs[$key]['IsXianDui'] = 1;
        if( ($num[0] == $num[2]) && ($flower[0] == $flower[2]) ){
          $rs[$key]['IsXianWanMeiDui'] = 1;
        }
      }
      //判断庄对
      if( ($num[1] == $num[3]) || (in_array($num[1],[1,11,12,13]) &&  in_array($num[3],[1,11,12,13])) ){
        $rs[$key]['IsZhuangDui'] = 1;
        if( ($num[1] == $num[3]) && ($flower[1] == $flower[3]) ){
          $rs[$key]['IsZhuangWanMeiDui'] = 1;
        }
      }
      // print_r($num);
      // print_r($flower);
    }
    $rs[] = [
      'DaXiao'=>"",
      'ForeCast'=>"221",
      'IsXianDui'=>0,
      'IsXianWanMeiDui'=>0,
      'IsZhuangDui'=>0,
      'IsZhuangWanMeiDui'=>0,
      'IssueNo'=>Lhd::expectLHD(2)['IssueNo'],
      'Xian'=>0,
      'Zhuang'=>0
    ];


    return $rs;
    // foreach ($list as $key => $value) {
    //   $value['content'] = json_decode($value['content'],true);
    //   preg_match_all("/\d+/s",$value['content']['code'][0], $long_num);
    //   preg_match_all("/\d+/s",$value['content']['code'][1], $hu_num);
    //   $rs[$key] = [
    //     'DaXiao'=>"Xiao",
    //     'ForeCast'=>"111",
    //     'IsXianDui'=>0,
    //     'IsXianWanMeiDui'=>0,
    //     'IsZhuangDui'=>0,
    //     'IsZhuangWanMeiDui'=>0,
    //     'IssueNo'=>"201807122098",
    //     'Xian'=>9,
    //     'Zhuang'=>5,
    //   ],
    //   $rs[$key] = [
    //     'ForeCast'=>"222",
    //     'Hu'=>$hu_num[0][0],
    //     'HuDanShuang'=>($hu_num[0][0]%2==1 ?0:1),
    //     'HuHongHei'=>0,
    //     'IssueNo'=>$value['expect'],
    //     'Long'=>$long_num[0][0],
    //     'LongDanShuang'=>($long_num[0][0]%2==1 ?0:1),
    //     'LongHongHei'=>0,
    //   ];
    //   if(substr($value['content']['code'][0], -1) == 'S' || substr($value['content']['code'][0], -1) == 'C'){
    //     $rs[$key]['LongHongHei'] = 1;
    //   }
    //   if(substr($value['content']['code'][1], -1) == 'S' || substr($value['content']['code'][1], -1) == 'C'){
    //     $rs[$key]['HuHongHei'] = 1;
    //   }
    // }
    // return $rs;

    // $rs = [
    //     [
    //       'DaXiao'=>"Xiao",
    //       'ForeCast'=>"121",
    //       'IsXianDui'=>0,
    //       'IsXianWanMeiDui'=>0,
    //       'IsZhuangDui'=>0,
    //       'IsZhuangWanMeiDui'=>0,
    //       'IssueNo'=>"201807171158",
    //       'Xian'=>4,
    //       'Zhuang'=>8,
    //     ],
    //     [
    //       'DaXiao'=>"Xiao",
    //       'ForeCast'=>"111",
    //       'IsXianDui'=>0,
    //       'IsXianWanMeiDui'=>0,
    //       'IsZhuangDui'=>0,
    //       'IsZhuangWanMeiDui'=>0,
    //       'IssueNo'=>"201807122098",
    //       'Xian'=>9,
    //       'Zhuang'=>5,
    //     ],
    //     [
    //       'DaXiao'=>"Xiao",
    //       'ForeCast'=>"111",
    //       'IsXianDui'=>0,
    //       'IsXianWanMeiDui'=>0,
    //       'IsZhuangDui'=>0,
    //       'IsZhuangWanMeiDui'=>0,
    //       'IssueNo'=>"201807122098",
    //       'Xian'=>9,
    //       'Zhuang'=>5,
    //     ],
    // ];
    // return $rs;
  }

  //百家乐房间赔率
  public function bjlodds(){
    $data = input('post.');
    $lottery_config = Db::table('lottery_config')->where('type','=',$data['type'])->find();

    $rs = [
      'Da'=>0.54,
      'He'=>8,
      'RenYiDui'=>5,
      'WanMeiDui'=>20,
      'Xian'=>1,
      'XianDui'=>11,
      'Xiao'=>1.5,
      'Zhuang'=>0.95,
      'ZhuangDui'=>11
    ];
    if(!empty($lottery_config)){
      $lottery_config['basic_config'] = json_decode($lottery_config['basic_config'],true);
      foreach ($rs as $key => &$value) {
        $value = $lottery_config['basic_config']['odds'][Lhd::chg($key,2)]['num'][$data['Desk']];
      }
    }
    return $rs;
  }
  //龙虎斗房间个数 进行的数据
  public function bjlList(){
    //print_r(1);
    $re = Db::table('lottery_config')->where('type','=',0)->find();
    $re['basic_config'] = json_decode($re['basic_config'],true);
    $rs = [
      'Code' => 1,
      'Data' => [],
      'ServerTime'=> time(),
      'NextTime'=> time()+10
    ];

    $expect = lhd::expectLHD(2);//获取龙虎斗
    // print_r($re['basic_config']);die;
    foreach ($re['basic_config']['room'] as $key => $value) {
      // print_r($value['min']);
      if($value['min'] > 500){
        $level = 3;
      }else if($value['min'] > 100){
        $level = 2;
      }else{
        $level = 1;
      }
      $rs['Data'][] = [
          "Desk"=> $key,
          "DisplayName"=> $value['name'],
          "State"=> $expect['State'],
          "MinBetMoney"=> $value['min'],
          "MaxBetMoney"=> $value['max'],
          "Level"=> $level,
          "StartTime"=> $expect['StartTime'],
          "EndTime"=> $expect['EndTime'],
          "NextState"=> $expect['NextState'],
          "NextStartTime"=> $expect['NextStartTime'],
          "NextEndTime"=> $expect['NextEndTime'],
          "IsEnable"=> true
      ];
    }
    // if($value[''])
    return $rs;

  }
  public function gameRule(){
    return '<div>
  <div class="title">
    简介<span>INTRODUCTION</span></div>
  <div class="content space">
    百家乐源起于意大利，简单的推理和快速运算是百家乐最大特点，因而从十九世纪即为广受欢迎的扑克游戏。</div>
  <div class="line">
    &nbsp;</div>
  <div class="title">
    玩法<span>GAME INSTRUCTION</span></div>
  <div class="content">
    游戏使用8副扑克牌：</div>
  <div class="list">
    <div>
      荷官会派出&ldquo;庄家&rdquo;和&ldquo;闲家&rdquo;两份牌。</div>
    <div>
      A是1点，2到9的牌面即为点数，K、Q、J、10是0点，加起来等于10也当作是0点；总数9点或最接近9点的一家胜出。</div>
    <div>
      当任何一家起手牌的点数总和为8或9，就称为&ldquo;天生赢家&rdquo;，牌局就算结束，双方不再补牌。</div>
    <div>
      您有9种下注选择：闲家、庄家、和局、庄对子、闲对子、任意对子、完美对子、大、小。</div>
    <div>
      派完起手牌，将依补牌规则补1张牌。</div>
  </div>
  <div class="content">
    补牌规则：</div>
  <div class="content red">
    闲家：</div>
  <div class="table">
    <div>
      <div>
        起手牌点数总和</div>
      <div>
        补牌规则</div>
    </div>
    <div>
      <div>
        0</div>
      <div>
        须补牌</div>
    </div>
    <div>
      <div>
        1</div>
      <div>
        须补牌</div>
    </div>
    <div>
      <div>
        2</div>
      <div>
        须补牌</div>
    </div>
    <div>
      <div>
        3</div>
      <div>
        须补牌</div>
    </div>
    <div>
      <div>
        4</div>
      <div>
        须补牌</div>
    </div>
    <div>
      <div>
        5</div>
      <div>
        须补牌</div>
    </div>
    <div>
      <div>
        6</div>
      <div>
        不须补牌</div>
    </div>
    <div>
      <div>
        7</div>
      <div>
        不须补牌</div>
    </div>
    <div>
      <div>
        8</div>
      <div>
        &ldquo;天生赢家&rdquo;</div>
    </div>
    <div>
      <div>
        9</div>
      <div>
        &ldquo;天生赢家&rdquo;</div>
    </div>
  </div>
  <div class="content red">
    庄家：</div>
  <div class="table">
    <div>
      <div>
        起手牌点数总和</div>
      <div>
        补牌规则</div>
    </div>
    <div>
      <div>
        0</div>
      <div>
        须补牌</div>
    </div>
    <div>
      <div>
        1</div>
      <div>
        须补牌</div>
    </div>
    <div>
      <div>
        2</div>
      <div>
        须补牌</div>
    </div>
    <div>
      <div>
        3</div>
      <div>
        当闲家补得第三张牌是8，不须补牌；其余则须补牌</div>
    </div>
    <div>
      <div>
        4</div>
      <div>
        当闲家补得第三张牌是0.1.8.9，不须补牌；其余则须补牌</div>
    </div>
    <div>
      <div>
        5</div>
      <div>
        当闲家补得第三张牌是0.1.2.3.8.9，不须补牌；其余则须补牌</div>
    </div>
    <div>
      <div>
        6</div>
      <div>
        当闲家补得第三张牌是0.1.2.3.4.5.8.9，不须补牌；其余则须补牌</div>
    </div>
    <div>
      <div>
        7</div>
      <div>
        不须补牌</div>
    </div>
    <div>
      <div>
        8</div>
      <div>
        &ldquo;天生赢家&rdquo;</div>
    </div>
    <div>
      <div>
        9</div>
      <div>
        &ldquo;天生赢家&rdquo;</div>
    </div>
  </div>
  <div class="content red">
    闲家起手牌点数为6点或7点，闲家不须补牌 ，此条件下庄家起手牌点数为5或5点以下，庄家必须补第三张牌。</div>
  <div class="line">
    &nbsp;</div>
  <div class="title">
    派彩<span>PAYOFF</span></div>
  <div class="table">
    <div>
      <div>
        下注组合</div>
      <div>
        赔率</div>
    </div>
    <div>
      <div>
        庄</div>
      <div id="e-Zhuang">
        1 赔 0.95</div>
    </div>
    <div>
      <div>
        闲</div>
      <div id="e-Xian">
        1 赔 1</div>
    </div>
    <div>
      <div>
        和</div>
      <div id="e-He">
        1 赔 8</div>
    </div>
    <div>
      <div>
        庄对</div>
      <div id="e-ZhuangDui">
        1 赔 11</div>
    </div>
    <div>
      <div>
        闲对</div>
      <div id="e-XianDui">
        1 赔 11</div>
    </div>
    <div>
      <div>
        任意对子</div>
      <div id="e-RenYiDui">
        1 赔 5</div>
    </div>
    <div>
      <div>
        完美对子</div>
      <div id="e-WanMeiDui">
        1 赔 20</div>
    </div>
    <div>
      <div>
        大</div>
      <div id="e-Da">
        1 赔 0.54</div>
    </div>
    <div>
      <div>
        小</div>
      <div id="e-Xiao">
        1 赔 1.5</div>
    </div>
  </div>
  <div class="list">
    <div>
      和局时，&ldquo;庄／闲&rdquo;的下注将退回。</div>
    <div>
      庄对指庄的起手牌为同数字或英文字母。</div>
    <div>
      闲对指闲的起手牌为同数字或英文字母。</div>
    <div>
      任意对子指庄或闲的起手牌为同数字或英文字母。</div>
    <div>
      完美对子指庄或闲的起手牌为同花色且同数字或同花色且同英文字母。</div>
    <div>
      大指当局开牌张数总和5张牌或6张牌为大。</div>
    <div>
      小指当局开牌张数总和4张牌为小。</div>
  </div>
  </div>
  <br />
  ';
  }

}
