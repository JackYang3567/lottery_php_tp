<?php
namespace app\home\controller;
use think\Controller;
use app\home\controller\Game;
use think\Db;

class Lhd extends Common
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
  // static function lhChg(){
  //   $chg = [
  //     'LongHei' => 'lhei',
  //     'He' => 'he',
  //     'HuShuang' => 'hs',
  //     'HuHei' => 'hhei',
  //     'HuHong' => 'hhong',
  //     'HuDan' => 'hd',
  //     'Hu' => 'h',
  //     'Long' => 'l',
  //     'LongShuang' => 'ls',
  //     'LongDan' => 'ld',
  //     'LongHong' => 'lhong',
  //   ];
  // }      //$data数据    $type龙虎还是百家  $get  1数据库数据   2页面显示数据
  static function chg($data,$type = 1,$get = 1){
    if($type == 1){
      $chg = [
        'LongHei' => 'lhei',
        'He' => 'he',
        'HuShuang' => 'hs',
        'HuHei' => 'hhei',
        'HuHong' => 'hhong',
        'HuDan' => 'hd',
        'Hu' => 'h',
        'Long' => 'l',
        'LongShuang' => 'ls',
        'LongDan' => 'ld',
        'LongHong' => 'lhong',
      ];
    }else{
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
    }
    if($get == 1){
      return $chg[$data];
    }else if($get == 2){

    }else{

    }
  }
  //龙虎 接受数据方法
  public function longHuBet(){
    $data = input('post.');
    $return_data = [
      'Code'=>1,
      'Message'=>'投注成功'
    ];
    // Code:504
    // Message:"发牌超过80局后,只能投注龙虎和，请等待重新开局后，再投注其他";
    $user = $this->checkLogin();
    if($user['code'] == 0){
      $return_data = [
        'Code'=>-1,
        'Message'=>'请先登录'
      ];
    }else{
      // 查询当前
      $rs = $this->expectLHD(1);
      // print_r($rs);
      // print_r($data);
      if(($rs['IssueNo'] != $data['IssueNo']) ){
        $return_data['Code'] = -1;
        $return_data['Message'] = '期号错误';
      }else if($rs['State'] == 'Close'){
        $return_data['Code'] = -2;
        $return_data['Message'] = '封盘';
      }

      $chg = [
        'LongHei' => 'lhei',
        'He' => 'he',
        'HuShuang' => 'hs',
        'HuHei' => 'hhei',
        'HuHong' => 'hhong',
        'HuDan' => 'hd',
        'Hu' => 'h',
        'Long' => 'l',
        'LongShuang' => 'ls',
        'LongDan' => 'ld',
        'LongHong' => 'lhong',
      ];
      $content = [];
      // print_r($data['IssueNo']);
      $insert = [
        'user_id' => $user['data']['id'],
        'other'=> $data['Desk'],
        'expect'=> $data['IssueNo'],
        'create_time' => time(),
        'content' => '',
        'money' => 0,
        'type' => 1,
      ];
      foreach ($data as $key => $value) {
        if(isset($chg[$key])){
          $content[$chg[$key]] = $value;//$value;
          $insert['money'] += $value;
        }
      }
      $insert['content'] = json_encode($content);
      // print_r($return_data$return_data);die;
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
    // Db::table('betting')->insert($insert);
    // if($return_data['code'] > 0){
    //   $text['uid'] = $insert['user_id'];
    //   $text['money'] = $insert['money'];
    //   $text['type'] = 0;
    //   // $text['explain'] = 'pc28('.$data['lottery'].')bet';
    //   Db::startTrans();
    //   try{
    //      $state = betting::insert($insert);
    //      if(moneyAction($text)['code'] && $state){
    //      }else{
    //        throw 'error';
    //      }
    //      Db::commit();
    //   }catch (\Exception $e) {
    //      Db::roollback();
    //   }
    // }

  }

  // //用户信息
  // public function userGet(){
  //   $rs = ["S"=>"333","D"=>""];
  //   return $rs;
  // }
  // 龙虎斗 用户数据 5S左右请求一次 最新数据
  Public function userGet(){
    $user = $this->checkLogin();
    // print_r($user);die;
    if($user['code'] <= 0){
      $rs = [
          'D'=>"登录超时，请重新登录",
          'S'=>"222"
        ];
    }else{
      $rs = [
        'Bgm_Open'=>1,                        //背景音乐
        'Display_Bet_Amount'=>1,
        'Display_Bet_Play'=>1,
        'PCDD_QA_Selected'=>"",
        'PCDD_QA_Setting'=>"5,10,20,50,100,500,1000,5000,10000,50000",
        'PC_QA_Selected'=>"",
        'Quickly_Amount'=>"",
        'Receive_Win'=>1,
        'Voice_Open'=>1,
      ];
    }
      return $rs;
  }
  //是否I登录  B金钱剩余 A未知
  public function realTimeData(){
    $user = $this->checkLogin();
    if($user['code'] <= 0){
      $rs = ['I'=> 2];
    }else{
      $rs = [
        'A' => 1,
        'B' => $user['data']['money'],
        'I' => 1,
      ];
    }
    // if($user)
    //
    //登录成功后的数据格式

    return $rs;
  }
  public function target(){
    return  Db::table('system_config')->where('name','=','home_url')->find()['value'];
  }
  //龙虎斗走势图
  public function lhdLuzi(){
    $data = input('post.');
    $exp = Db::table('lottery_code')->where('type','=','1')->order('expect','DESC')->find();
    if(!empty($exp)){
      $exp['content'] = json_decode($exp['content'],true);
      $count = $exp['content']['count'] >= 60 ? 60 :$exp['content']['count'];
    }else{
      $count = 60;
    }
//isset($exp['content']['count']) ? ($exp['content']['count'] < 61 ?$exp['content']['count'] :60) : 1;
    $list = Db::table('lottery_code')->where('type','=',1)->limit($count)->order('expect','DESC')->select();
    $rs = [];
    if(!empty($list)){
      foreach ($list as $key => $value) {
        $value['content'] = json_decode($value['content'],true);
        preg_match_all("/\d+/s",$value['content']['code'][0], $long_num);
        preg_match_all("/\d+/s",$value['content']['code'][1], $hu_num);
        $rs[$key] = [
          'ForeCast'=>"222",
          'Hu'=>$hu_num[0][0],
          'HuDanShuang'=>($hu_num[0][0]%2==1 ?0:1),
          'HuHongHei'=>0,
          'IssueNo'=>$value['expect'],
          'Long'=>$long_num[0][0],
          'LongDanShuang'=>($long_num[0][0]%2==1 ?0:1),
          'LongHongHei'=>0,
        ];
        if(substr($value['content']['code'][0], -1) == 'S' || substr($value['content']['code'][0], -1) == 'C'){
          $rs[$key]['LongHongHei'] = 1;
        }
        if(substr($value['content']['code'][1], -1) == 'S' || substr($value['content']['code'][1], -1) == 'C'){
          $rs[$key]['HuHongHei'] = 1;
        }
      }
    }
    $rs[] = [
      'ForeCast'=>"122",
      'Hu'=>0,
      'HuDanShuang'=>0,
      'HuHongHei'=>0,
      'IssueNo'=>$this->expectLHD()['IssueNo'],
      'Long'=>0,
      'LongDanShuang'=>0,
      'LongHongHei'=>0
    ];
    return $rs;
  }

  //龙虎斗期数计算
  static function expectLHD($type = 1){
    $rs = [
      'State' => '',   // 当前状态
      'IssueNo' => '', // 当前期号
      'StartTime' => '',// 当前状态时间
      'EndTime' => '',  // 当前状态结束时间
    ];
    $lottery_config = Db::table('lottery_config')->where('type','=',$type)->find();
    $lottery_config['time_config'] = json_decode($lottery_config['time_config'],true);
    $differ = time() - strtotime(date('Y-m-d ' . $lottery_config['time_config']['start_time']));
    //当前期号
    $rs['IssueNo'] = floor($differ / 30);
    //当前剩余时间
    $issue_no = $differ % 30;
    //本期的开始时间 && 开奖时间
    $rs['StartTime'] = strtotime(date('Y-m-d ' . $lottery_config['time_config']['start_time'])) + ($rs['IssueNo'] * 30);
    if($issue_no <= 17){ //下注时间
      $rs['State'] = 'Bet';
      $rs['EndTime'] = $rs['StartTime'] + 17;
    }else{              // 开奖时间
      $rs['StartTime'] = $rs['StartTime'] + 18;
      $rs['EndTime'] = $rs['StartTime'] + 11;
      $rs['State'] = 'Close';
    }
    //-----------以下为下一个状态数据----------------
    $rs['NextIssueNo'] = $rs['IssueNo'] + 1;
    if($rs['State'] == 'Close'){
      $rs['NextState'] = 'Bet';
      $rs['NextStartTime'] = $rs['EndTime'] + 1;
      $rs['NextEndTime'] = $rs['NextStartTime'] + 17;
    }else{
      $rs['NextState'] = 'Close';
      $rs['NextStartTime'] = $rs['EndTime'] + 1;
      $rs['NextEndTime'] = $rs['NextStartTime'] + 11;
    }
    $rs['IssueNo'] =date('Ymd'). sprintf("%04d", $rs['IssueNo']);//$rs['IssueNo'];
    $rs['NextIssueNo'] =date('Ymd'). sprintf("%04d", $rs['NextIssueNo']);//$rs['NextIssueNo'];
    return $rs;
  }

  //龙虎斗房间赔率
  public function lhdodds(){
    $data = input('post.');
    $lottery_config = Db::table('lottery_config')->where('type','=',$data['type'])->find();
    $rs = [
      'He'=>8,
      'Hu'=>1,
      'HuDan'=>0.75,
      'HuHei'=>0.9,
      'HuHong'=>0.9,
      'HuShuang'=>1.05,
      'Long'=>1,
      'LongDan'=>0.75,
      'LongHei'=>0.9,
      'LongHong'=>0.9,
      'LongShuang'=>1.05,
    ];
    if(!empty($lottery_config)){
      $lottery_config['basic_config'] = json_decode($lottery_config['basic_config'],true);
      foreach ($rs as $key => &$value) {
        $value = $lottery_config['basic_config']['odds'][$this->chg($key)]['num'][$data['Desk']];
      }
    }
    return $rs;
  }
  //龙虎斗房间个数 进行的数据
  public function lhdList(){
    //print_r(1);
    $re = Db::table('lottery_config')->where('type','=',1)->find();
    $re['basic_config'] = json_decode($re['basic_config'],true);
    $rs = [
      'Code' => 1,
      'Data' => [],
      'ServerTime'=> time(),
      'NextTime'=> time()+10
    ];

    $expect = $this->expectLHD(1);//获取龙虎斗
    foreach ($re['basic_config']['room'] as $key => $value) {
      $rs['Data'][] = [
          "Desk"=> $key,
          "DisplayName"=> $value['name'],
          "State"=> $expect['State'],
          "MinBetMoney"=> $value['min'],
          "MaxBetMoney"=> $value['max'],
          "Level"=> 1,
          "StartTime"=> $expect['StartTime'],
          "EndTime"=> $expect['EndTime'],
          "NextState"=> $expect['NextState'],
          "NextStartTime"=> $expect['NextStartTime'],
          "NextEndTime"=> $expect['NextEndTime'],
          "IsEnable"=> true
      ];
    }
    return $rs;
  }
  public function gameRule(){
    return '<div>
              <div class="title">
                简介<span>INTRODUCTION</span></div>
              <div class="content space">
                以牌面大小来决定输赢的桌牌游戏，游戏的容易程度让玩家不分男女老少都喜爱。牌面大小不比花色，只比点数，K为最大牌，A为最小。</div>
              <div class="line">
                &nbsp;</div>
              <div class="title">
                游戏玩法<span>GAME INSTRUCTION</span></div>
              <div class="list">
                <div>
                  游戏使用八副扑克牌。</div>
                <div>
                  玩家可投注 龙 虎 和 三门。</div>
                <div>
                  作废每局开始的第一张牌，然后庄家发出二张牌。</div>
                <div>
                  荷官只派两门牌，每门各派一只牌，即龙与虎，双方斗大。</div>
              </div>
              <div class="line">
                &nbsp;</div>
              <div class="title">
                大小<span>BIG &amp; SMALL CARD COUNTING</span></div>
              <div class="content space">
                最大为K，最小为A，不比花色，只比点数，点数相同为和。</div>
              <div class="line">
                &nbsp;</div>
              <div class="title">
                牌面点数<span>COUNT THE CARD</span></div>
              <div class="list">
                <div>
                  下注龙，<span id="e-Long">1赔1</span>(开和局时，退回一半下注金额)</div>
                <div>
                  下注虎，<span id="e-Hu">1赔1</span>(开和局时，退回一半下注金额)</div>
                <div>
                  下注和，<span id="e-He">1赔8</span></div>
                <div>
                  下注龙单，<span id="e-LongDan">1赔0.75</span></div>
                <div>
                  下注龙双，<span id="e-LongShuang">1赔1.05</span></div>
                <div>
                  下注虎单，<span id="e-HuDan">1赔0.75</span></div>
                <div>
                  下注虎双，<span id="e-HuShuang">1赔1.05</span></div>
                <div>
                  下注龙红，<span id="e-LongHong">1赔0.9</span></div>
                <div>
                  下注龙黑，<span id="e-LongHei">1赔0.9</span></div>
                <div>
                  下注虎红，<span id="e-HuHong">1赔0.9</span></div>
                <div>
                  下注虎黑，<span id="e-HuHei">1赔0.9</span></div>
              </div>
            </div>';
  }
}
