<?php
namespace app\djycpgk\controller;
use think\Controller;
use think\Db;
use think\facade\Request;

class Article extends Rbac {

	public function index() {
		$paginate = 15;
		$list = DB::table('article')->paginate($paginate)->each(function ($item, $key) {
			$item['cate_name'] = DB::table('article_category')->where('id', $item['cat_id'])->find()['name'];
			return $item;
		
		});
		$this->assign('list', $list);
		return $this->fetch();
	}
	public function add() {
		if (Request::method() == 'GET') {
			$catelist = DB::table('article_category')->select();
			$this->assign('catelist', $catelist);
			$this->assign('date', date('Y-m-d').' 至 '.'2099-12-31');
			return $this->fetch();

		} else {
            $data = Request::post();
		    $ext = 'jpg';
		    if(!$data['img'])
            {
                $this->error('请上传一张公告封面');
            }
            $public_path = config('public_path');
            $upload = $public_path.'upload';
            if(!file_exists($upload))
            {
                mkdir($upload);
            }
            $gonggao = $upload.DIRECTORY_SEPARATOR.'gonggao';
            if(!file_exists($gonggao))
            {
                mkdir($gonggao);
            }
            $fileDir = $gonggao.DIRECTORY_SEPARATOR.date('Ymd');
            if(!file_exists($fileDir))
            {
                mkdir($fileDir);
            }
            $now = explode(' ',microtime());
            $filename = md5($now[0] + $now[1]).'.'.$ext;
            $newFile = $fileDir.DIRECTORY_SEPARATOR.$filename;
            $savePath = explode('public',$newFile);
            $savePath = $savePath[1];

            preg_match('/^(data:\s*image\/(\w+);base64,)/',$data['img'],$res);
            $uploadimg = file_put_contents($newFile, base64_decode(str_replace($res[1],'', $data['img'])));//
            if(!$uploadimg)
            {
                $this->error('封面上传失败');
            }

			$data['create_time'] = time();
			$data['content'] = htmlspecialchars_decode($data['content']);
			$time = explode(' 至 ',$data['date']);
			$data['start_time'] = strtotime($time[0]);
			$data['end_time'] = strtotime($time[1]);
			unset($data['img']);
			unset($data['date']);
			$data['logo'] = $savePath;
			$rs = DB::name('article')->insert($data);

			if ($rs) {
				$this->success('添加成功', url('djycpgk/article/index'));
			} else {
				$this->error('添加失败');
			}
		}
	}

	public function edit() {
	
		if (Request::method() == 'GET') {

			$article = DB::table('article')->where('id',Request::param('article_id'))->find();
			$this->assign('article', $article);
			$catelist = DB::table('article_category')->select();
			$this->assign('catelist', $catelist);
			return $this->fetch();
		} else {
			$data = Request::post();
            if($data['img'])
            {
                $ext = 'jpg';
                $public_path = config('public_path');
                $upload = $public_path.'upload';
                if(!file_exists($upload))
                {
                    mkdir($upload);
                }
                $gonggao = $upload.DIRECTORY_SEPARATOR.'gonggao';
                if(!file_exists($gonggao))
                {
                    mkdir($gonggao);
                }
                $fileDir = $gonggao.DIRECTORY_SEPARATOR.date('Ymd');
                if(!file_exists($fileDir))
                {
                    mkdir($fileDir);
                }
                $now = explode(' ',microtime());
                $filename = md5($now[0] + $now[1]).'.'.$ext;
                $newFile = $fileDir.DIRECTORY_SEPARATOR.$filename;
                $savePath = explode('public',$newFile);
                $savePath = $savePath[1];

                preg_match('/^(data:\s*image\/(\w+);base64,)/',$data['img'],$res);
                $uploadimg = file_put_contents($newFile, base64_decode(str_replace($res[1],'', $data['img'])));//
                if(!$uploadimg)
                {
                    $this->error('封面上传失败');
                }
                $data['logo'] = $savePath;
            }
            else
            {
                $data['logo'] = $data['img_url'];
            }

            $data['create_time'] = time();
            $data['content'] = htmlspecialchars_decode($data['content']);
            $time = explode(' 至 ',$data['date']);
            $data['start_time'] = strtotime($time[0]);
            $data['end_time'] = strtotime($time[1]);
            unset($data['img']);
            unset($data['date']);
            unset($data['img_url']);
			$rs = DB::name('article')->update($data);
			if ($rs) {
				$this->success('修改成功', url('djycpgk/article/edit', array('article_id' => Request::param('id'))));
			} else {
				$this->error('修改失败');
			}
		}
	}
	public function delete() {
		$rs = DB::table('article')->where('id', 'in', Request::post('article_id/a'))->delete();
		if ($rs) {
			return json(['error' => 0, '删除成功']);
		} else {
			return json(['error' => 1, '删除失败']);
		}
	}
	public function category() {
		$list = DB::table('article_category')->select();
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function addCategory() {
		if (Request::method() == 'GET') {
			return $this->fetch();
		} else {
			$rs = DB::name('article_category')->insert(['name' => Request::post('name')]);
			if ($rs) {
				$this->success('添加成功', url('djycpgk/article/category'));
			} else {
				$this->error('添加失败');
			}
		}
	}

	public function editCategory() {
		if (Request::method() == 'GET') {
			$category = DB::table('article_category')->where('id', Request::get('cate_id'))->find();
			$this->assign('cate', $category);
			return $this->fetch();
		} else {
			$rs = DB::name('article_category')->update(['name' => Request::post('name'), 'id' => Request::post('id')]);
			if ($rs) {
				$this->success('修改成功', url('djycpgk/article/category'));
			} else {
				$this->error('修改失败');
			}
		}
	}

	public function dltCategory() {
		$rs = DB::table('article_category')->where('id', Request::post('data_id'))->delete();
		if ($rs) {
			return json(['error' => 0, '删除成功']);
		} else {
			return json(['error' => 1, '删除失败']);
		}
	}
}