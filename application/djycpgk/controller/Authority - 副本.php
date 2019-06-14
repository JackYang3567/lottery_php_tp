<?php
namespace app\djycpgk\controller;
use think\Controller;
use think\Db;
use think\facade\Request;

class Authority extends Rbac {
	
	public function xiougaimima(){
		$sr = DB::table('admin')->where('uid',Request::param('user_id'))->update(['password' => md5(Request::param('password'))]);
		if ($sr > 0) {
			return json(array('error' => 0, 'msg' => '删除成功'));
		} else {
			return json(array('error' => 1, 'msg' => '删除失败'));
		}
	}
	public function index() {
		$pageNum = 15;
		$list = DB::table('admin')->paginate($pageNum);
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function rulelist() {

		$pageNum = 15;
		$map = [];
		$pageParam = [];
		if (Request::param('keywords') != '') {
			$map['title'] = ['title', 'like', "%" . Request::param('keywords') . "%"];
			$pageParam['query']['keywords'] = Request::param('keywords');
			$this->assign('keywords', Request::param('keywords'));
		}

		if (Request::param('type') != '' && Request::param('type') != 0) {
			$map['type'] = Request::param('type');
			$pageParam['query']['type'] = Request::param('type');
			$this->assign('type', Request::param('type'));
		} else {
			$this->assign('type', 0);
		}

		$list = DB::table('think_auth_rule')->where($map)->paginate($pageNum, false, $pageParam)->each(function ($item, $key) {
			switch ($item['type']) {
			case '1':
				$item['type'] = '会员';
				return $item;
				break;
			case '2':
				$item['type'] = '彩票';
				return $item;
				break;
			case '3':
				$item['type'] = '系统';
				return $item;
				break;
			case '4':
				$item['type'] = '后台代理';
				return $item;
				break;
			case '5':
				$item['type'] = '文章';
				return $item;
				break;

			default:
				break;
			}
		});
		//  dump($list);die;
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function rule_add() {
		if (Request::method() == 'GET') {
			return $this->fetch();
		} else {
			$data = Request::post();
			$data['status'] = 1;
			$rs = DB::name('think_auth_rule')->insert($data);
			if ($rs) {
				$this->success('添加成功', url('djycpgk/authority/rulelist'));
			} else {
				$this->error('添加失败');
			}
		}
	}

	public function rule_edit() {
		if (Request::method() == 'GET') {
			$rule = DB::table('think_auth_rule')->where("id=" . Request::param('id'))->find();
			$this->assign('ruleinfo', $rule);
			return $this->fetch();
		} else {
		    $ss  = Request::post();
		    $ss['status'] = 1;
//		    dump($ss);die();
			$rs = DB::table('think_auth_rule')->where('id',Request::post('id'))->update($ss);
			if ($rs) {
				$this->success('修改成功', url('djycpgk/authority/rulelist'));
			} else {
				$this->error('修改失败');
			}
		}
	}
	public function rule_delete() {
		$rs = DB::table('think_auth_rule')->where('id', Request::post('data_id'))->delete();
		if ($rs > 0) {
			return json(array('error' => 0, 'msg' => '删除成功'));
		} else {
			return json(array('error' => 1, 'msg' => '删除失败'));
		}
	}

	public function grouplist() {
		$pageNum = 15;
		$list = DB::table('think_auth_group')->paginate($pageNum);
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function group_add() {

		$rulelist = DB::table('think_auth_rule')->field('id,title,type')->select();
		$rule_list = [];
		foreach ($rulelist as $key => $value) {
			$rule_list[$rulelist[$key]['type']][] = $value;
		}

		if (Request::method() == 'GET') {
			$this->assign('rulelist', $rule_list);
			return $this->fetch();
		} else {

			if (Request::param('rule_id/a') == null) {
				$this->error('请选择节点');
			}

			$data['title'] = Request::param('title');
			$data['rules'] = implode(',', Request::post('rule_id/a'));

			$rs = DB::name('think_auth_group')->insert($data);
			if ($rs) {
				$this->success('添加成功', url('djycpgk/authority/grouplist'));
			} else {
				$this->error('添加失败');
			}
		}
	}
	public function group_edit() {
		
		$rulelist = DB::table('think_auth_rule')->field('id,title,type')->select();
		$rule_list = [];
		foreach ($rulelist as $key => $value) {
			$rule_list[$rulelist[$key]['type']][] = $value;
		}
		$groupInfo = DB::table('think_auth_group')->where('id', Request::param('group_id'))->find();
		$groupInfo_list = explode(',', $groupInfo['rules']);
		if (Request::method() == 'GET') {
			$this->assign('groupinfo', $groupInfo);

			return $this->fetch();
		} else {
			if (Request::param('title/a') == null) {
				$this->error('名称不能为空');
			}
			$data['title'] = Request::param('title');
			$data['id'] = Request::param('group_id');

			$rs = DB::name('think_auth_group')->update($data);
			// dump(DB::name('think_auth_group')->getlastsql());die;
			if ($rs) {
				$this->success('修改成功', url('djycpgk/authority/grouplist'));
			} else {
				$this->error('修改失败');
			}
		}
	}

	public function proxy_add() { //添加管理员
		if (Request::method() == 'GET') {

			$grouplist = DB::table('think_auth_group')->field('id,title')->select();
			foreach ($grouplist as $key => $value) {
				if ($value['id'] ==11) {
					$grouplist[$key]['jiedian'] = DB::table('think_auth_rule')->where([['type','=',1],['status','=',1]])->field('id,title,type')->select();
				}
				if ($value['id'] ==12) {
					$grouplist[$key]['jiedian'] = DB::table('think_auth_rule')->where([['type','=',2],['status','=',1]])->field('id,title,type')->select();
				}
				if ($value['id'] ==13) {
					$grouplist[$key]['jiedian'] = DB::table('think_auth_rule')->where([['type','=',5],['status','=',1]])->field('id,title,type')->select();
				}
				if ($value['id'] ==14) {
					$grouplist[$key]['jiedian'] = DB::table('think_auth_rule')->where([['type','=',3],['status','=',1]])->field('id,title,type')->select();
				}
				if ($value['id'] ==15) {
					$grouplist[$key]['jiedian'] = DB::table('think_auth_rule')->where([['type','=',4],['status','=',1]])->field('id,title,type')->select();
				}
			}
			// dump($grouplist);
			$this->assign('grouplist', $grouplist);
			return $this->fetch();
		} else {
			$data_exist = DB::table('admin')->where("username = '" . Request::param('username') . "'")->find();
			if ($data_exist != null) {
				$this->error('账号已存在');
			}

			$data['username'] = Request::post('username');
			$data['password'] = md5(Request::post('password'));

            if (Request::post('group_id/a')==null){
                $this->error('没有勾选权限');
            }

			$rs = DB::name('admin')->insert($data);
			if ($rs) {

					foreach (Request::post('group_id/a') as $key => $value) {
						$access_data['uid'] = DB::table('admin')->where('username',Request::post('username'))->find()['uid'];
						$access_data['group_id'] = $value;
						if (empty(Request::post('rule_id/a')[$value]))  {
							$ss = '44,45,165';
						}else{
							$ss = implode(",", Request::post('rule_id/a')[$value]).',44,45,165';
						}
						$access_data['jiedian_id'] = $ss;
						$acc_rs = DB::name('think_auth_group_access')->insert($access_data);
					}
				$this->success('添加成功', url('djycpgk/authority/index'));
			} else {
				$this->error('添加失败');
			}
		}
	}

	public function admin_delete() {
		$rs = DB::table('admin')->where('uid', Request::post('data_id'))->delete();
		if ($rs > 0) {
			if (Request::post('data_id') != 1) {
				Db::table('think_auth_group_access')->where('uid', Request::post('data_id'))->delete();
			}
			return json(['error' => 0, 'msg' => '删除成功']);

		} else {

			return json(['error' => 1, 'msg' => '删除失败']);
		}
	}

	public function proxy_edit() {//修改管理员
		if (Request::method() == 'GET') {
			$adminInfo = DB::table('admin')->find(Request::get('admin_id'));//查询管理员 
			$grouplist = DB::table('think_auth_group')->field('id,title')->select();//获取管理员模块
			$accessInfo = DB::table('think_auth_group_access')->where('uid',Request::get('admin_id'))->select();//
			$arr = [];
			$arr2 =[
				'0'=>[],
				'1'=>[],
				'2'=>[],
				'3'=>[],
				'4'=>[],
			];
			foreach ($accessInfo as $ke => $val) {
				$arr[] = $val['group_id'];
				$arr2[$ke] = explode(',',$val['jiedian_id']);
			}

			foreach ($grouplist as $key => $value) {
				if ($value['id'] ==11) {
					$grouplist[$key]['jiedian'] = DB::table('think_auth_rule')->where([['type','=',1],['status','=',1]])->field('id,title,type')->select();
				}
				if ($value['id'] ==12) {
					$grouplist[$key]['jiedian'] = DB::table('think_auth_rule')->where([['type','=',2],['status','=',1]])->field('id,title,type')->select();
				}
				if ($value['id'] ==13) {
					$grouplist[$key]['jiedian'] = DB::table('think_auth_rule')->where([['type','=',5],['status','=',1]])->field('id,title,type')->select();
				}
				if ($value['id'] ==14) {
					$grouplist[$key]['jiedian'] = DB::table('think_auth_rule')->where([['type','=',3],['status','=',1]])->field('id,title,type')->select();
				}
				if ($value['id'] ==15) {
					$grouplist[$key]['jiedian'] = DB::table('think_auth_rule')->where([['type','=',4],['status','=',1]])->field('id,title,type')->select();
				}
			}
			// dump($arr2);
			$this->assign('arr', $arr);
			$this->assign('arr2', $arr2);
			$this->assign('accessInfo', $accessInfo);
			$this->assign('grouplist', $grouplist);
			$this->assign('adminInfo', $adminInfo);
			return $this->fetch();
		} else {
			$flag = 0;
            if (Request::post('group_id/a') ==null){
                $this->error('请选择权限');
            }
			if (Request::post('password') != '') {
				$data['uid'] = Request::post('uid');
				$data['username'] = Request::param('username');
				$data['password'] = md5(Request::post('password'));

				$admin_rs = DB::table('admin')->update($data);
				if ($admin_rs) {
					$flag++;
				}
			}

			$admin_rs1 = Db::table('admin')->where('uid', Request::param('uid'))->update(array('username' => Request::param('username')));
			if ($admin_rs1) {
				$flag++;
			}

			if (Request::post('uid') != 1) {

				Db::table('think_auth_group_access')->where('uid', Request::post('uid'))->delete();
//				 dump(Request::param());
//				 dump(Request::post('group_id/a'));exit();

				$ss = [['11'=>""],['12'=>""],['13'=>''],['14'=>''],['15'=>'']];
				foreach (Request::post('group_id/a') as $key => $value) {
					if (empty(Request::post('rule_id/a')[$value]))  {
						$ss = '44,45,165';
					}else{
						$ss = implode(",", Request::post('rule_id/a')[$value]).',44,45,165';
					}
					$access_data['uid'] = Request::post('uid');
					$access_data['group_id'] = $value;
					$access_data['jiedian_id'] = $ss;
					$acc_rs = DB::name('think_auth_group_access')->insert($access_data);
					if ($acc_rs) {
						$flag++;
					}
				}

			}

			if ($flag > 0) {
				$this->success('修改成功', url('djycpgk/authority/index'));
			} else {
				$this->error('修改失败');
			}
		}
	}

	public function proxylist() {
		$pageNum = 15;
		$map = ['type' => 1];

		$list = DB::table('proxy')->where($map)->paginate($pageNum)->each(function ($item, $key) {
			$item['children_num'] = DB::table('relationship')->where('prev', $item['uid'])->count();
			return $item;
		});
		$this->assign('list', $list);
		// dump($list);	
		return $this->fetch();
	}

	public function proxyadd() {
		if (Request::method() == 'GET') {
			return $this->fetch();
		} else {

			$data_exist = DB::table('proxy')->where('username', Request::param('username'))->find();
			if (null != $data_exist) {
				$this->error('该账号已存在');
			}
			$flag = 0;
			Db::startTrans();
			try {
				$data['username'] = Request::param('username');
				$data['password'] = md5(Request::param('password'));
				$data['child_num'] = Request::param('child_num');
				$data['create_time'] = time();
				$data['type'] = 1;
				$rs = DB::table('proxy')->insert($data);

				//查询uid
				$uid = Db::table('proxy')->where('username', Request::param('username'))->find()['uid'];

				//添加关系表
				$relation_data['top'] = $uid;
				$relation_data['userid'] = $uid;
				$relation_data['prev'] = 0;
				$relation_data['floor'] = 1;
				$relation_rs = Db::table('relationship')->insert($relation_data);

				if ($rs && $relation_rs) {
					$flag++;
				}
				Db::commit();
			} catch (\Exception $e) {
				Db::rollback();
			}

			if ($flag > 0) {
				$this->success('添加成功', url('djycpgk/authority/proxylist'));
			} else {
				$this->error('添加失败');
			}

		}
	}

	function proxyedit() {
		if (Request::method() == 'GET') {

			$proxyInfo = Db::table('proxy')->where('uid', Request::param('top_id'))->find();

			$this->assign('proxyInfo', $proxyInfo);
			return $this->fetch();

		} else {
			$data['uid'] = Request::param('uid');
			$data['username'] = Request::param('username');
			$data['child_num'] = Request::param('child_num');
			if (Request::param('password') != '') {
				$data['password'] = md5(Request::param('password'));
			}

			$rs = Db::table('proxy')->where('uid', Request::param('uid'))->update($data);

			if ($rs) {
				$type = Db::table('proxy')->where('uid', Request::param('uid'))->find()['type'];
				if ($type == 1) {
					$this->success('添加成功', url('djycpgk/authority/proxylist'));
				} else {
					$prev_id = Db::table('relationship')->where('userid', Request::param('uid'))->find()['prev'];

					$this->success('修改成功', url('djycpgk/authority/secondlist', ['top_id' => $prev_id]));
				}

			} else {
				$this->error('修改失败');
			}
		}
	}
	function proxyedit2() {
		if (Request::method() == 'GET') {

			$proxyInfo = Db::table('proxy')->where('uid', Request::param('top_id'))->find();
			$second_ids = Db::table('relationship')->field('userid')->where('prev', Request::param('top_id'))->select();
			$this->assign('proxyInfo', $proxyInfo);
			$this->assign('user_id', Request::param('user_id'));
			// dump(Request::param('user_id'));
			return $this->fetch();

		} else {
			$data['uid'] = Request::param('uid');
			$data['username'] = Request::param('username');
			$data['child_num'] = Request::param('child_num');
			if (Request::param('password') != '') {
				$data['password'] = md5(Request::param('password'));
			}

			$rs = Db::table('proxy')->where('uid', Request::param('uid'))->update($data);

			if ($rs) {
				$type = Db::table('proxy')->where('uid', Request::param('uid'))->find()['type'];
				if ($type == 1) {
					$this->success('添加成功', url('djycpgk/authority/proxylist'));
				} else {
					$prev_id = Db::table('relationship')->where('userid', Request::param('uid'))->find()['prev'];

					$this->success('修改成功', url('djycpgk/authority/secondlist', ['top_id' => $prev_id]));
				}

			} else {
				$this->error('修改失败');
			}
		}
	}

	public function proxy_delete() {

		//查询二级
		$second_num = Db::table('relationship')->where('prev', Request::param('data_id'))->select();
		$second_ids = [];
		$flag = 0;

		if (count($second_num) != 0) {
			foreach ($second_num as $key => $value) {
				$second_ids[] = $value['userid'];
				$child_ids = [];
				//查询普通用户
				$child_num = Db::table('relationship')->where('prev', $value['userid'])->select();

				if (count($child_num) != 0) {
					foreach ($child_num as $key => $value) {
						$child_ids[] = $value['userid'];

					}

					if (Db::table('user')->where(['id' => ['id', 'in', $child_ids]])->update(['proxy_id' => 0])) {
						$flag++;
					}
					if (Db::table('relationship')->where(['userid' => ['userid', 'in', $child_ids]])->delete()) {
						$flag++;
					}

				}

			}

			if (Db::table('relationship')->where(['userid' => ['userid', 'in', $second_ids]])->delete()) {
				$flag++;
			}

			if (Db::table('proxy')->where(['userid' => ['uid', 'in', $second_ids]])->delete()) {
				$flag++;
			}
		}

		if (Db::table('relationship')->where('userid', Request::param('data_id'))->delete()) {
			$flag++;
		}

		if (Db::table('proxy')->where('uid', Request::param('data_id'))->delete()) {
			$flag++;
		}

		if ($flag > 0) {
			return json(array('error' => 0, 'msg' => '移除成功'));
		} else {
			return json(array('error' => 1, 'msg' => '移除失败'));
		}

	}

	public function second_delete() {
		$child_num = Db::table('relationship')->where('prev', Request::param('data_id'))->select();
		$child_ids = [];
		$flag = 0;
		if (count($child_num) != 0) {
			foreach ($child_num as $key => $value) {
				$child_ids[] = $value['userid'];
			}

			if (Db::table('user')->where(['id' => ['id', 'in', $child_ids]])->update(['proxy_id' => 0])) {
				$flag++;
			}
			if (Db::table('relationship')->where(['userid' => ['userid', 'in', $child_ids]])->delete()) {
				$flag++;
			}

		}

		if (Db::table('relationship')->where('userid', Request::param('data_id'))->delete()) {
			$flag++;
		}

		if (Db::table('proxy')->where('uid', Request::param('data_id'))->delete()) {
			$flag++;
		}

		if ($flag > 0) {
			return json(array('error' => 0, 'msg' => '移除成功'));
		} else {
			return json(array('error' => 1, 'msg' => '移除失败'));
		}
	}

	public function ordinary_delete() {

		$rs = Db::table('user')->where(['id' => ['id', 'in', Request::param('recharge_list/a')]])->update(['proxy_id' => 0]);

		$rs1 = Db::table('relationship')->where(['userid' => ['userid', 'in', Request::param('recharge_list/a')]])->delete();

		if ($rs && $rs1) {
			return json(array('error' => 0, 'msg' => '移除成功'));
		} else {
			return json(array('error' => 1, 'msg' => '移除失败'));
		}
	}

	public function second_add() {
		if (Request::method() == 'GET') {
			$this->assign('top_id', Request::param('top_id'));
			return $this->fetch();
		} else {

			// dump(Request::param());
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
				$data['create_time'] = time();
				// $data['child_num'] 
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
				$this->success('添加成功', url('djycpgk/authority/proxylist'));
			} else {
				$this->error('添加失败');
			}

		}
	}

	public function secondlist() {

		$proxy_id = Request::param('top_id');
//        dump($proxy_id);
		//查询二级代理
		$second_datas = Db::table('relationship')->where('prev', $proxy_id)->select();

		$second_ids = [];
		foreach ($second_datas as $key => $value) {
			$second_ids[] = $value['userid'];
		}
		//dump($proxy_id);die;
		$pageNum = 15;
		$map['uid'] = ['uid', 'in', $second_ids];
//		if (Request::param('keywords') != '') {
//			$map['username'] = ['username', 'like', "%" . Request::param('keywords') . "%"];
//			$pageParam['query']['keywords'] = Request::param('keywords');
//		}

		$pageParam['query'] = ['top_id',Request::param('top_id')];
		$list = Db::table('proxy')->where($map)->paginate($pageNum, false, $config = ['query'=>array('top_id'=>Request::param('top_id'))])->each(function ($item, $key) {
			//查询下级数量
			$child_num = Db::table('relationship')->where('prev', $item['uid'])->select();
			// dump($child_num);
			// $item['child_num'] = count($child_num);

			$total_cz = 0;
			$total_tx = 0;
			$total_fs = 0;
			$total_fy = 0;
			$total_zs = 0;
			$total_xz = 0;
			$total_zj = 0;

			if ($child_num != 0) {
				//查询二级代理用户,循环用户

				$user_array = [];
				foreach ($child_num as $key => $value) {
					$user_array[] = $value['userid'];

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
					$pageParam['query']['start_time'] = Request::paordinarylistram('start_time');
					$this->assign('start_time', Request::param('start_time'));
					$pageParam['query']['end_time'] = Request::param('end_time');
					$this->assign('end_time', Request::param('end_time'));

				}
			

			}
			$item['children_num'] = DB::table('relationship')->where('prev', $item['uid'])->count();
			
			return $item;
		});
		$this->assign('top_id', Request::param('top_id'));
		$this->assign('list', $list);
		// dump($list);
		return $this->fetch();

	}

	public function ordinarylist() {

		$proxy_id = Request::param('second_id');


		
		$normal_ids = Db::table('relationship')->field('userid')->where(['prev' => $proxy_id, 'floor' => 3])->select();
	
		$ids = [];
		foreach ($normal_ids as $key => $value) {
			$ids[] = $value['userid'];
		}
		$pageNum = 15;
		$map['id'] = ['id', 'in', $ids];

		if (Request::param('keywords') != '') {
			$map['username'] = ['username', 'like', "%" . Request::param('keywords') . "%"];
			$pageParam['query']['keywords'] = Request::param('keywords');
		}

		$pageParam['query'] = [];

		
		if (Request::param('start_time') != null && Request::param('end_time') == null) {

				$map['create_time'] = ['create_time', '>', strtotime(Request::param('start_time'))];
			}

			if (Request::param('start_time') == '' && Request::param('end_time') != '') {
				$map['create_time'] = ['create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];
			}

			if (Request::param('start_time') != '' && Request::param('end_time') != '') {
				$map['create_time'] = ['create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];

			}
		$list = Db::table('user')->where($map)->paginate($pageNum, false,  $config = ['query'=>array('second_id'=>Request::param('second_id'))])->each(function ($item, $key) {
			$total_cz = 0;
			$total_tx = 0;
			$total_fs = 0;
			$total_fy = 0;
			$total_zs = 0;
			$total_xz = 0;
			$total_zj = 0;
			$map = ['user_id' => $item['id']];
			$map['type'] = ['type', 'in', ['0', '1', '2', '3', '5', '7', '8', '11']];
			$capital_datas = Db::table('capital_detail')->where($map)->select();
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
			

			return $item;
		});
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
		$start = 0;
		if (Request::param('start_time')==null  && Request::param('end_time')== null) {
			$start = date('Y-m-d', time());
		}

		$this->assign('start',$start);
		$this->assign('start_time', Request::param('start_time'));//开始时间
		$this->assign('end_time', Request::param('end_time'));//结束时间
		
		$this->assign('second_id', Request::param('second_id'));
		$this->assign('user_id', Request::param('user_id'));
		$this->assign('top_id', Request::param('top_id'));

		$this->assign('list', $list);
		$this->assign('zg', $zg);
		return $this->fetch();
	}
	public function ordinaryadd() {
		if (Request::method() == 'GET') {
			//查询普通用户
			$ordinarylist = DB::table('user')->field('id,username')->where(['proxy_id' => 0, 'type' => 0])->select();
			$child_num = Db::table('proxy')->where('uid', Request::param('second_id'))->find()['child_num'];
			$this->assign('child_num', $child_num);
			$this->assign('second_id', Request::param('second_id'));

			$this->assign('top_id', Request::param('top_id'));
			$this->assign('ordinarylist', $ordinarylist);
			return $this->fetch();
		} else {

			//添加关系表，修改用户表proxy_id
			$user_ids = input('post.send_list/a');
			$second_id = input('post.second_id');
			$top_id = input('post.top_id');

			Db::startTrans();
			try {
				$rs = Db::table('user')->where(['id' => ['id', 'in', $user_ids]])->update(['proxy_id' => $second_id]);
				foreach ($user_ids as $key => $value) {
					$data['top'] = $top_id;
					$data['prev'] = $second_id;
					$data['userid'] = $value;
					$data['floor'] = 3;
					Db::name('relationship')->insert($data);
				}
				Db::commit();
				return json(['error' => 0, 'msg' => '添加成功']);

			} catch (\Exception $e) {
				Db::rollback();
				return json(['error' => 1, 'msg' => '添加失败']);
			}
		}

	}

	public function proxylist_tj() {
		$pageNum = 15;
		$map = ['type' => 1];
		$pageParam['query'] = [];
		if (Request::param('keywords') != '') {
			$map['username'] = ['username', 'like', "%" . Request::param('keywords') . "%"];
			$pageParam['query']['keywords'] = Request::param('keywords');
		}

		$list = Db::table('proxy')->where($map)->paginate($pageNum, false, $pageParam)->each(function ($item, $key) {
			//查询下级数量
			$child_num = Db::table('relationship')->where('prev', $item['uid'])->select();
			$item['child_num'] = count($child_num);
			//查询所有普通用户
			$user_num = Db::table('relationship')->field('userid')->where(['top' => $item['uid'], 'floor' => 3])->select();

			$total_cz = 0;
			$total_tx = 0;
			$total_fs = 0;
			$total_fy = 0;
			$total_zs = 0;
			$total_xz = 0;
			$total_zj = 0;
			if (count($user_num) != 0) {
				$user_array = [];
				foreach ($user_num as $key => $value) {
					$user_array[] = $value['userid'];

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

				$map['user_id'] = ['user_id', 'in', $user_array];
				$map['type'] = ['type', 'in', ['0', '1', '2', '3', '5', '7', '8', '11']];
				//   dump($map);
				$capital_datas = Db::table('capital_detail')->where($map)->select();
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

			}

			$item['total_cz'] = $total_cz;
			$item['total_tx'] = $total_tx;
			$item['total_fs'] = $total_fs;
			$item['total_fy'] = $total_fy;
			$item['total_zs'] = $total_zs;
			$item['total_xz'] = $total_xz;
			$item['total_zj'] = $total_zj;
			return $item;

		});
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
//        dump($zg);
		$this->assign('keywords', Request::param('keywords'));
		//halt($list);
		$this->assign('zg', $zg);
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function secondlist_tj() {

		//查询二级代理
		$second_datas = Db::table('relationship')->where('prev', input('get.top_id'))->select();

		$second_ids = [];
		foreach ($second_datas as $key => $value) {
			$second_ids[] = $value['userid'];
		}

		$pageNum = 15;
		$map['uid'] = ['uid', 'in', $second_ids];
		if (Request::param('keywords') != '') {
			$map['username'] = ['username', 'like', "%" . Request::param('keywords') . "%"];
			$pageParam['query']['keywords'] = Request::param('keywords');
		}

		 // dump($map);

		$pageParam['query'] = ['top_id'=>Request::param('top_id')];
		$list = Db::table('proxy')->where($map)->paginate($pageNum, false, $pageParam)->each(function ($item, $key) {
			//查询下级数量
			$child_num = Db::table('relationship')->where('prev', $item['uid'])->select();
			$item['child_num'] = count($child_num);

			$total_cz = 0;
			$total_tx = 0;
			$total_fs = 0;
			$total_fy = 0;
			$total_zs = 0;
			$total_xz = 0;
			$total_zj = 0;

			if ($child_num != 0) {
				//查询二级代理用户,循环用户

				$user_array = [];
				foreach ($child_num as $key => $value) {
					$user_array[] = $value['userid'];

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
				$map['user_id'] = ['user_id', 'in', $user_array];
				$map['type'] = ['type', 'in', ['0', '1', '2', '3', '5', '7', '8', '11']];
				$capital_datas = Db::table('capital_detail')->where($map)->select();
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

			}

			$item['total_cz'] = $total_cz;
			$item['total_tx'] = $total_tx;
			$item['total_fs'] = $total_fs;
			$item['total_fy'] = $total_fy;
			$item['total_zs'] = $total_zs;
			$item['total_xz'] = $total_xz;
			$item['total_zj'] = $total_zj;
			return $item;
		});
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
		// dump(Request::param('top_id'));

		$this->assign('top_id', input('get.top_id'));
		$this->assign('keywords', Request::param('keywords'));
		$this->assign('list', $list);
		$this->assign('zg', $zg);
		return $this->fetch();
	}

	public function normal_tj() {

		//查询二级代理
		$normal_data = Db::table('relationship')->where('prev', input('get.second_id'))->select();

		$normal_ids = [];
		foreach ($normal_data as $key => $value) {
			$normal_ids[] = $value['userid'];
		}

		$pageNum = 15;
		$map['id'] = ['id', 'in', $normal_ids];

		if (Request::param('keywords') != '') {
			$map['username'] = ['username', 'like', "%" . Request::param('keywords') . "%"];
			$pageParam['query']['keywords'] = Request::param('keywords');
		}

        $pageParam['query'] = ['second_id'=>Request::param('second_id')];

		$list = Db::table('user')->where($map)->paginate($pageNum, false, $pageParam)->each(function ($item, $key) {

			$total_cz = 0;
			$total_tx = 0;
			$total_fs = 0;
			$total_fy = 0;
			$total_zs = 0;
			$total_xz = 0;
			$total_zj = 0;

			$map = ['user_id' => $item['id']];
			$map['type'] = ['type', 'in', ['0', '1', '2', '3', '5', '7', '8', '11']];

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

			$capital_datas = Db::table('capital_detail')->where($map)->select();
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

			return $item;
		});
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

		$this->assign('top_id', Request::param('top_id'));
		$this->assign('keywords', Request::param('keywords'));
		$this->assign('second_id', input('get.second_id'));
		$this->assign('list', $list);
		$this->assign('zg', $zg);
		return $this->fetch();
	}

	public function qrcode() {
		$uid = Request::param('id');
		$mop = ['name' => 'promote_url'];
		$urs = Db::table('system_config')->where($mop)->find()['value'];

        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

        $url = $http_type.$urs.'/#/in/ReAgent/'.$uid;
		$this->assign('url', $url);
		return $this->fetch();



	}

}
