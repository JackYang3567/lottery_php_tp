<?php
namespace app\djycpgk\controller;
use app\home\model\SystemConfig;
use Exception;
use think\Controller;
use think\Db;

class Commission extends Controller {

	// public function Harbor() //计算 港彩 反水
	// {
	// 	// $where1 = [['type','21'],]; //判断是 港彩

	// 	$leixing = Db::table('lottery_config')
	// 	->where('type','21')
	// 	->find();
	// 	dump($leixing);
	// }

	//28反水
	public function Backwater()
	{
		$where1 = [
					['type', 'in', '24,25,26,27'],


					]; //判断是 24：北京28 25：加拿大28 26：重庆28 27：新疆28

		$switch = Db::table('lottery_config')
		->where($where1)
		->select();


		foreach($switch as $ky=>$vr){
		$vr['return'] =  json_decode($vr['return'],true);
		$today = strtotime(date("Y-m-d"),time());//获得当日凌晨的时间戳
		$end = $today+60*60*24;//当天的24点时间戳
		if ($vr['return']['switch'] == 1 ) {//等于 1代表游戏是开启的
			$tj2 = [
					['type', '=', $vr['type']],
					['create_time','between',[$today,$end]]
				];
			$yx = DB::table('betting')   //获取期号 和投注总额
				->field('expect,SUM(money)')
				->where($tj2)
				->group('expect')
				->select();

			// dump($yx);
			$tj3 = [
					['create_time','between',[$today,$end]],
					];


	  		$mop['be.type'] =  ['be.type', '=', $vr['type']];
      		$mop['u.type'] = ['u.type','=','0'];
     		$mop['be.create_time'] = ['be.create_time', 'between', [$start_time, $end_time]];
      		$ptyh = DB::table('betting')->alias('be')->where($mop)->join('user u','be.user_id = u.id')->field('be.user_id')->group('be.user_id')->select();
      		$uid = [];

      		foreach ($ptyh as $ss) {
        	$uid[] = $ss['user_id'];
      		}

			$user_id = DB::table('betting')
						->field('user_id')
					   ->where($tj3)
					   ->select();
			dump($user_id[0]);
			$tj = [
					['type', '=', $vr['type']],
					['user_id','in',$user_id[0]],
					['create_time','between',[$today,$end]]
				];
			$dxds = DB::table('betting')
				->where($tj)
				->select();

			// dump($dxds);
			if (empty($dxds)){//判断是否为空

			}else{
			$yazhu = 0;
			$zhongjiang = 0;
			$fylbl = 0;
			foreach($dxds as $key=>$vc){

    			$dxds[$key]['content'] =  json_decode($dxds[$key]['content'],true);

    			foreach($dxds[$key]['content'] as $k=>$value){

    					// dump($dxds[$key]);
    					if (in_array($value['code'], ['a','b','c','d'])) {//判断是否为 大小单双

    						// dump($value['money']);

    						$yazhu = $yazhu + $value['money'];//将 一天的押注金额加上
    					}
    			}
    			 $zhongjiang = $zhongjiang+$vc['win'];//获取赢利
    			 // dump($vr['return']['condition']);//获取比例
    			 $dxbili = $vr['return']['condition']/100;
    			 // dump($dxbili);
    			 if ($dxds[$key]['expect'] == $yx[$key]['expect']) {

    			 	$bili  = $yazhu/$yx[$key]['SUM(money)'];
    			 	// dump($bili);
    			 	// dump($yazhu);
    			 	// dump($yx[$key]['SUM(money)']);

    			 	if ($bili >= $dxbili) {
    			 		exit();
    			 	}
    			 }

    		}
    		// dump($dxds[$key]['money']);
    		$fuyingli = $dxds[$key]['money']-$zhongjiang; //计算负盈利  押注-中奖金
    		arsort($vr['return']['range']);//将 多少负盈利的反水比例 以 数组的倒序排列

    		foreach($vr['return']['range'] as $K=>$v){
    				if ($fuyingli >= $v[0]) {
    					$fylbl = $v[1]; //将负盈利的比例传递给 $fylbl
    					break;
    				}
    			}
    		$fylbl =$fylbl/100; //反水比例 /100 成为百分比
    		$fsje = $fuyingli*$fylbl;// 负盈利  * 反水比例
    		if ($fsje<0) {
    			$fsje =0;
    		}
    		$fsje = round($fsje,2);//保留2位小数
    		// dump($switch[$ky]['name']);
    		// dump($fylbl);
    		// dump($fuyingli);
    		dump($fsje);
    		// echo $fsje;
			}
		}else{

		}

	}




	}

    //返佣
    public function commission($user_id = '', $time = '') {
        $map = [];
        if ($user_id != '') {
            $map['user_id'] = $user_id;
        }
        if ($time != '') {
            $day_start = strtotime($time);
            $day_end = $day_start + 3600 * 24 - 1;
        } else {
            $t = time();
            $day_start = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
            $day_end = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));
        }
        $list = DB::table('new_relationship')->where($map)->select();
        // dump($day_start);
        $flag = 0;
        foreach ($list as $key => $value) {
            $user = Db::table('user')->field('status,group')->where('id',$value['user_id'])->find();
            if ($user['status'] == 1 || $user['group']==1){
                continue;
            }
            $data['one_commission'] = $list[$key]['child_one'] != null ? $this->calculateCommission($value['child_one'], 1, $time, $value['rebate_way']) : 0;
            $data['two_commission'] = $list[$key]['child_two'] != null ? $this->calculateCommission($value['child_two'], 2, $time, $value['rebate_way']) : 0;
            $data['three_commission'] = $list[$key]['child_three'] != null ? $this->calculateCommission($value['child_three'], 3, $time, $value['rebate_way']) : 0;
            $data['user_id'] = $value['user_id'];
            $data['create_time'] = $day_end;
            $data_exist = DB::table('commission_log')->where('user_id', $value['user_id'])->where('create_time', 'between', [$day_start, $day_end])->find();
            //dump($data);die;
            if (null == $data_exist) {

                if ($data['one_commission'] != 0 || $data['two_commission'] != 0 || $data['three_commission'] != 0) {
                    Db::startTrans();
                    try {
                        $rs = DB::name('commission_log')->insert($data);
                        // $capital_data['user_id'] = $value['user_id'];
                        // $capital_data['type'] = 8;
                        // $capital_data['money'] = $data['one_commission']+$data['two_commission']+ $data['three_commission'];
                        // $capital_data['explain'] = '返佣';
                        // $capital_data['create_time'] = $day_end;
                        // $capital_rs = Db::name('capital_detail')->insert($capital_data);
                        // $user_rs = DB::name('user')->where('id',$value['user_id'])->setInc('money',$data['one_commission']+$data['two_commission']+ $data['three_commission']);

                        $data_config['uid'] = $value['user_id'];
                        $data_config['money'] = $data['one_commission'] + $data['two_commission'] + $data['three_commission'];
                        $data_config['type'] = 8;

                        //dump($data_config);die();

                        if($data_config['money'] < 0 ){
                            $flag = 0;
                        }else{
                            $leiji_rs = moneyAction($data_config);
                            if($rs && $leiji_rs['code'] == 1){
                                $flag++;
                            }
                        }



                        //if ($rs && $leiji_rs['code'] == 1) {
                        //$flag++;
                        //}


                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                    }
                }
            }
        }

        if ($flag > 0) {
            return json(['error' => 0, 'msg' => '返佣成功']);
        } else {
            return json(['error' => 1, 'msg' => '返佣失败']);
        }
    }


    public function calculateCommission($user_ids = '', $level = '', $time = '', $rebate_way = '') {

        $rates = DB::table('system_config')->where('name', 'in', ['child_one_rate', 'child_two_rate', 'child_three_rate'])->select();
        //   dump($rates);die;
        $child_one_rate = $rates[0]['value'];
        $child_two_rate = $rates[2]['value'];
        $child_three_rate = $rates[1]['value'];
        $commission = '';
        //0.2              0.08              0.02
        if ($time != '') {
            $day_start = strtotime($time);
            $day_end = $day_start + 3600 * 24 - 1;

        } else {
            $t = time();
            $day_start = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
            $day_end = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));
        }

        $map['user_id'] = ['user_id', 'in', explode(',', $user_ids)];
        $map['create_time'] = ['create_time', 'between', [$day_start, $day_end]];

        //  dump($rebate_way);die;
        if ($rebate_way == 0) {
            $recharge = Db::table('capital_audit')->where($map)->where(['state' => 1, 'type' => 0])->sum('money');
            $cash = Db::table('capital_audit')->where($map)->where(['state' => 1, 'type' => 1])->sum('money');
            $result = (abs($recharge) - abs($cash)) > 0 ? (abs($recharge) - abs($cash)) : 0.00;

        } else {
            // $winning = Db::table('capital_detail')->where($map)->where('type',3)->sum('money');
            $betting = Db::table('capital_detail')->where($map)->where('type','in', [0,14])->sum('money');
            $re_money = Db::table('capital_detail')->where($map)->where('type','=', 6)->sum('money');
            //dump($re_money);
            // dump($betting);

            $betting = abs($betting) - abs($re_money);

            $result = $betting;
        }

        if ($level == 1) {
            $commission = round($result * $child_one_rate / 100, 2);
        } elseif ($level == 2) {
            $commission = round($result * $child_two_rate / 100, 2);
            //  dump($child_two_rate);die;
        } else {
            $commission = round($result * $child_three_rate / 100, 2);
        }

        //dump(strpos('ab_front_abc','_front_')>0);die;

        return $commission;
    }

}
