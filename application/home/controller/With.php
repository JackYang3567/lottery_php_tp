<?php
namespace app\home\controller;
use app\home\controller\Lottery;
use app\home\model\Betting;
use think\Db;

class With extends Common
{
  public $config_time = [];

  public function getInfo($_id = false){
    $post = input('post.');
    $is_where = [
      'a.money' => 0
    ];
    if(0 && isset($post['is_id'])){
      $is_where['a.id'] = [ 'a.id','>',$post['is_id'] ];
    }else if($_id){
      $is_where['a.id'] = $_id;
    }
    $model = Db::table('betting a')->field('a.id,a.state,a.win,a.type,a.expect,a.explain as details,b.all,b.bd,b.buy,b.explain as xy,c.username,d.name')->join([
      [ 'betting_he b','b.betting_id=a.id' ],
      [ 'user c','c.id=a.user_id' ],
      [ 'lottery_config d','d.type=a.type','left' ]
    ])->where($is_where)->order('a.id DESC');
    $data = $model->paginate(7,true)->toArray();
     // print_r($data);die;
    if(!empty($data['data'])){
      $array_types = [ '进行中','未中奖','已撤单','追号中' ];
      foreach ($data['data'] as $key => &$value) {
        if($value['type'] == 35){
          $value['name'] = '竞彩足球';
        }
        $value['username'] = mb_substr($value['username'],0,3).'***';
        $value['explain'] = $array_types[$value['state']];
        $value['all'] = round($value['all'],2);
        $value['bd'] = round($value['bd'],2);
        $value['jd'] = round($value['buy'] / ($value['all'] / 100),2);
        if($value['win'] > 0){
          $value['explain'] = '已中奖' . $value['win'];
          $value['state'] = 0;
        }else{
          if($value['state'] == 0 || $value['state'] == 3){
            $value['state'] = 0;
            if($this->chekState([ 'betting_id'=>$value['id'],'expect'=>$value['expect'],'type'=>$value['type'] ])){
              if($value['all'] <= $value['buy']){
                $value['explain'] = '已满员';
              }else{
                $value['explain'] = '合买招股中';
                $value['state'] = 1;
              }
            }
          }else{
            $value['state'] = 0;
          }
        }
      }
    }
    return $data;
  }

  public function buy(){
    $return_data = [
      'code' => 0,
      'msg' => '参与失败'
    ];
    $post_data = input('post.');
    if(isset($post_data['id'])){
      $return_data['data'] = ($post_data['temp'] == 0 ? $this->getInfo($post_data['id'])['data'][0] : $this->details()['data']);
    }
    $user = $this->checkLogin();
    if($user['code'] == 0){
      $return_data['msg'] = '您还没有登陆';
      return $return_data;
    }
    $user = $user['data'];
    if($user['type'] == 1){
      $return_data['msg'] = '请您注册为有效会员';
      return $return_data;
    }
    if(isset($post_data['id']) && isset($post_data['value'])){
      if($user['money'] < $post_data['value']){
        $return_data['msg'] = '您的余额不足';
        return $return_data;
      }
      $model = Betting::field('id,expect,type,state')->find($post_data['id']);
      $main = $model->toArray();
      if($main['state'] == 1){
        $return_data['msg'] = '该合买已经结束了';
        return $return_data;
      }
      if($main['state'] == 2){
        $return_data['msg'] = '该合买未达到进度,自动撤单了';
        return $return_data;
      }
      if(!$this->chekState([ 'betting_id'=>$post_data['id'],'expect'=>$main['expect'],'type'=>$main['type'] ])){
        $return_data['msg'] = '该合买已经截止参与了';
        return $return_data;
      }
      $he = $model->he->toArray();
      if(($he['buy'] + $post_data['value']) > $he['all']){
        $return_data['msg'] = '该合买最多只能参与' . ($he['all'] - $he['buy']);
        return $return_data;
      }
      $is_action = moneyAction([ 'uid'=>$user['id'],'money'=>$post_data['value'],'type'=>0,'explain'=>'合买跟单下注' ]);
      if($is_action['code'] == 0){
        $return_data['msg'] = $is_action['msg'];
        return $return_data;
      }
      if(Db::execute('update betting_he set buy = buy + ? where `all` >= buy + ? AND betting_id = ?', [ $post_data['value'],$post_data['value'],$post_data['id'] ]) && Db::table('betting_gen')->insert([ 'betting_id'=>$post_data['id'],'user_id'=>$user['id'],'money'=>$post_data['value'],'create_time'=>time() ])){
        $return_data['code'] = 1;
        $return_data['msg'] = '跟单成功,祝您中大奖';
      }
      else{
        $return_data['msg'] = '跟单失败';
      }
    }
    if(isset($post_data['id'])){
      $return_data['data'] = ($post_data['temp'] == 0 ? $this->getInfo($post_data['id'])['data'][0] : $this->details()['data']);
    }
    return $return_data;
  }

  public function chekState($map = []){
    if($map['expect'] == 0){
      $map['expect'] = Db::table('betting_zhui')->where([ 'betting_id'=>$map['betting_id'] ])->min('expect');
    }
    if(!isset($this->config_time['t' . $map['type']])){
      $this->config_time['t' . $map['type']] = Db::table('lottery_config')->field('time_config')->find([ 'type'=>$map['type'] ]);
      if($this->config_time['t' . $map['type']]){
        $this->config_time['t' . $map['type']] = json_decode($this->config_time['t' . $map['type']]['time_config'],true);
      }
    }
    if((new Lottery)->calculationData([ 'data'=>$this->config_time['t' . $map['type']],'type'=>$map['type'] ])['expect'] == $map['expect']){
      return true;
    }
    return false;
  }

  public function details(){
    $return_data = [
      'code' => 0,
      'msg' => '没有找到数据',
      'data' => []
    ];
    $post_data = input('post.');
    if(!empty($post_data) && isset($post_data['id'])){
      $return_data['data'] = $this->getInfo($post_data['id'])['data'][0];
      $return_data['data'] += Db::table('betting')->field('user_id,create_time,content,category')->where([ 'id'=>$return_data['data']['id'] ])->find();
      $return_data['data'] += Db::table('betting_he')->field('num,tc,open')->where([ 'betting_id'=>$return_data['data']['id'] ])->find();
      // 查出所有跟单用户
      $return_data['data']['gen'] = Db::table('betting_gen a')->field('a.user_id,a.money,a.win,a.create_time,b.username')->join([
        [ 'user b','b.id=a.user_id' ]
      ])->where([ 'a.betting_id'=>$return_data['data']['id'] ])->order('a.id ASC')->select();
      // 这里如果有追号，在追号表查询所用追号期号
      if($return_data['data']['expect']){
        $return_data['data']['expect'] = [ ['expect'=>$return_data['data']['expect']] ];
      }else{
        $return_data['data']['expect'] = Db::table('betting_zhui')->field('expect')->where([ 'betting_id'=>$return_data['data']['id'] ])->select();
      }
      $user = $this->checkLogin();
      $chat_data = false;
      // 这里格式化跟单用户详情，及判断当前查看用户是否是跟单人
      foreach ($return_data['data']['gen'] as &$value) {
        $value['create_time'] = date('m-d H:i:s',$value['create_time']);
        if($user['code'] && !$chat_data && $value['user_id'] == $user['data']['id']){
          $chat_data = true;
        }
      }

      // 这里对投注内容是否公开及格式化处理
      if($user['code'] && $user['data']['id'] == $return_data['data']['gen'][0]['user_id']){
        // 如果是发起人自己查看,直接显示投注内容
        $return_data['data']['content'] = bettingFormat([ $return_data['data'] ])[0]['content'];
      }else{
        switch ($return_data['data']['open']) {
          case 0:
            $return_data['data']['content'] = bettingFormat([ $return_data['data'] ])[0]['content'];
            break;
          case 1:
            if($chat_data){
              $return_data['data']['content'] = bettingFormat([ $return_data['data'] ])[0]['content'];
            }else{
              $return_data['data']['content'] = '发起人对投注内容设置为跟单用户可见';
            }
            break;
          case 2:
            if($return_data['data']['state']){
              $return_data['data']['content'] = '发起人对投注内容设置为跟单截止后公开';
            }else{
              $return_data['data']['content'] = bettingFormat([ $return_data['data'] ])[0]['content'];
            }
            break;
          case 3:
            $return_data['data']['content'] = '发起人对投注内容设置为完全保密';
            break;
          default:
            $return_data['data']['content'] = '投注内容丢失';
            break;
        }
      }

      // 这里查询每期的开奖号码
      foreach ($return_data['data']['expect'] as &$value) {
        $is_code = Db::table('lottery_code')->field('content')->where([ 'type'=>$return_data['data']['type'],'expect'=>$value['expect'] ])->find();
        $value['code'] = (empty($is_code) ? '未开奖' : $is_code['content']);
      }
      // 如果是合买大厅点击进来，查看次数加1
      if(isset($post_data['type']) && $post_data['type']){
        Db::table('betting_he')->where([ 'betting_id'=>$return_data['data']['id'] ])->setInc('num', 1);
        $return_data['data']['num'] += 1;
      }
      // 投注时间格式化显示
      $return_data['data']['create_time'] = date('Y-m-d H:i:s',$return_data['data']['create_time']);
      // 自购为参与表第一条的 money
      $return_data['data']['zg'] = round($return_data['data']['gen'][0]['money'],2);
      $return_data['code'] = 1;
      $return_data['msg'] = 'success';
    }
    // print_r($return_data);
    return $return_data;
  }
}
