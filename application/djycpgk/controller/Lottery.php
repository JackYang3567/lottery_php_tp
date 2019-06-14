<?php
namespace app\djycpgk\controller;
use app\home\controller\Game;
use app\prize\controller\Klsf;
use app\prize\controller\Ks;
use app\prize\controller\Lhc;
use app\prize\controller\P3d;
use app\prize\controller\Pc28;
use app\prize\controller\Pk10;
use app\prize\controller\Ssc;
use app\prize\controller\Syxw;
use app\prize\controller\Xync;
use app\home\model\LotteryConfig;
use Illuminate\Support\Debug\Dumper;
use think\Controller;
use think\Db;
use think\facade\Request;
use app\prize\controller\Brnn;
use app\home\controller\Lottery28;
use app\djycpgk\model\PresetLotteryCode;
use think\Exception;
use app\djycpgk\model\LotteryCode;

class Lottery extends Rbac {
    public function text(){
        return $this->fetch();
    }
	 public function  length($type){
        $haoma =  LotteryCode::where('type',$type)->find()['content'];
        $array = str_replace(',','',$haoma); //字符串替换
        return strlen($array); // 获取字符串 长度
    }
    public function  sfcz($data){ //判断 彩种 期号是否存在
        $ss = PresetLotteryCode::where('type',$data['type'])->where('expect',$data['expect'])->find();
        return $ss;
    }
	//批量预设
    public function batch($type='',$content='',$ks=0,$js=0){
		
         if (Request::method() == 'POST') {
            if (input('post.ts')==1){
                $rs = PresetLotteryCode::where('expect',input('post.expect'))->update(['content'=>input('post.content')]);
                if ($rs){
                    return json(['error' => 1, 'msg' => '修改成功']);
                }else{
                    return json(['error' => -1, 'msg' => '修改失败']);
                }
            }else{
                if ($type == -1){
                    return json(['error' => -1, 'msg' => '请选择彩种']);
                }
                if ($ks == '' || $js == ''){
                    return json(['error' => -1, 'msg' => '请选择开奖期数']);
                }
                if ($content == ''){
                    return json(['error' => -1, 'msg' => '请输入预测号码']);
                }
                $ss = explode(",", $content);
                $list = abs($js - $ks);
                if (count($ss) != $list+1){
                    return json(['error' => -1, 'msg' => '预测号码的数量，与预测的期号不匹配']);
                }
                Db::startTrans();//开启事务
                try {
                    for ($z=0 ; $z<=$list ; $z++){
						$arr = ['5','6','8','9','11','36','37','38','39','40','41','42','44','45','51'] ;
                        if (in_array($type, $arr)) {
                            $strArray=str_split($ss[$z],2);
                        }else{
                            $strArray=str_split($ss[$z],1);
                        }
                        $qh = join(",",$strArray);
                        $data = ['expect'=>$ks+$z,'content'=>$qh,'type'=>$type,'create_time'=>time()];
                        $changdu = $this->length($type);

                        if ($changdu != strlen($ss[$z])){//判断 预测号码的 位数是否正确
                            throw new Exception('1');//直接跳到 catch()
                        }
                        $cz = $this->sfcz($data); //判断期号是否存在
                        if ($cz['expect']){
                            throw new Exception('2');
                        }
                        PresetLotteryCode::insert($data);
                    }
                    Db::commit();
                    return json(['error' => 1, 'msg' => '添加成功']);
                } catch (\Exception $e) {
                    Db::rollback();
                    if ($e->getMessage() == 1){
                        return json(['error' => -1, 'msg' => '第'.($z+1).'个，预测号码位数不正确']);
                    }else if ($e->getMessage() == 2 ){
                        return json(['error' => -1, 'msg' => $cz['expect'].'：期号已经预测']);
                    }
                    return json(['error' => -1, 'msg' => '添加失败']);
                }

            }
        }else{
              $pageParam['query'] = [];
            $type=4;
            if (input('get.type')){
                $type = input('get.type');
            }

            $zd=Db::query("SELECT MAX(expect) zd FROM lottery_code WHERE `type`=".$type)[0]['zd'];
			
            $yc = Db::table('preset_lottery_code')
                ->union([
                    'SELECT * FROM lottery_code WHERE type='.$type.' ORDER BY expect desc  limit 5',
                ])
                ->where('expect','>',$zd)
                ->where('type','=',$type)
                ->order('expect desc')
               ->select();
            foreach ($yc as &$value){
                $value['type'] = LotteryConfig::field('name')->where('type',$value['type'])->find()['name'];
            }
			$arr = ['5','6','8','9','11','36','37','38','39','40','41','42','44','45','51'] ;
            $ss = LotteryConfig::field('name,type')->where('type','in',$arr)->select();
            $this->assign('yc', $yc);
            $this->assign('type', $type);
            $this->assign('caizhong', $ss);
            return $this->fetch();
        }
    }
	public function gx_haoma()
	{
		$rs = DB::table('lottery_code')
		      ->where([['expect','=',Request::param('qh')],['type','=',Request::param('type')]])
		      ->update(['content' => Request::param('hm')]);
		      if ($rs) {
					return json(['error' => 0, 'msg' => '修改成功']);
				} else {
					return json(['error' => 1, 'msg' => '修改失败']);
				}
	}

	public function guize(){
		if (Request::method() == 'POST') {
			$guize = DB::table('lottery_config')->where('type',Request::param('type_id'))->update(['explain' => Request::param('explain')]);;
			// dump(DB::table('lottery_config')->getlastsql());exit();
			if ($guize) {
				$this->success('保存成功', url('djycpgk/lottery/guize',array('type_id'=>Request::param('type_id'),'type_name'=>Request::param('type_name'))));
			}else{
				$this->error('保存失败');
			}
		}else{

		$guize = DB::table('lottery_config')->where('type',Request::param('type_id'))->find()['explain'];

		$this->assign('cate', Request::param('type_cate'));
		$this->assign('guize', $guize);
		$this->assign('type_id', Request::param('type_id'));
		$this->assign('type_name', Request::param('type_name'));
		return $this->fetch();
		}

	}
	public function room() {

        //dump($this->returnMoney());die;

        $lottery = Db::table('lottery_config')->field('type,name')->where(['type' => ['type', 'in', [24, 25, 26, 27]]])->select();

        if (Request::param('type') != '') {
            $list = DB::table('room')->where('type', Request::param('type'))->select();
            foreach ($list as &$value)
            {
                $value['content'] = json_decode($value['content'],true);
            }
            $this->assign('type', Request::param('type'));
        } else {
            $list = [];
            $this->assign('type', '');
        }
        $this->assign('lottery', $lottery);
        $this->assign('list', $list);
        return $this->fetch();
    }
	
	public function room_add() {

		if (Request::method() == 'GET') {
			return $this->fetch();
		} else {
			$data = Request::post();

			$rs = DB::table('room')->insert($data);
			if ($rs) {
				$this->success('添加成功', url('djycpgk/lottery/room'));
			} else {
				$this->error('添加失败');
			}
		}
	}

	public function room_edit() {
		if (Request::method() == 'GET') {
			   $roomInfo = DB::table('room')->where('room_id', Request::param('room_id'))->find();
            $roomInfo['content'] =  json_decode($roomInfo['content'],true);
            $this->assign('roomInfo', $roomInfo);
            return $this->fetch();
		} else {
			$data = Request::post();
			$rs = DB::name('room')->where('room_id',$data['room_id'])->update(['content' => json_encode($data['content'])]);

			if ($rs) {
				$this->success('修改成功', url('djycpgk/lottery/room'));
			} else {
				$this->error('修改失败');
			}
		}
	}

	public function room_odds() {
		$data = Db::table('lottery_config')->where('type', Request::param('type'))->find();
		$data['basic_config'] = $this->object_to_array(json_decode($data['basic_config']));
		if (Request::method() == 'GET') {
			//$items = $data['basic_config'][Request::param('type_key')]['items'];
			//halt($data['basic_config']);
			//print_r(Request::param('room_key'));
			$this->assign('data', $data['basic_config']);
			$this->assign('room_key', Request::param('room_key'));
			$this->assign('type_id', Request::param('type'));
			return $this->fetch();
		} else {
			$new_odds = Request::param('odds/a');
			$new_sd_odds = $new_odds["'sd'"];
			$new_color_odds = $new_odds["'color'"];
			$new_num_odds = $new_odds["'num'"];
			//halt($data['basic_config']);
			$sd_items = $data['basic_config']['sd']['items'];
			foreach ($sd_items as $key => $value) {
				$sd_items[$key]['key'] = $key;
			}
			//  dump($items);die;
			//以 0,1,2重新排序
			$change_sd_items = array_values($sd_items);
			foreach ($change_sd_items as $key => $value) {
				$change_sd_items[$key]['odds'][Request::param('room_key')] = $new_sd_odds[$key];

			}
			//指定key 排序
			$new_sd_items = $this->array_group_by($change_sd_items, 'key');

			foreach ($new_sd_items as $key => $value) {
				unset($new_sd_items[$key]['key']);
			}

			$color_items = $data['basic_config']['color']['items'];
			foreach ($color_items as $key => $value) {
				$color_items[$key]['key'] = $key;
			}
			//  dump($items);die;
			//以 0,1,2重新排序
			$change_color_items = array_values($color_items);
			foreach ($change_color_items as $key => $value) {
				$change_color_items[$key]['odds'][Request::param('room_key')] = $new_color_odds[$key];

			}
			//指定key 排序
			$new_color_items = $this->array_group_by($change_color_items, 'key');

			foreach ($new_color_items as $key => $value) {
				unset($new_color_items[$key]['key']);
			}

			$num_items = $data['basic_config']['num']['items'];
			foreach ($num_items as $key => $value) {
				$num_items[$key]['key'] = $key;
			}
			//  dump($items);die;
			//以 0,1,2重新排序
			$change_num_items = array_values($num_items);
			foreach ($change_num_items as $key => $value) {
				$change_num_items[$key]['odds'][Request::param('room_key')] = $new_num_odds[$key];

			}

			//指定key 排序
			$new_num_items = $this->array_group_by($change_num_items, 'key');

			foreach ($new_num_items as $key => $value) {
				unset($new_num_items[$key]['key']);
			}

			$data['basic_config']['sd']['items'] = $new_sd_items;
			$data['basic_config']['color']['items'] = $new_color_items;
			$data['basic_config']['num']['items'] = $new_num_items;

			$rs = Db::table('lottery_config')->where('type', Request::param('type'))->update(['basic_config' => json_encode($data['basic_config'])]);
			if ($rs) {
				$this->success('修改成功');
			} else {
				$this->error('修改失败');
			}

		}

	}

	public function defaultPrize() {
		if (Request::method() == 'GET') {
		$paginate = 5;
		$pageParam['query'] = [];
		
		if (Request::param('type') != '') {
			$map['type'] = Request::param('type');
			$pageParam['query']['type'] = Request::param('type');
			$this->assign('type', Request::param('type'));
		} else {
			$map['type'] = ['type','=',5];
		}
		//dump(Request::param('type')); $item['lottery_name'] = Db::table('lottery_config')->where('type',$item['type'])->find()['name'];

		$list1 = Db::table('preset_lottery_code')->where($map)->order('expect desc')->limit(5)->select();
		if (count($list1) != 0) {
			foreach ($list1 as $key => $value) {
				$list1[$key]['is_preset'] = 1;
			}
		}

		$list2 = Db::table('lottery_code')->where($map)->order('expect desc')->limit(5)->select();

		if (count($list2) != 0) {
			foreach ($list2 as $key => $value) {
				$list2[$key]['is_preset'] = 0;
				if (in_array($list2[$key]['type'], ['0', '1'])) {
					$list2[$key]['content'] = implode(',', $this->object_to_array(json_decode($list2[$key]['content']))['code']);
					// dump($list2[$key]['content']);die;
				}
			}

		}
		// dump($list2);

		$list = array_merge($list1, $list2);
		// dump($list);
		foreach ($list as $key => $value) {
			$list[$key]['lottery_name'] = Db::table('lottery_config')->where('type', $value['type'])->find()['name'];
		}
		//查询彩票
		$arr = ['5','6','8','9','11','36','37','38','39','40','41','42','44','45','51'] ;
		
		//$sid = [4,7,12,13,16,18,19,20,22,23,26,27,28,30,33,34,32,43,48,50,54,55,0,1,29,35,53,56];
		$lottery_config = Db::table('lottery_config')->where([['type','in',$arr]])->field('type,name')->order('type ASC')->select();
		$volume = [];
		 foreach ($list as $key => $row)
		 {
		    $volume[$key]  = $row['expect'];
		 }
		 array_multisort($volume, SORT_DESC, $list);
		 // dump($list);
		$this->assign('list', $list);

		$this->assign('lottery_array', $lottery_config);

		return $this->fetch();
		}else{
			$map = [];
			$map['type'] = ['type','=', Request::post('type')];
			$map['expect'] = ['expect','=', Request::post('expect')];
			// dump($map);
			$sc = Db::table('preset_lottery_code')->where($map)->delete();
			// dump($sc);
			if ($sc) {
				return json(['error' => 0, 'msg' => '删除成功']);
			} else {
				return json(['error' => 1, 'msg' => '操作失败']);
			}
		}
	}

	public function room_delete() {
		$rs = DB::table('room')->where('room_id', Request::post('data_id'))->delete();
		if ($rs) {
			return json(['error' => 0, 'msg' => '删除成功']);
		} else {
			return json(['error' => 1, 'msg' => '删除失败']);
		}
	}

	public function rule28() {
		$list = DB::table('lottery_rule')->select();
		$tab = Request::param('tab') != '' ? Request::param('tab') : 1;
		$this->assign('tab', $tab);
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function rule_edit() {

		$rule_ids = Request::post('id/a');
		$mutiples = Request::post('multiple/a');

		$tab = Request::post('tab');

		$rulelist = array_combine($rule_ids, $mutiples);
		$flag = 0;
		foreach ($rulelist as $key => $value) {
			$rs = DB::name('lottery_rule')->where('id', $key)->update(['multiple' => $value]);
			if ($rs > 0) {
				$flag++;
			}
		}
		if ($flag > 0) {
			$this->success('修改成功', url('djycpgk/lottery/rule28', array('tab' => $tab)));
		} else {
			$this->error('修改失败');
		}
	}

	public function statusSetting() {
//        dump(Request::param());
		$paginate = 16;
		$pageParam['query'] = [];
		$map = [];
		//$map['type'] =2;
			$lottery_trade = $this->object_to_array(json_decode(DB::table('system_config')->where('name', 'lottery_trade')->find()['value']));
//			dump($lottery_trade);
			foreach ($lottery_trade as $key => $value) {
				if (Request::param('category') == $value['name']) {
					$map['type'] = ['type', 'in', $value['data']];
				}
			}

        if (Request::param('category') != '') {
            $pageParam['query']['type'] = ['type', 'in', $value['data']];
			$this->assign('lottery_cate', Request::param('category'));
		}

		$list = DB::table('lottery_config')->where($map)->field('name,type,switch,time_config,note')->order('type ASC')->paginate($paginate, false, $pageParam)->each(function ($item, $key) {
			if ($item['time_config'] != null) {
				$item['time_config'] = $this->object_to_array(json_decode($item['time_config']));
			}
			//halt($item['time_config']);
			return $item;
		});
		// dump(Request::param('page'));


		// if ($Request::param('page')) {
		// 	$this->assign('page', $Request::param('page'));
		// }else{

		// }

//		 dump($list);
		$this->assign('list', $list);

        $sicai = [5, 7, 6, 8, 9, 11, 36, 37, 38, 9, 40, 41, 42, 44, 45, 51,52,57,58];
        $this->assign('sicai', $sicai);
		return $this->fetch();
	}
	public function time_setting() {
		$time_config['start_time'] = Request::param('start_time');
		$time_config['desc'] = Request::param('desc');
		$time_config['cha'] = Request::param('cha');
		$time_config['num'] = Request::param('number');
		$rs = DB::table('lottery_config')->where('type', Request::param('type_id'))->update(['time_config' => json_encode($time_config)]);
		if ($rs) {
			return json(['error' => 0, 'msg' => '修改成功']);
		} else {
			return json(['error' => 1, 'msg' => '修改失败']);
		}
	}

	protected function object_to_array($obj) {
		$obj = (array) $obj;

		foreach ($obj as $k => $v) {
			if (gettype($v) == 'resource') {
				return;
			}
			if (gettype($v) == 'object' || gettype($v) == 'array') {
				$obj[$k] = (array) $this->object_to_array($v);
			}
		}

		return $obj;
	}

	public function type_switch() {
		$rs = Db::table('lottery_config')->where('type', Request::post('config_id'))->update(['switch' => Request::post('switch_id')]);
		if ($rs) {
			return json(['error' => 0, 'msg' => '修改成功']);
		} else {
			return json(['error' => 1, 'msg' => '修改失败']);
		}
	}

	public function detail_config() {

		if (Request::method() == 'GET') {
//            dump(Request::param());

			$data = Db::table('lottery_config')->where('type', Request::param('type_id'))->find();
			$config = json_decode($data['basic_config'], true);
			$data['basic_config'] = $this->object_to_array(json_decode($data['basic_config']));
		
			$data['official'] = $this->object_to_array(json_decode($data['official']));
			$type = $data['type'];
			$this->assign('type_id', Request::param('type_id'));
			
			if (Request::param('type_id') == 1 || Request::param('type_id') == 0) {
				$this->assign('data', $data);
				$this->assign('name', $data['name']);
				return $this->fetch('game_config');
			}elseif ($data['official']){//判断是 pk10
			    $this->assign('is_official',1);
                if (!in_array($data['type'],[53, 54, 55,56])) {
                    foreach ($data['basic_config'] as $key => $value) {
                        $data['basic_config'][$key]['key_name'] = $key;
                    }
                    foreach ($data['official'] as $key => $value) {
                        $data['official'][$key]['key_name'] = $key;
                    }
                }
                $this->assign('cate',Request::param('type_cate'));
                $this->assign('type', $type);
                $this->assign('data', $data);
				//dump($data['official']);
                $this->assign('config', $config);
                $this->assign('name', $data['name']);
				
                return $this->fetch();
            } else {
				if (!in_array($data['type'],[53, 54, 55,56])) {
					foreach ($data['basic_config'] as $key => $value) {
						$data['basic_config'][$key]['key_name'] = $key;
					}
				}


				$this->assign('cate',Request::param('type_cate'));
				$this->assign('type', $type);

				$this->assign('data', $data);
				$this->assign('config', $config);
				$this->assign('name', $data['name']);
				return $this->fetch();
			}
		} else {
			
			$data = Db::table('lottery_config')->where('type', Request::param('type_id'))->find();
			$data['basic_config'] = $this->object_to_array(json_decode($data['basic_config']));
			if (Request::param('type_id') == 1 || Request::param('type_id') == 0) {

				foreach ($data['basic_config']['odds'] as $key => $value) {

					$data['basic_config']['odds'][$key]['name'] = Request::param('name/a')[$key];

					foreach ($value['num'] as $k => $v) {

						// $value['num'][$k] =  Request::param("{$k}/a")[$key];
						$data['basic_config']['odds'][$key]['num'][$k] = Request::param("{$k}/a")[$key];
					}

					// dump($data['basic_config']['odds']);die;

				}

				$rs = Db::table('lottery_config')->where('type', Request::param('type_id'))->update(['basic_config' => json_encode($data['basic_config'])]);
			}elseif (Request::param('type_gf') == 'gf' ) {//判断是 pk10
                $data['official'] = json_decode($data['official'],true);

                $names = Request::post('name/a');
                $switchs = Request::post('switch/a');

                //将key 丢进里面的一维数组
                foreach ($data['official'] as $key => $value) {
                    $data['official'][$key]['key'] = $key;
                }
                //重新 按0.1.2排序
                $new_configs = array_values($data['official']);

                foreach ($names as $key => $value) {
                    $new_configs[$key]['name'] = $names[$key];
                    $new_configs[$key]['switch'] = intval($switchs[$key + 1]);
                }
                // 以指定key 重新排序
                $new_configs = $this->array_group_by($new_configs, 'key');
                foreach ($new_configs as $key => $value) {
                    unset($new_configs[$key]['key']);
                }
                $rs = Db::table('lottery_config')->where('type', Request::param('type_id'))->update(['official' => json_encode($new_configs)]);

			} else {
				$names = Request::post('name/a');
				$switchs = Request::post('switch/a');
				$numbers = Request::post('number/a');

				//将key 丢进里面的一维数组
				foreach ($data['basic_config'] as $key => $value) {
					$data['basic_config'][$key]['key'] = $key;
				}

				//重新 按0.1.2排序
				$new_configs = array_values($data['basic_config']);
				foreach ($names as $key => $value) {
					$new_configs[$key]['name'] = $names[$key];
					$new_configs[$key]['switch'] = intval($switchs[$key + 1]);
					$new_configs[$key]['number'] = intval($numbers[$key]);
				}
				// 以指定key 重新排序
				$new_configs = $this->array_group_by($new_configs, 'key');
				foreach ($new_configs as $key => $value) {
					unset($new_configs[$key]['key']);
				}
				$rs = Db::table('lottery_config')->where('type', Request::param('type_id'))->update(['basic_config' => json_encode($new_configs)]);
			}

			if ($rs) {
				$this->success('修改成功');
			} else {
				$this->error('修改失败');
			}
		}
	}

	public function saveJsonConfig($type, $json)
	{
		$config = LotteryConfig::get($type);

		if (!$config->saveConfig($json)) {
			return ['code' => 1, 'msg' => '保存配置失败'];
		}
		return ['code' => 0, 'msg' => '保存配置成功'];
	}

	public function room_config() {

		$data = Db::table('lottery_config')->where('type', Request::param('type_id'))->find();
		$data['basic_config'] = $this->object_to_array(json_decode($data['basic_config']));
		if (Request::method() == 'GET') {
			$this->assign('type_id', Request::param('type_id'));
			$this->assign('data', $data);
			return $this->fetch();
		} else {
			// print_r(Request::param());die;
			$new_room = [];
			$new_odds = [];
			foreach (Request::param('name/a') as $key => $value) {
				$new_room["room" . ($key + 1)]['name'] = $value;
				$new_room["room" . ($key + 1)]['min'] = Request::param('min/a')[$key];
				$new_room["room" . ($key + 1)]['max'] = Request::param('max/a')[$key];
				$new_odds["room" . ($key + 1)] = 1;
			}
			//$new_odds = array_keys($new_odds);
			foreach ($data['basic_config']['odds'] as $key => $value) {
				//$new_roms = array_intersect(array_keys($value['num']),$new_odds);
				//dump($new_roms);die;
				//$cha_array = array_diff($new_odds,array_keys($value['num']));
				$array2 = [];
				foreach ($new_odds as $k => $v) {
					if (isset($data['basic_config']['odds'][$key]['num'][$k])) {
						$array2[$k] = $data['basic_config']['odds'][$key]['num'][$k];
					} else {
						$array2[$k] = 1;
					}
				}
				//  foreach ($new_roms as $vv) {
				//        $array2[$vv] = $data['basic_config']['odds'][$key]['num'][$vv];
				//  }
				// foreach ($cha_array as  $vvv) {
				//     $array2[$vvv] = 1;
				// }
				$data['basic_config']['odds'][$key]['num'] = $array2;
			}
			$data['basic_config']['room'] = $new_room;
			$rs = Db::table('lottery_config')->where('type', Request::param('type_id'))->update(['basic_config' => json_encode($data['basic_config'])]);
			if ($rs) {
				return json(['error' => 0, 'msg' => '修改成功']);
			} else {
				return json(['error' => 1, 'msg' => '修改失败']);
			}
		}
	}

	public function batch_config() {

		$new_da = Request::param('da');
		$new_dan = Request::param('dan');
		$new_x = Request::param('x');
		$new_s = Request::param('s');
		$new_code = Request::param('code');
        if (Request::param('type_id') == 52){
            $new_ds = Request::param('ds');
            $new_dd = Request::param('dd');
            $new_xs = Request::param('xs');
            $new_xd = Request::param('xd');
        }else{
            $new_l = Request::param('l');
            $new_hu = Request::param('hu');
        }

		$lottery_config = DB::table('lottery_config')->where('type', Request::param('type_id'))->find()['basic_config'];
		$lottery_config = $this->object_to_array(json_decode($lottery_config));
		$new_config = [];

		foreach ($lottery_config as $key => $value) {
			$new_config[$key] = $value;
			foreach ($value['items'] as $k => $val) {
                $new_config[$key]['items'][$k]['odds'] =  $new_da != '' ? $new_da : $val['odds'];

				if ($k == 'da') {
					$new_config[$key]['items'][$k]['odds'] = $new_da != '' ? $new_da : $val['odds'];
				}
				if ($k == 'x') {
					$new_config[$key]['items'][$k]['odds'] = $new_x != '' ? $new_x : $val['odds'];
				}
				if ($k == 'dan') {
					$new_config[$key]['items'][$k]['odds'] = $new_dan != '' ? $new_dan : $val['odds'];
				}
				if ($k == 's') {
					$new_config[$key]['items'][$k]['odds'] = $new_s != '' ? $new_s : $val['odds'];
				}

                if (Request::param('type_id') == 52){
                    if ($k == 'ds') {
                        $new_config[$key]['items'][$k]['odds'] = $new_ds != '' ? $new_ds : $val['odds'];
                    }
                    if ($k == 'dd') {
                        $new_config[$key]['items'][$k]['odds'] = $new_dd != '' ? $new_dd : $val['odds'];
                    }
                    if ($k == 'xs') {
                        $new_config[$key]['items'][$k]['odds'] = $new_xs != '' ? $new_xs : $val['odds'];
                    }
                    if ($k == 'xd') {
                        $new_config[$key]['items'][$k]['odds'] = $new_xd != '' ? $new_xd : $val['odds'];
                    }
                }else{
                    if ($k == 'l') {
                        $new_config[$key]['items'][$k]['odds'] = $new_l != '' ? $new_l : $val['odds'];
                    }
                    if ($k == 'hu') {
                        $new_config[$key]['items'][$k]['odds'] = $new_hu != '' ? $new_hu : $val['odds'];
                    }
                }




				if (strpos($k, 'code_') !== false) {

					$new_config[$key]['items'][$k]['odds'] = $new_code != '' ? $new_code : $val['odds'];
				}
			}
		}
		// dump($new_config);exit();
		$rs = DB::table('lottery_config')->where('type', Request::param('type_id'))->update(['basic_config' => json_encode($new_config)]);

		if ($rs) {
			$this->success('设置成功');
		} else {
			$this->error('设置失败');
		}
	}

	public function cate_odds_setting() {
		$new_da = Request::param('da');
		$new_dan = Request::param('dan');
		$new_x = Request::param('x');
		$new_s = Request::param('s');
		$new_code = Request::param('code');
		$long = Request::param('long');
		$hu = Request::param('hu');
		$map = [];
		$flag = 0;
		$lottery_trade = $this->object_to_array(json_decode(DB::table('system_config')->where('name', 'lottery_trade')->find()['value']));
		foreach ($lottery_trade as $key => $value) {
			if (Request::param('cate') == $value['name']) {
				$map['type'] = ['type', 'in', $value['data']];
			}
		}
		// dump($map);exit();

		$lotterys = DB::table('lottery_config')->where($map)->select();
		// dump($lotterys);die;
		foreach ($lotterys as $a => $b) {

			$lottery_config = $this->object_to_array(json_decode($b['basic_config']));
			$new_config = [];
			foreach ($lottery_config as $key => $value) {
				$new_config[$key] = $value;
				foreach ($value['items'] as $k => $val) {
					if ($k == 'da') {
						$new_config[$key]['items'][$k]['odds'] = $new_da != '' ? $new_da : $val['odds'];
					}
					if ($k == 'x') {
						$new_config[$key]['items'][$k]['odds'] = $new_x != '' ? $new_x : $val['odds'];
					}
					if ($k == 'dan') {
						$new_config[$key]['items'][$k]['odds'] = $new_dan != '' ? $new_dan : $val['odds'];
					}
					if ($k == 's') {
						$new_config[$key]['items'][$k]['odds'] = $new_s != '' ? $new_s : $val['odds'];
					}
					if ($k == 'hu') {
						$new_config[$key]['items'][$k]['odds'] = $hu != '' ? $hu : $val['odds'];
					}
					if ($k == 'l') {
						$new_config[$key]['items'][$k]['odds'] = $long != '' ? $long : $val['odds'];
					}
					if (strpos($k, 'code_') !== false) {

						$new_config[$key]['items'][$k]['odds'] = $new_code != '' ? $new_code : $val['odds'];
					}
				}
			}
			// dump($new_config);exit();
			$rs = DB::table('lottery_config')->where('type', $b['type'])->update(['basic_config' => json_encode($new_config)]);

			if ($rs) {
				$flag++;
			}

		}

		if ($flag > 0) {
			return json(['error' => 0, 'msg' => '修改成功']);
		} else {
			return json(['error' => 1, 'msg' => '修改成功']);
		}

	}

	public function odds_setting() {

		if (Request::method() == 'GET') {
			//dump(input('get.'));
		    if (input('type_gf') == 'gf'){
                $data = Db::table('lottery_config')->where('type', Request::param('type_id'))->find();
                $data['official'] = $this->object_to_array(json_decode($data['official']));
                $items = $data['official'][Request::param('type_key')]['items'];
                $this->assign('data', $items);
                $this->assign('type_key', Request::param('type_key'));
                $this->assign('type_id', Request::param('type_id'));
				$this->assign('type_gf', Request::param('type_gf'));
				
            }else{
				
                $data = Db::table('lottery_config')->where('type', Request::param('type_id'))->find();
                $data['basic_config'] = $this->object_to_array(json_decode($data['basic_config']));
                $items = $data['basic_config'][Request::param('type_key')]['items'];
                $this->assign('data', $items);
                $this->assign('type_key', Request::param('type_key'));
                $this->assign('type_id', Request::param('type_id'));
				
				
            }
			return $this->fetch();
		} else {
			// if (Request::param('type_id')==21) {
			// }else{
			$data = Db::table('lottery_config')->where('type', Request::param('type_id'))->find();
			$data['basic_config'] = $this->object_to_array(json_decode($data['basic_config']));
			$data['official'] = $this->object_to_array(json_decode($data['official']));
			$name = Request::param('name/a');
			$odds = Request::param('odds/a');
			if (in_array(Request::param('type_id'), ['24', '25', '26', '27', '21','57','58'])) {
				$odds = array_values($odds);
			}

			$switchs = Request::param('switch/a');
			//表单提交的数据
			$new_datas = [];
			foreach ($name as $key => $value) {
				$new_datas[$key]['name'] = $name[$key];
				$new_datas[$key]['odds'] = $odds[$key];
				$new_datas[$key]['switch'] = $switchs[($key + 1)];
			}
			
			//dump(input('post.'));
			//die;
            if (  input('type_gf')== 'gf'  ){
				//dump(input('post.'));
                //把key 加入 value 里
				//dump(input('type_key'));
                $items = $data['official'][Request::param('type_key')]['items'];
                foreach ($items as $key => $value) {
                    $items[$key]['key'] = $key;
                }
                //以 0,1,2重新排序
                $change_items = array_values($items);
                foreach ($change_items as $key => $value) {
                    $change_items[$key]['name'] = $new_datas[$key]['name'];
                    $change_items[$key]['odds'] = $new_datas[$key]['odds'];
                    $change_items[$key]['switch'] = intval($new_datas[$key]['switch']);
                }
                //指定key 排序
                $new_items = $this->array_group_by($change_items, 'key');
                foreach ($new_items as $key => $value) {
                    unset($new_items[$key]['key']);
                }
                $data['official'][Request::param('type_key')]['items'] = $new_items;
                $new_configs = json_encode($data['official']);
                $rs = Db::table('lottery_config')->where('type', Request::param('type_id'))->update(['official' => $new_configs]);
            }else{
                //把key 加入 value 里
                $items = $data['basic_config'][Request::param('type_key')]['items'];
                foreach ($items as $key => $value) {
                    $items[$key]['key'] = $key;
                }
                //以 0,1,2重新排序
                $change_items = array_values($items);
                foreach ($change_items as $key => $value) {
                    $change_items[$key]['name'] = $new_datas[$key]['name'];
                    $change_items[$key]['odds'] = $new_datas[$key]['odds'];
                    $change_items[$key]['switch'] = intval($new_datas[$key]['switch']);
                }
                //指定key 排序
                $new_items = $this->array_group_by($change_items, 'key');
                foreach ($new_items as $key => $value) {
                    unset($new_items[$key]['key']);
                }
                $data['basic_config'][Request::param('type_key')]['items'] = $new_items;
                $new_configs = json_encode($data['basic_config']);
                $rs = Db::table('lottery_config')->where('type', Request::param('type_id'))->update(['basic_config' => $new_configs]);
            }
			if ($rs) {
				$this->success('修改成功');
			} else {
				$this->error('修改失败');
			}
		}
	}

	public function change_note() {
		$type_id = Request::param('type_id');
		$new_note = Request::param('note');
		$rs = DB::table('lottery_config')->where('type', $type_id)->update(['note' => $new_note]);
		if ($rs) {
			return json(['error' => 0, 'msg' => '修改成功']);
		} else {
			return json(['error' => 1, 'msg' => '修改失败']);
		}
	}

	public function change_name() {
		$type_id = Request::param('type_id');
		$new_note = Request::param('note');
		$rs = DB::table('lottery_config')->where('type', $type_id)->update(['name' => $new_note]);
		if ($rs) {
			return json(['error' => 0, 'msg' => '修改成功']);
		} else {
			return json(['error' => 1, 'msg' => '修改失败']);
		}
	}

	public static function array_group_by($arr, $key) {
		$grouped = [];
		foreach ($arr as $value) {
			$grouped[$value[$key]] = $value;
		}

		if (func_num_args() > 2) {
			$args = func_get_args();
			foreach ($grouped as $key => $value) {
				$parms = array_merge([$value], array_slice($args, 2, func_num_args()));
				$grouped[$key] = call_user_func_array('array_group_by', $parms);
			}
		}
		return $grouped;
	}

	public function renew() {
		$data = Db::table('lottery_config')->where('type', 3)->find();
		print_r($this->object_to_array(json_decode($data['basic_config'])));

	}
	public function showSetting() {
		if (Request::method() == 'GET') {
			//$sid = [4,7,12,13,16,18,19,20,22,23,26,27,28,30,33,34,32,43,48,50,54,55,29,35,53,56];
			
			 $loc = DB::table('lottery_config')->order('type ASC')->column('type,name');
		
			$lottery_show = DB::table('system_config')->where('name', 'lottery_show')->find();
			$settings = $this->object_to_array(json_decode($lottery_show['value']));
			$rs = Db::table('lottery_config')->column('type');
            $bit = array_values(array_unique(array_merge($settings[0]['data'],$rs)));
			$list = [];
			foreach ($bit as $k => $v){
                $list[] = [
                  'name' => $loc[$v],
                  'type' => $v
                ];
            }
			$this->assign('list', $list);
			$this->assign('settings', $settings);
			return $this->fetch();
		} else {
			$lottery_show[0]['name'] = '热门彩票';
			$lottery_show[0]['data'] = Request::param('rm_cp/a');
			// $time_configs = Db::table('lottery_config')->where(['type'=>['type','in',Request::param('rm_cp/a')]])->field('note')->select();
			// foreach ($time_configs as $key => $value) {
			//     $lottery_show[0]['notes'][] = $value['note'];
			// }

			$rs = DB::table('system_config')->where('name', 'lottery_show')->update(['value' => json_encode($lottery_show)]);
			if ($rs) {
				return json(['error' => 0, 'msg' => '修改成功']);
			} else {
				return json(['error' => 1, 'msg' => '修改失败']);
			}
		}

	}

	public function lotterymatch() {
		$paginate = 25;
		$map = [];
		$map['type'] = ['type', '<', 35];
		$pageParam['query'] = [];
		if (Request::param('type') == -1){
            $pageParam['query']['type'] = Request::param('type');
            $this->assign('lottery_type', Request::param('type'));
        }else if (Request::param('type') != '') {
			$map['type'] = Request::param('type');
			$pageParam['query']['type'] = Request::param('type');
			$this->assign('lottery_type', Request::param('type'));
		}

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
		//dump($map);die;

		$list = Db::table('lottery_code')->where($map)->order('create_time DESC')->paginate($paginate, false, $pageParam)->each(function ($item, $key) {

		    if ($item['type'] == 52){
                $niuniu = explode(",", $item['content']);

                $pok_f = ['(黑桃)','(红桃)','(梅花)','(方块)'];
                $pok_Num = ['1' => 'A', '11' => 'J', '12' => 'Q', '13' => 'K'];
//                dump($niuniu);
                $pai = " ";
                foreach ($niuniu as $key => $value){
                    $ss = floor($value/4)+1;
                    $pss = $value%4;
                    $pai .=$pok_f[$pss];
                    if ( $ss == 1 || $ss == 11 || $ss == 12 || $ss == 13 ){
                        $pai .= $pok_Num[$ss];

                    }else{
                        $pai .= floor($value/4)+1; //获取 牌的点数
                    }

                }
                $item['content'] = $pai;
            }

			if (in_array($item['type'], ['0', '1'])) {
				// content 没包含 code
				//iif(in_array('code'))

				// dump(array_key_exists('code', $this->object_to_array(json_decode($item['content']))));
				if (array_key_exists('code', $this->object_to_array(json_decode($item['content']))) === false) {
					dump($key);die;
				}
				//dump($this->object_to_array(json_decode($item['content'])));

				$item['content'] = implode(',', $this->object_to_array(json_decode($item['content']))['code']);
			}
			$item['lottery_name'] = DB::table('lottery_config')->where('type', $item['type'])->find()['name'];
			$betting_records = Db::table('betting')->where(['expect' => $item['expect'], 'state' => 1])->select();
			if (count($betting_records) == 0) {
				$item['yk'] = 0;
				$zg_money = 0;
				$hm_money = 0;
				$zh_money = 0;
				$zhui_records = Db::table('betting_zhui')->where(['expect' => $item['expect'], 'state' => 1])->find();
				if ($zhui_records != null) {
					$zh_win = $zhui_records['win'];

				} else {
					$zh_win = 0;
				}

				$betting_records = Db::table('betting')->where('id', $zhui_records['betting_id'])->find();
				//dump($betting_records);die;
				// dump(explode('$',$betting_records['explain']));die;

				$hm_datas = Db::table('betting_gen')->where('betting_id', $zhui_records['betting_id'])->select();
				if (count($hm_datas) != 0) {
					$hm_money = array_sum(array_map(function ($val) {return $val['money'];}, $hm_datas));
					$hm_win = array_sum(array_map(function ($val) {return $val['win'];}, $hm_datas));
				} else {
					$hm_money = 0;
					$hm_win = 0;
				}
				// dump($hm_win);die;
				//  dump()
				$item['yk'] = intval($zg_money) + intval($hm_money) + intval($zh_money) - floatval($zh_win) - $hm_win;
			} else {
				//dump($betting_records);
				$zg_money = array_sum(array_map(function ($val) {return $val['money'];}, $betting_records));
				$zg_win = array_sum(array_map(function ($val) {return $val['win'];}, $betting_records));
				foreach ($betting_records as $key => $value) {
					$hm_datas = Db::table('betting_gen')->where('betting_id', $value['id'])->select();

					if (count($hm_datas) != 0) {
						$hm_money = array_sum(array_map(function ($val) {return $val['money'];}, $hm_datas));
						$hm_win = array_sum(array_map(function ($val) {return $val['win'];}, $hm_datas));
					} else {
						$hm_money = 0;
					}

					$zh_datas = Db::table('betting_zhui')->where('betting_id', $value['id'])->select();

					if (count($zh_datas) != 0) {
						//$hm_money =  number_format(array_sum(array_map(create_function('$val', 'return $val["money"];'), $zh_datas)),2,'.',',');
					} else {
						$zh_money = 0;
						$zh_win = 0;
					}
					if ($value['money'] == 0) {
						$total_win = $hm_win;
					} else {
						$total_win = $zg_win;
					}

				}

				$item['yk'] = $zg_money + $hm_money + $zh_money - $total_win;
				//dump($total_win);die;
			}
			if ($item['type'] == 12 || $item['type'] == 27) {
				$item['lottery_name'] = '新疆时时彩/新疆28';
			}

			if ($item['type'] == 2 || $item['type'] == 26) {
				$item['lottery_name'] = '重庆时时彩/重庆28';
			}
			return $item;
		});

		$lotterys = DB::table('lottery_config')->field('type,name')->order('type ASC')->select();
		//dump($lotterys);die;
		foreach ($lotterys as $key => $val) {
			if ($lotterys[$key]['type'] == 12 || $lotterys[$key]['type'] == 27) {
				$lotterys[$key]['name'] = '新疆时时彩/新疆28';
			}
			if ($lotterys[$key]['type'] == 2 || $lotterys[$key]['type'] == 26) {
				$lotterys[$key]['name'] = '重庆时时彩/重庆28';
			}
		}
		$this->assign('lotterys', $lotterys);
		$this->assign('list', $list);

		return $this->fetch();
	}
	public  function sjhqnn($niu){ //随机获取牛牛



        $arr = [1,2,3,4,5,6,7,8,9,10,11,12,13];
        shuffle($arr);
        $bx = new Lottery28();
        $sj3w = $bx::strand($arr,3);

        foreach ($sj3w as $key=>$value){
            $nes = [];
            $nex = [];
            foreach ($value as $k=>$v){
                $nes[] = $v > 10 ? 10 : $v;
                $nex[] = $v;
            }

            if (ceil(array_sum($nes)/10) == array_sum($nes)/10){
                break;
            }
        }
        $nns = [];
        foreach ($nex as $ks =>$vs ){
            $nns[] = $vs -1 ;
        }
        $sj2w = $bx::strand($arr,2);
        foreach ($sj2w as $key=>$value){
            $nec = [];
            $neb = [];
            foreach ($value as $k=>$v){
                $nec[] = $v> 10 ? 10 : $v ;
                $neb[] = $v;

            }
            if (array_sum($nec)%10 == $niu ){
                break;
            }
        }
        $hlw = [];
        foreach ($neb as $ks =>$vs ){
            $hlw[] = $vs -1 ;
        }
        return [$nns,$hlw];
    }
    public function wuniu(){ //获取无牛
        $arr = [1,2,3,4,5,6,7,8,9,10,11,12,13];
        shuffle($arr);
        $bx = new Lottery28();
        $sj5w = $bx::strand($arr,5);
//        $wuniux = 0;
        $zwn = [];
        foreach ($sj5w as $key => $value){
            $wuniu = $bx::strand($value,3);
            $zs = [];

            foreach ($wuniu as $k =>$v){
                foreach ($v as $ku =>$vaa){
                    $zs[] = $vaa> 10 ? 10 : $vaa ;
                }
                if (ceil(array_sum($zs)/10) != array_sum($zs)/10){
                    $zwn = $value;
                }else{
                    continue;
                }
            }
        }
//        dump($zs);
//        dump($zwn);die();
        $sz = [];
        $arr = [0,1,2,3];
        foreach ($zwn as $key =>$value){
                $sz[] = $value*4+array_rand($arr,1);
        }
//        dump($sz);die();
        return $sz;
    }
    public  function  hqys($zh){ //转换    最终数子 = 传递的数子 * 4 + 随机的0~3的数
        $sz = [];
        $arr = [0,1,2,3];
        foreach ($zh as $key =>$value){
            foreach ($value as $k=>$v){
                $pc[] = $v;
                $sz[] = $v*4+array_rand($arr,1);

            }
        }
//        dump($pc);
//        dump($sz);die();
        return $sz;
    }
	//预设开奖
	public function prejk() {
	     $type = [3,2,10,12,13,16,17,18,20,21,24,25,26,27,28,30,31,32,33,34,42,43,46,47,48,49,50];
	     if (in_array(Request::param('type'),$type)){
             return json(['code' => 1, 'msg' => '不能预设有官网的彩种']);
         }
        if ( Request::param('type') == 52){


            $content = Request::param('content/a');
            $content2 = Request::param('content2/a');
            $shuju = [];
            foreach ($content as $key =>$value){
                $shuju[] = $value*4+$content2[$key];
            }
            $str=join(",",$shuju);
//            dump(Request::param('expect'));
            $data_exist = Db::table('preset_lottery_code')->where('expect', Request::param('expect'))->where('type', Request::param('type'))->find();


            if ($data_exist != null) {
                return json(['code' => 0, 'msg' => '该期预设已存在！']);
            }
            $data['content'] = $str;
            $data['type'] =Request::param('type');
            $data['expect'] = Request::param('expect');
            $data['create_time'] = strtotime(Request::param('create_time'));
//            dump($data);die();
            $code_data = Db::name('preset_lottery_code')->insert($data);
            if ($code_data) {
                return json(['code' => 0, 'msg' => '预设成功']);
            } else {
                return json(['code' => 1, 'msg' => '预设失败']);
            }
        }
		$cs = Request::param('content');
		$cc =  str_replace(',', '', $cs);
		if (isset($cc)) {
			if (in_array(Request::param('type'), ['3','4','11','21','16','17','18','44','45','48','49','36','5','37','39','38','51'])) {
				$strArray=str_split($cc,2);
				$str=join(",",$strArray);
			}else{
				$strArray=str_split($cc,1);
				$str=join(",",$strArray);
			}
		}
		// dump($cc);exit();

		// dump(Request::param());exit();
		if (Request::param('type') == 27) {
			$data['content'] = $str . '5,6';
			$data['type'] = 12;
		} elseif (Request::param('type') == 26) {
			$data['type'] = 2;
			$data['content'] = $str . '5,6';
		} else {
			$data['type'] = Request::param('type');
			$data['content'] = $str;
		}

		$data_exist = Db::table('preset_lottery_code')->where('expect', Request::param('expect'))->where('type', Request::param('type'))->find();
		if ($data_exist != null) {
			return json(['code' => 1, 'msg' => '该期预设已存在！']);
		}

		$data['expect'] = Request::param('expect');
		$data['create_time'] = strtotime(Request::param('create_time'));
		$code_data = Db::name('preset_lottery_code')->insert($data);

		if ($code_data) {
			return json(['code' => 0, 'msg' => '预设成功']);
		} else {
			return json(['code' => 1, 'msg' => '预设失败']);
		}
	}

	//返水
	public function return_config() { //28系列反水设置
//        dump('ss');
		if (Request::method() == 'GET') {

			$return_config = Db::table('lottery_config')->where('type', Request::param('type_id'))->find()['return'];
			if ($return_config != null) {
				$return_config = $this->object_to_array(json_decode($return_config));

			} else {
				$return_config['switch'] = '';
				$return_config['condition'] = '';
				$return_config['range'] = '';
			}
			$this->assign('cate', Request::param('type_cate'));
			$this->assign('return_config', $return_config);
			$this->assign('type_name', Request::param('type_name'));
			$this->assign('type_id', Request::param('type_id'));
			return $this->fetch();
		} else {
			$data['switch'] = Request::param('switch');
			$data['condition'] = Request::param('condition');
			$data['range'] = Request::param('final_array/a');

			$rs = Db::table('lottery_config')->where('type', Request::param('type_id'))->update(['return' => json_encode($data)]);

			if ($rs) {
				return json(['error' => 0, 'msg' => '设置成功']);
			} else {
				return json(['error' => 1, 'msg' => '设置失败']);
			}
		}

	}

	public  function kozkaijiang(){ //龙虎 1 和 百家乐 控制开奖
        if (Request::method() == 'GET') {
            $return_config = Db::table('lottery_config')->where('type', Request::param('type_id'))->find();
            if (!empty($return_config)) {
                $return_config['return'] = json_decode($return_config['return'], true);
            } else {
                $this->error('数据异常');
            }
//                dump($return_config['return']);
            $this->assign('return_config', $return_config['return']);

            $this->assign('cate', Request::param('type_cate'));
            $this->assign('type_name', Request::param('type_name'));
            $this->assign('type_id', Request::param('type_id'));
            return $this->fetch();
        }else{
//            dump(Request::param());die();
            $data['fanshui']['switch'] = Request::param('fanshui_switch', 0);
            $data['fanshui']['val'] = round(Request::param('fanshui_val',0),2);
            $data['kongzhi']['switch'] = Request::param('kongzhi_switch',0);
            $data['kongzhi']['val'] = Request::param('kongzhi_val',0);
            // dump(Request::param('kongzhi_switch'));
            //判断 反水比例或金额 是否改变 未改变 给出提示
            $yz = Db::table('lottery_config')->where('type', Request::param('type_id'))->find()['return'];

            if ($yz ==  json_encode($data)) {
                return json(['error' => 1, 'msg' => '没有改变反水比例或金额，无法保存']);
            }
            $rs = Db::table('lottery_config')->where('type', Request::param('type_id'))->update(['return' => json_encode($data)]);
            if ($rs) {
                return json(['error' => 0, 'msg' => '设置成功']);
            } else {
                return json(['error' => 1, 'msg' => '设置失败']);
            }
        }
    }
	public function return_config_01() //龙虎 1 和 百家乐 0  港彩 其他 反水设置
	{
		if (Request::method() == 'GET') {
//            dump(Request::param());

            if (Request::param('type_id') == 0 || Request::param('type_id')==1){
                $return_config = Db::table('lottery_config')->where('type', Request::param('type_id'))->find();
                if (!empty($return_config)) {
                    $return_config['return'] = json_decode($return_config['return'],true);
                } else {
                    $this->error('数据异常');
                }
//                dump($return_config['return']);
                $this->assign('return_config', $return_config['return']);

                $this->assign('cate', Request::param('type_cate'));
                $this->assign('type_name', Request::param('type_name'));
                $this->assign('type_id', Request::param('type_id'));
                return $this->fetch();
            }else{
                $return_config = Db::table('lottery_config')->where('type', Request::param('type_id'))->find();

                if (!empty($return_config)) {
                    $return_config['return'] = json_decode($return_config['return'],true);
                } else {
                    $this->error('数据异常');
                }

                $this->assign('cate', Request::param('type_cate'));
                $this->assign('return_config', $return_config['return']);

                $this->assign('type_name', Request::param('type_name'));
                $this->assign('type_id', Request::param('type_id'));
                return $this->fetch();
            }
		} else {

			if (Request::param('type_id') == 0 || Request::param('type_id') == 1) {
				//判断是否为空 或不为正数

				//获取传递过来的值   fanshui_switch-反水开关-  fanshui_val-反水比例- kongzhi_switch-控制开奖开关- kongzhi_val-控制开奖金额-
				$data['fanshui']['switch'] = Request::param('fanshui_switch');
				$data['fanshui']['val'] = round(Request::param('fanshui_val'),2);
				$data['kongzhi']['switch'] = Request::param('kongzhi_switch');
				$data['kongzhi']['val'] = Request::param('kongzhi_val');

				// dump(Request::param('kongzhi_switch'));
				//判断 反水比例或金额 是否改变 未改变 给出提示
				$yz = Db::table('lottery_config')->where('type', Request::param('type_id'))->find()['return'];

				if ($yz ==  json_encode($data)) {
					return json(['error' => 1, 'msg' => '没有改变反水比例或金额，无法保存']);
				}
				$rs = Db::table('lottery_config')->where('type', Request::param('type_id'))->update(['return' => json_encode($data)]);
				if ($rs) {
					return json(['error' => 0, 'msg' => '设置成功']);
				} else {
					return json(['error' => 1, 'msg' => '设置失败']);
				}

			}else{



				$data['switch'] = Request::param('switch');
				$data['chazhi'] =round(Request::param('chazhi'),2) ;


				//判断 反水比例 是否改变 未改变 给出提示
				$yz = Db::table('lottery_config')->where('type', Request::param('type_id'))->find()['return'];
				// dump($yz);
				// dump(json_encode($data));exit();
				if ($yz ==  json_encode($data)) {
					return json(['error' => 1, 'msg' => '没有改变反水比例，无法保存']);
				}

				$rs = Db::table('lottery_config')->where('type', Request::param('type_id'))->update(['return' => json_encode($data)]);
				if ($rs) {
					return json(['error' => 0, 'msg' => '设置成功']);
				} else {
					return json(['error' => 1, 'msg' => '设置失败']);
				}

			}


		}

	}

	//28返点
	public function returnMoney() {
		$t = time();
		// $start_time = strtotime(date("Y-m-d", strtotime("-1 day")));
		// $end_time = $start_time + 3600 * 24 - 1;
		$start_time = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
		$end_time = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));
		$map = [];
		$lotterys = Db::table('lottery_config')->where(['type' => ['type', 'in', [24, 25, 26, 27]]])->field('type,return')->select();
		foreach ($lotterys as $key => $value) {
			$lotterys[$key]['return'] = $this->object_to_array(json_decode($value['return']));
			$range = array_reverse($lotterys[$key]['return']['range'], true);
			$map['type'] = $value['type'];
			//
			$map['create_time'] = ['create_time', 'between', [$start_time, $end_time]];
			//找普通普通用户
			// dump($value['type']);
			$mop['be.type'] =  ['be.type', '=', $value['type']];
			$mop['u.type'] = ['u.type','=','0'];
			$mop['be.create_time'] = ['be.create_time', 'between', [$start_time, $end_time]];
			$ptyh = DB::table('betting')->alias('be')->where($mop)->join('user u','be.user_id = u.id')->field('be.user_id')->group('be.user_id')->select();
			$uid = [];
			foreach ($ptyh as $ss) {
				$uid[] = $ss['user_id'];
			}


			$map['user_id'] = ['user_id','in',$uid];
			$total_datas = Db::table('betting')->field('id,user_id,content,money,money,type,win')->where($map)->select();
			// dump($total_datas);die;
			if (count($total_datas) !== 0) {

				//计算大小单双所占比例
				$dxds = 0;
				foreach ($total_datas as $k => $v) {
					$content = $this->object_to_array(json_decode($v['content']));
					foreach ($content as $k1 => $v1) {
						if (in_array($content[$k1]['code'], ['a', 'b', 'c', 'd'])) {
							//dump($content[$k1]['money']);
							$dxds += $content[$k1]['money'];
						}
					}
				}
				//dump($dxds);die;
				//总投注额度
				$totalbetting = array_sum(array_map(function ($val) {return $val['money'];}, $total_datas));

				$totalwin = array_sum(array_map(function ($val) {return $val['win'];}, $total_datas));
				$percent = 0;
				$dxds_rate = $dxds / $totalbetting * 100;
				// dump($totalbetting);die;

				if ($dxds_rate < floatval($lotterys[$key]['return']['condition'])) {
					//dump($dxds_rate);die;
					//echo floatval($lotterys[$key]['return']['condition']);die;
					$sql = "select user_id,(sum(money)-sum(win)) as profit  from betting  where type=" . $value['type'] . " and create_time between " . $start_time . " and " . $end_time . " group by user_id";
					//dump($sql);die;
					$profit_datas = Db::table('betting')->query($sql);
					//dump($prox)
					//dump($profit_datas);die;
					// dump('ss');exit();
					if (count($profit_datas) !== 0) {

						foreach ($profit_datas as $kk => $vv) {
							$percent = 0;

							if ($profit_datas[$kk]['profit'] > end($range)[0]) {

								foreach ($range as $k2 => $v2) {
									if (($totalbetting - $totalwin) >= $v2[0]) {
										$percent = $v2[1];
										break;
									}
								}
								// dump($vv);die;
								$data['uid'] = $vv['user_id'];
								$data['money'] = round($vv['profit'] * $percent / 100, 2);
								$data['type'] = 11;
								// dump($data['uid']);die;
								moneyAction($data);
							}
						}
					}

				}

			}

		}

	}

	//手动派奖
	public function paijiang() {
		$cs = Request::param('content');
		$cc =  str_replace(',', '', $cs);
		// $cc =  trimall($str);

		if (isset($cc)) {
			if (in_array(Request::param('type'),
				['3','4','11','21','16','17','18','44','45','48','49','36','5','37','39','38'])) {
				$strArray=str_split($cc,2);
				$str=join(",",$strArray);
			}else{
				$strArray=str_split($cc,1);
				$str=join(",",$strArray);
			}
		}
		// dump($str);exit();


		if (Request::param('type') == 27) { //判断type 是27的时候$type =12
			$type = 12;
		} elseif (Request::param('type') == 26) {
			$type = 2;
		} else {
			$type = Request::param('type');
		}

		//echo 'dsadsa';die;
		$data_exist = Db::table('lottery_code')->where(['type' => $type, 'expect' => Request::param('expect')])->find();
		// dump(Request::param());
		// dump($data_exist);exit();
		// print_r($data_exist);die;
		$Klsf_array = [20, 50];
		$Ks_array = [10, 14, 15, 30, 31, 32, 33, 34, 40, 41, 42, 43];
		$Lhc_array = [11, 21];
		$P3d_array = [19, 22];
		$Pc28_array = [24, 25, 26, 27];
		$Pk10_array = [3, 4, 5, 36, 37, 38, 39, 51];
		$Ssc_array = [2, 6, 7, 8, 9, 12, 13, 28 ];
		$Syxw_array = [16, 17, 18, 44, 45, 46, 47, 48, 49];
		$Xync_array = [23];
		$bhd_bjl = [0, 1]; //龙虎斗 ，百家乐
        $niuniu = [52];

		if (null != $data_exist || Request::param('type') <= 1 ) {//判断是否有开奖记录    不能为空成立

			if (in_array(Request::param('type'), $Klsf_array)) {//判断type 是否为 快乐是否的 type
				$rs = new Klsf();
			} elseif (in_array(Request::param('type'), $Ks_array)) {//快3 系列
				$rs = new Kstatussettings();
			} elseif (in_array(Request::param('type'), $Lhc_array)) {//极速六合彩
				$rs = new Lhc();
			} elseif (in_array(Request::param('type'), $P3d_array)) {//福彩3D  排列三
				$rs = new P3d();
			} elseif (in_array(Request::param('type'), $Pc28_array)) {// 28 系列
				$rs = new Pc28();
			} elseif (in_array(Request::param('type'), $Pk10_array)) {//PK10  1.5分PK10 幸运赛车
				$rs = new Pk10();
			} elseif (in_array(Request::param('type'), $Ssc_array)) {//时时彩 系列
				$rs = new Ssc();
			} elseif (in_array(Request::param('type'), $Syxw_array)) {// 11选5 系列
				$rs = new Syxw();
			} elseif (in_array(Request::param('type'), $Xync_array)) {//幸运农场
				$rs = new Xync();
			} elseif (in_array(Request::param('type'), $bhd_bjl)) {//龙虎 百家
				$rs = new Game();
				//dump('ss');exit();
			}else if (in_array(Request::param('type'), $niuniu)){ //牛牛
                $rs = new Brnn();
            } else {
				return json(['code' => 1, 'msg' => '该彩种不存在']);
			}

		} else {//如果 lottery_code 表 里里面 不存在 先lottery_code向添加数据 在 派奖


			//28系列 需要补号
			if (Request::param('type') == 27) {
				$data['content'] = $str . '5,6';
				$data['type'] = 12;
			} elseif (Request::param('type') == 26) {
				$data['type'] = 2;
				$data['content'] = $str . '5,6';
			}elseif (Request::param('type') == 52){
                $number = range(0,51);
                shuffle($number);
                $ss = array_slice($number,0,10);
                $cc =implode(',',$ss);
                $data['type'] = Request::param('type');
                $data['content'] = $cc;
            } else {
				$data['type'] = Request::param('type');
				$data['content'] = $str;
			}

			$data['expect'] = Request::param('expect');
			$data['create_time'] = strtotime(Request::param('create_time'));

			// 加code 或 jion数据
			// if (Request::param('type') == 0 || Request::param('type') == 1) {
			// 	return json(['code' => 1, 'msg' => '数据异常']);
			// }

			$code_data = Db::name('lottery_code')->insert($data);

			if ($code_data) {

				if (in_array(Request::param('type'), $Klsf_array)) {
					$rs = new Klsf();
				} elseif (in_array(Request::param('type'), $Ks_array)) {
					$rs = new Ks();
				} elseif (in_array(Request::param('type'), $Lhc_array)) {
					$rs = new Lhc();
				} elseif (in_array(Request::param('type'), $P3d_array)) {
					$rs = new P3d();
				} elseif (in_array(Request::param('type'), $Pc28_array)) {
					$rs = new Pc28();
				} elseif (in_array(Request::param('type'), $Pk10_array)) {
					$rs = new Pk10();
				} elseif (in_array(Request::param('type'), $Ssc_array)) {
					$rs = new Ssc();
				} elseif (in_array(Request::param('type'), $Syxw_array)) {
					$rs = new Syxw();
				} elseif (in_array(Request::param('type'), $Xync_array)) {
					$rs = new Xync();
				} elseif (in_array(Request::param('type'), $bhd_bjl)) {//龙虎 百家
					$rs = new Game();
				} else if (in_array(Request::param('type'), $niuniu)){ //牛牛
                    $rs = new Brnn();
                }else {
					return json(['code' => 1, 'msg' => '该彩种不存在']);
				}
			}
		}
		//dump($rs);die;
//		 echo Request::param('expect');
//		 echo '<pre>';
//		 echo '<pre>';du
//		 echo Request::param('type');
//		 die;
		$rs->prize(Request::param('expect'), Request::param('type'));

	}

	public function hmzh() {
		if (Request::method() == 'GET') {
			$hmzh = Db::table('system_config')->where('name', 'hm_zh')->find()['value'];
			$hmzhinfo = $this->object_to_array(json_decode($hmzh));
			$this->assign('hmzhinfo', $hmzhinfo);
			return $this->fetch();
		} else {

			$data['speed'] = Request::param('speed');
			$data['total'] = Request::param('total');
			$data['zg'] = Request::param('zg');
			$data['bd'] = Request::param('bd');
			$data['tc_num'] = Request::param('tc_num');
			$data['tc_switch'] = Request::param('tc_switch');
			$data['zh_switch'] = Request::param('zh_switch');
			$data['bd_switch'] = Request::param('bd_switch');
			$data['hm_switch'] = Request::param('hm_switch');
			$rs = Db::table('system_config')->where('name', 'hm_zh')->update(['value' => json_encode($data)]);
			if ($rs) {
				$this->success('修改成功');
			} else {
				$this->error('修改失败');
			}
		}

	}

	public function gcSetting() {
		if (Request::method() == 'GET') {
			$list = DB::table('lottery_config')->field('type,name')->order('type ASC')->select();

			$lottery_show = DB::table('system_config')->where('name', 'lottery_trade')->find();
			if (Request::param('cate') == '') {
				$settings[] = $this->object_to_array(json_decode($lottery_show['value'])[0]);
			} else {
				$settings[] = $this->object_to_array(json_decode($lottery_show['value'])[Request::param('cate')]);
			}
			$tab_settings = $this->object_to_array(json_decode($lottery_show['value']));
			//dump($settings);
			$this->assign('tab', Request::param('cate'));
			$this->assign('list', $list);
			$this->assign('tab_settings', $tab_settings);
			$this->assign('settings', $settings);
			return $this->fetch();
		} else {
			$setting_list = Request::param('setting_list/a');
			$lottery_show = DB::table('system_config')->where('name', 'lottery_trade')->find();
			$new_settings = $this->object_to_array(json_decode($lottery_show['value']));

			$new_settings[Request::param('cate')]['data'] = $setting_list[0];

			// dump($new_settings);die;
			// $settings = $lottery_show['value']
			// $name_list = Request::param('name_list/a');

			// $lottery_trade =[];
			//  foreach ($name_list as $key => $value) {
			//      $lottery_trade[$key]['name'] = $value;
			//      $lottery_trade[$key]['data'] = $setting_list[$key];
			//  }

			$rs = DB::table('system_config')->where('name', 'lottery_trade')->update(['value' => json_encode($new_settings)]);

			if ($rs) {
				return json(['error' => 0, 'msg' => '修改成功']);
			} else {
				return json(['error' => 1, 'msg' => '修改失败']);
			}
		}
	}

	public function matching() {
		//echo 'dasd';die;
		$paginate = 25;
		$pageParam['query'] = [];
		$sid = [4,7,12,13,16,18,19,20,22,23,26,27,28,30,33,34,32,43,48,50,54,55,0,1,29,35,53,56];
		if (Request::param('type') != '') {
			$map['type'] = Request::param('type');
			$pageParam['query']['type'] = Request::param('type');
			$this->assign('type', Request::param('type'));
		} else {
			$map['type'] = ['type','=',2];
		}
		
		$list = Db::table('lottery_code')->where($map)->order('expect', 'desc')->paginate($paginate, false, $pageParam)->each(function ($item, $key) {
            if ($item['type'] == 52){
                $niuniu = explode(",", $item['content']);

                $pok_f = ['(黑桃)','(红桃)','(梅花)','(方块)'];
                $pok_Num = ['1' => 'A', '11' => 'J', '12' => 'Q', '13' => 'K'];
                $pai = " ";
                foreach ($niuniu as $key => $value){
                    $ss = floor($value/4)+1;
                    $pss = $value%4;
                    $pai .=$pok_f[$pss];
                    if ( $ss == 1 || $ss == 11 || $ss == 12 || $ss == 13 ){
                        $pai .= $pok_Num[$ss];

                    }else{
                        $pai .= floor($value/4)+1; //获取 牌的点数
                    }

                }
                $item['content'] = $pai;
            }
			$item['lottery_name'] = Db::table('lottery_config')->where('type', $item['type'])->find()['name'];
			if (in_array($item['type'], ['0', '1'])) {
				// dump($item['content']);exit();
				$item['content'] = implode(',', $this->object_to_array(json_decode($item['content']))['code']);
			}
			return $item;
		});

		//查询彩票
		$lottery_config = Db::table('lottery_config')->where('type','not in',$sid)->field('type,name')->select();
		// dump($lottery_config);
		$this->assign('list', $list);
		$this->assign('lottery_array', $lottery_config);
		return $this->fetch();
	}

	public function tb_peilv() {
		$lottery_trade = Db::table('system_config')->where('name', 'lottery_trade')->find()['value'];

		$cate_array = '';
		$flag = 0;
		$lottery_trade = $this->object_to_array(json_decode($lottery_trade));


		foreach ($lottery_trade as $key => $value) {
			// dump($value['data']);
			if ($value['data']!='' && in_array(Request::param('type'), $value['data'])) {
				$cate_array = $value['data'];
				break;
			}
		}
		if($cate_array == ''){
			return json(['error' => 1, 'msg' => '没有同类彩票']);
		}

		$lottery_config = Db::table('lottery_config')->where('type', Request::param('type'))->find()['basic_config'];
		foreach ($cate_array as $key => $value) {
			$data['type'] = $value;
			$data['basic_config'] = $lottery_config;
			$rs = Db::table('lottery_config')->update($data);
			if ($rs) {
				$flag++;
			}
		}

		if ($flag > 0) {
			return json(['error' => 0, 'msg' => '修改成功']);
		} else {
			return json(['error' => 1, 'msg' => '修改失败']);
		}

	}
	//彩种设置->内容排序
	public function sort(){
		if(Request::method() == 'POST'){
			$data = input('post.');
			if($data['type'] == 1){
				$rs = Db::table('lottery_config')->field('basic_config')->where('type','=',$data['data'])->find()['basic_config'];
				return json_decode($rs,true);
			}else{
				$return_data = [
					'code' => -1,
					'msg'  => '修改失败'
				];
				$save['basic_config'] = json_encode($data['data']);
				$save['type'] = $data['lottery'];
				if((new LotteryConfig)->save($save,true)){
					$return_data['code'] = 1;
					$return_data['msg'] = '修改成功';
				}
				return $return_data;
			}
		}else{
			$fh = input('get.type_cate');
			$this->assign('cate', $fh);
			return $this->fetch();
		}
	}



}
