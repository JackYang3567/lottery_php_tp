<?php
namespace app\djycpgk\controller;
use think\Controller;
use think\Db;
use think\facade\Request;


class Football extends Controller {

	public function edit()//修改联赛
	{
		if (Request::method() == 'GET') {

			$data = DB::table('football_leagues')->where('id=' . Request::param('id'))->find();
			$this->assign('data', $data);
			return $this->fetch();
		} else {
			$validate = validate('Footballss');//连接 Footballss 验证		

			


  			$file =  request()->file('image');//获取上传图片


			

			if($file){	//判断是否有上传图片
				$info = $file->validate(['size'=>15678,'ext'=>'jpg,png,gif'])->rule('date')->move('../public/static/images/football'); //上传到指定位置

        		$Route = '/static/images/football/'.$info->getSaveName();; //获取上传和的路径
				$data = Request::post();
				$data['logo'] = $Route;

				if(!$validate->check($data)){//验证 数据
					$this->error($validate->getError());
				}else{
					$rs = DB::name('football_leagues')->update($data);
					if ($rs) {
						$this->success('修改成功', url('djycpgk/Football/edit', array('id' => Request::param('id'))));
					} else {
						$this->error('修改失败');
					}
				}

				
		    }else{//没有上传图片
		    	$data = Request::post();
		    	if(!$validate->check($data)){//验证 数据
					$this->error($validate->getError());
				}else{
					$rs = DB::name('football_leagues')->update($data);
					if ($rs) {
						$this->success('修改成功', url('djycpgk/Football/edit', array('id' => Request::param('id'))));
					} else {
						$this->error('修改失败');
					}
				}

		        
		    }

		
		}

	}

	public function index() {  //首页 显示	

		$list = DB::name('football_leagues')->paginate(10);//查询数据库 find 查询第一条数据
        $this->assign('list',$list);

		return $this->fetch();
	}
	public function add() //添加联赛
	{

		if (Request::method() == 'GET') {
			return $this->fetch();

		} else {

			$validate = validate('Footballss');//连接 Footballss 验证		
			

            $file =  request()->file('image');//获取上传图片
			
			if($file){	//上传成功 进行数据添加操作
				$info = $file->validate(['size'=>15678,'ext'=>'jpg,png,gif'])->rule('date')->move('../public/static/images/football'); //上传到指定位置
        		$Route = '/static/images/football/'.$info->getSaveName();; //获取上传和的路径
				$data = Request::post();
				$data['logo'] = $Route;
				if(!$validate->check($data)){//验证 数据
					$this->error($validate->getError());
				}else{
   					$rs = DB::name('football_leagues')->insert($data);
					if ($rs) {
						$this->success('添加成功', url('djycpgk/Football/index'));
					} else {
						$this->error('数据异常');
					}
				}
				
		    }else{//失败给出提示信息
		    	$this->error('logo不能为空');

		    }
		}
	}
	public function delete(){//删除

		$rs = DB::table('football_leagues')->where('id', 'in', Request::post('id/a'))->delete();
		if ($rs) {
			return json(['error' => 0, '删除成功']);
		} else {
			return json(['error' => 1, '删除失败']);
		}
	}

}
