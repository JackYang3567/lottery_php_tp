<?php
namespace app\home\controller;
use Firebase\JWT\JWT;
use app\home\model\User;
use app\home\model\LoginLog;
use app\home\model\newRelationship;
use app\home\model\bettingGen;
use app\home\model\bettingZhui;
use app\home\model\bettingHe;
use app\home\model\SystemConfig;
use app\home\controller\Lottery28;
use app\home\model\CapitalDetail;
use think\Cookie;
use think\Db;

class In extends Common
{   
    /**
     * 全部福利开关，包括:
     * 1.签到得积分 
     * 2.幸运大转盘 
     * 3.打码反水   
     * 4.代理返佣   
     * 5.红包       
     * 6.系列日负回水 
     */
    // static function allWelfare(){
    //   return false;
    // }

    /**
     * 普通用户登录
     */
    public function login()
    {
      $return_data = [
        'code' => 0,
        'msg' => '登陆信息缺失'
      ];
      if($this->checkLogin()['code']){
        $return_data['code'] = 1;
        $return_data['msg'] = '您已登录,若要登录其他账户,请先退出登录';
        return $return_data;
      }
      $data = input('post.');
      if(!empty($data) && !empty($data['username']) && !empty($data['password'])){
        $user = User::field('id,username,password,money,status')->where(['username'=>$data['username']])->find();
        $verify_open = Db::table('system_config')->where('name','=','login_verify')->field('value')->find();
        if(empty($user)){
            $return_data['msg'] = '没有这个用户';
        }else if($user['status'] == 1){
            $return_data['msg'] = '改账户已被冻结,无法登陆,如有疑问请联系客服!';
        }else if(($verify_open['value'] == 1) && !$this->verifyCheck($data['verify'])){
            $return_data['msg'] = '验证码错误';
        }else{
          // || $data['password'] == $user['password']
          if(md5($data['password']) == $user['password']){
            $return_data['code'] = 1;
            $return_data['msg'] = '用户登陆成功';

            (new LoginLog)->save(['user_id'=>$user['id'],'ip'=>request()->ip(),'browser'=>$_SERVER['HTTP_USER_AGENT']]);
            header('pragma:' . $this->JwtEncode(['uid'=>$user['id']]) . '||' . json_encode(['id'=>$user['id'],'username'=>$user['username'],'money'=>$user['money'],'level'=>$this->userLevel($user['id'])]));

            $return_data['card'] = '';
            if($data['remberp'] == 'true'){
              //$return_data['card'] = $user['password'];
            }
            $return_data['cardu'] = '';
            if($data['remberu'] == 'true'){
              $return_data['cardu'] = $data['username'];
            }
          }else{
            $return_data['msg'] = '用户密码错误';
          }
        }
      }
      return $return_data;
    }

    /**
     * 用户等级查询
     * @param int 用户id
     * @return int 用户等级
     */
    public function userLevel($uid){

       //查询设置的等级
       $level = Db::table('user_rank')->select();
       $userlevel = 1;
       //查询该用户的累计流水
       $get = Db::table('accumulation')->order('create_time','desc')->where('user_id',$uid)->find();
       if($get){
         foreach ($level as $key => $value) {
           $value['condition'] = json_decode($value['condition'],true);
           //流水判断                                                                    //判断充值
           if( ($get['use_money']+$get['winning']) < $value['condition']['account'] || ($get['in_money']+$get['online_money']) < $value['condition']['recharge'] )
           {
             break;
           }

           $userlevel = $value['rank'];
         }
       }
      return $userlevel;
    }

    /**
     * 代理注册下线
     */
    public function agentReg(){
      $data = input('post.');
      $return_data = [
        'code' => 0,
        'msg' => '注册失败'
      ];
      // print_r($data);die;
      if( empty($data) && empty($data['username']) && empty($data['password']) && empty($data['to_password']) ){  //判断账户是否有问题
        $return_data['msg'] = '信息缺失';
      }else if($data['password'] != $data['to_password']){
        $return_data['msg'] = '两次密码不一致';
      }else if(!$this->verifyCheck($data['verify'])){
        $return_data['msg'] = '验证码错误';
      }else if(!is_numeric($data['agent'])){
        $return_data['msg'] = '代理无效';
      }else{
        $agent = Db::table('proxy')->where('uid','=',$data['agent'])->find();
        if(empty($agent)){
          $return_data['msg'] = '没有找到代理';
        }else if($agent['type'] == 1){
          $add = [
            'username' => $data['username'],
            'password' => md5($data['to_password']),
            'create_time' => time(),
            'type' => 2
          ];
          if( Db::table('proxy')->where('username','=',$data['username'])->find() ){
            $return_data['msg'] = '用户名已存在';
          }else if($rs = Db::table('proxy')->insertGetId($add) ){
            $return_data['code'] = 1;
            $return_data['msg'] = '注册成功,您注册的是二级代理,请资讯客服进入管理后台';
            $addr = [
              'top' => $agent['uid'],
              'userid' => $rs,
              'prev' => $agent['uid'],
              'floor' => 2
            ];
            Db::table('relationship')->insert($addr);
          }
        }else if($agent['type'] == 2){
          //二级代理发展普通下线
          $phone_on = SystemConfig::where('id',50)->column('value')[0];
          $add = [
            'username' => $data['username'],
            'password' => md5($data['to_password']),
            'proxy_id' => $agent['uid'],
            'photo' => rand(1,19),
            'create_time' => time(),
            'active_time' => time(),
            'active_ip'   => request()->ip()
          ];
          if(Db::table('user')->where('username','=',$data['username'])->find()){
            $return_data['msg'] = '用户名已存在';
          }else if($phone_on == 1 && Db::table('user_info')->where('phone_number','=',$data['phone'])->find() ){
            $return_data['msg'] = '手机号已被注册';
          }else if($rs = Db::table('user')->insertGetId($add)){
            //插入手机号
            if($phone_on == 1 && isset($data['phone']) ){
              Db::table('user_info')->insert(['user_id'=>$rs,'phone_number'=>$data['phone']]);
            }
            $return_data['code'] = 2;
            $return_data['msg'] = '注册成功,祝您游戏愉快';
            //$this->onMessage($rs);
            //填入代理信息
            $upId = Db::table('relationship')->field('top')->where('userid','=',$agent['uid'])->find();
            $addr = [
              'top' => $upId['top'],
              'userid' => $rs,
              'prev' => $agent['uid'],
              'floor' => 3
            ];
            Db::table('relationship')->insert($addr);
             (new LoginLog)->save(['user_id'=>$rs,'ip'=>request()->ip(),'browser'=>$_SERVER['HTTP_USER_AGENT']]);
             header('pragma:' . $this->JwtEncode(['uid'=>$rs]) . '||' . json_encode(['id'=>$rs,'username'=>$data['username'],'level'=>1]));
          }
        }
      }
      return $return_data;
    }

    /**
     * 发送提示信息
     */
    public function onMessage($id){
      $llk = ['user_id' => $id,'title' => '温馨提示','content' => '完善资料才可提现。','state' => 0,'create_time' =>time() ];
      model('user_message')->where($id)->insert($llk);
    }

    /**
     * 普通用户注册
     */
    public function reg()
    {
      $return_data = [
        'code' => 0,
        'msg' => '注册信息缺失'
      ];
      $data = input('post.');
      // print_r($data);
      //获取配置信息
      $phone_on = SystemConfig::where('id',50)->column('value')[0];
      // print_r($phone_on);die;
      if(!empty($data) && !empty($data['username']) && !empty($data['password']) && !empty($data['to_password'])){
        if($data['password'] != $data['to_password']){
          $return_data['msg'] = '两次密码不一致';
        }else if(!$this->verifyCheck($data['verify'])){
          $return_data['msg'] = '验证码错误';
        }else if($phone_on == 1 && Db::table('user_info')->where('phone_number','=',$data['phone'])->find() ){
          $return_data['msg'] = '手机号已被注册';
        }else{
          $user = User::field('id')->where(['username'=>$data['username']])->find();
          if(empty($user)){
            $user = new User(['username'=>$data['username'],'photo' => rand(1,19),'password'=>md5($data['password']),'active_time'=>time(),'active_ip'=>request()->ip()]);
            if($user->save()){
              $return_data['code'] = 1;
              $return_data['msg'] = '用户注册成功';
              //注册成功发送消息
              //$this->onMessage($user->id);

              // 插入手机号
              if($phone_on == 1 && isset($data['phone']) ){
                Db::table('user_info')->insert(['user_id'=>$user->id,'phone_number'=>$data['phone']]);
              }
              (new LoginLog)->save(['user_id'=>$user->id,'ip'=>request()->ip(),'browser'=>$_SERVER['HTTP_USER_AGENT']]);
              header('pragma:' . $this->JwtEncode(['uid'=>$user->id]) . '||' . json_encode(['id'=>$user->id,'username'=>$data['username'],'level'=>1]));
              //注册成功后 查看是否有代理设置代理
              if(is_numeric($data['agent'])){
                $this->proxy($data['agent'],$user->id);
              }
              //代理设置完
            }else{
              $return_data['msg'] = '用户注册失败';
            }
          }else{
            $return_data['msg'] = '这个用户已经存在了';
          }
        }
      }
      return $return_data;
    }
public function xiao(){
    $data = $_GET('id');
    var_dump($data);
}
    /**
     * 免费试玩开关
     */
    public function demoLoginOpen(){
        return Db::table('system_config')->field('value')->where('name','=','demo_user')->find()['value'];
    }

    /**
     * 免费试玩用户
     */
    public function demoLogin()
    {
      $data = $this->checkLogin();
      $return_data['code'] = 1;
      if($data['code']){
        $return_data['msg'] = '您已登录,祝您游戏愉快';
      }else if($this->demoLoginOpen() != 1){
        $return_data['msg'] = '试玩已关闭,请注册为正式会员';
      }else{
        $rs = Db::table('user')->insertGetId(['type'=>'1']);
        $check = true;
        $add['username'] = 's'.$rs;
        while($check){
          if(user::field('id')->where(['username'=>$add['username']])->find()){
            $add['username'] = 's'.date("Y").date("m").date("d").date("H").date("i").date("s").rand(0,1000);
          }else{
            $check = false;
          }
        }
        $add['password'] = md5('123456789');
        $add['money'] = '2000.00';
        $add['create_time'] = time();
        if(Db::table('user')->where('id',$rs)->update($add)){
          $return_data['msg'] = '登录成功';
          (new LoginLog)->save(['user_id'=>$rs,'ip'=>request()->ip(),'browser'=>$_SERVER['HTTP_USER_AGENT']]);
          header('pragma:' . $this->JwtEncode(['uid'=>$rs]) . '||' . json_encode(['id'=>$rs,'username'=>$add['username'],'money'=>$add['money']]));
        }else{
          $return_data['code'] = -1;
          $return_data['msg'] = '错误';
        }
      }
      return $return_data;
    }

    /**
     * 注册时候用户协议
     */
    public function getRegtip()
    {
      $data = input('post.');
      //查询开户条约 和 启动手机配置
      $bit = Db::table('system_config')->where('id','in',[13,50])->column('id,name,value');
      $return_data['xy'] = $bit[13]['value'];
      $return_data['phoneOn'] = $bit[50]['value'];
      //查询注册后跳转配置
      $return_data['tge'] = Db::table('system_config')->field('value')->where('name','=','login_jump')->find()['value'];
      $return_data['tge'] = json_decode($return_data['tge'],true);

      if(isset($data['type'])){
        $return_data['re_type'] = Db::table('proxy')->field('type')->where('uid','=',$data['type'])->find()['type'];
      }
      return $return_data;
    }

    //找回密码
    public function backpwd()
    {
        $data = input('post.');
        $return_data['code'] = 1;
        $return_data['msg'] = '下一步';
        if(!$this->verifyCheck($data['data']['verify'])){
          $return_data['code'] = -1;
          $return_data['msg'] = '验证码错误！';
        }else{
          $userdata = Db::table('user')->where('username',$data['data']['username'])->find();
          $rs1 = Db::table('user_info')->where('user_id',$userdata['id'])->field('question,email')->find();
          //print_r($data);
         if($data['pace'] == 1){  //第一步
            if($userdata) {
              if(!empty($rs1['email']) ||!empty($rs1['question'])){
                $return_data['pace'] = 2;
                $rs1['question'] = explode('|',$rs1['question'])[0];
                $return_data['problem'] = $rs1;
              }else{
                $return_data['code'] = -2;
                $return_data['msg'] = '该用户没有设置安全问题和邮箱，请联系客服！';
              }
            }else{
              $return_data['code'] = -3;
              $return_data['msg'] = '用户名错误！';
            }
          }else if($data['pace'] == 2){//第二步
            // print_r($rs1);
            if($data['data']['selected'] == 'question' && $rs1['question'] ){
                $rs2 = explode('|',$rs1['question']);
                if( $rs2[1] == $data['data']['answer'] ){
                  $return_data['pace'] = 3;
                }else{
                  $return_data['code'] = -4;
                  $return_data['msg'] = '安全问题错误';
                }
            }else if($data['data']['selected'] == 'email' && $rs1['email'] ){
                print_r('这里是email');
            }else{
              $return_data['code'] = -7;
              $return_data['msg'] = '错误！';
            }
          }else if($data['pace'] == 3){//第三步
            if($data['data']['password'] == '' || $data['data']['to_password'] == ''){
              $return_data['code'] = -8;
              $return_data['msg'] = '密码缺失！';
            }else if($data['data']['password'] != $data['data']['to_password']){
              $return_data['code'] = -9;
              $return_data['msg'] = '两次密码不一致';
            }else{
              if(Db::table('user')->where('id',$userdata['id'])->data(['password'=>md5($data['data']['to_password']) ])->update() ){
                $return_data['pace'] = 4;
                $return_data['code'] = 2;
                $return_data['msg'] = '修改密码成功';
              }else{
                $return_data['code'] = -10;
                $return_data['msg'] = '密码修改失败';
              }
            }
          }
        }
        //print_r($return_data);
        return $return_data;
    }

    public function _init(){
      echo 'SUCCESS';
    }

    /**
     * 代理方法 $this->proxy() $pid 上级用户   $user_id 注册用户
     * @param int $pid 上级用户
     * @param int $user_id 自身id
     * @return array 是否成功
     */
    public  function proxy($pid='',$user_id="")
    {
      $return_data = ['error'=>'0','msg'=>'已建立代理关系'];
      if($pid >$user_id){
          return ['error'=>1,'msg'=>'用户关系错误'];
      }
      $user_pid  =DB::table('user')->where('id',$user_id)->find()['pid'];
      if($user_pid != 0){
          return ['error'=>1,'msg'=>'该用户已有上级'];
      }
      $user_exist = DB::table('user')->where('id',$pid)->find();
      if(null == $user_exist){
          return ['error'=>1,'msg'=>'上级用户不存在'];
      }

      $relationship_exist = DB::table('new_relationship')->where('user_id',$pid)->find();
      if(null == $relationship_exist){
            $new_data['user_id'] = $pid;
            $new_data['child_one'] = $user_id;
            $new_rs = DB::name('new_relationship')->insert($new_data);
            if($new_rs){
                $user_pid_change =  DB::table('user')->where('id',$user_id)->update(['pid'=>$pid]);
            }
      }
      $user_pid_change =  DB::table('user')->where('id',$user_id)->update(['pid'=>$pid]);
      if(null != $relationship_exist['child_one']){
          $child_one_array = explode(',',$relationship_exist['child_one']);
        }else{
          $child_one_array = [];
        }

      if(!in_array($user_id,$child_one_array)){
          array_push($child_one_array,$user_id);
          $new_child_one = implode(',',$child_one_array);
          $new_child_one_rs = DB::table('new_relationship')->where('user_id',$pid)->update(['child_one'=>$new_child_one]);

      }

      //检测pid 的上级代理
        $pid_pid = DB::table('user')->where('id',$pid)->find()['pid'];

        if(0 !=$pid_pid && $pid_pid != $pid){
          $pid_relationship = Db::table('new_relationship')->where('user_id',$pid_pid)->find();
          if(null != $pid_relationship['child_two']){
              $child_two_array = explode(',',$pid_relationship['child_two']);
            }else{
              $child_two_array = [];
            }

          if(!in_array($user_id,$child_two_array)){
              array_push($child_two_array,$user_id);
              $new_child_two = implode(',',$child_two_array);
              $new_child_two_rs = DB::table('new_relationship')->where('user_id',$pid_pid)->update(['child_two'=>$new_child_two]);
          }
          //检测pid 的上上级代理
          $pid_pid_pid = DB::table('user')->where('id',$pid_pid)->find()['pid'];

          if(0 != $pid_pid_pid && $pid_pid_pid != $pid){
                $pid_pid_relationship = Db::table('new_relationship')->where('user_id',$pid_pid_pid)->find();
                    if(null != $pid_pid_relationship['child_three']){
                      $child_three_array = explode(',',$pid_pid_relationship['child_three']);
                    }else{
                      $child_three_array = [];
                    }

                  if(!in_array($user_id,$child_three_array)){
                      array_push($child_three_array,$user_id);
                      $new_child_three = implode(',',$child_three_array);
                      $new_child_three = DB::table('new_relationship')->where('user_id',$pid_pid_pid)->update(['child_three'=>$new_child_three]);
                  }
          }
        }
      return $return_data;
    }
    //系统配置
    static function setUp(){
      $data = input('post.');
      if(!isset($data)){
        return;
      }
      //$model = SystemConfig
      if($data['type'] == 'promote_url_Or_backwater'){//代理中心要查询推广域名和打码
        $pro = SystemConfig::where('name','in',['promote_url','child_one_rate','child_two_rate','child_three_rate'])->column('value','id');
        $return_data['url'] = $pro[36];
        $return_data['backwater'] = [$pro[22],$pro[23],$pro[24]];
      }else{
        $return_data = SystemConfig::field('value')->where('name','=',$data['type'])->find();
      }

      //如果是查询合买设置转码一次
      if($data['type'] == 'hm_zh' || $data['type'] == 'login_jump'){
        $return_data['value'] = json_decode($return_data['value'],true);
      }
      return $return_data;
    }

    /**
     * 多线路 弃用
     */
    // public function line(){
    //   $data = Db::table('system_config')->field('value')->where('name','web_line')->find();
    //   $data['value'] = json_decode($data['value']);
    //   // return $data['value'];
    //   foreach ($data['value'] as $key => $value) {
    //     // // 记录开始时间
    //     // $time_start = microtime(true);
    //     // // 这里放要执行的PHP代码:
    //     // file_get_contents('http://cs.wanda315.com/#/');
    //     // // 记录结束时间
    //     // $time_end = microtime(true);
    //     //
    //     // $return_data[$key]['speed'] = floor(($time_end - $time_start)*1000).'ms';
    //     // $return_data[$key]['url'] = $value;
    //     $return_data[$key]['speed'] = '0ms';
    //     $return_data[$key]['url'] = '暂无';
    //   }
    //    return $return_data;
    // }

    /**
     * 幸运大转盘
     * @param array
     */
    public function luck(){
      $data = input('post.');
      $return_data = [
        'code' => 1,
        'msg' => '抽奖',
        'num' => 0,
      ];
      //用户是否登录
      $user = $this->checkLogin();
      //大转盘查询数据
      $get = Db::table('system_config')->where('name','turntable')->find()['value'];
      //转换
      $get = json_decode($get,true);

      if($user['code'] > 0){
        $dit = CapitalDetail::where('create_time','between',[strtotime(date('Y-m-d 00:00:00')),time()])->where(['type'=>10,'user_id'=>$user['data']['id']])->count();
        //$cz = Db::table('accumulation')->where('user_id','=',$user['data']['id'])->field('in_money,online_money')->order('create_time','DESC')->find();
        $cz1 = CapitalDetail::where('create_time','between',[strtotime(date('Y-m-d 00:00:00')),time()])->where(['type'=>2,'user_id'=>$user['data']['id']])->sum('money');
        $cz2 = CapitalDetail::where('create_time','between',[strtotime(date('Y-m-d 00:00:00')),time()])->where(['type'=>7,'user_id'=>$user['data']['id']])->sum('money');
        if($get['switch'] < 1){
          $return_data['code'] = -1;
          $return_data['msg'] = '充值抽奖已关闭';
        } elseif ($user['data']['group'] == 1){
          $return_data['code'] = -8;
          $return_data['msg'] = '抽奖福利已被取消,如有疑问请联系客服';
        } elseif ($user['data']['status'] == 1){
          $return_data['code'] = -3;
          $return_data['msg'] = '帐号已被冻结';
        } elseif ($get['time'] - $dit <= 0){
          $return_data['code'] = -4;
          $return_data['msg'] = '今日抽奖次数已用完';
        } elseif ($user['data']['type'] == 1){
          $return_data['code'] = -5;
          $return_data['msg'] = '请注册为有效会员';
        } elseif ($user['data']['point'] < $get['use_point']){
          $return_data['code'] = -6;
          $return_data['msg'] = '积分不足'.$get['use_point'];
        } elseif (($cz1+$cz2) < $get['recharge_condition']){
          $return_data['code'] = -7;
          $return_data['msg'] = '今日累计充值金额未达到'.$get['recharge_condition'];
        } else {
          $return_data['num'] = $get['time'] - $dit;
        }

      }else{
        $return_data['code'] = -2;
        $return_data['msg'] = '请先登录';
      }
      if($data['type'] == 1){
        foreach ($get['data'] as $key => $value) {//奖品
          $return_data['data'][$key]['text'] = $value['text'];
          $return_data['data'][$key]['money'] = $value['point'];
        }
        //充值规则
        $return_data['rule'] = [
          'use_point' => $get['use_point'],                //使用积分
          'in_money' =>$get['recharge_condition'],         //累计充值金额
          'num' => $get['time']                            //每次抽奖次数
        ];
      }else{
        if($return_data['code'] > 0){
          $return_data['num'] -= 1;
            //随机摇号0-1000
            $num = rand(1,1000);
            //中奖key
            $win = 0;
            //
            foreach ($get['data'] as $key => $value) {
              $bit[$key] = floor(1000/100*(isset($value['percent']) ? $value['percent'] : 0));
              if($num <= array_sum($bit)){
                $win = $key;
                break;
              }
            }
            // print_r($win);die;
            $return_data['data'] = $win;
            $add = [
              'uid' => $user['data']['id'],
              'money' => $get['data'][$win]['point'],
              'type' => 10,
              'explain' => $get['data'][$win]['text'],
            ];
            if($add['money'] == 0){ //未中奖时 添加一条空数据
              Db::table('capital_detail')->insert(['user_id' => $user['data']['id'],
                                                   'money' => 0,
                                                   'type' => 10,
                                                   'explain' => '很遗憾,未中奖.',
                                                   'create_time' => time()
                                                  ]);
            }else{
              if(moneyAction($add)['code']){
                Db::table('user')->where('id','=',$user['data']['id'])->setDec('point',$get['use_point']);
                // $return_data['num'] -= 1;
                $chat['user_id'] = 0;
                $chat['content'] = '恭喜玩家'.mb_substr($user['data']['username'],0,3).'***'.' '.'在幸运大转盘中获得了'.$add['explain'].',奖金'.$add['money'].'元';
                $chat['create_time'] = time();
                Db::table('chat_room')->insert($chat);
              }
            }
          }
      }

      return $return_data;
    }
    // 在线客服
    public function onlineService(){
      $data = Db::table('system_config')->where('name','in','hongbao_config,customer_service')->column('name,value');
      $return_data =[
        'online'=> json_decode($data['customer_service'],true),
        'hb' =>json_decode($data['hongbao_config'],true)['state']['value']
      ];
      return $return_data;
    }
    // 退出登录
    public function loginOut(){
      $return_data = [
        'code' => 0,
        'msg' => 'error',
      ];
      $user = $this->checkLogin();
      if($user['code'] == 0){
        $return_data['msg'] = '您没有登陆';
      }else{
        //$data = Db::table('login_log')->field('create_time')->where(['user_id'=>$user['data']['id']])->order('create_time DESC')->find();
        if($user['data']['active_time'] > 0 && Db::table('user')->where('id',$user['data']['id'])->setDec('active_time', 20 * 60)){
          $return_data['msg'] = '已安全退出登陆';
          $return_data['code'] = 1;
        };
      }
      cookie('token', null);
      return $return_data;
    }
    //首页会员动态
    public function userAction(){
      // $data = input('post.');
      $rs = Db::table('capital_detail')
        ->alias('a')
        ->join('user b','a.user_id=b.id','left')
        ->where('a.type','in',[0,1,2,3])
        ->order('a.id','DESC')
        ->field('a.*,b.username,b.type as utype')
        ->limit(10)
        ->select();
        // print_r($rs);die;
        if(!empty($rs)){
          $arr = [
            '0' => ['playing','下注：'],
            '1' => ['money-out','提现：'],
            '2' => ['money-out','充值：'],
            '3' => ['winning','中奖：'],
          ];
          foreach ($rs as $key => $value) {
            $strl = mb_strlen($value['username']);
            $return_data[$key] = [
              'class' => $arr[$value['type']][0],
              'type' => $arr[$value['type']][1],

              'username' => $value['utype'] == 1 ? '试玩用户' :(mb_substr($value['username'],0,floor($strl/2) ).'**'),
              // 'username' => $value['utype'] == 1 ? '试玩用户' : (mb_substr($value['username'],0,3).'****'),
              'text' => '',
              'money' => '￥'.$value['money'],
              'time' => date('Y-m-d H:i',$value['create_time'])
            ];
          }
        }else{
          $return_data = '';
        }
        // { class:'money-out',type:'提现:',username:'张**',text:'(成功提现): ',money:'￥500',time:'[02-06 9:50]' },

      return $return_data;
       //print_r($return_data);
    }

    /**
     * 访问删除机器人
     * @param number $val 删除几天以前的机器人,默认当天0点
     * @return Boolean
     */
    public function robotDelete(){
      $val = input('param.type');
      if(!isset($val) || $val == 0){
        $time = strtotime(date('Y-m-d 00:00:00'));
      }else{
        $time = strtotime(date('Y-m-d 00:00:00',strtotime($val." day")));
      }
      if(Db::table('robot_bet')->where( 'create_time','<',$time )->delete()){
        return json_encode(['code'=>1,'删除成功']);
      }else{
        return json_encode(['code'=>-1,'删除失败或没有数据删除']);
      }
    }

    /**
     * 机器人随机投注
     * @param int 彩种类型
     * @return string json
     */
    public function robotOn(){
      $type = input('param.type');
      //print_r(isset($data));
      $return_data = [
        'code' => 1,
        'msg' => '机器人投注成功'
      ];
      if(!isset($type) || !is_numeric($type) ){
        $return_data['code'] = -1;
        $return_data['msg'] = '数据出错';
        return $return_data;
      }
      // print_r(!isset($type));
      // die;
      //获取期号
      $expect = lottery28::lottery($type);
      //获取对应机器人
      $robot = Db::table('robot')->where('type',['=',0],['=',$type],'or')->column('id');//select();
      $config = Db::table('lottery_config')->field('basic_config,switch')->where('type','=',$type)->find();
      // if($expect['expect'] == 0){
      //   $return_data['code'] = -2;
      //   $return_data['msg'] = '维护中';
      // } elseif ($expect['time'] <= 0){
      //   $return_data['code'] = -3;
      //   $return_data['msg'] = '封盘中';
      // } elseif (empty('$robot') || empty($config)){
      //   $return_data['code'] = -4;
      //   $return_data['msg'] = '该类彩票机器人无法投注';
      // } elseif ($config['switch'] == 0){
      //   $return_data['code'] = -5;
      //   $return_data['msg'] = '此彩种已关闭了,停止数据注入';
      // }

      if($return_data['code'] > 0){
        $config['basic_config'] = json_decode($config['basic_config'],true);
        if(in_array($type,[24,25,26,27])){
          // 28系列处理
          //随机房间0，1，2
          $other = rand(0,2);
          //请求出28房间设
          $rs = Db::table('room')->where('type','=',$type)->where('level','=',$other)->find();
          $rs['betting_min'] = $rs['betting_min'] == 0 ? 1 : $rs['betting_min'];
          $money = [$rs['betting_min'],$rs['betting_min'],$rs['betting_min']+(10*rand(1,10)),$rs['betting_min']+(10*rand(1,10)),$rs['betting_min']+(10*rand(1,10)),$rs['betting_min']+(10*rand(1,20)),rand($rs['betting_min'],floor($rs['betting_max']*0.6))];
          $on_money = $money[array_rand($money,1)];
          $bet = range(0,27);
          $bet = array_merge($bet,['a','b','c','d','ac','ad','bc','bd','green','yellow','blue','red']);
          //随机投注
          $lit = $bet[array_rand($bet,1)];
          $on_bet = [
            'code' =>$lit,
            'money'=>$on_money,
          ];
          print_r('28系列暂时未完成');die;
        }else{
          $money = [1,1,2,3,5,6,10,12,15,20,rand(1,100)];
          // 获取所有可下注内容
          $bet = [];
          foreach ($config['basic_config'] as $key => $value) {
            foreach ($value['items'] as $key1 => $value1) {
              $bet[] = [$key,$key1];
            }
          }
          //随机投注
          $lit = $bet[array_rand($bet,1)];
          $on_bet = json_encode([ $lit[0] => [ $lit[1] => 1 ] ]);
          $on_money = $money[array_rand($money,1)];
        }



        $content = [
          'robot_id' => $robot[array_rand($robot,1)], //随机机器人id
          'content' => $on_bet,    //投注内容
          'money' => $on_money,    //投注金额
          'expect' => $expect['expect'],
          'type' => $type,
          'create_time' => time(),
        ];
        if(in_array($type,[24,25,26,27])){
          $content['other'] = $other;
        }
        // if(!Db::table('robot_bet')->insert($content)){
        //   $return_data['code'] = -100;
        //   $return_data['msg'] = '机器人投注失败';
        // }
      }
      return json_encode($return_data);
    }
    //清理试玩用户方法
    public function deleteDemo(){
      $demo_id = Db::table('user')->where('type','=',1)->column('id');
      //如果有则删除
      if(!empty($demo_id)){
        // 记录累加表
        Db::table('accumulation')->where('user_id','in',$demo_id)->delete();
        // 资金明细表
        Db::table('capital_detail')->where('user_id','in',$demo_id)->delete();
        // 资金核审表
        Db::table('capital_audit')->where('user_id','in',$demo_id)->delete();
        // 聊天室表
        Db::table('chat_room')->where('user_id','in',$demo_id)->delete();
        Db::table('chat_room')->where('create_time','<',strtotime(date('Y-m-d 00:00:00')) )->delete();
        // 登录日志表
        Db::table('login_log')->where('user_id','in',$demo_id)->delete();
        // 个人设置表
        Db::table('user_config')->where('user_id','in',$demo_id)->delete();
        // 用户表
        Db::table('user')->where('type','=',1)->delete();
        // 用户银行设置表
        Db::table('user_bank')->where('user_id','in',$demo_id)->delete();
        // 用户信息表
        Db::table('user_info')->where('user_id','in',$demo_id)->delete();
        // msg消息表
        Db::table('user_message')->where('user_id','in',$demo_id)->delete();
        // 删除计划表plan
        Db::table('plan')->where('create_time','<',strtotime( date('Y-m-d 00:00:00',time()) ) )->delete();
        //投注合买表--删除
        $rs = Db::table('betting')->where('user_id','in',$demo_id)->where('money','=',0)->column('id');
        if(!empty($rs)){
          Db::table('betting_he')->where('betting_id','in',$rs)->delete();
          Db::table('betting_gen')->where('betting_id','in',$rs)->delete();
        }
        //投注追号表--删除
        $rs1 = Db::table('betting')->where('user_id','in',$demo_id)->where('expect','=',0)->column('id');
        if(!empty($rs1)){
          Db::table('betting_zhui')->where('betting_id','in',$rs1)->delete();
        }
        // 投注表
        Db::table('betting')->where('user_id','in',$demo_id)->delete();
      }
      return [
        'code' => 1,
        'msg' => '已清理试玩用户所有数据'
      ];
    }
    //清理图图片存储缓存
    public function deleteImg(){
      //获取当前正在使用的图片12幻灯片 30app下载二维码 15网站LOGO 33在线客服
      $rs = Db::table('system_config')->where('id','in',[12,15,30])->column('id,value','id');
      $list = [];
      if(isset($rs[12])){
        $rs[12] = json_decode($rs[12],true);
        foreach ($rs[12] as $key => $value) {
          $list[] = preg_replace('/^\/.*\/+/','',$value['img_url']);
        }
      }
      if(isset($rs[15])){
        $list[] = preg_replace('/^\/.*\/+/','',$rs[15]);
      }
      if(isset($rs[30])){
        $list[] = preg_replace('/^\/.*\/+/','',$rs[30]);
      }
      // if(isset($rs[33])){
      //   $rs[33] = json_decode($rs[33],true);
      //   if(isset($rs[33]['wx_service'])){
      //     foreach ($rs[33]['wx_service'] as $key => $value) {
      //       $list[] = preg_replace('/^\/.*\/+/','',$value);
      //     }
      //   }
      // }
      //还有支付图片
      $pay = Db::table('bank_pay')->where('qr_code','<>','')->column('qr_code');
      if(!empty($pay)){
        foreach ($pay as $key => $value) {
          $list[] = preg_replace('/^\/.*\/+/','',$value);
        }
      }

      // 所有使用图片$list[]
      // scandir 获取上传文件所有文件夹
      $files = scandir('./uploads/');
      // 遍历所有文件夹 是否有 $list 中存在的 如果没有就删除
      foreach ($files as $key => $value) {
        if($value == '.' || $value == '..'){continue;}
        $one = './uploads/'.$value.'/';
        $bit = scandir($one);
        //获取期号文件下的所有文件
        foreach ($bit as $key1 => $value1) {
          if($value1 == '.' || $value1 == '..'){continue;}
          if(!in_array($value1,$list)){
            //如果没有使用就删除
            unlink($one.$value1);
          }
        }
        //如果这个文件数量等于2 就删除这个空文件夹
        if(count(scandir($one)) == 2){
          rmdir($one);
        }
      }
      return [
        'code' => 1,
        'msg' => '清除图片缓存成功'
      ];
    }

}
