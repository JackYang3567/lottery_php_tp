<?php
namespace app\djycpgk\controller;
use think\Controller;
use think\Db;
use think\facade\Request;
use app\home\model\ChatRoom;

class Cash extends Rbac {
	public function sx(){ //实时刷新
		$zd_id = DB::table('capital_audit')->field('MAX(id) id')->where('type',1)->find();
		$ss= Request::param('id');//获取页面传递过来的最大id
		if ($zd_id['id'] > $ss) {
			return 0;
		}else {
			return 1;
		}
	}
	public function index() {
		$paginate = 15;

		$map = ['type' => 1];
		$mop = ['type' => 1];
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
		    $userx =  DB::table('user')->field('username,type')->where('id', $item['user_id'])->find();
		    if ($userx['type'] == 2){
                $item['user_name'] = $userx['username'].'(内部试玩)';
            }else{
                $item['user_name'] =  $userx['username'];
            }
			$user_bank = DB::table('user_bank')->where('user_id', $item['user_id'])->find();
//			dump($user_bank['branch']);
			$item['username_ba'] = $user_bank['username'];
			$item['name_ba'] = $user_bank['name'];
			$item['number_ba'] = $user_bank['number'];
			$item['branch_ba'] = $user_bank['branch'];



			$today = strtotime(date('Y-m-d 00:00:00',$item['create_time']));//获取用户体现当天的 0.00 时间
			$end = $today+60*60*24;//当天的24点时间戳

			$tj2 = [
					['user_id', '=', $item['user_id']],
					['create_time','between',[$today,$end]],

					];
			$item['jrxzls'] = DB::table('betting')->field("SUM(money) money_s")->where($tj2)->find();
			// dump(DB::table('betting')->getlastsql());exit();

			return $item;
		});

		$zd_id = DB::table('capital_audit')->field('MAX(id) id')->where('type',1)->find();
		// dump($zd_id);
		 $this->assign('zd_id', $zd_id['id']);

		 $this->assign('ze', $ze);
		$this->assign('list', $list);
//		dump($list);
		return $this->fetch();
	}
	public function confirm() {
		$cash_data = DB::table('capital_audit')
		->alias('a')
		->join('user b','a.user_id=b.id')
		->where('a.id','=',Request::post('data_id'))
		->field('a.*,b.username')
		->find();

		if ($cash_data['state'] == 1) {
			return json(['error' => 1, 'msg' => '操作失败，请勿重复操作']);
		}

		$cash_config['uid'] = $cash_data['user_id'];
		$cash_config['type'] = 1;
		$cash_config['money'] = $cash_data['money'];
		$cash_rs = moneyAction($cash_config);
//		dump($cash_rs);
		if (1 == $cash_rs['code']) {

			DB::table('capital_audit')->where('id', Request::post('data_id'))->update(['state' => 1]);
			//存入聊天
			$strl = mb_strlen($cash_data['username']);
			$chat_name = mb_substr($cash_data['username'],0,floor($strl/2) ).'**';
			$chat_room = [
				'user_id' => 0,
				'content' => '恭喜玩家'.$chat_name.',成功提现'.$cash_data['money'].'元.',
				'create_time' => time(),
			];
			(new ChatRoom)->insert($chat_room);
			return json(['error' => 0, 'msg' => '提现成功']);
		} else {
			return json(['error' => 1, 'msg' => '提现失败']);
		}
	}

	public function refuse() {
//        dump(Request::param());
		$rs = DB::table('capital_audit')->where('id', Request::param('recharge_id'))->update(['state' => 2, 'remarks' => Request::param('remark')]);
        $frozen_money = Db::table('capital_audit')->where('id', Request::param('recharge_id'))->find()['money'];
        $rs1 = moneyAction([ 'uid'=>Request::param('user_id'),'money'=>$frozen_money,'type'=>13,'explain'=>'提款拒绝退款' ]);
		if ($rs && $rs1['code']) {
			//把冻结资金反到余额里面
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
		$msg = '';
		//todo 打款
		foreach ($datas as $key => $value) {
			$check_rs = $this->check($value);
			if ($check_rs['error'] == 0) {
				$success++;
			} else {
				$msg = $check_rs['msg'];
				$error++;
			}
		}

		return json(['error' => $error, 'success' => $success, 'total' => $count, 'msg' => $msg]);
	}

	public function check($data_id = '') {

		$cash_data = DB::table('capital_audit')
							->alias('a')
							->join('user b','a.user_id=b.id')
							->where('a.state','=',0)
							->where('a.id','=',$data_id)
							->field('a.*,b.username')
							->find();
		if ($cash_data == '') {
			return ['error' => 1, 'msg' => '操作失败'];
		}

		$cash_config['uid'] = $cash_data['user_id'];
		$cash_config['type'] = 1;
		$cash_config['money'] = $cash_data['money'];
		$cash_rs = moneyAction($cash_config);
		// dump($cash_rs);die;
		if (1 == $cash_rs['code']) {
			DB::table('capital_audit')->where('id', $data_id)->update(['state' => 1]);

			$strl = mb_strlen($cash_data['username']);
			$chat_name = mb_substr($cash_data['username'],0,floor($strl/2) ).'**';
			$chat_room = [
				'user_id' => 0,
				'content' => '恭喜玩家'.$chat_name.',成功提现'.$cash_data['money'].'元.',
				'create_time' => time(),
			];
			(new ChatRoom)->insert($chat_room);
			return ['error' => 0, 'msg' => '提现成功'];
		} else {
			return ['error' => 1, 'msg' => $cash_rs['msg']];
		}
	}

}
