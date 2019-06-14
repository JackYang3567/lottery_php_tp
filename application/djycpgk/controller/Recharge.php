<?php
namespace app\djycpgk\controller;
use think\Controller;
use think\Db;
use think\facade\Log;
use think\facade\Request;
use app\home\model\ChatRoom;
use app\home\model\LinePayOrder;
class Recharge extends Rbac {
	public function line_pay(){
		if(Request::method() == 'POST'){
			$data = input('post.');
			$where = [];
			if($data['where']['click'] == 1) {
				switch ($data['where']['type']) {
					case 1:
                        $where[] = ['order_id','=',$data['where']['text']];
						break;
					case 2:
                        $where[] = ['user_id','=',$data['where']['text']];
						break;
					default:
						break;
				}
			}
			$order = ['id','DESC'];
			if($data['order']['click'] == 1){
				switch ($data['order']['type']) {
					case 1:
						$order[0] = 'money';
						break;
					case 2:
                        $order[0] = 'create_time';
						break;
					case 3:
                        $order[0] = 'id';
						break;
					default:
						break;
				}
				$order[1] = $data['order']['up'] == 1 ? 'ASC' : 'DESC';
			}
			$rs = LinePayOrder::listData($where,$data['order'])->toArray();
			return $rs;
		}else{
			return $this->fetch();
		}
	}
	public function sx(){ //实时刷新
		$zd_id = DB::table('capital_audit')->field('MAX(id) id')->where('type',0)->find();
		// dump($zd_id);
		$ss= Request::param('id');//获取页面传递过来的最大id

		if ($zd_id['id'] > $ss) {
			return 0;
		}else {
			return 1;
		}
	}
	public function index() {

		$paginate = 15;
        $map['type'] = ['type','in',[0,3]];

		$mop = ['type' => 0];


		$mop['create_time'] = ['create_time', 'between', [strtotime(date("Y-m-d"),time()),strtotime(date("Y-m-d"),time())+24*3600]];
		$pageParam['query'] = [];
		if (Request::param('state') != '') {


			$map['state'] = Request::param('state');
			$mop['state'] = Request::param('state');
			$pageParam['query']['state'] = Request::param('state');
			$this->assign('state', Request::param('state'));
		}

		if (Request::param('keywords') != '') {
			$usernames = DB::table('user')->field('id')->where('username', 'like', "%" . Request::param('keywords') . "%")->select();
			if ($usernames) {
				$user_ids = [];
				foreach ($usernames as $key => $value) {
					$user_ids[] = $value['id'];
				}
				$map['user_id'] = array('user_id', 'in', $user_ids);
				$mop['user_id'] = array('user_id', 'in', $user_ids);
				$pageParam['query']['keywords'] = Request::param('keywords');
				$this->assign('keywords', Request::param('keywords'));
			}
		}

		if (Request::param('start_time') != '' && Request::param('end_time') == '') {
			$map['create_time'] = ['create_time', '>', strtotime(Request::param('start_time'))];
			$mop['create_time'] = ['create_time', '>', strtotime(Request::param('start_time'))];
			$pageParam['query']['start_time'] = Request::param('start_time');
			$this->assign('start_time', Request::param('start_time'));
		}

		if (Request::param('start_time') == '' && Request::param('end_time') != '') {
			$map['create_time'] = ['create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];
			$mop['create_time'] = ['create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];
			$pageParam['query']['end_time'] = Request::param('end_time');
			$this->assign('end_time', Request::param('end_time'));
		}

		if (Request::param('start_time') != '' && Request::param('end_time') != '') {

			$map['create_time'] = ['create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];
			$mop['create_time'] = ['create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];
			$pageParam['query']['start_time'] = Request::param('start_time');
			$this->assign('start_time', Request::param('start_time'));
			$pageParam['query']['end_time'] = Request::param('end_time');
			$this->assign('end_time', Request::param('end_time'));

		}

		$state = Request::param('state');
		$ze=['zq'=>0,'zt'=>0];
		if ($state=='') {
			$ze['zt'] = 3;//为0 页面不显示总金额
		}elseif ($state == 0) {
			$ze['zt'] = 0;
		}elseif ($state == 1) {
			$ze['zt'] = 1;
		}elseif ($state == 2) {
			$ze['zt'] = 2;
		}
		$zone = DB::table('capital_audit')->field('money')->where($mop)->select();
		foreach ($zone as $key => $value) {
			$ze['zq'] += $value['money'];
		}
		$list = DB::table('capital_audit')->where($map)->order('state ASC,id DESC')->paginate($paginate, false, $pageParam)->each(function ($item, $key) {
			$user = DB::table('user')->where('id', $item['user_id'])->find();

            if ($user['type'] == 2){
                $item['user_name'] = $user['username'].'(内部试玩)';
            }else{
                $item['user_name'] =  $user['username'];
            }
			$item['bank_name'] = $item['bank'] != '0' ? bankTool($item['bank']) : '无法查询';
			$item['now_money'] = $user['money'];
			// $item['give_money'] =  $give_money = $this->caculate_give($item['money'],$item['user_id']);
			return $item;
		});
		$zd_id = DB::table('capital_audit')->field('MAX(id) id')->where('type',0)->find();
		 $this->assign('zd_id', $zd_id['id']);
		 $this->assign('ze', $ze);
		$this->assign('list', $list);
		return $this->fetch();
	}

	//充值确认
	public function confirm() {
		//充值数据
		 $recharge_data = DB::table('capital_audit')          //->find(Request::post('data_id'));
									->alias('a')
									->join('user b','a.user_id=b.id')
									->where('a.id','=',Request::post('data_id'))
									->field('a.*,b.username')
									->find();
		if ($recharge_data['state'] == 1) {
			return json(['error' => 1, 'msg' => '操作失败，请勿重复操作']);
		}

        $recharge_give_punch = Db::table('system_config')->where('name','recharge_give_punch')->find()['value'];


		if ($recharge_give_punch==1  ){// $recharge_give_punch是否开启首冲
            $recharge_give_condition = Db::table('system_config')->where('name','recharge_give_condition')->find()['value'];//首冲条件
            switch ($recharge_give_condition){
                case 0:
                    $map = [];
                    break;
                case 1:
                    $day_start =  strtotime(date("Y-m-d 00:00:00")); //当天 0时.0点.0分
                    $map['create_time'] = ['create_time','>',$day_start];
                    break;
                case 2:
                    $week =  strtotime(date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"))));//这周0时.0点.0分；
                    $map['create_time'] = ['create_time','>',$week];
                    break;
                case 3:
                    $month = strtotime(date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y"))));//这月 0时.0点.0分；
                    $map['create_time'] = ['create_time','>',$month];
                    break;
                case 4:
                    $season = ceil((date('n'))/3);//当月是第几季度
                    $quarter = strtotime(date('Y-m-d H:i:s', mktime(0, 0, 0,$season*3-3+1,1,date('Y'))));//这季度 0时.0点.0分；
                    $map['create_time'] = ['create_time','>',$quarter];
                    break;
                default:
                    break;
            }

            $config = DB::table('capital_audit')->where('user_id',$recharge_data['user_id'])->where($map)->where('type',0)->where('state',1)->count();//获取用户所有的充值记录
            if ($config==0){ //$config判断是否 有 充值
                $first_punch = $this->first_punch($recharge_data['money'], $recharge_data['user_id']);
                $config2['uid'] = $recharge_data['user_id'];
                $config2['type'] = 2;
                $config2['money'] = $recharge_data['money'];
                $recharge_rs = moneyAction($config2);
                if ($first_punch != 0){ //判断    首冲赠送不等于 0 ；
                    $give_config['uid'] = $recharge_data['user_id'];
                    $give_config['type'] = 5;
                    $give_config['money'] = $first_punch;
                    $give_rs = moneyAction($give_config);//存入累加表 赠送
                    if ($recharge_rs['code'] == 1 && $give_rs['code'] == 1) {
                        //存入聊天
                        DB::table('capital_audit')->where('id', Request::post('data_id'))->update(['state' => 1, 'remarks' => "首冲充值成功,赠送{$first_punch}"]);
                        $strl = mb_strlen($recharge_data['username']);
                        $chat_name = mb_substr($recharge_data['username'],0,floor($strl/2) ).'**';
                        $chat_room = [
                            'user_id' => 0,
                            'content' => '恭喜玩家'.$chat_name.',首冲成功'.$recharge_data['money'].'元.'.($first_punch > 0 ? '赠送'.$first_punch.'元': ''),
                            'create_time' => time(),
                        ];
                         $ss =  (new ChatRoom)->insert($chat_room);

                        return json(['error' => 0, 'msg' => '充值已确认']);
                    } else {

                        return json(['error' => 1, 'msg' => '操作失败']);

                    }
                }else{
                    if ($recharge_rs['code'] == 1) {
                        DB::table('capital_audit')->where('id', Request::post('data_id'))->update(['state' => 1, 'remarks' => "首冲充值成功,赠送{$first_punch}"]);
                        return json(['error' => 0, 'msg' => '充值已确认']);
                    } else {
                        return json(['error' => 1, 'msg' => '操作失败']);
                    }
                }
            }
        }

        //充值赠送
        $give_money = $this->caculate_give($recharge_data['money'], $recharge_data['user_id']);
        $config2['uid'] = $recharge_data['user_id'];
        $config2['type'] = 2;
        $config2['money'] = $recharge_data['money'];

        $recharge_rs = moneyAction($config2);
        if (0 != $give_money) {
            $give_config['uid'] = $recharge_data['user_id'];
            $give_config['type'] = 5;
            $give_config['money'] = $give_money;
            $give_rs = moneyAction($give_config);

            if ($recharge_rs['code'] == 1 && $give_rs['code'] == 1) {

                DB::table('capital_audit')->where('id', Request::post('data_id'))->update(['state' => 1, 'remarks' => "充值成功,赠送{$give_money}"]);

                // $log = "user_id为{$recharge_data['user_id']}的用户充值{$recharge_data['money']}成功,赠送{$give_money},时间".date('Y-m-d H:i:s',time());
                // Log::write($log,'notice');
                //存入聊天
                $strl = mb_strlen($recharge_data['username']);
                $chat_name = mb_substr($recharge_data['username'],0,floor($strl/2) ).'**';
                $chat_room = [
                    'user_id' => 0,
                    'content' => '恭喜玩家'.$chat_name.',成功充值'.$recharge_data['money'].'元.'.($give_money > 0 ? '赠送'.$give_money.'元': ''),
                    'create_time' => time(),
                ];
                (new ChatRoom)->insert($chat_room);

                return json(['error' => 0, 'msg' => '充值已确认']);
            } else {
                return json(['error' => 1, 'msg' => '操作失败']);
            }
        } else {

            if ($recharge_rs['code'] == 1) {
                DB::table('capital_audit')->where('id', Request::post('data_id'))->update(['state' => 1, 'remarks' => "充值成功,赠送{$give_money}"]);
                // $log = "user_id为{$recharge_data['user_id']}的用户充值{$recharge_data['money']}成功,赠送{$recharge_give},时间".date('Y-m-d H:i:s',time());
                //   Log::write($log,'notice');
                return json(['error' => 0, 'msg' => '充值已确认']);
            } else {
                return json(['error' => 1, 'msg' => '操作失败']);
            }
        }
        



	}
    protected function first_punch($money = '', $user_id = ''){ //首冲赠送
        $sczs = 0;
        //首冲赠送规则
        $give_list = DB::table('recharge_give')->where('type',2)->order('id ASC')->select();
        foreach ($give_list as $key => $value) {
            if ( $money >= $value['begin'] && $money<= $value['end']){
                $sczs = $value['percent'];
                return $sczs;
            }
        }
        return $sczs;
    }
	protected function caculate_give($money = '', $user_id = '') {
		$config = Db::table('system_config')->select();
		$recharge_give_open = $config[10]['value'];
		$recharge_give = 0;
	    if ($recharge_give_open == 0) {
			$recharge_give = 0;
		} else {

			//充值赠送规则
			$give_list = DB::table('recharge_give')->where('type',1)->order('id ASC')->select();
			//查询充值赠送次数
			$give_time = $config[5]['value'];
			//获取系统最高充值
			$highest_give = $config[6]['value'];

			if ($give_time == 0) {

				foreach ($give_list as $key => $value) {
					if ($money >= $give_list[$key]['begin'] && $money <= $give_list[$key]['end']) {

						$recharge_give = round($money * $give_list[$key]['percent'] / 100, 2);

					}

					if ($money > $give_list[count($give_list) - 1]['end']) {
						if ($highest_give == 0) {

							$recharge_give = round($money * $give_list[count($give_list) - 1]['percent'] / 100, 2);
						} else {
							$recharge_give = $highest_give;
						}
					}
				}

			} else {

				$t = time();
				$day_start = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
				$day_end = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));
				$map['user_id'] = $user_id;
				$map['type'] = 0;
				$map['state'] = 0;
				$map['create_time'] = array('create_time', 'between', array($day_start, $day_end));
				$recharge_count = DB::table('capital_audit')->where($map)->count();

				if ($recharge_count >= $give_time) {

					$recharge_give = 0;
				} else {
					foreach ($give_list as $key => $value) {
						if ($money >= $give_list[$key]['begin'] && $money <= $give_list[$key]['end']) {
							$recharge_give = round($money * $give_list[$key]['percent'] / 100, 2);
						}
						if ($money > $give_list[count($give_list) - 1]['end']) {
							if ($highest_give == 0) {
								$recharge_give = round($money * $give_list[count($give_list) - 1]['percent'] / 100, 2);
							} else {
								$recharge_give = $highest_give;
							}
						}
					}
				}
			}
		}

		return $recharge_give;
	}

	public function refuse() {

		$rs = DB::table('capital_audit')->where('id', Request::param('recharge_id'))->update(['state' => 2, 'remarks' => Request::param('remark')]);
		if ($rs) {
			return json(['error' => 0, 'msg' => '操作成功']);
		} else {
			return json(['error' => 1, 'msg' => '操作失败']);
		}
	}

	public function all_confirm() {
		$datas = Request::post('recharge_list/a');
		$success = 0;
		$error = 0;
		$count = count($datas);
		//todo 打款
		foreach ($datas as $key => $value) {
            $check_rs = $this->check($value);
			if ($check_rs['error'] == 0) {
				$success++;
			} else {

				$error++;
			}
		}

		return json(['error' => $error, 'success' => $success, 'total' => $count]);

	}

	public function delete_one() {

		$rs = DB::table('capital_audit')->where('id', Request::param('data_id'))->delete();
		if ($rs) {
			return json(['error' => 0, 'msg' => '操作成功']);
		} else {
			return json(['error' => 1, 'msg' => '操作失败']);
		}
	}

	public function delete_all() {
		$datas = Request::param('recharge_list/a');
		$success = 0;
		$error = 0;
		$count = count($datas);
		foreach ($datas as $key => $value) {
			$rs = Db::table('capital_audit')->where('id', $value)->delete();
			if ($rs) {
				$success++;
			} else {
				$error++;
			}
		}

		return json(['error' => $error, 'success' => $success, 'total' => $count]);

	}

	protected function check($data_id = '') {

		$recharge_data = DB::table('capital_audit')
									->where('state=0')
									->alias('a')
									->join('user b','a.user_id=b.id')
									->where('a.state','=',0)
									->where('a.id','=',$data_id)
									->field('a.*,b.username')
									->find();
		if ($recharge_data == '') {
			return ['error' => 1, 'msg' => '操作失败'];
		}
		$give_money = $this->caculate_give($recharge_data['money'], $recharge_data['user_id']);

		$config['uid'] = $recharge_data['user_id'];
		$config['type'] = 2;
		$config['money'] = $recharge_data['money'];
		$recharge_rs = moneyAction($config);

		if (0 != $give_money) {
			$give_config['uid'] = $recharge_data['user_id'];
			$give_config['type'] = 5;
			$give_config['money'] = $give_money;
			$give_rs = moneyAction($give_config);

			if ($recharge_rs['code'] == 1 && $give_rs['code'] == 1) {

				DB::table('capital_audit')->where('id', $data_id)->update(['state' => 1]);

				$log = "user_id为{$recharge_data['user_id']}的用户充值{$recharge_data['money']}成功,赠送{$give_money},时间" . date('Y-m-d H:i:s', time());
				Log::write($log, 'notice');

				$strl = mb_strlen($recharge_data['username']);
				$chat_name = mb_substr($recharge_data['username'],0,floor($strl/2) ).'**';
				$chat_room = [
					'user_id' => 0,
					'content' => '恭喜玩家'.$chat_name.',成功充值'.$recharge_data['money'].'元.'.($give_money > 0 ? '赠送'.$give_money.'元': ''),
					'create_time' => time(),
				];
				(new ChatRoom)->insert($chat_room);

				return ['error' => 0, 'msg' => '充值已确认'];
			} else {

				return ['error' => 1, 'msg' => '操作失败'];
			}

		} else {
            $recharge_give = time();
			if ($recharge_rs['code'] == 1) {
				DB::table('capital_audit')->where('id', $data_id)->update(['state' => 1]);
				$log = "user_id为{$recharge_data['user_id']}的用户充值{$recharge_data['money']}成功,赠送{$recharge_give},时间" . date('Y-m-d H:i:s', time());
				Log::write($log, 'notice');
				return ['error' => 0, 'msg' => '充值已确认'];
			} else {

				return ['error' => 1, 'msg' => '操作失败'];
			}
		}
	}
}
