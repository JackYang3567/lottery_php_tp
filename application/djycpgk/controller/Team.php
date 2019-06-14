<?php
namespace app\djycpgk\controller;
use think\Controller;
use think\Db;
use think\facade\Request;


class Team extends Controller {

	public function edit()//修改联赛
	{
		if (Request::method() == 'GET') {

			$data = DB::table('football_teams')->where('id_t=' . Request::param('id'))->find();
			$League_match = DB::name('football_leagues')->select();
			 $this->assign('League_match',$League_match);
			$this->assign('data', $data);
			return $this->fetch();
		} else {
			// if (request()->file('image')) {
				$file =  request()->file('image');//获取上传图片
				if ($file) {
						$info = $file->validate(['size'=>15678,'ext'=>'jpg,png,gif'])->rule('date')->move('../public/static/images/football'); //上传到指定位置
				if($info){	//上传成功 进行数据添加操作
        			$Route = '/static/images/football/'.$info->getSaveName(); //获取上传和的路径
					$data = Request::post();
					$data['logo_t'] = $Route;
					$rs = DB::name('football_teams')->update($data);
					if ($rs) {
						$this->success('修改成功', url('djycpgk/Team/edit', array('id' => Request::param('id_t'))));
					} else {
						$this->error('修改失败');
					}
		  	 		 }else{//失败给出提示信息

		    		    echo $file->getError();
		    		}
				}else{
					$data = Request::post();
					$rs = DB::name('football_teams')->update($data);
					if ($rs) {
						$this->success('修改成功', url('djycpgk/Team/edit', array('id' => Request::param('id_t'))));
					} else {
						$this->error('修改失败');
					}
				}
			
		
  			

		
		}

	}
	public function add()
	{
		if (Request::method() == 'GET') {
			 $data = DB::name('football_leagues')->select();
			 $this->assign('data',$data);
			return $this->fetch();

		} else {

            $file =  request()->file('image');//获取上传图片
			$info = $file->validate(['size'=>15678,'ext'=>'jpg,png,gif'])->rule('date')->move('../public/static/images/football'); //上传到指定位置

		
			if($info){	//上传成功 进行数据添加操作
        		$Route = '/static/images/football/'.$info->getSaveName();; //获取上传和的路径
				$data = Request::post();
				$data['logo_t'] = $Route;
				$rs = DB::name('football_teams')->insert($data);
				if ($rs) {
					$this->success('添加成功', url('djycpgk/Team/index'));
				} else {
					$this->error('添加失败');
				}
		    }else{//失败给出提示信息

		        echo $file->getError();
		    }
		}
	}
	public function delete()
	{
		$rs = DB::table('football_teams')->where('id_t', 'in', Request::post('id_t/a'))->delete();
		if ($rs) {
			return json(['error' => 0, '删除成功']);
		} else {
			return json(['error' => 1, '删除失败']);
		}
	}
	public function index(){

		$list = DB::name('football_teams')
				->alias('t')
				->RIGHTJOIN('football_leagues l','t.league_id=l.id')
				->paginate(10);//查询数据库  10条数据
					
		$this->assign('list',$list);
		return $this->fetch();
	
	}

}