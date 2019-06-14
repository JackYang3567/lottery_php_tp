<?php
namespace app\home\model;
use think\Model;

class BettingGen extends Model
{
  public function bet(){
    return $this->hasOne('Betting','id','betting_id');
  }

  public function he(){
    return $this->hasOne('Bettinghe','betting_id','betting_id');
  }

  public function zhui(){
    return $this->hasOne('BettingZhui','betting_id','betting_id');
  }
}
