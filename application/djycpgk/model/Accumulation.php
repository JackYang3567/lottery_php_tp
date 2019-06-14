<?php
namespace app\djycpgk\model;

use think\Model;

class Accumulation extends Model {
    static public function getCount($user_id = '', $create_time = '1970-01-01 08:00:00', $create_time2 = '')
    {
        $fields = ['in_money','online_money', 'out_money', 'use_money', 'give', 'debit', 'winning', 'refund', 'maid', 'return', 'money'];
        $accumulation = new self;
        if ($create_time2 == '') {
            $create_time2 = date('Y-m-d H:i:s');
        }

        $create_time = strtotime($create_time);
        $create_time2 = strtotime($create_time2)+86400;

        if (!$end = $accumulation->where('user_id', $user_id)->where('create_time', '<', $create_time2 )->order('create_time DESC')->find()) {

            $res = [];
            foreach ($fields as $item) {
                $res[$item] = 0;
            }
            return $res;
        }

        if (!$begin = $accumulation->where('user_id', $user_id)->where('create_time', '<', $create_time)->order('create_time DESC')->find()) {

            $res = [];
            foreach ($fields as $item) {
                $res[$item] = 0;
            }
        }

        $res = [];
        foreach ($fields as $item) {
            $res[$item] = $end[$item] - $begin[$item];
        }

        return $res;
        
    }
}