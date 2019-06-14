<?php
namespace app\gk\controller;
use think\Db;
use think\facade\Cache;
use think\facade\Request;
use think\facade\Session;

class Index extends Dlauth {

    public function search()
    {
        if (session::get('proxy.type') == 1) {
            $user_num = Db::table('relationship')->field('userid')->where(['top' => session::get('proxy.uid'), 'floor' => 3])->select();
        } else {
            $user_num = Db::table('relationship')->field('userid')->where(['prev' => session::get('proxy.uid'), 'floor' => 3])->select();
        }
        $user_array = ['0'];
        if (count($user_num) != 0) {
            $user_array = [];
            foreach ($user_num as $key => $value) {
                $user_array[] = $value['userid'];
            }
        }
        $ss = [];
        $new_relationship = Db::table('new_relationship')->where('user_id','in',$user_array)->find();//获取 new_relationship 普通会员的 代理关系

        $arr[]  = explode(",",$new_relationship['child_one']);
        $arr[]  = explode(",",$new_relationship['child_two']);
        $arr[]	= explode(",",$new_relationship['child_three']);

        foreach($arr as $key=>$value){

            foreach($value as $v){

                $ss[] = $v;
            }

        }
        foreach($user_array as $key=>$ka){
            $ss[] = $ka;
        };


        $bwk = array_filter($ss); //查询所有二级代理下面的 普通用户 和 普通用户 下面的 二级；
        $map['user_id'] = ['user_id', 'in', $bwk];
        if (Request::param('start_time') != '' && Request::param('end_time') == '')
        {
            $map['create_time'] = ['create_time', '>', strtotime(Request::param('start_time'))];

        }

        if (Request::param('start_time') == '' && Request::param('end_time') != '')
        {
            $map['create_time'] = ['create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];

        }

        if (Request::param('start_time') != '' && Request::param('end_time') != '')
        {

            $map['create_time'] = ['create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];

        }

//        $user_array = [];
//        $user_ids = Db::table('user')->field('id')->where('type', 0)->select();
//        foreach ($user_ids as $key => $value)
//        {
//            $user_array[] = $value['id'];
//        }
//        $map['user_id'] = ['user_id', 'in', $user_array];

        //区间的投注记录
        $betting_records = DB::table('capital_detail')->where($map)->where('type', 0)->select();
        //  dump(DB::table('capital_detail')->getlastsql());die;
        if (count($betting_records) == 0)
        {
            $betting_count = 0;
            $betting_money = 0;
        }
        else
        {
            $betting_count = count($betting_records);
            $betting_money = number_format(array_sum(array_map(function ($val)
            {
                return abs($val['money']);
            }, $betting_records)), 2, '.', ',');
        }

        //派奖
        $pj_records = DB::table('capital_detail')->where($map)->where('type', 3)->select();
        //dump(DB::table('capital_detail')->getlastsql());die;
        if (count($pj_records) == 0)
        {
            $pj_count = 0;
            $pj_money = 0;
        }
        else
        {
            $pj_count = count($pj_records);
            $pj_money = number_format(array_sum(array_map(function ($val)
            {
                return abs($val['money']);
            }, $pj_records)), 2, '.', ',');
        }
	

        //反水
        $fs_records = DB::table('capital_detail')->where($map)->where('type', 11)->select();

        if (count($fs_records) == 0)
        {
            $fs_count = 0;
            $fs_money = 0;
        }
        else
        {
            $fs_count = count($fs_records);
            $fs_money = number_format(array_sum(array_map(function ($val)
            {
                return $val['money'];
            }, $fs_records)), 2, '.', ',');
        }

        //返佣

        $commission_records = DB::table('capital_detail')->where($map)->where('type', 8)->select();

        if (count($commission_records) == 0)
        {
            $commission_count = 0;
            $comission_money = 0;
        }
        else
        {
            $commission_count = count($commission_records);
            $comission_money = number_format(array_sum(array_map(function ($val)
            {
                return $val['money'];
            }, $commission_records)), 0, '.', ',');
        }

        //充值
        $cz_records = DB::table('capital_detail')->where($map)
            ->where(['type' => ['type', 'in', [2, 7,15]]])->select();
        if (count($cz_records) == 0)
        {
            $cz_count = 0;
            $cz_money = 0;
        }
        else
        {
            $cz_count = count($cz_records);
            $cz_money = number_format(array_sum(array_map(function ($val)
            {
                return abs($val['money']);
            }, $cz_records)), 2, '.', ',');
        }
        //提现
        $tx_records = DB::table('capital_detail')->where($map)->where('type', 1)->select();
        if (count($tx_records) == 0)
        {
            $tx_count = 0;
            $tx_money = 0;
        }
        else
        {
            $tx_count = count($tx_records);
            $tx_money = number_format(array_sum(array_map(function ($val)
            {
                return abs($val['money']);
            }, $tx_records)), 2, '.', ',');
        }
        //赠金
        $give_records = DB::table('capital_detail')->where($map)->where('type', 5)->select();
        if (count($give_records) == 0)
        {
            $give_count = 0;
            $give_money = 0;
        }
        else
        {
            $give_count = count($give_records);
            $give_money = number_format(array_sum(array_map(function ($val)
            {
                return $val['money'];
            }, $give_records)), 2, '.', ',');
        }
        $xz = 0;
        foreach ($betting_records as  $k =>$v){

            $xz +=abs($v['money']);
        }
        $pj = 0;
        foreach ($pj_records as  $k =>$v){

            $pj +=abs($v['money']);
        }

        $yl = round($xz,2) -round($pj,2);
		
		//dump($yl);
        $this->assign('yl', $yl);
        $this->assign('betting_count', $betting_count);
        $this->assign('betting_money', $betting_money);
        //$this->assign('cd_count',$cd_count);
        // $this->assign('cd_money',$cd_money);
        $this->assign('commission_count', $commission_count);
        $this->assign('comission_money', $comission_money);
        $this->assign('pj_count', $pj_count);
        $this->assign('pj_money', $pj_money);
        $this->assign('cz_count', $cz_count);
        $this->assign('cz_money', $cz_money);
        $this->assign('tx_count', $tx_count);
        $this->assign('tx_money', $tx_money);
        // $this->assign('winning_records',$winning_records);
        $this->assign('fs_count', $fs_count);
        $this->assign('fs_money', $fs_money);
        $this->assign('give_count', $give_count);
        $this->assign('give_money', $give_money);

        $this->assign('start_time', Request::param('start_time'));
        $this->assign('end_time', Request::param('end_time'));
        return $this->fetch();
    }
	public function index() {

		//dump(intval((0.7 + 0.1) * 100 / 10));die;
		//dump(session::get());die;
		$t = time();
		// mktime 获取今日的起始时间戳和结束时间戳的方法
		$day_start = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
		$day_end = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));

		//判断数据库里面的时间是否小于当前时间
		$map['create_time'] = ['create_time', '<', time()];
		//$map['create_time'] = ['create_time','between',[$day_start,$day_end]];
		$chat = Db::table('chat_room')->where($map)->order('create_time desc')->limit(20)->select();
		//以相反的元素顺序返回数组
		$chat = array_reverse($chat);
		foreach ($chat as $key => $value) {
			if ($chat[$key]['user_id'] == 0) {
//当id = 0的时候向数组里面添加 $chat[$key]['username'] = '管理员'
				$chat[$key]['username'] = '管理员';
			} else {
//不等于0的时候 将 $chat[$key]['username'] = user 数据库里面的 username 值
				$chat[$key]['username'] = Db::table('user')->where('id', $chat[$key]['user_id'])->find()['username'];
			}
			//注意：chat_room 表 里面没有username 这只是在数组里面添加的一个
		}
		//连接capital_audit 表 查询 state = 0  0:未处理1:成功2:拒绝 type=0  0:充值 1:取款
		$recharge = DB::table('capital_audit')->where('state', 0)->where('type', 0)->count();
		//连接capital_audit 表 查询 state = 0  0:未处理1:成功2:拒绝 type=1  0:充值 1:取款
		$cash = DB::table('capital_audit')->where('state', 0)->where('type', 1)->count();
		$this->assign('recharge', $recharge);
		$this->assign('cash', $cash);

		//检测在线用户
		$end_time = time();
		$start_time = time() - 60 * 20; //减去20分钟
		//连接login_log表 条件 create_time创建时间 匹配 $start_time开始时间 $end_time结束时间
		$people_count = Db::table('login_log')->where(['create_time' => ['create_time', 'between', [$start_time, $end_time]]])->group('user_id')->count();
		$this->assign('people_count', $people_count);

		$this->assign('chat', $chat);
		return $this->fetch();

	}

	public function top() {

		return $this->fetch();
	}

	public function left() {
		return $this->fetch();
	}

	public function childindex() {

//		  dump(session::get());
		if (session::get('proxy.type') == 1) {
			$user_num = Db::table('relationship')->field('userid')->where(['top' => session::get('proxy.uid'), 'floor' => 3])->select();
		} else {
			$user_num = Db::table('relationship')->field('userid')->where(['prev' => session::get('proxy.uid'), 'floor' => 3])->select();
		}
		$user_array = ['0'];
		if (count($user_num) != 0) {
			$user_array = [];
			foreach ($user_num as $key => $value) {
				$user_array[] = $value['userid'];

			}
		}
		$t = time();
		$day_start = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
		$day_end = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));
		$map['create_time'] = ['create_time', 'between', [$day_start, $day_end]];
		$map['user_id'] = ['user_id', 'in', $user_array];

		// dump($map);die;

		//今日消费
		$todaybetting = DB::table('capital_detail')->where($map)->where(['type' => 0, 'create_time' => ['create_time', 'between', [$day_start, $day_end]]])->select();
		// dump(Db::table('capital_detail')->getLastSql());die;
		$today_betting_count = count($todaybetting);
		$today_betting_money = number_format(array_sum(array_map(function ($val) {return abs($val['money']);}, $todaybetting)), 2, '.', ',');
		$this->assign('today_betting_count', $today_betting_count);
		$this->assign('today_betting_money', $today_betting_money);

		//今日派奖
		$todaypj = DB::table('capital_detail')->where($map)->where(['type' => 3, 'create_time' => ['create_time', 'between', [$day_start, $day_end]]])->select();
		//   dump($todaypj);die;
		$today_pj_count = count($todaypj);
		$today_pj_money = number_format(array_sum(array_map(function ($val) {return $val['money'];}, $todaypj)), 2, '.', ',');

		$this->assign('today_pj_count', $today_pj_count);
		$this->assign('today_pj_money', $today_pj_money);

		//获取昨天00:00
		$timestart = strtotime(date('Y-m-d' . '00:00:00', time() - 3600 * 24));
		//获取今天00:00
		$timeend = strtotime(date('Y-m-d' . '00:00:00', time()));
		//昨日反水()
		$todayfs = DB::table('capital_detail')->where($map)->where(['type' => 11, 'create_time' => ['create_time', 'between', [$timestart, $timeend]]])->select();
		$today_fs_count = count($todayfs);
		$today_fs_money = number_format(array_sum(array_map(function ($val) {return $val['money'];}, $todayfs)), 2, '.', ',');
		$this->assign('today_fs_count', $today_fs_count);
		$this->assign('today_fs_money', $today_fs_money);

		//今日入金
		$today_recharge = DB::table('capital_detail')->where($map)->where(['type' => ['type', 'in', [2, 7,15]], 'create_time' => ['create_time', 'between', [$day_start, $day_end]]])->select();
		$today_recharge_count = count($today_recharge);
		$today_recharge_money = number_format(array_sum(array_map(function ($val) {return $val['money'];}, $today_recharge)), 2, '.', ',');
		$this->assign('today_recharge_count', $today_recharge_count);
		$this->assign('today_recharge_money', $today_recharge_money);

		//今日出金
		$today_cash = DB::table('capital_detail')->where($map)->where(['type' => 1, 'create_time' => ['create_time', 'between', [$day_start, $day_end]]])->select();
		$today_cash_count = count($today_cash);
		$today_cash_money = number_format(array_sum(array_map(function ($val) {return abs($val['money']);}, $today_cash)), 2, '.', ',');
		$this->assign('today_cash_count', $today_cash_count);
		//dump($today_cash_money);
		$this->assign('today_cash_money', $today_cash_money);

		//昨日反拥
		$today_commission = DB::table('capital_detail')->where($map)->where(['type' => 8, 'create_time' => ['create_time', 'between', [$timestart, $timeend]]])->select();
		$today_commission_count = count($today_cash);
		$today_commission_money = number_format(array_sum(array_map(function ($val) {return $val['money'];}, $today_commission)), 2, '.', ',');
		$this->assign('today_commission_count', $today_commission_count);
		$this->assign('today_commission_money', $today_commission_money);

		// //今日输赢(下注-中奖)
		// $today_winning = number_format(DB::table('capital_detail')->where($map)->where(['type'=>0,'create_time'=>['create_time','between',[$day_start,$day_end]]])->sum('money')-DB::table('capital_detail')->where($map)->where(['type'=>3,'create_time'=>['create_time','between',[$day_start,$day_end]]])->sum('money'),2,'.',',');

		// $this->assign('today_winning',$today_winning);

		//今日赠金
		$today_give = DB::table('capital_detail')->where($map)->where(['type' => ['type', 'in', [5, 10]], 'create_time' => ['create_time', 'between', [$day_start, $day_end]]])->select();
		$today_give_count = count($today_give);
		$today_give_money = number_format(array_sum(array_map(function ($val) {return $val['money'];}, $today_give)), 2, '.', ',');

		$today_yl = (array_sum(array_map(function ($val) {return $val['money'];}, $todaybetting)) - array_sum(array_map(function ($val) {return $val['money'];}, $todaypj)));

		$this->assign('today_yl', $today_yl);

		$this->assign('today_give_count', $today_give_count);
		$this->assign('today_give_money', $today_give_money);
		$this->assign('start_time', Request::param('get.start_time'));
		$this->assign('end_time', Request::param('get.end_time'));
		return $this->fetch();
	}

	public function cache_clear() {

		$rs = Cache::clear();
		if ($rs) {
			return json(array('error' => 0, 'msg' => '清除成功'));
		} else {
			return json(array('error' => 1, 'msg' => '清除失败'));
		}

	}

	public function checknewdata() {

		$new_recharge_data = DB::table('capital_audit')->where('state', 0)->where('type', 0)->count();
		$new_cash_data = DB::table('capital_audit')->where('state', 0)->where('type', 1)->count();

		return json(array('new_recharge' => $new_recharge_data, 'new_cash' => $new_cash_data));
	}

	public function adminchat() {
		$data['content'] = Request::param('content');
		$data['user_id'] = 0;
		$data['create_time'] = time();
		$rs = Db::name('chat_room')->insert($data);
		if ($rs) {
			return json(['error' => 0, 'time' => date('Y-m-d H:i:s', time())]);
		} else {
			return json(['error' => 1, 'time' => date('Y-m-d H:i:s', time())]);
		}
	}

	public function newchat() {
		$map['id'] = ['id', '>', Request::param('last_id')];
		$map['user_id'] = ['user_id', '<>', 0];
		$data = Db::table('chat_room')->where($map)->select();
		if (count($data) != 0) {

			foreach ($data as $key => $value) {
				$data[$key]['username'] = Db::table('user')->where('id', $data[$key]['user_id'])->find()['username'];
				$data[$key]['create_time'] = date('Y-m-d H:i:s', $data[$key]['create_time']);
			}

			return json(['error' => 0, 'content' => $data, 'count' => count($data)]);
		} else {
			return json(['error' => 1, 'content' => '', 'count' => 0]);
		}

	}

	public function history_chat() {

		$map['id'] = ['id', '<', Request::param('id')];
		$pageParam['query']['id'] = Request::param('id');
		$list = Db::table('chat_room')->where($map)->order('id desc')->paginate(15, true, $pageParam)->each(function ($item, $index) {
			if ($item['user_id'] == 0) {
				$item['username'] = '管理员';
			} else {
				$item['username'] = Db::table('user')->where('id', $item['user_id'])->find()['username'];
			}
			$item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
			return $item;
		});

		$this->assign('list', $list);
		return $this->fetch();
	}

	public function checknowpeople() {

		if (session::get('proxy.type') == 1) {
			$user_num = Db::table('relationship')->field('userid')->where(['top' => session::get('proxy.uid'), 'floor' => 3])->select();
		} else {
			$user_num = Db::table('relationship')->field('userid')->where(['prev' => session::get('proxy.uid'), 'floor' => 3])->select();
		}
		$user_array = ['0'];
		if (count($user_num) != 0) {
			$user_array = [];
			foreach ($user_num as $key => $value) {
				$user_array[] = $value['userid'];

			}
		}

		$end_time = time();
		$start_time = time() - 60 * 20;
		$data_count = Db::table('login_log')->where(['create_time' => ['create_time', 'between', [$start_time, $end_time]], 'user_id' => ['user_id', 'in', $user_array]])->group('user_id')->count();

		return $data_count;
	}

}
