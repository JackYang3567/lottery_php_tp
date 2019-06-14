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
use think\Cache;

class LinePay extends Common
{
  /**
   * 创建订单
   * @param number $money 用户金额
   * @param int $id 用户id
   */
  private function createOrder($money,$id){
    $out_trade_no = date("YmdHis") . mt_rand(10000,99999);
    if (!LinePayOrder::create([
      'money' => $money,
      'user_id' => $id,
      'order_id' => $out_trade_no
    ])) {
      return;
    }
    return $out_trade_no;
  }

  /**
   * 订单生成，成功跳转 失败返回
   * @param number $money 金额
   * @param string $host 前端地址
   * @param int $list 前端地址
   */  
  public function index($money='', $host='', $list=''){
    $return_data = [
      'code' => -1,
      'msg' => '错误',
    ];
    //验证请求--------
    $user = $this->checkLogin();
    if(!$user['code']){
      $return_data['msg'] = '请先登录';
      return $return_data;
    }

    $model = new LinePayOrder;
    //清除撤销订单
    $model->revoke();

    if($model->onNum() >= 5){
      $return_data['msg'] = '有过多的订单未处理';
      return $return_data;
    }

    $judge = $this->checkInput($money, $host, $list);
    if($judge['code'] <= 0){
      return $return_data;
    }
    //验证请求完------


    //S_KEY->商户KEY，到平台首页自行复制粘贴，该参数无需上传，用来做签名验证和回调验证，请勿泄露
    $s_key = $judge['key']; //'15acb0014';
    //商户ID 
    $account_id = $judge['id'];//'10154';
    //订单号码->这个是四方网站发起订单时带的订单信息，一般为用户名，交易号，等字段信息
    if(!$out_trade_no = $this->createOrder($money,$user['data']['id'])) {
      $return_data['msg'] = '订单提交失败,24小时候后自动撤销';
      return $return_data;
    }
    //支付金额
    $amount = floatval($money);
    //生成签名
    $sign = $this->sign($s_key, ['amount'=>$amount,'out_trade_no'=>$out_trade_no]);
    //查询后台地址
    $callback = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'];

    $post_data = [
      'account_id'    => $account_id,         //商户ID->到平台首页自行复制粘贴
      'content_type'  => 'json',          //请求获取的网页类型，json 返回json数据，text直接跳转html界面支付，如没有特殊需要，建议默认text即可
      'thoroughfare'  => 'service_auto',  //支付通道：支付宝（公开版）：alipay_auto、微信（公开版）：wechat_auto、服务版（免登陆/免APP）：service_auto
      'out_trade_no'  => $out_trade_no,   //订单号码->这个是四方网站发起订单时带的订单信息，一般为用户名，交易号，等字段信息
      'sign'          => $sign,           //生成签名
      'robin'         => 2,               //轮训状态，是否开启轮训，状态 1 为关闭   2为开启
      'callback_url'  => $callback.'/home/line_pay/successPay',              //支付成功后回调函数 http://admin.wanda315.com/pay/callback_demo.php
      'success_url'   => $host.'/#/pay_return/1',           //支付成功后跳转地址 http://admin.wanda315.com/pay/ok
      'error_url'     => $host.'/#/pay_return/2',           //支付失败回调 http://admin.wanda315.com/pay/sb
      'amount'        => $amount,         //支付金额
      'type'          => 2,               //支付类型->类型参数是服务版使用，公开版无需传参也可以
      'keyId'         => ''               //微信设备KEY，新增加一条支付通道，会自动生成一个device Key，可在平台的公开版下看见，如果为轮训状态无需附带此参数，如果$robin参数为1的话，就必须附带设备KEY，进行单通道支付
    ];
    //发送请求
    $res = $this->request_post('http://pay.wanda315.com/gateway/index/checkpoint',$post_data);
    $return_data['code'] = 1;
    $return_data['msg'] = 'http://pay.wanda315.com/gateway/pay/service.do?id='.json_decode($res,true)['data']['order_id'];
    return $return_data;
  }

  /**
   * 输入验证方法
   */
  private function checkInput($money='', $host='', $list=''){
    $return_data = [
      'code' => -1,
      'id' => '',
      'key' => ''
    ];
    if($money == '' || $host == '' || $list == ''){
      return $return_data;
    }
    if(!is_numeric($moeny) || $moeny <= 0 ){
      return $return_data;
    }

    $rs = Db::name('bank_pay')->where('id','=',$list)->find();
    if(!empty($rs)){
      if(!empty($rs['number']) && !empty($rs['qr_code']) && $rs['switch'] == 1){
        $return_data['code'] = 1;
        $return_data['id'] = $rs['number'];
        $return_data['key'] = $rs['qr_code'];
      }
    }
    return $return_data;
  }

  /**
   * 成功后回调函数接口
   */
  public function successPay(){
    //商户名称
    //$account_name  = $_POST['account_name'];
    //支付时间戳
    //$pay_time  = $_POST['pay_time'];
    //支付状态
    //$status  = $_POST['status'];
    //支付金额
    $amount  = $_POST['amount'];
    //支付时提交的订单信息
    $order_id  = $_POST['order_id'];
    //平台订单交易流水号
    $trade_no  = $_POST['trade_no'];
    //该笔交易手续费用
    //$fees  = $_POST['fees'];
    //签名算法
    $sign  = $_POST['sign'];
    //回调时间戳
    //$callback_time  = $_POST['callback_time'];
    //支付类型
    //$type = $_POST['type'];
    //商户KEY（S_KEY）
    //$account_key = $_POST['account_key'];
    //第一步，检测商户KEY是否一致
	/*
    if ($account_key != 'cb8ssr5es85') exit('error:key');
    //第二步，验证签名是否一致
    if ($this->sign('cb8ssr5es85', ['amount'=>$amount,'out_trade_no'=>$out_trade_no]) != $sign) exit('error:sign');
*/	
	
	
    if( !$sign == $this->sign('db4a4c20559f882d168d8ef146625780',['amount'=> $amount ,'order_id'=>$order_id , 'trade_no' => $trade_no]) ){
      return 'off_sign';
    }
    if (!$order = LinePayOrder::get(['order_id' => $trade_no, 'status' => 0])) {
      return 'off';
    }
    $order->status = 1;
    $order->over_time = date('Y-m-d H:i:s');
    //充值赠送规则
    $money = 0;
    if(user::get($order->user_id)->group == 0){
      $money = $this->recharge($order->money);
      if($money > 0){
        $order->remarks = '充值成功,赠送彩金'.$money;
		moneyAction(['uid' => $order->user_id,'money' => $money, 'type' => 5, 'explain' => '充值赠送']);
      }
    }
    $order->save();
    moneyAction(['uid' => $order->user_id,'money' => $order->money, 'type' => 7, 'explain' => '在线充值']);
	  return 'ok';
  }

  /**
   * 充值成功时，赠送金额
   */
  public function recharge(){
    
  }

  /**
   * 签名算法
   * @param unknown $key_id S_KEY（商户KEY）
   * @param unknown $array 例子：$array = array('amount'=>'1.00','out_trade_no'=>'2018123645787452');
   * @return string
   */
  private function sign($key, $param)
  {
      ksort($param);
      $paramArr = [];
      foreach ($param as $k => $v) {
          $paramArr[] = $k . '=' . $v;
      }
      $sign_str = $key . "&" . implode('&', $paramArr);
      $sign = md5($sign_str);
      return $sign;
  }
  
    /**
     * 模拟post进行url请求
     * @param string $url
     * @param array $post_data
     */
    public function request_post($url = '', $post_data = array()) {
      if (empty($url) || empty($post_data)) {
          return false;
      }
      
      $o = "";
      foreach ( $post_data as $k => $v ) 
      { 
          $o.= "$k=" . urlencode( $v ). "&" ;
      }
      $post_data = substr($o,0,-1);

      $postUrl = $url;
      $curlPost = $post_data;
      $ch = curl_init();//初始化curl
      curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
      curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
      curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
      curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
      $data = curl_exec($ch);//运行curl
      curl_close($ch);
      
      return $data;
  }
}
