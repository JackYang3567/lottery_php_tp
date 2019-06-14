<?php
namespace app\djycpgk\model;

use think\Model;

class ChatRoom extends Model {

    public function getUsernameAttr($value, $data)
    {
        if (!$user = User::get($data['user_id'])) {
            return '未知用户';
        }

        return $user->username;
    }

    public function getCreateTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    public static function getChatList($last_id)
    {
            $ss = self::where('user_id', '<>', '0')->where('id', '>', $last_id)->order('id DESC')->select();
            return  $ss;

    }
}