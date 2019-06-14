<?php
namespace app\home\model;
use think\Model;

class UserConfig extends Model
{
  protected $pk = 'user_id';
  protected $autoWriteTimestamp = false;
}
