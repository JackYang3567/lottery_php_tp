<?php
namespace app\home\controller;
use think\Db;
use app\home\model\Betting;
use app\home\model\User;
use app\home\model\UserConfig;
use app\home\model\RobotBet;
use app\home\model\LotteryCode;
use think\facade\Session;


class Lottery28 extends Common
{
    public function getData(){
      //$time_start = microtime(true); //----------------------------
      $data = input('post.');
      if($data['type'] == 26){
        $data['type1'] = 2;
      }else if($data['type'] == 27){
        $data['type1'] = 12;
      }else{
        $data['type1'] = $data['type'];
      }

      $lottery = $this->lottery($data['type']);//获取当前期号与时间

      //查询投注是否有最大一期的where,
      $bet = [
        [ 'create_time','>',$data['peoplet'] ],
        [ 'type','=',$data['type'] ],
      ];
      // print_r($data['type']);die;
      //如果不等于52 则加一个房间判断
      if($data['type'] != 52){
        $bet[] = [ 'other','=',$data['level'] ];
      }

      //查询期号是否有大于传上来的值
      $exp = [
        [ 'expect','>',$data['number'] ],
        [ 'type','=',$data['type1'] ]
      ];

      if(betting::field('id')->where($bet)->find() || RobotBet::where($bet)->find() || LotteryCode::where($exp)->field('expect')->find() || $data['time'] > 0 || $data['number'] == 0){
            $return_data = [
              'expect' =>$lottery['expect'],//当前期号
              'time' => $lottery['time'],  //当前时间
              'number' => $this->getExpect($data)//请求最新号码
            ];
            if($data['type'] == 52){
                $return_data['people'] = $this->brnnPeo($data);  //投注数据处理（百人牛牛）
            }else{
                $return_data['people'] = $this->people($data); //投注数据处理
            }
             // $time_end = microtime(true); //--------------------------------
             // $time = floor(($time_end - $time_start)*1000);
             // echo '全部执行时间'.$time.'ms';

          $config = json_decode(SystemConfig::get(63)['value'],true);
          if($config['type']){
              if(!(Session::get('time_control'))){
                  Session::set('time_control',1);
              }else{
                  $num =  Session::get('time_control');
                  $num++;
                  Session::set('time_control',$num);
              }
              if(Session::get('time_control') > $config['time_control']){
                  $this->Robot($config['Robot_config'],$return_data['expect']);
                  Session::set('time_control',1);
              }
          }


             return json($return_data);
      }else{
        return '';
      }
    }
    public function getDatas(){
        $data = input('post.');
        $lottery = $this->lottery($data['type']);//获取当前期号与时间
        //查询投注是否有最大一期的where,
        $bet = [
            [ 'id','>',$data['people'] ],
            [ 'type','=',$data['type'] ],
        ];
        //print_r($data['type']);die();
        //查询期号是否有大于传上来的值
        $exp = [
            [ 'expect','>',$data['number'] ],
            [ 'type','=',$data['type'] ]
        ];
        if(betting::field('id')
                ->where($bet)
                ->find() || Db::table('lottery_code')
                ->field('expect')
                ->where($exp)
                ->find() || $data['time'] > 0 || $data['number'] == 0){
            $return_data = [
                'expect' =>$lottery['expect'],//当前期号
                'time' => $lottery['time'],  //当前时间
                'number' => $this->getExpect($data)//请求最新号码
            ];
            if($data['type'] == 59) {
                $return_data['people'] = $this->peoples($data); //投注数据处理
            }

            return  $return_data;
        }
    }
    //获取百人牛牛最新投注
    public function brnnPeo($data){
      $userdata = $this->checkLogin();
      if($userdata['code'] == 0){
         $userdata['data']['id'] = 0;
      }
      $bet = Db::table('betting')
              ->where('create_time','>',$data['peoplet'])
              // ->where('other','=',$data['level'])
              ->where('type','=',$data['type'])
              ->order('id','DESC')->limit(5)->select();

      //加入机器人
      $bet_r = Db::table('robot_bet')
                ->where('create_time','>',$data['peoplet'])
                ->where('type','=',$data['type'])
                ->order('id','DESC')->limit(5)->select();
      //合并
      $bet_all = array_merge($bet,$bet_r);

      // print_r($bet_all);die;
      if(empty($bet_all)){
        return '';
      }else{
        //排序
        usort($bet_all, function($a, $b) {
            if($a['create_time'] == $b['create_time']){
              return 0;
            }else{
              return ($a['create_time'] > $b['create_time']) ? -1:1;
            }
        });
        //如果第一次请求就截取5位
        if($data['peoplet'] == 0 && !empty($bet) && !empty($bet_r) && count($bet_all) > 5 ){
          $bet_all = array_slice($bet_all,0,5);
        }

        $return = [];
        //获取配置文件
        $config = Db::table('lottery_config')->field('basic_config')->where('type','=',$data['type'])->find()['basic_config'];

        $config = json_decode($config,true);
        $arr = [
          '1' => 'A',
          '11' => 'J',
          '12' => 'Q',
          '13' => 'K'
        ];
        // print_r($bet_all);die;
        foreach ($bet_all as $key => $value) {
          $value['content'] = json_decode($value['content'],true);
          //用户和机器人分开设置
          if(isset($value['user_id'])){
            //请求出用户的设置
            $value['user'] = Db::table('user')
                          ->alias('a')
                          ->leftJoin('user_config b','a.id = b.user_id')
                          ->where('a.id',$value['user_id'])
                          ->order('a.create_time DESC')
                          ->field('a.username,a.photo,a.type,b.backstage')
                          ->find();
          }else{
            $value['user'] = Db::table('robot')
                          ->where('id','=',$value['robot_id'])
                          ->find();
          }

          // print_r($value);die;
          $money = floor($value['money'] / $this->betNum($value['content']));
          foreach ($value['content'] as $k1 => $v1) {
            // $money = floor($value['money'] / count($v1));
            foreach ($v1 as $k2 => $v2){
              // print_r($config[$k1]['items'][$k2]['name']);
              //用户
              $type = 1;
              if(isset($value['user_id'])){
                if($userdata['code'] > 0 && ($value['user_id'] == $userdata['data']['id'])){
                  $type = 2;
                }
                if( $value['user']['type'] == 1 ){
                  $name = '试玩用户';
                }else{
                  $strl = mb_strlen($value['user']['username']);
                  $name = mb_substr($value['user']['username'],0,floor($strl/2) ).'**';
                }
              }else{
                $strl = mb_strlen($value['user']['name']);
                $name = mb_substr($value['user']['name'],0,floor($strl/2) ).'**';
              }

              $return[] = [
                'username' =>  $name,
                'list' => $value['id'],
                'listt' => $value['create_time'],
                'type' => $type,
                'code' =>  $config[$k1]['name'].'_'.($arr[$config[$k1]['items'][$k2]['name']] ?? $config[$k1]['items'][$k2]['name']),
                'money' => $money,
                'expect' => $value['expect'],
                'menu' =>$k1.','.$k2,
                'photo' => $value['user']['photo'],
                'time' => date('Y-m-d H:i:s',$value['create_time'])
              ];
            }
          }
        }
      }
      return $return;
    }
    //获取当前房间最新投注
    public function peoples($data){
        $userdata = $this->checkLogin();
        if($userdata['code'] == 0){
            $userdata['data']['id'] = 0;
        }
        $bet = Db::table('betting')
            ->where('create_time','>',$data['peoplet'])
            ->where('type','=',$data['type'])
            ->order('id DESC')->limit(5)->select();
        //加入机器人投注
        //print_r($bet);
        $datas = Db::table('lottery_config')
            ->where(['type'=>59])
            ->find();
        $tais = json_decode($datas['basic_config'],true);

        //pr($tais);die;
        if(empty($bet)){
            $return = '';
        }else{
            foreach ($bet as $key => $value) {
                //pr($value);die;
                $value['content'] = json_decode($value['content'],true);
                $value['user'] = Db::table('user')
                    ->alias('a')
                    ->leftJoin('user_config b','a.id = b.user_id')
                    ->where('a.id',$value['user_id'])
                    ->order('a.create_time DESC')
                    ->field('a.username,a.type,b.backstage')
                    ->find();
                //pr($value['user']);die;
                // print_r($value['user']);
                if(!empty($value['user']['backstage'])){
                    $value['user']['backstage'] = json_decode($value['user']['backstage'],true);
                }
                foreach ($value['content'] as $k => $v) {
                    if(!empty($value['user']['backstage']) ){
                        foreach ($v as $k2 => $v3) {
                            foreach ($tais as $k3 => $v4) {
                                foreach ($v4['items'] as $k4 => $v5) {
                                    $v['code'] = isset($value['user']['backstage']['bet_money']) ? $tais[$k3]['name'] . '_' . $v4['items'][$k4]['name'] : "";
                                    $v['money'] = isset($value['user']['backstage']['bet_money']) ? $value['money'] : "";
                                    $v['menu'] =  $k3.','. $k4;
                                }
                            }
                        }
                    }else{
                        $v['code'] = is_numeric($v['code_1']['code']);
                    }
                    $v['id'] = $value['user_id'];
                    $v['time'] = date('Y-m-d H:i:s',$value['create_time']);
                    $v['expect'] = $value['expect'];
                    //print_r($value['user']['backstage']);die;
                    $strl = mb_strlen($value['user']['username']);
                    $v['name'] = $value['user']['type'] == 1 ? '试玩用户' :mb_substr($value['user']['username'],0,floor($strl/2) ).'**';
                    $v['type'] = $userdata['data']['id'] == $value['user_id'] ? 2 : 1 ;
                    $v['list'] = $value['id'];
                    $v['listt'] = $value['create_time'];
                    $return[] = $v;
                }
            }
        }
        return $return;
    }
    /**
     * 获取通用格式注数
     */
    public function betNum($val){
  		$i = 0;
  		foreach($val as $item){
  			foreach($item as $item1){
  				$i+=1;
  			}
  		}
  		return $i;
  	}

    /**
     * 获取当前房间最新投注 只有28系列
     * @param array $data
     * @return array
     */
    public function people($data){
      $userdata = $this->checkLogin();
      if($userdata['code'] == 0){
         $userdata['data']['id'] = 0;
      }
                            /*彩种类型      时间戳           房间等级        数量*/
     $bet = Betting::listBet($data['type'],$data['peoplet'],$data['level'],5)->toArray();
      //print_r($data);
     if(!empty($bet)){
       foreach ($bet as $value) {
         foreach ($value['chg28'] as $value1) {
           // print_r();
           $return[] = [
             'code' => $value1->code,
             'money' => $value1->money,
             'explain' => $value1->explain,
             'odds' => $value1->odds,
             'id' => $value['user']->id,
             'time' => $value['create_time'],
             'expect' => $value['expect'],
             'name' => $value['user']->name,
             'type' => $userdata['data']['id'] == $value['user_id'] ? 2 : 1 ,
             'list' => $value['id'],
             'listt' => strtotime($value['create_time']),
             'photo' => $value['user']->photo,
           ];
         }
       }
     }else{
       return '';
     }
     return $return;
    }

    /**
     * 获取开奖号码
     *  @param array
     *  @return array
     */
    public function getExpect($data){
      //如果expectid为0获取最新的期数与开奖号 30 条

      if($data['type'] == 26 || $data['type'] == 27){
        $where = ['type'=>$data['type1']];
      }else{
        $where = ['type'=>$data['type']];
      }

      $rs = Db::table('lottery_code')
                ->where($where)
                ->where('expect','>',$data['number'])
                ->order('expect DESC')->limit(30)->select();
      if($data['type'] == 26 || $data['type'] == 27){
        foreach ($rs as $key => &$value) {
          $value['content'] = array_slice(explode(',',$value['content']),0,3);
          $value['content'] = join(',',$value['content'] );
        }
      }
      // print_r($rs);
      // die;
      if(!empty($rs)){
        $return_data = [];
        foreach ($rs as $key => $value) {
          $return_data[$key]['expect'] = $value['expect'];
          if($data['type'] == 52){
            $return_data[$key]['num'] = $this->brnnConfig($value['content']);
          }elseif ($data['type'] == 59) {
              $return_data[$key]['num'] = str_replace(',','',$value['content']);
          }else{
            $return_data[$key]['num'] = $value['content'];
          }
            $io = str_split($value['content']);
            $ky = array_sum($io);
            if ($ky >=11) {
                if (($ky%2) == 0) {
                    $return_data[$key]['tai'] = '总和'.':'.$ky .','. '大'.','.'双';
                }elseif (($ky%2) != 0) {
                    $return_data[$key]['tai'] = '总和'.':'.$ky .','. '大'.','.'单';
                }
                /*$return_data[$key]['tai'] = '总和'.$ky . '大'*/
            }elseif ($ky <=10) {
                if (($ky%2) == 0) {
                    $return_data[$key]['tai'] = '总和'.':'.$ky .','. '小'.','.'双';
                }elseif (($ky%2) != 0) {
                    $return_data[$key]['tai'] = '总和'.':'.$ky .','. '小'.','.'单';
                }
            }
        }
        //如果请求的百人牛牛则需要单独处理


      }else{
        $return_data = '';
      }
      return $return_data;
    }

    public function brnnConfig($val){

      if(!isset($val)){return;}
      // $val = explode(',',$val);
      // var_dump( substr('123456',-1) );die;
      // $pokers = range(0, 51); shuffle() array_rand();

      $openPoker = explode(',', $val);

      $parseData = [];

      foreach ($openPoker as $item) {
        $parseData[] = $this->parsePoker($item);
      }

      $userPoker = [array_slice($parseData, 0, 5), array_slice($parseData, 5)];
      $_openPoker = [array_slice($openPoker, 0, 5), array_slice($openPoker, 5)];

      $p1 = $this->getNnSize($userPoker[0]);
      $p2 = $this->getNnSize($userPoker[1]);

      $p1win = 0;

      if ($p1 == $p2) {
        $p1win = max($_openPoker[0]) > max($_openPoker[1]);
      } else {
        $p1win = $p1 > $p2;
      }
      // print_r($userPoker);die;
      $narr = [
        '1' => '牛一',
        '2' => '牛二',
        '3' => '牛三',
        '4' => '牛四',
        '5' => '牛五',
        '6' => '牛六',
        '7' => '牛七',
        '8' => '牛八',
        '9' => '牛九',
        '10' => '牛牛',
        '11' => '花色牛',
      ];
      $return = [
          //左边->蓝方
          [
            'code'=>$userPoker[0],
            'type' =>($narr[$p1] ?? '无牛'),
            'win' => $p1win ? '1' : '0',           //0输 1胜
          ],
          //右边->红方
          [
            'code'=>$userPoker[1],
            'type' =>($narr[$p2] ?? '无牛'),
            'win' => $p1win ? '0':'1',           //0输 1胜
          ]
      ];
      return $return;
      //返回结果的数据展示
      // $return = [
      //   //左边->蓝方
      //   [
      //     'code'=>[1,2,3,5,6],
      //     'flower'=>[1,2,3,4,1], //1->S->黑桃 2->H->红桃 3->C->美化 4->D->方块
      //     'type' =>'牛1',
      //     'win' => '0',           //0输 1胜
      //   ],
      //   //右边->红方
      //   [
      //     'code'=>[10,11,12,13,12],
      //     'flower'=>[1,2,3,4,1], //1->S->黑桃 2->H->红桃 3->C->美化 4->D->方块
      //     'type' =>'牛1',
      //     'win' => '1',           //0输 1胜
      //   ]
      // ];
    }
    //获取转换后的牌和花色  派奖也在使用
    static function parsePoker($num)
    {
      //0->S->黑桃 1->H->红桃 2->C->梅花 3->D->方块
      $number = floor($num / 4) + 1;
      $hua_num = $num % 4;
      // $hua = $hua_str[$hua_num];
      return [$number, $hua_num];
    }
    //获取那副牌的牛牛  派奖也在使用
    public function getNnSize($poker)
    {
      $i = 0;

      $poker_sum = 0;
      foreach ($poker as $item) {
        if ($item[0] > 10) {
          $i ++;
        }
        $poker_sum += ($item[0] > 10 ? 10 : $item[0]);
      }

      // 五花牛
      if ($i == 5) {
        return 11;
      }

      $res = $this->strand($poker, 3);

      // 无牛
      $is_niu = -1;

      foreach ($res as $item) {
        $sum = ( $item[0][0]>10 ? 10 : $item[0][0] ) + ( $item[1][0] > 10 ? 10 : $item[1][0] ) + ( $item[2][0]>10 ? 10 : $item[2][0] );
        if ($sum % 10 == 0) {
          $is_niu = $poker_sum % 10;
          break;
        }
      }
      if ($is_niu == 0) {
        $is_niu = 10;
      }
      return $is_niu;
    }

    // public function test()
    // {
    //   $a = range(0, 9);
    //   return $this->strand($a, 3);
    // }

    /**
     * 组合排列
     * @param array $val 要排列的数组 [1,2,3,4,5,6]
     */
    static function strand($val, $num)
    {
      //2018,11,07
      $rs = [];
      $list = 1;
      for ($i = 0; $i < pow(2, count($val)); $i ++){
          if(substr_count(decbin($i),1) != $num){
            continue;
          }
          $a = 0;
          $b = [];
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
      //旧版
      // $rs = [];
      // for ($i = 0; $i < pow(2, count($val)); $i ++){
      //     $a = 0;
      //     $b = [];
      //     for ($j = 0; $j < count($val); $j ++){
      //         if ($i >> $j & 1){
      //             $a ++;
      //             $b[] = $val[$j];
      //         }
      //     }
      //     if ($a == $num){
      //         $rs[] = $b;
      //     }
      // }
      // return $rs;
    }

    static function lottery($type){//开奖期 和 倒计时

      $Basics = [
        'exp'=>[
         '24'=>867158, //北京28
         '25'=>2272125,//加拿大28
         '26'=>'',     //重庆28
         '27'=>''      //新疆28
        ],
        'time'=>[
          '24'=>'2018-01-15 09:05:00',
          '25'=>'2018-04-16 21:00:30',
          '26'=>'',
          '27'=>''
        ]
      ];
      // //基数期数
      // private $expect = ['24'=>867158, //北京28
      //                    '25'=>2272125,//加拿大28
      //                    '26'=>'',     //重庆28
      //                    '27'=>''];    //新疆28
      // //基数时间
      // private $expecttime =['24'=>'2018-01-15 09:05:00',
      //                       '25'=>'2018-04-16 21:00:30',
      //                       '26'=>'',
      //                       '27'=>''];

        $nowtime = date("Y-m-d H:i:s"); //当前时间
        $qishu = 0;//返回期数
        $yushu = 0;//返回时间
        // $retime = 0;//维护时间
        // print_r($expect);

        if($type == '24'){ //北京28--------------------------------------------------------------------------------------
          $expect = $Basics['exp'][$type];//期数
          $time = $Basics['time'][$type];//时间
          if(strtotime(date("H:i:s")) > strtotime("09:05:00") && strtotime(date("H:i:s")) <  strtotime("23:55:00")){
            $date=floor((strtotime($nowtime)-strtotime($time))/86400); //天
            $second=floor((strtotime($nowtime)-strtotime($time))%86400);  //天数剩余的秒
            // print_r('时间'.strtotime($time));
            $qishu = $expect+$date*179+floor($second/300) - 703; //增加的期数 702过年期间多出来的期数
            $yushu = (300 - $second%300); //当前开奖剩余时间
            // $qishu = (Db::table('lottery_code')->where('type',$type)->max('expect')) + 1;
          }
        }else if($type == '25'){//加拿大28-----------------------------------------------------------------------------
          $qishu = '';
          $yushu = '';

          if( self::getJieQi(date('Y'),date('m'),date('d'))['name1'] == 1 ){
            //夏至时间表 
            $jnd_time = [
              'start' => '19:00:00',
              'end' => '20:00:00',
              'one_end' => '21:00:00',
              'normal_time' => '20:01:00',//获取期数 -> 正常结束后的时间 
              'actual_time' => '19:30:00',// 
            ];
          }else{
            //冬至时间表
            $jnd_time = [
              'start' =>  '20:00:00',//维护开始时间
              'end' => '21:00:00',   //维护结束时间
              'one_end' => '22:00:00',//星期一结束时间
              'normal_time' => '21:01:00',//获取期数 -> 正常结束后的时间 
              'actual_time' => '20:30:00',//
            ];
          }


          //strtotime(date("H:i:s")) >= (date('w')==1?strtotime("22:00:00"):strtotime("21:00:00")) || strtotime(date("H:i:s")) <=  strtotime("20:00:00")//冬至时间表
          //strtotime(date("H:i:s")) >= (date('w')==1?strtotime("21:00:00"):strtotime("20:00:00")) || strtotime(date("H:i:s")) <=  strtotime("19:00:00")//夏至时间表
          if(strtotime(date("H:i:s")) >= (date('w')==1?strtotime($jnd_time['one_end']):strtotime($jnd_time['end'])) || strtotime(date("H:i:s")) <=  strtotime($jnd_time['start'])){//夏至时间表
          //新版本--------------------------------------------
            if(time() < strtotime(date('Y-m-d '.$jnd_time['start']))){
              //请求实际维护后第一期的时间(前一天)
              $request_ = strtotime(date("Y-m-d",strtotime("-1 day")).' '.$jnd_time['actual_time']);
              //正常维护后的第一期的时间(前一天)
              $request = strtotime(date("Y-m-d",strtotime("-1 day")).' '.$jnd_time['normal_time']);
            }else{
              //请求实际维护后第一期的时间(今天)
              $request_ = strtotime(date("Y-m-d").' '.$jnd_time['actual_time'] );
              //正常维护后的第一期的时间
              $request = strtotime(date("Y-m-d").' '.$jnd_time['normal_time'] );
            }
            //很重要 每次维护后第一期 相差不得超过210秒 否则会出错
            $jnd28 = Db::table('lottery_code')
                  ->where('type',$type)
                  ->where('create_time','>',$request_)
                  ->order('expect','ASC')
                  ->find();
            //模拟数据
            // $jnd28['expect'] = 2277988;                               //查询出维护后第一期期号
            // $jnd28['create_time'] = strtotime('2018-05-01 20:01:24'); //查询出维护后的第一期时间
            if($jnd28){
              //正常计算的时间 每天20:01:00
              $now = time() - $request;
              //实际开奖时间与计算时间差
              //strtotime($jnd28['create_time'])
              $nowx = $jnd28['create_time'] - $request;
              //相差了多少期取整 小于210 说明没有多余期数
              $down = floor($nowx/210);
              //正常计算期数 + 维护后的基数 - 相差的期数
              $qishu = ceil($now/210) + $jnd28['expect'] - $down;
              $yushu = 210-floor($now%210);
            }
          }
        }else if($type == '26'){//重庆28--------------------------------------------------------------------------------
          
          if(strtotime(date("H:i:s")) < strtotime("02:00:00") || strtotime(date("H:i:s")) >  strtotime("10:00:00")){
           
            if(strtotime(date("H:i:s")) < strtotime("02:00:00")){
              $data_qishu = strtotime(date("H:i:s")) - strtotime(date("Y-m-d"));
              $qishu = floor($data_qishu/300);
              $yushu = 300 - floor($data_qishu%300);
            }else if(strtotime(date("H:i:s")) > strtotime("10:00:00") && strtotime(date("H:i:s")) < strtotime("22:00:00")){
              $data_qishu = strtotime(date("H:i:s")) - strtotime(date("Y-m-d 10:00:00"));
              $qishu = floor($data_qishu/600) + 25;
              $yushu = 600 - floor($data_qishu%600);
            }else if(strtotime(date("H:i:s")) >= strtotime("22:00:00")){
              $data_qishu = strtotime(date("H:i:s")) - strtotime(date("Y-m-d 10:00:00"));
              $qishu = floor($data_qishu/300) + 97;
              $yushu = 300 - floor($data_qishu%300);
            }
            $qishu = str_pad($qishu,3,"0",STR_PAD_LEFT);
            $qishu = date("Y").date("m").date("d").$qishu;
          
            // $qishu = substr($qishu,4);
          }
        }else if($type == '27'){//新疆28--------------------------------------------------------------------------------
          if(strtotime(date("H:i:s")) < strtotime("02:00:00") || strtotime(date("H:i:s")) >  strtotime("10:00:00")){
            $data_qishu = strtotime(date("H:i:s")) - strtotime(date("Y-m-d 10:00:00"));
            $qishu = ceil($data_qishu/600);
            $yushu = 600 - floor($data_qishu%600);
            $qishu = str_pad($qishu,2,"0",STR_PAD_LEFT);
            $qishu = date("Y").date("m").date("d").$qishu;
            // print_r(date("m"));
            // $qishu = substr($qishu,4);
          }
        }else{  //这一部分为有彩种有规则情况下，无规则不适用
           $config = Db::table('lottery_config')->field('time_config')->where('type','=',$type)->find()['time_config'];
           $config = json_decode($config,true);
           //开始时间
           $start = strtotime(date('Y-m-d '.$config['start_time']));
           if( time() >= $start ){
             $qishu = (time() - $start)/($config['cha']*60)+1;
             $yushu = ($config['cha']*60) - (time() - $start)%($config['cha']*60);
             $qishu = date('Ymd') . sprintf("%03d", $qishu);
             // print_r($yushu);
             // echo '--';
             // print_r($qishu);
             // die;
           }
           // $li =  date('Y-m-d');
           // print_r( strtotime(date('Y-m-d '.$config['start_time'])) );die;
        }
        // print_r($qishu);($yushu-30)
        // print_r($yushu);$qishu
        if(in_array($type,[24,25,26,27])){
          return ['expect'=>$qishu,'time'=>(intval($yushu)-30)];
        }else{
          return ['expect'=>$qishu,'time'=>intval($yushu - $config['desc'])];
        }
    }

    public function betting(){    //28投注方法
   
      $data = input("post.");
      // print_r($data);
      $return_data = [
        'code' => 1,
        'msg' => '投注成功'
      ];
      // print_r($data);
      // return;
      $lottery = $this->lottery($data['lottery']); //获取当前期号与倒计时
      $userdata = $this->checkLogin();
      if($userdata['code'] < 1){                   //判断是否登录了
        $return_data['code'] = -1;
        $return_data['msg'] = '请登录';
      }else if($userdata['data']['status'] == 1){
        $return_data['code'] = -12;
        $return_data['msg'] = '您的帐号已被冻结,无法投注';
      }else if($lottery['time'] < 0){              //判断当前时间是否可以投注
        $return_data['code'] = -10;
        $return_data['msg'] = '已封盘,请停止投注';
      }else if($lottery['expect'] == 0){
        $return_data['code'] = -11;
        $return_data['msg'] = '维护中...';
      }else{
        //出问题时 需要判定是否在正确时间内 或者 投注正确期数
        // $wrong = [//配置错误时间 超过该时间则表示开奖错误
        //   '24' => 300, //北京28 5分钟一期
        //   '25' => 210, //加拿大28 3分30一期
        //   '26' => 600, //重庆28 10分钟一期
        //   '27' => 600, //新疆28 10分钟一期
        // ];

        //判断投注内容是否正确
        if(!$this->checkAll28($data)){
          $return_data['code'] = -15;
          $return_data['msg'] = '特殊错误';
          return $return_data;
        }
        //初始化房间规则
          $room_rule = [
            //初级房
            '0' => ['single'=>['max'=>20000,'bet'=>5000],
                    'double'=>['max'=>5000,'bet'=>500],
                    'color'=>['max'=>5000,'bet'=>500],
                    'other'=>['max'=>5000,'bet'=>500],
                    'min' => 0,
                  ],
            //中级房
            '1' => ['single'=>['max'=>50000,'bet'=>10000], //大 小 单 双
                    'double'=>['max'=>10000,'bet'=>2000],  //组合单点
                    'color'=>['max'=>10000,'bet'=>2000],   //波色
                    'other'=>['max'=>10000,'bet'=>2000],   //极大极小豹子
                    'min' => 0,                           //单次注最低限额
                  ],
            //高级房
            '2' => ['single'=>['max'=>100000,'bet'=>20000],
                    'double'=>['max'=>20000,'bet'=>5000],
                    'color'=>['max'=>20000,'bet'=>5000],
                    'other'=>['max'=>20000,'bet'=>5000],
                    'min' => 0,
                  ],
            'note' => ['single' => '大小单双',
                      'double' => '组合单点',
                      'color' => '红蓝绿波',
                      'other' => '极大极小豹子',
                      'min'   => '单次最低投注'
                  ]
          ];
           
          //查询当前房间彩种规则配置
          $db_room = Db::table('room')->field('content')->where('type','=',$data['lottery'])->where('level','=',$data['room'])->find();
          $room_rule[$data['room']] = json_decode($db_room['content'],true);

          //最大值判断  预先定义数组变量 否则报错
          $plus = [//投注
            'single' => 0,
            'double' => 0,
            'color' => 0,
            'other' => 0,
            'sum'  => 0,
          ];
          $userPlus = $plus;//用户

        //循环投注单

        //投注规则判断投注哪一注  其他则无法投注
        $bet_rule = [
           'ac' => ['ad','bc'],
           'bd' => ['ad','bc'],
           'ad' => ['ac','bd'],
           'bc' => ['ac','bd'],
        ];
        $bet_content = '';//缓存
        $i = 0;
        foreach ($data['data'] as $key => $value) {

            if(in_array($value[2],['ac','ad','bc','bd'])){//判断
              if($bet_content == ''){//如果为空查询本期此人的所有投注
                $bet_content = Db::table('betting')->field('content')->where(['expect'=>$lottery['expect'],'type'=>$data['lottery'],'user_id'=>$userdata['data']['id']])->select();
              }
              $reg = '#"'.$bet_rule[$value[2]][0].'"|"'.$bet_rule[$value[2]][1].'"#';
              foreach ($bet_content as $k => $v) {
                preg_match($reg,$v['content'],$matches);
                if($matches){
                  $return_data['code'] = -5;
                  $return_data['msg'] = '(大双,小单)与(小双,大单)不能同时投注';
                  break 2;
                }
              }
            }

            $bet[$i]['code'] = $value[2];                                           //投注种类
            if($data['type'] == 2){                                                 //是否为手输投注
              $plus[$value[3]] += $data['money'][$i];
              $bet[$i]['money'] = $data['money'][$i];
              $plus['sum'] += $data['money'][$i];
              if($room_rule[$data['room']][$value[3]]['bet'] > 0 && ($data['money'][$i] > $room_rule[$data['room']][$value[3]]['bet'])){ //单次投注上限
                $return_data['code'] = -2;
                $return_data['msg'] = '单次投注('.$room_rule['note'][$value[3]].')金额,超过上限';
                break;
              }elseif($data['money'][$i] < $room_rule[$data['room']]['min'] ){
                $return_data['code'] = -2;
                $return_data['msg'] = '房间单注最低'.$room_rule[$data['room']]['min'].'元';
                break;
              }
            }else{
              $plus[$value[3]] += $data['money'];
              $bet[$i]['money'] = $data['money'];
              $plus['sum'] += $data['money'];
              if($room_rule[$data['room']][$value[3]]['bet'] > 0 && ($data['money'] > $room_rule[$data['room']][$value[3]]['bet'])){     //单次投注上限
                $return_data['code'] = -2;
                $return_data['msg'] = '单次投注('.$room_rule['note'][$value[3]].')金额,超过上限';
                break;
              }elseif($data['money'] < $room_rule[$data['room']]['min'] ){
                $return_data['code'] = -2;
                $return_data['msg'] = '房间单注最低'.$room_rule[$data['room']]['min'].'元';
                break;
              }
            }                              //记录总值
            $bet[$i]['explain'] = $value[3];
            $bet[$i]['odds'] = $value[1];
          $i++;
        }

        //金额判断
        if($return_data['code'] > 0){
          $userMoney = Db::table('user')->where('id','eq',$userdata['data']['id'])->field('money')->find();
          if($userMoney['money'] < $plus['sum']){
            $return_data['code'] = -3;
            $return_data['msg'] = '余额不足!';
          }
        }

        //总注限额判断
        if($return_data['code'] > 0){
          $seldata = Db::table('betting')->where('user_id','eq',$userdata['data']['id'])
                      ->where('expect','=',$lottery['expect'])//878099)//
                      ->field('content,money')
                      ->select();
                  // print_r($seldata);die;

          if(!empty($seldata)){

            foreach ($seldata as $key => $value) {
              $value['content'] = json_decode($value['content'],true);
              foreach ($value['content'] as $k => $v) {
                $userPlus[$v['explain']]+=$v['money'];
              }
            }

            foreach ($room_rule['note'] as $key => $value) {
              if($key == 'min'){continue;}
              if($room_rule[$data['room']][$key]['max'] > 0 && ($userPlus[$key] + $plus[$key]) > $room_rule[$data['room']][$key]['max']){
                $return_data['code'] = -4;
                $return_data['msg'] = '本期('.$value.')总投注已上限,目前已经投注'.$userPlus[$key];
                break;
              }
            }
          }
        }
        $lname = Home::lotteryAll();

        //投注成功
        if($return_data['code'] > 0){
          $add['content'] = json_encode($bet);      //本期所有投注 转换json
          $add['create_time'] = time();
          $add['expect'] = $lottery['expect'];      //投注期号
          $add['other'] = $data['room'];            //投注房间
          $add['type'] = $data['lottery'];          //投注彩种类型
          $add['user_id'] = $userdata['data']['id'];//用户ID
          $add['money'] = $plus['sum'];             //本期总共投注金额
          $text['uid'] = $userdata['data']['id'];
          $text['money'] = $plus['sum'];
          $text['type'] = 0;
          $text['explain'] = $lname[$data['lottery']].'28';
          Db::startTrans();
          try{
             $state = betting::insert($add);
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

    /**
     * 投注28系列玩法检测
     * @param array $bet 投注内容
     * @return blooean true 表示通过
     */
    public function checkAll28($bet){
      //  print_r($bet);die();
      //  此检测方式暂时判断使用
      $type = ['double','single','color','other'];
      $type1 = ['a','b','c','d','ac','ad','bc','bd','min','max','red','blue','green','yellow'];
      try{
        
        foreach($bet['data'] as $k => $v){

          if(!in_array($v[3],$type)){
            return false;
          }
          if(is_numeric($v[2])){
            if(!is_int((int)$v[2]) || $v[2] > 27 || $v[2] < 0 ){
              // print_r(!is_int((int)$v[2]));
              return false;
            }
          }else{
            if(!in_array($v[2],$type1)){
              return false;
            }
          }

          if($bet['type'] == 2){
            //选择注单投注检测金额
            if(!is_numeric($bet['money'][$k]) || $bet['money'][$k] <= 0){
              return false;
            }
          }
        }
      
        if($bet['type'] == 1){
          //输入注单投注检测金额
          if(!is_numeric($bet['money']) || $bet['money'] <= 0){
            return false;
          }
        }
        return true;
      }catch(\Exception $e){
        // print_r( $e->getMessage());
        return false;
      }

      // if($bet['type'] == 1){
      //   //选择注单投注
        
      // }else{
      //   //输入注单投注
      // }
      // return true;
    }

    //获取下注玩法及倍率
    public function bet(){
      $data = input('post.');
      $return_data['code'] = 1;
      if($data['type'] == '' || !is_numeric($data['type'])){
        $return_data['code'] = -1;
        $return_data['msg'] = '页面信息错误！';
      }else{
        $get = Db::table('lottery_config')->where('type','=',$data['type'])->find();
        if(!$get){
          $return_data['code'] = -2;
          $return_data['msg'] = '没有此游戏';
        }else if($get['switch'] != 1){
          $return_data['code'] = -3;
          $return_data['msg'] = '本彩票游戏暂时关闭';
        }else{
          $return_data['data'] = json_decode($get['basic_config'],true); //赔率及内容
          $return_data['desc'] = json_decode($get['time_config'],true)['desc'];//封盘时间
        }
      }
      return  $return_data;
    }
    /**
     * 节气算法
     * @param  number _year 年
     * @param  number month 月
     * @param  number day 日
     */
    static function getJieQi($_year,$month,$day)   
    {   
             $year = substr($_year,-2)+0;   
             $coefficient = array(   
                 array(5.4055,2019,-1),//小寒   
                 array(20.12,2082,1),//大寒   
                 array(3.87),//立春   
                 array(18.74,2026,-1),//雨水   
                 array(5.63),//惊蛰   
                 array(20.646,2084,1),//春分   
                 array(4.81),//清明   
                 array(20.1),//谷雨   
                 array(5.52,1911,1),//立夏   
                 array(21.04,2008,1),//小满   
                 array(5.678,1902,1),//芒种   
                 array(21.37,1928,1),//夏至   
                 array(7.108,2016,1),//小暑   
                 array(22.83,1922,1),//大暑   
                 array(7.5,2002,1),//立秋   
                 array(23.13),//处暑   
                 array(7.646,1927,1),//白露   
                 array(23.042,1942,1),//秋分   
                 array(8.318),//寒露   
                 array(23.438,2089,1),//霜降   
                 array(7.438,2089,1),//立冬   
                 array(22.36,1978,1),//小雪   
                 array(7.18,1954,1),//大雪   
                 array(21.94,2021,-1)//冬至   
             );   
            //  $term_name = array(      
            //  "小寒","大寒","立春","雨水","惊蛰","春分","清明","谷雨",      
            //  "立夏","小满","芒种","夏至","小暑","大暑","立秋","处暑",      
            //  "白露","秋分","寒露","霜降","立冬","小雪","大雪","冬至");  
             $term_name = array(2,2,2,2,2,2,2,2,1,1,1,1,1,1,1,1,1,1,1,1,2,2,2,2);  
                 
             $idx1 = ($month-1)*2;   
             $_leap_value = floor(($year-1)/4);   
             $day1 = floor($year*0.2422+$coefficient[$idx1][0])-$_leap_value;
             if(isset($coefficient[$idx1][1])&&$coefficient[$idx1][1]==$_year) $day1 += $coefficient[$idx1][2];   
             $day2 = floor($year*0.2422+$coefficient[$idx1+1][0])-$_leap_value;   
             if(isset($coefficient[$idx1+1][1])&&$coefficient[$idx1+1][1]==$_year) $day1 += $coefficient[$idx1+1][2];   
                
             //echo __FILE__.'->'.__LINE__.' $day1='.$day1,',$day2='.$day2.''.chr(10);
             $data=array();
             if($day<$day1){
                 $data['name1']=$term_name[$idx1-1];
                 $data['name2']=$term_name[$idx1-1].'后';
             }else if($day==$day1){
                 $data['name1']=$term_name[$idx1];
                 $data['name2']=$term_name[$idx1];
             }else if($day>$day1 && $day<$day2){
                 $data['name1']=$term_name[$idx1];
                 $data['name2']=$term_name[$idx1].'后';
             }else if($day==$day2){
                 $data['name1']=$term_name[$idx1+1];
                 $data['name2']=$term_name[$idx1+1];
             }else if($day>$day2){
                 $data['name1']=$term_name[$idx1+1];
                 $data['name2']=$term_name[$idx1+1].'后';
             }
             return $data;
    }  
}
