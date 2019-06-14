<?php
namespace app\home\controller;
use app\home\controller\LotteryL;
use app\home\controller\Lottery28;
use app\home\model\ApiSocketMsg;
use function GuzzleHttp\Psr7\str;
use think\Db;

class Home extends Common
{
  /**
 * 组合排列,改良版
 * @param array $val 要排列的优化数组 [1,2,3,4,5,6]
 */
  static function strand($val, $num)
  {
    $rs = [];
    $list = 1;
    for ($i = 0; $i < pow(2, count($val)); $i ++){
        $a = 0;
        $b = [];
        if(substr_count(decbin($i),1) != $num){
          continue;
        }
        for ($j = 0; $j < count($val); $j ++){
            if ($i >> $j & 1){
                $a ++;
                $b[] = $val[$j];
                if($a == $num){
                  break;
                }
            } 
        }
        if ($a == $num){
            $rs[] = $b;
        }
    }
    return $rs;
  }

  public function test(){
    //  $rs = array_rand(range(1,10),5);
    // print_r($rs);
    // (new Plan)->index(1,1,1);
    // $rs = $this->strand([1,2,3,4,5,7,8,9,10,11,12,13,14,15,16,17,18,19,20],2);
    // print_r($rs);
    // $rs = (new Lottery)->getHistory(2);
    // print_r($rs);
    // (new Plan)->index(6,201810271101,[5,3,2,4,1]);
    // $rs = array_rand(['da' => '大','xiao' => '小'],1);
    // print_r($rs);
                      //彩种 期号 开奖号
    
    // $data = [
    //   [
    //     'type' => 2,                // 彩种类型
    //     'plan_bet' => 'code',       // 计划玩法
    //     'num'  => 5,                // 数字计划，如果plan_bet 不为code 则失效
    //     'plan_code' => '0',         // 计划第几球
    //     'uid' => 0,                 // 发言者id
    //     'uid_type' => 0,            // 0用户还是1管理者  如果uid为0的情况下或者找不到  则统一为'系统'
    //     'note' => '精准一哥',        // 响亮的称号
    //     'switch' => 1,              // 单独开关
    //   ],
    //   [
    //     'type' => 3,         //彩种类型
    //     'plan_bet' => 'code',       //计划玩法
    //     'plan_code' => '0',          //计划第几球
    //     'uid' => 0,        //发言者id
    //     'uid_type' => 0,         //0用户还是1管理者  如果id为0的情况下或者找不到  则统一为'系统'
    //     'note' => '',        //响亮的称号
    //     'switch' => 1,       //单独开关
    //   ]
    // ];
    // print_r(json_encode($data));
    //Db::table('chat_room')->where('user_id','>',0)->update(['type'=>1]);

    //login_user合并
    // $user = Db::table('user')->column('id');
    // foreach($user as $vo){
    //   $rs = Db::table('login_log')->where('user_id','=',$vo)->order('create_time','DESC')->find();
    //   if(!empty($rs)){
    //     Db::table('user')->where('id','=',$vo)->update([ 'active_time'=>$rs['create_time'],'active_ip'=>$rs['ip'] ]);
    //   }
    // }


    // 彩种配置
    // $all = [0,1,24,25,26,27,35,53,54,55,56,57,58];
    // for($i=0;$i<=56;$i++){
    //   if(in_array($i,$all)){continue;}
    //   $rs = Db::table('lottery_config')->field('basic_config')->where('type',$i)->find()['basic_config'];
    //   $rs = json_decode($rs,true);

    //   foreach($rs as &$item){
    //     $item['bet'] = 0;
    //     //  foreach($item as &$vo){
    //     //    $vo['switch'] = ($vo['switch'] == 1 ? [1,1] : [0,0]);
    //     //  }
    //   }
    //   Db::table('lottery_config')->where('type',$i)->update(['basic_config'=>json_encode($rs)]);
    //   print_r($i);
    //   echo '--';
    // }
  }
  /*demo添加*/
  public function testAdd(){
    $data = input('post.');
    // print_r($data);die;
    /**/
    if($data['pdata'] != ''){
      $add = [
        'node_id' => Db::table('tree')->max('node_id') + 1,
        'name' => $data['name'],
        'parent_id' => $data['pdata']['node_id'],
        'lft' => $data['pdata']['rgt'],
        'rgt' => (int)$data['pdata']['rgt'] + 1
      ];
      // /*修改操作 添加目标大于left的 left和right 全部+2*/
      Db::table('tree')->where('lft','>=',$data['pdata']['rgt'])->setInc('lft',2);
      Db::table('tree')->where('rgt','>=',$data['pdata']['rgt'])->setInc('rgt',2);
    }else{
      $node_js = Db::table('tree')->max('node_id');
      $l = Db::table('tree')->max('rgt');
      $add = [
        'node_id' => $node_js ? ($node_js + 1) : 1,
        'name' => $data['name'],
        'parent_id' => 0,
        'lft' => $l + 1,
        'rgt' => $l + 2
      ];
    }
    /*添加进数据库*/
    Db::table('tree')->insert($add);
  }

  /*demo 删除*/
  public function testdel(){
    $data = input('post.')['data'];
    /*删除操作*/
    Db::table('tree')->where('lft','>=',$data['lft'])->where('rgt','<=',$data['rgt'])->delete();
    //删除操作 首先找出删除个体+下线的总个数
    $num = ($data['rgt'] - $data['lft'] - 1) / 2 + 1;
    Db::table('tree')->where('lft','>',$data['rgt'])->setDec('lft',($num * 2));
    Db::table('tree')->where('rgt','>',$data['rgt'])->setDec('rgt',($num * 2));
    // print_r($data);
    return 1;
  }

  /*转移*/
  public function testchg(){
    $data = input('post.');
    //转移 首先找出转移个体+下线的总个数
    $num = (($data['u']['rgt'] - $data['u']['lft'] - 1) / 2) + 1;
    //很重要高节点转移低节点 正数为高节点转移低节点  负数为低节点转高节点
    $AD = $data['pu']['rgt'] -  $data['u']['rgt'];
    if($AD > 0){
      //$AD大于0的情况下 大于自身right 小于等于目标left的 left和right 都要进行变化-
      $modelL = Db::table('tree')->where('lft','>',$data['u']['rgt'])->where('lft','<=',$data['pu']['lft']);
      $modelR = Db::table('tree')->where('rgt','>',$data['u']['rgt'])->where('rgt','<',$data['pu']['rgt']);
      $modelL -> update(['type'=>1]);
      $modelR -> update(['type'=>1]);
      $modelL -> setDec('lft',($num * 2));
      $modelR -> setDec('rgt',($num * 2));
      //自身将要进行的变化 +
      $bh = ($data['pu']['rgt'] - $num*2) - $data['u']['lft'];
      // print_r($bh);
      Db::table('tree')->where('type','=',0)->where('lft','>=',$data['u']['lft'])->where('lft','<',$data['pu']['rgt'])->setInc('lft',$bh);
      Db::table('tree')->where('type','=',0)->where('rgt','>',$data['u']['lft'])->where('rgt','<',$data['pu']['rgt'])->setInc('rgt',$bh);
    }else{
      //高转低
      $modelL = Db::table('tree')->where('lft','<',$data['u']['lft'])->where('lft','>=',$data['pu']['rgt']);
      $modelL -> update(['type'=>1]);
      $modelL -> setInc('lft',($num * 2));

      $modelR = Db::table('tree')->where('rgt','<',$data['u']['lft'])->where('rgt','>=',$data['pu']['rgt']);
      $modelR -> update(['type'=>1]);
      $modelR -> setInc('rgt',($num * 2));
      $bit = $data['u']['lft'] - $data['pu']['rgt'];
      Db::table('tree')->where('type','=',0)->where('lft','>=',$data['u']['lft'])->where('lft','<',$data['u']['rgt'])->setDec('lft',$bit);
      Db::table('tree')->where('type','=',0)->where('rgt','>',$data['u']['lft'])->where('rgt','<=',$data['u']['rgt'])->setDec('rgt',$bit);
    }
    Db::table('tree')->where('type','=',1)->update(['type'=>0]);

    return 1;
  }

  //足球竞猜获取数据
  public function footBall(){
    $data = Db::table('football_list')
          ->where('over_time','>',time())
          ->order('over_time','ASC')
          ->select();
    $return_data = [];
    if(!empty($data)){
      foreach ($data as $key => &$value) {

         $key = floor(($value['over_time'] - strtotime(date('Y-m-d 00:00:00')))/86400);
         $content = json_decode($value['content'],true);
         $content['over_time'] = date('H:i',$value['over_time']);
         $content['id'] = $value['order_id'];
         $content['history']['guest'] = explode(',',$content['history']['guest']);
         $content['history']['host'] = explode(',',$content['history']['host']);
         if($content['rankInfo'][0] == ''){
           $content['rankInfo'] = ['-','-'];
         }
         if($content['history']['num'] == 0){
           $content['history']['score'] = [0,0,0];
         }else{
           $content['history']['score'] = explode(',',$content['history']['score']);
         }

        if(!empty($return_data[$key])){
          array_push($return_data[$key]['content'],$content);
        }else{
          $return_data[$key] = [
            'time' => [date('Y-m-d',$value['over_time']),date('w',$value['over_time'])],
            'content' => [$content]
          ];
        }

      }
    }else{
      $return_data = '';
    }
    return $return_data;
    // print_r($return_data);
  }
  public function getBasic(){
    $data = $lottery_list = Db::table('system_config')->field('value,name')->where('name','in',['domain_name','web_logo','web_title', 'web_login','web_open','demo_user','login_verify','dialog_kg','color_setting'])->select();
    $data_chat = [];
    foreach ($data as $key => $value) {
      $data_chat[$value['name']] = $value['value'];
    }
    $login = $this->checkLogin();
    $user_config['chip'] = [];
    $user_config['config'] = [];
    if($login['code']>0){
      $config = Db::table('user_config')->where('user_id','=',$login['data']['id'])->find();
      // print_r($config);
      if(!empty($config)){
        $rs = array_merge( json_decode($config['backstage'],true),json_decode($config['reception'],true) ) ;
        foreach ($rs as $key => $value) {
          if($key == 'chip'){
            $user_config['chip'] = $value;
          }else{
            $user_config['config'][$key] = $value;
          }
        }
      }
    }
    return [
      'static_path' =>  $data_chat['domain_name'],
      'web_name' =>     $data_chat['web_title'],
      'logo_path' =>    $data_chat['web_logo'],
      'user_config' =>  $user_config,
      'web_state' =>    $data_chat['web_open'],        // 网站开关
      'web_login' =>    $data_chat['web_login'],       // 是否必须登录才能进入网站
      'login_verify' => $data_chat['login_verify'],    // 是否开启验证码
      'demo_user' =>    $data_chat['demo_user'],       // 是否开启试玩用户
      'home_window' =>    $data_chat['dialog_kg'],     // 主页弹窗
      'chat_id' => session_id(),
      'main_color' => $data_chat['color_setting']
    ];
  }
  public function gameRule(){
    $data = input('post.');
    return Db::table('lottery_config')->where('type','=',$data['type'])->field('explain')->find()['explain'];
  }
  public function getInfo(){
    $rs = Db::table('system_config')->field('value')->where('name','in',['bulletin_trumpet','web_ppt'])->column('name,value');

    $notice = Db::table('article')
          ->where('cat_id',3)
          ->where('start_time','<=',time())
          ->where('end_time','>=',time())
          ->order('id DESC')
          ->find();

    return [
      'swipe' => (empty($rs['web_ppt']) ? [] : json_decode($rs['web_ppt'],true)),
      'notice' => $notice
    ];
  }

  /**
   * 获取数据库彩票数据
   * @return array 所有开启的彩票
   */
  static function lotteryAll(){
    return Db::table('lottery_config')->where('switch',1)->column('name','type');
  }
  //开奖记录查询
  public function queryData(){
    $data = input('post.');
    $data['where'] = json_decode($data['where'],true);
    //暂时屏蔽龙虎开奖数据
    // if($data['where']['type'] <= 1){
    //
    // }

    if($data['click'] == 1){             //启用查询时间
      if(!empty($data['where']['expect'])){
        $where[] = ['expect','=',$data['where']['expect']];
      }else{
        $start = $data['where']['start_time'];
        $end = $data['where']['end_time'];
        if(strtotime($start) > strtotime($end)){
          $start = [$end,$end = $start][0];
        }
        $start = strtotime($start.' '.'00:00:00');
        $end = strtotime($end.' '.'23:59:59');
        $where[] = ['create_time','between',[$start,$end]];
        $where1[] = ['a.create_time','between',[$start,$end]];
      }
    }

    if($data['where']['type'] == 26){
      $data['where']['type1'] = 2;
    }else if($data['where']['type'] == 27){
      $data['where']['type1'] = 12;
    }else{
      $data['where']['type1'] = $data['where']['type'];
    }
    // if($data['click'] == 1 && !empty($data['where']['expect'])){
    //   //print_r($data['where']['expect']);die();

    
    // }
    $where[] = ['type','=',$data['where']['type1']];
    $rs = Db::table('lottery_code')
      ->where($where)
      ->order('expect','DESC')
      ->paginate(10)
      ->toArray();
    foreach ($rs['data'] as $key => &$item) {
      if($data['where']['type'] == 26 || $data['where']['type'] == 27){
        $item['content'] = array_slice(explode(',',$item['content']),0,3);
      }else{
        $item['content'] = !empty($item['content']) ? explode(",",$item['content']): '';
      }
      $item['plus'] = $this->openPlus($item['content'],$data['where']['type']);
      $item['open_type'] = $this->openType($data['where']['type']);
      if($item['open_type'] == 3){
        foreach ($item['content'] as &$value) {
          $bit = lotteryL::codeType($value,date("Y"),$item['create_time']);
          $value = [$bit['code'],$bit['wave'][1],$bit['zodiac'][0]];
        }
      }else if($item['open_type'] == 4){
        $bit = [];
        if($item['type'] == 52){
          //1->S->黑桃 2->H->红桃 3->C->美化 4->D->方块
          $arr52 = ['spade', 'heart', 'club', 'diamond' ];
          $arr52_p = ['1'=>'A','11'=>'J','12'=>'Q','13'=>'K'];
          foreach ($item['content'] as $k4 => $ite) {
            $bit[$k4] = Lottery28::parsePoker($ite);
            $bit[$k4][1] = $arr52[$bit[$k4][1]];
            $bit[$k4][0] = $arr52_p[$bit[$k4][0]] ?? $bit[$k4][0];
          }
        }
        $item['content'] = [array_slice($bit, 0, 5), array_slice($bit, 5)];
        $item['title'] = ['蓝方','红方'];

        // print_r($item);
          // $bit = Lottery28::parsePoker($item['content']);
          // $bit = lotteryL::codeType($value,date("Y"));
          // $value = [$bit['code'],$bit['wave'][1],$bit['zodiac'][0]];
      }
      $item['create_time'] = date("Y-m-d H:i:s",$item['create_time']);
    }
    // print_r($rs);
    return $rs;
  }


    public function getHistory(){
        $data = input('post.');
        //$data = input('get.');

        $where['type'] = $data['type'];
        $config = Db::table('lottery_config')->where('type',$data['type'])->value('time_config');
        $config = json_decode($config,true);
        $num = (string)$config['num'];
        $rs = Db::table('lottery_code')
            ->where($where)
            ->order('expect','DESC')
            ->limit(10)
            ->select();
        foreach ($rs as $key => &$item) {
            if($where['type'] == 26 || $where['type'] == 27){
                $item['content'] = array_slice(explode(',',$item['content']),0,3);
            }else{
                $item['content'] = !empty($item['content']) ? explode(",",$item['content']): '';
            }
            $item['expect'] = substr($item['expect'],strlen($num) * -1);
            $item['plus'] = $this->openPlus($item['content'],$where['type']);
            $item['plus'][0]['data'] = explode(',',$item['plus'][0]['data']);
            if(isset($item['plus'][1]))
            {
                $item['plus'][1]['data'] = explode(',',$item['plus'][1]['data']);
            }
            $item['open_type'] = $this->openType($where['type']);
            if($item['open_type'] == 3){
                foreach ($item['content'] as &$value) {
                    $bit = lotteryL::codeType($value,date("Y"),$item['create_time']);
                    $value = [$bit['code'],$bit['wave'][1],$bit['zodiac'][0]];
                }
            }else if($item['open_type'] == 4){
                $bit = [];
                if($item['type'] == 52){
                    //1->S->黑桃 2->H->红桃 3->C->美化 4->D->方块
                    $arr52 = ['spade', 'heart', 'club', 'diamond' ];
                    $arr52_p = ['1'=>'A','11'=>'J','12'=>'Q','13'=>'K'];
                    foreach ($item['content'] as $k4 => $ite) {
                        $bit[$k4] = Lottery28::parsePoker($ite);
                        $bit[$k4][1] = $arr52[$bit[$k4][1]];
                        $bit[$k4][0] = $arr52_p[$bit[$k4][0]] ?? $bit[$k4][0];
                    }
                }
                $item['content'] = [array_slice($bit, 0, 5), array_slice($bit, 5)];
                $item['title'] = ['蓝方','红方'];

                // print_r($item);
                // $bit = Lottery28::parsePoker($item['content']);
                // $bit = lotteryL::codeType($value,date("Y"));
                // $value = [$bit['code'],$bit['wave'][1],$bit['zodiac'][0]];
            }
            $item['create_time'] = date("Y-m-d H:i:s",$item['create_time']);
        }
//        dump($rs);die;
        if($rs)
        {
            return $rs;
        }
        else
        {
            return false;
        }
        // print_r($rs);
    }
  //走势计算
  public function trend(){
    $obj = new Lottery;
    $data = $obj->post_data;
    // print_r($data);
    $rs = [];
    $return_data['name'] = $obj->lottery_config['name'];
    $return_data['code'] = 1;
    $return_data['type'] = 0;
    if(!empty($return_data['name'])){
      if(in_array($data['type'],[3,4,5,16,17,18,36,37,38,39])){//pk10
        $return_data['type'] = 1;
        $num = 5;
      } elseif (in_array($data['type'],[11,21])){//六合彩
        $return_data['type'] = 2;
        $num = 24;
      } elseif (in_array($data['type'],[10,14,15,40,41,42,43])){//快三
        $num = 3;
      } elseif (in_array($data['type'],[20])){
        $num = 10;
      } elseif ($data['type'] == 52) {
        $num = 6;
      }else{
        $num = 4;
      }
      // print_r($data['type']);die;
      if($data['type'] == 26){
        $data['type1'] = 2;
      }else if($data['type'] == 27){
        $data['type1'] = 12;
      }else{
        $data['type1'] = $data['type'];
      }

      if(in_array($data['type'],[24,25,26,27])){
        $return_data['now'] = Lottery28::lottery($data['type']);
        $return_data['now']['time'] = $return_data['now']['time']+30;
      }else{
        // $lottery = Db::table('lottery_config')->field('time_config')->where('type','=',$data['type'])->find();
        // print_r($lottery);
        $return_data['now'] = $obj->calculationData();
        // print_r($return_data['now']);
      }

      if($return_data['now']['expect'] == 0 && $return_data['now']['time'] == 0){
        $return_data['code'] = -2;
      }

      if($data['expect']==0 || Db::table('lottery_code')->field('expect')->where('type','=',$data['type1'])->where('expect','>',$data['expect'])->find() ){
        $return_data['data'] = Db::table('lottery_code')->field('expect,content,create_time')->order('expect','DESC')->where('type','=',$data['type1'])->limit(20)->select();
      }else{
        $return_data['data'] = '';
      }
      if(!empty($return_data['data'])){

        $poker = [
          '1' => 'A',
          '11' => 'J',
          '12' => 'Q',
          '13' => 'K',
        ];

        foreach ($return_data['data'] as $key => &$value) {

          if(in_array($data['type'],[26,27])){
            $value['content'] = array_slice(explode(',',$value['content']),0,3);
          }else{
            $value['content'] = explode(',',$value['content']);
          }
          foreach ($value['content'] as $k => &$v) {
            if($data['type'] == 52){
              $v = floor($v/4) + 1;
            }
            $value['single'][$k] = ($v%2 == 1 ? '1' : '2');
            $value['big'][$k] = ($v > $num ? '1' : '2');
            //特殊处理
            if($num == 24){
              $bit = lotteryL::codeType($v,date("Y"),$value['create_time']);
              $value['class'][$k] = $bit['wave'][1];
              $value['zodiac'][$k] = $bit['zodiac'][0];
            } elseif ($data['type'] == 52){
              $v = $poker[$v] ?? $v;
            }
          }

        }
      }
    }else{
      $return_data['code'] = -1;
    }
    return $return_data;
  }
  public function trendPc(){
    $data = input('post.');
    if($data['id'] == 26){
      $data['id1'] = 2;
    }else if($data['id'] == 27){
      $data['id1'] = 12;
    }else{
      $data['id1'] = $data['id'];
    }

    $rs = Db::table('lottery_code')
      ->field('expect,content')
      ->where('type','=',$data['id1'])
      ->order('expect','DESC')
      ->paginate(20)
      ->toArray();
    //$arr = [
      //a   b    c    d    ac     bc    ad     bd
      //'大','小','单','双','大单','小单','大双','小双'
    //];
    foreach ($rs['data'] as $key => &$value) {
      if(in_array($data['id'],[26,27])){
        $value['content'] = array_slice(explode(',',$value['content']),0,3);
      }else{
        $value['content'] = explode(',',$value['content']);
      }
      $value['plus'] = array_sum($value['content']);
      $bit1 = $value['plus'] >= 14 ? 'a' : 'b';
      $bit2 = $value['plus']%2 == 1 ? 'c' : 'd';
      $value['list'] = [$bit1,$bit2,($bit1.$bit2)];
    }
    return $rs;
  }
  //开奖类型返回
  static function openType($type){
    $data = 0;                          //普通
    if( in_array($type,[3,4,5,16,17,18,36,37,38,39]) ){      //pk10一类
      $data = 1;
    }else if( in_array($type,[23]) ){   //幸运农场
      $data = 2;
    }else if( in_array($type,[11,21]) ){//6合彩
      $data = 3;
    }else if( in_array($type,[0,1,52]) ){
      $data = 4;
    }
    return $data;
  }

  //联赛目录
  static function LeagueMatch(){
    $arr = [
      '世界杯','英超','西甲','欧冠','中超','德甲','意甲','法甲','澳甲',
      'K联赛','卡星赛','酋超','沙联赛','印超','J1联','南非超','埃超','美职联','墨甲',
      '斯伐超','土超','英乙','匈甲','苏甲','丹甲','瑞典甲','芬超','白俄超','丹超',
      '希超','瑞典超','俄超','英冠','德乙','比乙','亚美超','挪超','意乙','克甲',
      '葡甲','荷甲','以超','威超','苏超','罗甲','葡超','乌超','爱甲','西乙',
      '马超','波超','塞浦甲','德丙','英甲','奥乙','苏冠','瑞士超','保甲','爱超',
      '立甲','比甲','奥甲','捷甲','塞尔超','斯甲','拉超','冰超','挪超','法乙',
      '巴甲','圣州杯','巴乙','巴拉圭甲','巴丙','阿甲','哥甲','秘甲','玻甲','厄甲',
      '智甲','乌甲'
    ];
  }
  //开奖和值计算
  static function openPlus($val='',$type){

    if(empty($val)){
      return '';
    }
    $plus = array_sum($val);

    if(in_array($type,[2,12,13,6,7,8,9,28])){//ssc & ffc
      $rs[0]['name'] = '龙虎';
      $rs[0]['data'] = $val[0] > $val[4] ? '龙' :($val[0] == $val[4] ? '和' :'虎');
      $rs[1]['name'] = '和值';
      $rs[1]['data'] = $plus.','.($plus <=22 ? '小':'大').','.($plus%2 == 0 ? '双' : '单');
    }else if(in_array($type,[3,4,5,36,37,38,39,51])){//pk10系列
      $rs[0]['name'] = '冠亚和值';
      $plus = $val[0] + $val[1];
      $rs[0]['data'] = $plus.','.($plus <=10 ? '小':'大').','.($plus%2 == 0 ? '双' : '单');
      $rs[1]['name'] = '1~5龙虎';
      $bit = [];
      for($i=0;$i<5;$i++){
        $bit[$i] = $val[$i] > $val[9-$i] ? '龙' : '虎';
      }
      $rs[1]['data'] = implode(',',$bit);

    }
    // else if(in_array($type,[6,7,8,9,28])){//ffc
    //   $rs[0]['name'] = '龙虎';
    //   $rs[0]['data'] = $val[0] > $val[4] ? '龙' :($val[0] = $val[4] ? '和' :'虎');
    //   $rs[1]['name'] = '和值';
    //   $rs[1]['data'] = $plus.','.($plus <=22 ? '小':'大').','.($plus%2 == 0 ? '双' : '单');
    // }
    else if(in_array($type,[10,14,15,30,31,32,33,34,40,41,42,43])){ //k3
      $rs[0]['name'] = '和值';
      $rs[0]['data'] = $plus.','.($plus <=10 ? '小':'大').','.($plus%2 == 0 ? '双' : '单');
    }else if(in_array($type,[11,21])){                              //js6h &&gc
      $rs[0]['name'] = '特码';
      $rs[0]['data'] = $val[6].','.($val[6] <=24 ? '小':'大').','.($val[6] == 49 ? '和':$val[6]%2==0?'双':'单');
      $rs[1]['name'] = '总和';
      $rs[1]['data'] = $plus.','.($plus <=174 ? '小':'大').','.($plus%2 == 0 ? '双' : '单');
    }else if(in_array($type,[16,17,18,44,45,46,47,48,49])){         //11x5
      $rs[0]['name'] = '和值';
      $rs[0]['data'] = $plus.','.($plus ==30 ? '和':$plus < 30 ? '小' : '大').','.($plus%2 == 0 ? '双' : '单');
    }else if(in_array($type,[24,25,26,27,57,58]) || in_array($type,[19,22])){//pc28 & 福彩3D
      $rs[0]['name'] = '和值';
      $rs[0]['data'] = $plus.','.($plus <= 13 ? '小' : '大').','.($plus%2 == 0 ? '双' : '单');
    }
    // else if(in_array($type,[19,22])){//3d
    //   $rs[0]['name'] = '和值';
    //   $rs[0]['data'] = $plus.','.($plus <= 13 ? '小' : '大').','.($plus%2 == 0 ? '双' : '单');
    // }
    else if(in_array($type,[23])){//xync
      $rs[0]['name'] = '和值';
      $rs[0]['data'] = $plus.','.($plus ==84 ? '和':$plus < 84 ? '小' : '大').','.($plus%2 == 0 ? '双' : '单');
    }else if(in_array($type,[20,50])){//gxkl10f gdklsf
      $rs[0]['name'] = '和值';
      $rs[0]['data'] = $plus.','.($plus ==55 ? '和':$plus < 55 ? '小' : '大').','.($plus%2 == 0 ? '双' : '单');
    }else{
      $rs[0]['name'] = "";
      $rs[0]['data'] = "";
    }
    // else if(in_array($type,[21])){//gc
    //   print_r('11');
    // }
    return $rs;
  }
  //获取中奖信息
    public function getWinning()
    {
        $info = Db::table('betting')->alias('b')
            ->join('lottery_config lc','lc.type=b.type')
            ->join('user u','u.id=b.user_id')
            ->where('b.state',1)
            ->where('b.win','>',0)
            ->order('b.create_time desc')
            ->field('b.win,u.username,u.photo,lc.name')
            ->limit(10)
            ->select();

        foreach ($info as &$i)
        {
            $len = strlen($i['username']);
            if($len > 6)
            {
                $i['username'] = substr($i['username'],0,3).'***';
            }
            else
            {
                $i['username'] = substr($i['username'],0,2).'***';
            }
        }
        $info[] = $info[0];
        return $info;
    }

    public function getSocketMsg()
    {
      //  var_dump("====getSocketMsg===");
    
        $to = ''; //接收者uuid
        $pushdata = ["id"=>"世界杯","name"=>"英超","age"=>"张三"];
       return ApiSocketMsg::pushMsg($pushdata, $to);
       
        
    }
}
