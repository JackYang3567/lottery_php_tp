<?php
namespace app\home\controller;

use think\Controller;
use think\Db;
use think\facade\Request;
use app\home\model\ApiConfig;
use app\home\model\ApiBetting;
use app\home\model\ApiGame;
use app\home\model\SystemConfig;
use app\home\model\User;
use app\home\model\CapitalAudit;
use think\Exception;

class ApiFish extends Common 
{
  protected $mtoken = '1234567';
  public function in(){
    
  }
  
  /**
   * 钱包接口
   */
  public function wallet(){
    // {
    //   "_id": "59672a547aa48000019260cf",
    //   "action": "bet",
    //   "target": {
    //     "account": "fifi"
    //   },
    //   "status": {
    //     "createtime": "2017-07-13T04:07:48.644-04:00",
    //     "endtime": "2017-07-13T04:07:48.673-04:00",
    //     "status": "success",  //失败时 failed
    //     "message": "success"  //失败时 failed
    //   },
    //   "before": 8164082.95,   //失败时 0
    //   "balance": 8164072.95,  //失败时 0
    //   "currency": "CNY",
    //   "event": [
    //     {
    //       "mtcode": "testbet1123456:GC",
    //       "amount": 10,
    //       "eventtime": "2017-07-05T05:08:41-04:00"
    //     }
    //   ]
    // }
    
    

    //获取token
    $token = header('wtoken');
    //RFC3339时间格式
    // $RFCT = "Y-m-d\TH:i:sP" ;
    // $a = [
    //   'list'=>[
    //     'code'=>5
    //   ]
    // ];
    // print_r($a['list']);
  }

  /**
   * 取得游戏连接
   */
  public function player(){

  }

  /**
   * 模拟post进行url请求
   * @param string $url
   * @param string $param
   * @param string $token  定义请求$token
   */
  private function request_post($url = '', $param = '', $token='') {
    if (empty($url) || empty($param) || empty($token)) {
        return false;
    }
    $postUrl = $url;
    $curlPost = $param;
    $ch = curl_init();//初始化curl
    //当需要通过curl_getinfo来获取发出请求的header信息时，该选项需要设置为true
    // curl_setopt($ch, CURLINFO_HEADER_OUT, true);

    curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
      // curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HEADER, $header);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    return $data;
  }
}