<?php
namespace app\gk\controller;
use think\Controller;
use think\Db;
use think\facade\Session;

class Dlauth extends Controller {

	function initialize() {

		$sess_auth = session::get('proxy');
		if (!$sess_auth) {
			$this->error('非法访问！正在跳转登录页面！', url('gk/login/index'));
		}
		if ($sess_auth['uid'] == 1) {

			$session_id = Db::table('proxy')->where('uid', session::get('proxy.uid'))->find()['session_id'];

			if (session::get('proxy.session_id') != $session_id) {

				session::delete('proxy');
				$this->error('您的账号已在其他地方登陆', url('gk/Login/index'));
			}

			return true;
		}
	}

}

?>

