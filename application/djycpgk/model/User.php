<?php
namespace app\djycpgk\model;

use think\Db;
use think\Model;

class User extends Model {
    static public function usernameToId($username)
    {
        if (!$user = self::get('username', $username)) {
            return 0;
        }
//        $user->where('type',0);
        return $user->type;
    }

    public $count_time = [
        'begin' => '1970-01-01 08:00:00',
        'end' => ''
    ];

    public function accumulation($time1, $time2)
    {
        return Accumulation::getCount($this->id, $time1, $time2);
    }
    public  function  getParentAttr($value,$data){ //获取上级代理

        if (!empty($data['proxy_id'])){
            return Proxy::where('uid',$data['proxy_id'])->find()['username'].'(代理)';
        }
        return '无上级';
    }
    public  function  getSuperiorUserAttr($value,$data){ //获取上级用户

        if (!empty($data['pid'])){
            return self::where('id',$data['pid'])->find()['username'].'(用户)';
        }
        return '无上级';
    }
    public function  getAccumulatedWinningAttr($value,$data){//获取累积中奖

        if (!$winninng = Accumulation::where('user_id',$data['id'])->order('id','desc')->find()['winning']){
            return '0.00';
        }
        return $winninng;
    }

    public function  getIsOnlineAttr($value,$data)//判断是否在线
    {
        if ( time()-$data['active_time'] < 1200 ){
            return 1;
        }
        return 0;
    }
    public function getDianHuaAttr($value,$data){
        if(!$userlnfo = UserInfo::where('user_id',$data['id'])->find()['phone_number']){
            return '未填写号码';
        }
        return $userlnfo;
    }
    public function getDengJiAttr($value,$data){ //等级图片
        if ($data['grade'] !=0){
            $dengji = Db::table('user_rank')->where(['rank'=>$data['grade']])->find();
            return $dengji['logo'];
        }else{
            $ss =  Accumulation::where('user_id',$data['id'])->field('(in_money+online_money) as chongzhi,use_money')->order('create_time desc')->find();
            $dengji = Db::table('user_rank')->order('rank desc')->select();
            foreach ($dengji as $k =>$v ){
                $sj = json_decode($v['condition'],true);
                if ($ss['chongzhi'] >= $sj['recharge'] ){
                    if ( $ss['use_money'] >= $sj['account']){
                        return $v['logo'];
                    }
                }
            }
        }
    }
    public function getSheBeiAttr($value,$data){ //设备判断
        $agent = LoginLog::where('user_id',$data['id'])->field('browser')->order('id desc')->find()['browser'];
        $is_pc = strpos($agent, 'Windows NT')? true : false;
        $is_iphone = (strpos($agent, 'iPhone')) ? true : false;
        $is_ipad = (strpos($agent, 'iPad')) ? true : false;
        $is_android = (strpos($agent, 'Android')) ? true : false;
        if($is_pc){
            return "PC";
        }
        if($is_iphone){
            return "iPhone";
        }
        if($is_ipad){
            return "iPad";
        }
        if($is_android){
            return "Android";
        }
        return '未知设备';
    }
}
