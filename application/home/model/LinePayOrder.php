<?php
namespace app\home\model;
use think\Model;

class LinePayOrder extends Model
{
    protected $autoWriteTimestamp = 'timestamp';
    protected $updataTime = false;

    /**
     * 大于24小时未支付订单 切换为撤销状态
     */
    public function revoke(){
        self::where('create_time','<=',date('Y-m-d H:i:s',(time()-86400)))->where('status','=',0)->update(['status'=>2,'over_time'=>date('Y-m-d H:i:s')]);
    }

    /**
     * 查询最近24小时未支付订单个数
     * @return int 
     */
    public function onNum(){
        return self::where('create_time','>',date('Y-m-d H:i:s',(time()-86400)))->where('status','=',0)->count();
    }

    /**
     * 获取用户名
     */
    public function getUsernameAttr($value, $data)
    {
        return User::get($data['user_id'])->username;
    }

    /**
     * 转换支付类型
     */
    public function getTypepayAttr($value, $data)
    {
        return $data['type_pay'] ? bankTool($data['type_pay']) : '无';
    }

    /**
     * 获取最新订单情况
     * @param array $where 如果有值表示有查询条件
     * @param array $order 如果有表示有排序条件
     */
    public static function listData($where = [],$order = []){
        $rs = self::order('id','DESC')->where($where)->paginate(15);
        $rs->append(['username','typepay'])->hidden(['type_pay']);
        return $rs;
    }
}
