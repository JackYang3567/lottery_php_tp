<?php
namespace app\djycpgk\model;

use think\Model;

class CapitalDetail extends Model {


    public function getUsernameAttr($value, $data)
    {
        if (!$user = User::field('username,type')->where('id', $data['user_id'])->find()) {
            return '未知用户';
        }else if ($user['type'] == 2){
            return $user->username.'(内部试玩)';
        }
        return $user->username;
    }

    public function getStatusTextAttr($value,$data)//获取器
    {
        if (!$CapitalDetailType = CapitalDetailType::field('name')->where('id',$data['type'])->find()){
                return '未知类型';
        }
        return $CapitalDetailType['name'];
    }


    public static function accumulation($keywords='',$type='',$start_time='', $end_time='') //方法
    {

        $capitalDetail = self::order('id desc');
        if ($start_time != '') {
            $capitalDetail->where('create_time', '>=', strtotime($start_time));
        }

        if ($end_time != '') {
            $end_time = strtotime($end_time)+86400;
            $capitalDetail->where('create_time', '<', $end_time);
        }
        if ($keywords!=''){
            $user_id = User::usernameToId($keywords);
            $capitalDetail->where('user_id', $user_id);
        }

        if ($type!='') {
            $capitalDetail->where('type', $type);
        }
        return $capitalDetail;
    }
    public static function TongJi($type='',$start_time='',$end_time=''){


        $capitalDetail = self::where('type',$type)->field('money');

        if ($start_time =='' && $end_time ==''){
            $start_time =strtotime(date("Y-m-d"),time());
            $end_time =strtotime(date("Y-m-d"),time())+24*3600;

            $capitalDetail->where('create_time', '>=', $start_time)->where('create_time', '<', $end_time);
        }
        if ($start_time != '') {
            $capitalDetail->where('create_time', '>=', strtotime($start_time));
        }
        if ($end_time != '') {
            $end_time = strtotime($end_time)+86400;
            $capitalDetail->where('create_time', '<', $end_time);
        }

        return $capitalDetail;

    }

}