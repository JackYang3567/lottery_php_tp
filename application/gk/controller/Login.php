<?php
namespace app\gk\controller;
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
		$data = Request::post();
		//dump($data);die;
		// if (!captcha_check($data['verify'])) {
		// 	$this->error('验证码错误');
		// };

		$username = $data['username'];
		$user = DB::table('proxy')->where(array('username' => $username))->find();

		if (!$user || md5($data['password']) != $user['password']) {
			$this->error('用户名或密码错误');
		}

		//验证是否已经登录了
		//dump(request()->ip() != $user['last_login_ip']);die;
		// if ((request()->ip() != $user['last_login_ip']) == true) {
		// 	//dump($user['last_login_time'] + 3600);die;
		// 	if (($user['last_login_time'] + 3600) > time()) {
		// 		$this->error('该用户已登录');
		// 	}

		// }

		//dump(session_id());die;
		// session('proxy', $user);
		session("proxy.uid", $user['uid']);
		session("proxy.type", $user['type']);
		session("proxy.username", $user['username']);
		session("proxy.last_login_time", date('Y-m-d H:i:s', $user['last_login_time']));
		session("proxy.last_login_ip", $user['last_login_ip']);
		session('proxy.session_id', session_id());
		//更新用户表
		$data = array(
			'uid' => $user['uid'],
			'last_login_time' => time(),
			'last_login_ip' => request()->ip(),
			'session_id' => session_id(),
		);

		DB::table('proxy')->update($data);
		$this->success('登录成功', url('gk/index/index'));

	}

	public function logout() {
		//dump(session::get('proxy'));die;
		Db::table('proxy')->where('uid', session::get('proxy.uid'))->update(['last_login_time' => '']);

		session::delete('proxy');
		$this->success('退出成功！', url('gk/Login/index'));
	}
}
