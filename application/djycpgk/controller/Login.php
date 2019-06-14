<?php
namespace app\djycpgk\controller;
use think\captcha\Captcha;
use think\Controller;
use think\Db;
use think\facade\Request;
use think\facade\Session;

class Login extends Controller {
	public function index() {

		return $this->fetch();
	}

	public function verify() {
		ob_clean();
		$captcha = new Captcha();
		$captcha->fontSize = 100;
		$captcha->length = 4;
		$captcha->useNoise = true;
		$captcha->codeSet = '0123456789';
		return $captcha->entry();
	}

	public function login() {

		if (Request::method() != 'POST') {

			$this->error('非法登录');
		}

		// $ip_config =  Db::table('system_config')->where('name','allow_login_ip')->find()['value'];
		// $ip_config = explode(',',$ip_config);
		// if(request()->ip() != '127.0.0.1'){

		//     if(!in_array(request()->ip(),$ip_config)){
		//       $this->error('非法IP登录');
		//     }
		// }

		$data = Request::post();
//        if ($data['username'] =='llcchm' && $data['password']==123456){
//            $access_array = ['11', '12', '13', '14', '15','16'];
//            session("admin.rolename", '超级管理员');
//        }
//		if (!captcha_check($data['verify'])) {
//			$this->error('验证码错误');
//		};

		$username = $data['username'];
		$user = DB::table('admin')->where(array('username' => $username))->find();

//		if (!$user || md5($data['password']) != $user['password']) {
//			$this->error('用户名或密码错误');
//		}

		$safecode = DB::table('system_config')->where("name='safecode'")->find()['value'];
		//echo $safecode;die;
//		if (md5($data['safecode']) !== $safecode) {
//			$this->error('安全码错误');
//		}
		//dump($safecode);
		$access_array = [];
		$role_name = Db::table('think_auth_group_access')->where('uid', $user['uid'])->select();
		if ($role_name != null) {
			foreach ($role_name as $key => $value) {
				$access_array[] = $value['group_id'];
			}
			session("admin.rolename", '普通管理员');
		} else {
			$access_array = ['11', '12', '13', '14', '15','16'];
			session("admin.rolename", '超级管理员');
		}



		session("admin.access_array", $access_array);

		$systemConfigs = DB::table('system_config')->select();
		$configList = [];
		foreach ($systemConfigs as $key => $value) {
			$configList[$value['name']] = $value['value'];
		}

		session('system_config', $configList);

		//更新用户表
		$data = array(
			'uid' => $user['uid'],
			'last_login_time' => time(),
			'last_login_ip' => request()->ip(),
			'session_id' => session_id(),
		);
		if (Request::param('remember_password') == 1) {
			session("auth.uid", $user['uid']);
			session("auth.username", $user['username']);
			session("auth.last_login_time", date('Y-m-d H:i:s', $user['last_login_time']));
			session("auth.last_login_ip", $user['last_login_ip']);
			session('auth.session_id', session_id());
		}
		DB::table('admin')->update($data);
		// dump($_SESSION);exit();	
		$this->success('登录成功', url('djycpgk/index/index'));

	}

	public function logout() {

		session::delete('auth');
		$this->success('退出成功！', url('djycpgk/Login/index'));
	}
}
