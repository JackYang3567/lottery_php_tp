<?php
namespace app\gk\controller;
use app\djycpgk\model\User as Usermodel;
use app\djycpgk\model\UserBankInfo;
use app\djycpgk\model\UserInfo;
use app\djycpgk\model\Proxy as Proxymodel;
use app\djycpgk\model\Relationship;
use think\Controller;
use think\Db;
use think\facade\Request;
use think\facade\Session;

class Proxy extends Dlauth {
    public function demo($search=0,$name=''){
        $proxy = session('proxy');
        if (Request::method() == 'POST'){
            $pageParam = [];
            $paginate = 18;
            $where = [];
            if ($search){
                if ($name){
                    $where[] = ['username','=',$name];
                }
            }
            $data =  Usermodel::field('id,username,create_time, active_time,active_ip,point,money,no_money,status,type,pid,proxy_id,group,CASE WHEN unix_timestamp(now()) - active_time < 1200 THEN active_time ELSE create_time END sort_time
        ')->where('type', 2)->where($where)->where('proxy_id',$proxy['uid'])->order('active_time','desc')->paginate($paginate,false,$pageParam);
            $data->append(['Parent','AccumulatedWinning','DianHua','SheBei','SuperiorUser','IsOnline']);
            return json($data);
        }else{
            return $this->fetch();
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
    public function zhDetail() {  //追号查看
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
    public function hmdetail() {//合买查看
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
	public function ewm(){
		
		return $this->fetch();
	}
	public function capitaldetail() { //投注明细
		$proxy_id = Session::get('proxy.uid');//获取代理的id

		if (Session::get('proxy.type') == 2) {//判断为 二级代理
			$normal_ids = Db::table('relationship')->where(['prev' => $proxy_id, 'floor' => 3])->select();

		} else {	//登录的是顶级代理
			$normal_ids = Db::table('relationship')->where(['top' => $proxy_id, 'floor' => 3])->select();//relationship 里面的代理关系
		}
		$ids = [];

		foreach ($normal_ids as $key => $value) {//便利 把
			$ids[] = $value['userid'];
		};

		$paginate = 25;
		$map = [];
		$pageParam['query'] = [];
		if (Request::param('type') != '') {
			$map['type'] = Request::param('type');
			$pageParam['query']['type'] = Request::param('type');
			$this->assign('type', Request::param('type'));
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
		$list = Db::table('capital_detail')->where($map)->where('user_id','in',$ids)->order('create_time DESC')->paginate($paginate, false, $pageParam)->each(function ($item, $key) {
			$userinfos = DB::table('user')->where('id', $item['user_id'])->find();

			$item['username'] = $userinfos['username'];
			$item['left_money'] = $userinfos['money'];
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
				$item['expend'] = $item['money'];
				$item['in_come'] = 0;
				break;
            case '16':
                $item['typename'] = '<b style="">红包</b>';
                $item['expend'] = 0;
                $item['in_come'] =$item['money'];
                break;
            case '17':
                $item['typename'] = '<b style="">个人红包</b>';
                $item['expend'] = 0;
                $item['in_come'] =$item['money'];
                break;
			default:
                $item['typename'] = '<b style="">未知</b>';
                $item['expend'] = 0;
                $item['in_come'] =0;
				break;
			}

			return $item;
		});

		// dump($list);
		$this->assign('list', $list);
		return $this->fetch();
	}
	public function normal_tj(){

		// dump(Request::param());
		$password = md5(Request::param('password'));
		$user_id = Request::param('user_id');
		$gx= DB::table('proxy')->where('uid',$user_id)->update(['password'=>$password]);
		if ($gx) {
			return json(['error' => 0, 'msg' => '修改成功']);
		}else{
			return json(['error' => 1, 'msg' => '修改失败']);
		}

	}
	public function Change_Password(){//修改密码
		if (Request::method() == 'POST') {
			$xmm = md5(Request::param('password'));
			$proxy_id = Session::get('proxy.uid');
			$sr = DB::table('proxy')->where('uid',$proxy_id)->update(['password'=>$xmm]);
			if($sr){  
			    $this->success('修改成功', 'gk/Proxy/Change_Password');  
			} else {  
			    $this->error('修改失败');  
			}  
		}else{

			return $this->fetch();

		}
	}
	public function mmyy()//判断密码是否正确
	{	

		$jm = md5(Request::param('password'));
		$proxy_id = Session::get('proxy.uid');
		$sjk = DB::table('proxy')->where('uid',$proxy_id)->find()['password'];
		if ($jm == $sjk) {
			return json(['error' => 0, 'msg' => ' ']);
		}else{
			return json(['error' => 1, 'msg' => '密码不正确']);
		}

	}
	public function Betting_detail(){//投注明细
		$proxy_id = Session::get('proxy.uid');//获取代理的id
		$pageNum = 15;

		if (Session::get('proxy.type') == 2) {//判断为 二级代理
			$normal_ids = Db::table('relationship')->where(['prev' => $proxy_id, 'floor' => 3])->select();

		} else {	//登录的是顶级代理
			$normal_ids = Db::table('relationship')->where(['top' => $proxy_id, 'floor' => 3])->select();//relationship 里面的代理关系
		}
		$ids = [];

		foreach ($normal_ids as $key => $value) {//便利 把
			$ids[] = $value['userid'];
		};
		
		// $pageNum = 15;
		
		
		if (Request::param('keywords') != '') {
			$map['username'] = ['username', 'like', "%" . Request::param('keywords') . "%"];
			$pageParam['query']['keywords'] = Request::param('keywords');
		}
		$pageParam['query'] = [];

		$ss = [];
		$new_relationship = Db::table('new_relationship')->where('user_id','in',$ids)->find();//获取 new_relationship 普通会员的 代理关系

			$arr[]  = explode(",",$new_relationship['child_one']);
			$arr[]  = explode(",",$new_relationship['child_two']); 
			$arr[]	= explode(",",$new_relationship['child_three']);

			foreach($arr as $key=>$value){ 

					foreach($value as $v){

						$ss[] = $v;
					}
				
			}
			foreach($ids as $key=>$ka){ 
				$ss[] = $ka;
			};
		
		
			$bwk = array_filter($ss);

			$map['id'] = ['id', 'in', $bwk];
			$u_id = [];
			$list = Db::table('user')->where($map)->select();
			$x_id = Request::param('x_id');
			if (isset($x_id)) {
				$u_id[] = Request::param('x_id');
				$u_name = Request::param('x_name');
				$this->assign('u_name', $u_name);
				$this->assign('u_id', $x_id);
			}else{
				foreach ($list as $key => $value) {
				$u_id[] = $value['id'];
				}
			}
			
			$map =[];

			if (Request::param('expect') != '') {

				$map['expect'] = array('expect', 'like', "%" . Request::param('expect') . "%");
				$pageParam['query']['expect'] = Request::param('expect');
				$this->assign('expect', Request::param('expect'));
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

			$tzmx = DB::table('betting')->where([['type','not in',[53,54,55,56]]])->where($map)->where('user_id','in',$u_id)->order('create_time', 'desc')->paginate($pageNum, false, $pageParam)->each(function ($item, $key) {
				$item['betting_count'] = count(json_decode($item['content'],true));
				$item = bettingFormat([$item], true)[0];
				$item['user_name'] = DB::table('user')->where('id',$item['user_id'])->find()['username'];
				$item['caizhong'] = DB::table('lottery_config')->where('type',$item['type'])->find()['name'];
				// $item['content'] = json_decode($item['content'],true);
				return $item;
			});
			// dump($tzmx);

		$this->assign('keywords', Request::param('keywords'));
		$this->assign('keywords', Request::param('keywords'));
		$this->assign('second_id', input('get.second_id'));
		$this->assign('list', $tzmx);
		// dump($list);
        return $this->fetch('Betting_detail');

	}

	public function swindex() {

		$User = new Usermodel(); // 实例化User对象
		$data = Request::get();
		$pageNum = 15;
		$pageParam = [];

		if (isset($data['keywords']) && $data['keywords'] != '') {
			$map1 = [
				['a.username', 'like', "%" . $data['keywords'] . "%"],
				['a.type', '=', 2],
				['a.proxy_id', '=', Session::get('proxy.uid')],

			];
			$map2 = [
				['a.username', 'like', "%" . $data['keywords'] . "%"],
				['a.type', '=', 2],
				['a.proxy_id', '=', Session::get('proxy.uid')],
			];
			$pageParam['query']['keywords'] = $data['keywords'];

			if (isset($data['sort_id'])) {
				$pageParam['query']['sort_id'] = $data['sort_id'];
				if ($data['sort_id'] == 0) {
					$list = $User->order('id DESC')->whereOr([$map1, $map2])->paginate($pageNum, false, $pageParam);
					$this->assign('sort_id', 1);
				} else {
					$list = $User->order('id ASC')->whereOr([$map1, $map2])->paginate($pageNum, false, $pageParam);
					$this->assign('sort_id', 0);
				}

			} else {
				if (isset($data['sort_money'])) {
					$pageParam['query']['sort_money'] = $data['sort_money'];
					if ($data['sort_money'] == 0) {

						$list = $User->order('money DESC')->whereOr([$map1, $map2])->paginate($pageNum, false, $pageParam);
						$this->assign('sort_money', 1);
					} else {

						$list = $User->order('money ASC')->whereOr([$map1, $map2])->paginate($pageNum, false, $pageParam);
						$this->assign('sort_money', 0);
					}
				} else {

					// $list = $User->whereOr([$map1,$map2])->paginate($pageNum, false, $pageParam);

					//print_r($map1);die;

					$list = $User->alias('a')->leftjoin('login_log b', 'b.user_id=a.id')->field('a.*,max(b.create_time)  as create_times')->group('a.id')->whereOr([$map1, $map2])->order('max(b.create_time) DESC')->paginate($pageNum, false, $pageParam)->each(function ($item, $key) {
						//echo 'dsad';die;
						$online_time = time() - 60 * 20;
						//dump($online_time);die;
						
						if ($item['create_times'] > $online_time) {
							$item['is_online'] = 1;
						} else {
							$item['is_online'] = 0;
						}
						return $item;
					});

				}
			}
			// dump($list);die;
			// dump($User->getlastsql());die;
			$this->assign('keywords', $data['keywords']);
		} else {
			//   echo 'dasda';die;
			// $map['type'] = ['type',0];
			// $map['proxy_id'] = Session::get('proxy.uid');
			if (isset($data['sort_id'])) {
				$pageParam['query']['sort_id'] = $data['sort_id'];
				if ($data['sort_id'] == 0) {
					$list = $User->order('id DESC')->where($map)->paginate($pageNum, false, $pageParam);
					$this->assign('sort_id', 1);
				} else {
					$list = $User->order('id ASC')->where($map)->paginate($pageNum, false, $pageParam);
					$this->assign('sort_id', 0);
				}
			} else {
				if (isset($data['sort_money'])) {
					$pageParam['query']['sort_money'] = $data['sort_money'];
					if ($data['sort_money'] == 0) {
						$list = $User->order('money DESC')->where($map)->paginate($pageNum, false, $pageParam);
						$this->assign('sort_money', 1);
					} else {

						$list = $User->order('money ASC')->where($map)->paginate($pageNum, false, $pageParam);
						$this->assign('sort_money', 0);
					}
				} else {
					$list = $User->alias('a')->leftjoin('login_log b', 'b.user_id=a.id')->field('a.*,max(b.create_time)  as create_times')->where(['a.type' => ['a.type', '=', 2], 'a.proxy_id' => Session::get('proxy.uid')])->group('a.id')->order('max(b.create_time) DESC')->paginate($pageNum, false, $pageParam)->each(function ($item, $key) {
						$online_time = time() - 60 * 20;
						// dump($item);
						if ($item['create_times'] > $online_time) {
							$item['is_online'] = 1;
						} else {
							$item['is_online'] = 0;
						}
						return $item;
					});
				}
			}

		}
		$list->each(function ($item, $key) {
			$item['parent'] = $item['proxy_id'] != 0 ? DB::table('proxy')->where('uid', $item['proxy_id'])->find()['username'] : 'admin';
			$item['last_login_ip'] = count(Db::table('login_log')->where('user_id', $item['id'])->order('create_time DESC')->limit(1)->select()) != 0 ? Db::table('login_log')->where('user_id', $item['id'])->order('create_time DESC')->limit(1)->select()[0]['ip'] : '0.0.0.0';
			$accumulated_winning = DB::table('accumulation')->where('user_id', $item['id'])->field('winning')->order('create_time DESC')->limit(1)->select();
			$item['accumulated_winning'] = count($accumulated_winning) != 0 ? $accumulated_winning[0]['winning'] : 0;
			return $item;
		});
		$this->assign('list', $list);
		return $this->fetch();

	}

	public function user_add() {

		if (Request::method() == 'GET') {
			return $this->fetch();
		} else {
			// $User = new User;
			$data = Request::post();
			$userdata['username'] = $data['username'];
			$userdata['password'] = md5($data['password']);
			$userdata['type'] = 2;
			$userdata['status'] = 0;
			$userdata['proxy_id'] = Session::get('proxy.uid');
			//$userdata['nickname'] = 'adminuser';
			$userdata['create_time'] = time();
			$data_exist = Usermodel::where("username='" . $data['username'] . "'")->find();
			if ($data_exist) {
                return json(['error' => -1, 'msg' => '用户名已存在']);
//				$this->error('用户名已存在');
			}

			$rs = Db::table('user')->insert($userdata);

			if ($rs) {
                return json(['error' => 1, 'msg' => '添加成功']);
//				$this->success('添加成功', url('gk/proxy/swindex'));
			} else {
                return json(['error' => -1, 'msg' => '添加失败']);
//				$this->error('添加失败');
			}
		}
	}

	public function info_edit() {

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
			return $this->fetch('user_info');
		} else {

			$data = Request::param();
			// dump($data);die;
			if ($data['flag'] == 'base') {
				//   $User = Usermodel::get($data['id']);
				if ($data['password'] != '') {
					$datas['password'] = md5($data['password']);
				}
				$datas['id'] = Request::param('id');

				$datas['status'] = Request::param('status');

				$base_rs = Db::table('user')->update($datas);
				if ($base_rs) {
                    return json(['error' => 1, 'msg' => '修改成功']);
//					$this->success('修改成功', url('gk/proxy/swindex'));
				} else {
                    return json(['error' => -1, 'msg' => '修改失败']);
//					$this->error('修改失败');
				}
			} else if ($data['flag'] == 'info') {
				$UserInfo = UserInfo::get($data['id']);

				if ($data['draw_password'] != '') {
					$data = Request::only(['email', 'qq', 'id_number', 'draw_password']);
					$data['draw_password'] = md5($data['draw_password']);
				} else {
					$data = Request::only(['email', 'qq', 'id_number']);
				}

				$extra_rs = $UserInfo->save($data, ['user_id' => Request::post('id')]);

				if ($extra_rs) {
					$this->success('修改成功', url('gk/proxy/swindex'));
				} else {
					$this->error('修改失败', url('gk/proxy/info_edit', ['user_id' => $UserInfo['user_id'], 'tab' => 2]));
				}

			} elseif ($data['flag'] == 'bank') {
				$UserBankInfo = UserBankInfo::where('user_id', $data['user_id'])->find();
				$data = Request::only(['username', 'name', 'number', 'branch']);
				$bank_rs = $UserBankInfo->save($data, ['user_id' => Request::post('user_id')]);
				if ($bank_rs) {
					$this->success('修改成功', url('gk/proxy/swindex'));
				} else {
					$this->error('修改失败', url('gk/proxy/info_edit', array('user_id' => $UserBankInfo['user_id'], 'tab' => 3)));
				}
			}
		}
	}

	public function renew() {

		$config['uid'] = Request::param('user_id');
		$config['type'] = Request::param('category');
		$config['money'] = Request::param('money');
		$rs = moneyAction($config);
		if ($rs['code'] == 1) {
			return json(['error' => 0, 'msg' => '操作成功']);
		} else {
			return json(['error' => 1, 'msg' => '操作失败']);
		}

	}

	public function zxzs($uid = 0){
		$normal_ids = Db::table('relationship')->where(['prev' => $uid, 'floor' => 3])->select();  //查询普通会员
		$ids = [];
		$arr = [];
		foreach ($normal_ids as $key => $value) {//便利 
			$arr[][] = $value['userid'];
			$ids[] = $value['userid'];
		};
			
		//统计所有二代下面所有的 存款	取款	返水	返佣	彩金	下注	中奖	盈利
			$ss = [];
			$new_relationship = Db::table('new_relationship')->where('user_id','in',$ids)->find();//获取 new_relationship 普通会员的 代理关系
			$arr[]  = explode(",",$new_relationship['child_one']);
			$arr[]  = explode(",",$new_relationship['child_two']); 
			$arr[]	= explode(",",$new_relationship['child_three']);

			foreach($arr as $key=>$value){ 
					foreach($value as $v){

						$ss[] = $v;
					}
			}
			
			$bwk = array_filter($ss);
			$max['a.id'] = ['a.id','in',$bwk];
			$liss =DB::table('user')->alias('a')
			->field('a.*,max(b.create_time)  as create_times')
			->leftjoin('login_log b', 'b.user_id=a.id')
			->group('b.user_id')
			->where($max)
			->select();

			$zx = 0;
			$sj = time()-60*20;
			foreach ($liss as $key => $value) {
				if ($value['create_times']> $sj) {
					$zx++;
				}
			}
			return $zx;
	}
	public function yg_tj($search=0,$name='',$start_time='',$end_time='') {
        if (Request::method() == 'POST') {
            $pageNum=18;
            $pageParam=[];
            $money = 0;
            $where = [];
            $tongji=['2'=>0,'5'=>0,'1'=>0,'0'=>0,'4'=>0,'7'=>0,'3'=>0,'8'=>0,'11'=>0]; //统计
            if ($search==1){
                if ($name){
                    $where[] = ['username','=',$name];
                }
            }
            if ($start_time !=''){
                $start_time = 'AND create_time > '.strtotime($start_time);
            }else{
                $start_time ='AND create_time >'.strtotime(date('Y-m-d 00:00:00'));
            }
            if ($end_time !=''){

                $end_time ='AND create_time < '.(strtotime($end_time)+24*60*60);
            }else{
                $end_time ='AND create_time <'. time();
            }
            $proxy_id = Session::get('proxy.uid');
            $map[] = ['uid', 'in', function($query) use($proxy_id) {
                $query->table('relationship')->field('userid')->where('prev', $proxy_id)->select();
            }];
            $list = Proxymodel::where($map)->field('uid,username')->where($where)->paginate($pageNum, false, $pageParam);
            $list->append(['YongHun','UserId']);
            $data = $list->toArray();
            foreach ($data['data'] as $k=>&$value){
                $value['zx_zs'] = $this->zxzs($value['uid']);
                if ($value['UserId'] ==[]){
                    $value['statistics'] = ['2'=>0,'5'=>0,'1'=>0,'0'=>0,'4'=>0,'7'=>0,'3'=>0,'8'=>0,'11'=>0];
                    continue;
                }
                $id = implode(",", $value['UserId']);
                $sql = "SELECT sum(money) as v, type FROM (SELECT * FROM capital_detail  WHERE  user_id in (".$id.")  $start_time  $end_time  ) t  GROUP BY type";
                $sc = [];
                $statistics = Db::query($sql);
                foreach ($statistics as $k) {
                    $sc[$k['type']] = $k['v'];
                }
                foreach ([2, 5, 1,0,4,7,3,8,11] as  $vc){
                    if (!isset($sc[$vc])){
                        $sc[$vc] = 0.00;
                    }
                }
                foreach ([2, 5, 1,0,4,7,3,8,11] as  $vc){
                    $tongji[$vc] +=  $sc[$vc];
                }
                $value['statistics'] = $sc;
            }
            return json($data);
        }else{
            return $this->fetch();
        }
	}
	public function ptyh(){
		$proxy_id = Request::param('uid');//获取代理的id
		// dump($proxy_id);
		$pageNum = 15;

			$normal_ids = Db::table('relationship')->where(['prev' => $proxy_id, 'floor' => 3])->select();

		$ids = [];

		foreach ($normal_ids as $key => $value) {//便利 把
			$ids[] = $value['userid'];
		};
		
		
		if (Request::param('keywords') != '') {
			$map['username'] = ['username', 'like', "%" . Request::param('keywords') . "%"];
			$pageParam['query']['keywords'] = Request::param('keywords');
		}
		$pageParam['query'] = [];

		$ss = [];
		$new_relationship = Db::table('new_relationship')->where('user_id','in',$ids)->find();//获取 new_relationship 普通会员的 代理关系


			$arr[]  = explode(",",$new_relationship['child_one']);
			$arr[]  = explode(",",$new_relationship['child_two']); 
			$arr[]	= explode(",",$new_relationship['child_three']);


			foreach($arr as $key=>$value){ 

					foreach($value as $v){

						$ss[] = $v;
					}
				
			}
			foreach($ids as $key=>$ka){ 
				$ss[] = $ka;
			};
		
		
			$bwk = array_filter($ss);

			$map[] = ['a.id', 'in', $bwk];
        if (Request::param('start_time') != '' && Request::param('end_time') == '') {
            $pageParam['query']['uid'] = Request::param('uid');
            $pageParam['query']['start_time'] = Request::param('start_time');
            $pageParam['query']['end_time'] = '';
            $this->assign('start_time', Request::param('start_time'));
        }

        if (Request::param('start_time') == '' && Request::param('end_time') != '') {
            $pageParam['query']['uid'] = Request::param('uid');
            $pageParam['query']['start_time'] = '';
            $pageParam['query']['end_time'] = Request::param('end_time');
            $this->assign('end_time', Request::param('end_time'));
        }

        if (Request::param('start_time') != '' && Request::param('end_time') != '') {
            $pageParam['query']['uid'] = Request::param('uid');
            $pageParam['query']['start_time'] = Request::param('start_time');
            $pageParam['query']['end_time'] = Request::param('end_time');
            $this->assign('start_time', Request::param('start_time'));
            $this->assign('end_time', Request::param('end_time'));
        }
			$list =  Db::table('user')
					->alias('a')
					->leftjoin('login_log b', 'b.user_id=a.id')
					->field('a.*,max(b.create_time)  as create_times')
					->group('a.id')->order('max(b.create_time) DESC')
					->where($map)->order('id')
					->paginate($pageNum, false, $pageParam)
					->each(function ($item, $key) {
				$online_time = time() - 60 * 20;
				$zx = DB::table('login_log')->field('max(create_time) as create_time')->where('user_id',$item['id'])->find();
				if ($zx['create_time'] > $online_time) {
					$item['is_online'] = 1;
				} else {
					$item['is_online'] = 0;
				}
			// $child_num = Db::table('user')->where('id',$item['id'] )->select();
			$sjx = 0;
			$sjx2 = 0;
			if ($item['pid'] >0) {
				$sjx = DB::table('user')->where('id',$item['pid'])->find()['username'];
			}
			if($item['proxy_id'] >0) {
				$sjx2 = DB::table('proxy')->where('uid',$item['proxy_id'])->find()['username'];
			}
			// dump($sjx);

			$total_cz = 0;
			$total_tx = 0;
			$total_fs = 0;
			$total_fy = 0;
			$total_zs = 0;
			$total_xz = 0;
			$total_zj = 0;

			$map = ['user_id' => $item['id']];
			$map['type'] = ['type', 'in', ['0', '1', '2', '3', '5', '7', '8', '11']];

			$zs = strtotime(date('Y-m-d 00:00:00',time()));
			$ws = $zs+3600*24;
				
			if (Request::param('start_time') == '' && Request::param('end_time') == '') {

				$map['create_time'] = ['create_time', 'between', [$zs,$ws]];

			}
            if (Request::param('start_time') == '' && Request::param('end_time') == '') {

                $map['create_time'] = ['create_time', 'between', [$zs,$ws]];

            }
            if (Request::param('start_time') != '' && Request::param('end_time') == '') {
                $map['create_time'] = ['create_time', '>', strtotime(Request::param('start_time'))];
            }

            if (Request::param('start_time') == '' && Request::param('end_time') != '') {
                $map['create_time'] = ['create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];
            }

            if (Request::param('start_time') != '' && Request::param('end_time') != '') {
                $map['create_time'] = ['create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];
            }
			// ['id', 'in', $ids]
			// $shangji = DB::table('relationship')->where([['floor','=',3],['prev','in',$ids]])->select()['prev'];
			// dump($shangji);exit();
			
			// dump($map);
			$capital_datas = Db::table('capital_detail')->where($map)->select();

			// dump(DB::table('capital_detail')->getlastsql());

			if (count($capital_datas) != 0) {
				foreach ($capital_datas as $k => $v) {
					if ($capital_datas[$k]['type'] == 0) {
						$total_xz += $v['money'];
					} elseif ($capital_datas[$k]['type'] == 1) {
						$total_tx += $v['money'];
					} elseif ($capital_datas[$k]['type'] == 7 || $capital_datas[$k]['type'] == 2) {
						$total_cz += $v['money'];
					} elseif ($capital_datas[$k]['type'] == 11) {
						$total_fs += $v['money'];
					} elseif ($capital_datas[$k]['type'] == 8) {
						$total_fy += $v['money'];
					} elseif ($capital_datas[$k]['type'] == 3) {
						$total_zj += $v['money'];
					} elseif ($capital_datas[$k]['type'] == 5) {
						$total_zs += $v['money'];
					}

				}
			}
			$item['total_cz'] = $total_cz;
			$item['total_tx'] = $total_tx;
			$item['total_fs'] = $total_fs;
			$item['total_fy'] = $total_fy;
			$item['total_zs'] = $total_zs;
			$item['total_xz'] = $total_xz;
			$item['total_zj'] = $total_zj;
			$item['sjx2'] = $sjx2;
			$item['sjx'] = $sjx;

			return $item;
		});
        //统计 当前这页的所有 金额
        $zg = ['total_cz'=>0,'total_tx'=>0,'total_fs'=>0,'total_fy'=>0,'total_zs'=>0,'total_xz'=>0,'total_zj'=>0];
        foreach ($list as $k =>$v){
            $zg['total_cz'] += $v['total_cz'];
            $zg['total_tx'] += $v['total_tx'];
            $zg['total_fs'] += $v['total_fs'];
            $zg['total_fy'] += $v['total_fy'];
            $zg['total_zs'] += $v['total_zs'];
            $zg['total_xz'] += $v['total_xz'];
            $zg['total_zj'] += $v['total_zj'];
        }
//        dump($list);
        $this->assign('uid', Request::param('uid'));
		$this->assign('keywords', Request::param('keywords'));
		$this->assign('keywords', Request::param('keywords'));
		$this->assign('second_id', input('get.second_id'));
		$this->assign('list', $list);
		$this->assign('zg', $zg);
		return $this->fetch();


	}
        public function hy_tj() {//用户统计

            $proxy_id = Session::get('proxy.uid');//获取代理的id

            if (Session::get('proxy.type') == 2) {//判断为 二级代理
                $normal_ids = Db::table('relationship')->where(['prev' => $proxy_id, 'floor' => 3])->select();

            } else {	//登录的是顶级代理
                $normal_ids = Db::table('relationship')->where(['top' => $proxy_id, 'floor' => 3])->select();//relationship 里面的代理关系

            }
            $ids = [];

            foreach ($normal_ids as $key => $value) {//便利 把
                $ids[] = $value['userid'];
            };
            $pageNum = 15;


            if (Request::param('keywords') != '') {
                $map['username'] = ['username', 'like', "%" . Request::param('keywords') . "%"];
                $pageParam['query']['keywords'] = Request::param('keywords');
            }
            $pageParam['query'] = [];

            $ss = [];
            $new_relationship = Db::table('new_relationship')->where('user_id','in',$ids)->find();//获取 new_relationship 普通会员的 代理关系

                $arr[]  = explode(",",$new_relationship['child_one']);
                $arr[]  = explode(",",$new_relationship['child_two']);
                $arr[]	= explode(",",$new_relationship['child_three']);

                foreach($arr as $key=>$value){

                        foreach($value as $v){

                            $ss[] = $v;
                        }

                }
                foreach($ids as $key=>$ka){
                    $ss[] = $ka;
                };

            $bwk = array_filter($ss); //查询所有二级代理下面的 普通用户 和 普通用户 下面的 二级；
    //				 dump($bwk);exit();
                $map[] = ['a.id', 'in', $bwk];

            if (Request::param('start_time') != '' && Request::param('end_time') == '') {
                $pageParam['query']['start_time'] = Request::param('start_time');
            }
            if (Request::param('start_time') == '' && Request::param('end_time') != '') {
                $pageParam['query']['end_time'] = Request::param('end_time');
            }
            if (Request::param('start_time') != '' && Request::param('end_time') != '') {
                $pageParam['query']['start_time'] = Request::param('start_time');
                $pageParam['query']['end_time'] = Request::param('end_time');
            }
                $list = Db::table('user')
                        ->alias('a')
                        ->leftjoin('login_log b', 'b.user_id=a.id')
                        ->field('a.*,max(b.create_time)  as create_times')
                        ->group('a.id')->order('max(b.create_time) DESC')
                        ->where($map)->order('id')
                        ->paginate($pageNum, false, $pageParam)
                        ->each(function ($item, $key) {
                    $online_time = time() - 60 * 20;
                    if ($item['create_times'] > $online_time) {
                        $item['is_online'] = 1;
                        // $zs++;

                    } else {
                        $item['is_online'] = 0;
                    }
                $sjx = 0;
                $sjx2 = 0;
                if ($item['pid'] >0) {
                    $sjx = DB::table('user')->where('id',$item['pid'])->find()['username'];

                }
                if($item['proxy_id'] >0) {
                    $sjx2 = DB::table('proxy')->where('uid',$item['proxy_id'])->find()['username'];
                }


                $total_cz = 0;
                $total_tx = 0;
                $total_fs = 0;
                $total_fy = 0;
                $total_zs = 0;
                $total_xz = 0;
                $total_zj = 0;

                $map = ['user_id' => $item['id']];
                $map['type'] = ['type', 'in', ['0', '1', '2', '3', '5', '7', '8', '11','15']];

                $zs = strtotime(date('Y-m-d 00:00:00',time()));
                $ws = $zs+3600*24;
                if (Request::param('start_time') == '' && Request::param('end_time') == '') {

                    $map['create_time'] = ['create_time', 'between', [$zs,$ws]];

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
                // ['id', 'in', $ids]
                // $shangji = DB::table('relationship')->where([['floor','=',3],['prev','in',$ids]])->select()['prev'];
                // dump($shangji);exit();

                $capital_datas = Db::table('capital_detail')->where($map)->select();



                if (count($capital_datas) != 0) {
                    foreach ($capital_datas as $k => $v) {
                        if ($capital_datas[$k]['type'] == 0) {
                            $total_xz += $v['money'];
                        } elseif ($capital_datas[$k]['type'] == 1) {
                            $total_tx += $v['money'];
                        } elseif ($capital_datas[$k]['type'] == 7 || $capital_datas[$k]['type'] == 2 || $capital_datas[$k]['type']==15) {
                            $total_cz += $v['money'];
                        } elseif ($capital_datas[$k]['type'] == 11) {
                            $total_fs += $v['money'];
                        } elseif ($capital_datas[$k]['type'] == 8) {
                            $total_fy += $v['money'];
                        } elseif ($capital_datas[$k]['type'] == 3) {
                            $total_zj += $v['money'];
                        } elseif ($capital_datas[$k]['type'] == 5) {
                            $total_zs += $v['money'];
                        }

                    }
                }
                $item['total_cz'] = $total_cz;
                $item['total_tx'] = $total_tx;
                $item['total_fs'] = $total_fs;
                $item['total_fy'] = $total_fy;
                $item['total_zs'] = $total_zs;
                $item['total_xz'] = $total_xz;
                $item['total_zj'] = $total_zj;
                $item['sjx2'] = $sjx2;
                $item['sjx'] = $sjx;

                return $item;
            });

            //统计 当前这页的所有 金额
            $zg = ['total_cz'=>0,'total_tx'=>0,'total_fs'=>0,'total_fy'=>0,'total_zs'=>0,'total_xz'=>0,'total_zj'=>0];
            foreach ($list as $k =>$v){
                $zg['total_cz'] += $v['total_cz'];
                $zg['total_tx'] += $v['total_tx'];
                $zg['total_fs'] += $v['total_fs'];
                $zg['total_fy'] += $v['total_fy'];
                $zg['total_zs'] += $v['total_zs'];
                $zg['total_xz'] += $v['total_xz'];
                $zg['total_zj'] += $v['total_zj'];
            }

            // dump($list);
            $this->assign('keywords', Request::param('keywords'));
            $this->assign('keywords', Request::param('keywords'));
            $this->assign('second_id', input('get.second_id'));
            $this->assign('list', $list);
            $this->assign('zg', $zg);
            return $this->fetch();

        }

	public function add_second() {
		if (Request::method() == 'GET') {
			$this->assign('top_id', Session::get('proxy.uid'));
			return $this->fetch();
		} else {

			$data_exist = DB::table('proxy')->where('username', Request::param('username'))->find();
			if (null != $data_exist) {
				$this->error('该账号已存在');
			}

			$child_num = Db::table('proxy')->where('uid', Request::param('top_id'))->find()['child_num'];
			$has_child_num = Db::table('relationship')->where('prev', Request::param('top_id'))->count();
			if (($has_child_num + 1) > $child_num) {
				$this->error('已超过允许的下线数量，无法添加');
			}

			$flag = 0;
			Db::startTrans();
			try {
				$data['username'] = Request::param('username');
				$data['password'] = md5(Request::param('password'));
				// $data['child_num'] = Request::param('child_num');
				$data['type'] = 2;
				$rs = DB::table('proxy')->insert($data);

				//查询uid
				$uid = Db::table('proxy')->where('username', Request::param('username'))->find()['uid'];

				//添加关系表
				$relation_data['top'] = Request::param('top_id');
				$relation_data['userid'] = $uid;
				$relation_data['prev'] = Request::param('top_id');
				$relation_data['floor'] = 2;
				$relation_rs = Db::table('relationship')->insert($relation_data);

				if ($rs && $relation_rs) {
					$flag++;
				}
				Db::commit();
			} catch (\Exception $e) {
				Db::rollback();

			}

			if ($flag > 0) {
				$this->success('添加成功', url('gk/proxy/yg_tj'));
			} else {
				$this->error('添加失败');
			}

		}
	}
	public function qrcode() {



		$uid = Request::param('id');
		$mop = ['name' => 'home_url'];
		$urs = DB::table('system_config')->where('id', '36')->find()['value'];

		$url = 'http://'.$urs.'/#/in/ReAgent/'.$uid;
		$this->assign('url', $url);
		return $this->fetch();
	}


}