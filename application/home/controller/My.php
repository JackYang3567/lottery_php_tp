<?php
namespace app\home\controller;

use app\home\model\UserInfo;
use app\home\model\UserBank;
use app\home\model\UserConfig;
use app\home\model\SystemConfig;
use app\home\model\ChatRoom;
use app\home\model\User;
use app\home\model\Betting;
use app\home\model\BettingGen;
use app\home\model\LinePayOrder;
use think\Db;
use app\home\controller\Home;
use think\Cache;
use think\facade\Request;

class My extends Common
{
  public $user = [];
  public function _initialize(){
    $data = $this->checkLogin();
    if($data['code']){
      $this->user = $data['data'];
    }else{
      $this->error('您还没有登陆');
    }
  }
  //推广中心
  public function extension(){
    $data=input('post.');
    $return_data = [];
    $return_data['code'] = 1;
    if($this->user['type'] != 0){
        $return_data['code'] = -1;
        $return_data['msg'] = '请注册为正式会员';
    }else{
      if($data['type'] == 1){//收益
        $return_data['user'] = $this->user['id'];
        $return_data['data'] = [
          'today' => 0,    //今日收益
          'yesterday' => 0,//昨日收益
          'past' => 0,     //过往30天收益
        ];
        $time['today'] = ['create_time','between',[strtotime(date("Y-m-d 00:00:00",strtotime("-1 day"))),strtotime(date("Y-m-d 23:59:59",strtotime("-1 day")))]];
        $time['yesterday'] = ['create_time','between',[strtotime(date("Y-m-d 00:00:00",strtotime("-2 day"))),strtotime(date("Y-m-d 23:59:59",strtotime("-2 day")))]];
        $time['past'] = ['create_time','between',[strtotime(date("Y-m-d 00:00:00",strtotime("-30 day"))),time()]];

        foreach ($time as $key => $value) {
        $return_data['data'][$key] = Db::table('capital_detail')
                                ->where('user_id',$this->user['id'])
                                ->where($value[0],$value[1],$value[2])
                                ->where('type',8)
                                ->sum('money');
        }
        // print_r($return_data);
      }else{                 //用户
        $rs = Db::table('new_relationship')->field('child_one,child_two,child_three')->where('user_id','=',$this->user['id'])->find();
        if($data['time'] == ''){
          $start_time = strtotime(date('Y-m-d 00:00:00'));
          $end_time = time();
        }else{
          $start_time = strtotime($data['time'].' 00:00:00');
          $end_time = strtotime($data['time'].' 23:59:59');
        }
        // print_r($start_time);die;
        $bit=[];
        if(!empty($rs)){
          foreach ($rs as $key => $value) {
            if(!empty($value)){
              $child = explode(',',$value);
              $bit[$key]['count'] = count($child);
              $bet = 0;
              //foreach ($child  as $k => $v) {
                //此处默认计算一天的有效打码量(投注)
                // $bet = Db::table('capital_detail')
                //         ->where('user_id',$v)
                //         ->where('create_time','between',[$start_time,$end_time])
                //         ->where('type','in',[0,3])
                //         ->sum('money');
                // $bit[$key]['money'] = !empty($bet) ? $bet : 0;
                $u_all = Db::table('capital_detail')
                        ->field('type,money')
                        ->where('user_id','in',$child)
                        ->where('create_time','between',[$start_time,$end_time])
                        ->where('type','in',[0,6,14])
                        ->select();
                if(!empty($u_all)){
                  foreach ($u_all as $bk => $bv) {
                    if($bv['type'] == 6){
                      $bet -= $bv['money'];
                    }else{
                      $bet += $bv['money'];
                    }
                  }
                  $bit[$key]['money'] = round($bet,2);
                }else{
                  $bit[$key]['money'] = 0;
                }
              //}
            }else{
              $bit[$key]['count'] = 0;
              $bit[$key]['money'] = 0;
            }
          }
          $return_data['code'] = 1;
          // print_r($bit);die;
          $return_data['data'] = [
            ['level'=>1,'count'=>$bit['child_one']['count'],'money'=>$bit['child_one']['money']],
            ['level'=>2,'count'=>$bit['child_two']['count'],'money'=>$bit['child_two']['money']],
            ['level'=>3,'count'=>$bit['child_three']['count'],'money'=>$bit['child_three']['money']],
          ];
        }else{
          $return_data['code'] = -2;
          $return_data['msg'] = '无';
        }
      }
    }
    return $return_data;
    // print_r($return_data);
  }
  //代理查询详细数据
  public function exList(){
    $data = input('post.');
    // print_r($this->user);
    //查询下级
    $return_data = '';
    $rs = Db::table('new_relationship')->where('user_id','=',$this->user['id'])->find();
    // print_r(explode(',',$rs['child_two']));die;
    if(!empty($rs)){
        $arr = [
          '1' => 'child_one',
          '2' => 'child_two',
          '3' => 'child_three',
        ];
        $list_data = explode(',',$rs[$arr[$data['type']]]);
        if($data['time'] == ''){
          $start_time = strtotime(date('Y-m-d 00:00:00'));
          $end_time = time();
        }else{
          $start_time = strtotime($data['time'].' 00:00:00');
          $end_time = strtotime($data['time'].' 23:59:59');
        }
        $return_data = [];
        // die;
        //遍历请求的下级内容
        // print_r($list_data);
        // $user = User::where('id', 'in', $list_data)->select();
        $user = User::where('id', 'in', $list_data)->select();
        // ->paginate(20)
        // ->toArray();
        $username = $user->append(['log'])->toArray();
        // print_r($username);die();
        // $username = Db::table('user')
        //     ->alias('a')
        //     ->field('a.id,a.username')
        //     ->join('login_log b','a.id = b.user_id','left')
        //     ->where('a.id','in',$list_data)
        //     ->order('b.create_time','DESC')
        //     ->select();
        // $username = Db::query('SELECT *,login_log.* FROM `user` join `login_log` where `id` in(119, 120, 121) and GROUP BY login_log.user_id order by login_log.create_time DESC');
            //->column('a.id,a.username,max(b.create_time) time');
        // $username = Db::table('user')
        //     ->where('id','in',$list_data)
        //     ->column('id,username');
        // print_r($username);die;
        foreach ($username as $key => $value) {
          //请求当前
          $list = Db::table('capital_detail')
                  ->field('type,money')
                  ->where('user_id','=',$value['id'])
                  ->where('create_time','between',[$start_time,$end_time])
                  ->where('type','in',[0,6,14])
                  ->select();
          $bet = 0;
          $return_data[$key] = [
            'username' => $value['username'],
            'line' => 0,
            'bet' => 0,
          ];
          if(!empty($list)){
            foreach ($list as $bk => $bv) {
              if($bv['type'] == 6){
                $bet -= $bv['money'];
              }else{
                $bet += $bv['money'];
              }
            }
            $return_data[$key]['bet'] = round($bet,2);
          }
          if($value['log'] == ''){
            $return_data[$key]['line'] = $value['create_time'];
          }else{
            $return_data[$key]['line'] = $value['log'];
          }
          // if($value['log'] != ''){
          //   if( time() - strtotime($value['log']) <= (60*20) ){
          //     $return_data[$key]['line'] = 1;
          //   }
          // }

        }
    }
    return $return_data;
  }
  //消息中心
  public function messages(){
    $data = input('post.');
    // if(false && $data['type'] = 1){//刷新
    //   $rs = Db::table('user_message')->field('id,title,content,create_time')->where('user_id',$this->user['id'])->where('state',$data['state'])->order('id','DESC')->limit(25)->select();
    // }else{//加载
      $rs = Db::table('user_message')->field('id,title,content,create_time')->where('user_id',$this->user['id'])->where('state',$data['state'])->order('id','DESC')->paginate(25)->each(
        function($item,$key){
          $item['create_time'] = date("Y-m-d",$item['create_time']);
          return $item;
        }
      );
    // }
     return $rs;
  }
  //信息中心-阅读
  public function msgRead(){
    $data = input('post.');
    Db::table('user_message')->where('id',$data['id'])->update(['state'=>1]);
    // print_r($data);
  }
  //信息中心-删除
  public function msgDel(){
    $data = input('post.');
    $return_data['code'] = -1;
    $return_data['msg'] = '删除失败';
    if(Db::table('user_message')->where('user_id',$this->user['id'])->delete($data)){
      $return_data['code'] = 1;
      $return_data['msg'] = '删除成功';
    }
    return $return_data;
  }
  //基本资料设置-修改资料
  public function getInfo(){
    if($this->user['type'] == 1){
      return ['code'=>-1,'msg'=>'请先注册正式会员'];
    }
    $data = UserInfo::field('phone_number,qq,email,id_number,name')->where(['user_id'=>$this->user['id']])->find();
    if(empty($data)){
      return ['code'=>0];
    }
    return ['code'=>1,'data'=>$data];
  }
  public function editInfo(){
    if($this->user['type'] == 1){
      return ['code'=>-1,'msg'=>'请先注册正式会员'];
    }
    $get = input('post.');
    // print_r($get);die;
    if(Db::table('user_info')->field('user_id')->where('user_id','<>',$this->user['id'])->where('phone_number','=',$get['phone_number'])->find()){
      return ['code'=>-2,'msg'=>'手机号码已被注册'];
    }

    $data = (new UserInfo)->allowField(true)->save(array_filter(input('post.')) + ['user_id'=>$this->user['id']],(UserInfo::field('user_id')->where(['user_id'=>$this->user['id']])->find()?true:false));
    if($data){
      return ['code'=>1,'msg'=>'资料更新成功'];
    }else{
      return ['code'=>0,'msg'=>'没有新的资料更新'];
    }
  }
  //基本资料设置-安全问题
  public function question(){
    if($this->user['type'] == 1){
      return ['code'=>-1,'msg'=>'请先注册正式会员'];
    }
    $data = input('post.');
    $return_data = [
      'code' => -1,
      'msg' => '设置安全问题信息缺失'
    ];
    if(!empty($data) && !empty($data['key']) && !empty($data['value']) && !empty($data['password'])){
      if(md5($data['password']) == $this->user['password']){
        if(UserInfo::update(['user_id'=>$this->user['id'],'question'=>$data['key'].'|'.$data['value']])){
          $return_data['code'] = 1;
          $return_data['msg'] = '安全问题设置成功';
        }
        else{
          $return_data['msg'] = '没有更新安全问题';
        }
      }else{
        $return_data['msg'] = '您输入的登陆密码不正确';
      }
    }
    return $return_data;
  }

  /**
   * 基本设置-安全问题初始值
   * @return array 0问题 1答案
   */
  //基本设置-安全问题初始值
  public function quesGet(){
    if($this->user['type'] == 1){
      return ['code'=>-1,'msg'=>'请先注册正式会员'];
    }
    $rs = Db::table('user_info')->field('question')->where('user_id',$this->user['id'])->find();
    $return_data = '';
    if(isset($rs['question'])){
      $return_data = explode('|',$rs['question']);
    }
    return $return_data;
  }

  //基本资料设置-修改密码
  public function editPassword(){
    if($this->user['type'] == 1){
      return ['code'=>-1,'msg'=>'请先注册正式会员'];
    }
    $data = input('post.');
    $return_data = [
      'code' => 0,
      'msg' => '修改密码信息缺失'
    ];
    if(!empty($data) && !empty($data['password']) && !empty($data['new_password']) && !empty($data['to_password'])){
      if($data['new_password'] == $data['to_password']){
        if(md5($data['password']) == $this->user['password']){
          if(User::update(['id'=>$this->user['id'],'password'=>md5($data['new_password'])])){
            $return_data['code'] = 1;
            $return_data['msg'] = '密码修改成功';
          }
          else{
            $return_data['msg'] = '密码没有更新';
          }
        }else{
          $return_data['msg'] = '您的原密码不正确';
        }
      }else{
        $return_data['msg'] = '两次新密码不一致';
      }
    }
    return $return_data;
  }
  //银行卡设置-查询银行卡信息
  public function getBank(){
    if($this->user['type'] == 1){
      return ['code'=>-1,'msg'=>'请先注册正式会员'];
    }
    $data = UserBank::field('username,name,number,branch')->where(['user_id'=>$this->user['id']])->find();
    $data_phone = UserInfo::field('phone_number')->where(['user_id'=>$this->user['id']])->find();
    $phone = '';
    if(!empty($data_phone) && isset($data_phone['phone_number'])){
      $phone = $data_phone['phone_number'] ?? '';
    }
    $rs = $this->getDraw();
    // $data['number']=123;
    // var_dump($data['number']);
    // var_dump(!empty($data['number']));die;
    // print_r($data['phone']);die;
    if(empty($data) || (!isset($data['number']) || !isset($data['username'])) || $data['number'] == null ){
      return ['code'=>0,'phone'=>$phone,'change_type'=>$rs];
    }
    $data['number'] = substr($data['number'],0,4).'******'.substr($data['number'],-4);
    $data['username'] = mb_substr($data['username'],0,1,'utf-8').'**';
    return ['code'=>1,'phone'=>$phone,'data'=>$data,'change_type'=>$rs];
  }
  //提款密码 1:未设置 2:已设置
  public function getDraw(){
    $rs = Db::table('user_info')->field('draw_password')->where(['user_id'=>$this->user['id']])->find()['draw_password'] ? 2 : 1;
    return $rs;
  }
  //银行卡设置-绑定银行卡信息
  public function editBank(){

    if($this->user['type'] == 1){
      return ['code'=>-1,'msg'=>'请先注册正式会员'];
    }
    $get_data = input('post.');
    $return_data['code'] = 1;
    // print_r($get_data);die;
    $user_password = Db::table('user_info')->where('user_id',$this->user['id'])->field('draw_password')->find();
    if(!empty($user_password['draw_password']) ? md5($get_data['password']) == $user_password['draw_password'] : md5($get_data['password'])==$this->user['password']){


      //此处判断手机号是否有绑定
      if($get_data['phone'] == ''){
//        if(Db::table('user_info')->where('phone_number','=',$get_data['phone'])->find()){
//          return ['code'=>-3,'msg'=>'手机号已被注册,绑定失败'];
//        }
          return ['code'=>-3,'msg'=>'身份证号码不能为空'];
      }
      if(UserBank::get($this->user['id'])){
        return ['code'=>-2,'msg'=>'无法再次绑定'];
      };
      if(isset($get_data['type']) && $get_data['type'] == 1){
        if((new UserInfo)->save(['user_id'=>$this->user['id'],'phone_number'=>$get_data['phone']],(UserInfo::field('user_id')->where(['user_id'=>$this->user['id']])->find()?true:false) )){
          return ['code'=>1,'msg'=>'身份证号码更新成功'];
        }else{
          return ['code'=>-2,'msg'=>'身份证号码更新失败'];
        }
      }

      // if($get_data['phone'] != '' && Db::table('user_info')->where('phone_number','=',$get_data['phone'])->find()){
      //   return ['code'=>-3,'msg'=>'手机号已被注册,绑定失败'];
      // }
      //此处判断银行卡号是否有绑定
      if(Db::table('user_bank')->where('number','=',$get_data['number'])->find()){
        return ['code'=>-2,'msg'=>'银行卡已被注册,绑定失败'];
      }
      //此处判断是否是第一次设置 并查询提供赠送金额
      $zs = 0;
      if($this->user['type'] == 0 && !Db::table('user_bank')->where('user_id','=',$this->user['id'])->find() ){
          $zs = Db::table('system_config')->field('value')->where('name','=','new_regist')->find()['value'];
      }

      if((new UserBank)->allowField(true)->save(input('post.') + ['user_id'=>$this->user['id']],(UserBank::field('user_id')->where(['user_id'=>$this->user['id']])->find()?true:false))){
        if($get_data['phone'] != ''){
          (new UserInfo)->save(['user_id'=>$this->user['id'],'phone_number'=>$get_data['phone']],(UserInfo::field('user_id')->where(['user_id'=>$this->user['id']])->find()?true:false));
        }
        $return_data['msg'] = '银行卡信息更新成功';
        if($zs > 0){
          $add = [
            'uid' => $this->user['id'],
            'money' => $zs,
            'type' => 5,
            'explain' => '完善资料赠送'.$zs.'￥'
          ];
          moneyAction($add);
        }
      }else{
        $return_data['code'] = -2;
        $return_data['msg'] = '银行卡信息没有新的资料更新';
      }
    }else{
          $return_data['code'] = -3;
          $return_data['msg'] = !empty($user_password['draw_password']) ? '提款密码错误':'登录密码错误';
    }
    return $return_data;
  }
  //银行卡设置-设置取款码
  public function setDrawPassword(){
    if($this->user['type'] == 1){
      return ['code'=>-1,'msg'=>'请先注册正式会员'];
    }

    $data = input('post.');
    $return_data = [
      'code' => 0,
      'msg' => '设置提款密码信息缺失'
    ];
    if(!empty($data) && !empty($data['password']) && !empty($data['draw_password']) && !empty($data['to_password'])){
      if($data['draw_password'] == $data['to_password']){
        $rs = Db::table('user_info')->field('draw_password')->where(['user_id'=>$this->user['id']])->find();
        if(empty($rs['draw_password']) ? md5($data['password'])==$this->user['password'] : md5($data['password']) == $rs['draw_password']){
          if((new UserInfo)->save(['user_id'=>$this->user['id'],'draw_password'=>md5($data['draw_password'])],($rs?true:false))){
            $return_data['code'] = 1;
            $return_data['msg'] = '提款密码设置成功';
          }
          else{
            $return_data['msg'] = '提款密码没有更新';
          }
        }else{
          $return_data['msg'] = empty($rs['draw_password'])? '您的登录密码不正确,设置失败' : '旧提款密码错误';
        }
      }else{
        $return_data['msg'] = '两次的提款密码不一致';
      }
    }
    // print_r($return_data);
    return $return_data;
  }

  public function moneyAllType(){
    return moneyType();
  }

  //下注记录表 - 开奖号记录表 - 账户流水表 - 充值记录表 - 提现记录表
  public function queryData(){
    $data = input('post.');

    $data['where'] = json_decode($data['where'],true);
    //可查询的表  投注表      开奖号表      账户流水表      充值记录表   提现记录表
    //$surface = ['betting','lottery_code','capital_detail','recharge','cash'];

    // if(false && ($end - $start)>(30*86400)){//默认时间判断取消
    //   $rs['msg'] = '只能查询最近30天';
    //   $rs['code'] = -1;
    // }else{
      if($data['click'] == 1){             //启用查询时间
          //时间处理
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
      if($data['code'] == 0){//---------------------------------------------------投注表
          //开启查询条件
          if($data['click'] == 1 && !empty($data['where']['type'])){
            $where[] = ['type','=',$data['where']['type']];
          }

          $where[] = ['user_id','=',$this->user['id']];
          //查询0自购1合买2跟单
          if($data['buy'] == 0){
            $where[] = ['money','>',0];
            $table = 'betting';
            $rs = Db::table($table)
                ->where($where)
                ->order('id','DESC')
                ->paginate(10)->toArray();
                // print_r($where);die;
          }else{
            $table = 'betting_gen';
            if($data['buy'] == 1){
              $where[] = ['main','=',1];
              $where1[] = ['a.main','=',1];
            }else{
              $where[] = ['main','=',0];
              $where1[] = ['a.main','=',0];
            }
            $rs = Db::table($table)
                  ->alias('a')
                  ->join('betting b','a.betting_id=b.id','left')
                  ->join('betting_he c','a.betting_id=c.betting_id','left')
                  ->join('user d','a.user_id=d.id')
                  ->where('a.user_id','=',$this->user['id'])
                  ->where($where1)
                  ->order('a.id','DESC')
                  ->field('a.user_id,a.money,a.win as swin,a.create_time,b.win,b.content,b.type,b.other,b.expect,b.state,b.id,a.main,c.*,d.username')
                  ->paginate(20)->toArray();
          }


          // 总计暂时取消
          // $rs['all_money'][0] = Db::table($table)
          //       ->where($where)
          //       ->sum('money');
          // $rs['all_money'][1] = Db::table($table)
          //       ->where($where)
          //       ->sum('win');
          $rs['sin_money'] = [0,0];
          if(!empty($rs['data'])){
            $i=0;
//              dump($rs['data']);die;
            foreach ($rs['data'] as $key => $value) {
                //2019=05-05 新加代码
                $return_data[$i]['id'] = $value['id'];
                $return_data[$i]['user_id'] = $value['user_id'];
                $return_data[$i]['state'] = $value['state'];


                //print_r($value);
                // $rs['sin_money'][0] += $value['money'];
                // $rs['sin_money'][1] += $value['win'];
                // 投注格式化
                $change = bettingFormat([$value],$value['type']);
                // 是否有追号 35 不需要追号
                $return_data[$i]['type'] = $value['type'];
                // 竞猜足球 水果拉霸 闭月羞花 老虎机
                // print_r(123);die;
                if(in_array($value['type'],[35, 53, 54 ,55 ,56])){
                  $return_data[$i]['lotype'] = 1;
                  $return_data[$i]['expect'] = $change[0]['lottery_name'];
                }else if($value['expect'] == 0){
                  $return_data[$i]['lotype'] = 2;       //前端判断是否追号字段
                  $return_data[$i]['expect'] = $change[0]['lottery_name'].'追号';
                  if($value['state'] == 0 || $value['state'] == 3){
                    $return_data[$i]['expect'] .='中..';
                  }else if($value['state'] == 1){
                    $return_data[$i]['expect'] .='已结算';
                  }else if($value['state'] == 2){
                    $return_data[$i]['expect'] .='已撤单';
                  }
                  $return_data[$i]['expect_num'] = [];
                  // print_r($return_data[$i]['expect_num']);die;
                }else{
                  $return_data[$i]['lotype'] = 1;
                  $return_data[$i]['expect'] = $change[0]['lottery_name'].'第'.$value['expect'].'期'; //投注彩种和期号
                  $return_data[$i]['win'] = $value['state'] == 1 ? $value['win']:'未开奖';              //总共赢得金钱
                }
                //如果21 要分ab盘
                if($value['type'] == 21){
                  $return_data[$i]['expect'] = '('.($value['other'] == 0? 'A盘':'B盘').')'.$return_data[$i]['expect'];
                }
                 // print_r($value);die;
                if($data['buy'] == 1 || $data['buy'] == 2){ //合买数据处理
                  $return_data[$i]['percent'] = round(($value['buy']/$value['all'])*100);
                  $return_data[$i]['bd'] = $value['bd'];
                  $return_data[$i]['num'] = $value['num'];
                  $return_data[$i]['all'] = $value['all'];
                  $return_data[$i]['buy'] = $value['buy'];
                  $return_data[$i]['zg'] = $value['money'];
                  if($value['user_id'] == $this->user['id']){
                    $return_data[$i]['onname'] = '自己的合买单';
                  }else{
                    $return_data[$i]['onname'] = mb_substr($value['username'],0,2)."***";
                  }
                  if($value['main'] == 1){ //判断如果自己是发起者 则计算全补奖金
                    $return_data[$i]['swin'] = Db::table('betting_gen')
                                             ->where('betting_id','=',$value['id'])
                                             ->where('user_id','=',$value['user_id'])
                                             ->sum('win');
                  }else{
                    $return_data[$i]['swin'] = $value['swin'];
                  }
                  // print_r($return_data[$i]['percent']);
                }
                $return_data[$i]['win'] = $value['win'];
                // die;
                if($value['state'] == 2){
                  $return_data[$i]['win'] = '已撤单';
                }

                if($value['type'] == 35){  //投注内容
                  $return_data[$i]['play'] = $change[0]['content']['play'];
                  $return_data[$i]['game'] = $change[0]['content']['game'];
                }else{
                  $return_data[$i]['play'] = explode(';',$change[0]['content']);
                  if($data['buy'] == 2 && $value['open'] > 1 && $value['user_id'] != $this->user['id']){
                    if($value['open'] == 2){
                      if($value['state'] == 0){
                        $return_data[$i]['play'] = ['发起者设置(截止后公开)'];
                      }
                    }else if($value['open'] == 3){
                        $return_data[$i]['play'] = ['发起者设置(完全保密)'];
                    }
                  }

                }
                  $return_data[$i]['money'] = $value['money'];                                        //投注金额
                  $return_data[$i]['time'] = date('Y-m-d H:i:s',$change[0]['create_time']);           //投注时间
                  //$return_data[$i]['odds'] = '';//赔率显示暂时取消
                  $return_data[$i]['list'] = $value['id'];                                                 //详细加载使用的id属性
                  $return_data[$i]['open'] = '';//!empty($open) ? explode(",",$open): $open;               //开奖号合买查询有open 字段不要混淆
                  $return_data[$i]['plus'] = '';//$this->openPlus($return_data[$i]['open'],$value['type']);//开奖和值
                  $return_data[$i]['explain'] = '';//explode(';',$value['explain']); //$value['explain'];  //格式化明细
                $i++;
            }
          }else{
            $return_data = '';
          }
           $rs['data'] = $return_data;
      }else if($data['code'] == 1){
          //----------------------------------开奖号记录表
            // 开奖记录已转移Home 控制器
            // if($data['where']['type'] == 26){
            //   $data['where']['type1'] = 2;
            // }else if($data['where']['type'] == 27){
            //   $data['where']['type1'] = 12;
            // }else{
            //   $data['where']['type1'] = $data['where']['type'];
            // }
            // if($data['click'] == 1 && !empty($data['where']['expect'])){
            //   $where[] = ['expect'=>$data['where']['expect']];
            // }
            //  $where[] = ['type','=',$data['where']['type1']];
            // $rs = Db::table('lottery_code')
            //   ->where($where)
            //   ->order('expect','DESC')
            //   ->paginate(10)
            //   ->toArray();
            // foreach ($rs['data'] as $key => &$item) {
            //   if($data['where']['type'] == 26 || $data['where']['type'] == 27){
            //     $item['content'] = array_slice(explode(',',$item['content']),0,3);
            //   }else{
            //     $item['content'] = !empty($item['content']) ? explode(",",$item['content']): '';
            //   }
            //   $item['create_time'] = date("Y-m-d H:i:s",$item['create_time']);
            //   $item['plus'] = $this->openPlus($item['content'],$data['where']['type']);
            //
            //   $item['open_type'] = $this->openType($data['where']['type']);
            //   if($item['open_type'] == 3){
            //     foreach ($item['content'] as $key => &$value) {
            //       $bit = lotteryL::codeType($value,date("Y"));
            //       $value = [$bit['code'],$bit['wave'][1],$bit['zodiac'][0]];
            //     }
            //   }
            // }

      }else if($data['code'] == 2){//-------------------------------账户流水表

        $type_arr = moneyType();

        $where[] = ['user_id','=',$this->user['id']];
        $where[] = ['money','>',0];
        if($data['click'] == 1 && isset($data['where']['type']) ){
          $where[] = ['type','=',$data['where']['type']];
        }else{
          $where[] = ['type','<>',9];
        }
        // else{
        //   $where[] = ['type','<',9];
        // }
// print_r($where);die;
        $rs = Db::table('capital_detail')
          ->where($where)
          ->field('money,type,create_time')
          ->order('create_time','DESC')
          ->paginate(20)
          ->toArray();
        // print_r($rs);
        // die;

        $rs['money'] = 0;
        foreach ($rs['data'] as $key => &$value) {
           $value['type'] = $type_arr[$value['type']];
           $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
           $rs['money'] +=$value['money'];
        }
        // $rs['moneyAll'] = Db::table('capital_detail')
        //   ->where($where)
        //   ->sum('money');
      }else if($data['code'] == 3 || $data['code'] == 4){//------------------------------------充值记录表&&提现记录表
        $where[] = ['user_id','=',$this->user['id']];
        $where[] = ['type','=',($data['code'] == 3 ? 0:1)];
        if($data['click'] == 1 && $data['where']['type'] != ''){
            $where[] = ['state','=',$data['where']['type']];
        }
        $rs = Db::table('capital_audit')
            ->field('pay_account,money,state,create_time,remarks')
            ->where($where)
            ->order('create_time','DESC')
            ->paginate(10)
            ->toArray();
        // $rs['all_money']= Db::table('capital_audit')
        //     ->where($where)
        //     ->sum('money');
            $rs['sin_money'] = 0;

        $state = $data['code'] == 3 ? ['未支付','已支付','已撤单']:['未处理','已处理','已撤单'];
        foreach ($rs['data'] as $key => &$value) {
          $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
          $value['state'] = $state[$value['state']];
          $rs['sin_money'] += $value['money'];
        }
        // print_r($rs);
      }else if($data['code'] == 5){//-------------------------------抽奖记录表
        $where[] = ['user_id','=',$this->user['id']];
        $where[] = ['type','=',10];
        $rs = Db::table('capital_detail')
          ->field('money,explain,create_time')
          ->where($where)
          ->order('create_time','DESC')
          ->paginate(20)
          ->toArray();
          foreach ($rs['data'] as $key => &$value) {
            $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
          }
      }
      $rs['code'] = 1;
    //}
    return $rs;
  }
  /**
   * 在线充值记录表
   */
  public function rechargeLine(){
    (new LinePayOrder)->revoke();
    $data = input('post.');
    $where = [
      ['user_id','=',$this->user['id']]
    ];
    if($data['click'] == 1){
      $data['where'] = json_decode($data['where'],true);
      //时间处理
      $start = $data['where']['start_time'];
      $end = $data['where']['end_time'];
      if(strtotime($start) > strtotime($end)){
        $start = [$end,$end = $start][0];
      }
      $start = $start.' '.'00:00:00';
      $end = $end.' '.'23:59:59';
      $where[] = ['create_time','>=',$start];
      $where[] = ['create_time','<=',$end];
      if(!empty($data['where']['type'])){
        $where[] = ['status','=',$data['where']['type']];
      }
    }
    // // $where[] = ['user_id','=',$this->user['id']];
    $rs = Db::table('line_pay_order')
        // ->field('money,explain,create_time')
        ->where($where)
        ->order('create_time','DESC')
        ->paginate(20)
        ->toArray();
    if(!empty($rs)){
      $status = ['未支付','已支付','已撤单'];
      foreach($rs['data'] as $k => &$v){
        $v['name'] = bankTool($v['name']);
        $v['status'] = $status[$v['status']];
      }
    }
    return $rs;
    // print_r($rs);
  }
  //账户流水-报表
  public function waterReport(){
    $data = input('post.');
    // print_r($data);
    if($data['start_time'] == ''){
      $start = strtotime(date('Y-m-d 00:00:00',strtotime('-7 days')));
    }else{
      $start = strtotime($data['start_time'].' 00:00:00');
    }
    if($data['end_time'] == ''){
      $end = time();
    }else{
      $end = strtotime($data['end_time'].' 23:59:59');
    }
    if($start > $end){
      $start = [$end,($end=$start)][0];
    }
    $return_data = [
      ['name'=>'下注','num'=>0],
      ['name'=>'中奖','num'=>0],
      ['name'=>'充值','num'=>0],
      ['name'=>'提现','num'=>0],
      ['name'=>'赠送','num'=>0],
      ['name'=>'代理反佣','num'=>0],
    ];
    //查询最新一期的累加表数据
    $now_data = Db::table('accumulation')->where('create_time','<=',$end)->where('user_id','=',$this->user['id'])->order('create_time','DESC')->find();
    if(!empty($now_data)){
      //如果有数据
      $before_data = Db::table('accumulation')->where('create_time','<=',$start)->where('user_id','=',$this->user['id'])->order('create_time','DESC')->find();
      if(!empty($before_data)){
        //是否有撤单资金
        $return_data[0]['num'] = $now_data['use_money'] - $before_data['use_money'] - ($now_data['refund'] - $before_data['refund']);
        $return_data[1]['num'] = $now_data['winning'] - $before_data['winning'];
        $return_data[2]['num'] = ($now_data['in_money'] + $now_data['online_money']) - ($before_data['in_money'] + $before_data['online_money']);
        $return_data[3]['num'] = $now_data['out_money'] - $before_data['out_money'];
        $return_data[4]['num'] = $now_data['give'] - $before_data['give'];
        $return_data[5]['num'] = $now_data['maid'] - $before_data['maid'];
      }else{
        $return_data[0]['num'] = $now_data['use_money'] - $now_data['refund'];
        $return_data[1]['num'] = $now_data['winning'];
        $return_data[2]['num'] = $now_data['in_money'];
        $return_data[3]['num'] = $now_data['out_money'];
        $return_data[4]['num'] = $now_data['give'];
        $return_data[5]['num'] = $now_data['maid'];
      }
    }
    foreach ($return_data as $key => &$value) {
      $value['num'] = round($value['num'],2);
    }
    // print_r($now_data);

    // $a = ['a'=>5,'b'=>4];
    // print_r(isset($a['c']));
    // 查询资金明细表
    // $rs = Db::table('capital_detail')->where('create_time',['>',$start],['<',$end],'and')->where('type','in',[0,1,2,3,5,7,8,13,14])->where('user_id','=',$this->user['id'])->select();
    // foreach ($rs as $key => $value) {
    //   switch ($value['type']) {
    //     case 0:
    //       $return_data[0]['num'] += $value['money'];
    //       break;
    //     case 1:
    //       $return_data[3]['num'] += $value['money'];
    //       break;
    //     case 2:
    //       $return_data[2]['num'] += $value['money'];
    //       break;
    //     case 3:
    //       $return_data[1]['num'] += $value['money'];
    //       break;
    //     case 5:
    //       $return_data[4]['num'] += $value['money'];
    //       break;
    //     case 7:
    //       $return_data[2]['num'] += $value['money'];
    //       break;
    //     case 8:
    //       $return_data[5]['num'] += $value['money'];
    //       break;
    //     case 13:
    //       $return_data[0]['num'] -= $value['money'];
    //       break;
    //     case 14:
    //       $return_data[0]['num'] += $value['money'];
    //     default:
    //       break;
    //   }
    // }
     return $return_data;
  }
  //下注记录表-追号查看
  public function betDetailsChasing(){
    $data = input('post.');
    $model = Betting::get($data['key']);
    $return_data['expect'] = $model->zhui()->select()->toArray();
    // if($return_data['expect'][0]['stop'] == 0){
    //   $return_data['type'] = '中奖继续追号';
    // }else{
    //   $return_data['type'] = '中奖停止追号';
    // }

    $expectAll = [];
    foreach ($return_data['expect'] as $k => &$v) {
      $v['state'] = $v['state'] == 1 ? '已派奖': '未开奖';
      $expectAll[] = $v['expect'];
    }

    return $return_data;
  }
  //下注记录表-加载详情
  public function betDetails(){
    $data = input('post.');
    // print_r($data);die;
    $bet = Db::table('betting')->where('id',$data['key'])->find();
    // print_r($bet);die;
    //print_r($bet);

    if($bet['state'] == 0 || $bet['explain'] == '' ){        //普通投注
      $return_data = '';
    }else{
      if($bet['type'] == 26){
        $bet['type1'] = 2;
      }else if($bet['type'] == 27){
        $bet['type1'] = 12;
      }else{
        $bet['type1'] = $bet['type'];
      }

      if($bet['expect'] != 0){
        $return_data['win'] = $bet['win'];                                           //赢得金钱
        $return_data['explain'] = explode(';',$bet['explain']); //$value['explain']; //格式化明细
        $open = Db::table('lottery_code')
              ->where('expect','=',$bet['expect'])
              ->where('type','=',$bet['type1'])
              ->find()['content'];
        if(empty($open)){
          return $return_data;
        }
        // print_r($open);die;
        if($bet['type'] == 0 || $bet['type'] == 1){ //龙虎百家处理方式
          $open = json_decode($open,true);
          if($bet['type'] == 0){
            $return_data['open'] = [
              ['闲',[]],
              ['庄',[]],
            ];
            foreach ($open['code'] as $ok => $ov) {
              if($ov != 0){
                preg_match_all("/\d+/s",$ov, $num);
                if($ok%2 == 0){
                  $return_data['open'][0][1][] = [$num[0][0],substr($ov, -1)];
                }else{
                  $return_data['open'][1][1][] = [$num[0][0],substr($ov, -1)];
                }
              }
            }
          }else{
            preg_match_all("/\d+/s",$open['code'][0], $num);
            preg_match_all("/\d+/s",$open['code'][1], $num1);
            $return_data['open'] = [
              ['龙',[[$num[0][0],substr($open['code'][0], -1)]]],
              ['虎',[[$num1[0][0],substr($open['code'][1], -1)]]],
            ];
          }
        }else if($bet['type'] == 52){ //百人牛牛
            $bit = (new Lottery28)->brnnConfig($open);
            $arr = ['S','H','C','D'];
            foreach($bit as &$item){
              foreach ($item['code'] as &$vo) {
                $vo[1] = $arr[$vo[1]];
              }
            }
            // print_r($bit);die;
            $return_data['open'] = [
              [('蓝方'.($bit[0]['win'] == 1 ? '胜':'败')),$bit[0]['code']],
              [('红方'.($bit[1]['win'] == 1 ? '胜':'败')),$bit[1]['code']],
            ];
            $return_data['plus'] = [['name'=>'牛牛','data'=>('蓝方-'.$bit[0]['type'].','.'红方-'.$bit[1]['type'])] ];
        }else{ //普通处理方式
           if($bet['type'] == 26 || $bet['type'] == 27){
              $return_data['open'] = array_slice(explode(',',$open),2);
            }else{
              $return_data['open'] = explode(",",$open);    //开奖号
            }
            $return_data['plus'] = Home::openPlus($return_data['open'],$bet['type']);   //开奖和值
        }

      }else{
        $op = Betting::get($data['key'])->zhui()->column('expect');
        $op1 = Db::table('lottery_code')->where('expect','in',$op)->where('type','=',$bet['type1'])->column('content');
        $return_data['open'] = [];
        foreach ($op as $key => $value) {
          $return_data['open'][$key] = [$value,(isset($op1[$key])? $op1[$key]:'未开奖')];
        }
      }


      // 开奖样式变化
      // $item['open_type'] = $this->openType($data['where']['type']);
      // if($item['open_type'] == 3){
      //   foreach ($item['content'] as $key => &$value) {
      //     $bit = lotteryL::codeType($value,date("Y"));
      //     $value = [$bit['code'],$bit['wave'][1],$bit['zodiac'][0]];
      //   }
      // }
    }

    return $return_data;

  }
  //保存用户个人账户设置
  public function setting(){
    $data = input('post.');
    $get = Db::table('user_config')->where('user_id','=',$this->user['id'])->find();
    if( (new userConfig)->save(['user_id'=>$this->user['id'],'backstage'=>json_encode($data['backstage']),'reception'=>json_encode($data['reception'])],($get ? true : false)) ){
      return ['code' => 1,'msg' => '保存成功'];
    }else{
      return ['code' => -1,'msg' => '数据未变动'];
    }
  }
  //读取
  public function userset(){
    //$data = input('post.');


    $get = Db::table('user_config')->where('user_id','=',$this->user['id'])->find();
    if(!empty($get)){
      $get['backstage'] = json_decode($get['backstage'],true);
      $get['reception'] = json_decode($get['reception'],true);
      $return_data = [
        'bet_sel' => [
          'config' => [
            'notice' =>$get['backstage']['notice'],      //中奖通知(未)
            'win_radio'=>$get['backstage']['win_radio'],   //中奖广播(未)
            'bet_money'=>$get['backstage']['bet_money'],   //显示下注筹码(普通投注)
            'bet_play'=>$get['backstage']['bet_play'],    //下注玩法(未)
            'all_music'=>$get['reception']['all_music'],   //开启全局音乐
            'bgm'=>$get['reception']['bgm'],         //开启背景音乐(未)
          ],
          'chip' => isset($get['reception']['chip']) ? $get['reception']['chip']:[]
        ],
        'chess' => isset($get['reception']['chess']) ? $get['reception']['chess'] : [10,100,500,1000,10000] ,
        'color' => isset($get['reception']['color']) ? $get['reception']['color'] : ['r'=>0,'g'=>0,'b'=>0,'op'=>'OFF'],
      ];
      $return_data['system_color'] = Db::table('system_config')->field('value')->where('name','=','color_setting')->find()['value'];
    }else{
      $return_data = '';
    }
    return $return_data;
  }
  
  /**
   * 签到方法  
   */
  public function sign(){
    $return_data['code'] = 1;
    $return_data['msg'] = '签到成功！';
    $user = $this->user;
    if($user['type'] == 1){
      $return_data['code'] = -1;
      $return_data['msg'] = '请先注册为正式会员';
    }else if($user['status']){
      $return_data['code'] = -4;
      $return_data['msg'] = '帐号已被冻结';
    }else{
      // 巴登分组取消福利
      if($user['group'] == 1){
        $return_data['code'] = -3;
        $return_data['msg'] = '您的签到积分福利已被取消,如有问题请联系客服';
      } else if (Db::table('capital_detail')->where('create_time','between',[strtotime(date('Y-m-d 00:00:00')),time()])->where(['type'=>9,'user_id'=>$user['id']])->find()){
        $return_data['code'] = -2;
        $return_data['msg'] = '今天已经签到过了';
      } else {
        $num = Db::table('system_config')->field('value')->where('id','25')->find()['value'];
        $add = [
          'uid' => $user['id'],
          'money' => $num,
          'type' => 9,
          'explain' => '签到',
        ];
        if(moneyAction($add)['code']){
          $return_data['code'] = 1;
          $return_data['msg'] = '签到成功';
        }else{
          $return_data['code'] = -1;
          $return_data['msg'] = '签到失败';
        }
      }
    }
    return $return_data;
  }
  public function sendChat(){
    $return_data = [
      'code' => 0,
      'msg' => '发送失败'
    ];
    $content = input('post.content');

    //print_r($content);die();
    //$rs = preg_replace('/<img.+>$|.{1}$/','',$content);
    // print_r($content);die();

    $chart_config = SystemConfig::get(32);
    $chart_config->value = json_decode($chart_config->value,true);
    if($chart_config->value['is_open'] == 0){
      if(!isset($chart_config->value['say_id'])){
        return $return_data;
      }elseif( in_array($this->user['id'],$chart_config->value['say_id']) ){
        if((new ChatRoom)->save([ 'content'=>$content,'user_id'=>$this->user['id'],'type'=>1 ])){
          $return_data['code'] = 1;
          $return_data['msg'] = '发送成功';
        }
        return $return_data;
      }else{
        $rs = preg_replace('/<img.+>$|.{1}$/','',$content);
        if($rs != ''){
          return $return_data;
        }
      }
    }

    if(empty($content)){
      $return_data['msg'] = '请输入您要发送的信息';
      return $return_data;
    }else{
      $um = Db::table('accumulation')->where([['user_id','=',$this->user['id'],['in_money|online_money','>',0]] ])->find();
      if($this->user['money'] == 0 || empty($um)){
        $return_data['code'] = -1;
        $return_data['msg'] = '只有充值的会员才能发送信息,请先充值';
      }else if((new ChatRoom)->save([ 'content'=>$content,'user_id'=>$this->user['id'],'type'=>1 ])){
        $return_data['code'] = 1;
        $return_data['msg'] = '发送成功';
      }
    }
    return $return_data;
  }

  //计算流水和盈亏 会员中心
  public function getBasic(){

    $return_data = [
      //累积提款
      'out_money' => 0,
      //今日赢亏
      'win'   => 0,
      //今日流水
      'water' => 0,
      //红包
      'hongbao' => 0,
      //是否有消息
      'msg'   => 0,
    ];

    //当前累加表
    $out_money = Db::table('accumulation')->field('out_money,winning,use_money,create_time')->where([ 'user_id'=>$this->user['id'] ])->order('create_time DESC')->find();
    if(!empty($out_money)){
      $return_data['out_money'] = $out_money['out_money'];
      if( $out_money['create_time'] > strtotime(date('Y-m-d 00:00:00')) ){
        //今天的以前最新
        $now_data = Db::table('accumulation')->where('user_id','=',$this->user['id'])->where('create_time','<',strtotime(date('Y-m-d 00:00:00')) )->order('create_time DESC')->find();
        if(!empty($now_data)){
          $return_data['win'] = round(($out_money['winning'] - $now_data['winning']) - ($out_money['use_money'] - $now_data['use_money']),2);
          $return_data['water'] = round(($out_money['use_money'] - $now_data['use_money']),2);
        }else{
          $return_data['win'] = round(($out_money['winning'] - $out_money['use_money']),2);
          $return_data['water'] = $out_money['use_money'];
        }
      }
    }

    // 查询一下是否有消息
    $um = Db::table('user_message')->where('user_id',$this->user['id'])->where('state','=',0)->find();
    if(!empty($um)){
      $return_data['msg'] = 1;
    }

    //查询一下app跳转地址
    $jump = Db::table('system_config')->field('value')->where('name','=','login_jump')->find()['value'];
    $return_data['jump'] = json_decode($jump,true);

    // 个人红包
    if($this->checkUserHongbao()['code']){
      $return_data['hongbao'] = 1;
    }
    
    return $return_data;
  }

  public function checkUserHongbao(){
    $return_data = [
      'code' => 0,
      'msg' => '没有可以领取的红包'
    ];
    $user_hongbao_config = Db::table('system_config')->field('value')->where('name','user_hongbao')->find()['value'];
    $user_hongbao_config = json_decode($user_hongbao_config,true);
    $user_hongbao_config_set = [
      'day' => [
        'hongbao_type' => 1,
        'begin_time' => strtotime(date('Y-m-d 00:00:00'))
      ],
      'month' => [
        'hongbao_type' => 2,
        'begin_time' => time() - (24 * 60 * 60) * 30
      ],
      'quarter' => [
        'hongbao_type' => 3,
        'begin_time' => time() - (24 * 60 * 60) * 30 * 3
      ],
      'year' => [
        'hongbao_type' => 4,
        'begin_time' => time() - (24 * 60 * 60) * 30 * 12
      ]
    ];

    //取消判断分组福利  
    if($user_hongbao_config['switch']['value'] && $this->user['group'] == 0){
      foreach ($user_hongbao_config as $key => $value) {
        if($key != 'switch' && $value['status']){
          // 这里是如果会员领取了红包，则计算充值开始时间为他上次领取红包的时间
          $hongbao_log = Db::table('hongbao_log')->field('create_time')->where([ 'user_id'=>$this->user['id'],'expect'=>$user_hongbao_config_set[$key]['hongbao_type'] ])->order('create_time','DESC')->find();
          // print_r(Db::getLastSql(). '---' . date('Y-m-d H:i:s',$hongbao_log['create_time']) . '*********');
          if(!empty($hongbao_log) && $hongbao_log['create_time'] > $user_hongbao_config_set[$key]['begin_time']){
            continue;
          }else{
            $begin_time = $user_hongbao_config_set[$key]['begin_time'];
          }
          $history_money1 = Db::table('accumulation')->field('in_money,online_money')->where('user_id',$this->user['id'])->where('create_time','<',$begin_time)->order('create_time','DESC')->find();
          $history_money1 = empty($history_money1) ? 0 : $history_money1['in_money'] + $history_money1['online_money'];
          $history_money2 = Db::table('accumulation')->field('in_money,online_money')->where('user_id',$this->user['id'])->where('create_time','<',time())->order('create_time','DESC')->find();
          $history_money2 = empty($history_money2) ? 0 : $history_money2['in_money'] + $history_money2['online_money'];
          $history_money1 = $history_money2 - $history_money1;
          if($history_money1 >= $user_hongbao_config[$key]['where_money']){
            $return_data['code'] = 1;
            $return_data['data'] = [
              'status' => 1,
              'type' => $user_hongbao_config_set[$key]['hongbao_type'],
              'money' => $user_hongbao_config[$key]['money'],
              'msg' => $user_hongbao_config[$key]['name']
            ];
            break;
          }
        }
      }
    }
    return $return_data;
  }

  public function getUserHongbao(){
    $return_data = [
      'code' => 0,
      'msg' => '没有可以领取的红包'
    ];
    $data = $this->checkUserHongbao();
    if($data['code']){
      try{
        Db::startTrans();
        if(Db::table('hongbao_log')->insert([
          'user_id' => $this->user['id'],
          'expect' => $data['data']['type'],
          'number' => $data['data']['money'],
          'create_time' => time()
        ]) && moneyAction([
          'uid'=> $this->user['id'],
          'money' => $data['data']['money'],
          'type' => 17,
          'explain' => '个人充值红包奖励'
        ])['code']){
          Db::commit();
          $return_data['code'] = 1;
          $return_data['msg'] = "恭喜您获得{$data["data"]["msg"]},奖励红包{$data['data']['money']},已返至您的账户";
          $return_data['data'] = $this->checkUserHongbao()['code'];
        }else{
          Db::rollback();
          $return_data['msg'] = '获得个人红包出错';
        }
      }catch (\Exception $e) {
        $return_data['msg'] = $e->getMessage();
        Db::rollback();
      }
    }
    return $return_data;
  }

  /**
   * 修改用户头像
   * @param number
   * @return array
   */
  public function userPhoto($type)
  {
    $return_data = [
      'code' => -1,
      'msg' => '更新失败',
    ];
    if(is_numeric($type)){
      $user = User::get($this->user['id']);
      $user->photo = $type;
      if($user->save()){
        $return_data['code'] = 1;
        $return_data['msg'] = '更新成功';
      }
    }else{
      $return_data['msg'] = '数据错误';
    }
    return $return_data;
  }

  // /**
  //  * 判断用户是否享受福利
  //  */
  // public function judgeWelfare(){
  //   print_r(123);
  // }

    //前台撤单方法
    public function cancelTheOrder()
    {
        if(Request::isPost())
        {
            $post = Request::param();
            $id = $post['id'];
            $user_id = $post['user_id'];
            if($user_id != $this->user['id'])
            {
                return '数据异常，不能进行撤单操作！';
            }
            $be = Db::table('betting')->where('id',$id)->find();
            if(!$be)
            {
                return '数据异常，不能进行撤单操作！';
            }
            else
            {
                if($be['state'] > 0 || $be['money'] <= 0)
                {
                    return '该订单状态不允许撤单！';
                }
                if($be['user_id'] != $user_id)
                {
                    return '数据异常，不能进行撤单操作！';
                }
                $cd =cheDan($id);
                if ($cd['code']){
                    return 1;
                }else{
                    return $cd['msg'];
                }
            }
        }
        else
        {
            return '系统繁忙，请稍后重试！';
        }
    }


    //获取用户收藏
    public function getCollection()
    {
        $user = $this->user;
        $collection = Db::table('user_collection')
            ->alias('uc')
            ->join('lottery_config lc','lc.type=uc.type')
            ->where('uc.user_id',$user['id'])
            ->field('lc.name,uc.type')
            ->order('uc.create_time desc')
            ->select();
        return $collection;
    }

    //添加收藏
    public function addCollection()
    {
        if(Request::isPost())
        {
            $type = Request::post('type');
            $caipiao = Db::table('lottery_config')->where('type',$type)->find();
            if(!$caipiao)
            {
                return '抱歉，彩票不存在';
            }
            $user = $this->user;
            $collection = Db::table('user_collection')->where('type',$type)->where('user_id',$user['id'])->find();
            if($collection)
            {
                return '已经收藏这个彩种了';
            }
            $insert = [
                'type'=>$type,
                'user_id'=>$user['id'],
                'create_time'=>time()
            ];
            $add = Db::table('user_collection')->insert($insert);
            if($add)
            {
                return 1;
            }
            else
            {
                return '系统繁忙，请稍后重试';
            }
        }
        else
        {
            return '参数错误';
        }
    }
    //删除收藏
    public function delCollection()
    {
        if(Request::isPost())
        {
            $type = Request::post('type');
            $user = $this->user;
            $collection = Db::table('user_collection')->where('type',$type)->where('user_id',$user['id'])->find();
            if(!$collection)
            {
                return '数据不存在';
            }
            $del = Db::table('user_collection')->where('type',$type)->where('user_id',$user['id'])->delete();
            if($del)
            {
                return 1;
            }
            else
            {
                return '系统繁忙，请稍后重试';
            }
        }
        else
        {
            return '参数错误';
        }
    }
}
