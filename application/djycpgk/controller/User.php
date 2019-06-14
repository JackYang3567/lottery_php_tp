<?php
namespace app\djycpgk\controller;
use app\djycpgk\model\NewRelationship;
use app\djycpgk\model\Proxy;
use app\djycpgk\model\User as Usermodel;
use app\djycpgk\model\UserBankInfo;
use app\djycpgk\model\UserInfo;
use QL\Dom\Query;
use think\Db;
use app\home\controller\Lottery28;
use app\home\controller\Lottery;
use think\Env;
use think\facade\Request;
use \tp5er\Backup;
use app\common\Page;
use app\home\controller\Plan;
use Endroid\QrCode\QrCode;


class User extends Rbac {

    public function Grade($id,$grade){
        $ss = Usermodel::where('id',$id)->update(['grade' =>$grade ]);
        if ($ss){
            return json(['error' => 1, 'msg' => '修改成功']);
        }else{
            return json(['error' => -1, 'msg' => '修改失败']);
        }
    }
    public function proxylist_yh()
    {
        if (Request::method() == 'POST'){
            $list = NewRelationship::select();
            return json($list);
        }else{
            return $this->fetch();
        }
    }
    public function vue($name='',$user_id='',$agent='',$search=0,$start_time='',$end_time=''){
        if (Request::method() == 'POST'){
            $where = [];
            if ($search ==1){
                if ($name!=''){
                    $where[] = ['username','=',$name];
                }
                if ($user_id!=''){
                    $where[] = ['id','=',$user_id];
                }
                if($agent != ''){
                    $dl = Proxy::where('username',$agent)->field('uid')->find()['uid'];
                    $where[] = ['proxy_id','=',$dl];
                }
                if ($start_time !=0){
                    $where[] = ['create_time','>',strtotime($start_time)];
                }
                if ($end_time !=0){
                    $where[] = ['create_time','<',strtotime($end_time)+24*60*60-1];
                }
            }
            $pageParam = [];
            $paginate = 15;
            $urer =  Usermodel::field('
        id,username,create_time, active_time,active_ip,point,money,no_money,status,type,pid,proxy_id,group,grade,CASE WHEN unix_timestamp(now()) - active_time < 1200 THEN active_time ELSE create_time END sort_time
        ')->where('type', 0)->where($where)->order('active_time','desc')->paginate($paginate,false,$pageParam);
            $urer->append(['Parent','AccumulatedWinning','DianHua','SheBei','SuperiorUser','IsOnline','DengJi']);
            return json($urer);
        }else{
            return $this->fetch();
        }
    }
	public function manualPayment(){//人工入款
        if (Request::method() == 'POST'){
            if (input('post.type')==1){
                $name = input('post.naem');
                $user = Usermodel::where('username',$name)->field('id,username,money')->find();
                if ($user){
                    return json(['error' => 1, 'msg' => $user]);
                }else{
                    return json(['error' => -1, 'msg' => '未找到用户']);
                }
            }
        }else{
            return $this->fetch();
        }
    }
	 /**
     * 数据格式化
     * @param array $data
     */
    public function planFor($data,$lottery){
        $rs = new Plan;

        foreach($data as &$item){
            $bit = $rs->allLotteryType($item['type']);
            $item['plan_code_name'] = $bit['plan'][$item['plan_bet']]['content'][$item['plan_code']];
            $item['plan_bet_name'] = $bit['plan'][$item['plan_bet']]['name'];

            if($item['uid'] == 0){
                $item['uid'] = $item['uid'].'(系统)';
            }else{
                if($item['uid_type'] == 1){
                    $item['uid'] = $item['uid'].'(用户)';
                }else{
                    $item['uid'] = $item['uid'].'(管理员)';
                }
            }
            $item['type'] = $lottery[$item['type']];
        }
        return $data;
    }

    /**
     * 彩票计划
     */
	public function plan(){
		if(Request::method() == "POST"){
			$return_data = [
				'code' => 1,
				'msg' => 'ok'
			];
			$data = input('post.');
			$set = Db::table('system_config')->field('value')->where('id',53)->find();
			if($data['type'] == 'lottery'){
				//查询彩种
				$return_data['lottery'] = Db::table('lottery_config')->where('type','not in',[0,1,29,35,53,54,55,56])->where('switch','=',1)->column('name','type');
				$return_data['set'] = [];
				//处理查询的配置
				if(!empty($set['value'])){
					$set['value'] = json_decode($set['value'],true);
					$list = $this->planFor($set['value'],$return_data['lottery']);
					$return_data['set'] = $list;
				}
				
				// print_r($set['value']);die();
				
			}elseif( $data['type'] == 'lottery_config' ){
				//获取计划配置
				$rs = (new Plan)->allLotteryType($data['code']);
				if($rs['code'] == -1){
					$return_data['code'] = -1;
					$return_data['msg'] = '该彩种无法添加计划';
				}else{
					$return_data = $rs;
				}
			}elseif( $data['type'] == 'on_plan' ){
				//添加内容
				if(!empty($set['value'])){
					$set['value'] = json_decode($set['value'],true);
					if(count($set['value']) >= 10){
						$return_data['code'] = -1;
						$return_data['msg'] = '计划最多10个';
					}
					foreach($set['value'] as $vo){
						if($vo['type'] == $data['data']['type'] && $vo['uid'] == $data['data']['uid'] && $vo['uid_type'] == $data['data']['uid_type']){
							$return_data['msg'] = '此用户id已添加过这个彩种的计划,请换一个用户id';
							return $return_data;
						}
					}
				}
				$lottery = Db::table('lottery_config')->where('type','not in',[0,1,29,35,53,54,55,56])->where('switch','=',1)->column('name','type');
				$set['value'][] = $data['data'];
				Db::table('system_config')->where('id',53)->update(['value'=>json_encode($set['value'])]);
				$return_data['data'] = $this->planFor($set['value'],$lottery);
			}elseif( $data['type'] == 'change' ){
				//修改和删除操作
				$set['value'] = json_decode($set['value'],true);
				if($data['data']['type'] == 'switch'){
					$set['value'][$data['data']['key']]['switch'] = $set['value'][$data['data']['key']]['switch'] == 1? 0:1;
				}else{
					array_splice($set['value'],$data['data']['key'],1);
				}
				Db::table('system_config')->where('id',53)->update(['value'=>json_encode($set['value'])]);
			}
			return $return_data;
		}else{
			return $this->fetch();
		}	
	}
    public  function  chedan(){ //撤单
        $id = Request::param('type_id');
        $cd =cheDan($id);
        if ($cd){
            return json(['code' => $cd['code'], 'msg' => $cd['msg']]);
        }else{
            return json(['code' => $cd['code'], 'msg' => $cd['msg']]);
        }
    }
	public function ajax(){

		//dump(input('post.'));die;
		//$type =  期号type number
		//$keywords = 用户名 string
		//$start_time = 开始时间 time
		//$end_time  = 结束时间

        $map['id'] = ['id','in',['1','2','3']];
		$paginate = 15;
		$map = [];
		$pageParam['query'] = [];
		if (Request::param('type') != '') {
			$map['be.type'] = Request::param('type');
			$pageParam['query']['type'] = Request::param('type');
			$this->assign('lottery_type', Request::param('type'));
		}else{
            $map['be.type'] = ['be.type','not in',[53,54,55,56]];

        }
		if (Request::param('keywords') != '') {
			$usernames = DB::table('user')->field('id')->where('username', 'like', "%" . Request::param('keywords') . "%")->select();
			if ($usernames) {
				$user_ids = [];
				foreach ($usernames as $key => $value) {
					$user_ids[] = $value['id'];
				}
				$map['user_id'] = array('user_id', 'in', $user_ids);
				$pageParam['query']['keywords'] = Request::param('keywords');
				$this->assign('keywords', Request::param('keywords'));
			} else {
				$map['user_id'] = 'nodata';
				$pageParam['query']['keywords'] = Request::param('keywords');
				$this->assign('keywords', Request::param('keywords'));
			}
		}
		if (Request::param('expect') != '') {

			$map['expect'] = array('expect', 'like', "%" . Request::param('expect') . "%");
			$pageParam['query']['expect'] = Request::param('expect');
			$this->assign('expect', Request::param('expect'));
		}
		if (Request::param('start_time') != '' && Request::param('end_time') == '') {
			$map['be.create_time'] = ['be.create_time', '>', strtotime(Request::param('start_time'))];
			$pageParam['query']['start_time'] = Request::param('start_time');
			$this->assign('start_time', Request::param('start_time'));
		}
		if (Request::param('start_time') == '' && Request::param('end_time') != '') {
			$map['be.create_time'] = ['be.create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];
			$pageParam['query']['end_time'] = Request::param('end_time');
			$this->assign('end_time', Request::param('end_time'));
		}
		if (Request::param('start_time') != '' && Request::param('end_time') != '') {
			$map['be.create_time'] = ['be.create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];
			$pageParam['query']['start_time'] = Request::param('start_time');
			$this->assign('start_time', Request::param('start_time'));
			$pageParam['query']['end_time'] = Request::param('end_time');
			$this->assign('end_time', Request::param('end_time'));
		}
		if (Request::param('category') == '') {
			$list = Db::table('betting')->alias('be')
	            ->join('user u','be.user_id = u.id')
	            ->where($map)->where([['u.type','in',[0,2]]])
	            ->field('be.id,be.user_id,be.content,be.money,be.expect,be.category,be.type,be.win,be.state,be.other,be.create_time,u.type as u_type')
	            ->order(['state','id'=>'desc'])
	            ->paginate($paginate, false, $pageParam)
			    ->each(function ($item, $key) {
			    $item = bettingFormat([$item], true)[0];
			    $item['gem'] = '0';
				$item['create_time'] = date('Y-m-d H:i:s',$item['create_time']);
				$item['betting_count'] = count($this->object_to_array(json_decode($item['content'])));
				if ($item['money'] == 0) {
					$gen = DB::table('betting_gen')->alias('g')
					       ->join('user u','g.user_id=u.id')
					       ->field('g.user_id,g.money,g.win,g.create_time,u.username as user_name,u.type as u_type')
					       ->where([ 'g.main'=>0,'g.betting_id'=>$item['id'] ])
					       ->select();
					$item['gen'] = $gen;
				}
				
				if($item['type'] == 26){
					$map['type'] = ['type','=',2];
				} elseif ($item['type'] == 27){
					$map['type'] = ['type','=',12];
				} else {
					$map['type'] = ['type','=',$item['type']];
				}
				$yc = DB::table('lottery_code')->field('expect as qh')->where($map)->order('expect DESC')->find();
				if ($yc['qh'] == null) {
					$item['js_time'] = 1;
				}else{
					$item['js_time'] = 1;
					if ($item['expect'] > $yc['qh']) {
                        $item['js_time'] = 0;
						$map['expect'] = ['expect','=',$item['expect']];
                        $ss = DB::table('preset_lottery_code')->field('expect,content')->where($map)->find();
						if (!$ss) {
                            $item['pd_yskj'] = 0;
						}else{
                            $item['content']=$ss['content'];
							$item['pd_yskj'] = 1;
						}
					}
				}
				return $item;
			});
		}
		$zuida_id = DB::table('betting')->field('id')->order('id DESC')->where('type','not in',[ 53,54,55 ,56])->find();
		return json(['list' => $list, 'zuida_id' => $zuida_id ]);
	}
	public function yucekaijiang(){
		// dump(Request::param());exit();
		$bett =  Db::table('lottery_code')->where([['expect','=',Request::param('qh')],['type','=',Request::param('id')]])->find();
		$id = Request::param('id');
		$list2 = Db::table('lottery_code')->where('type',Request::param('id'))->order('expect desc')->limit(5)->select();
		// dump($list2);exit();
		// dump(DB::table('lottery_code')->getlastsql());exit();


		if(Request::param('id') <= 1){
			$pok_f = ['H'=>'红桃','C'=>'梅花','D'=>'方块','S'=>'黑桃'];
			$pok_Num = ['1'=>'A','11'=>'J','12'=>'Q','13'=>'K'];
			foreach ($list2 as $key => &$value) {
				$value['content'] = json_decode($value['content'],true)['code'];
				foreach ($value['content'] as $k => &$v) {
					if($v == 0){continue;}
					preg_match_all("/\d+/s",$v, $num);
					$v .= '('.$pok_f[substr($v, -1)].(isset($pok_Num[$num[0][0]]) ? $pok_Num[$num[0][0]] : $num[0][0]).')';
				}
			}
		}
	}

//	public function sx(){//实时刷新
//		// dump(Request::param());
//		$zd_id = DB::table('betting')->field('MAX(id) id')->find();
//		$ss= Request::param('id');//获取页面上最大id
//
//		if ($zd_id['id'] > $ss) {
//			return 0;
//		}else {
//			return 1;
//		}
//
//	}
	public function sw_delete($id){ //删除试玩用户
        Db::startTrans();
        try {
            DB::table('user')->where('id', $id)->delete();
            DB::table('betting')->where('user_id', $id)->delete();
            DB::table('capital_detail')->where('user_id', $id)->delete();
            Db::commit();
            return json(['error' => 0, 'msg' => '删除成功']);

        } catch (\Exception $e) {
            Db::rollback();
            return json(['error' => 1, 'msg' => '操作失败']);
        }
	}
	public function sdpj()//手动派奖
	{
//	    $type = [];
//        if (in_array(Request::param('id'),$type))
//        {
//
//        }
//        dump(Request::param('id'));
		// dump(Request::param());exit();
		$bett =  Db::table('lottery_code')->where([['expect','=',Request::param('qh')],['type','=',Request::param('id')]])->find();
		$id = Request::param('id');
		
		
		$list2 = Db::table('lottery_code')->where('type',Request::param('id'))->order('expect desc')->limit(5)->select();
		// dump($list2);
		// dump(DB::table('lottery_code')->getlastsql());exit();


		if(Request::param('id') <= 1 ) {
            $pok_f = ['H' => '红桃', 'C' => '梅花', 'D' => '方块', 'S' => '黑桃'];
            $pok_Num = ['1' => 'A', '11' => 'J', '12' => 'Q', '13' => 'K'];
            foreach ($list2 as $key => &$value) {
                $value['content'] = json_decode($value['content'], true)['code'];
                foreach ($value['content'] as $k => &$v) {
                    if ($v == 0) {
                        continue;
                    }
                    preg_match_all("/\d+/s", $v, $num);
                    $v .= '(' . $pok_f[substr($v, -1)] . (isset($pok_Num[$num[0][0]]) ? $pok_Num[$num[0][0]] : $num[0][0]) . ')';
                }
            }
        }
        if(Request::param('id') == 52) {
            $pok_f = ['(黑桃)','(红桃)','(梅花)','(方块)'];
            $pok_Num = ['1' => 'A', '11' => 'J', '12' => 'Q', '13' => 'K'];

            foreach ($list2 as $key => &$values) {
                $pai = " ";
                $niuniu = explode(",", $values['content']);
                foreach ($niuniu as $key => $value){
                    $ss = floor($value/4)+1;
                    $pss = $value%4;
                    $pai .=$pok_f[$pss];
                    if ( $ss == 1 || $ss == 11 || $ss == 12 || $ss == 13 ){
                        $pai .= $pok_Num[$ss];
                    }else{
                        $pai .= floor($value/4)+1; //获取 牌的点数
                    }
                }
                $values['content'] = $pai;
            }
        }

		return array($bett,$list2,$id);
}
	public function djzj()
	{
		// $data['no_money'] = Request::param('p_id');
		$user = DB::table('user')->where('id',Request::param('user_id'))->update(['no_money' => number_format(Request::param('p_id'), 2,'.','')]);


		// dump(Request::param('user_id'));exit();
		// dump(number_format(Request::param('p_id'), 2,'.',''));exit();

		if ($user) {
			return json(['error' => 0, 'msg' => '操作成功']);
		} else {
			return json(['error' => 1, 'msg' => '操作失败']);
		}
	}
    public function index() {
        $data = Request::get();
        $pageNum = 20;
        $pageParam = [ 'query'=>[] ];
        $where = [];
        $user_name = '';
        $xuanxiang = 0;
        $type = '';
        $page = 1;
        if(isset($data['user_name']) && !empty($data['user_name'])){
            $pageParam['query']['user_name'] = $data['user_name'];
            if ($data['xuanxiang'] == 0){
                $where['username'] = [ 'username','=', $data['user_name'] ];
            }else if($data['xuanxiang'] == 2){
                $where['id'] = [ 'id','=', $data['user_name'] ];
            }else{
                $user_id = model('UserInfo')->field('user_id')->where('phone_number',$data['user_name'])->find()['user_id'];
                $where['id'] = [ 'id','=',$user_id?$user_id:0];
            }
            $xuanxiang = $data['xuanxiang'];
            $user_name = $data['user_name'];
        }
        if(isset($data['type']) && !empty($data['type'])){
            $pageParam['query']['type'] = $data['type'];
            $type = $data['type'];
        }
        if(isset($data['page']) && !empty($data['page'])){
            $page = $data['page'];
        }
        $list = Usermodel::field('
        id,username,create_time, active_time,active_ip,point,off_money,money,no_money,status,type,pid,proxy_id,group,CASE WHEN unix_timestamp(now()) - active_time < 1200 THEN active_time ELSE create_time END sort_time
        ')->where('type',(isset($data['type']) ? $data['type'] : 0))->where($where)->order('active_time','desc')->paginate($pageNum,false,$pageParam);

        $this->assign('set_data', [
            'type' => $type,
            'user_name' => $user_name,
            'page' => $page,
            'xuanxiang' =>$xuanxiang
        ]);
        $this->assign('list', $list);
        return $this->fetch();
    }
    /**
     * 分组操作
     */
    public function  group($id,$group)
    {
        $user = (new Usermodel)->where('id',$id)->data(['group'=>$group])->update();
        if ($user){
            return json(['error' => 0, 'msg' => '修改成功']);
        }else{
            return json(['error' => 1, 'msg' => '修改失败']);
        }
    }

	public function user_add() //试玩添加

	{
		if (Request::method() == 'GET') {
			return $this->fetch();
		} else {
			// $User = new User;
			$data = Request::post();

			$userdata['username'] = $data['username'];
			$userdata['password'] = md5($data['password']);
			$userdata['type'] = $data['type'];
			//$userdata['nickname'] = 'adminuser';
			$userdata['create_time'] = time();
			$data_exist = Usermodel::where("username='" . $data['username'] . "'")->find();
			if ($data_exist) {
				$this->error('用户名已存在');
			}

			$rs = Db::table('user')->insert($userdata);

			if ($rs) {
				$this->success('添加成功', url('djycpgk/user/index').'?type=2');
			} else {
				$this->error('添加失败');
			}
		}
	}

	public function info_edit() {
			$baseinfo = Usermodel::where('id=' . Request::param('user_id'))->find();
			$extra_info = UserInfo::where('user_id=' . Request::param('user_id'))->find();
			if ($extra_info == null) {
				$UserInfo = new UserInfo;
				$UserInfo->user_id = Request::param('user_id');
				$UserInfo->save();
				$extra_info = UserInfo::where('user_id=' . Request::param('user_id'))->find();
			}
			$banklist = UserBankInfo::where('user_id=' . Request::param('user_id'))->select();
			$tab = Request::param('tab') != '' ? Request::param('tab') : 1;
			$this->assign('set_data', [
				'type' => Request::param('type'),
				'page' => Request::param('page')
			]);
			$this->assign('banklist', $banklist);
			$this->assign('tab', $tab);
			$this->assign('user_id', Request::param('user_id'));
			$this->assign('baseinfo', $baseinfo);
			$this->assign('extra_info', $extra_info);
			return $this->fetch('user_info');
	}
	public function info_xg(){
			$data = Request::post();
//			dump($data);die();
			$User = Usermodel::get($data['user_id']);
			if ($data['flag'] == 'base') {
				if ($data['password'] != '') {
					$User->password = md5($data['password']);
				}

				// $User->nickname =  $data['nickname'];
				$User->status = $data['status'];
				$User->type = $data['type'];
				$base_rs = $User->save();
				if ($base_rs) {
					$this->success('修改成功', url('djycpgk/user/info_edit', array('user_id' => $data['user_id'], 'type'=>$data['sw_type'],'tab' => 1)));
				} else {
					$this->error('修改失败');
				}
			} else if ($data['flag'] == 'info') {
				$UserInfo = UserInfo::get($data['user_id']);

				if ($data['draw_password'] != '') {
					$data = Request::only(['id', 'email', 'qq', 'id_number', 'draw_password']);
					$data['draw_password'] = md5($data['draw_password']);
				} else {
					$data = Request::only(['email', 'qq', 'id_number']);
				}
				//   dump($data);die;
				$extra_rs = $UserInfo->save($data, ['user_id' => Request::post('user_id')]);

				if ($extra_rs) {
					$this->success('修改成功', url('djycpgk/user/info_edit', ['user_id' => $UserInfo['user_id'], 'tab' => 2]));
				} else {
					$this->error('修改失败', url('djycpgk/user/info_edit', ['user_id' => $UserInfo['user_id'], 'tab' => 2]));
				}

			} elseif ($data['flag'] == 'bank') {
				$UserBankInfo = UserBankInfo::where('user_id', $data['user_id'])->find();
				$data = Request::only(['username', 'name', 'number', 'branch']);
				$bank_rs = $UserBankInfo->save($data, ['user_id' => Request::post('user_id')]);
				if ($bank_rs) {
					$this->success('修改成功', url('djycpgk/user/info_edit', array('user_id' => $UserBankInfo['user_id'], 'tab' => 3)));
				} else {
					$this->error('修改失败', url('djycpgk/user/info_edit', array('user_id' => $UserBankInfo['user_id'], 'tab' => 3)));
				}
			}
	}

		public function info_edit2() {

		if (Request::method() == 'GET') {

			$baseinfo = Usermodel::where('id=' . Request::param('user_id'))->find();
			$extra_info = UserInfo::where('user_id=' . Request::param('user_id'))->find();

			if ($extra_info == null) {

				$UserInfo = new UserInfo;
				$UserInfo->user_id = Request::param('user_id');
				$UserInfo->save();
				$extra_info = UserInfo::where('user_id=' . Request::param('user_id'))->find();
			}

			$banklist = UserBankInfo::where('user_id=' . Request::param('user_id'))->select();

			$tab = Request::param('tab') != '' ? Request::param('tab') : 1;
			$this->assign('banklist', $banklist);
			$this->assign('tab', $tab);
			$this->assign('user_id', Request::param('user_id'));
			$this->assign('baseinfo', $baseinfo);
			$this->assign('extra_info', $extra_info);
			return $this->fetch('user_info2');
		} else {
			// dump(Request::param());
			$data = Request::post();
			$User = Usermodel::get($data['id']);
			// dump($data);die;
			if ($data['flag'] == 'base') {
				if ($data['password'] != '') {
					$User->password = md5($data['password']);
				}

				// $User->nickname =  $data['nickname'];
				$User->status = $data['status'];
				$User->type = $data['type'];
				$base_rs = $User->save();
				if ($base_rs) {
					$this->success('修改成功', url('djycpgk/user/info_edit', array('user_id' => $data['id'], 'tab' => 1)));
				} else {
					$this->error('修改失败');
				}
			} else if ($data['flag'] == 'info') {
				$UserInfo = UserInfo::get($data['id']);

				if ($data['draw_password'] != '') {
					$data = Request::only(['id', 'email', 'qq', 'id_number', 'draw_password']);
					$data['draw_password'] = md5($data['draw_password']);
				} else {
					$data = Request::only(['email', 'qq', 'id_number']);
				}
				//   dump($data);die;
				$extra_rs = $UserInfo->save($data, ['user_id' => Request::post('id')]);

				if ($extra_rs) {
					$this->success('修改成功', url('djycpgk/user/info_edit', ['user_id' => $UserInfo['user_id'], 'tab' => 2]));
				} else {
					$this->error('修改失败', url('djycpgk/user/info_edit', ['user_id' => $UserInfo['user_id'], 'tab' => 2]));
				}

			} elseif ($data['flag'] == 'bank') {
				$UserBankInfo = UserBankInfo::where('user_id', $data['user_id'])->find();
				$data = Request::only(['username', 'name', 'number', 'branch']);
				$bank_rs = $UserBankInfo->save($data, ['user_id' => Request::post('user_id')]);
				if ($bank_rs) {
					$this->success('修改成功', url('djycpgk/user/info_edit', array('user_id' => $UserBankInfo['user_id'], 'tab' => 3)));
				} else {
					$this->error('修改失败', url('djycpgk/user/info_edit', array('user_id' => $UserBankInfo['user_id'], 'tab' => 3)));
				}
			}
		}
	}


	public function default_bank() {
		//$UserBankInfo = new UserBankInfo;
		$banklist = UserBankInfo::where('user_id=' . Request::param('user_id'))->select();
		foreach ($banklist as $key => $value) {
			UserBankInfo::update(['is_default' => 0], ['id' => $banklist[$key]['id']]);
		}
		$rs = UserBankInfo::update(['is_default' => 1], ['id' => Request::post('bank_id')]);
		return json(['error' => 0, 'msg' => '修改成功']);
	}

	public function bettingRecord() {
        if(Request::method() == 'POST'){
             if (input('post.pd')==1){
                $ss = DB::table('preset_lottery_code')->where('expect',input('post.qh'))->where('type',input('post.type'))->update(['content'=>input('post.content')]);
                if ($ss){
                    return json(['error' => 1, 'msg' => '修改成功']);
                }else{
                    return json(['error' => -1, 'msg' => '修改失败']);
                }
            }else{
                $data = input('post.');
                return $data['list'] == 0 ? 0 : Db::table('betting')->where('id','>',$data['list'])->where('type','not in',[ 53,54,55,56 ])->count();
            }
		}else{
			$lotterys = DB::table('lottery_config')->where([['type','not in',[53,54,55]]])->field('type,name')->order('type ASC')->select();
			$this->assign('lotterys', $lotterys);
			return $this->fetch();
		}
	}

	// public function yushekaijiang($expect=0,$type=0) //判断是否为预设开奖
	// {
	// 	if ($expect !=0 || $type != 0) {
	// 		$map['expect'] = ['expect','=',$expect];
	// 		$map['type'] = ['type','=',$type];
	// 		$yskj = DB::table('preset_lottery_code')->where($map)->find();
	// 		// dump($yskj);
	// 		if ($yskj==null) {
	// 			return 0;
	// 		}else{
	// 			return 1;
	// 		}
	// 	}else{
	// 		return '数据异常';
	// 	}
	// }
    public function messagelist() {
        $paginate = 25;
        $map = ['user_id' => Request::param('user_id')];
        $pageParam['query'] = [];
        $pageParam['query']['user_id'] = Request::param('user_id');
        if (Request::param('keywords') != '') {
            $map['title'] = Request::param('keywords');
            $pageParam['query']['title'] = Request::param('keywords');
            $this->assign('keywords', Request::param('keywords'));
        }
        $list = DB::table('user_message')->where($map)->order('create_time DESC')->paginate($paginate, false, $pageParam)->each(function ($item, $key) {

            return $item;
        });
        //dump($list);die;
        $username = DB::table('user')->where('id', Request::param('user_id'))->find()['username'];
        $this->assign('set_data', [
            'type' => Request::param('type'),
            'page' => Request::param('page')
        ]);
        $this->assign('username', $username);
        $this->assign('list', $list);
        return $this->fetch();
    }

	public function new_message() {
		$data['content'] = Request::param('message');
		$data['title'] = Request::param('title');
		$data['user_id'] = Request::param('user_id');
		$data['create_time'] = time();
		$rs = DB::table('user_message')->insert($data);
		if ($rs) {
			return json(['error' => 0, 'msg' => '发送成功']);
		} else {
			return json(['error' => 1, 'msg' => '删除成功']);
		}
	}


	public function messageManage() {

		$list = DB::table('user')->select();
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function group_send() {

		$users = Db::table('user')->field('id')->select();
		$user_ids = [];
		foreach ($users as $key => $value) {
			$user_ids[] = $value['id'];
		}
		$data = [];
		foreach ($user_ids as $key => $value) {
			$data[$key]['user_id'] = $value;
			$data[$key]['title'] = Request::param('type');
			$data[$key]['content'] = Request::param('content');
			$data[$key]['create_time'] = time();
		}

		$rs = DB::table('user_message')->insertAll($data);

		if ($rs) {
			return json(['error' => 0, 'msg' => '发送成功']);
		} else {
			return json(['error' => 1, 'msg' => '发送失败']);
		}
	}

    public function renew() {

        $return_data = ['code' => 0, 'msg' => '充值密码错误'];
        $post_data = input('post.');
//        if ($post_data['password'] != '1238888'){
//            return ['code' => 0, 'msg' => '充值密码错误'];
//        }
        if(!isset($post_data['id']) || empty($post_data['id']) || !isset($post_data['type']) || !isset($post_data['money']) || $post_data['money'] < 1){
            return $return_data;
        }
        $action = moneyAction([
            'uid' => $post_data['id'],
            'type' => $post_data['type'],
            'money' => $post_data['money']
        ]);
        if($action['code']) {
            $return_data['code'] = 1;
            $return_data['msg'] = '操作成功';
        }else {
            $return_data['msg'] = $action['msg'];
        }
        return $return_data;
    }
	public function message_delete() {
		$rs = DB::table('user_message')->where('id', Request::param('data_id'))->delete();
		if ($rs) {
			return json(['error' => 0, 'msg' => '删除成功']);
		} else {
			return json(['error' => 1, 'msg' => '删除失败']);
		}
	}

	public function capitaldetail() {
		$paginate = 25;
		$map = [];
		$mop=[];
		$mop['create_time'] = ['create_time', 'between', [strtotime(date("Y-m-d"),time()),strtotime(date("Y-m-d"),time())+24*3600]];
		$pageParam['query'] = [];
		if (Request::param('type') != '') {
			$map['type'] = Request::param('type');
			$mop['type'] = Request::param('type');
			$pageParam['query']['type'] = Request::param('type');
			$this->assign('type', Request::param('type'));
		}
		if (Request::param('keywords') != '') {
			$usernames = DB::table('user')->field('id')->where('username', '=',  Request::param('keywords'))->select();
			if ($usernames) {
				$user_ids = [];
				foreach ($usernames as $key => $value) {
					$user_ids[] = $value['id'];
				}
				$map['user_id'] = array('user_id', 'in', $user_ids);
				$mop['user_id'] = array('user_id', 'in', $user_ids);
				$pageParam['query']['keywords'] = Request::param('keywords');
				$this->assign('keywords', Request::param('keywords'));
			} else {
				$map['user_id'] = 'nodata';
				$mop['user_id'] = 'nodata';
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

        $list = Db::table('capital_detail')->where($map)->order('id DESC')->paginate($paginate, false, $pageParam)->each(function ($item, $key) {
			$userinfos = DB::table('user')->where('id', $item['user_id'])->find();

			$item['left_money'] = $userinfos['money'];
			if ($userinfos['type']==2){
                $item['username'] = $userinfos['username'].'(内部试玩用户)';
            }else{
                $item['username'] = $userinfos['username'];
            }

            $liebiao = moneyType();

            switch ($item['type']) {
			case '0':
				$item['typename'] = '<b style="color:red;">下注<b>';
				$item['expend'] = $item['money'];
				$item['in_come'] = 0;
				break;
			case '1':
				$item['typename'] = '<b style="color:Orange;">提现<b>';
				$item['expend'] = $item['money'];
				$item['in_come'] = 0;
				break;
			case '2':
				$item['typename'] = '<b style="color:#ff9933;">线下充值<b>';
				$item['expend'] = 0;
				$item['in_come'] = $item['money'];
				break;
			case '3':
				$item['typename'] = '<b style="color:Green;">中奖<b>';
				$item['expend'] = 0;
				$item['in_come'] = $item['money'];
				break;
			case '4':
				$item['typename'] = '<b style="color:Blue;">扣款<b>';
				$item['expend'] = $item['money'];
				$item['in_come'] = 0;
				break;
			case '5':
				$item['typename'] = '<b style="color:Indigo;">赠送<b>';
				$item['expend'] = 0;
				$item['in_come'] = $item['money'];

				break;
			case '6':
				$item['typename'] = '<b style="color:Violet;">退款<b>';
				$item['expend'] = 0;
				$item['in_come'] = $item['money'];

				break;
			case '7':
				$item['typename'] = '<b>在线充值</b>';
				$item['expend'] = 0;
				$item['in_come'] = $item['money'];
				break;

			case '8':
				$item['typename'] = '<b style="color:#6f599c;">返佣</b>';
				$item['expend'] = 0;
				$item['in_come'] = $item['money'];
				break;
			case '9':
				$item['typename'] = '<b style="color:#8A2BE2;">签到</b>';
				$item['expend'] = 0;
				$item['in_come'] = $item['money'];
				break;
			case '10':
				$item['typename'] = '<b style="">抽奖</b>';
				$item['expend'] = 0;
				$item['in_come'] = $item['money'];
				break;
			case '11':
				$item['typename'] = '<b style="">返水</b>';
				$item['expend'] = 0;
				$item['in_come'] = $item['money'];
				break;
			case '12':
				$item['typename'] = '<b style="">冻结资金</b>';
				$item['expend'] = $item['money'];
				$item['in_come'] = 0;
				break;
			case '13':
				$item['typename'] = '<b style="">冻结返还</b>';
				$item['expend'] = 0;
				$item['in_come'] = $item['money'];
				break;
			case '14':
				$item['typename'] = '<b style="">保底投注</b>';
				$item['expend'] = $item['money'];
				$item['in_come'] = 0;
				break;
			case '15':
				$item['typename'] = '<b style="">系统充值</b>';
				$item['expend'] =  0;
				$item['in_come'] =$item['money'];
				break;
			case '16':
				$item['typename'] = '<b style="">红包</b>';
				$item['expend'] = 0;
				$item['in_come'] = $item['money'];
				break;
			case '17':
                $item['typename'] = '<b style="">个人红包</b>';
                $item['expend'] = 0;
                $item['in_come'] = $item['money'];
                break;
            case '19':
                $item['typename'] = '<b style="">棋牌上分</b>';
                $item['expend'] = $item['money'];
                $item['in_come'] = 0;
                break;
            case '20':
                $item['typename'] = '<b style="">棋牌下分</b>';
                $item['expend'] = 0;
                $item['in_come'] = $item['money'];
                break;
			default:
                $item['typename'] = '<b style="">错误</b>';
                $item['expend'] = 0;
                $item['in_come'] = 0;
				break;
			}

			return $item;
		});
		$ze = 0;
		$types = Request::param('type');
		if ($types == null){

		}else {
			$zone = DB::table('capital_detail')->field('money')->where($mop)->select();
			foreach ($zone as $key => $value) {
				$ze += $value['money'];
			}

		}
		$this->assign('ze', $ze);
//		 dump($list);
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function loginlog() {

		$paginate = 25;
		$map = [];

		$pageParam['query'] = [];
		if (Request::param('ip') != '') {
			$map['ip'] = Request::param('ip');
			$pageParam['query']['ip'] = Request::param('ip');
			$this->assign('ip', Request::param('ip'));
		}
		if (Request::param('keywords') != '') {
			$usernames = DB::table('user')->field('id')->where('username', 'like', "%" . Request::param('keywords') . "%")->select();
			if ($usernames) {
				$user_ids = [];
				foreach ($usernames as $key => $value) {
					$user_ids[] = $value['id'];
				}
				$map['user_id'] = array('user_id', 'in', $user_ids);
				$pageParam['query']['keywords'] = Request::param('keywords');
				$this->assign('keywords', Request::param('keywords'));
			} else {
				$map['user_id'] = 'nodata';
				$pageParam['query']['keywords'] = Request::param('keywords');
				$this->assign('keywords', Request::param('keywords'));
			}
		}

		if (Request::param('start_time') != '' && Request::param('end_time') == '') {
			$map['create_time'] = ['create_time', '>', strtotime(Request::param('start_time'))];
			$pageParam['query']['start_time'] = Request::param('start_time');
			$this->assign('start_time', Request::param('start_time'));
		}

		if (Request::param('start_time') == '' && Request::param('end_time') != '') {
			$map['create_time'] = ['create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];
			$pageParam['query']['end_time'] = Request::param('end_time');
			$this->assign('end_time', Request::param('end_time'));
		}

		if (Request::param('start_time') != '' && Request::param('end_time') != '') {

			$map['create_time'] = ['create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];
			$pageParam['query']['start_time'] = Request::param('start_time');
			$this->assign('start_time', Request::param('start_time'));
			$pageParam['query']['end_time'] = Request::param('end_time');
			$this->assign('end_time', Request::param('end_time'));

		}
		$list = Db::table('login_log')->where($map)->order('create_time DESC')->paginate($paginate, false, $pageParam)->each(function ($item, $key) {

			$item['user_name'] = DB::table('user')->where('id', $item['user_id'])->find()['username'];
			if ($item['ip'] != '127.0.0.1') {
				$item['ip_address'] = $item['ip'];
			} else {
				$item['ip_address'] = '本局域网登录';
			}

			return $item;
		});

		//dump($list);die;
		$this->assign('list', $list);

		return $this->fetch();
	}

	protected function getIpregion($ip = '') {

		$ip = @file_get_contents("http://ip.taobao.com/service/getIpInfo.php?ip={$ip}");
		$ip = json_decode($ip, true);
		if (null !== $ip) {
			$ip_address = $ip['data']['country'] . "-" . $ip['data']['region'] . "-" . $ip['data']['city'];
		} else {
			$ip_address = '无法查询该ip所在地';
		}

		return $ip_address;
	}

	public function dellog() {
		$days = Request::param('days');
		$start_day = strtotime(date("Y-m-d", strtotime("-" . $days . " day")));
		$rs = Db::table('login_log')->where('create_time', '<', $start_day)->delete();
		if ($rs) {
			return json(['error' => 0, 'msg' => '删除成功']);
		} else {
			return json(['error' => 1, 'msg' => '删除失败']);
		}
	}
    // 用户冻操作
    public function userFrozen() {
        $return_data = [
            'code' => 0,
            'msg' => '冻结操作失败'
        ];
        $post_data = input('post.');
        if(!isset($post_data['id']) && $post_data['id'] !=''){
            return $return_data;
        }
        $status = ($post_data['status'] == 1 ? 0 : 1);
        if (DB::table('user')->update([ 'id'=>$post_data['id'],'status'=>$status ])) {
            $return_data['code'] = 1;
            $return_data['msg'] = '操作成功';
        }
        return $return_data;
    }



    /**
     * @param string $user_name  用户搜索 的 名称
     * @param string $order_name  排序的字段
     * @param int $begin_time  开始
     * @param int $end_time
     * @param string $action
     * @param string $order
     * @param int $p
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function statement($user_name='',$order_name='',$begin_time=0,$end_time=0,$action='',$order='DESC',$page=1){
        $page_num = 15;
        $page_param = [];
        $map = [];
        $money = 0;
        $tongji=['2'=>0,'5'=>0,'1'=>0,'0'=>0,'4'=>0,'7'=>0,'3'=>0,'8'=>0,'11'=>0]; //统计
//        if ($user_name){
//            if ($action){
//                $user_data = Db::table('user')->field('id')->where('username',$user_name)->find();
//                if($user_data){
//                    $child_data = Db::table('new_relationship')->field('child_one,child_two,child_three')->where('user_id',$user_data['id'])->find();
//                    $user_data = [ $user_data['id'] ];
//                    if ($child_data) {
//                        $user_data = array_merge($user_data,$child_data['child_one'] ? explode(",", $child_data['child_one']) : []);
//                        $user_data = array_merge($user_data,$child_data['child_two'] ? explode(",", $child_data['child_two']) : []);
//                        $user_data = array_merge($user_data,$child_data['child_three'] ? explode(",", $child_data['child_three']) : []);
//                    }
//                    $map['id'] = ['id', 'in', $user_data];
//                    $page_param['query']['user_name'] = $user_name;
//                }else{
//                    $map['username'] = ['username', '=', $user_name ];
//                    $page_param['query']['user_name'] = $user_name;
//                }
//            }else{
//                $map['username'] = ['username', '=',  $user_name];
//                $page_param['query']['user_name'] = $user_name;
//            }
//        }
        if ($user_name){
            $map['username'] = ['username', '=',  $user_name];
            $page_param['query']['user_name'] = $user_name;
        }
        if($begin_time){
            $page_param['query']['begin_time'] = $begin_time;
            $begin_time = strtotime($begin_time . '00:00:00');
        }
        if($end_time){ //如果操作 就放进$page_param 分页传递参数
            $page_param['query']['end_time'] = $end_time;
            $end_time = strtotime($end_time . '23:59:59');
        }
        if ($begin_time ==0){ //第一次进来没有 给开始和结束时间 赋值 当天 00 ：00:00  到 24:00:00
            $begin_time =strtotime(date('Y-m-d 00:00:00'));
        }
        if ($end_time ==0){
            $end_time = time();
        }
        if ($order_name!=null){//排序
            $list = [];
            //构建查询的SQL语句

            //查询所有有记录的用户
            $sql = "SELECT DISTINCT user_id as `count` FROM capital_detail  WHERE  type = $order_name AND judge=0 AND create_time > $begin_time AND create_time< $end_time  ";//执行SQL语句
            //执行SQL语句
            $count = Db::query($sql);
            $list['pandu'] =1;
            $data=[];
            $p1 = $page - 1;
            if ($order_name==2){
                $field ='sum(money) as v,user_id,type';
                $wher = "(type = 2 OR type = 7)";
            }elseif ($order_name=='200'){
                $field ='sum(money) as v,user_id,type';
                $wher = "(type = 2 OR type = 7 OR type=1)";
            } else{
                $field ='sum(money) as v,user_id,type';
                $wher = "type = $order_name";
            }
            $sql ="SELECT $field FROM (SELECT * FROM capital_detail  WHERE  $wher AND judge=0  AND create_time > $begin_time AND create_time< $end_time ) t  GROUP BY user_id ORDER BY v  $order LIMIT $p1,$page_num";
            $statistics = Db::query($sql);
            $Page  =  new Page($count,$page_num,Request::get(''));
            $show       = $Page->pageHtml();// 分页显示输出
            $this->assign('count',$count);// 赋值分页输出

            $this->assign('page2',$show);// 赋值分页输出
            foreach ($statistics as $v){
                $data1 = (new Usermodel)->alias('a')->field('id,username,money,type')->where('type','in',[0,2])->where('id',$v['user_id'])->find();
                $sql = "SELECT sum(money) as v, type FROM (SELECT * FROM capital_detail  WHERE  user_id = ".$data1['id']." AND create_time > ".$begin_time." AND create_time< ".$end_time." ) t  GROUP BY type";

                $sc = [];
                $statistics = Db::query($sql);
                $data1['statistics'] = [];
                foreach ($statistics as $k) {
                    $sc[$k['type']] = $k['v'];
                }
                foreach ([2, 5, 1,0,4,7,3,8,11] as  $vc){
                    if (!isset($sc[$vc])){
                        $sc[$vc] = 0.00;
                    }
                }
                $data1['statistics'] = $sc;
                $data1['child'] = 0;
                $child_data = Db::table('new_relationship')->field('child_one,child_two,child_three')->where('user_id',$data1['id'])->find();
                if ($child_data) {
                    $child_1 = $child_data['child_one'] ? count(explode(",", $child_data['child_one'])) : 0;
                    $child_2 = $child_data['child_two'] ? count(explode(",", $child_data['child_two'])) : 0;
                    $child_3 = $child_data['child_three'] ? count(explode(",", $child_data['child_three'])) : 0;
                    $data1['child'] = $child_1 + $child_2 + $child_3;
                }
                $data[] = $data1;
                foreach ([2, 5, 1,0,4,7,3,8,11] as  $vc){
                    $tongji[$vc] +=  $sc[$vc];
                }
            }
            //print_r($data);die();

        }else{
            $list = (new Usermodel)->field('id,username,money,type')->where($map)->where('type','in',[0,2])->order('id desc')->paginate($page_num, false, $page_param);

            $data = $list->toArray()['data'];

            foreach ($data as &$item) {
//                查询单个用户的所以资金
                $sql = "SELECT sum(money) as v, type FROM (SELECT * FROM capital_detail  WHERE  user_id = ".$item['id']." AND create_time > ".$begin_time." AND create_time< ".$end_time." ) t  GROUP BY type";
                $sc = [];
                $money +=  $item['money'];
                $statistics = Db::query($sql);
                $item['statistics'] = [];
                foreach ($statistics as $k) {
                    $sc[$k['type']] = $k['v'];
                }
                //统计当页数据
                foreach ([2, 5, 1,0,4,7,3,8,11] as  $vc){
                    if (!isset($sc[$vc])){
                        $sc[$vc] = 0.00;
                    }
                }

                $item['statistics'] = $sc;
                $item['login_time']= Db::table('login_log')->where('user_id',$item['id'])->order('create_time desc')->find()['create_time'];
                $item['child'] = 0;
                //查询下级代理
                $child_data = Db::table('new_relationship')->field('child_one,child_two,child_three')->where('user_id',$item['id'])->find();
                if ($child_data) {
                    $child_1 = $child_data['child_one'] ? count(explode(",", $child_data['child_one'])) : 0;
                    $child_2 = $child_data['child_two'] ? count(explode(",", $child_data['child_two'])) : 0;
                    $child_3 = $child_data['child_three'] ? count(explode(",", $child_data['child_three'])) : 0;
                    $item['child'] = $child_1 + $child_2 + $child_3;
                }
                foreach ([2, 5, 1,0,4,7,3,8,11] as  $vc){
                    $tongji[$vc] +=  $sc[$vc];
                }
            }
        }

        $this->assign('action',$action);//判断是否查询下级
        $this->assign('user_name', $user_name); //传递用户名
        $this->assign('begin_time', $begin_time); //传递开始时间
        $this->assign('end_time', $end_time); //传递结束时间
        $this->assign('money', $money); //传递当页总余额
        $this->assign('data', $data);
        $this->assign('tongji', $tongji); //统计当页所有数据
        $this->assign('list', $list);//分页
        $this->assign('order_name',$order_name);
        $this->assign('order',$order);
        return $this->fetch();
    }

	protected function commission_sum($user_id = '', $start_time = '', $end_time = '') {

		$commission = '';
		$map = [];
		$map['user_id'] = $user_id;
		$map['type'] = 8;
		if ($start_time != '' && $end_time == '') {
			$map['create_time'] = ['create_time', '>', strtotime($start_time)];
		}

		if (Request::param('start_time') == '' && Request::param('end_time') != '') {
			$map['create_time'] = ['create_time', '<', strtotime($end_time) + 3600 * 24 - 1];

		}

		if (Request::param('start_time') != '' && Request::param('end_time') != '') {

			$map['create_time'] = ['create_time', 'between', [strtotime($start_time), strtotime($end_time) + 3600 * 24 - 1]];

		}

		$commission_data = Db::table('capital_detail')->where($map)->sum('money');

		$commission = $commission_data != null ? $commission_data : 0;

		return $commission;
	}

	protected function subordinate($user_id = '') {
		$user_data = [];
		$user_ids = [];
		$relationship_data = DB::table('new_relationship')->where('user_id', $user_id)->find();
		if (null == $relationship_data) {
			$user_ids = [$user_id];
			$user_data = ['user_ids' => $user_ids, 'subordinate' => 0];
		} else {
			$user_ids = $user_id . ',' . $relationship_data['child_one'] . "," . $relationship_data['child_two'] . "," . $relationship_data['child_three'];
			$user_ids = array_filter(explode(',', $user_ids));
			$user_data = ['user_ids' => $user_ids, 'subordinate' => (count($user_ids) - 1)];
		}
		return $user_data;
	}

	public function proxy($pid = '', $user_id = "") {
		$return_data = ['error' => '0', '已建立代理关系'];
		if ($pid > $user_id) {
			return ['error' => 1, 'msg' => '用户关系错误'];
		}
		$user_pid = DB::table('user')->where('id', $user_id)->find()['pid'];
		if ($user_pid != 0) {
			return ['error' => 1, 'msg' => '该用户已有上级'];
		}
		$user_exist = DB::table('user')->where('id', $pid)->find();
		if (null == $user_exist) {
			return ['error' => 1, 'msg' => '上级用户不存在'];
		}

		$relationship_exist = DB::table('new_relationship')->where('user_id', $pid)->find();
		if (null == $relationship_exist) {
			$new_data['user_id'] = $pid;
			$new_data['child_one'] = $user_id;

			$new_rs = DB::name('new_relationship')->insert($new_data);
			if ($new_rs) {
				$user_pid_change = DB::table('user')->where('id', $user_id)->update(['pid' => $pid]);
			}

		}
		$user_pid_change = DB::table('user')->where('id', $user_id)->update(['pid' => $pid]);
		if (null != $relationship_exist['child_one']) {
			$child_one_array = explode(',', $relationship_exist['child_one']);
		} else {
			$child_one_array = [];
		}

		if (!in_array($user_id, $child_one_array)) {
			array_push($child_one_array, $user_id);
			$new_child_one = implode(',', $child_one_array);
			$new_child_one_rs = DB::table('new_relationship')->where('user_id', $pid)->update(['child_one' => $new_child_one]);

		}

		//检测pid 的上级代理
		$pid_pid = DB::table('user')->where('id', $pid)->find()['pid'];

		if (0 != $pid_pid && $pid_pid != $pid) {
			$pid_relationship = Db::table('new_relationship')->where('user_id', $pid_pid)->find();

			if (null != $pid_relationship['child_two']) {
				$child_two_array = explode(',', $pid_relationship['child_two']);
			} else {
				$child_two_array = [];
			}

			if (!in_array($user_id, $child_two_array)) {
				array_push($child_two_array, $user_id);
				$new_child_two = implode(',', $child_two_array);
				$new_child_two_rs = DB::table('new_relationship')->where('user_id', $pid_pid)->update(['child_two' => $new_child_two]);
			}
			//检测pid 的上上级代理
			$pid_pid_pid = DB::table('user')->where('id', $pid_pid)->find()['pid'];

			if (0 != $pid_pid_pid && $pid_pid_pid != $pid) {
				$pid_pid_relationship = Db::table('new_relationship')->where('user_id', $pid_pid_pid)->find();
				if (null != $pid_pid_relationship['child_three']) {
					$child_three_array = explode(',', $pid_pid_relationship['child_three']);
				} else {
					$child_three_array = [];
				}

				if (!in_array($user_id, $child_three_array)) {
					array_push($child_three_array, $user_id);
					$new_child_three = implode(',', $child_three_array);
					$new_child_three = DB::table('new_relationship')->where('user_id', $pid_pid_pid)->update(['child_three' => $new_child_three]);
				}
			}
		}
		return $return_data;
	}

	protected function object_to_array($obj) {
		$obj = (array) $obj;

		foreach ($obj as $k => $v) {
			if (gettype($v) == 'resource') {
				return;
			}
			if (gettype($v) == 'object' || gettype($v) == 'array') {
				$obj[$k] = (array) $this->object_to_array($v);
			}
		}

		return $obj;
	}

	public function proxylist() {

		$paginate = 20;
		$map = [];
		$pageParam['query'] = [];
		$pageParam['query']['start_time'] = Request::param('start_time');
		$pageParam['query']['end_time'] = Request::param('end_time');
		if (Request::param('keywords') != '') {
			$usernames = DB::table('user')->field('id')->where('username', 'like', "%" . Request::param('keywords') . "%")->select();
			if ($usernames) {
				$user_ids = [];
				foreach ($usernames as $key => $value) {
					$user_ids[] = $value['id'];
				}
				$map['user_id'] = array('user_id', 'in', $user_ids);
				$pageParam['query']['keywords'] = Request::param('keywords');
				$this->assign('keywords', Request::param('keywords'));
			} else {
				$map['user_id'] = 'nodata';
				$pageParam['query']['keywords'] = Request::param('keywords');
				$this->assign('keywords', Request::param('keywords'));
			}
		}

		$list = DB::table('new_relationship')->where($map)->paginate($paginate, false, $pageParam)->each(function ($item, $index) {
			$item['username'] = Db::table('user')->where('id', $item['user_id'])->find()['username'];
			$item['one_count'] = $item['child_one'] != '' ? count(explode(',', $item['child_one'])) : 0;
			$item['one_names'] = $item['child_one'] != '' ? $this->getUsernames($item['child_one']) : '暂无';

			$item['two_count'] = $item['child_two'] != '' ? count(explode(',', $item['child_two'])) : 0;
			$item['two_names'] = $item['child_two'] != '' ? $this->getUsernames($item['child_two']) : '暂无';
			$item['three_count'] = $item['child_three'] != '' ? count(explode(',', $item['child_three'])) : 0;
			$item['three_names'] = $item['child_three'] != '' ? $this->getUsernames($item['child_three']) : '暂无';
			$item['dama'] = $this->dama($item['child_one'] . "," . $item['child_two'] . "," . $item['child_three'], Request::param('start_time'), Request::param('end_time'));
			$item['total_fc'] = $this->total_fc($item['child_one'], 1, Request::param('start_time'), Request::param('end_time')) + $this->total_fc($item['child_two'], 2, Request::param('start_time'), Request::param('end_time')) + $this->total_fc($item['child_three'], 3, Request::param('start_time'), Request::param('end_time'));
			return $item;
		});
//        dump($list)
		$this->assign('start_time', Request::param('start_time'));
		$this->assign('end_time', Request::param('end_time'));

		$this->assign('list', $list);
		return $this->fetch();

	}

	protected function dama($user_id = [], $start_time = '', $end_time = "") {
		$user_id = array_filter(explode(',', $user_id));
		//$map['create_time'] = $dama_map;
		if (Request::param('start_time') != '' && Request::param('end_time') == '') {
			$map['create_time'] = ['create_time', '>', strtotime(Request::param('start_time'))];

		}

		if (Request::param('start_time') == '' && Request::param('end_time') != '') {
			$map['create_time'] = ['create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];

		}

		if (Request::param('start_time') != '' && Request::param('end_time') != '') {

			$map['create_time'] = ['create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];
		}

		$map['type'] = ['type', '=', 0];
		$map['user_id'] = ['user_id', 'in', $user_id];
		$rs = Db::table('capital_detail')->where($map)->sum('money');
		// dump(Db::table('capital_detail')->getlastsql());
		return $rs;
	}

	protected function total_fc($user_id = [], $level = 1) {
		$user_id = array_filter(explode(',', $user_id));

		$map['type'] = ['type', 'in', [0]];
		$map['user_id'] = ['user_id', 'in', $user_id];
		if (Request::param('start_time') != '' && Request::param('end_time') == '') {
			$map['create_time'] = ['create_time', '>', strtotime(Request::param('start_time'))];

		}

		if (Request::param('start_time') == '' && Request::param('end_time') != '') {
			$map['create_time'] = ['create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];

		}

		if (Request::param('start_time') != '' && Request::param('end_time') != '') {

			$map['create_time'] = ['create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];
		}

		$total_money = Db::table('capital_detail')->where($map)->sum('money');

		if ($total_money == 0) {
			$rs = 0;
		} else {

			$rates = Db::table('system_config')->where(['id' => ['id', 'in', ['22', '23', '24']]])->select();

			switch ($level) {
			case '1':
				$pecent_rate = $rates[0]['value'];
				break;
			case '2':

				$pecent_rate = $rates[1]['value'];
				break;
			case '3':

				$pecent_rate = $rates[2]['value'];
				break;
			default:
				# code...
				break;
			}

			$rs = sprintf("%.2f", ($total_money) * $pecent_rate / 100);

		}

		return $rs;

	}

	public function getUsernames($user_ids = '') {
		$username_array = [];
		$user_id_array = explode(',', $user_ids);
		$usernames = Db::table('user')->field('username')->where(['id' => ['id', 'in', $user_id_array]])->select();

		foreach ($usernames as $key => $value) {
			$username_array[] = $value['username'];
		}

		return implode(',', $username_array);
	}

	function transfer() {
		//dump($this->proxy('58','60'));die;

		$new_pid = input('post.p_id');
		$user_id = input('post.user_id');

		//检查是否有该ID用户
		$p_user = DB::table('user')->where('id', $new_pid)->find();
		if (null == $p_user) {
			return json(['error' => 1, 'msg' => '无目标代理相关信息，无法转移!']);
		}

		//查询user_id 的 所有子集
		$allchilds = $this->getChildrenIds($user_id);

		if (in_array($new_pid, explode(',', $allchilds))) {
			return json(['error' => 1, 'msg' => '目标代理已经在该代理线下,无法转移']);
		}

		//检测new_pid 在 关系表 是否有数据

		$new_relationship_exist = DB::table('new_relationship')->where('user_id', $new_pid)->find();
		$old_pid = Db::table('user')->where('id', $user_id)->find()['pid'];

		if ($user_id == $new_pid) {

			return json(['error' => 1, 'msg' => '上级所属不能是自己']);
		}

		if ($old_pid == $new_pid) {
			return json(['error' => 1, 'msg' => '该用户已经是目标代理的下级']);
		}
		//检测关系表是否有数据
		if (null == $new_relationship_exist) {
			//如果所转移的用户 之前无所属
			if ($old_pid == 0) {
				Db::startTrans();
				try {
					$this->backuprelationship();
					$user_rs = DB::table('user')->where('id', $user_id)->update(['pid' => $new_pid]);
					$new_relationship_data['user_id'] = $new_pid;
					$new_relationship_data['child_one'] = $user_id;
					$relationship_rs = DB::name('new_relationship')->insert($new_relationship_data);
					//查询新父级的上级
					$new_pid_pid = DB::table('user')->where('id', $new_pid)->find()['pid'];
					if (0 != $new_pid_pid) {
						//新父级的父级关系表
						$new_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid_pid)->find();
						//   dump($new_pid_pid_relationship);die;
						//将所转移用户 放入新父级的父级child_two
						$new_pid_pid_newchildtwo = $new_pid_pid_relationship['child_two'] != null ? $new_pid_pid_relationship['child_two'] . ',' . $user_id : $user_id;
						// dump($new_pid_pid_newchildthree);die;
						$new_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $new_pid_pid)->update(['child_two' => $new_pid_pid_newchildtwo]);
						// dump($new_pid_pid_newchildthree)
						//查询新父级的上级的上级
						$new_pid_pid_pid = DB::table('user')->where('id', $new_pid_pid)->find()['pid'];
						if (0 != $new_pid_pid_pid) {
							//将所转移用户的  放入 新父级的父级的父级的 child_three
							//新父级的父级的关系表
							$new_pid_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid_pid_pid)->find();
							$new_pid_pid_pid_newchildthree = $new_pid_pid_pid_relationship['child_three'] != null ? $new_pid_pid_pid_relationship['child_three'] . ',' . $user_id : $user_id;
							$new_pid_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $new_pid_pid_pid)->update(['child_three' => $new_pid_pid_pid_newchildthree]);
						}

					}

					Db::commit();
					return json(['error' => 0, 'msg' => '转移成功','name'=>$p_user['username']]);
				} catch (\Exception $e) {
					Db::rollback();
					return json(['error' => 1, 'msg' => '转移失败']);
				}
			} else {
				//判断所转移的用户 有无下级
				$user_relationship = DB::table('new_relationship')->where('user_id', $user_id)->find();
				if (null == $user_relationship) {
					//上级用户的 关系
					$old_relationship = DB::table('new_relationship')->where('user_id', $old_pid)->find();
					Db::startTrans();
					try {
						$this->backuprelationship();
						//把该用户从父级中删除
						$old_new_childone = str_replace($user_id, '', $old_relationship['child_one']);
						$old_new_childone = implode(',', array_filter(explode(',', $old_new_childone)));
						$old_relationship_rs = DB::table('new_relationship')->where('user_id', $old_pid)->update(['child_one' => $old_new_childone]);

						$old_user_pid = DB::table('user')->where('id', $old_pid)->find()['pid'];
						if (0 != $old_user_pid) {
							//上级的上级的关系
							$old_pid_relationship = DB::table('new_relationship')->where('user_id', $old_user_pid)->find();
							$old_new_childtwo = str_replace($user_id, '', $old_pid_relationship['child_two']);
							$old_new_childtwo = implode(',', array_filter(explode(',', $old_new_childtwo)));

							$old_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $old_user_pid)->update(['child_two' => $old_new_childtwo]);
							//把所转移的用户从 上级的上级的上级  删除(如果user_id 的 old_pid—old_pid 的 pid != 0)
							$old_user_pid_pid = DB::table('user')->where('id', $old_user_pid)->find()['pid'];
							if (0 != $old_user_pid_pid) {
								$old_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $old_user_pid_pid)->find();
								$old_new_childthree = str_replace($user_id, '', $old_pid_pid_relationship['child_three']);
								$old_new_childthree = implode(',', array_filter(explode(',', $old_new_childthree)));

								$old_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $old_user_pid_pid)->update(['child_three' => $old_new_childthree]);
							}

						}

						//修改user pid
						$user_rs = DB::table('user')->where('id', $user_id)->update(['pid' => $new_pid]);
						$new_relationship_data['user_id'] = $new_pid;
						$new_relationship_data['child_one'] = $user_id;
						$relationship_rs = DB::name('new_relationship')->insert($new_relationship_data);

						//查询新父级的上级
						$new_pid_pid = DB::table('user')->where('id', $new_pid)->find()['pid'];
						if (0 != $new_pid_pid) {
							//新父级的父级关系表
							$new_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid_pid)->find();
							//   dump($new_pid_pid_relationship);die;
							//将所转移用户 放入新父级的父级child_two

							$new_pid_pid_newchildtwo = $new_pid_pid_relationship['child_two'] != null ? $new_pid_pid_relationship['child_two'] . ',' . $user_id : $user_id;

							$new_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $new_pid_pid)->update(['child_two' => $new_pid_pid_newchildtwo]);

							//查询新父级的上级的上级
							$new_pid_pid_pid = DB::table('user')->where('id', $new_pid_pid)->find()['pid'];

							if (0 != $new_pid_pid_pid) {
								//将所转移用户的  放入 新父级的父级的父级的 child_three
								//新父级的父级的关系表
								$new_pid_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid_pid_pid)->find();

								$new_pid_pid_pid_newchildthree = $new_pid_pid_pid_relationship['child_three'] != null ? $new_pid_pid_pid_relationship['child_three'] . ',' . $user_id : $user_id;

								$new_pid_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $new_pid_pid_pid)->update(['child_three' => $new_pid_pid_pid_newchildthree]);
							}

						}

						Db::commit();
						return json(['error' => 0, 'msg' => '转移成功','name'=>$p_user['username']]);

					} catch (\Exception $e) {
						Db::rollback();
						return json(['error' => 1, 'msg' => '转移失败']);

					}

				} else {

					//所转移的用户 存在下级用户
					$old_relationship = DB::table('new_relationship')->where('user_id', $old_pid)->find();
					Db::startTrans();
					try {
						$this->backuprelationship();
						//把所转移的用户从父级中删除
						$old_new_childone = str_replace($user_id, '', $old_relationship['child_one']);
						$old_new_childone = implode(',', array_filter(explode(',', $old_new_childone)));

						//把所转移的用户的child_one 从父级的childtwo中 删除
						if (null != $user_relationship['child_one']) {
							$old_new_childone_childtwo = str_replace($user_relationship['child_one'], '', $old_relationship['child_two']);
							$old_new_childone_childtwo = implode(',', array_filter(explode(',', $old_new_childone_childtwo)));
						} else {

							$old_new_childone_childtwo = $old_relationship['child_two'];
						}
						//把所转移的用户的child_child_two 从父级的childthree中 删除
						if (null != $user_relationship['child_two']) {
							$old_new_childone_childthree = str_replace($user_relationship['child_two'], '', $old_relationship['child_three']);
							$old_new_childone_childthree = implode(',', array_filter(explode(',', $old_new_childone_childthree)));
						} else {
							$old_new_childone_childthree = $old_relationship['child_three'];
						}

						$old_relationship_rs = DB::table('new_relationship')->where('user_id', $old_pid)->update(['child_one' => $old_new_childone, 'child_two' => $old_new_childone_childtwo, 'child_three' => $old_new_childone_childthree]);
						//把所转移的用户从 上级的上级 删除(如果user_id 的 old_pid 的 pid != 0)
						$old_user_pid = DB::table('user')->where('id', $old_pid)->find()['pid'];
						if (0 != $old_user_pid) {
							//上级的上级的关系
							$old_pid_relationship = DB::table('new_relationship')->where('user_id', $old_user_pid)->find();
							$old_new_childtwo = str_replace($user_id, '', $old_pid_relationship['child_two']);
							$old_new_childtwo = implode(',', array_filter(explode(',', $old_new_childtwo)));

							//将所转移用户的child_one 从 父级的父级的 child_three 中删除
							if (null != $user_relationship['child_one']) {
								$old_new_childtwo_three = str_replace($user_relationship['child_one'], '', $old_pid_relationship['child_three']);
								$old_new_childtwo_three = implode(',', array_filter(explode(',', $old_new_childtwo_three)));
							} else {
								$old_new_childtwo_three = $old_pid_relationship['child_three'];
							}

							$old_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $old_user_pid)->update(['child_two' => $old_new_childtwo, 'child_three' => $old_new_childtwo_three]);
							//把所转移的用户从 上级的上级的上级  删除(如果user_id 的 old_pid—old_pid 的 pid != 0)
							$old_user_pid_pid = DB::table('user')->where('id', $old_user_pid)->find()['pid'];
							if (0 != $old_user_pid_pid) {

								$old_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $old_user_pid_pid)->find();

								$old_new_childthree = str_replace($user_id, '', $old_pid_pid_relationship['child_three']);
								$old_new_childthree = implode(',', array_filter(explode(',', $old_new_childthree)));

								$old_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $old_user_pid_pid)->update(['child_three' => $old_new_childthree]);
							}

						}
						//修改user pid
						$user_rs = DB::table('user')->where('id', $user_id)->update(['pid' => $new_pid]);
						$new_relationship_data['user_id'] = $new_pid;
						$new_relationship_data['child_one'] = $user_id;
						$relationship_rs = DB::name('new_relationship')->insert($new_relationship_data);

						//查询新的父级的relationship
						$new_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid)->find();
						//将所转移的用户child_one放入新父级的child_two
						if (null != $user_relationship['child_one']) {

							$new_pid_childtwo = $new_pid_relationship['child_two'];
							$new_pid_childtwo = $new_pid_childtwo . ',' . $user_relationship['child_one'];
							$new_pid_childtwo = implode(',', array_filter(explode(',', $new_pid_childtwo)));
							$new_pid_rs = DB::name('new_relationship')->where('user_id', $new_pid)->update(['child_two' => $new_pid_childtwo]);
						}

						//将所转移的用户child_two放入新父级的child_three

						if (null != $user_relationship['child_two']) {

							$new_pid_childthree = $new_pid_relationship['child_three'];
							$new_pid_childthree = $new_pid_childthree . ',' . $user_relationship['child_two'];
							$new_pid_childthree = implode(',', array_filter(explode(',', $new_pid_childthree)));
							$new_pid_rs = DB::name('new_relationship')->where('user_id', $new_pid)->update(['child_three' => $new_pid_childthree]);

						}
						Db::commit();
						return json(['error' => 0, 'msg' => '转移成功','name'=>$p_user['username']]);

					} catch (\Exception $e) {
						Db::rollback();
						return json(['error' => 1, 'msg' => '转移失败']);
					}
				}

			}

		} else {
			//在关系表中 已有数据
			$user_relationship = DB::table('new_relationship')->where('user_id', $user_id)->find();

			if (0 == $old_pid) {
				//判断所转移的用户 有无下级
				if (null == $user_relationship['child_one']) {

					Db::startTrans();
					try {
						$this->backuprelationship();
						$new_relationship_new_childone = $new_relationship_exist['child_one'] != null ? $new_relationship_exist['child_one'] . ',' . $user_id : $user_id;
						$user_rs = DB::table('user')->where('id', $user_id)->update(['pid' => $new_pid]);
						$new_relationship_rs = Db::table('new_relationship')->where('user_id', $new_pid)->update(['child_one' => $new_relationship_new_childone]);
						//查询新父级的上级
						$new_pid_pid = DB::table('user')->where('id', $new_pid)->find()['pid'];

						if (0 != $new_pid_pid) {

							//新父级的父级关系表
							$new_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid_pid)->find();
							//   dump($new_pid_pid_relationship);die;
							//将所转移用户 放入新父级的父级child_two

							$new_pid_pid_newchildtwo = $new_pid_pid_relationship['child_two'] != null ? $new_pid_pid_relationship['child_two'] . ',' . $user_id : $user_id;

							//将所转移用户的child_one 放入新父级的父级child_three
							$new_pid_pid_newchildthree = $new_pid_pid_relationship['child_three'] != null ? $new_pid_pid_relationship['child_three'] . ',' . $user_relationship['child_one'] : $user_relationship['child_one'];

							// dump($new_pid_pid_newchildthree);die;
							$new_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $new_pid_pid)->update(['child_two' => $new_pid_pid_newchildtwo, 'child_three' => $new_pid_pid_newchildthree]);

							// dump($new_pid_pid_newchildthree)
							//查询新父级的上级的上级
							$new_pid_pid_pid = DB::table('user')->where('id', $new_pid_pid)->find()['pid'];

							if (0 != $new_pid_pid_pid) {
								//将所转移用户的  放入 新父级的父级的父级的 child_three

								//新父级的父级的关系表
								$new_pid_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid_pid_pid)->find();

								$new_pid_pid_pid_newchildthree = $new_pid_pid_pid_relationship['child_three'] != null ? $new_pid_pid_pid_relationship['child_three'] . ',' . $user_id : $user_id;

								$new_pid_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $new_pid_pid_pid)->update(['child_three' => $new_pid_pid_pid_newchildthree]);
							}

						}

						Db::commit();
						return json(['error' => 0, 'msg' => '转移成功','name'=>$p_user['username']]);

					} catch (\Exception $e) {
						Db::rollback();
						return json(['error' => 1, 'msg' => '转移失败']);
					}

				} else {
					//有下级

					Db::startTrans();
					try {
						$this->backuprelationship();
						//将所转移的用户的child_one 放入 new_pid 的child_two
						$new_relationship_new_childone = $new_relationship_exist['child_one'] != null ? $new_relationship_exist['child_one'] . ',' . $user_id : $user_id;
						$new_relationship_new_childtwo = $new_relationship_exist['child_two'] != null ? $new_relationship_exist['child_two'] . ',' . $user_relationship['child_one'] : $user_relationship['child_one'];

						if (null != $user_relationship['child_two']) {

							$new_relationship_new_childthree = $new_relationship_exist['child_three'] != null ? $new_relationship_exist['child_three'] . ',' . $user_relationship['child_two'] : $user_relationship['child_two'];
						} else {
							$new_relationship_new_childthree = $new_relationship_exist['child_three'];
						}

						$new_relationship_rs = Db::table('new_relationship')->where('user_id', $new_pid)->update(['child_one' => $new_relationship_new_childone, 'child_two' => $new_relationship_new_childtwo, 'child_three' => $new_relationship_new_childthree]);
						$user_rs = DB::table('user')->where('id', $user_id)->update(['pid' => $new_pid]);

						//查询新父级的上级
						$new_pid_pid = DB::table('user')->where('id', $new_pid)->find()['pid'];

						if (0 != $new_pid_pid) {

							//新父级的父级关系表
							$new_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid_pid)->find();
							//   dump($new_pid_pid_relationship);die;
							//将所转移用户 放入新父级的父级child_two

							$new_pid_pid_newchildtwo = $new_pid_pid_relationship['child_two'] != null ? $new_pid_pid_relationship['child_two'] . ',' . $user_id : $user_id;

							//将所转移用户的child_one 放入新父级的父级child_three
							$new_pid_pid_newchildthree = $new_pid_pid_relationship['child_three'] != null ? $new_pid_pid_relationship['child_three'] . ',' . $user_relationship['child_one'] : $user_relationship['child_one'];

							// dump($new_pid_pid_newchildthree);die;
							$new_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $new_pid_pid)->update(['child_two' => $new_pid_pid_newchildtwo, 'child_three' => $new_pid_pid_newchildthree]);

							// dump($new_pid_pid_newchildthree)
							//查询新父级的上级的上级
							$new_pid_pid_pid = DB::table('user')->where('id', $new_pid_pid)->find()['pid'];

							if (0 != $new_pid_pid_pid) {
								//将所转移用户的  放入 新父级的父级的父级的 child_three

								//新父级的父级的关系表
								$new_pid_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid_pid_pid)->find();

								$new_pid_pid_pid_newchildthree = $new_pid_pid_pid_relationship['child_three'] != null ? $new_pid_pid_pid_relationship['child_three'] . ',' . $user_id : $user_id;

								$new_pid_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $new_pid_pid_pid)->update(['child_three' => $new_pid_pid_pid_newchildthree]);
							}

						}

						Db::commit();
						return json(['error' => 0, 'msg' => '转移成功','name'=>$p_user['username']]);

					} catch (\Exception $e) {
						Db::rollback();
						return json(['error' => 1, 'msg' => '转移失败']);
					}

				}
			} else {

				//判断所转移的用户有无下级
				if (null == $user_relationship['child_one']) {

					//上级用户的 关系
					$old_relationship = DB::table('new_relationship')->where('user_id', $old_pid)->find();

					Db::startTrans();
					try {
						//每次进行转移时， 先备份关系表
						$this->backuprelationship();
						//把该用户从父级中删除
						$old_new_childone = str_replace($user_id, '', $old_relationship['child_one']);
						$old_new_childone = implode(',', array_filter(explode(',', $old_new_childone)));
						$old_relationship_rs = DB::table('new_relationship')->where('user_id', $old_pid)->update(['child_one' => $old_new_childone]);

						$old_user_pid = DB::table('user')->where('id', $old_pid)->find()['pid'];

						if (0 != $old_user_pid) {
							//上级的上级的关系
							$old_pid_relationship = DB::table('new_relationship')->where('user_id', $old_user_pid)->find();

							$old_new_childtwo = str_replace($user_id, '', $old_pid_relationship['child_two']);
							$old_new_childtwo = implode(',', array_filter(explode(',', $old_new_childtwo)));

							$old_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $old_user_pid)->update(['child_two' => $old_new_childtwo]);

							//dump($old_pid_relationship);die;

							//把所转移的用户从 上级的上级的上级  删除(如果user_id 的 old_pid—old_pid 的 pid != 0)

							$old_user_pid_pid = DB::table('user')->where('id', $old_user_pid)->find()['pid'];

							if (0 != $old_user_pid_pid) {

								$old_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $old_user_pid_pid)->find();

								$old_new_childthree = str_replace($user_id, '', $old_pid_pid_relationship['child_three']);
								$old_new_childthree = implode(',', array_filter(explode(',', $old_new_childthree)));

								$old_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $old_user_pid_pid)->update(['child_three' => $old_new_childthree]);
							}

						}
						//修改user pid
						$user_rs = DB::table('user')->where('id', $user_id)->update(['pid' => $new_pid]);
						$new_relationship_new_childone = $new_relationship_exist['child_one'] != null ? $new_relationship_exist['child_one'] . ',' . $user_id : $user_id;
						$new_relationship_rs = Db::table('new_relationship')->where('user_id', $new_pid)->update(['child_one' => $new_relationship_new_childone]);

						//查询新父级的上级
						$new_pid_pid = DB::table('user')->where('id', $new_pid)->find()['pid'];
						if (0 != $new_pid_pid) {

							//新父级的父级关系表
							$new_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid_pid)->find();

							//将所转移用户 放入新父级的父级child_two

							$new_pid_pid_newchildtwo = $new_pid_pid_relationship['child_two'] != null ? $new_pid_pid_relationship['child_two'] . ',' . $user_id : $user_id;

							$new_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $new_pid_pid)->update(['child_two' => $new_pid_pid_newchildtwo]);

							//查询新父级的上级的上级
							$new_pid_pid_pid = DB::table('user')->where('id', $new_pid_pid)->find()['pid'];

							if (0 != $new_pid_pid_pid) {

								//将所转移用户的  放入 新父级的父级的父级的 child_three

								//新父级的父级的关系表
								$new_pid_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid_pid_pid)->find();

								$new_pid_pid_pid_newchildthree = $new_pid_pid_pid_relationship['child_three'] != null ? $new_pid_pid_pid_relationship['child_three'] . ',' . $user_id : $user_id;

								$new_pid_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $new_pid_pid_pid)->update(['child_three' => $new_pid_pid_pid_newchildthree]);
							}

						}

						Db::commit();
						return json(['error' => 0, 'msg' => '转移成功','name'=>$p_user['username']]);

					} catch (\Exception $e) {
						Db::rollback();
						return json(['error' => 1, 'msg' => '转移失败']);

					}
				} else {
					//所转移的用户有下级

					$old_relationship = DB::table('new_relationship')->where('user_id', $old_pid)->find();

					Db::startTrans();
					try {
						$this->backuprelationship();die;
						//把所转移的用户从父级中删除
						$old_new_childone = str_replace($user_id, '', $old_relationship['child_one']);
						$old_new_childone = implode(',', array_filter(explode(',', $old_new_childone)));
						//把所转移的用户的child_one 从父级的childtwo中 删除
						if (null != $user_relationship['child_one']) {
							$old_new_childone_childtwo = str_replace($user_relationship['child_one'], '', $old_relationship['child_two']);
							$old_new_childone_childtwo = implode(',', array_filter(explode(',', $old_new_childone_childtwo)));
						} else {

							$old_new_childone_childtwo = $old_relationship['child_two'];
						}
						//把所转移的用户的child_child_two 从父级的childthree中 删除
						if (null != $user_relationship['child_two']) {
							$old_new_childone_childthree = str_replace($user_relationship['child_two'], '', $old_relationship['child_three']);
							$old_new_childone_childthree = implode(',', array_filter(explode(',', $old_new_childone_childthree)));
						} else {
							$old_new_childone_childthree = $old_relationship['child_three'];
						}

						$old_relationship_rs = DB::table('new_relationship')->where('user_id', $old_pid)->update(['child_one' => $old_new_childone, 'child_two' => $old_new_childone_childtwo, 'child_three' => $old_new_childone_childthree]);

						//把所转移的用户从 上级的上级 删除(如果user_id 的 old_pid 的 pid != 0)
						$old_user_pid = DB::table('user')->where('id', $old_pid)->find()['pid'];

						if (0 != $old_user_pid) {
							//上级的上级的关系
							$old_pid_relationship = DB::table('new_relationship')->where('user_id', $old_user_pid)->find();
							$old_new_childtwo = str_replace($user_id, '', $old_pid_relationship['child_two']);
							$old_new_childtwo = implode(',', array_filter(explode(',', $old_new_childtwo)));

							//将所转移用户的child_one 从 父级的父级的 child_three 中删除
							if (null != $user_relationship['child_one']) {
								$old_new_childtwo_three = str_replace($user_relationship['child_one'], '', $old_pid_relationship['child_three']);
								$old_new_childtwo_three = implode(',', array_filter(explode(',', $old_new_childtwo_three)));
							} else {
								$old_new_childtwo_three = $old_pid_relationship['child_three'];
							}

							$old_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $old_user_pid)->update(['child_two' => $old_new_childtwo, 'child_three' => $old_new_childtwo_three]);

							//把所转移的用户从 上级的上级的上级  删除(如果user_id 的 old_pid—old_pid 的 pid != 0)

							$old_user_pid_pid = DB::table('user')->where('id', $old_user_pid)->find()['pid'];
							if (0 != $old_user_pid_pid) {

								$old_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $old_user_pid_pid)->find();

								$old_new_childthree = str_replace($user_id, '', $old_pid_pid_relationship['child_three']);
								$old_new_childthree = implode(',', array_filter(explode(',', $old_new_childthree)));

								$old_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $old_user_pid_pid)->update(['child_three' => $old_new_childthree]);
							}

						}

						//修改user pid
						$user_rs = DB::table('user')->where('id', $user_id)->update(['pid' => $new_pid]);

						//判断所转移的用户 ，之前是否在new_pid 的 其他层级.
						//dump($new_relationship_exist);die;

						if (false !== strpos($new_relationship_exist['child_two'], $user_id)) {
							//从child_two 删除
							$two = str_replace($user_id, '', $new_relationship_exist['child_two']);
							$two = implode(',', array_filter(explode(',', $two)));
							$two = $two != '' ? $two : null;
							$remove_rs = DB::name('new_relationship')->where('user_id', $new_pid)->update(['child_two' => $two]);

						}

						if (false !== strpos($new_relationship_exist['child_three'], $user_id)) {
							//从child_three 删除
							$three = str_replace($user_id, '', $new_relationship_exist['child_three']);
							$three = implode(',', array_filter(explode(',', $three)));
							$three = $three != '' ? $three : null;
							$remove_rs = DB::name('new_relationship')->where('user_id', $new_pid)->update(['child_three' => $three]);

						}
						$new_relationship_exist = DB::name('new_relationship')->where('user_id', $new_pid)->find();

						//dump($new_relationship_exist);die;
						//将user_id 放入新父级的child_one
						$new_relationship_new_childone = $new_relationship_exist['child_one'] != null ? $new_relationship_exist['child_one'] . ',' . $user_id : $user_id;

						$new_pid_rs = DB::name('new_relationship')->where('user_id', $new_pid)->update(['child_one' => $new_relationship_new_childone]);

						//将所转移的用户child_one放入新父级的child_two
						if (null != $user_relationship['child_one']) {

							$new_pid_childtwo = $new_relationship_exist['child_two'];
							$new_pid_childtwo = $new_pid_childtwo . ',' . $user_relationship['child_one'];
							$new_pid_childtwo = implode(',', array_filter(explode(',', $new_pid_childtwo)));

							$new_pid_rs = DB::name('new_relationship')->where('user_id', $new_pid)->update(['child_two' => $new_pid_childtwo]);

						}

						//将所转移的用户child_two放入新父级的child_three

						if (null != $user_relationship['child_two']) {

							$new_pid_childthree = $new_relationship_exist['child_three'];
							$new_pid_childthree = $new_pid_childthree . ',' . $user_relationship['child_two'];
							$new_pid_childthree = implode(',', array_filter(explode(',', $new_pid_childthree)));
							$new_pid_rs = DB::name('new_relationship')->where('user_id', $new_pid)->update(['child_three' => $new_pid_childthree]);

						}

						//查询新父级的上级
						$new_pid_pid = DB::table('user')->where('id', $new_pid)->find()['pid'];
						if (0 != $new_pid_pid) {
							//新父级的父级关系表
							$new_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid_pid)->find();

							//将所转移用户 放入新父级的父级child_two

							$new_pid_pid_newchildtwo = $new_pid_pid_relationship['child_two'] != null ? $new_pid_pid_relationship['child_two'] . ',' . $user_id : $user_id;

							//将所转移用户的child_one 放入新父级的父级child_three
							$new_pid_pid_newchildthree = $new_pid_pid_relationship['child_three'] != null ? $new_pid_pid_relationship['three'] . ',' . $user_relationship['child_one'] : $user_relationship['child_one'];

							$new_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $new_pid_pid)->update(['child_two' => $new_pid_pid_newchildtwo, 'child_three' => $new_pid_pid_newchildthree]);

							//查询新父级的上级的上级
							$new_pid_pid_pid = DB::table('user')->where('id', $new_pid_pid)->find()['pid'];

							if (0 != $new_pid_pid_pid) {
								//将所转移用户的  放入 新父级的父级的父级的 child_three

								//新父级的父级的关系表
								$new_pid_pid_pid_relationship = DB::table('new_relationship')->where('user_id', $new_pid_pid_pid)->find();

								$new_pid_pid_pid_newchildthree = $new_pid_pid_pid_relationship['child_three'] != null ? $new_pid_pid_pid_relationship['child_three'] . ',' . $user_id : $user_id;

								$new_pid_pid_pid_relationship_rs = DB::table('new_relationship')->where('user_id', $new_pid_pid_pid)->update(['child_three' => $new_pid_pid_pid_newchildthree]);
							}

						}

						Db::commit();
						return json(['error' => 0, 'msg' => '转移成功','name'=>$p_user['username']]);

					} catch (\Exception $e) {
						Db::rollback();
						return json(['error' => 1, 'msg' => '转移失败']);
					}

				}
			}

		}

	}

	public function change_rebate() {
		$rs = DB::table('new_relationship')->where('user_id', Request::param('user_id'))->update(['rebate_way' => Request::param('rebate_way')]);
		if ($rs) {
			return json(['error' => 0, 'msg' => '修改成功']);
		} else {
			return json(['error' => 1, 'msg' => '修改失败']);
		}
	}

	//查找子孙
	function getChildrenIds($sort_id) {

		$ids = '';

		$result = DB::table('user')->where('pid', $sort_id)->select();
		if ($result) {
			foreach ($result as $key => $val) {
				$ids .= ',' . $val['id'];
				$ids .= $this->getChildrenIds($val['id']);
			}
		}
		return $ids;
	}

	public function backuprelationship() {

		$tables = ['new_relationship'];
		$db = new Backup();

		$file = ['name' => date('Ymd-His'), 'part' => 1];
		$start = $db->setFile($file)->backupALL($tables, 0);
		if (0 == $start) {
		} else {
			return json(array('error' => 1, 'msg' => '用户转移中,备份失败,请重新转移'));
		}
	}

	public function ranklist() {

		$list = Db::table('user_rank')->paginate(30)->each(function ($item, $index) {
			$item['condition'] = $this->object_to_array(json_decode($item['condition']));
			$item['right'] = $this->object_to_array(json_decode($item['right']));
			return $item;
		});

		// dump($list);die;
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function rank_edit() {

		if (Request::method() == 'GET') {
			$rankinfo = Db::table('user_rank')->where('rank', Request::param('rank'))->find();
			$rankinfo['condition'] = $this->object_to_array(json_decode($rankinfo['condition']));
			$rankinfo['right'] = $this->object_to_array(json_decode($rankinfo['right']));
			$this->assign('rankinfo', $rankinfo);
			return $this->fetch();
		} else {

			$data['name'] = Request::param('name');
			$data['logo'] = Request::param('logo');
			$condition['recharge'] = Request::param('recharge') != '' ? Request::param('recharge') : 0;
			$condition['account'] = Request::param('account') != '' ? Request::param('account') : 0;
			$data['condition'] = json_encode($condition);
			$right['betting_max'] = Request::param('betting_max') != '' ? Request::param('betting_max') : 0;
			$right['betting_min'] = Request::param('betting_min') != '' ? Request::param('betting_min') : 0;
			$data['right'] = json_encode($right);

			$rs = Db::table('user_rank')->where('rank', Request::param('rank'))->update($data);
			if ($rs) {
				$this->success('修改成功', url('djycpgk/user/ranklist'));
			} else {
				$this->error('修改失败');
			}
		}

	}

	public function rank_add() {
		if (Request::method() == 'GET') {
			return $this->fetch();
		} else {

			$data['name'] = Request::param('name');
			$data['logo'] = Request::param('logo');
			$condition['recharge'] = Request::param('recharge') != '' ? Request::param('recharge') : 0;
			$condition['account'] = Request::param('account') != '' ? Request::param('account') : 0;
			$data['condition'] = json_encode($condition);
			$right['betting_max'] = Request::param('betting_max') != '' ? Request::param('betting_max') : 0;
			$right['betting_min'] = Request::param('betting_min') != '' ? Request::param('betting_min') : 0;
			$data['right'] = json_encode($right);

			$rs = Db::name('user_rank')->insert($data);
			if ($rs) {
				$this->success('添加成功', url('djycpgk/user/ranklist'));
			} else {
				$this->error('添加失败');
			}
		}
	}

	public function rank_del() {
		$rs = Db::table('user_rank')->where('rank', Request::param('rank'))->delete();
		if ($rs) {
			$this->success('删除成功', url('djycpgk/user/ranklist'));
		} else {
			$this->error('删除失败', url('djycpgk/user/ranklist'));
		}
	}

	public function hmdetail() {
		//彩票基本信息
		$basicdata = Db::table('betting')->where('id', input('get.betting_id'))->find();
		$basicdata['lottery_name'] = Db::table('lottery_config')->where('type', $basicdata['type'])->find()['name'];
		if ($basicdata['state'] == 1) {
			$basicdata['opencode'] = Db::table('lottery_code')->where('expect', $basicdata['expect'])->find()['content'];
		} else {
			$basicdata['opencode'] = '--';
		}
		//合买信息
		$hmdata = Db::table('betting_he')->where('betting_id=' . input('get.betting_id'))->find();

		$hmdata['jindu'] = round($hmdata['buy'] / $hmdata['all'] * 100, 2);

		$zhdata = DB::table('betting_zhui')->where('betting_id', input('get.betting_id'))->select();
		if (count($zhdata) != 0) {
			foreach ($zhdata as $key => $value) {
				$zhdata[$key]['content'] = Db::table('lottery_code')->where('expect', $value['expect'])->find()['content'];
			}
		}
		//dump($zhdata);die;
		//跟号信息
		$gendata = Db::table('betting_gen')->where('betting_id=' . input('get.betting_id'))->select();
		foreach ($gendata as $key => $value) {
			$gendata[$key]['username'] = Db::table('user')->where('id', $value['user_id'])->find()['username'];
		}

		//halt($gendata);
		// dump($zhdata);
		$this->assign('betting_cou', Request::param('betting_cou'));
		$this->assign('zhdata', $zhdata);
		$this->assign('basicdata', $basicdata);
		$this->assign('hmdata', $hmdata);
		$this->assign('gendata', $gendata);
		return $this->fetch();
	}

	public function zhDetail() {
		$basicdata = DB::table('betting')->where('id', input('get.betting_id'))->find();
		$pageParam['query']['betting_id'] = input('get.betting_id');
		//追号信息
		$zhdata = Db::table('betting_zhui')->where('betting_id', input('get.betting_id'))->paginate(20, true, $pageParam);
		$zhcount = Db::table('betting_zhui')->where('betting_id', input('get.betting_id'))->count();
		$this->assign('zh_count', $zhcount);
		$this->assign('basicdata', $basicdata);
		$this->assign('zhdata', $zhdata);
		return $this->fetch();
	}

	public function proxy_child() {


		$relationship = Db::table('new_relationship')->where('user_id', Request::param('user_id'))->find();
		$rates = Db::table('system_config')->where(['id' => ['id', 'in', ['22', '23', '24']]])->select();

		if (Request::param('start_time') != '' && Request::param('end_time') == '') {
			$capital_map['create_time'] = ['create_time', '>', strtotime(Request::param('start_time'))];

		}

		if (Request::param('start_time') == '' && Request::param('end_time') != '') {
			$capital_map['create_time'] = ['create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];
		}

		if (Request::param('start_time') != '' && Request::param('end_time') != '') {

			$capital_map['create_time'] = ['create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];
		}

		switch (Request::param('child_level')) {
		case '1':
			$child_ids = explode(',', $relationship['child_one']);
			$pecent_rate = $rates[0]['value'];
			break;
		case '2':
			$child_ids = explode(',', $relationship['child_two']);
			$pecent_rate = $rates[1]['value'];
			break;
		case '3':
			$child_ids = explode(',', $relationship['child_three']);
			$pecent_rate = $rates[2]['value'];
			break;
		default:
			# code...
			break;
		}

		$map['id'] = ['id', 'in', $child_ids];

		$list = Db::table('user')->field('id,username,money')->where($map)->select();
		foreach ($list as $key => $value) {
			$total_xz = 0;
			$total_zj = 0;
			$total_cz = 0;
			$total_tx = 0;
			$total_zs = 0;
			if (Request::param('start_time') != '' && Request::param('end_time') != '') {
				$capital_datas = Db::table('capital_detail')->where($capital_map)->where('user_id', $value['id'])->select();
			}else{
				$capital_datas = Db::table('capital_detail')->where('user_id', $value['id'])->select();
			}

			if (count($capital_datas) != 0) {
				foreach ($capital_datas as $k => $v) {
					if ($capital_datas[$k]['type'] == 0) {
						$total_xz += $v['money'];
					} elseif ($capital_datas[$k]['type'] == 3) {
						$total_zj += $v['money'];
					} elseif ($capital_datas[$k]['type'] == 7 || $capital_datas[$k]['type'] == 2) {
						$total_cz += $v['money'];
					} elseif ($capital_datas[$k]['type'] == 1) {
						$total_tx += $v['money'];
					} elseif ($capital_datas[$k]['type'] == 5) {
						$total_zs += $v['money'];
					}
				}
			}
			// dump($pecent_rate);
			$list[$key]['xz'] = $total_xz;
			$list[$key]['zj'] = $total_zj;
			$list[$key]['cz'] = $total_cz;
			$list[$key]['tx'] = $total_tx;
			$list[$key]['zs'] = $total_zs;
			$list[$key]['fc'] = sprintf("%.2f", ($total_xz) * $pecent_rate / 100);
		}

		//dump($list);die;
		$this->assign('list', $list);
		return $this->fetch();
	}

}
