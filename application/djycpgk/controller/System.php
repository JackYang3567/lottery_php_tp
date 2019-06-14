<?php
namespace app\djycpgk\controller;
use Illuminate\Support\Debug\Dumper;
use think\Controller;
use think\Db;
use think\facade\Config;
use think\facade\Log;
use think\facade\Request;
use \tp5er\Backup;
use app\djycpgk\model\Notice;

class System extends Rbac {
    //修改银行信息
    public function bank_edit($bank_id){
        if (Request::method() == 'POST') {
            $rs = Db::table('bank_pay')->where('id',$bank_id)->find();
            $rs['qr_code']=json_decode($rs['qr_code'],true);

            $rs['user_name']=json_decode($rs['user_name'],true);
            if ($rs['name'] !='payalipay' && $rs['name'] !='payweixin'){
                $rs['number']=json_decode($rs['number'],true);
            }
            if ($rs){
                return json($rs);
            }else{

            }
        }
        else{
            $this->assign('bank_id', $bank_id);
            return $this->fetch();
        }
    }
    //添加银行
    public function bank_add(){
        if (Request::method() == 'POST') {
            $bankTool = bankTool('');
            $rank = Db::table('user_rank')->field('rank,name')->select();
            return json([$bankTool,$rank]);
        }else {
            return $this->fetch();
        }
    }
    public function recharge_punch()//首冲赠送
    {
        if (Request::method() == 'GET') {
            $rules = DB::table('recharge_give')->where('type',2)->order('id ASC')->select();

            $this->assign('rules', $rules);
            return $this->fetch();
        } else {
            $data = Request::post('final_array/a');

            $model_count = DB::table('recharge_give')->where('type',2)->count();
            $data_count = count($data);
            $id = 100;
            $flag = 0;
            if ($data_count >= $model_count) {

                foreach ($data as $key => $value) {
                    $id = $id+1;
                    $give_data['id'] = $id;
                    $give_data['begin'] = $value[0];
                    $give_data['end'] = $value[1];
                    $give_data['percent'] = $value[2];
                    $give_data['type'] = 2;
                    if (($id -100) <= $model_count) {
                        $rs = DB::name('recharge_give')->update($give_data);
                        if ($rs) {
                            $flag++;
                        }
                    } else {
                        $rs = DB::name('recharge_give')->insert($give_data);
                        if ($rs) {
                            $flag++;
                        }
                    }

                }
            } else {
                $model_data = DB::table('recharge_give')->where('type',2)->select();

                foreach ($model_data as $key => $value) {
                    $id = $id + 1;
                    foreach ($data as $k => $val) {

                        if (($id -100) <= $data_count) {
                            $give_data['id'] = $id;
                            $give_data['begin'] = $val[0];
                            $give_data['end'] = $val[1];
                            $give_data['percent'] = $val[2];
                            $give_data['type'] = 2;
                            $rs = DB::name('recharge_give')->update($give_data);

                            if ($rs) {
                                $flag++;
                            }
                        } else {
                            $rs = DB::table('recharge_give')->where('type',2)->delete($id);
                            if ($rs) {
                                $flag++;
                            }
                        }
                    }
                }
            }
            if ($flag > 0) {
                return json(array('error' => 0, 'msg' => '修改成功'));
            } else {
                return json(array('error' => 1, 'msg' => '修改失败,请完善数据'));
            }
        }
    }
    public function notice_edit(){//修改公告
        if (Request::method() == 'POST'){
            $content = input('post.content');
            $id = input('post.id');
            $bt = input('post.bt');
            $type =Notice::where('id',$id)->find()['type'];
            if ($type == 1){
                Db::table('system_config')->where('name','dialog')->update(['value' => $content]);
            }
            $rs  = Notice::where('id',$id)->update(['name' => $bt,'val'=>$content]);
            if ($rs) {
                return json(['error' => 1, 'msg' => '修改成功']);
            } else {
                return json(['error' => -1, 'msg' => '修改失败']);
            }
        }else{
            $id = input('get.id');
            $list =Notice::where('id',$id)->find();
            $this->assign('list', $list);
            $this->assign('id', $id);
            return $this->fetch();
        }
    }
    public function  noticeadd(){//弹出公告添加
        if (Request::method() == 'POST'){
            $name = input('post.bt');
            $content = input('post.content');
            $data = ['name' => $name,'val'=>$content,'adding_time'=>time()];
            $rs = Db::name('notice')->insert($data);
            if ($rs) {
                return json(['error' => 1, 'msg' => '添加成功']);
            } else {
                return json(['error' => -1, 'msg' => '添加失败']);
            }
        }else{
            return $this->fetch();
        }

    }
    public function  notice(){//弹出公告.
        if (Request::method() == 'POST'){
            $type = input('post.type');
            if ($type == 1){
                $id = input('post.id');
                $kg = input('post.kg');
                $gb = Notice::where('id','>',0)->update(['type' => '0']);//将所有的 type变为0

                $gx = Notice::where('id',$id)->update(['type' => '1']); // 将默认 公告设置为 1
                $neirong = Notice::where('id',$id)->find()['val'];//查询 默认公告的内容
                Db::table('system_config')->where('name','dialog_kg')->update(['value' => $kg]);//更新公告 开关
                Db::table('system_config')->where('name','dialog')->update(['value' => $neirong]); //更新公告内容

                if ($gb && $gx){
                    return json(['error' => 1, 'msg' => '修改成功']);
                }else{
                    return json(['error' => -1, 'msg' => '修改失败']);
                }
            }else if($type == 2){
                $id = input('post.id');
                $notice_type = Notice::where('id',$id)->find()['type'];
                if ($notice_type == 1 ){
                    return json(['error' => -1, 'msg' => '不能删除默认公告']);
                }
                $rs = Notice::where('id',$id)->delete();
                if ($rs){
                    return json(['error' => 1, 'msg' => '删除成功']);
                }else{
                    return json(['error' => -1, 'msg' => '删除失败']);
                }
            }
        }else{
            $paginate = 15;
            $list =Notice::order('id desc')->paginate($paginate);
            $dialog_kg = Db::table('system_config')->where('name','dialog_kg')->find()['value'];
            $this->assign('list', $list);
            $this->assign('dialog_kg', $dialog_kg);
            return $this->fetch();
        }
    }

    public function  jqrTianjia(){
        if (Request::method() == 'POST') {
            if (Request::post('id')){
//                dump(Request::post());die();
                $sr = Db::table('robot')->where('id',Request::post('id'))->update(Request::post());
                if ($sr) {
                    return json(['error' => 0, 'msg' => '修改成功']);
                } else {
                    return json(['error' => 1, 'msg' => '修改失败']);
                }
            }
            $sr = DB::table('robot')->insert(Request::post());
            if ($sr) {
                return json(['error' => 0, 'msg' => '添加成功']);
            } else {
                return json(['error' => 1, 'msg' => '添加失败']);
            }
        }else{
            if (Request::param('id')){
                $sr = Db::table('robot')->where('id',Request::param('id'))->find();
                $this->assign('sr', $sr);
                return $this->fetch();
            }else{
                return $this->fetch();
            }

        }
    }
    public function jqrShezhi(){ //机器人设置
        if (Request::param('type') == 1){ //删除
            $sr = Db::table('robot')->where('id',Request::post('id'))->delete();
            if ($sr > 0) {
                return json(['error' => 0, 'msg' => '删除成功']);
            } else {
                return json(['error' => 1, 'msg' => '删除失败']);
            }
        }
        $paginate = 15;
        $jqr = Db::table('robot')->paginate($paginate);
        $this->assign('jqr', $jqr);
        return $this->fetch();
    }
    public function xiazhuliushui(){ // 选择要反水的游戏 选择
        if (Request::method() == 'POST') {


            $fanshui = DB::table('system_config')->where('name', 'return_config')->find()['value'];
            $fanshuiaa = json_decode($fanshui, true);
            $fanshuiaa['return_type']['rule'][1][1] = Request::param('rule_id/a')==null ?[0]:Request::param('rule_id/a');
            $fanshuiaa['return_type']['rule'][1][2] = Request::param('api/a') ==null ? [0]: Request::param('api/a');
            $rs = Db::table('system_config')->where('name', 'return_config')->update(['value' => json_encode($fanshuiaa)]);
            if ($rs > 0) {
                return json(['error' => 0, 'msg' => '修改成功']);
            } else {
                return json(['error' => 1, 'msg' => '修改失败']);
            }
        }else {
            $fz = Db::table('lottery_config')->field('type,name')->where([['type','not in',[1,0,52,53,54,55]],['switch','=',1]])->select();
            $fanshui = DB::table('system_config')->where('name', 'return_config')->find()['value'];
            $fanshuiaa = json_decode($fanshui, true);
            $api = Db::table('api_config')->field('id,name')->select();
            $this->assign('api', $api);
            $this->assign('fz', $fz);
            $this->assign('fs', $fanshuiaa['return_type']['rule'][1][1]);
            $this->assign('api_xz', $fanshuiaa['return_type']['rule'][1][2]);
            return $this->fetch();
        }
    }
    public function fanShui(){ //反水
        if (Request::method() == 'POST') {
            if (input('post.type')==2){ //回水设置
                $list = Request::param();
                $huishui = DB::table('system_config')->where('name', 'return_loss')->find()['value'];
                $huishuiaa = json_decode($huishui, true);

                foreach ($list['return_rule'] as $vo){
                    if( $vo['min'] == ''|| $vo['max'] == '' || $vo['value'] == '' ){
                        return json(['error' => 1, 'msg' => '条件不能为空']);
                    }
                    if( $vo['max'] <= $vo['min'] ){
                        return json(['error' => 1, 'msg' => '结束值不能小于等于开始值']);
                    }
                }
                if ($huishuiaa['switch']['value'] == 0 && $list['switch'] == 1 && time() > $huishuiaa['switch']['time']){
                    $huishuiaa['switch']['time'] = strtotime(date('Y-m-d 00:00:00',time())) + 86400 + (4*3600) + (10*60);
                }

                $huishuiaa['switch']['value'] = $list['switch'];
                $huishuiaa['return_rule']['value'] = $list['return_rule_vale'];
                $huishuiaa['return_rule']['rule'][1][1] = $list['return_rule'];
                $huishuiaa['return_rule']['rule'][0][1] = $list['rule'];

                $rs = Db::table('system_config')->where('name', 'return_loss')->update(['value' => json_encode($huishuiaa)]);

                if ($rs > 0) {
                    return json(['error' => 0, 'msg' => '修改成功']);
                } else {
                    return json(['error' => 1, 'msg' => '修改失败']);
                }
            }else{//反水设置
                $list = Request::param();
                if ($list['return_type'] == 0){
                    foreach ($list['return_rule'] as $vo){
                        if( $vo['min'] == ''|| $vo['max'] == '' || $vo['value'] == ''|| $vo['value1'] == ''){
                            return json(['error' => 1, 'msg' => '条件不能为空']);
                        }
                        if( $vo['max'] <= $vo['min'] ){
                            return json(['error' => 1, 'msg' => '结束值不能小于等于开始值']);
                        }
                    }
                }else{
                    foreach ($list['return_rule_1'] as $vo){
                        if( $vo['min'] == ''|| $vo['max'] == '' || $vo['value'] == ''|| $vo['value1'] == ''){
                            return json(['error' => 1, 'msg' => '条件不能为空']);
                        }
                        if( $vo['max'] <= $vo['min'] ){
                            return json(['error' => 1, 'msg' => '结束值不能小于等于开始值']);
                        }
                    }
                }

                $fanshui = DB::table('system_config')->where('name', 'return_config')->find()['value'];
                $fanshuiaa = json_decode($fanshui, true);
                if (isset( $list['return_rule']) && !isset( $list['return_rule_1']) ){

                    $list['return_rule_1'] = [['min'=>0,'max'=>0,'value'=>0]];
                }
                if ( !isset( $list['return_rule']) && isset( $list['return_rule_1']) ){

                    $list['return_rule'] = [['min'=>0,'max'=>0,'value'=>0]];
                }
                if (!isset( $list['return_rule']) ||  !isset( $list['return_rule_1'])){

                    return json(['error' => 1, 'msg' => '比例不能为空']);
                }



                if ( $fanshuiaa['switch']['name'] == '返水开关' ){
                    $fanshuiaa['switch']['value'] = $list['switch'];
                }
                if ($fanshuiaa['return_type']['name'] == '返水方式'){

                    $fanshuiaa['return_type']['value'] = $list['return_type'];

                }
                if ($fanshuiaa['return_time']['name'] == '返水时间'){
                    $fanshuiaa['return_time']['value'] = $list['return_time'];

                }
                if ($fanshuiaa['return_rule']['name'] == '返水按照那种方式'){
                    $fanshuiaa['return_rule']['value'] = $list['return_rule_vale'];
                    $fanshuiaa['return_rule']['rule'][0][1] = $list['rule'];
                    if ($fanshuiaa['return_type']['value'] == 0 ){
                        $fanshuiaa['return_rule']['rule'][1][1][0] = $list['return_rule'];
                    }else if ($fanshuiaa['return_type']['value'] == 1){
                        $fanshuiaa['return_rule']['rule'][1][1][1] = $list['return_rule_1'];
                    }
                }
                $rs = Db::table('system_config')->where('name', 'return_config')->update(['value' => json_encode($fanshuiaa)]);

                if ($rs > 0) {
                    return json(['error' => 0, 'msg' => '修改成功']);
                } else {
                    return json(['error' => 1, 'msg' => '修改失败']);
                }
            }
        }else{
            if (input('get.type')==2){ //回水设置
                $hs = DB::table('system_config')->where('name','return_loss')->find()['value'];
                $huishui = json_decode($hs,true);
//                dump($huishui);
                $this->assign('huishui', $huishui);
                return $this->fetch('huiShuiList');
            }else{
                $fs = DB::table('system_config')->where('name','return_config')->find()['value'];
                $fanshui = json_decode($fs,true);
                $this->assign('fanshui', $fanshui);
                return $this->fetch();
            }
        }
    }
    public function  user_hongbao(){
        if (Request::method() == 'POST') {
            $list = Request::post();
//            dump($list);die();
            $hb = DB::table('system_config')->where('name','user_hongbao')->find()['value'];
            $hb = json_decode($hb,true);
            foreach ($hb as $key => $v) {
                if ($hb[$key]['name'] == '个人红包开关'){
                    $hb[$key]['value'] = $list[$key];
                }else{
                    $hb[$key]['where_money'] = $list[$key][1];
                    $hb[$key]['money'] = $list[$key][2];
                    $hb[$key]['status'] = $list[$key][0];
                }
            }
            $rs = Db::table('system_config')->where('name', 'user_hongbao')->update(['value' => json_encode($hb)]);
            if ($rs > 0) {
                return json(['error' => 0, 'msg' => '修改成功']);
            } else {
                return json(['error' => 1, 'msg' => '修改失败']);
            }
        }else{
            $hb = DB::table('system_config')->where('name', 'user_hongbao')->find()['value'];
            $hb = json_decode($hb, true);
            $this->assign('hb', $hb);
//            dump($hb);
            return $this->fetch();
        }
    }
	public function hongbao_edit(){//红包问题修改
		if (Request::method() == 'POST') {
			$data['problem'] = input('post.problem');
			$data['answer'] = input('post.answer');
			$data['title'] = input('post.title');

			$cg = Db::table('chat_hongbao_config')->where('id',input('post.id'))->update($data);
			if ($cg > 0) {
				return json(['error' => 0, 'msg' => '修改成功']);
			} else {
				return json(['error' => 1, 'msg' => '修改失败']);
			}
		}else{
			$xg = Db::table('chat_hongbao_config')->where('id',input('get.id'))->find();
			$this->assign('xg', $xg);
			return $this->fetch();
		}
		
	}
	public function hongbao_delete(){//红包问题删除
		$sr = Db::table('chat_hongbao_config')->where('id',Request::post('data_number'))->delete();
		if ($sr > 0) {
				return json(['error' => 0, 'msg' => '删除成功']);
			} else {
				return json(['error' => 1, 'msg' => '删除失败']);
			}
	}
	public function wentitianjia(){ //红包问题添加
		if (Request::method() == 'POST') {
			$sr = DB::table('chat_hongbao_config')->insert(Request::post());
			if ($sr) {
				$this->success('添加成功', url('djycpgk/system/hongbaowenti'));
			} else {
				$this->error('添加失败');
			}

		}else{
			return $this->fetch();
		}
	}
	public function hongbaowenti(){//红包问题设置
		$paginate = 15;
		$sj =DB::table('chat_hongbao_config')->paginate($paginate);
		$this->assign('list', $sj);
		return $this->fetch();
	}
	public function hongbaoshezhi(){//红包设置
		if (Request::method() == 'POST') {
			$list = Request::post();
			$hb = DB::table('system_config')->where('name','hongbao_config')->find()['value'];
			$hb = json_decode($hb,true);
			if (!isset($list['check']['value'])){
                return json(['error' => 1, 'msg' => '抢红包门槛不能为空']);
            }
			foreach ($hb as $key => $v) {
                    if ($hb[$key]['note'] == '抢红包门槛'){

                        $hb[$key]['value'] =$list[$key]['value'];
                        foreach ($hb[$key]['rule'] as $k => $vc){
                            $hb[$key]['rule'][$k][1] = $list[$key]['rule'][$k];
                        }
                    }else{
                        $hb[$key]['value'] = $list[$key];

                    }

			}
			$rs = Db::table('system_config')->where('name', 'hongbao_config')->update(['value' => json_encode($hb)]);
			if ($rs > 0) {
				return json(['error' => 0, 'msg' => '修改成功']);
			} else {
				return json(['error' => 1, 'msg' => '修改失败']);
			}
		}else{
			$hb = DB::table('system_config')->where('name','hongbao_config')->find()['value'];
			$hbx = json_decode($hb,true);
			$this->assign('mk', $hbx['check']['value']);
			$this->assign('hbx', $hbx);

			return $this->fetch();
		}
		
	}
	

	public function app_Download(){ //app 下载路径和开关
			if (Request::method() == 'POST') {
				$data['apk'] = Request::param('apk');
				$data['switch'] = Request::param('switch');
				$data['ipa'] = Request::param('ipa');
				
				$rs = Db::table('system_config')->where('name', 'login_jump')->update(['value' => json_encode($data)]);

				if ($rs) {
					return json(['error' => 0, 'msg' => '修改成功']);
				} else {
					return json(['error' => 1, 'msg' => '修改失败']);
				}
			}else{
				$pz =DB::table('system_config')->where('name','login_jump')->find();
				$jx = json_decode($pz['value'],true);
				$this->assign('jx', $jx);
				return $this->fetch();
			}
		
	}
	public function labagogao(){//喇叭公告发布

		if (Request::method() == 'POST') {
			// dump(Request::post());
			$sz = DB::table('system_config')->where('id',45)->update(['value'=>Request::post('content')]);
			if ($sz > 0) {
				return json(array('error' => 0, 'msg' => '发布成功'));
			} else {
				return json(array('error' => 1, 'msg' => '修改失败,请完善数据'));
			}
		}else{
			$sz = DB::table('system_config')->where('id',45)->find()['value'];
			$this->assign('sz', $sz);
			return $this->fetch();
		}


	}
	public function qiangtai_ys(){//前台页面颜色更换
		if (Request::method() == 'POST') {
			// dump(input('post.ys'));
			$ysx = '#'.input('post.ys');
			$yss =  DB::table('system_config')->where('id', 44)->find()['value'];
			if ($yss == $ysx) {
				return json(array('error' => 1, 'msg' => '未改变颜色无法修改'));
			}
			$se =  DB::table('system_config')->where('id', 44)->update(['value' =>$ysx]);

			if ($se) {
				return json(array('error' => 0, 'msg' => '修改成功'));
			} else {
				return json(array('error' => 1, 'msg' => '修改失败'));
			}

		}else{
			$se =  DB::table('system_config')->where('id', 44)->find()['value'];
			$this->assign('se', $se);
			return $this->fetch();
		}


	}
	public function Domaindeletion($key){//域名删除
        $extension = DB::table('system_config')->where('name','extension')->find()['value'];
        $extension = json_decode($extension,true);
        if ($extension['type'] ==$key ){
            return json(['error' => -1, 'msg' => '不能删除选中的推广域名']);
        }
        unset( $extension['content'][$key]);
        $extension['content'] = array_values( $extension['content']);
        $rs = DB::name('system_config')->where(['name' => 'extension'])->update(['value' => json_encode($extension)]);
        if ($rs){
            return json(['error' => 1, 'msg' => '删除成功']);
        }else{
            return json(['error' => -1, 'msg' => '删除失败']);
        }
    }
	public function ewmym($name){//二维码域名添加
        $extension = DB::table('system_config')->where('name','extension')->find();
        $extension['value'] = json_decode($extension['value'],true);
        $extension['value']['content'][] =$name;
        $rs = DB::name('system_config')->where(['name' => 'extension'])->update(['value' => json_encode($extension['value'])]);
        if ($rs){
            return json(['error' => 0, 'msg' => '添加成功']);
        }else{
            return json(['error' => 1, 'msg' => '添加失败']);
        }
    }
	public function index() {
		$systemInfo = DB::table('system_config')->where('id', 'in', [1, 2, 3, 12, 15, 20, 21, 25, 30, 33, 36, 37, 40, 41, 42,56])->select();

		$tab = Request::param('tab') != '' ? Request::param('tab') : 1;
		$ppts = $this->object_to_array(json_decode($systemInfo['3']['value']));

		$lines = implode(',', json_decode($systemInfo['6']['value']));
		$this->assign('lines', $lines);

		$qrcodes = $this->object_to_array(json_decode($systemInfo['7']['value']));

		$service = $this->object_to_array(json_decode($systemInfo['9']['value']));

		$ty = DB::table('system_config')->where('id', 13)->find()['value'];

		$dialog = DB::table('system_config')->where('id', 38)->find()['value'];

		$dialog_kg = DB::table('system_config')->where('id', 43)->find()['value'];

        $zhancheng = DB::table('system_config')->where('name','zhancheng')->find()['value'];
        $this->assign('zhancheng', $zhancheng);

		// dump($dialog_kg);exit();
        $systemInfo[15]['value']  = json_decode($systemInfo[15]['value'],true);


		$this->assign('dialog_kg', $dialog_kg);

		$this->assign('dialog', $dialog);
		$this->assign('ty', $ty);
		// dump($service);exit();

		$this->assign('service', $service);
		$this->assign('qrcodes', $qrcodes);
		$this->assign('ppts', $ppts);
		$this->assign('tab', $tab);
		$this->assign('systemInfo', $systemInfo);
		return $this->fetch();

	}

	public function recharge() {
       $ss =  DB::table('system_config')->where('id','>',50)->select();
		$Info = DB::table('system_config')->where('name', 'in', ['max_recharge_day', 'min_recharge_day', 'recharge_give_time',
			'recharge_give_max',
			'min_cash_day',
			'max_cash_day',
			'cash_time_day',
			'recharge_give_open',
			'cash_explain',
			'cash_time',
			'cash_switch',
			'cash_ls_condition',
			'recharge_give_punch',
			'recharge_give_condition'
		])->select();
		$systemInfo = [];
		foreach ($Info as $key => $value) {
			$systemInfo[$value['name']] = $value['value'];

		}

		$systemInfo['cash_time'] = $this->object_to_array(json_decode($systemInfo['cash_time']));

		$this->assign('systemInfo', $systemInfo);
		return $this->fetch();
	}

	public function setting() {
		$configs = Request::post();

		$flag = 0;
		$extension = [];
		$extension['type'] = $configs['type'];
		$extension['content'] = $configs['content'];
        $extension_rs  = DB::name('system_config')->where(['name' => 'extension'])->update(['value' => json_encode($extension)]);
        if ($extension_rs){
            $rs7 = DB::name('system_config')->where(['name' => 'promote_url'])->update(['value' =>  $configs['content'][$configs['type']]]);
            if ($rs7) {
                $flag++;
            }
        }
//        $rs7 = DB::name('system_config')->where(['name' => 'promote_url'])->update(['value' => $configs['promote_url']]);
//        if ($rs7) {
//            $flag++;
//        }


		$rs = DB::name('system_config')->where(['name' => 'web_title'])->update(['value' => $configs['web_title']]);
		if ($rs) {
			$flag++;
		}
		$rs1 = DB::name('system_config')->where(['name' => 'web_open'])->update(['value' => $configs['web_open']]);
		if ($rs1) {
			$flag++;
		}
		if ($configs['safecode'] != '') {
			$rs2 = DB::name('system_config')->where(['name' => 'safecode'])->update(['value' => md5($configs['safecode'])]);
			if ($rs2) {
				$flag++;
			}
		}

		$rs3 = DB::name('system_config')->where(['name' => 'web_logo'])->update(['value' => $configs['logo']]);
		if ($rs3) {
			$flag++;
		}

		$rs4 = DB::name('system_config')->where(['name' => 'domain_name'])->update(['value' => $configs['domain_name']]);
		if ($rs4) {
			$flag++;
		}
		
		// $rs5 = DB::name('system_config')->where(['name' => 'web_line'])->update(['value' => json_encode(explode(',', $configs['web_line']))]);

		// if ($rs5) {
		// 	$flag++;
		// }

		$rs6 = DB::name('system_config')->where(['name' => 'app_qrcode'])->update(['value' => $configs['app_qrcode']]);
		if ($rs6) {
			$flag++;
		}



		$rs8 = DB::name('system_config')->where(['name' => 'allow_login_ip'])->update(['value' => $configs['allow_login_ip']]);
		if ($rs8) {
			$flag++;
		}

		$rs9 = DB::name('system_config')->where(['name' => 'web_login'])->update(['value' => $configs['web_login']]);
		if ($rs9) {
			$flag++;
		}

		$rs10 = DB::name('system_config')->where(['name' => 'home_url'])->update(['value' => $configs['home_url']]);
		if ($rs10) {
			$flag++;
		}

		$rs11 = DB::name('system_config')->where(['name' => 'login_verify'])->update(['value' => $configs['login_verify']]);
		if ($rs10) {
			$flag++;
		}

		if ($flag > 0) {
			$this->success('配置成功');
		} else {
			$this->error('配置失败');
		}
	}

	public function recharge_setting() {
        $type = input('type');
        if(isset($type)){
            $sezhi = input('sezhi');
            $rs = DB::name('system_config')->where(['name' => 'recharge_give_condition'])->update(['value' => $sezhi]);
            if ($rs > 0) {
                return json(array('error' => 0, 'msg' => '配置成功'));
            } else {
                return json(array('error' => 0, 'msg' => '配置失败'));
            }
        }
		$configs = Request::post();

		$cash_time['start_time'] = $configs['start_time'];
		$cash_time['end_time'] = $configs['end_time'];
		$configs['cash_time'] = json_encode($cash_time);

		$flag = 0;
		foreach ($configs as $key => $value) {
			$rs = DB::name('system_config')->where(['name' => $key])->update(['value' => $value]);
			if ($rs) {
				$flag++;
			}
		}

		if ($flag > 0) {
			$this->success('配置成功');
		} else {
			$this->error('配置失败');
		}
	}

	public function recharge_rule() {
		if (Request::method() == 'GET') {
			$rules = DB::table('recharge_give')->where('type',1)->order('id ASC')->select();
			$this->assign('rules', $rules);
			return $this->fetch();
		} else {
			$data = Request::post('final_array/a');
			$model_count = DB::table('recharge_give')->where('type',1)->count();
			$data_count = count($data);
			$flag = 0;
			if ($data_count >= $model_count) {
				foreach ($data as $key => $value) {
					$give_data['id'] = $key + 1;
					$give_data['begin'] = $value[0];
					$give_data['end'] = $value[1];
					$give_data['percent'] = $value[2];
					$give_data['type'] = 1;
					if (($key + 1) <= $model_count) {
						$rs = DB::name('recharge_give')->update($give_data);
						if ($rs) {
							$flag++;
						}
					} else {
						$rs = DB::name('recharge_give')->insert($give_data);
						if ($rs) {
							$flag++;
						}
					}
				}
			} else {

				$model_data = DB::table('recharge_give')->select();
				foreach ($model_data as $key => $value) {
					foreach ($data as $k => $val) {
						if (($key + 1) <= $data_count) {
							$give_data['id'] = $k + 1;
							$give_data['begin'] = $val[0];
							$give_data['end'] = $val[1];
							$give_data['percent'] = $val[2];
							$give_data['type'] = 1;
							$rs = DB::name('recharge_give')->update($give_data);
							if ($rs) {
								$flag++;
							}
						} else {
							$rs = DB::table('recharge_give')->where('type',1)->delete($key + 1);

							if ($rs) {
								$flag++;
							}
						}
					}
				}
			}

			if ($flag > 0) {
				return json(array('error' => 0, 'msg' => '修改成功'));
			} else {
				return json(array('error' => 1, 'msg' => '修改失败,请完善数据'));
			}
		}

	}
	public function ppt_update() {//幻灯片 保存
		// dump(Request::post());exit();
		$img_url = Request::post('img_url/a');
		$img_href = Request::post('img_href/a');
		$paixu = Request::post('paixu/a');

		$ppts = [];
		foreach ($img_url as $key => $value) {
			$ppts[$key]['img_url'] = $value;
			$ppts[$key]['img_href'] = $img_href[$key];
			$ppts[$key]['paixu'] = $paixu[$key];
		}
        $arr1 = array_column($ppts, 'paixu');

        array_multisort($arr1,SORT_ASC,$ppts );
		$rs = DB::table('system_config')->where(['name' => 'web_ppt'])->update(['value' => json_encode($ppts)]);
		if ($rs) {
			$this->success('修改成功', url('djycpgk/system/index', array('tab' => 2)));
		} else {
			$this->error('保存失败');
		}

	}

	public function qrcode_update() {

		$qrcode = Request::post('img_url/a');
		$cat = Request::post('cat/a');
		//dump(Request::param());die;
		$qrcodes = [];
		foreach ($qrcode as $key => $value) {
			$qrcodes[$key]['qrcode'] = $value;
			$qrcodes[$key]['cat'] = $cat[$key];
		}

		$rs = DB::table('system_config')->where(['name' => 'offline_qrcode'])->update(['value' => json_encode($qrcodes)]);
		if ($rs) {
			$this->success('修改成功', url('djycpgk/system/index', array('tab' => 3)));
		} else {
			$this->error('保存失败');
		}

	}

	public function imgupload() {//图片上传
		$file = request()->file('file');
		$info = $file->validate(['size' => 1024 * 1024 * 2, 'ext' => 'jpg,png,gif,jpeg,svg'])->move('uploads');
		if ($info) {
			// 成功上传后 获取上传信息
			$path = '/uploads/' . $info->getSaveName();
			return json(array('error' => 0, 'msg' => $path));
		} else {
			// 上传失败获取错误信息
			return json(array('error' => 1, 'msg' => $file->getError()));
		}
	}

	public function user() {
		if (Request::method() == 'GET') {

			$Info = DB::table('system_config')->where('name', 'in', ['demo_user', 'new_regist', 'max_rebates', 'min_rebates', 'child_one_rate', 'child_two_rate', 'child_three_rate', 'integral', 'login_integral'])->select();

			$systemInfo = [];
			foreach ($Info as $key => $value) {
				$systemInfo[$value['name']] = $value['value'];
			}
			$this->assign('systemInfo', $systemInfo);
			return $this->fetch();

		} else {

			$configs = Request::post();
			$flag = 0;
			foreach ($configs as $key => $value) {
				$rs = DB::name('system_config')->where(['name' => $key])->update(['value' => $value]);
				if ($rs) {
					$flag++;
				}
			}

			if ($flag > 0) {
				$this->success('配置成功');
			} else {
				$this->error('配置失败');
			}
		}

	}

	public function bank_pay() {
		$paginate = 15;
        $map = [];
		$map = [['type','=',0]];
		$pageParam['query'] = [];

		$list = DB::table('bank_pay')->where($map)->order("id desc")->paginate($paginate)->each(function ($item, $index) {
		    if ($item['name'] !='payalipay' && $item['name'] !='payweixin'){
                $item['number'] = json_decode($item['number']);
            }
			$item['bank_name'] = bankTool($item['name']);

			$item['user_name'] = json_decode($item['user_name']);
//			dump($item);
			return $item;
		});
//		 dump($list);
		$this->assign('list', $list);
		return $this->fetch();
	}

	public function bank_edit_y() {

		if (Request::method() == 'GET') {
			$bankinfo = Db::table('bank_pay')->where('id', Request::param('bank_id'))->find();

			$bankTool = bankTool('');
			// dump($bankinfo);

			$this->assign('bankTool', $bankTool);
			$this->assign('bankinfo', $bankinfo);
			return $this->fetch();

		} else {
			// dump(Request::param());exit(); 
			$validate = validate('System');  
			$data2 = Request::param();
		    if(!$validate->check($data2)){//验证 数据
					$this->error($validate->getError());

			}else{

			// dump(Request::param());exit();
			$data['name'] = Request::param('name');
			$data['number'] = Request::param('number');
			$data['user_name'] = Request::param('user_name');
			$data['explain'] = Request::param('explain');
			$data['slogan'] = Request::param('slogan');
			$data['min'] = Request::param('min');
			$data['max'] = Request::param('max');
			// $data['fileupload'] = Request::param('fileupload');
			$data['qr_code'] = Request::param('qr_code');
			$data['sort'] = Request::param('sort');
			$data['switch'] = Request::param('switch');


			// unset($data['fileupload']);
			// dump($data);die;
			$rs = Db::table('bank_pay')->where('id', Request::param('id'))->update($data);

			if ($rs) {
				$this->success('修改成功', url('djycpgk/system/bank_pay'));
			} else {
				$this->error('修改失败');
			}
			}
		}
	}

	public function bank_add_y() {
		if (Request::method() == 'GET') {
			$bankTool = bankTool('');
			$this->assign('bankTool', $bankTool);
			return $this->fetch();
		} else {
			$validate = validate('System');
			$data = Request::param();
		    if(!$validate->check($data)){//验证 数据
					$this->error($validate->getError());

			}else{
			// dump($data);exit();

			unset($data['fileupload']);
			unset($data['/djycpgk/system/bank_add_html']);
			$data_exist = Db::table('bank_pay')->where('number', Request::param('number'))->find();
			if (null != $data_exist) {

				$this->error('账户号已存在');
			}

			$rs = Db::table('bank_pay')->insert($data);
			if ($rs) {
				$this->success('添加成功', url('djycpgk/system/bank_pay'));
			} else {
				$this->error('添加失败');
			}
			}
		}

	}

	public function bank_delete() {
		$rs = Db::table('bank_pay')->where('id', Request::param('data_number'))->delete();
		if ($rs) {
			return json(array('error' => 0, 'msg' => '删除成功'));
		} else {
			return json(array('error' => 1, 'msg' => '删除失败'));
		}
	}

	public function log_clear() {
		$rs = Log::clear();
		dump($rs);
	}

	public function bak() {
		$db = new Backup();
		$baklist = $db->fileList();
		$this->assign('list', $baklist);
		return $this->fetch();
	}

	public function backupall() {

		$db_name = Config::get('database.database');
		$db = new Backup();
		$sql = "select table_name
                        from information_schema.tables
                        where table_schema='{$db_name}'";
		$tableslist = DB::query($sql);
		$tables = [];

		foreach ($tableslist as $key => $value) {
			$tables[] = $value['table_name'];
		}

		$file = ['name' => date('Ymd-His'), 'part' => 1];

		$start = $db->setFile($file)->backupALL($tables, 0);
		if (0 == $start) {
			return json(array('error' => 0, 'msg' => '备份成功'));
		} else {
			return json(array('error' => 1, 'msg' => '备份失败'));
		}
	}

	public function backupdel() {
		$db = new Backup();
		$rs = $db->delFile(Request::param('time'));
		if ($rs) {
			return json(array('error' => 0, 'msg' => '删除成功'));
		} else {
			return json(array('error' => 1, 'msg' => '删除失败'));
		}
	}

	public function importbak() {
		$db = new Backup();
		$file = $db->getFile('timeverif', Request::param('time'));
		$start = 0;
		$start = $db->setFile($file)->import($start);
		if (0 == $start) {
			return json(array('error' => 0, 'msg' => '恢复成功'));
		} else {
			return json(array('error' => 1, 'msg' => '恢复失败'));
		}
	}

	public function downbak() {
		$db = new Backup();
		$db->downloadFile(Request::param('time'));
	}

	public function newslist() {
		$articles = DB::table('article')->field('id,title')->where('cat_id', 2)->select();
		$this->assign('articles', $articles);
		return $this->fetch();
	}

	public function lotto() {
		if (Request::method() == 'GET') {
			$data = Db::table('system_config')->where('name', 'turntable')->find()['value'];
			$lotto = $this->object_to_array(json_decode($data,true));
			 // halt($lotto);
			 $mc = [];//获取名称
			 $bl = [];//获取中奖比例
			 foreach ($lotto['data'] as $key => $value) {
			 		$mc[] = $value['text'];
			 		$bl[] = $value['percent'];
			 }
			 // print_r($mc);
			 $this->assign('mc', implode(",", $mc));
			 $this->assign('bl', implode(",", $bl));
			 $this->assign('lotto', $lotto);
			return $this->fetch();
		} else {
			$data = [];
			$final_array = Request::param('final_array/a');
			if (count($final_array) < 3 || count($final_array) > 16) {
				return json(['error' => 1, 'msg' => '奖品数最少3个，最多16个']);
			}

			$total_percent = array_sum(array_map(function ($val) {return $val["2"];}, $final_array));
			if ($total_percent > 100) {
				return json(['error' => 1, 'msg' => '中奖几率总和不能超过100']);
			}
			$data['use_point'] = Request::param('use_point');
			$data['switch'] = Request::param('switch_id');
			$data['recharge_condition'] = Request::param('recharge_condition');
			$data['time'] = Request::param('time');

			foreach ($final_array as $key => $val) {

				$data['data'][$key]['text'] = $val[0];
				$data['data'][$key]['point'] = $val[1];
				$data['data'][$key]['percent'] = $val[2];
			}

			$rs = Db::table('system_config')->where('name', 'turntable')->update(['value' => json_encode($data)]);

			if ($rs) {
				return json(['error' => 0, 'msg' => '修改成功']);
			} else {
				return json(['error' => 1, 'msg' => '修改失败']);
			}

		}

	}

	public function chatconfig() {
		if (Request::method() == 'GET') {
			$data = Db::table('system_config')->where('name', 'chat_config')->find()['value'];
			$chat = $this->object_to_array(json_decode($data));
            $chat['say_id'] = implode(',',$chat['say_id']);
			$this->assign('chat', $chat);
			return $this->fetch();
		} else {

			$data['is_open'] = Request::param('is_open');
			$data['explain'] = Request::param('explain');
			$data['say_id'] = explode(',',Request::param('say_id')) ;
			$rs = Db::table('system_config')->where('name', 'chat_config')->update(['value' => json_encode($data)]);

			if ($rs) {
				return json(['error' => 0, 'msg' => '修改成功']);
			} else {
				return json(['error' => 1, 'msg' => '修改失败']);
			}

		}

	}

	public function serviceconfig() {
		 // dump(Request::param());die;
		$data['online_service'] = explode(',', Request::param('online_service'));
		$data['qq_service'] = explode(',', Request::param('qq_service'));
		$data['wx_service'] = explode(',', Request::param('wx_service'));
		$rs = Db::table('system_config')->where('name', 'customer_service')->update(['value' => json_encode($data)]);
		if ($rs) {
			$this->success('保存成功', url('djycpgk/system/index', array('tab' => 3)));
		} else {
			$this->error('保存失败');
		}
	}

	public function tychange() {
		$ty = htmlspecialchars_decode(Request::param('content'));
		$rs = Db::table('system_config')->where('name', 'register_treaty')->update(['value' => $ty]);

		if ($rs) {
			$this->success('保存成功', url('djycpgk/system/index', array('tab' => 4)));
		} else {
			$this->error('保存失败');
		}
	}

	public function dialog() {
		$value = Request::param('value');
		// dump($value); exit();

		$dialog = htmlspecialchars_decode(Request::param('content1'));

		//判断 获取的数据是否和数据库一样一样给出提示
		$rs_valus =  Db::table('system_config')->where('name', 'dialog')->find()['value'];
		$kg_valus = Db::table('system_config')->where('name', 'dialog_kg')->find()['value'];

		if ($value == $kg_valus &&  $dialog ==$rs_valus) {
			$this->error('内容未改变，无法修改。	');
		}


		//修改数据
		$rs = Db::table('system_config')->where('name', 'dialog')->update(['value' => $dialog]);
		$kg = Db::table('system_config')->where('name','dialog_kg')->update(['value' => $value]);

		//判断是否 修改成功
		if ($rs && $kg) {
			$this->success('保存成功', url('djycpgk/system/index', array('tab' => 5)));
		} elseif($rs){
			$this->success('公告修改保存成功', url('djycpgk/system/index', array('tab' => 5)));
		}elseif ($kg) {
			$this->success('状态保存成功', url('djycpgk/system/index', array('tab' => 5)));
		}
		else {
			$this->error('保存失败');
		}
	}

	public function dataClean() {
		if (Request::method() == 'GET') {
			return $this->fetch();
		} else {
//			dump(Request::param());die();
			$type = Request::param('type');
			if (Request::param('start_time') != '' && Request::param('end_time') == '') {
				$map['create_time'] = ['create_time', '>', strtotime(Request::param('start_time'))];
			}
			if (Request::param('start_time') == '' && Request::param('end_time') != '') {
				$map['create_time'] = ['create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];
			}
			if (Request::param('start_time') != '' && Request::param('end_time') != '') {
				$map['create_time'] = ['create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];
			}
            if ($type==1) {
                $biao = 'capital_detail';
            }elseif ($type==2) {
                $biao = 'betting';
            }elseif ($type==3) {
                $biao = 'betting_zhui';
            }elseif ($type==4) {
                $biao = 'lottery_code';
            }elseif ($type==5){
                $biao = 'chat_room';
            }
            $rs = DB::table($biao)->where($map)->delete();
			if ($rs) {
				return json(['error' => 0, 'msg' => '删除成功']);
			}else{
				return json(['error' => 1, 'msg' => '删除失败']);
			}

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



}
