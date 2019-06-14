<?php
namespace app\home\model;
use think\Model;

class UserBank extends Model
{
   protected $pk = 'user_id';
   protected $autoWriteTimestamp = false;
}
