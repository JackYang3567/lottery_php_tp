<?php
namespace app\djycpgk\controller;
use think\Auth;
use think\Controller;
use think\Db;
use think\facade\Request;
use think\facade\Session;

class Rbac extends Controller {

	function initialize() {

		$sess_auth = session::get('auth');
        if (!$sess_auth) {
            $this->error('非法访问！正在跳转登录页面！', url('djycpgk/login/index'));
//			$this->redirect('/djycpgk/login/index');
        }
//		session::delete('auth');
		//$this->success('退出成功！', url('djycpgk/Login/index'));
		//dump(session::get('auth'));
		if ($sess_auth['uid'] == 1) {

			//$str = Session::get('admin'); //username last_login_time

			$session_id = Db::table('admin')->where('uid', session::get('auth.uid'))->find()['session_id'];

			if (session::get('auth.session_id') != $session_id) {
				//dump(session::get());die;
				session::delete('auth');
				$this->error('您的账号已在其他地方登陆', url('djycpgk/login/index'));
			}
			return true;
		}

		$auth = new Auth();
		// dump($auth);die;
		if (!$auth->check(request()->module() . '/' . request()->controller() . '/' . request()->action(), $sess_auth['uid'])) {
			// $this->error('没有权限', url('djycpgk/index/childindex'));
			 // echo "<script> alert('没有权限');</script>"; 
			 echo  '<h1 style="text-align:center;color:red; ">没有权限</h1>';die();
		}

	}

}

?>

