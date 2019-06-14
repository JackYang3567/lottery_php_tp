<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use app\home\model\SystemConfig;
use app\home\model\Betting;
use app\home\model\CapitalDetail;
use app\home\model\User;
use app\home\controller\ApiGameConfig;

class ReturnLoss extends Controller
{
	private $config;
	/**
	 * 初始化查询当前日负配置,确认配置
	 */
	// public function initialize()
	// {

	// }

	public function run(){
		(new ApiGameConfig)->pullBet();
		$data = SystemConfig::get(51)->toArray();
		if(empty($data)){
			return;
			// $this->error('没有找到日负回水设置');
		}
		// print_r($data);die();
		$data['value'] = json_decode($data['value'],true);
		
		if($data['value']['switch']['value'] == 0){
			return;
			// $this->error('功能已关闭');
		}
		
		if( count($data['value']['lottery']['value']) == 0 ){
			return;
			// $this->error('没有配置彩种');
		}
		$this->config = $data['value'];

		$this->back();
	}

	/**
	 * 回水入口 前一天回水
	 */
	public function back(){
		//判断回水时间，大于当前时间，则未到回水时间
		if($this->config['switch']['time'] > time() ){
			return;
		}

		//先将时间重置为明天凌晨4:10分
		$bit = $this->config;
		$bit['switch']['time'] = strtotime(date('Y-m-d 00:00:00',time())) + 86400 + (4*3600) + (10*60);
		Db::table('system_config')->where('id','=',51)->update(['value' => json_encode($bit)]);

		//获取正常用户(除开试玩用户,冻结用户)
		// $all_id = User::AllUserIdGet();
		
		//上一个回水时间
		$re_day = date('Ymd',$this->config['switch']['time'] );
		//当前时间
		$now_day = date('Ymd',time());
		//判断相差相差一天即为正常回水天数
		$dec_day = $now_day - $re_day;
		
		//防止未知出错
		if($dec_day < 0 ){return;}
		
		if( $dec_day == 0 ){
			//正常回水情况下
			$this->actionBack($this->config['switch']['time'] - 86400);
		}else{ 
			//不正常回水情况下
			$this->actionBack($this->config['switch']['time'] - 86400);
			$this->actionBack($this->config['switch']['time']);
		}
		
		/*路线*/
		// -->1月1日
		// -->1月2日(4:10)后  访问并投注 触发回水1月1日 并将下次回水时间调整至1月3日(4:10)后
		// -->1月3日(4:10)前  访问并投注 未触发回水 之后没有访问

		// -->1月10日(4:10)后 访问并投注 

		// -->触发回水1月2日 并将下次回水时间调整至1月11日(4:10) ？？此方法错误

		// -->查询今天是否是正常反水天数
		// -->如果是则正常反水
		// -->如果不是则要反水1月2日 和 1月3日 并将下次回水时间调整至1月11日(4:10)
	}

	/**
	 * 回水方法 默认回一天
	 * @param int $start 开始时间
	 */
	protected function actionBack($start = 0)
	{
		// print_r('123');
		if($start == 0){
			return ;//['code' => -1,'msg' => '时间错误'];
		}
		//设置时间
		$time = [strtotime(date('Y-m-d 00:00:00',$start)),strtotime(date('Y-m-d 23:59:59',$start))];
		//获取除了 试玩用户,冻结用户,取消福利用户 以外的用户
		$all_id = User::AllUserIdGet();
		//遍历用户
		foreach ($all_id as $value) {
			//查询下注和中奖
			$rs = Betting::negative($this->config['lottery']['value'],$time,$value);
				// print_r($rs);
				// continue;
			//计算用户亏损 下注-中奖
			$ks = abs($rs[0]) - abs($rs[1]);
			$re_money = 0;
			//如果亏损进入计算亏损回水
			if( $ks > 0 ){
				// print_r($this->config);die();
				if($this->config['return_rule']['value'] == 0){
					//方式一回水
					$re_money = round($ks*( (int)$this->config['return_rule']['rule'][0][1]/100 ),2);
				}else{
					//方式二回水
					foreach ($this->config['return_rule']['rule'][1][1] as $vo) {
						if($ks >= $vo['min'] && $ks <= $vo['max']){
							$re_money = round( $ks/100*$vo['value'] ,2);
							break;
						}
					}
				}
				// print_r($re_money);
				// 如果$re_money大于0 进行回水添加
				if($re_money > 0){
					$add = [
						'uid' => $value,
						'money' => $re_money,
						'type' => 18,
						'explain' => '日负回水'.date('Y-m-d',$time[1]),
						'time' => strtotime(date('Y-m-d 04:10:00',($start+86400) ))
					];
					
					if(moneyAction($add)['code'] > 0){
						$return_data['code'] = 1;
						$return_data['msg'] = '回水成功';
					}
				}
			}
		}
	}
	/*
	 * 回水格式
	 $data = [
		 'switch' => [
			 'name' => '日负回水开关',
			 'value' => 0
		 ],
		 'lottery' => [
			 'name' => '日负回水彩种',
			 'value' => [24,25,26,27]
		 ],
		 'return_rule' => [
			 'name' => '日负回水方式',
			 'value' => 0,
			 'rule' => [
				 ['日负按固定比列回水',2],
				 ['日负按不同条件不同比例回水',[
						 [
							 'min' => 0,
							 'max' => 500,
							 'value' => 10
						 ],
						 [
							 'min' => 501,
							 'max' => 1000,
							 'value' => 5,
						 ]
					 ]
				 ]
			 ]
		 ]
	 ];
	 */

}
