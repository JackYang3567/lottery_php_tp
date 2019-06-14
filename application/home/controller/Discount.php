<?php
namespace app\home\controller;
use app\home\model\User;
use think\Db;

class Discount extends Common
{
  public function getInfo(){
    $data = input('post.');
    if(isset($data['notice_type']) && $data['notice_type'] == 3)
    {
        $data = Db::table('article')
            ->where('cat_id',3)
            ->where('start_time','<=',time())
            ->where('end_time','>=',time())
            ->order('id DESC')
            ->select();
        foreach ($data as &$d)
        {
            $d['start_time'] = date('Y-m-d',$d['start_time']);
            $d['end_time'] = date('Y-m-d',$d['end_time']);
        }
    }
    else
    {
        $model = Db::table('article')
            ->alias('a')
            ->join('article_category ac','ac.id=a.cat_id')
            ->field('a.*,ac.name')
            ->where('a.cat_id','not in',[3])
            ->where('a.start_time','<=',time())
            ->where('a.end_time','>=',time())
            ->order('a.id DESC');
        if(!empty($data) && isset($data['id'])){
            $data = $model->where('a.id','>',$data['id'])->select();
            foreach ($data as &$d)
            {
                $d['start_time'] = date('Y-m-d',$d['start_time']);
                $d['end_time'] = date('Y-m-d',$d['end_time']);
            }
        }else{
            $data = $model->paginate(15,true)->toArray();
            foreach ($data['data'] as &$d)
            {
                $d['start_time'] = date('Y-m-d',$d['start_time']);
                $d['end_time'] = date('Y-m-d',$d['end_time']);
            }
        }
    }
    return $data;
  }

  public function details(){
    $return_data = [
      'code' => 0,
      'msg' => '没有找到数据',
      'data' => []
    ];
    $data = input('post.');
    if(!empty($data) && isset($data['id'])){
      $article = Db::table('article')->field('title,content')->where(['id'=>$data['id']])->find();
      if(!empty($article)){
        $return_data['code'] = 1;
        $return_data['msg'] = '获得数据';
        $return_data['data'] = $article;
      }
    }
    return $return_data;
  }

    public function getChatRoom(){
        $return_data = [
            'code' => 1,
            'msg' => 'nothing',
            'data' => []
        ];
        $id = input('post.id');

        $data = Db::table('chat_room')
//        ->where('id',$id)
            ->limit(20)
            ->order('id DESC')
            ->select();

        if($data){
            foreach ($data as $k => &$value) {
                // if(empty($model)) {  }
                $value['photo'] = 0;
                if($value['type'] == 1){
                    $model = User::get($value['user_id']);
                    if(empty($model)){
                        unset($data[$k]);
                        continue;
                    }
                    $value['user_name'] = getUserName('',true,$model->username);
                    $value['photo'] = $model->photo;
                }elseif($value['type'] == 2){
                    if($value['user_id'] == 0){
                        $value['user_name'] = '系统';
                    }else{
                        $value['user_name'] = '系统管理员';
                        $admin_date = Db::table('admin')->where('uid',$value['user_id'])->find();
                        if(!empty($admin_date)){

                            if(isset($admin_date['nick'])){
                                $value['user_name'] = $admin_nick['nick'];
                            }
                            if(isset($admin_date['photo'])){
                                $value['photo'] = $admin_nick['photo'];
                            }else{
                                $value['photo'] = 0;
                            }
                        }
                    }
                }else{
                    $value['user_name'] = '系统';
                }
                $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
            }
        }
        $return_data['code'] = 1;
        $return_data['msg'] = 'success';
        $set = Db::table('system_config')->field('value')->where(['name'=>'chat_config'])->find();
        $return_data['data'] = [
            'list' => array_reverse($data),
            'set' => ($set ? json_decode($set['value'],true) : null)
        ];
        return $return_data;
    }



  public function getChatRoom_s(){
    $return_data = [
      'code' => 1,
      'msg' => 'nothing',
      'data' => []
    ];
    $id = input('post.id');
    
    $data = Db::table('chat_room')->where('id',$id)->limit(20)->order('id DESC')->select();
    if($data){
      foreach ($data as &$value) {
        // if(empty($model)) {  }
        $value['photo'] = 0;
        if($value['type'] == 1){
          $model = User::get($value['user_id']);
          if(empty($model)){
            unset($data[$k]);
            continue;
          }
          $value['user_name'] = getUserName('',true,$model->username);
          $value['photo'] = $model->photo;
        }elseif($value['type'] == 2){
          if($value['user_id'] == 0){
            $value['user_name'] = '系统';
          }else{
            $value['user_name'] = '系统管理员';
            $admin_date = Db::table('admin')->where('uid',$value['user_id'])->find();
            if(!empty($admin_date)){
              
              if(isset($admin_date['nick'])){
                $value['user_name'] = $admin_nick['nick'];
              }
              if(isset($admin_date['photo'])){
                $value['photo'] = $admin_nick['photo'];
              }else{
                $value['photo'] = 0;
              }
            }
          }
        }else{
          $value['user_name'] = '系统';
        }
        $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
      }
    }
    $return_data['code'] = 1;
    $return_data['msg'] = 'success';
    $set = Db::table('system_config')->field('value')->where(['name'=>'chat_config'])->find();
    $return_data['data'] = [
      'list' => array_reverse($data),
      'set' => ($set ? json_decode($set['value'],true) : null)
    ];



    return $return_data;
  }

  public function getChatHistory(){
    $return_data = [
      'code' => 0,
      'msg' => '没有数据'
    ];
    $data = Db::table('chat_room')->field('user_id,content,create_time')->order('id DESC')->paginate(20)->toArray();
    if($data){
      foreach ($data['data'] as &$value) {
        $value['user_name'] = ($value['user_id'] ? substr(getUserName($value['user_id']),0,3) : '系统管理员');
        $value['create_time'] = date('Y-m-d H:i:s',$value['create_time']);
      }
      $return_data['code'] = 1;
      $return_data['msg'] = 'success';
      $return_data['data'] = $data;
    }
    return $return_data;
  }

  public function timeTool($data){
    $chat_now_time = strtotime(date('His'));
    $chat_begin_time = strtotime($data['begin_time']['value']);
    $cha_time = $chat_now_time - $chat_begin_time;
    $expect = ceil($cha_time / 60 / $data['cha']['value']);
    $is_time = $expect * ($data['cha']['value'] * 60) - $cha_time;
    $expect = date('Ymd') . $expect;
    return [
      'time' => $is_time,
      'expect' => $expect
    ];
  }

  public function getHongBaoConfig(){
    $data = Db::table('system_config')->field('value')->where([ 'name'=>'hongbao_config' ])->find();
    $data = json_decode($data['value'],true);

    $chat_now_time = strtotime(date('His'));
    $chat_begin_time = strtotime($data['begin_time']['value']);

    $is_time = 0;
    $expect = 0;
    $hb_data = [];

    if($chat_now_time > $chat_begin_time && $chat_now_time < strtotime($data['end_time']['value']) && $data['state']['value']){
      $time_tool = $this->timeTool($data);
      $is_time = $time_tool['time'];
      $expect = $time_tool['expect'];
      $config = Db::table('chat_hongbao_config')->field('id,problem,title')->select();
      if($config){
        $length = count($config) - 1;
        for($i = 0;$i < $data['num']['value'];$i++){
          $hb_data[] = $config[mt_rand(0,$length)];
        }
      }
    }
    return [
      'time' => $is_time,
      'expect' => $expect,
      'hb_data' => $hb_data
    ];
  }

  public function hongbaoAction(){
    $post = input('post.');
    $return_data = [
      'code' => 0,
      'msg' => '抢红包失败'
    ];
    if(!isset($post['id']) || !isset($post['value']) || !isset($post['expect']) || empty($post['id']) || $post['value'] == '' || empty($post['expect']))
    {
      return $return_data;
    }
    $user = $this->checkLogin();

    if($user['code']){
      $user = $user['data'];
    }else{
      $return_data['msg'] = '您还没有登陆';
      return $return_data;
    }
    //判断分组是否可以享受该福利
    // if(In::allWelfare() && $user['data']['group'] == 1){
    //   $return_data['msg'] = '您的红包福利已被取消,如有疑问请联系客服';
    //   return $return_data;
    // }
    $data = json_decode(Db::table('system_config')->field('value')->where([ 'name'=>'hongbao_config' ])->find()['value'],true);
    foreach ($data['check']['value'] as $value) {
      if($value == 1){
        // 充值限制
        $user_history_data1 = Db::table('accumulation')->field('in_money,online_money')->where([ 'user_id'=>$user['id'] ])->order('create_time','DESC')->find();
        $user_history_data1 = $user_history_data1 ? ($user_history_data1['in_money'] + $user_history_data1['online_money']) : 0;
        // $user_history_data2 = Db::table('accumulation')->field('in_money')->where([ 'user_id'=>$user['id'],'create_time'=>['create_time','>',time() - ($data['check']['rule'][$value][1] * 86400) ] ])->order('create_time','ASC')->find();
        //$user_history_data2 = $user_history_data2 ? $user_history_data2['in_money'] : 0;
        if($user_history_data1 < $data['check']['rule'][$value][1]){
          $return_data['msg'] = '充值金额至少达到' . $data['check']['rule'][$value][1] . '元,才能参与抢红包';
          return $return_data;
        }
      }else if($value == 2){
        // 余额限制
        if($user['money'] < $data['check']['rule'][$value][1]){
          $return_data['msg'] = '用户余额必须大于' . $data['check']['rule'][$value][1] . '元,才能参与抢红包';
          return $return_data;
        }
      }else if($value == 3){
        // 红包流水限制
        $user_history_data1 = Db::table('accumulation')->field('use_money')->where('user_id','=',$user['id'])->where('chat_hongbao','>',0)->order('create_time','ASC')->find();
        $user_history_data1 = $user_history_data1 ? $user_history_data1['use_money'] : 0;
        $user_history_data2 = Db::table('accumulation')->field('use_money,chat_hongbao')->where('user_id','=',$user['id'])->where('chat_hongbao','>',0)->order('create_time','DESC')->find();
        $user_history_data3 = $user_history_data2 ? $user_history_data2['use_money'] : 0;
        $user_history_data4 = $user_history_data2 ? $user_history_data2['chat_hongbao'] : 0;
        // 判断用户抢得红包后，在之后的下注金额是否大于后台设置的红包流水倍率限制
        if($user_history_data3 - $user_history_data1 < $user_history_data4 * $data['check']['rule'][$value][1]){
          $return_data['msg'] = '您的消费没有达到抢得的红包' . $user_history_data4 . '元的' . $data['check']['rule'][$value][1] . '倍的流水,还需要消费'. ($user_history_data4 * $data['check']['rule'][$value][1] - $user_history_data3 - $user_history_data1) . '元,才能参与抢红包';
          return $return_data;
        }
      }
    }
    $time_tool = $this->timeTool($data);
    if($time_tool['expect'] - $post['expect'] > 1){
      $return_data['code'] = -1;
      $return_data['msg'] = '红包过期了';
      return $return_data;
    }
    $hb_config = Db::table('chat_hongbao_config')->field('answer')->where([ 'id'=> $post['id'] ])->find();
    if($hb_config['answer'] != $post['value']){
      $return_data['msg'] = '您回答问题错误,没有抢到这个红包';
      return $return_data;
    }
    $user_num = Db::table('hongbao_log')->where([ 'user_id'=> $user['id'],'expect'=>$post['expect'] ])->count();
    if($user_num >= $data['user_num']['value']){
      $return_data['msg'] = '这期红包您不能再抢了,每期只能抢' . $data['user_num']['value'] . '个红包';
      return $return_data;
    }
    $hb_num = Db::table('hongbao_log')->where([ 'expect'=> $post['expect'] ])->count();
    if($hb_num >= $data['num']['value']){
      $return_data['code'] = -1;
      $return_data['msg'] = '对不起,这期红包已经被抢完了';
      return $return_data;
    }
    $hb_sum = Db::table('hongbao_log')->where([ 'expect'=> $post['expect'] ])->sum('number');
    // 算出红包还有多少个
    $hb_num = $data['num']['value'] - $hb_num;
    // 算出红包总金额还剩多少
    $hb_sum = $data['sum_money']['value'] - $hb_sum;
    // 每份至少多少
    $hb_mf = round(($hb_sum / $hb_num),2);
    $get_num = $hb_mf + mt_rand(0,($data['max_money']['value'] - $hb_mf > 0 ? $data['max_money']['value'] - $hb_mf : 0));
    $user_action = moneyAction([
      'uid' => $user['id'],
      'money' => $get_num,
      'type' => 16,
      'explain' => '聊天室抢得红包'
    ]);
    if($user_action['code']){
      $return_data['code'] = 1;
      $return_data['msg'] = '恭喜您抢得红包金额' . $get_num . '元';
      Db::table('chat_room')->insert([
        'user_id' => 0,
        'content' => '恭喜玩家' . substr(getUserName($user['id']),0,3) . '*** 在聊天室抢得红包' . $get_num . '元',
        'create_time' => time()
      ]);
      Db::table('hongbao_log')->insert([
        'user_id' => $user['id'],
        'expect' => $post['expect'],
        'number' => $get_num,
        'create_time' => time()
      ]);
    }else{
      $return_data['msg'] = $user_action['msg'];
    }
    return $return_data;
  }
}
