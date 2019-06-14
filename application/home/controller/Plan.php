<?php
namespace app\home\controller;

use think\Controller;
use app\home\model\User;
use app\home\model\SystemConfig;
use app\home\model\LotteryConfig;
use think\Db;

class Plan extends Controller
{   
  /**
   * 计划入口文件 
   * @param int $type 彩种
   * @param int $expect 期号
   * @param array $code 开奖号
   */
  public function index($type,$expect,$code){

   
    //查询当前最新一期 如果没有则退出
    $now_expect = (in_array($type,[24,25,26,27]) ? Lottery28::lottery($type) : (new Lottery)->getHistory($type));
    if($now_expect['expect'] == '' || $now_expect['expect'] == 0){return;}

    //如果当前期和传过来的期数大于1期则退出 --@@-
    if( ($now_expect['expect'] - $expect) > 1){return;}
    
    if( $now_expect['expect'] == $expect ){$now_expect['expect'] +=1; }

    //获取可计划的玩法规则彩种配置
    $lo_type = $this->allLotteryType($type);
    if($lo_type['code'] < 0){return;}

    $config = SystemConfig::get(53)->value;
    if (empty($config) ) {return;}
    $config = json_decode($config,true);

    // 循环该彩种有几个发言者
    $all_spack = [];
    foreach( $config as $k => $vo ){
      if($vo['type'] == $type && $vo['switch'] == 1){
        $all_spack[] = $k;
      }
    }
    //如果没有配置计划退出
    if(count($all_spack) == 0){return;}
 
    //如果查询到下一期有计划无论是谁的 都退出 --@@-
    if(Db::table('plan')->where('expect','>',$expect)->where('type','=',$type)->find()){return;}
 
    //循环发言者 发送计划plan
    foreach( $all_spack as $vo ){
      $where = [];
      $where[] = ['uid','=',$config[$vo]['uid']];
      if($config[$vo]['uid'] > 0){
        $where[] = ['uid_type','=',$config[$vo]['uid_type']];
      }
      $where[] = ['type','=',$type];
      //查询数据库最近一期是否有该发言者 
      $bit = Db::table('plan')->where($where)->where('expect','=',$expect)->order('id','DESC')->find();

      // print_r($bit);die();
      //写入聊天室
      $add_chat = [
        'user_id' => $config[$vo]['uid'],
        'content' => $config[$vo]['note'].' '.(LotteryConfig::get($type)->name),
        'type' => ($config[$vo]['uid'] == 0 ? 2 : $config[$vo]['uid_type']),
        'create_time' => time()
      ];
      //写入新一期计划  
      $add_plan = [
        'uid' => $config[$vo]['uid'],
        'uid_type' => $config[$vo]['uid_type'],
        'type' => $config[$vo]['type'],
        'expect' => $now_expect['expect'],//((int)$expect+1),
        'create_time' => time()
      ];
      //判断是否有计划
      
      if(!empty($bit)){
       
        $bit['content'] = json_decode($bit['content'],true);
        //如果查询到该用户有这一期的计划 则判断修改并写入下一期的计划

        //模拟数据
        // [id] => 3
        // [uid] => 0        //用户或者admin的id
        // [uid_type] => 0   //判断用户还是admin 如果uid为零 则统一为系统
        // [type] => 2       //发送计划的彩种
        // [expect] => 501   //发送计划的期号
        // [content] => {"key":"code","value":[4,6,8]}  //计划的内容
        // [loss_num] => 0   //连输几期
        // [plan_num] => 1   //计划几期
        // [win] => 0
       
        //查询连续 玩法设置使用content
        // $where[] = ['win','=',1];
        // $where[] = ['id','>',$bit['id']-$bit['plan_num']];
        $now_bit = Db::table('plan')->where($where)->order('id','DESC')->limit(($bit['plan_num'] > 15 ? 15 : $bit['plan_num']))->select();
        if(!empty($now_bit)){
          $sort = count($now_bit);
          for($i=($sort-1);$i>=0;$i--){
            $vo1 = $now_bit[$i];
            if($vo1['win'] != 1){continue;}
            //foreach($now_bit as $vo1){
            $vo1['content'] = json_decode($vo1['content'],true);
            $add_chat['content'] .= '<div>'.substr($vo1['expect'],-3).' 期 '.$lo_type['plan'][$vo1['content']['plan_bet']]['content'][$vo1['content']['plan_code']].' ';
            if($vo1['content']['plan_bet'] == 'code'){
              $add_chat['content'] .= implode('',$vo1['content']['value']); 
            }else{
              $plan_c = $lo_type['plan'][$vo1['content']['plan_bet']]['play'];
              $add_chat['content'] .= $plan_c[$vo1['content']['value']];
            }
            if(pow(2,$vo1['loss_num']) > 1 ){
              $add_chat['content'] .= ' '.pow(2,$vo1['loss_num']).'倍 中</div>';
            }else{
              $add_chat['content'] .= ' 中</div>';
            }
          }
        } 
      
        //判断上一个是否猜中
        if($this->planInfo($code,$bit['content'],$lo_type)){
       
          $bit['win'] = 1;
          //下一期的连输记录清零
          $add_plan['loss_num'] = 0;
          //并将本期也刷出
          if($bit['content']['plan_bet'] == 'code'){
            $add_chat['content'] .= $this->splicing($bit['expect'], $lo_type['plan'][$bit['content']['plan_bet']]['content'][$bit['content']['plan_code']], implode('',$bit['content']['value']),1,pow(2,$bit['loss_num']) );
          }else{
            $plan_d = $lo_type['plan'][$bit['content']['plan_bet']]['play'];
            $add_chat['content'] .= $this->splicing($bit['expect'], $lo_type['plan'][$bit['content']['plan_bet']]['content'][$bit['content']['plan_code']], $plan_d[$bit['content']['value']],1,pow(2,$bit['loss_num']) );
          }
       
        }else{
          $bit['win'] = 2;
          //下一期连输记录加1
          $add_plan['loss_num'] = $bit['loss_num'] + 1;
        }
   
        // print_r($bit);
        // die();
        //更新本期
        $bit['content'] = json_encode($bit['content']);
        Db::table('plan')->where('id',$bit['id'])->update($bit);
        //下一期计划+1
        $add_plan['plan_num'] = $bit['plan_num'] + 1;
        //加入下一期计划
        if($config[$vo]['plan_bet'] == 'code'){
          //随机获取
          $arr_list = range($lo_type['data']['min'],$lo_type['data']['max']);
          $arr = array_rand($arr_list,$config[$vo]['num']);
          foreach($arr as &$it){
            $it = $arr_list[$it];
          }

          // $add_chat['content'] .= implode('',$arr).'</div>';
          $add_chat['content'] .= $this->splicing($now_expect['expect'],$lo_type['plan'][$config[$vo]['plan_bet']]['content'][$config[$vo]['plan_code']],implode('',$arr),'',pow(2,$add_plan['loss_num']) );
        }else{
        
          $plan_a = $lo_type['plan'][$config[$vo]['plan_bet']]['play'];
          $arr = array_rand($plan_a,1);
         
          $add_chat['content'] .= $this->splicing($now_expect['expect'],$lo_type['plan'][$config[$vo]['plan_bet']]['content'][$config[$vo]['plan_code']],$plan_a[$arr],'',pow(2,$add_plan['loss_num']) );
        }

      }else{

        //如果没有查询到该用户这一期的计划 则写入[下一期]的计划

        // //写入
        // $add_chat = [
        //   'user_id' => $config[$vo]['uid'],
        //   'content' => '',
        //   'type' => ($config[$vo]['uid'] == 0 ? 2 : $config[$vo]['uid_type']),
        //   'create_time' => time()
        // ];        
        //$add_chat['content'] .= '<div>'.substr($expect,-3).' 期 '. $lo_type['plan'][$config[$vo]['plan_bet']]['content'][$config[$vo]['plan_code']].' ';
        if($config[$vo]['plan_bet'] == 'code'){
          //随机获取
          $arr = array_rand(range($lo_type['data']['min'],$lo_type['data']['max']),$config[$vo]['num']);
          // $add_chat['content'] .= implode('',$arr).'</div>'; $config[$vo]['num']
          $add_chat['content'] .= $this->splicing($now_expect['expect'],$lo_type['plan'][$config[$vo]['plan_bet']]['content'][$config[$vo]['plan_code']],implode('',$arr));
        }else{
          $plan_c = $lo_type['plan'][$config[$vo]['plan_bet']]['play'];
          $arr = array_rand($plan_c,1);
          // $add_chat['content'] .= $plan_c[$arr].'</div>';
          // print_r($arr);die();
          $add_chat['content'] .= $this->splicing($now_expect['expect'],$lo_type['plan'][$config[$vo]['plan_bet']]['content'][$config[$vo]['plan_code']],$plan_c[$arr]);
        }
        //加入plan表    
      }
      $plan_content = [
        'plan_bet' => $config[$vo]['plan_bet'], 
        'plan_code' => $config[$vo]['plan_code'], 
        'value' => $arr,
      ];
      //加入plan表
      $add_plan['content'] = json_encode($plan_content);
      Db::table('plan')->insert($add_plan);
      Db::table('chat_room')->insert($add_chat);
    }
    //print_r($arr);
  }

  /**
   * dom Div拼接
   * @param int $expect   期号
   * @param string $plan_code 玩法
   * @param string $code 预测号
   * @param int $odds  赔率默认空
   * @param int $win   1为中奖
   */
  static function splicing( $expect,$plan_code='',$code='',$win='',$odds='' ){
    $content = '<div>'.substr($expect,-3).' 期 '.' '.$plan_code.' '.$code.' ';
    if($odds != '' && $odds > 1){
      $content .= $odds.'倍 ';
    }
    if($win != ''){
      $content .= '中';
    }
    $content .= '</div>';
    return $content;
    // return .' '.($odds!='' ? ($odds.'倍'):'').' '.($win!='' ? '中':'').'</div>';
  }

  // /**
  //  * 赔率计算
  //  * @param $loss 未中次数
  //  */
  // static function odds($loss){
  //   $loss = pow(2,$loss);
  // }

  /**
   * 中奖判断
   * @param array $code     开奖号
   * @param array $content 计划内容     彩种
   * @param array $config  配置
   * @return blooean 0是 1否  中奖
   */
  public function planInfo($code,$content,$config){
   
    if($content['plan_bet'] == 'code'){
     
      foreach($content['value'] as $vo){
        if($vo == $code[$content['plan_code']]){
          return true;
        }
      }
    }elseif($content['plan_bet'] == 'size'){

      if($content['plan_code'] == 'zh'){
    
        $he = array_sum($code);
        if( $content['value'] == 'xiao' && $he <= $config['data']['all_little'] ){
          return true;
        }elseif( $content['value'] == 'da' && $he > $config['data']['all_little'] ){
          return true;
        }
      }elseif($content['value'] == 'xiao' && $code[$content['plan_code']] <= $config['data']['little']){
        return true;
      }elseif( $content['value'] == 'da' && $code[$content['plan_code']] > $config['data']['little'] ){
        return true;
      }

    }elseif($content['plan_bet'] == 'single'){
     
      if($content['plan_code'] == 'zh'){
        $he = array_sum($code);
        
        if( $content['value'] == 'dan' && ($he%2 == 1) ){
          return true;
        }elseif( $content['value'] == 'shuang' && ($he%2 == 0) ){
          return true;
        }
      }elseif($content['value'] == 'dan' && ($code[$content['plan_code']]%2 == 1)){
        return true;
      }elseif( $content['value'] == 'shuang' && ($code[$content['plan_code']]%2 == 0) ){
        return true;
      }
    }

    return false;
  }

  /**
   * 返回所需彩种的所有内容
   * @param int $type 获取彩种编号
   * @return array 
   */
  static function allLotteryType($type){

    $return_data = [
      'code' => 1,
    ];

    if(in_array($type,[0,1])){
      //龙虎百家
      $return_data['code'] = -1;
    }elseif(in_array($type,[2,12,13,6,7,8,9,28])){
      //ssc&ffc

      // $data = [
      //   'max' => '',   //每一个最大值
      //   'min' => '',   //每一个最小值
      //   'num' => '',   //要开多少个号码
      //   'sel' => '',   //在预测code时 使用
      //   'continue'=>false             //开奖号码隔天是否连续
      // ];

      $return_data['data'] = [
        'min' => 0,
        'max' => 9,
        'num' => 5,
        'little'=> 4,
        'all_little'=> 22,
        // 'sel' => $num,
        // 'continue' => false
      ];
      $return_data['plan'] = [
        //可预测的投注类型 和 内容
        'size' => [
          'name' => '大小',
          'content' => [
            '0' => '第一球',
            '1' => '第二球',
            '2' => '第三球',
            '3' => '第四球',
            '4' => '第五球',
            'zh' => '总和'
          ],
          'play' => ['da' => '大','xiao' => '小'],
        ],
        'single' => [
          'name' => '单双',
          'content' => [
            '0' => '第一球',
            '1' => '第二球',
            '2' => '第三球',
            '3' => '第四球',
            '4' => '第五球',
            'zh' => '总和'
          ],
          'play' => ['dan' => '单','shuang' => '双']
        ],
        'code' => [
          'name' => '数字',
          'content' => [
            '0' => '第一球',
            '1' => '第二球',
            '2' => '第三球',
            '3' => '第四球',
            '4' => '第五球'
          ]
        ]
      ];
    }elseif(in_array($type,[3,4,5,36,37,38,39,51])){
      //pk10
      $return_data['data'] = [
        'min' => 1,
        'max' => 10,
        'num' => 10,
        'little'=> 5,
        'all_little'=> 0,
        // 'sel' => $num,
        // 'continue' => false
      ];
      $return_data['plan'] = [
        //可预测的投注类型 和 内容
        'size' => [
          'name' => '大小',
          'content' => [
            '0' => '冠军',
            '1' => '亚军',
            '2' => '第3名',
            '3' => '第4名',
            '4' => '第5名',
            '5' => '第6名',
            '6' => '第7名',
            '7' => '第8名',
            '8' => '第9名',
            '9' => '第10名',
          ],
          'play' => ['da' => '大','xiao' => '小'],
        ],
        'single' => [
          'name' => '单双',
          'content' => [
            '0' => '冠军',
            '1' => '亚军',
            '2' => '第3名',
            '3' => '第4名',
            '4' => '第5名',
            '5' => '第6名',
            '6' => '第7名',
            '7' => '第8名',
            '8' => '第9名',
            '9' => '第10名',
          ],
          'play' => ['dan' => '单','shuang' => '双']
        ],
        'code' => [
          'name' => '数字',
          'content' => [
            '0' => '冠军',
            '1' => '亚军',
            '2' => '第3名',
            '3' => '第4名',
            '4' => '第5名',
            '5' => '第6名',
            '6' => '第7名',
            '7' => '第8名',
            '8' => '第9名',
            '9' => '第10名',
          ]
        ]
      ];
    }elseif(in_array($type,[10,14,15,30,31,32,33,34,40,41,42,43])){
      //k3
      $return_data['code'] = -1;
    }elseif(in_array($type,[11,21])){
      //lhc
      $return_data['code'] = -1;
    }elseif(in_array($type,[16,17,18,44,45,46,47,48,49])){
      //11x5
      $return_data['code'] = -1;
    }elseif(in_array($type,[24,25,26,27,57,58])){
      //28
      $return_data['code'] = -1;
    }elseif(in_array($type,[19,22])){
      //福彩3d & 排列3
      $return_data['code'] = -1;
    }elseif(in_array($type,[23])){
      //幸运农场
      $return_data['code'] = -1;
    }elseif(in_array($type,[20,50])){
      //广西快乐十分
      $return_data['code'] = -1;
    }else{
      $return_data['code'] = -1;
    }
    return $return_data;
  }
}
