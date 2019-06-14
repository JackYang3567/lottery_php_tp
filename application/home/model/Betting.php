<?php
namespace app\home\model;
use think\Model;

class Betting extends Model
{
  protected $json = ['content'];

  public function he(){
    return $this->hasOne('BettingHe','betting_id','id');
  }

  public function gen(){
    return $this->hasMany('BettingGen','betting_id','id');
  }

  public function zhui(){
    return $this->hasMany('BettingZhui','betting_id','id');
  }

  /**
   * 获取器28系列,投注数据修改
   * @return array
   */
  public function getChg28Attr($value, $data)
  {
    $bet_arr = [
      'a' => '大',
      'b' => '小',
      'c' => '单',
      'd' => '双',
      'ac' => '大单',
      'ad' => '大双',
      'bc' => '小单',
      'bd' => '小双',
      'max' => '极大',
      'min' => '极小',
      'green' => '绿',
      'blue' => '蓝',
      'red' => '红',
      'yellow' => '豹子',
    ];
    foreach ($data['content'] as &$item) {
      $item->code = $bet_arr[$item->code] ?? $item->code;
    };
    return $data['content'];
  }

  public function getUserAttr($value, $data)
  {
    return User::get($data['user_id'])->append(['name'])->visible(['photo', 'type']);
  }

  /**
   * 28系列查询
   * @param number type  彩种类型
   * @param number time  当前最大时间戳
   * @param number level 房间
   * @param number sum   获取条数
   * @return array 格式化后
   */
  public static function listBet($type,$time,$level,$sum)
  {
    $bet = self::where('create_time','>',$time)
                ->where('other','=',$level)
                ->where('type','=',$type)
                ->order('id','DESC')
                ->limit($sum)
                ->select();
    return $bet->append(['chg28', 'user'])->hidden(['content']);
  }

  /**
   * 获取指定彩种 指定时间段 指定用户 的下注与赢钱情况
   * @param array $lottery  需要查询的彩种数组
   * @param array $time 0是开始时间  1是结束时间
   * @param int $uid 用户id
   * @return array 0是投注 1是中奖
   */
  public static function negative($lottery,$time,$uid){
    $bit = self::where('user_id','=',$uid)
          ->where('create_time','>=',$time[0])
          ->where('create_time','<=',$time[1])
          ->where('type','in',$lottery)
          ->where('state','=',1);
    return [$bit->sum('money'),$bit->sum('win')];
    
  }
}
