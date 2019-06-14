<?php
namespace app\home\controller;
use app\home\model\Betting;
use think\Db;

class Game extends Common {
	public $lottery_config;
	public $post_data;

	public function _initialize() {
		$this->post_data = input('param.');
		if (empty($this->post_data) || !isset($this->post_data['type'])) {
			$this->error('访问出错');
		}
		isset($this->post_data['Desk']) && ($this->post_data['old'] = $this->post_data['Desk']);
		$this->post_data['Desk'] = 'room1';
		$lottery_config = Db::table('lottery_config')->field('basic_config,time_config,name')->where(['type' => $this->post_data['type'], 'switch' => 1])->find();
		if (empty($lottery_config)) {
			$this->error('此游戏没有配置数据或者已经关闭...');
		} else {
			$lottery_config['basic_config'] = json_decode($lottery_config['basic_config'], true);
			$lottery_config['time_config'] = (empty($lottery_config['time_config']) ? '' : json_decode($lottery_config['time_config'], true));
			$this->lottery_config = $lottery_config;
		}
	}

	//龙虎斗期数计算
	public function expectLHD() {
		$rs = [
			'State' => '', // 当前状态
			'IssueNo' => '', // 当前期号
			'StartTime' => '', // 当前状态时间
			'EndTime' => '', // 当前状态结束时间
		];
		$differ = time() - strtotime(date('Y-m-d ' . $this->lottery_config['time_config']['start_time']));
		//当前期号
		$rs['IssueNo'] = floor($differ / 30);
		//当前剩余时间
		$issue_no = $differ % 30;
		//本期的开始时间 && 开奖时间
		$rs['StartTime'] = strtotime(date('Y-m-d ' . $this->lottery_config['time_config']['start_time'])) + ($rs['IssueNo'] * 30);
		if ($issue_no <= 17) {
			//下注时间
			$rs['State'] = 'Bet';
			$rs['EndTime'] = $rs['StartTime'] + 17;
		} else {
			// 开奖时间
			$rs['StartTime'] = $rs['StartTime'] + 18;
			$rs['EndTime'] = $rs['StartTime'] + 11;
			$rs['State'] = 'Close';
		}
		//-----------以下为下一个状态数据----------------
		$rs['NextIssueNo'] = $rs['IssueNo'] + 1;
		if ($rs['State'] == 'Close') {
			$rs['NextState'] = 'Bet';
			$rs['NextStartTime'] = $rs['EndTime'] + 1;
			$rs['NextEndTime'] = $rs['NextStartTime'] + 17;
		} else {
			$rs['NextState'] = 'Close';
			$rs['NextStartTime'] = $rs['EndTime'] + 1;
			$rs['NextEndTime'] = $rs['NextStartTime'] + 11;
		}
		$rs['IssueNo'] = date('Ymd') . sprintf("%04d", $rs['IssueNo']);//$rs['IssueNo'];//sprintf("%04d", 2);
		$rs['NextIssueNo'] = date('Ymd') . sprintf("%04d", $rs['NextIssueNo']);//$rs['NextIssueNo'];
		return $rs;
	}
	//获取筹码
	public function getChess() {
		$login = $this->checkLogin();
		if ($login['code'] == 0) {
			$chess = ['10', '100', '500', '1000', '10000'];
		} else {
			$rs = Db::table('user_config')->where('user_id', '=', $login['data']['id'])->find();
			if (!empty($rs)) {
				$rs['reception'] = json_decode($rs['reception'], true);
				$chess = isset($rs['reception']['chess']) ? $rs['reception']['chess'] : ['10', '100', '500', '1000', '10000'];
			} else {
				$chess = ['10', '100', '500', '1000', '10000'];
			}
		}
		return $chess;
	}
	//
	public function getInfo() {

		$data = $this->expectLHD();
		// print_r($data);die;
		$is_code = Db::table('lottery_code')->where('type', '=', $this->post_data['type'])->order('expect', 'DESC')->find();
		if ($data['State'] == "Bet") {
			$open_code = json_decode($is_code['content'], true);
		} else {

			if ($is_code['expect'] != $data['IssueNo']) {
				$open_code = $this->control();
				// $open_code = $this->open();
				$in_data = [
					'expect' => $data['IssueNo'],
					'type' => $this->post_data['type'],
					'content' => json_encode($open_code),
					'create_time' => time(),
				];
				if($test_li = Db::table('lottery_code')->where('type', '=', $this->post_data['type'])->where('expect','=',$data['IssueNo'])->find()){
					$open_code = json_decode($test_li['content'], true);
					//Db::table('lottery_code')->where('expect','=',$data['IssueNo'])->where('type','=',$this->post_data['type'])->update(['content'=>json_encode($open_code),'create_time'=>time()]);
				}else{
					Db::table('lottery_code')->insert($in_data);
				}
				//try{ Db::table('lottery_code')->insert($in_data); } catch (\Exception $e) {};
			} else {
				$open_code = json_decode($is_code['content'], true);
			}
			// $this->prize($data['IssueNo'], $this->post_data['type'], $open_code['code'], $this->lottery_config);
		}


		$data = [
			'State' => $data['State'], //"Close",//目前状态 close投注时间  bet开奖时间  shuffle洗牌时间
			'DisplayName' => '', //$this->post_data['room'],//房间号
			'IssueNo' => $data['IssueNo'], //$time_data['expect'],//期号30秒一期
			'MinBetMoney' => $this->lottery_config['basic_config']['room'][$this->post_data['old']]['min'], //房间最小投注
			'MaxBetMoney' => $this->lottery_config['basic_config']['room'][$this->post_data['old']]['max'], //房间最大投注
			'Level' => 1, // 等级
			'Chip' => $this->getChess(), //筹码
			'RemainCard' => $open_code['num'], //当前剩余牌数量
			'PreOpenCard' => $open_code['prent_code'], //上次开奖内容
			'StartTime' => $data['StartTime'], //开始时间
			'EndTime' => $data['EndTime'], //结束时间
			'OpenNum' => $data['State'] == "Close" ? join(',', $open_code['code']) : '', //本次开奖结果
			'GiveCount' => $open_code['count'], //局数
			'NextIssueNo' => $data['NextIssueNo'], //($time_data['expect'] + 1),//期号
			'NextState' => $data['NextState'], //下一个动作Close投注时间  Bet开奖时间  shuffle洗牌时间
			'NextStartTime' => $data['NextStartTime'], //下一个动作开始时间
			'NextEndTime' => $data['NextEndTime'], //下一个动作结束时间
			'ServerTime' => time(), //服务器时间
			'IsEnable' => true, //房间开启true 关闭false
			// 房间是否是开放时间
			'Status' => (strtotime(date('His')) < strtotime(date($this->lottery_config['time_config']['start_time'])) ? 0 : 1),
			'CloseInfo' => '', //??
		];
		return $data;
	}
	public function open($type = '') {
		if (empty($type)) {
			$type = $this->post_data['type'];
		}

		$data = Db::table('lottery_code')->field('content')->where(['type' => $type])->order('expect DESC')->find();
		if (!empty($data)) {
			$data = json_decode($data['content'], true);
		}

		// print_r(empty($data));die;
		if (empty($data) || $data['num'] < ($type == 1 ? 3 : 6)) {
			$data = $this->prizeInfo();
		}
		$rand_code = $this->rand_code($data, ($type == 0 ? 4 : 2));

		if ($type == 0) {
			//闲起手点数
			$is_chat = ((substr($rand_code['code'][0], 0, strlen($rand_code['code'][0]) - 1) > 9 ? 0 : substr($rand_code['code'][0], 0, strlen($rand_code['code'][0]) - 1)) + (substr($rand_code['code'][2], 0, strlen($rand_code['code'][2]) - 1) > 9 ? 0 : substr($rand_code['code'][2], 0, strlen($rand_code['code'][2]) - 1))) % 10;
			//庄起手点数
			$is_chat1 = ((substr($rand_code['code'][1], 0, strlen($rand_code['code'][1]) - 1) > 9 ? 0 : substr($rand_code['code'][1], 0, strlen($rand_code['code'][1]) - 1)) + (substr($rand_code['code'][3], 0, strlen($rand_code['code'][3]) - 1) > 9 ? 0 : substr($rand_code['code'][3], 0, strlen($rand_code['code'][3]) - 1))) % 10;
			//任意起手牌如果大于等于8 就不补牌
			if($is_chat < 8 && $is_chat1 < 8){
				// 这里是闲补牌
				if ($is_chat < 6) {
					$is_chat_num = $this->rand_code($rand_code['prize_data'], 1);
					$rand_code['code'][] = $is_chat_num['code'][0];
					$rand_code['prize_data'] = $is_chat_num['prize_data'];
				} else {
					$rand_code['code'][] = 0;
				}
				// 这里是庄补牌
				$zhuangbu = 0;
				if ($is_chat1 < 3) {
					$zhuangbu = 1;
				}else if(($is_chat == 6 || $is_chat == 7) && $is_chat1 <= 5){
					$zhuangbu = 1;
				}else {
					preg_match_all("/\d+/s", $rand_code['code'][4], $num);
					if ($is_chat1 == 3) {
						if ($num[0][0] != 8) {
							$zhuangbu = 1;
						}
					} else if ($is_chat1 == 4) {
						if (!in_array($num[0][0], [0, 1, 8, 9])) {
							$zhuangbu = 1;
						}
					} else if ($is_chat1 == 5) {
						if (!in_array($num[0][0], [0, 1, 2, 3, 8, 9])) {
							$zhuangbu = 1;
						}
					} else if ($is_chat1 == 6) {
						if (!in_array($num[0][0], [0, 1, 2, 3, 4, 5, 8, 9])) {
							$zhuangbu = 1;
						}
					}
				}

				if ($zhuangbu == 1) {
					$is_chat_num = $this->rand_code($rand_code['prize_data'], 1);
					$rand_code['code'][] = $is_chat_num['code'][0];
					$rand_code['prize_data'] = $is_chat_num['prize_data'];
				} else {
					$rand_code['code'][] = 0;
				}
			}else{
				$rand_code['code'][] = 0;
				$rand_code['code'][] = 0;
			}


		}
		// print_r($is_chat1);die;
		return [
			'prize' => $rand_code['prize_data']['prize'],
			'code' => $rand_code['code'],
			'prent_code' => $rand_code['prize_data']['prent_code'],
			'num' => $rand_code['prize_data']['num'] - ($type == 1 ? 1 : 0),
			'count' => $rand_code['prize_data']['count'] + 1,
		];
	}
	//控制开奖
	public function control($type = '',$exp = '') {
		if (!isset($type)) {
			$type = $this->post_data['type'];
		}
		//查询当期
		if(empty($exp)){
			$exp = $this->expectLHD()['IssueNo']; //['IssueNo'=>201807252079];//$this->expectLHD();
		}
		// 查询并返回应该开的类型
		$code_t = $this->controlBet($type, $exp);
		if ($code_t['code'] == -1) {
			return $this->open(); //表示本期并没有投注 启用随机开奖即可
		}

		//获取上一期剩余牌
		$data = Db::table('lottery_code')->field('content')->where(['type' => $type])->order('expect','DESC')->find()['content'];
		if (!empty($data)) {
			$data = json_decode($data, true);
		}

		//如果没有牌或者牌不够时 洗牌
		if (empty($data) || $data['num'] <= ($type == 1 ? 3 : 6)) {
			$data = $this->prizeInfo();
		}

		if($type == 1){
			$code = $this->controlOpenLhd($code_t['data'],$data);
		}else{
			$code = $this->controlOpenBjl($code_t['data'],$data);
		}
		if ($code['code'] == -1) {
			return $this->open(); //表示本期没有成功获取控制开奖 启用随机开奖即可
		}
		return $code['data'];
	}
	//获取对应花色 数字 的所有组合 牌$data 获取的数字$num
	public function allGroup($data, $num, $flower=['C','H','D','S']){
		$all_num = [];
		foreach ($data['prize'] as $key => $value) {
			foreach ($value as $k => $v) {
				if (in_array($k, $flower)) {
					//花色是否匹配
					if (in_array($num, $v)) {
						//花色匹配情况下 是否有需要的数字
						$all_num[] = [$key, $k, array_search($num, $v), $num];
					}
				}
			}
		}
		return $all_num;
	}
	//筛选在牌中所能取到对应花色的所有数字 牌$arr  $flower花色 $need_odd单双值 num 0双  1 单  2 全部
	public function allPrize($arr, $flower, $need_odd = 2) {
		$re = [];
		foreach ($arr as $key => $value) {
			foreach ($value as $k => $v) {
				if (in_array($k, $flower)) {
					//需要的
					$re = array_merge($re, $v); //并集
					$re = array_unique($re); //去重复
					if (count($re) == 13) {
						break 2;
					}
				}
			}

		}
		if ($need_odd != 2) {
			$re = array_filter($re, function ($item) use ($need_odd) {
				return $need_odd == 1 ? ($item & 1) : (!($item & 1));
			});
		}
		sort($re); //重新排序
		// print_r($re);die;
		return $re;
	}
	//删除获取的牌组$data牌 $val arr 0牌的key 1花色 2数值key
	public function controlDeletePoker($data, $val) {
		// 删除开奖结果;
		array_splice($data['prize'][$val[0]][$val[1]], $val[2], 1);
		// 如果这副牌下的花色没有数字了,则删除这副牌的这个花色
		if (count($data['prize'][$val[0]][$val[1]]) < 1) {
			unset($data['prize'][$val[0]][$val[1]]);
		}
		// 如果这副牌下的花色没有了,则删除这副牌
		if (count($data['prize'][$val[0]]) < 1) {
			array_splice($data['prize'][$val[0]], [$val[1]], 1);
		}
		return $data;
	}
	//给与开奖内容 进行开奖(龙虎)
	public function controlOpenLhd($val,$data) {

		//查询上一期 开奖内容
		$return_data = [
			'code' => 1,
			'data' => [],
		];
		//获取随机哪一副牌
		// $poknum = array_keys($data['prize'])[array_rand(array_keys($data['prize']))];
		// print_r($data['prize'][$poknum]);

		//龙花色
		if ($val[2] === 'lhei') {
			$lhs_list = ['C', 'S'];
		} else if ($val[2] === 'lhong') {
			$lhs_list = ['H', 'D'];
		} else {
			$lhs_list = ['C', 'S', 'H', 'D'];
		}
		//虎花色
		if ($val[4] === 'hhei') {
			$hhs_list = ['C', 'S'];
		} else if ($val[4] === 'hhong') {
			$hhs_list = ['H', 'D'];
		} else {
			$hhs_list = ['C', 'S', 'H', 'D'];
		}

		//获取虎 指定花色的 指定单双的 所有值
		$h_arr = $this->allPrize($data['prize'], $hhs_list, ($val[3] == 'hd' ? 1 : ($val[1] == 'hs' ? 0 : 2)));
		//获取龙 指定花色的 指定单双的 所有值
		$l_arr = $this->allPrize($data['prize'], $lhs_list, ($val[1] == 'ld' ? 1 : ($val[1] == 'ls' ? 0 : 2)));

		if (count($l_arr) == 0 || count($h_arr) == 0) {
			$return_data['code'] = -1;
			return $return_data['code'];
		}

		//取出 数字的存放
		$lsz = 0;
		$hsz = 0;
		// print_r($val[0]);
		if ($val[0] === 'l') {
			//设定开龙的情况下 龙数一定比虎的第0个要大否则无法开出
			if (end($l_arr) > $h_arr[0]) {
				//获取龙
				$c = $h_arr[0];
				$bit = array_filter($l_arr, function ($a) use ($c) {return $a > $c;});
				$lsz = $bit[array_rand($bit)];
				//获取虎
				$c = $lsz;
				$bit = array_filter($h_arr, function ($a) use ($c) {return $a < $c;});
				$hsz = $bit[array_rand($bit)];

			} else {
				$lsz = $l_arr[array_rand($l_arr)];
				$hsz = $h_arr[array_rand($h_arr)];
			}
		} else if ($val[0] === 'h') {
			if ($l_arr[0] < end($h_arr)) {
				//获取龙
				$c = end($h_arr);
				$bit = array_filter($l_arr, function ($a) use ($c) {return $a < $c;});
				$lsz = $bit[array_rand($bit)];
				//获取虎
				$c = $lsz;
				$bit = array_filter($h_arr, function ($a) use ($c) {return $a > $c;});
				$hsz = $bit[array_rand($bit)];
			} else {
				$lsz = $l_arr[array_rand($l_arr)];
				$hsz = $h_arr[array_rand($h_arr)];
			}
		} else if ($val[0] === 'he') {
			//请求交集
			//print_r($l_arr); print_r($h_arr);
			$bit = array_intersect($l_arr, $h_arr);
			if(!empty($bit)){
				$lsz = $bit[array_rand($bit)];
				$hsz = $lsz;
			}else{
				$lsz = $l_arr[array_rand($l_arr)];
				$hsz = $lsz;
			}
			//
			// print_r($lsz.'--'.$hsz);
			// $bit = array_intersect($l_arr,$h_arr);

		} else {
			$lsz = $l_arr[array_rand($l_arr)];
			$hsz = $h_arr[array_rand($h_arr)];
		}
		// print_r($lsz);echo '--';print_r($hsz);
		// if($lsz == 0 || $hsz == 0){
		//   $return_data['code'] = -1;
		//   return $return_data['code'];
		// }
		//找到所有符合 与数字 和  花色匹配的牌 的龙 存入结果$long_all_num
		$long_all_num = [];
		foreach ($data['prize'] as $key => $value) {
			foreach ($value as $k => $v) {
				if (in_array($k, $lhs_list)) {
					//花色是否匹配
					if (in_array($lsz, $v)) {
						//花色匹配情况下 是否有需要的数字
						$long_all_num[] = [$key, $k, array_search($lsz, $v), $lsz];
					}
				}
			}
		}
		//随机选取一组 并 存入最终龙结果; 0牌的key 1花色 2数值key 3数值
		$long = $long_all_num[array_rand($long_all_num)];
		$data = $this->controlDeletePoker($data, $long);

		//随机组合
		$hu_all_num = [];
		foreach ($data['prize'] as $key => $value) {
			foreach ($value as $k => $v) {
				if (in_array($k, $hhs_list)) {
					//花色是否匹配
					if (in_array($hsz, $v)) {
						//花色匹配情况下 是否有需要的数字
						$hu_all_num[] = [$key, $k, array_search($hsz, $v), $hsz];
					}
				}
			}
		}

		if (count($hu_all_num) < 1) {
			$return_data['code'] = -1;
			return $return_data['code'];
		}
		$hu = $hu_all_num[array_rand($hu_all_num)];
		$data = $this->controlDeletePoker($data, $hu);
		$return_data['data'] = [
			'prize' => $data['prize'],
			'code' => [($long[3] . $long[1]), ($hu[3] . $hu[1])],
			'prent_code' => '',
			'num' => $data['num'] - 3,
			'count' => $data['count'] + 1,
		];
		// print_r($hu_all_num);
		// print_r($long);
		// print_r($hu);
		// print_r($val);
		// print_r('本期开奖指示');
		// print_r($val);
		// print_r('开奖结果');
		// print_r($return_data['data']['code']);

		return $return_data;
	}
	//给与开奖内容 进行开奖(百家乐)
	public function controlOpenBjl($val,$data){

		//查询上一期 开奖内容
		$return_data = [
			'code' => 1,
			'data' => [],
		];

		$open = [];
		if($val[0] == '0'){
			for($i=0;$i<4;$i++){
				if($i == 3 && $val[1] === 'he_off'){
					$xian_qs = (($open[0][3] >= 10 ? 0:$open[0][3]) + ($open[2][3] >= 10 ? 0:$open[2][3]))%10;
					$tem_arr = $this->allPrize($data['prize'],['C','H','D','S'],2);
					$c = [$xian_qs,($open[1][3] >= 10 ? 0 : $open[1][3]) ];
					$bit = array_filter($tem_arr, function ($a) use ($c) {return ((($a>=10?0:$a)+$c[1])%10 != $c[0]) ;});
					$tem_num = $bit[array_rand($bit)];
					$tem_arr1 = $this->allGroup($data,$tem_num);
					$open[$i] = $tem_arr1[array_rand($tem_arr1)];
					$data = $this->controlDeletePoker($data, $open[$i]);
				}else{
					$tem_arr = $this->allPrize($data['prize'],['C','H','D','S'],2);
					$tem_num = $tem_arr[array_rand($tem_arr)];
					$tem_arr1 = $this->allGroup($data,$tem_num);
					$open[$i] = $tem_arr1[array_rand($tem_arr1)];
					$data = $this->controlDeletePoker($data, $open[$i]);
				}
			}
			if(!($val[1] === 'he_off')){
				// 获取闲起手点数
				$xian_qs = (($open[0][3] >= 10 ? 0:$open[0][3]) + ($open[2][3] >= 10 ? 0:$open[2][3]))%10;
			}
				//庄起手点数
				$zhuang_qs = (($open[1][3] >= 10 ? 0:$open[1][3]) + ($open[3][3] >= 10 ? 0:$open[3][3]))%10;
		}else{
			//获取闲第一张牌
			$tem_arr = $this->allPrize($data['prize'],['C','H','D','S'],2);
			$x_num = $tem_arr[array_rand($tem_arr)];
			$x_num1 = $this->allGroup($data,$x_num);
			$open[0] = $x_num1[array_rand($x_num1)];
			$data = $this->controlDeletePoker($data, $open[0]);
			//获取庄第一张牌
			$tem_arr = $this->allPrize($data['prize'],['C','H','D','S'],2);
			$x_num = $tem_arr[array_rand($tem_arr)];
			$x_num1 = $this->allGroup($data,$x_num);
			$open[1] = $x_num1[array_rand($x_num1)];
			$data = $this->controlDeletePoker($data, $open[1]);

			// 获取第二张闲
			$tem_arr = $this->allPrize($data['prize'],['C','H','D','S'],2);
			$c = ($open[0][3] >= 10?0 :$open[0][3]);
			if($val[0] === 'xian'){
					$bit = array_filter($tem_arr, function ($a) use ($c) {return (($a>=10?0:$a)+$c)%10 > 0;});
			}else{
					$bit = array_filter($tem_arr, function ($a) use ($c) {return (($a>=10?0:$a)+$c)%10 < 9;});
			}
			$x_num = $bit[array_rand($bit)];
			$x_num1 = $this->allGroup($data,$x_num);
			$open[2] = $x_num1[array_rand($x_num1)];
			$data = $this->controlDeletePoker($data, $open[2]);
			// 获取闲起手点数
			$xian_qs = (($open[0][3] >= 10 ? 0:$open[0][3]) + ($open[2][3] >= 10 ? 0:$open[2][3]))%10;

			// 获取第二张庄
			$tem_arr = $this->allPrize($data['prize'],['C','H','D','S'],2);
			$c = [$xian_qs,($open[1][3] >= 10 ? 0 : $open[1][3]) ];
			if($val[0] === 'xian'){
				// if($val[2] >= 'da'){
				//
				// }
				$bit = array_filter($tem_arr, function ($a) use ($c) {return ((($a>=10?0:$a)+$c[1])%10 < $c[0]) ;});
			}else{
				$bit = array_filter($tem_arr, function ($a) use ($c) {return ((($a>=10?0:$a)+$c[1])%10 > $c[0]) ;});
			}
			if(count($bit) == 0){
				$return_data['code'] = -1;
				return $return_data;
			}
			$x_num = $bit[array_rand($bit)];
			$x_num1 = $this->allGroup($data,$x_num);
			$open[3] = $x_num1[array_rand($x_num1)];
			$data = $this->controlDeletePoker($data, $open[3]);
			//庄起手点数
			$zhuang_qs = (($open[1][3] >= 10 ? 0:$open[1][3]) + ($open[3][3] >= 10 ? 0:$open[3][3]))%10;
		}

		// //获取第二张庄
		// if($val[1] === 'he_off' && $val[0] != '0'){
		//
		// }
		// print_r($xian_qs);
		// print_r('--');
		// print_r($zhuang_qs);

		//双方棋手起手牌都小于8的情况下 判断补牌
		if($zhuang_qs < 8 && $xian_qs < 8){
			//闲补牌设置
			$xian_b = '';
			if($xian_qs <= 5){
				$tem_arr = $this->allPrize($data['prize'],['C','H','D','S'],2);
				if($val[0] === 'xian'){
					$c = [$xian_qs,0];
					$bit = array_filter($tem_arr, function ($a) use ($c) {return (($a>=10?0:$a)+$c[0])%10 > $c[1];});
				}else if(($val[0] === 'zhuang')){
					$c = [$xian_qs,9];
					$bit = array_filter($tem_arr, function ($a) use ($c) {return (($a>=10?0:$a)+$c[0])%10 < $c[1];});
				}else if($val[1] === 'he_off'){
					if(($zhuang_qs < 3) || ($xian_qs == 6 || $xian_qs == 7) && ($zhuang_qs <=5)){
						$bit = $tem_arr;
					}else{
						$c = [$xian_qs,$zhuang_qs];
						$bit = array_filter($tem_arr, function ($a) use ($c) {return (($a>=10?0:$a)+$c[0])%10 != $c[1];});
					}
				}else{
					$bit = $tem_arr;
				}
				$xian_b = $bit[array_rand($bit)];
				$x_num1 = $this->allGroup($data,$xian_b);
				$open[4] = $x_num1[array_rand($x_num1)];
				$data = $this->controlDeletePoker($data, $open[4]);
			}

			//庄补牌判断
			$zbp = 0;
			if($zhuang_qs < 3){
				$zbp = 1;
			}if(isset($xian_b)){
				$xbp = ($xian_b >=10 ? 0 : $xian_b);
				if($zhuang_qs == 3 && ($xbp != 8 ) ){
					$zbp = 1;
				}else if($zhuang_qs == 4 && in_array($xbp,[2,3,4,5,6,7]) ){
					$zbp = 1;
				}else if( $zhuang_qs == 5 && in_array($xbp,[4,5,6,7]) ){
					$zbp = 1;
				}else if( $zhuang_qs == 6 && in_array($xbp,[6,7]) ){
					$zbp = 1;
				}
			}

			//庄补牌
			if($zbp == 1){
				$tem_arr = $this->allPrize($data['prize'],['C','H','D','S'],2);
				$x_zuizhong = ($xian_qs + (isset($open[4]) ? ($open[4][3] >=10?0:$open[4][3]) : 0))%10;
				$c = [$zhuang_qs,$x_zuizhong];
				if($val[0] === 'xian'){
					$bit = array_filter($tem_arr, function ($a) use ($c) {return (($a>=10?0:$a)+$c[0])%10 < $c[1];});
				}else if($val[0] === 'zhuang'){
					$bit = array_filter($tem_arr, function ($a) use ($c) {return (($a>=10?0:$a)+$c[0])%10 > $c[1];});
				}else if($val[1] === 'he_off'){
					$bit = array_filter($tem_arr, function ($a) use ($c) {return (($a>=10?0:$a)+$c[0])%10 != $c[1];});
				}else{
					$bit = $tem_arr;
				}
				if(count($bit) == 0){
					$return_data['code'] = -1;
					return $return_data;
				}
				$x_num = $bit[array_rand($bit)];
				$x_num1 = $this->allGroup($data,$x_num);
				$open[5] = $x_num1[array_rand($x_num1)];
				$data = $this->controlDeletePoker($data, $open[5]);
			}
		}

		for($i=0;$i<6;$i++){
			$re_bjl[$i] = isset($open[$i])? ($open[$i][3].$open[$i][1]) : 0;
		}

		$return_data['data'] = [
			'prize' => $data['prize'],
			'code' => $re_bjl, //implode(',',$re_bjl),
			'prent_code' => '',
			'num' => $data['num'] - (count($open)),
			'count' => $data['count'] + 1,
		];
		// print_r('进派奖');
		return $return_data;
	}
	//本期投注查询方法 并返回应该开的类型
	public function controlBet($type = '', $expect = '') {
		//查询出开奖的本期投注内容 where('other', '=', $this->post_data['Desk'])->
		$rs = Db::table('betting')->where('type', '=', $type)->where('expect', '=', $expect)->select();
		// print_r($this->post_data['Desk']);
		// print_r('--');
		// print_r($expect);
		// print_r('--');
		// print_r($type);
		// print_r($rs);
		// 查询差值配置
		// print_r(233);die;
		$return_data = [
			'code' => 1,
			'data' => []
		];

		//如果为空则返回
		if(empty($rs)){

		  $return_data['code'] = -1;
		  return $return_data;
		}

		// print_r(233);
		//查询出当前赔率以及配置
		$lo_con = Db::table('lottery_config')->field('basic_config,return')->where('type', '=', $type)->find();
		$con = json_decode($lo_con['return'], true)['kongzhi']['val'];
		$odds = json_decode($lo_con['basic_config'], true)['odds'];

		if(json_decode($lo_con['return'], true)['kongzhi']['switch'] == 0){
			$return_data['code'] = -1;
			return $return_data;
		}
		//百家乐设置-------------------------------------------
		if ($type == 0) {

			//派奖
			$big = [
				'xian' => 0,
				'zhuang' => 0,
				'xd' => 0,
				'wmdz' => 0,
				'rydz' => 0,
				'zd' => 0,
				'xiao' => 0,
				'he' => 0,
				'da' => 0,
			];
			//本金
			$big1 = [
				'xian' => 0,
				'zhuang' => 0,
				'xd' => 0,
				'wmdz' => 0,
				'rydz' => 0,
				'zd' => 0,
				'xiao' => 0,
				'he' => 0,
				'da' => 0,
			];
			//算出本期全部投注
			foreach ($rs as $key => $value) {
				$value['content'] = json_decode($value['content'], true);
				foreach ($value['content'] as $k => $v) {
					$big[$k] += $v;
					$big1[$k] += $v;
				}
			}
			//中奖后金额总发放
			foreach ($big as $key => &$value) {
				$value = ($odds[$key]['num'][$this->post_data['Desk']] * $value);
			}
			 // print_r($con);
			//开奖内容0庄闲 和 1大小 对子待用后面待用
			// if (($big['xian'] == $big['zhuang'] && $big['zhuang'] == $big['he']) || ( (abs($big['xian'] - $big['zhuang']) < $con) && (abs($big['xian'] - $big['he']) < $con) && (abs($big['zhuang'] - $big['he']) < $con) )  ) {
			// 	$return_data['data'][0] = 0;
			// } else if ($big['xian'] <= $big['zhuang'] && $big['xian'] <= $big['he']) {
			// 	if (($big['xian'] == $big['zhuang']) || (abs($big['xian'] - $big['zhuang']) < $con) ) {
			// 		$return_data['data'][0] =  ['xian', 'zhuang'][array_rand(['xian', 'zhuang'])];
			// 	} else if ($big['xian'] == $big['he'] || (abs($big['xian'] - $big['he']) < $con) ) {
			// 		$return_data['data'][0] =  ['xian', 'he'][array_rand(['xian', 'he'])];
			// 	} else {
			// 		$return_data['data'][0] = 'xian';
			// 	}
			// } else if ($big['zhuang'] < $big['xian'] && $big['zhuang'] <= $big['he']) {
			// 	if ($big['zhuang'] == $big['he'] || (abs($big['zhuang'] - $big['he']) < $con) ) {
			// 		$return_data['data'][0] = ['zhuang', 'he'][array_rand(['zhuang', 'he'])];
			// 	} else {
			// 		$return_data['data'][0] = 'zhuang';
			// 	}
			// } else {
			// 	$return_data['data'][0] = 'he';
			// }


			if( ($big['xian'] == $big['zhuang']) || (abs($big['xian'] - $big['zhuang']) < $con) ){
				$return_data['data'][0] = 0;
			}else if($big['xian'] > $big['zhuang']) {
				$return_data['data'][0] = 'zhuang';
			}else {
				$return_data['data'][0] = 'xian';
			}
			//如果和大于0
			$return_data['data'][1] = 0;
			if($return_data['data'][0] === 0 && $big['he'] > 0){
				//如果开和 加上退还的本金 大于了庄和闲 设置的差值 则不准开和
				if( ($big['he'] + $big1['zhuang'] + $big1['xian']) >= $con ){
					$return_data['data'][1] = 'he_off';
				}
			}
			// print_r($return_data['data']);
			// if ( ($big['da'] == $big['xiao']) || abs($big['da'] - $big['xiao']) < $con )   {
			// 	$return_data['data'][2] = 0;
			// } else if ($big['da'] > $big['xiao']) {
			// 	$return_data['data'][2] = 'xiao';
			// } else {
			// 	$return_data['data'][2] = 'da';
			// }
		}
		//龙虎斗设置-----------------------------------------------------------
		if ($type == 1) {
			//查询出当前所有赔率
			$big = [
				'l' => 0,
				'h' => 0,
				'ld' => 0,
				'ls' => 0,
				'lhei' => 0,
				'lhong' => 0,
				'hs' => 0,
				'hd' => 0,
				'hhei' => 0,
				'hhong' => 0,
				'he' => 0,
			];
			//算出本期全部投注
			foreach ($rs as $key => $value) {
				$value['content'] = json_decode($value['content'], true);
				foreach ($value['content'] as $k => $v) {
					$big[$k] += $v;
				}
			}
			//中奖后金额总发放
			foreach ($big as $key => &$value) {
				$value = ($odds[$key]['num'][$this->post_data['Desk']] * $value);
			}

			//判断开龙虎和
			if ($big['l'] == $big['h'] && $big['h'] == $big['he']  || ( (abs($big['l'] - $big['h']) < $con) && (abs($big['l'] - $big['he']) < $con) && (abs($big['h'] - $big['he']) < $con) ) ) {
				$return_data['data'][0] = 0;
			} else if ($big['l'] <= $big['h'] && $big['l'] <= $big['he']) {
				if ($big['l'] == $big['h'] || (abs($big['l'] - $big['h']) < $con)) {
					$return_data['data'][0] = ['l', 'h'][array_rand(['l', 'h'])];
				} else if ($big['l'] == $big['he'] || (abs($big['l'] - $big['he']) < $con)) {
					if(mt_rand(0,100) <= 5){
						$return_data['data'][0] = 'he';
					}else{
						$return_data['data'][0] = 'l';
					}
				} else {
					$return_data['data'][0] = 'l';
				}
			} else if ($big['h'] < $big['l'] && $big['h'] <= $big['he']) {
				if ($big['h'] == $big['he'] || (abs($big['h'] - $big['he']) < $con)) {
					if(mt_rand(0,100) < 5){
						$return_data['data'][0] = 'he';
					}else{
						$return_data['data'][0] = 'h';
					}
				} else {
					$return_data['data'][0] = 'h';
				}
			} else {
				$return_data['data'][0] = 'he';//['h', 'he'][array_rand(['h', 'he'])];
			}
			//判断开龙单双 和 龙黑红
			if ($big['ld'] == $big['ls'] || (abs($big['ld'] - $big['ls']) < $con)) {
				$return_data['data'][1] = 0;
			} else if ($big['ld'] < $big['ls']) {
				$return_data['data'][1] = 'ld';
			} else {
				$return_data['data'][1] = 'ls';
			}
			if ($big['lhei'] == $big['lhong'] || (abs($big['lhei'] - $big['lhong']) < $con)) {
				$return_data['data'][2] = 0;
			} else if ($big['lhei'] < $big['lhong']) {
				$return_data['data'][2] = 'lhei';
			} else {
				$return_data['data'][2] = 'lhong';
			}
			//判断开虎双单 和 虎红黑
			if ($big['hd'] == $big['hs'] || (abs($big['hd'] - $big['hs']) < $con)) {
				$return_data['data'][3] = 0;
			} else if ($big['hd'] < $big['hs']) {
				$return_data['data'][3] = 'hd';
			} else {
				$return_data['data'][3] = 'hs';
			}
			if ($big['hhei'] == $big['hhong'] || (abs($big['hhei'] - $big['hhong']) < $con)) {
				$return_data['data'][4] = 0;
			} else if ($big['hhei'] < $big['hhong']) {
				$return_data['data'][4] = 'hhei';
			} else {
				$return_data['data'][4] = 'hhong';
			}

		}
		if(count(array_unique($return_data['data'])) == 1 ){
			$return_data['code'] = -1;
		}
		return $return_data;
	}
	//获取开奖号码
	public function rand_code($prize_data, $num) {
		$code = [];
		$prize_data['num'] -= $num;
		for ($i = 0; $i < $num; $i++) {
			$color_type = [];
			if (count($prize_data['prize']) == 0) {
				$prize_data = $this->prizeInfo();
			}
			// 随机那副牌
			$rand_num1 = mt_rand(0, count($prize_data['prize']) - 1);
			// 删除这副牌不存在的花色(随机花色时用到)
			foreach (['D', 'H', 'S', 'C'] as $key => $value) {
				if (isset($prize_data['prize'][$rand_num1][$value])) {
					$color_type[] = $value;
				}
			}
			// 随机这副牌下的花色
			$rand_num2 = $color_type[mt_rand(0, count($color_type) - 1)];
			// 随机那副牌下面花色的数字
			$rand_num3 = mt_rand(0, count($prize_data['prize'][$rand_num1][$rand_num2]) - 1);
			// 开奖号码
			$code[] = $prize_data['prize'][$rand_num1][$rand_num2][$rand_num3] . $rand_num2;
			// 删除已经开出的那副牌的那个花色下面的数字
			array_splice($prize_data['prize'][$rand_num1][$rand_num2], $rand_num3, 1);
			// 如果这副牌下的花色没有数字了,则删除这副牌的这个花色
			if (count($prize_data['prize'][$rand_num1][$rand_num2]) < 1) {
				unset($prize_data['prize'][$rand_num1][$rand_num2]);
			}
			// 如果这副牌下的花色没有了,则删除这副牌
			if (count($prize_data['prize'][$rand_num1]) < 1) {
				array_splice($prize_data['prize'], $rand_num1, 1);
			}
		}
		return [
			'code' => $code,
			'prize_data' => $prize_data,
		];
	}
	//重置牌
	public function prizeInfo() {
		$prize = [];
		for ($i = 0; $i < 8; $i++) {
			$prize[] = [
				'D' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
				'H' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
				'S' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
				'C' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
			];
		}
		return [
			'prent_code' => '',
			'prize' => $prize,
			'num' => 416,
			'count' => 0,
		];
	}
	//派奖
	public function prize($expect = '', $type = '', $code = '', $basic = '') {
		$return_data = [
			'code' => 0,
			'msg' => '派奖失败',
		];

		if ($type == '') {
			$return_data['msg'] = '请输入要派奖游戏';
			return $return_data;
		}

		if (empty($expect)) {
			$expectHD = $this->expectLHD();
			$expect = $expectHD['IssueNo'];
			if ($expectHD['State'] != 'Close') {
				$return_data['msg'] = $this->lottery_config['name'] . ' ' . $expect . ' 期未到开奖派奖时间';
				return $return_data;
			}
		}

		if (empty($basic)) {
			$basic = Db::table('lottery_config')->field('basic_config,name')->where(['type' => $this->post_data['type']])->find();
			if (empty($basic)) {
				$return_data['msg'] = '找不到这个游戏的配置';
				return $return_data;
			}
			$basic['basic_config'] = json_decode($basic['basic_config'], true);
		}
		if (empty($code)) {
			$code = Db::table('lottery_code')->field('content')->where(['expect' => $expect, 'type' => $this->post_data['type']])->find();
			if (empty($code)) {
				$open_code = $this->control($type,$expect);
				 Db::table('lottery_code')->insert(['expect' => $expect, 'type' => $type, 'content' => json_encode($open_code), 'create_time' => time()]);
				$code = $open_code['code'];
			} else {
				$code = json_decode($code['content'], true)['code'];
			}
		}
		$betting_data = Db::table('betting')->field('id,user_id,content,other')->where(['expect' => $expect, 'type' => $this->post_data['type'], 'state' => 0])->select();
		if (empty($betting_data)) {
			$return_data['msg'] = $basic['name'] . ' ' . $expect . ' 期,没有投注数据或已经全部结算';
			return $return_data;
		}
		$code_type = [];
		$pai_num = 0;
		//var_dump($code);die;
		if ($type == 1) {
			$long = substr($code[0], 0, strlen($code[0]) - 1);
			$hu = substr($code[1], 0, strlen($code[1]) - 1);
			if ($long == $hu) {
				// 大小和
				$code_type['he'] = true;
			} else if ($long > $hu) {
				$code_type['l'] = true;
			} else {
				$code_type['h'] = true;
			}

			if ($long % 2) {
				// 龙单双
				$code_type['ld'] = true;
			} else {
				$code_type['ls'] = true;
			}
			if ($hu % 2) {
				// 虎单双
				$code_type['hd'] = true;
			} else {
				$code_type['hs'] = true;
			}
			// 龙黑红
			if (strstr($code[0], 'S') || strstr($code[0], 'C')) {
				$code_type['lhei'] = true;
			} else {
				$code_type['lhong'] = true;
			}
			// 虎黑红
			if (strstr($code[1], 'S') || strstr($code[1], 'C')) {
				$code_type['hhei'] = true;
			} else {
				$code_type['hhong'] = true;
			}

		} else if ($type == 0) {
			$xian = 0;
			$zhuang = 0;
			foreach ($code as $key => $value) {
				if ($value == 0) {continue;}
				$is_value = substr($value, 0, strlen($value) - 1);
				if ($is_value > 0) {
					$pai_num++;
				}
				if ($is_value > 9) {
					$is_value = 0;
				}
				if ($key % 2) {
					$zhuang += $is_value;
				} else {
					$xian += $is_value;
				}
			}
			$xian = $xian % 10;
			$zhuang = $zhuang % 10;
			if ($xian == $zhuang) {
				$code_type['he'] = true;
			} else {
				if ($xian > $zhuang) {
					$code_type['xian'] = true; // 闲赢
				} else {
					$code_type['zhuang'] = true; // 庄赢
				}
			}
			if (substr($code[0], 0, strlen($code[0]) - 1) == substr($code[2], 0, strlen($code[2]) - 1)) {
				$code_type['xd'] = true; // 闲对
				$code_type['rydz'] = true; // 任意对子
			}
			if (substr($code[1], 0, strlen($code[1]) - 1) == substr($code[3], 0, strlen($code[3]) - 1)) {
				$code_type['zd'] = true; // 庄对
				$code_type['rydz'] = true; // 任意对子
			}
			if ($code[1] == $code[3] || $code[0] == $code[2]) {
				$code_type['wmdz'] = true; // 完美对子
			}
			if ($pai_num > 4) {
				$code_type['da'] = true; // 大
			} else {
				$code_type['xiao'] = true; // 小
			}
		} else {
			$return_data['msg'] = '未知游戏';
			return $return_data;
		}
		// 投注表要更新的数据
		$update_data = [];
		// 用户表要操作的数据
		$user_data = [];
		// 聊天房间播报数据
      	$room_data = [];

		foreach ($betting_data as $key => $value) {
			$win_sum = 0;
			$explain = '';
			$content = json_decode($value['content'], true);
			// 这里是开和 退本金操作
			if(isset($code_type['he'])){
				if($this->post_data['type'] == 0){
					if(isset($content['zhuang'])){
						$win_sum += $content['zhuang'];
						$explain .= $basic['basic_config']['odds']['zhuang']['name'] . '开和退本金:' . $content['zhuang'] . '元;';
					}
					if(isset($content['xian'])){
						$win_sum += $content['xian'];
						$explain .= $basic['basic_config']['odds']['xian']['name'] . '开和退本金:' . $content['xian'] . '元;';
					}
				}else{
					if(isset($content['l'])){
						$win_sum += round(($content['l']/2),2);
						$explain .= $basic['basic_config']['odds']['l']['name'] . '开和退本金:' . round(($content['l']/2),2) . '元;';
					}
					if(isset($content['h'])){
						$win_sum += round(($content['h']/2),2);
						$explain .= $basic['basic_config']['odds']['h']['name'] . '开和退本金:' . round(($content['h']/2),2) . '元;';
					}
				}
			}
			foreach ($content as $key2 => $value2) {
				if (isset($code_type[$key2])) {
					$win_sum += round($value2 * $basic['basic_config']['odds'][$key2]['num'][$value['other']] + $value2, 2);
					$explain .= $basic['basic_config']['odds'][$key2]['name'] . '中奖:' . $win_sum . '元;';
				}
			}
			if ($win_sum) {
	          	$room_data[] = [
		            'user_id' => 0,
		            'content' => '恭喜玩家' . substr(getUserName($value['user_id']),0,3) . '*** 在' . $this->lottery_config['name'] . '游戏 ' . $explain,
		            'create_time' => time()
	          	];
				$user_data[] = [
					'uid' => $value['user_id'],
					'money' => $win_sum,
					'type' => 3,
					'explain' => $basic['name'] . '中奖',
				];
			}
			$update_data[] = [
				'id' => $value['id'],
				'state' => 1,
				'win' => $win_sum,
				'explain' => (empty($explain) ? '已结算' : $explain),
			];
		}
		// 进行所有资金操作
		count($user_data) && moneyAction($user_data);
		// 更新投注表数据状态
		count($update_data) && ((new Betting)->saveAll($update_data, true));
    	// 进行中奖播报
    	count($room_data) && (Db::table('chat_room')->insertAll($room_data));
		$return_data['code'] = 1;
		$return_data['msg'] = $basic['name'] . ' ' . $expect . ' 期已经全部结算';
		print_r(json_encode($return_data));
	}
}
