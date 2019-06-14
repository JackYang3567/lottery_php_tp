<?php
namespace app\djycpgk\controller;
use think\Controller;
use think\Db;
use think\facade\Request;
class Match extends Controller{
	

	public function index() {  //首页 显示
			$arr = [];
			$list = DB::table('football_list')->order('over_time desc')->paginate(10);//查询数据库 paginate分页
			foreach ($list as $key => $value) {
			 	$arr[]= json_decode($list[$key]['content'],true);//建数据库的jion数据转换为array
			}
			$this->assign('list',$list);
			$this->assign('arr',$arr);
			return $this->fetch();
		
	}
	public function search() //搜索
	{

		if (empty(Request::param('start_time')) && empty(Request::param('end_time')) && empty(Request::param('content'))) {
			$this->error('数据异常','djycpgk/Match/index',3);die();  
		}else{

		}


			if (Request::param('start_time') != '' && Request::param('end_time') == '') {//开始时间不为空 结束时间为空

				$map['over_time'] = ['over_time', '>', strtotime(Request::param('start_time'))];
				
			}else if (Request::param('start_time') == '' && Request::param('end_time') != '') {//开始为空 结束不为空

				$map['over_time'] = ['over_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];

			}else if (Request::param('start_time') != '' && Request::param('end_time') != '') {//开始结束都不为空

				$map['over_time'] = ['over_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];
			}
			$content = Request::param('content');
			if ($content !=' ') {
				$map['content'] = ['content','like',"%$content%"];
			}
			
			
			$list = DB::table('football_list')->where($map)->order('over_time DESC')->select();
			$shuliang = DB::table('football_list')->where($map)->order('over_time DESC')->Count();
			$arr = [];
			foreach ($list as $key => $value) {
			 	$arr[]= json_decode($list[$key]['content'],true);//建数据库的jion数据转换为array	
			}
		
			if ($arr == ' ') {
				$this->assign('list',$list);
			}else{
				$this->assign('list',$list);
				$this->assign('arr',$arr);
			}
			
			// dump($arr);
			$this->assign('shuliang',$shuliang);
			$this->assign('start_time',Request::param('start_time'));
			$this->assign('end_time',Request::param('end_time'));
		

			
			return $this->fetch();
	
		
	}
	public function details() //战队详情 -AVG赔率 -历史战绩 等
	{
			$id = $this->request->param('id');
			$list = DB::table('football_list')->where('order_id','=',$id)->find();
			$arr = json_decode($list['content'],true);
			// dump($arr['odds']['bf']);
			// dump(explode(',',$arr['odds']['bf']));
			$this->assign('arr',$arr);
			return $this->fetch();

	}
	

}
