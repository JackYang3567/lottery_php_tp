<?php
namespace app\djycpgk\controller;
use app\djycpgk\model\ApiBetting;
use think\Db;
use think\facade\Cache;
use think\facade\Request;
use app\djycpgk\model\ChatRoom;
use app\djycpgk\model\CapitalAudit;

class Index extends Rbac
{

    public function index()
    {
        //phpinfo();
        $t = time();
        $day_start = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
        $day_end = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));
        $map['create_time'] = ['create_time', '<', time()];
        // $map['create_time'] = ['create_time','between',[$day_start,$day_end]];
        $chat = Db::table('chat_room')->where($map)->order('create_time desc')->limit(20)->select();

        $chat = array_reverse($chat);
        foreach ($chat as $key => $value)
        {
            if ($chat[$key]['user_id'] == 0)
            {
                $chat[$key]['username'] = '管理员';
            }
            else
            {
                $chat[$key]['username'] = Db::table('user')->where('id', $chat[$key]['user_id'])->find()['username'];
            }
        }

        $recharge = DB::table('capital_audit')->where('state', 0)->where('type', 0)->count();
        $cash = DB::table('capital_audit')->where('state', 0)->where('type', 1)->count();
        $sxf = DB::table('capital_audit')->where('state', 0)->where('type','in',[3,4] )->count();
        $this->assign('recharge', $recharge);
        $this->assign('cash', $cash);
        $this->assign('sxf', $sxf);


        //检测在线用户
        $end_time = time();
        $start_time = time() - 60 * 20;
        $people_count = Db::table('login_log')->where('create_time', 'between', [$start_time, $end_time])->group('user_id')->count();
        if (!$people_count){
            $people_count = 0;
        }
        //今日 注册 充值 提现 人数
        $ks = strtotime(date("Y-m-d"),time());
        $js =strtotime(date("Y-m-d"),time())+60*60*24;

        $cz_rs = DB::table('capital_audit')->where('create_time', 'between', [$ks, $js])->where('state', 1)->where('type', 0)->count();
        $tx_rs = DB::table('capital_audit')->where('create_time', 'between', [$ks, $js])->where('state', 1)->where('type', 1)->count();

        $zhuce = Db::table('user')->where('type',0)->where('create_time', 'between', [$ks, $js])->count();


        $this->assign('cz_rs', $cz_rs);
        $this->assign('tx_rs', $tx_rs);
        $this->assign('zhuce', $zhuce);
        $this->assign('people_count', $people_count);
        $this->assign('chat', $chat);



        return $this->fetch();

    }

    public function top()
    {

        return $this->fetch();
    }

    public function left()
    {
        return $this->fetch();
    }

    public function childindex()
    {

        $t = time();
        $day_start = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
        $day_end = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));



        //今日 注册 充值 提现 人数
        $map['create_time'] = ['create_time', 'between', [$day_start, $day_end]];
        $map['user_id'] = ['user_id', 'in', function($query) {
            $query->table('user')->field('id')->where('type', 0)->select();
        }];
		$map['judge'] = ['judge', '=', 0];

        //今日消费
        $todaybetting = DB::table('capital_detail')->field('count(*) as count,sum(money) as sum_money')->where($map)->where(['type' => 0, 'create_time' => ['create_time', 'between', [$day_start, $day_end]]])->find();
        $today_betting_count =$todaybetting['count'];
		
        $today_betting_money = number_format(abs($todaybetting['sum_money']), 2, '.', ',');
        $this->assign('today_betting_count', $today_betting_count);
        $this->assign('today_betting_money', $today_betting_money);

        //今日派奖
        $todaypj = DB::table('capital_detail')->field('count(*) as count,sum(money) as sum_money')->where($map)->where(['type' => 3, 'create_time' => ['create_time', 'between', [$day_start, $day_end]]])->find();
        $today_pj_count = $todaypj['count'];
        $today_pj_money = number_format($todaypj['sum_money'], 2, '.', ',');
        $this->assign('today_pj_count', $today_pj_count);
        $this->assign('today_pj_money', $today_pj_money);

        //代理商 占成

        $maw[] = ['game_end_time','>',date("Y/m/d")];
        $maw[] = ['game_end_time','<',date("Y/m/d").'23:59:59'];
        $dls_zc = 0;
        $zc = Db::table('system_config')->where('name','zhancheng')->find()['value'];//获取占成
        $dls = ApiBetting::field('sum(profit) as profit')->where($maw)->find()['profit'];
        if ($dls < 0){
            $dls_zc = round($dls*-1*$zc/100,2);
        }else{
            $dls_zc = 0;
        }
        $this->assign('dls_zc', $dls_zc);

        //盈利
        $today_yl = abs($todaybetting['sum_money'])-abs($todaypj['sum_money']-$dls_zc);

        //昨日返佣()

        //获取昨天00:00
        $timestart = strtotime(date('Y-m-d' . '00:00:00', time() - 3600 * 24));
        //获取今天00:00
        $timeend = strtotime(date('Y-m-d' . '00:00:00', time()));
        $time['create_time'] = ['create_time', 'between', [$timestart, $timeend]];

        $todayfs = DB::table('capital_detail')->field('count(*) as count,sum(money) as sum_money')->where($map)->where(['type' => 11, 'create_time' => ['create_time', 'between', [$timestart, $timeend]]])->find();
        $today_fs_count = $todayfs['count'];
        $today_fs_money = number_format($todayfs['sum_money'], 2, '.', ',');
        $this->assign('today_fs_count', $today_fs_count);
        $this->assign('today_fs_money', $today_fs_money);

        //今日存款
        $today_recharge = DB::table('capital_detail')->field('count(*) as count,sum(money) as sum_money')->where($map)
            ->where(['type' => ['type', 'in', [2, 7,15]], 'create_time' => ['create_time', 'between', [$day_start, $day_end]]])
        ->find();
        //今日取款
        $today_cash = DB::table('capital_detail')->field('count(*) as count,sum(money) as sum_money')->where($map)->where(['type' => 1, 'create_time' => ['create_time', 'between', [$day_start, $day_end]]])->find();
        $today_cash['sum_money'] = abs($today_cash['sum_money']);


        $today_recharge_count = $today_recharge['count'];
        $today_recharge_money = number_format($today_recharge['sum_money'], 2, '.', ',');
        $this->assign('today_recharge_count', $today_recharge_count);
        $this->assign('today_recharge_money', $today_recharge_money);

        $today_cash_count = $today_cash['count'];
        $today_cash_money = number_format($today_cash['sum_money'], 2, '.', ',');
        $this->assign('today_cash_count', $today_cash_count);
        $this->assign('today_cash_money', $today_cash_money);

        //昨日佣金
        $today_commission = DB::table('capital_detail')->field('count(*) as count,sum(money) as sum_money')->where($map)->where(['type' => 8, 'create_time' => ['create_time', 'between', [$timestart, $timeend]]])->find();
        $today_commission_count = $today_commission['count'];
        $today_commission_money = number_format($today_commission['sum_money'], 2, '.', ',');
        $this->assign('today_commission_count', $today_commission_count);
        $this->assign('today_commission_money', $today_commission_money);


        //今日赠金
        $today_give = DB::table('capital_detail')->field('count(*) as count,sum(money) as sum_money')->where($map)->where(['type' => ['type', 'in', [5, 10]], 'create_time' => ['create_time', 'between', [$day_start, $day_end]]])->find();
        $today_give_count = $today_give['count'];
        $today_give_money = number_format($today_give['sum_money'], 2, '.', ',');




        $cz_rs = DB::table('capital_audit')->where('create_time', 'between', [$day_start, $day_end])->where('state', 1)->where('type', 0)->count();
        $tx_rs = DB::table('capital_audit')->where('create_time', 'between', [$day_start, $day_end])->where('state', 1)->where('type', 1)->count();
        $zhuce = Db::table('user')->where('type',0)->where('create_time', 'between', [$day_start, $day_end])->count();
//        dump()
        $this->assign('cz_rs', $cz_rs);
        $this->assign('tx_rs', $tx_rs);
        $this->assign('zhuce', $zhuce);

        $this->assign('today_yl', $today_yl);
        $this->assign('today_give_count', $today_give_count);
        $this->assign('today_give_money', $today_give_money);
        $this->assign('start_time', Request::param('get.start_time'));
        $this->assign('end_time', Request::param('get.end_time'));

        return $this->fetch();
    }

    public function cache_clear()
    {

        $rs = Cache::clear();
        if ($rs)
        {
            return json(array('error' => 0, 'msg' => '清除成功'));
        }
        else
        {
            return json(array('error' => 1, 'msg' => '清除失败'));
        }

    }

    public function checknewdata() //充值提现提示
    {
        $new_recharge_data = DB::table('capital_audit')->where('state', 0)->where('type', 0)->count();
        $new_cash_data = DB::table('capital_audit')->where('state', 0)->where('type', 1)->count();
        $sxf = DB::table('capital_audit')->where('state', 0)->where('type','in',[3,4] )->count();

        // 今日 注册  提现 充值 人数
        $ks = strtotime(date("Y-m-d"),time());
        $js =strtotime(date("Y-m-d"),time())+60*60*24;


        $cz_rs = CapitalAudit::getCapitalList(0);
        $tx_rs = CapitalAudit::getCapitalList(1);
        $zhuce = Db::table('user')->where('type',0)->where('create_time', 'between', [$ks, $js])->count();
        //获取当前在线人数
        $start_time = time() - 60 * 20;
        $data_count = Db::table('user')->where('type',0)->where('active_time', '>=', $start_time)->count();
        if (!$data_count){
            $data_count = 0;
        }

        return json_encode(array('new_recharge' => $new_recharge_data, 'new_cash' => $new_cash_data,'rs'=>$zhuce,'cz'=>$cz_rs,'tx'=>$tx_rs,'data_count'=>$data_count,'sxf'=>$sxf));
    }

    public function liaoTianshi($last_id = null){ //聊天室
//            dump($last_id);
            if ($last_id == null) {
                $jg =  json_encode(['error' => 1, 'content' => '', 'count' => 0]);
                return $jg;
            }
            $data = ChatRoom::getChatList($last_id);
            if (!$data->toArray()) {
                return ;
            }
            $data->append(['username', 'create_time']);
            $jg =  json_encode(['error' => 0, 'content' => $data, 'count' => count($data)]);
            return $jg;
    }
    public function sx($id=0){ //实时刷新
        $zd_id = DB::table('capital_audit')->field('MAX(id) id')->where('type',1)->find();
        $ss= $id;//获取页面传递过来的最大id
        if ($zd_id['id'] > $ss) {
            return 0;
        }else {
            return 1;
        }
    }
    public function czsx(){ //充值刷新
        $zd_id = DB::table('capital_audit')->field('MAX(id) id')->where('type',0)->find();
        $ss= Request::param('id');//获取页面传递过来的最大id

        if ($zd_id['id'] > $ss) {
            return 0;
        }else {
            return 1;
        }
    }
    public function  changLunxun($type){ //长轮训
            longPolling(function() use ($type){
                if ($type == 'lts'){ //聊天室
                    $pd =  $this->liaoTianshi(Request::param('last_id'));

                    if(!$pd){
                        return;
                    }
                    echo $pd;
                    return true;

                }else  if ($type == 'checknewdata'){//统计
                    $data = Request::post('');
                    $tj = $this->checknewdata();
                    if (isset($data['pd'][0]) && $data['pd'][0]==0){
                        echo $tj;
                        return true;
                    }
                    $tj = json_decode($tj,true);
                    foreach ($tj as $k =>$v){
                        $ss[$k] = (string)$v;
                    }
                    $ss['type'] = 'checknewdata';

                    sort($data);
                    sort($ss);
                    if($data == $ss) {
                        return ;
                    }
                    echo json_encode($tj);
                    return true;
                }else  if ($type == 'sx'){
                    $ss =  $this->sx(Request::post('id'));
                    if ($ss){

                        return ;
                    }
                    echo $ss;
                    return true;
                }else  if ($type == 'czsx'){
                    $ss =  $this->czsx(Request::post('id'));
                    if ($ss){
                        return ;
                    }
                    echo $ss;
                    return true;
                }else if($type == 'zd_id')
                {

                }

            });

    }

    public function adminchat()
    {
        $data['content'] = Request::param('content');
        $data['user_id'] = 0;
        $data['create_time'] = time();
        $rs = Db::name('chat_room')->insert($data);
        if ($rs)
        {
            return json(['error' => 0, 'time' => date('Y-m-d H:i:s', time())]);
        }
        else
        {
            return json(['error' => 1, 'time' => date('Y-m-d H:i:s', time())]);
        }
    }

    public function newchat()
    {
        if (Request::param('last_id') == null) {
             return json(['error' => 1, 'content' => '', 'count' => 0]);
        }

        $map['id'] = ['id', '>', Request::param('last_id')];
        $map['user_id'] = ['user_id', '<>', 0];
        $data = Db::table('chat_room')->where($map)->select();
        if (count($data) != 0)
        {

            foreach ($data as $key => $value)
            {
                $data[$key]['username'] = Db::table('user')->where('id', $data[$key]['user_id'])->find()['username'];
                $data[$key]['create_time'] = date('Y-m-d H:i:s', $data[$key]['create_time']);
            }

            return json(['error' => 0, 'content' => $data, 'count' => count($data)]);
        }
        else
        {
            return json(['error' => 1, 'content' => '', 'count' => 0]);
        }

    }

    public function history_chat()
    {

        $map['id'] = ['id', '<', Request::param('id')];
        $pageParam['query']['id'] = Request::param('id');
        $list = Db::table('chat_room')->where($map)->order('id desc')->paginate(15, true, $pageParam)->each(function ($item, $index)
        {
            if ($item['user_id'] == 0)
            {
                $item['username'] = '管理员';
            }
            else
            {
                $item['username'] = Db::table('user')->where('id', $item['user_id'])->find()['username'];
            }
            $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
            return $item;
        });

        $this->assign('list', $list);
        return $this->fetch();
    }
    public function search()
    {
        $map = [];
        $mop = [];
        $maw = [];
        if (Request::param('start_time') != '' && Request::param('end_time') == '')
        {
            $map['create_time'] = ['create_time', '>', strtotime(Request::param('start_time'))];
            $mop['create_time'] = ['create_time', '>', strtotime(Request::param('start_time'))];
            $maw[] = ['game_end_time', '>', Request::param('start_time')];
        }
        if (Request::param('start_time') == '' && Request::param('end_time') != '')
        {
            $map['create_time'] = ['create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];
            $mop['create_time'] = ['create_time', '<', strtotime(Request::param('end_time')) + 3600 * 24 - 1];
            $maw[] = ['game_end_time', '<', Request::param('end_time').':23:59:59'];
        }
        if (Request::param('start_time') != '' && Request::param('end_time') != '')
        {
            $map['create_time'] = ['create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];
            $mop['create_time'] = ['create_time', 'between', [strtotime(Request::param('start_time')), strtotime(Request::param('end_time')) + 3600 * 24 - 1]];
            $maw[] = ['game_end_time', 'between', [Request::param('start_time'), Request::param('end_time').':23:59:59']];
        }
        $map['user_id'] = ['user_id', 'in', function($query) {
            $query->table('user')->field('id')->where('type', 0)->select();
        }];
        $ss = Db::table('capital_detail')
                ->field('sum(money) as money,type,COUNT(id) as sl')
                ->group('type')
                ->where($map)
                ->where('judge',0)
            ->select();

        //消费记录
        $betting_count = 0;
        $betting_money = 0;
        //派奖
        $pj_count = 0;
        $pj_money = 0;
        //反水
        $fs_count = 0;
        $fs_money = 0;
        //返佣
        $commission_count = 0;
        $comission_money = 0;
//        充值
        $cz_count = 0;
        $cz_money = 0;
        //提现
        $tx_count = 0;
        $tx_money = 0;
        //赠送
        $give_count = 0;
        $give_money = 0;
        foreach ($ss as $v){
            switch ($v['type']){
                case 0:
                    $betting_count = $v['sl'];
                    $betting_money = $v['money'];
                    break;
                case 3:
                    $pj_count = $v['sl'];
                    $pj_money = $v['money'];
                    break;
                case 11:
                    $fs_count = $v['sl'];
                    $fs_money = $v['money'];
                    break;
                case 8:
                    $commission_count = $v['sl'];
                    $comission_money = $v['money'];
                    break;
                case 7:
                case 2:
                     $cz_count += $v['sl'];
                     $cz_money += $v['money'];
                      break;
                case 1:
                    $tx_count = $v['sl'];
                    $tx_money = $v['money'];
                    break;
                case 5:
                    $give_count = $v['sl'];
                    $give_money = $v['money'];
                    break;
                default:
                    break;
            }
        }

        $zc = Db::table('system_config')->where('name','zhancheng')->find()['value'];//获取占成
        $dls = ApiBetting::field('sum(profit) as profit')->where($maw)->find()['profit'];
        if ($dls < 0){
            $dls_zc = round($dls*-1*$zc/100,2);
        }else{
            $dls_zc = 0;
        }
        $this->assign('dls_zc', $dls_zc);
        $yl = abs(round($betting_money,2)) -abs(round($pj_money,2)-$dls_zc);
        $betting_money = number_format( abs($betting_money), 2, '.', ',');
		
        $pj_money = number_format( $pj_money, 2, '.', ',');
        $fs_money = number_format( $fs_money, 2, '.', ',');
        $comission_money = number_format( $comission_money, 2, '.', ',');
        $cz_money = number_format( $cz_money, 2, '.', ',');
        $tx_money = number_format( $tx_money, 2, '.', ',');
        $give_money = number_format( $give_money, 2, '.', ',');
        // 在线人数 充值人数 提现人数
        $cz_rs = DB::table('capital_audit')->where($mop)->where('state', 1)->where('type', 0)->count();
        $tx_rs = DB::table('capital_audit')->where($mop)->where('state', 1)->where('type', 1)->count();
        $zhuce = Db::table('user')->where('type',0)->where($mop)->count();

        $this->assign('cz_rs', $cz_rs);
        $this->assign('tx_rs', $tx_rs);
        $this->assign('zhuce', $zhuce);

        $this->assign('yl', $yl);

        $this->assign('betting_count', $betting_count);
		
        $this->assign('betting_money', $betting_money);
        $this->assign('commission_count', $commission_count);
        $this->assign('comission_money', $comission_money);
        $this->assign('pj_count', $pj_count);
        $this->assign('pj_money', $pj_money);
        $this->assign('cz_count', $cz_count);
        $this->assign('cz_money', $cz_money);
        $this->assign('tx_count', $tx_count);
        $this->assign('tx_money', $tx_money);
        $this->assign('fs_count', $fs_count);
        $this->assign('fs_money', $fs_money);
        $this->assign('give_count', $give_count);
        $this->assign('give_money', $give_money);

        $this->assign('start_time', Request::param('start_time'));
        $this->assign('end_time', Request::param('end_time'));

        return $this->fetch();
    }

}
