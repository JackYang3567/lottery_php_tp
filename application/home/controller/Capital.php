<?php
namespace app\home\controller;
use app\home\model\CapitalAudit;
use think\Db;

class Capital extends Common
{
  public $user;

  public function _initialize(){
    $data = $this->checkLogin();
    if($data['code']){
      $this->user = $data['data'];
      if($data['data']['type'] == 1){
        $this->error('请您注册为有效正式会员');
      }
    }else{
       $this->error('您还没有登陆');
    }
  }

  public function getBankList(){
    $return_data = [
      'code' => 0,
      'msg' => '这个充值通道没有开启',
      'data' => []
    ];
    $get = input('post.type');
    $field = 'id,name,slogan';
    if($get == 1){
      $field = 'id,name,slogan,explain,min,max';
    }
    $data = Db::table('bank_pay')->field($field)->where(['switch'=>1,'type'=>$get])->order('sort ASC')->select();
    if($data){
      foreach ($data as &$value) {
        $value['bank_name'] = bankTool($value['name']);
      }
      $return_data['code'] = 1;
      $return_data['msg'] = 'ok';
      $return_data['data'] = $data;
    }
    return $return_data;
  }

  public function orderDetails(){
	  /*
      $return_data = [
        'code' => 0,
        'msg' => '错误',
        'data' => []
      ];
      $id = input('post.type');
      if(!empty($id) && is_numeric($id)){
        $data = Db::table('bank_pay')->field('name,user_name,number,qr_code,min,max,explain,slogan')->where(['id'=>$id,'switch'=>1])->find();
        if($data){
          $data['bank_name'] = bankTool($data['name']);
          $return_data['code'] = 1;
          $return_data['msg'] = '获得数据';
          $return_data['data'] = $data;
        }
      }
      return $return_data;
	  */
      $return_data = [
        'code' => 0,
        'msg' => '错误',
        'data' => []
      ];
     
      $id = input('post.type');
      if(!empty($id) && is_numeric($id)){
        $data = Db::table('bank_pay')->field('name,user_name,number,qr_code,min,max,explain,slogan')->where(['id'=>$id,'switch'=>1])->find();
        if(empty($data)){return $return_data;}
        try{
          $chg = [
            'name' => '',
            'user_name' => '',
            'number' => '',
            'qr_code' => $data['qr_code'],
            'min' => $data['min'],
            'max' => $data['max'],
            'explain' => $data['explain'],
            'slogan' => $data['slogan']
          ];
          //添加等级显示判断
          $level = ($this->user['level'] - 1) < 0 ? 0:($this->user['level'] - 1 );
          if($data['name'] == 'payweixin' || $data['name'] == 'payalipay'){
            $chg['user_name'] = $data['user_name'];
            $chg['number'] = $data['number'];
            $data['qr_code'] = json_decode($data['qr_code'],true);
            for($i = $level;$i>=0;$i--){
              if($data['qr_code'][$level]){
                $chg['qr_code'] = $data['qr_code'][$level];
              }
              if($chg['qr_code'] != ''){
                break;
              }
            }
          }else{
            $data['user_name'] = json_decode($data['user_name'],true);
            
            $data['number'] = json_decode($data['number'],true);
            for($i = $level;$i>=0;$i--){
              if($data['user_name'][$level]){
                $chg['user_name'] = $data['user_name'][$level];
              }
              if($data['number'][$level]){
                $chg['number'] = $data['number'][$level];
              }
              if($chg['user_name'] != '' || $chg['number'] != ''){
                break;
              }
            }
          }
        
        //添加等级显示判断
        }catch (\Exception $e){
          $return_data['msg'] = '分层错误';
          return $return_data;
        }
   
        if($data){
          $chg['bank_name'] = bankTool($data['name']);
          $return_data['code'] = 1;
          $return_data['msg'] = '获得数据';
          $return_data['data'] = $chg;
        }
      }
      return $return_data;
  }

  public function bankSubmit(){
    $return_data = [
      'code' => 0,
      'msg' => '提交失败'
    ];
    $data = input('post.');
    if(isset($data['money']) && is_numeric($data['money']) && $data['money'] > 0 && isset($data['name'])){
      $map = [
        'user_id' => $this->user['id'],
        'bank' => $data['bank'],
        'money' => $data['money'],
        'pay_account' => $data['name'],
        // 'user_notes' => $data['msg'],
        'type' => 0,
        'state' => 0
      ];
      if(isset($data['msg']) && !empty($data['msg'])){
        $map['remarks'] = $data['msg'];
      }
      if((new CapitalAudit)->save($map)){
        $return_data['code'] = 1;
        $return_data['msg'] = '提交成功,请耐心等待审核';
      }
    }
    return $return_data;
  }

  public function verification(){
    $return_data = [
      'code' => -1,
      'msg' => ''
    ];
    //$lottery_list =
    $data_chat =  Db::table('system_config')->field('value,name')->where(['name'=> ['name','in',['cash_explain','cash_time','cash_switch','cash_time_day','min_cash_day']]])->column('name,value');

    $return_data['min'] = $data_chat['min_cash_day'];
    if($data_chat['cash_switch'] != 1){
      $return_data['msg'] = '提现系统维护中...';
      return $return_data;
    }

    $cash_time = json_decode($data_chat['cash_time'],true);
    $now_time = strtotime(date('His'));
    if($now_time < strtotime($cash_time['start_time']) || $now_time > strtotime($cash_time['end_time'])){
      $return_data['msg'] = '未到提款时间,提款时间为：' . $cash_time['start_time'] . '~' . $cash_time['end_time'];
      return $return_data;
    }

    $draw = Db::table('user_info')->field('draw_password')->where(['user_id'=>$this->user['id']])->find();
    if(empty($draw) || empty($draw['draw_password'])){
      $return_data['code'] = -2;
      $return_data['msg'] = '您还没有设置提现密码';
      return $return_data;
    }

    $bank = Db::table('user_bank')->field('username,name,number')->where(['user_id'=>$this->user['id']])->find();
    if(empty($bank) || empty($bank['username']) || empty($bank['name']) || empty($bank['number'])){
      $return_data['code'] = -3;
      $return_data['msg'] = '请您完善您的银行卡资料';
      return $return_data;
    }

    $num = CapitalAudit::where([ 'create_time'=>['create_time','egt',strtotime(date('Y-m-d 00:00:00'))],'user_id'=>$this->user['id'],'type'=>1 ])->count();
    if($data_chat['cash_time_day'] > 0 && $num >= $data_chat['cash_time_day']){
      $return_data['msg'] = '今天取款次数已经用完了';
      return $return_data;
    }

    $return_data['code'] = 1;
    $return_data['data'] = [
      'cash_explain' => $data_chat['cash_explain'],
      'cash_time' => '提款时间为：' . $cash_time['start_time'] . '~' . $cash_time['end_time'],
      'num' => [ $data_chat['cash_time_day'],($data_chat['cash_time_day'] > 0 ? $data_chat['cash_time_day'] - $num : 0) ]
    ];

    $return_data['frozen'] = Db::table('user')->where('id','=',$this->user['id'])->field('off_money')->find()['off_money'];

    // print_r($this->user);
    return $return_data;
  }


  public function moneyOut(){
    $return_data = [
      'code' => 0,
      'msg' => '发起提现失败'
    ];
    $data = $data = input('post.');

    if(!empty($data) && isset($data['money']) && is_numeric($data['money']) && isset($data['password']) && !empty($data['password'])){
      if(Db::table('user_info')->field('draw_password')->where(['user_id'=>$this->user['id']])->find()['draw_password'] != md5($data['password'])){
        $return_data['msg'] = '您的提款密码不对';
        return $return_data;
      }
      if($this->user['money'] < $data['money']){
        $return_data['msg'] = '余额不足';
        return $return_data;
      }
      if($this->user['status'] == 1){
        $return_data['msg'] = '帐号已被冻结,无法提款';
        return $return_data;
      }
      $min = Db::table('system_config')->field('value')->where('id','=',8)->find()['value'];
      if($data['money'] < $min){
        $return_data['msg'] = '最低提现'.$min;
        return $return_data;
      }

      $liushui = Db::table('user')->where('id','=',$this->user['id'])->field('off_money')->find();
      if($liushui['off_money'] > 0 ){
        $return_data['msg'] = '还需要完成￥'.$liushui['off_money'].'的流水才能提现,如有疑问,请联系客服!';
        return $return_data;
      }
      $map = [
        'user_id' => $this->user['id'],
        'money' => $data['money'],
        'type' => 1,
        'state' => 0
      ];
      // if(isset($data['remarks']) && !empty($data['remarks'])){
      //   $map['remarks'] = $data['remarks'];
      // }
      // $liushui = Db::table('user')->where('id','=',$this->user['id'])->field('off_money')->find();
      // if($liushui['off_money'] > 0){
      //     $map['remarks'] = '未达到流水,强行提款';
      // }
      Db::commit();
      $rs = moneyAction(['uid'=>$this->user['id'],'money'=>$data['money'],'type'=>12,'explain'=>'提现冻结']);
      // print_r($rs);die;
      if($rs['code'] && (new CapitalAudit)->save($map)){
        $return_data['code'] = 1;
        $return_data['msg'] = '发起提现成功,请耐心等待出款。';
        // if($liushui['off_money'] > 0){
        //   $return_data['msg'] = '发起强行提现成功,将会扣取一定手续费。';
        // }else{
        //   $return_data['msg'] = '发起提现成功,请耐心等待出款。';
        // }
      }else{
        Db::rollback();
      }
    }
    return $return_data;
  }

}
