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

class ApiGameConfig extends Common {
  public function test(){
    $rs = $this->in(6,195,'','',1545898293,1545901893);
  }
  /*--------------------------------------------------后台接口调用-----------------------------------*/
  /**
   * 后台调用接口
   * @param int $code 2
   * @param int $list 
   * @param int $uid
   * @return array 查询结果
   */
  public function bga($code = '',$list = '',$uid = ''){
    $return_data = [
      'code' => -1,
      'msg' => '错误'
    ];
    if($code == '' || $list == '' || $uid == ''){return $return_data;}
    $arr = [1,2,3];
    if(!in_array($code,$arr)){
      $return_data['msg'] = '无操作类型';
      return $return_data;
    }
    $rs = $this->in($code,$list,'',$uid);
    if($rs['code'] > 0){
      $return_data['code'] = 1;
      $return_data['msg'] = 'ok';
      $return_data['data'] = $rs['data'];
    }else{
      $return_data['msg'] = $rs['msg'];
    }
    return $return_data;
  }

  /*---------------------------------------------------离线回调接口-----------------------------------*/
  /**
   * 离线回调接口
   * @param int agent 代理通道id
   * @param int timestamp unix的时间戳
   * @param string param 加密的用户信息串码
   * @param string key 需要验证的加密串码
   */
  public function offLine(){
    $input = input('param.');
    //获取代理id
      //查询代理
      $ag = Db::table('api_config')->field('id,content')->where('agent','=',$input['agent'])->find();
      if(empty($ag)){
        return;
        //throw new Exception('error');
      }
      $ag['content'] = json_decode($ag['content'],true);

      //key验证
      if( $input['key'] != md5($input['agent'].$input['timestamp'].$ag['content']['data']['md5key']) ){
        return;
        //throw new Exception('error');
      }

      //解码
      //对密钥进行解码
      $lit = $this->desDecode($ag['content']['data']['deskey'],$input['param']);
      parse_str($lit,$b);
      //获取用户名
      $username = explode('_',$b['account'])[1];
      //获取用户id
      $money = 0;
      $bit = Db::table('user')->field('id')->where('username','=',$username)->find();
      if(!empty($bit)){
        //查询可下分数量
        $rs = $this->in(1,$ag['id'],'',$bit['id']);
        if($rs['code'] > 0){
          $money = round($rs['data']['money'],2);
        }else{
          return;
          //throw new Exception('error');
        }
      }else{
        return;
        //throw new Exception('error');
      }
      if($money <= 0){return;}
      //进行下分操作
      $li = $this->in(3,$ag['id'],'',$bit['id'],'','',$money);
      //如果下分成功 则进行资金操作
      if($li['code'] > 0){
        $game = ApiConfig::get($ag['id']);
        $capital = new CapitalAudit;
        $capital->pay_account = $li['orderid'];
        $capital->bank = 'chess';
        $capital->user_id = $bit['id'];
        $capital->remarks = $game->name.'(退出登录,自动下分)';
        $capital->money = $money;
        $capital->type = 4;
        $capital->create_time = time();
        //如果成功登录 需要  进行资金操作
        $money_config = [
          'uid' => $bit['id'],
          'money' => $money,
          'type' => 20,
          'explain' => '退出'.$game->name.'并下分'
        ];
        try{
          if(moneyAction($money_config) <= 0){
            //上一层保险 如果成功登录但是资金操作没有成功
            $capital->remarks .= ',但用户金额未添加成功';
            //$txt = '用户id为('.$bit['id'].')的用户在下分(增加)('.$game->name.')金额('.$money.')时错误;';
            //$this->fileInput($txt);
          }else{
            $capital->state = 1;
          }
        } catch (\Exception $e) {
      
        }
        $capital->save();
      }
  }

  
  /*---------------------------------------------这一部分为前端可使用接口------------------------------------------------------*/
  /**
   * 查询用户在某厂商的可下分情况
   * @param int list 游戏厂商
   */
  public function queryDownMoney( $list ){
    $return_data = [
      'code' => -1,
      'msg' => '错误'
    ];
    $user = $this->checkLogin();
    if($user['code'] <= 0){
      $return_data['msg'] = '未登录';
      return $return_data;
    } elseif($user['data']['type'] != 0){
      $return_data['msg'] = '必须为正式用户';
      return $return_data;
    }else{
      $uid = $user['data']['id'];
    }
    //获取游戏厂商名称
    $game = ApiConfig::get($list);//(new ApiConfig)->find($list);
    if(empty($game)){
      $return_data['msg'] = '未知错误';
      return $return_data;
    }else if($game->switch == 0){
      $return_data['msg'] = '该通道已关闭,如有疑问请联系客服';
      return $return_data;
    }
    $rs = $this->in(1,$list,'',$user['data']['id']);
    if($rs['code'] > 0){
      $return_data['code'] = 1;
      $return_data['msg'] = 'ok';
      $return_data['data'] = $rs['data']['money'];
    } else {
      $return_data['data'] = 0;
    }
    return $return_data;
  }

  /**
   * 用户游戏上下分内容
   * @param int s  上分还是下分
   * @param float money  金额
   * @param int list 游戏厂商
   */
  public function gameInOut(){
    $post_data = input('param.');
    $user = $this->checkLogin();
    $return_data = [
      'code' => -1,
      'msg' => '错误',
    ];
    if($user['code'] <= 0){
      $return_data['msg'] = '请先登录';
      return $return_data;
    } elseif( $user['data']['type'] != 0 ){
      $return_data['msg'] = '请先注册为正式用户';
      return $return_data;
    } elseif($post_data['money'] <= 0){
      $return_data['msg'] = '金额错误';
      return $return_data;
    }
    //获取游戏厂商名称
    $game = ApiConfig::get($post_data['list']);//(new ApiConfig)->find($post_data['list']);
    if(empty($game)){
      $return_data['msg'] = '未知错误';
      return $return_data;
    }else if($game->switch == 0){
      $return_data['msg'] = '该通道已关闭,如有疑问请联系客服';
      return $return_data;
    }
    if($post_data['s'] == 2){
      //为棋牌游戏上分
      if((int)$user['data']['money'] < (int)$post_data['money']){
        $return_data['msg'] = '金额不足';
        return $return_data;
      }
      //记录表
      $capital = new CapitalAudit;
      $capital->bank = 'chess';
      $capital->user_id = $user['data']['id'];
      $capital->remarks = $game->name.'(手动上分)';
      $capital->money = $post_data['money'];
      $capital->type = 3;
      $capital->create_time = time();

      if($post_data['money'] > 0){
        //资金操作
        $money_config = [
          'uid' => $user['data']['id'],
          'money' => $post_data['money'],
          'type' => 19,
          'explain' => $game->name.',上分'
        ];
        if(moneyAction($money_config)['code'] <= 0){
          $return_data['msg'] = '上分错误';
          return $return_data;
        }
      }

      //进行游戏上分
      $rs = $this->in(2,$post_data['list'],'',$user['data']['id'],'','',$post_data['money']);
      if($rs['code'] > 0){
        $capital->pay_account = $rs['orderid'];
        $return_data['code'] = 1;
        $return_data['msg'] = '上分成功';
        $capital->remarks = '上分成功';
        $capital->state = 1;
      }else{
        $capital->remarks = '金额已扣除,但未上分成功';
      }
      $capital->save();
    } elseif($post_data['s'] == 3){
    //为棋牌游戏下分

    //可下分查询
    $is_money = $this->in(1,$post_data['list'],'',$user['data']['id']);
    if($is_money['code'] <= 0 || $post_data['money'] == 0 ){
      $return_data['msg'] = '无法下分';
      return $return_data;
    } elseif($is_money['data']['money'] < $post_data['money']) {
      $return_data['msg'] = '下分金额不足';
      return $return_data;
    }

    //记录表
    $capital = new CapitalAudit;
    $capital->bank = 'chess';
    $capital->user_id = $user['data']['id'];
    $capital->remarks = $game->name.'(手动下分)';
    $capital->money = $post_data['money'];
    $capital->type = 4;
    $capital->create_time = time();
    //获取内容
    $rs = $this->in(3,$post_data['list'],'',$user['data']['id'],'','',$post_data['money']);
    
      if($rs['code'] > 0){
        $capital->pay_account = $rs['orderid'];

        //资金操作
        $money_config = [
          'uid' => $user['data']['id'],
          'money' => $post_data['money'],
          'type' => 20,
          'explain' => $game->name.'(下分)'
        ];
        if(moneyAction($money_config)['code'] <= 0){
          $return_data['msg'] = '资金添加失败,请联系客户';
          $capital->remarks = '下分成功,但用户金额未添加成功!';
        }else{
          $capital->state = 1;
          $capital->remarks = '下分成功';
          $return_data['code'] = 1;
          $return_data['msg'] = '下分成功';
        }
        $capital->save();
      }else{
        $return_data['msg'] = '下分失败';
      }
    }
    return $return_data;
  }

  /**
   * 游戏登录接口
   * @param int $list 厂商接口
   */
  public function gameLogin($list,$code){
    $user = $this->checkLogin();
    $return_data = [
      'code' => -1,
      'msg' => '错误'
    ];
    if($user['code'] <= 0){
      $return_data['msg'] = '未登录';
      return $return_data;
    } elseif($user['data']['type'] != 0){
      $return_data['msg'] = '必须为正式用户';
      return $return_data;
    }else{
      $uid = $user['data']['id'];
    }
    //在登录前需要操作充值记录表添加数据记录

    $rs = $this->in(0,$list,$code,$uid);
    if($rs['code'] > 0){
      $return_data['code'] = 1;
      $return_data['msg'] = 'ok';
      $return_data['data'] = $rs['data']['url'];

      $game = ApiConfig::get($list);
      if($user['data']['money'] > 0){
        //如果成功登录 需要  进行资金操作

        //记录表
        $capital = new CapitalAudit;
        $capital->bank = 'chess';
        $capital->user_id = $user['data']['id'];
        $capital->remarks = $game->name.'(登录,自动上分)';
        $capital->money = $user['data']['money'];
        $capital->type = 3;
        $capital->create_time = time();

        $money_config = [
          'uid' => $user['data']['id'],
          'money' => $user['data']['money'],
          'type' => 19,
          'explain' => '登录'.$game->name.'并上分'
        ];
        if(moneyAction($money_config)['code'] <= 0){
          //上一层保险 如果成功登录但是资金操作没有成功
          $capital->remarks = '上分成功,资金明细未记录';
          $capital->remarks = 1;
          //$txt = '用户id为('.$user['data']['id'].')的用户在上分(扣除)('.$game->name.')金额('.$user['data']['money'].')时错误;';
          //$this->fileInput($txt);
          $be_user = User::get($user['data']['id']);
          $be_user->money = 0;
          $be_user->save();
          // $return_data['msg'] = '上分错误';
          return $return_data;
        }else{
          $capital->state = 1;
        }
        $capital->save();
      }
    }else{
      $return_data['msg'] = $rs['msg'];
    }
    return $return_data;
  }

  /**
   * 写错误日志
   * @param string $txt需要写入的错误日志
   */
  public function fileInput($txt){
    //打开日志文件
    $error_txt = fopen('gameLineLog.txt',"a+");
    //写入错误信息
    $txt = '('.date('Y-m-d H:i:s').'>>'.$txt.')';
    fwrite($error_txt, $txt);
    fclose($error_txt);
  }

  /*-----------------------------------------------------注单拉取接口---------------------------------------------------*/
  /**
   * 自动注单拉取接口
   */
  public function pullBet(){
    //查询投注单;
    // print_r(123);
    $return_data = [
      'code' => -1,
      'msg' => '错误'
    ];
    //查询上次统一拉取时间
    $scon = SystemConfig::get(54);
    if(empty($scon)){return;}
    if($scon->value == 0 || $scon->value == NULL || time() - (int)$scon->value >= 120){
      $scon->value = time();
      $scon->save();
      //查询时间可以拉取后 进行 全部统一拉取
      $data = ApiConfig::field('id,bet_time')->where('switch',1)->select();//(new ApiConfig)->field('id,bet_time')->where('switch',1)->select();
      //如果没有开启内容则退出
      if(empty($data)){
        $return_data['msg'] = '没有需要拉取的注单';
        return $return_data;
      }
      foreach($data as $vo){
        if($vo->bet_time == 0 || $vo->bet_time == ''){
          $endTime = time();
          $vo->bet_time = $endTime;
          $startTime = $endTime - 3600;
        }else{
          if((time() - $vo->bet_time) <= 60){
            continue;
          }elseif((time() - $vo->bet_time) > 3600){
            $startTime = $vo->bet_time;
            $vo->bet_time += 3600;
            $endTime = $vo->bet_time;
          }else{
            $startTime = $vo->bet_time;
            if(time() - $vo->bet_time < 3300){
            	$startTime -= 120;
            }
            $vo->bet_time = time();
            $endTime = $vo->bet_time;
          }
        }
        //获取注单
        $rs = $this->in(6,$vo->id,'','',$startTime,$endTime);
        if($rs['code'] > 0){
          $vo->save();
          if($rs['data']['code'] == 0){
            $this->betHandle($rs['data'],$vo->id);
          }
        }
      }
    }
    return;
  }

  /**
   * 拉取注单后的处理程序
   * @param array $arr 处理程序
   * @param int $ptid 平台id
   */
  private function betHandle($arr,$ptid){
    if(!$arr){return;}
    
    //存储使用
    $list = [];
    //临时变量使用
    $uarr = [];
    //流水记录
    $water = [];

    foreach( $arr['list']['GameID'] as $key => $item ){
      //如果游戏局号数据库已存在 则退出
      if( ApiBetting::where('game_id','=',$item)->find() ){
        return;
      }
      $username = explode('_',$arr['list']['Accounts'][$key])[1];
      if(in_array($username,$uarr)){
        $user_id = array_search($username,$uarr);
        $water[$user_id] += $arr['list']['CellScore'][$key];
      }else{
        $bit = User::where('username','=',$username)->find();
        $water[$bit->id] = $arr['list']['CellScore'][$key];
        if(!empty($bit)){
          $uarr[$bit->id] = $username;
          $user_id = $bit->id;
        }else{
          $user_id = 0;
        }
      }

      //存入数据
      $list[] = [
        'username' => $username,                     //用户名
        'user_id' => $user_id,                       //用户id
        'game_id' => $item,   //游戏局号
        'accounts' => $arr['list']['Accounts'][$key],//用户名+代理号
        'kind_id' =>  $arr['list']['KindID'][$key],
        'server_id' => $arr['list']['ServerID'][$key],
        'cell_score' => $arr['list']['CellScore'][$key],  //有效下注
        'card_value' => $arr['list']['CardValue'][$key],  //手牌公牌
        'all_bet' => $arr['list']['AllBet'][$key],
        'profit' => $arr['list']['Profit'][$key],        //盈利
        'revenue' => $arr['list']['Revenue'][$key],      //抽水
        'game_start_time' => $arr['list']['GameStartTime'][$key],
        'game_end_time' => $arr['list']['GameEndTime'][$key],
        'api_id' => $ptid
      ];
    }
    if(count($list)){
      (new ApiBetting)->saveAll($list);
      foreach($water as $k => $vo){
        $model = User::get($k);
        if(!empty($model)){
          if(($model->off_money - $vo) > 0){
            $model->off_money -= $vo;
          }else{
            $model->off_money = 0;
          }
          $model->save();
        }
      }
    }
    return;
  }
  /*------------------------------------------所有游戏入口-------------------------------------------------*/
  /**
   * 所有游戏接口
   * @param int $s 操作需要
   * @param int $list 厂商id
   * @param int $code 游戏id
   * @param int $user_id  用户id
   * @param int $startTime 区间开始时间
   * @param int $endTime 结束时间
   * @param float $money 上下分时候的金额
   */
  protected function in($s = 0,$list = '',$code = '',$user_id = '',$startTime = '',$endTime = '',$money = 0){
    /*
      $s 类型示例
      0 => 登录
      1 => 查询可下分
      2 => 上分
      3 => 下分
      4 => 查询上下分订单情况
      5 => 查询玩家在线状态
      6 => 查询注单
      7 => 查询游戏内总分、玩家可下分余额、玩家在线状态
    */
    $return_data = [
      'code' => -1,
      'msg' => '错误'
    ];

    if($s == 6){
      if($startTime == '' || $endTime == ''){
        $return_data['msg'] = '日期错误';
        return $return_data;
      }elseif ($startTime >= $endTime){
        $return_data['msg'] = '日期错误';
        return $return_data;
      }
      $startTime *= 1000;
      $endTime *= 1000;
    }else{
      if($user_id != ''){
        $user = User::get($user_id)->toArray();
        if(empty($user)){
          $return_data['msg'] = '未登录';
          return $return_data;
        }
      }else{
        $return_data['msg'] = '未登录';
        return $return_data;
      }
      //用户名
      $account = $user['username'];
    }

    //配置地址 和 基本信息
    try{
      $api = ApiConfig::get($list);//(new ApiConfig)->find($list);
      if(empty($api) || $api->content == ''){
        throw new Exception('游戏未配置,请联系客服');
      } elseif($api->switch == 0) {
        throw new Exception('游戏关闭');
      }else{
        //登录时获取接口基本信息
        $link_data = json_decode( $api->content,true );
        $agent = $api->agent;//$link_data['data']['agent'];    //代理编号
        $deskey = $link_data['data']['deskey'];
        $md5key = $link_data['data']['md5key'];
        if($s == 0){
          //游戏ID进入不同游戏的ID
          if($code == ''){throw new Exception('游戏关闭');}
          $game_api = ApiGame::get($code);
          if($game_api->switch == 0){
            throw new Exception('游戏已关闭');
          }else if($game_api->king_id == ''){
            throw new Exception('未配置的游戏');
          }
          $KindID = $game_api->king_id;
        }
        //拉单地址和 其他地址分开
        if($s == 6){
          $target = $link_data['s']['ladan'];
        }else{
          $target = $link_data['s']['default'];
        }
      }
    }catch(\Exception $e){
      $return_data['msg'] = $e->getMessage();
      return $return_data;
    }


    //带毫秒的当前时间戳
    $timestamp = $this->msectime();
    //lineCode 站点标识
    $lineCode = 'xingyun';
    //流水号
    if($s != 6){
      $orderId = $agent.(date('YmdHis').$this->msectime(1).'+0800').$account;
    }
    //玩家的ip
    $ip = request()->ip();
    //接口地址
    // $target = 'https://kyapi.ky206.com:189/channelHandle?';
    switch($subCmd = intval($s)) {
      case 0: // 登录
        $param = http_build_query(array(
          's' => $s,
          'account' => $account,
          'money' => $user['money'],            //此处是登录时上分内容默认传0
          'orderid' => $orderId,
          'ip' => $ip,
          'lineCode' => $lineCode,
          'KindID' => $KindID,
        ));
        break;
      case 1: // query the money of account
      case 5: // check if the account is online
      case 7: // query the game's total coin or money 
      case 8: // 强制下线
        $param = http_build_query(array(
          's' => $s,
          'account' => $account
        ));
        break;
      case 2: // charge the money of account
      case 3: // 下分和上分
        $param = http_build_query(array(
          's' => $s,
          'account' => $account,
          'orderid' => $orderId,
          'money' => $money,
          'ip' => $ip
        ));
        if($money <= 0){
          $return_data['msg'] = '上下分金额错误';
          return $return_data;
        }
        break;
      case 4: // 用id查询订单号
        $param = http_build_query(array(
          's' => $s,
          'orderid' => $orderId,
        ));
        break;
      case 6: // 查询订单
        $param = http_build_query(array(
          's' => $s,
          'startTime' => $startTime,
          'endTime' => $endTime
        ));
        break;
      default:
        return $return_data;
    }
    $url_data = [
      'agent' => $agent,
      'timestamp' => $timestamp,
      'param' => $this->desEncode($deskey, $param),
      'key' =>  md5($agent.$timestamp.$md5key)
    ];

    $href = $target.'?'.http_build_query($url_data);
    $pgtime = 30;
    if($s == 6){
      $pgtime = 2;
    }
    $rs = $this->request_get($href,$pgtime); //请求完成后的
    if(isset($orderId)){
      $return_data['orderid'] = $orderId;
    }
    $rs = json_decode($rs,true);
    if(isset($rs['d']) && $rs['d']['code'] == 0){
      $return_data['code'] = 1;
      $return_data['msg'] = 'ok';
      if($s == 0){
        //获取内容
        $back_url = SystemConfig::get(41)->value;
        if(!strpos($back_url,'http')){
          $back_url = 'http://'.$back_url;
        }
        //在登录的时候拼接返回地址
        $rs['d']['url'] .= '&backUrl='.$back_url.'&jumpType=3';
      }
      $return_data['data'] = $rs['d'];
    }elseif($s == 6 && $rs['d']['code'] == 16){
      $return_data['code'] = 1;
      $return_data['msg'] = 'ok';
      $return_data['data'] = $rs['d'];
    }else{
      $return_data['msg'] = '失败'.$rs['d']['code'];
    }
    return $return_data;
  }

  //-------------------------------------以下为各个加密和解密算法---------------------------------------------
  /**
   * 模拟post进行url请求
   * @param string $url
   * @param string $param
   */
  private function request_post($url = '', $param = '') {
    if (empty($url) || empty($param)) {                                                                 
        return false;
    }
    $postUrl = $url;
    $curlPost = $param;
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

	/**
	 * 传入json数据进行HTTP Get请求
	 * @param string $url $data_string
   * @param int $time 请求超时时间
	 * @return string
	 */
	private function request_get($url,$time=30)
	{
	  $curl = curl_init(); // 启动一个CURL会话
		curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT,$time);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
    $tmpInfo = curl_exec($curl);     //返回api的json对象
		//关闭URL请求
    curl_close($curl);
    
    if(!$tmpInfo){
      return json_encode([
        'd' => [ 'code' => -10000 ]
      ]);
    }

		return $tmpInfo;    //返回json对象
	}

  /**
   * 获取当前unix时间戳,多三位
   */
  private function msectime($type = 0) {
    list($msec, $sec) = explode(' ', microtime());
    if($type == 0){
      return (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    }else{
      return round($msec*1000);
    }
  }

  /**
   * 加密算法
   */
  private function desEncode($key, $str)
  {
    if (function_exists('mcrypt_encrypt')) {
      // dbglog('Using mcrypt encode module');
      return $this->mcrypt_desEncode($key, $str);
    }
  
    if (function_exists('openssl_encrypt')) {
      // dbglog('Using openssl encode module');
      return $this->openssl_desEncode($key, $str);
    }
    // dbglog('CAN NOT USE ENCRYPTION MODULE');
    return null;
  }
  
  //des解密
  private function desDecode($key, $str)
  {

    if (function_exists('openssl_encrypt')) {
      // dbglog('Using openssl decode module');
      return $this->openssl_desDecode($key, $str);
    }
    if (function_exists('mcrypt_encrypt')) {
      // dbglog('Using mcrypt decode module');
      return $this->mcrypt_desDecode($key, $str);
    }


    // dbglog('CAN NOT USE ENCRYPTION MODULE');
    return null;
  }
  private function openssl_desEncode($key, $str)
  {
    $str = $this->pkcs5_pad(trim($str), 16);
    $encrypt_str = openssl_encrypt($str, 'AES-128-ECB', $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);
    return base64_encode($encrypt_str);  
  }

  private function mcrypt_desDecode($encryptKey, $str)  
  {  
    $str = base64_decode($str);  
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);  
    $decrypt_str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $encryptKey, $str, MCRYPT_MODE_ECB, $iv);  
    
    return $this->pkcs5_unpad(trim($decrypt_str));  
    //return pkcs7_unpad(trim($decrypt_str));  
  }  
  private function openssl_desDecode($key, $str)
  {
    $str = base64_decode($str);  
    $decrypt_str = openssl_decrypt($str, 'AES-128-ECB', $key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING);
    return trim($this->pkcs5_unpad($decrypt_str));  
  }
  private function pkcs5_unpad($text) 
  { 

    $pad = ord($text{strlen($text)-1}); 
    if ($pad > strlen($text)) return false; 
    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false; 
    return substr($text, 0, -1 * $pad); 
  }
  
  /**
   * 加密算法
   */
  private function mcrypt_desEncode($encryptKey, $str)  
  {  
    $str = trim($str);  
    $blocksize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);  
    //$str = pkcs7_pad($str, $blocksize);  
    $str = $this->pkcs5_pad(trim($str), $blocksize);
    $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_RAND);  
    $encrypt_str = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $encryptKey, $str, MCRYPT_MODE_ECB, $iv);  
    return base64_encode($encrypt_str);  
  } 

  private function pkcs5_pad($text, $blocksize) 
  {
    $pad = $blocksize - (strlen($text) % $blocksize);
    return $text . str_repeat(chr($pad), $pad);
  }
}