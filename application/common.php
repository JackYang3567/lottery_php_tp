<?php

// 应用公共文件
use think\Db;
use app\home\model\Betting;
use app\home\controller\LotteryL;
use app\home\model\User;

use app\foropencode\controller\OpenKs;
use app\foropencode\controller\Openpk10;
/**
 * 所有涉及到资金变动的，调用这个函数来变动(会写入资金明细表，和累加表)
 */
function moneyAction($config = []){

    /* [
      'uid'=>$user['id'],
      'money'=>$basic['hm']['bd'],
      'type'=>12,
      'explain'=>'合买保底冻结'
      'create_time'=>有默认值
    ] */

    /*
    type 0:下注 1;提现 2:线下充值 3:中奖 4:扣款 5:赠送 6:退款 7:在线充值 8:返佣 9:签到
        10:抽奖 11:返水 12:冻结 13:冻结返还 14:保底投注 15:系统充值 16:聊天室红包 17:个人充值红包
        18:日负反水,19:游戏上分,20:游戏下分
    */
    $return_data = [
        'code' => 0,
        'msg' => ''
    ];
    if(empty($config)){
        $return_data['msg'] = '没有更新资金得数据';
        return $return_data;
    }
    $turn = [
        //消费
        [
            // 累加表字段,如果不需要累加,则为 false
            'field' => 'use_money',
            // 需要操作的用户字段
            'action' => [
                // 字段、类型(true 增加 false扣除)
                [ 'money',false ]
            ]
        ],
        //取款
        [
            'field' => 'out_money',
            'action' => [
                [ 'no_money',false ]
            ],
        ],
        //线下充值
        [
            'field' => 'in_money',
            'action' => [
                ['money',true ]
            ]
        ],
        //中奖
        [
            'field' => 'winning',
            'action' => [
                ['money',true ]
            ]
        ],
        //扣款
        [
            'field' => 'debit',
            'action' => [
                ['money',false ]
            ]
        ],
        //赠送
        [
            'field' => 'give',
            'action' => [
                ['money',true ]
            ]
        ],
        //退款
        [
            'field' => 'refund',
            'action' => [
                ['money',true ]
            ]
        ],
        //在线充值
        [
            'field' => 'online_money',
            'action' => [
                ['money',true ]
            ]
        ],
        //返佣
        [
            'field' => 'maid',
            'action' => [
                ['money',true ]
            ]
        ],
        //签到(积分)
        [
            'field' => 'sign',
            'action' => [
                ['point',true ]
            ]
        ],
        //抽奖
        [
            'field' => 'luck',
            'action' => [
                ['money',true ]
            ]
        ],
        //返水
        [
            'field' => 'return',
            'action' => [
                ['money',true ],
                ['off_money',true]
            ]
        ],
        //冻结
        [
            'field' => false,
            'action' => [
                ['money',false ],
                ['no_money',true ]
            ]
        ],
        //保底返还
        [
            'field' => false,
            'action' => [
                ['money',true ],
                ['no_money',false ]
            ]
        ],
        //保底投注
        [
            'field' => 'use_money',
            'action' => [
                ['no_money',false ]
            ]
        ],
        //系统充值
        [
            'field' => false,
            'action' => [
                ['money',true ]
            ]
        ],
        //聊天室红包
        [
            'field' => 'chat_hongbao',
            'action' => [
                ['money',true ]
            ]
        ],
        //个人充值红包
        [
            'field' => 'user_hongbao',
            'action' => [
                ['money',true ]
            ]
        ],
        //日负回水
        [
            'field' => 'return',
            'action' => [
                ['money',true ]
            ]
        ],
        //从网站上分到游戏
        [
            'field' => false,
            'action' => [
                ['money',false ]
            ]
        ],
        //从游戏下分到本站
        [
            'field' => false,
            'action' => [
                ['money',true ]
            ]
        ],
        //发红包
        [
            'field' => false,
            'action' => [
                ['money',false ]
            ]
        ],
    ];
    // 这里是累加表所有字段初始化，如果累加表有变动，则需要修改这里
    $accumulation_data = [
        'maid' => 0,
        'sign' => 0,
        'luck' => 0,
        'in_money' => 0,
        'winning' => 0,
        'give' => 0,
        'return' => 0,
        'use_money' => 0,
        'out_money' => 0,
        'debit' => 0 ,
        'refund' => 0,
        'online_money' => 0,
        'chat_hongbao' => 0,
        'user_hongbao' => 0,
        'user_id' => 0
    ];
    if(count($config) == count($config, 1)){
        $config = [ $config ];
    }
    Db::startTrans();
    foreach ($config as $value) {
        try{
            $value += [
                // 用户id
                'uid' => 0,
                // 操作金额
                'money' => 0,
                // 0:下注 1;提现 2:线下充值 3:中奖 4:扣款 5:赠送 6:退款 7:在线充值 8:返佣 9:签到 10:抽奖 11:返水 12:冻结 13:冻结返还 14:保底投注 15:系统充值 16:聊天室红包 17:个人充值红包 22:抽奖红包
                'type' => 'no',
                // 说明(写入资金明细表的说明)
                'explain' => null
            ];
            $user = Db::table('user')->field('money,point,no_money,type,off_money')->where(['id'=>$value['uid']])->find();
            if(empty($user)){
                $msg = '没有找到id为' . $value['uid'] . '的用户';
                $return_data['msg'] = $msg;
                throw new Exception($msg);
            }
            if($value['money'] <= 0){
                $msg = '特殊错误4';
                $return_data['msg'] = $msg;
                throw new Exception($msg);
            }
            if(empty($value['uid']) || empty($value['money']) || !is_numeric($value['type'])){
                $msg = 'id为' . $value['uid'] . '的用户参数不正确';
                $return_data['msg'] = $msg;
                throw new Exception($msg);
            }
            if(!isset($turn[$value['type']])){
                $msg = '未知的资金操作类型';
                $return_data['msg'] = $msg;
                throw new Exception($msg);
            }
            $user_money = $user['money'];
            //这里是操作会员资金
            foreach ($turn[$value['type']]['action'] as $value1) {
                if(!$value1[1] && $user[$value1[0]] < $value['money']){
                    $msg  = 'id为' . $value['uid'] . '的用户' . $value1[0] . '不足';
                    $return_data['msg'] = $msg;
                    throw new Exception($msg);
                }
                // 这里操作 用户 数据
                $model = Db::table('user')->where(['id'=>$value['uid']]);
                if($value1[1]){
                    $model->setInc($value1[0], $value['money']);
                    if($value1[0] == 'money'){
                        $user_money = $user['money'] + $value['money'];
                    }
                }else{
                    if(!Db::execute('update user set '.$value1[0].'='.$value1[0].' -'.$value['money'].' where '.$value1[0].' -'.$value['money'].' >= 0 and id='.$value['uid'])){
                        throw new Exception('操作过于频繁！');
                    }
                    if($value1[0] == 'money'){
                        $user_money = $user['money'] - $value['money'];
                    }
                }
            }

            /** 这里是提款限制操作 */
            if($value['type'] == 2 || $value['type'] == 7 || $value['type'] == 5 || $value['type'] == 0){
                if(!isset($no_money_set)){
                    $no_money_set = Db::table('system_config')->field('value')->where([ 'name'=>'cash_ls_condition' ])->find()['value'];
                }
                if($no_money_set > 0){
                    if($value['type'] == 0){
                        // 下注操作冻结
                        $user['off_money'] -= $value['money'];
                        if($user['off_money'] < 0){
                            $user['off_money'] = 0;
                        }
                    }else{
                        // 充值、赠送操作冻结
                        $user['off_money'] += $value['money'] / 100 * $no_money_set;
                    }
                    Db::table('user')->data([ 'off_money'=>$user['off_money'] ])->where([ 'id'=>$value['uid'] ])->update();
                }
            }
            /** -- */

            // 这里是需要操作累加表的处理,只要正式会员才会写入累加表
            if($turn[$value['type']]['field'] && $user['type'] == 0){
                $accumulation = Db::table('accumulation')->where(['user_id'=>$value['uid']])->order('create_time DESC')->find();
                if($accumulation){
                    $accumulation_data = $accumulation;
                }
                $accumulation_data['user_id'] = $value['uid'];
                $accumulation_data['create_time'] = $value['create_time'] ?? time();
                // 用户余额是变动之前的余额
                $accumulation_data['money'] = $user['money'];
                $is_create_time = Db::table('accumulation')->field('create_time')->order('create_time DESC')->find();
                // 这里避免累加表数据库时间重复
                if(!empty($is_create_time) && $is_create_time['create_time'] >= $accumulation_data['create_time']){
                    $accumulation_data['create_time'] = $is_create_time['create_time'] + 1;
                }
                //删除 累加表里面的 id
                unset($accumulation_data['id']);
                //这里提前写一条在累加数据之前到数据库，用于报表统计
                Db::table('accumulation')->insert($accumulation_data);
                $accumulation_data['create_time'] += 1;
                $accumulation_data[$turn[$value['type']]['field']] += $value['money'];
                Db::table('accumulation')->insert($accumulation_data);
            }
            Db::table('capital_detail')->insert([
                'user_id' => $value['uid'],
                'money' => $value['money'],
                'type' => $value['type'],
                'explain' => $value['explain'],
                'user_money' => $user_money,
                'create_time' => $value['create_time'] ?? time()
            ]);
        } catch (\Exception $e) {
            $return_data['msg'] = $e->getMessage();
            Db::rollback();
            return $return_data;
        }
    }
    try{
        // if(User::get($value['uid'])->money < 0){
        //   throw new Exception('操作过于频繁');
        // }
        // 卡到这儿
        Db::commit();
    }catch (\Exception $e) {
        Db::rollback();
        $return_data['msg'] = $e->getMessage();
        return $return_data;
    }
    $return_data['code'] = 1;
    $return_data['msg'] = '操作成功';
    return $return_data;
}

function Robot($Robot_config,$expect){

    $del = Db::table('betting')
        ->field('id')
        ->where('explain','Robot')
        ->where('create_time','<',time()-300)
        ->select();
    $del_arr = [];
    if($del){
        foreach ( $del as $v){
            $del_arr []= $v['id'];
        }
        Db::table('betting')->delete($del_arr);
    }
    $Bets = [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,'red','blue','green','yellow','a','b','c','d','min','max','ad','bc','ac','bd'];
    $data = Db::table('user')
        ->field('id')
        ->where('type',2)
        ->select();
    foreach ($Robot_config as $v){
        $lottery = $v['code'];
        $Room = $v['Room'];
        for($i = 0;$i<count($Room);$i++){
            $Room_num = $i;
            $id_num = [];
            for($j = 0;$j<$Room[$i];$j++){
                $id_num []= $data[rand(0,count($data)-1)]['id'];
            }
            if($Room_num==2){
                $money = rand(500,1000);
            }elseif($i == 1){
                $money = rand(200,500);
            }else{
                $money = rand(50,200);
            }

            for($k = 0;$k<count($id_num);$k++){
                $arr = [
                    [ 'code'=>$Bets[rand(0,count($Bets)-1)],
                        'money'=>$money,
                        'explain'=>'other',
                        'odds'=>'1']
                ];

                $insert []= [
                    'user_id'=>$id_num[$k],
                    'content'=>json_encode($arr),
                    'money'=>$money,
                    'expect'=>$expect,
                    'type'=>$lottery,
                    'win'=>0,
                    'state'=>1,
                    'other'=>$Room_num,
                    'create_time'=>time(),
                    'explain'=>'Robot'
                ];
            }
        }
    }
    Db::table('betting')->insertAll($insert);
}


/**
 * [bettingFormat 投注内容格式化显示]
 * @param  $data[array] 查询出来的投注记录
 * @return $name_dis[Boolean] 如果传这个参数为 true 用户名全显示，否则只显示用户名前三位
 */
function bettingFormat($data,$name_dis = false){
    $data_chat = [];
    foreach ($data as &$value) {
        if($name_dis){
            $value['user_name'] = getUserName($value['user_id']);
        }else{
            $value['user_name'] = mb_substr(getUserName($value['user_id'],true),0,3) . '**';
        }
        // 这里是缓存彩种配置判断，不再查询数据库
        if(isset($data_chat['type_'.$value['type']])){
            $datas = $data_chat['type_'.$value['type']]['basic_config'];
        }else{
            // 获取配置 35 是足球
            if($value['type'] == 35){
                // 临时统一名字
                $data_chat['type_'.$value['type']]['name'] = '竞彩足球';
            }else{
                // print_r($value['type']);
                $lottery_config = Db::table('lottery_config')->field('basic_config,name,official')->where(['type'=>$value['type']])->find();
                $lottery_config['basic_config'] = json_decode($lottery_config['basic_config'],true);
                $lottery_config['official'] = json_decode($lottery_config['official'],true);
                // print_r($lottery_config);die;
                $data_chat['type_'.$value['type']] = $lottery_config;
                if($lottery_config['official'] && count($lottery_config['official']) > 0)
                {
                    $datas = array_merge($lottery_config['basic_config'],$lottery_config['official']);
                }
                else
                {
                    $datas = $lottery_config['basic_config'];
                }
            }
        }

        $value['lottery_name'] = $data_chat['type_'.$value['type']]['name'];
        $betting = json_decode($value['content'],true);

        $str = '';
        if($value['type'] <= 1){
            if($value['type'] ==0){
                $namek = [
                    'xian' => '闲',
                    'zhuang' => '庄',
                    'xd' => '闲对',
                    'wmdz' => '完美对子',
                    'rydz' => '任意对子',
                    'zd' => '庄对',
                    'xiao' => '小',
                    'he' => '和',
                    'da' => '大',
                ];
            }else{
                $namek = [
                    'he' => '和',
                    'hs' => '虎双',
                    'hhei' => '虎黑',
                    'hhong' => '虎红',
                    'hd' => '虎单',
                    'h' => '虎',
                    'l' => '龙',
                    'ls' => '龙双',
                    'ld' => '龙单',
                    'lhong' => '龙红',
                    'lhei' => '龙黑',
                ];
            }
            $value['content'] = '';
            foreach ($betting as $gk => $gv) {
                $value['content'] .= ($namek[$gk].';');
            }
        }else if($value['type'] == 35){                          //竞彩足球处理
            // $value['content'] = [];
            $jczq = [];
            foreach ($betting as $bk => $bv) {
                $zq_pl = []; // 单关串玩法
                $zq_tz = []; // 投注内容
                foreach ($betting[$bk]['play'] as $kp => $vp) {
                    if($vp == 1){
                        $zq_pl[] = '单关';
                    }else{
                        $zq_pl[] = $vp.'串1';
                    }
                }
                foreach ($betting[$bk]['data'] as $bk1 => $bv1) {
                    $zq_i = 0;
                    $zq_na = [
                        'bf' => '比分',
                        'zjq' => '总进球',
                        'bqc' => '半全场',
                    ];
                    $bit = [];
                    foreach ($bv1['data'] as $bk2 => $bv2) {
                        if($bk2 == 'spf'){ //胜平负 单独处理
                            $spf_arr = [
                                's' => '胜',
                                'p' => '平',
                                'f' => '负'
                            ];
                            foreach ($bv2 as $bk3 => $bv3) {
                                $bit[$zq_i] = [
                                    'name' => ($bv3['lost'] == 0 ? '胜平负' : '让球胜平负'),
                                    'type' => ($bv3['lost'] == 0 ? 'spf' : 'rqspf'),
                                    'lost' => $bv3['lost'],
                                    'data' => [],
                                ];

                                foreach ($bv3['data'] as $bk4  => $bv4) {
                                    $bit[$zq_i]['data'][] = [$spf_arr[$bk4],$bv4];
                                }
                                $zq_i++;

                            }
                        }else{
                            $bit[$zq_i] = [
                                'name' => $zq_na[$bk2],
                                'type' => $bk2,
                                'data' => [],
                            ];
                            foreach ($bv2 as $bk5 => $bv5) {
                                if($bk2 == 'bf'){
                                    if($bv5[0][0] == -1 && $bv5[0][1] == -1){
                                        $bit[$zq_i]['data'][] = ['平其他',$bv5[1]];
                                    }else if($bv5[0][0] == -1 && $bv5[0][1] == 0){
                                        $bit[$zq_i]['data'][] = ['负其他',$bv5[1]];
                                    }else if($bv5[0][0] == 0 && $bv5[0][1] == -1){
                                        $bit[$zq_i]['data'][] = ['胜其他',$bv5[1]];
                                    }else{
                                        $bit[$zq_i]['data'][] = [$bv5[0][0].'比'.$bv5[0][1],$bv5[1]];
                                    }
                                }else if($bk2 == 'zjq'){
                                    $bit[$zq_i]['data'][] = ['总进'.($bv5[0]>=7?'7球以上':$bv5[0].'球'),$bv5[1]];
                                }else if($bk2 == 'bqc'){
                                    $bit[$zq_i]['data'][] = [($bv5[0][0]==0? '平' : ($bv5[0][0]==1? '胜':'负')).($bv5[0][1]==0? '平' : ($bv5[0][1]==1? '胜':'负')),$bv5[1]];
                                }
                            }
                            $zq_i++;
                        }
                    }
                    $zq_rs = Db::table('football_list')->where('order_id','=',$bv1['id'])->find();
                    $zq_rs['content'] = json_decode($zq_rs['content'],true);
                    $zq_tz[] = [
                        'vs' => [$zq_rs['content']['hostName'],$zq_rs['content']['guestName']],
                        'jz' => date('Y-m-d H:i',$zq_rs['over_time']),
                        'data'=>$bit,        //每场球赛投注内容
                    ];
                }
            }
            $value['content'] = [
                'play' => $zq_pl,
                'game' => $zq_tz
            ];
            // print_r($betting);die;
        }else if(in_array($value['type'],[24,25,26,27,57,58])){ //pc28系列处理
            $bet_28 = [
                'a' => '大',
                'b' => '小',
                'c' => '单',
                'd' => '双',
                'ac' => '大单',
                'ad' => '大双',
                'bc' => '小单',
                'bd' => '小双',
                'max' => '极大',
                'min' => '极小',
                'green' => '绿',
                'blue' => '蓝',
                'red' => '红',
                'yellow' => '豹子',
            ];
            // ['sd','color','num']  备用
            foreach ($betting as $key1 => $value1) {
                $str .= (is_numeric($value1['code']) ? $value1['code'] :$bet_28[$value1['code']]).',';
            }
            $str =  preg_replace('/,$/','',$str);
            $value['content'] = $str;
        } else if (in_array($value['type'],[53, 54, 55, 56])) {

        }else{
            $lhsz = lotteryL::codeType(false,2018);//六合彩
            foreach ($betting as $key1 => $value1) {

                $str .= $datas[$key1]['name'] . ': ';
                // print_r($key1);die;
                // if(in_array($value['type'],[11,21]) && in_array($key1,['tswf','wx'])){
                // }else{
                // }
                foreach ($value1 as $key2 => $value2) {

                    $str .= $datas[$key1]['items'][$key2]['name'] . (function($data){
                            $str = '';
                            if(!empty($data)){
                                foreach ($data as $value) {
                                    $str .= '(' . (is_array($value) ? join($value,',') : $value) . ')';
                                }
                            }
                            return $str;
                        })($value2['code']) ;
                    if(in_array($value['type'],[11,21]) && in_array($key1,['tswf','wx'])){
                        // print_r($lhsz['animal'][0][0][0]);die;
                        if($key1 == 'tswf'){
                            if($key2 == 'jx'){      //家肖
                                $sx = $lhsz['animal'][0][0][0];
                            }else if($key2 == 'yx'){//野肖
                                $sx = $lhsz['animal'][0][1][0];
                            }else if($key2 == 'tx'){//天肖
                                $sx = $lhsz['tdsx'][0][0][0];
                            }else{//地肖
                                $sx = $lhsz['tdsx'][0][1][0];
                            }
                        }else{
                            $arr = [
                                'jin' => 0,
                                'mu' => 1,
                                'shui' => 2,
                                'huo' => 3,
                                'tu' => 4
                            ];
                            $sx = $lhsz['five'][0][$arr[$key2]][0];
                        }
                        // print_r($sx);die;
                        $str = $str . '（'. implode(',',$sx) .'）';
                    }
                    $str = $str.',';
                    // print_r($str);die;
                }
                $str = preg_replace('/,$/','',$str) . ';';
            }

            $value['content'] = preg_replace('/;$/','',$str);

        }
    }
    return $data;
}

/**
 * [getUserName 根据用户Id获得用户名]
 * @param  [int] $userid 用户Id
 * @param  [boolean] $pb=false 如果为true则进行屏蔽
 * @param  [string] $name='' 如果有名字则前两项不起作用直接进行屏蔽
 * @return [string]
 */
function getUserName($userid,$pb=false,$name=''){
    if($name != ''){
        $strl = mb_strlen($name);
        $name = mb_substr($name,0,floor($strl/2) ).'***';
        return $name;
    }
    $data = Db::table('user')->field('username')->where(['id'=>$userid])->find();
    if($data){
        if($pb){
            $strl = mb_strlen($data['username']);
            $data['username'] = mb_substr($data['username'],0,floor($strl/2) ).'***';
        }
        return $data['username'];
    }else{
        return false;
    }
}

/**
 *  根据银行简码返回银行名称，如果 $value 参数为空，则返回整个银行 简码及名字
 */
function bankTool($value){
    $data = [
        'payalipay' => '支付宝',
        'payweixin' => '微信',
        'payqqpakge' => 'QQ',
        'ICBC' => '工商银行',
        'CCB' => '建设银行',
        'BOC' => '中国银行',
        'ABC' => '农业银行',
        'PSBC' => '邮政银行',
        'CMB' => '招商银行',
        'SPDB' => '浦发银行',
        'CIB' => '兴业银行',
        'CMBC' => '民生银行',
        'CEB' => '光大银行',
        'PAB' => '平安银行',
        'HXB' => '华夏银行',
        'payservice' => '客服充值',
        'chess' => '棋牌游戏'
    ];
    if(empty($value)){
        return $data;
    }else{
        if(isset($data[$value])){
            return $data[$value];
        }else{
            return '没有配置这个银行';
        }
    }
}

//资金明细类型总汇
function moneyType(){
    $type_arr = [
        '0'=> '下注',
        '1'=> '提现',
        '2'=> '线下充值',
        '3'=> '中奖',
        '4'=> '扣款',
        '5'=> '赠送',
        '6'=> '退款',
        '7'=> '在线充值',
        '8'=> '返佣',
        '10' => '抽奖',
        '11' => '返水',
        '12' => '冻结资金',
        '13' => '冻结返还',
        '14' => '保底投注',
        '15' => '系统充值',
        '16' => '聊天室红包',
        '17' => '个人红包',
        '18' => '日负反水',
        '19' => '游戏上分',
        '20' => '游戏下分'
    ];
    return $type_arr;
}
/**
 * 这里是撤单操作
 */
function cheDan($id = ''){
    $return_data = [
        'code' => 0,
        'msg' => '出错,撤单失败'
    ];
    if(empty($id)){
        $return_data['msg'] = '没有找到这个单子';
        return;
    }
    $model = (new Betting)->field('id,user_id,money,state')->find($id);
    $main = $model->toArray();
    if($main['state'] == 1){
        $return_data['msg'] = '这个单子已经结算,不能进行撤单操作';
        return $return_data;
    }
    if($main['state'] == 2){
        $return_data['msg'] = '这个单子已经进行过撤单操作,不能再次撤单';
        return $return_data;
    }
    if($main['state'] == 3){
        $return_data['msg'] = '这个单子追号中..,不能撤单';
        return $return_data;
    }
    $che_dan_data = [];
    if($main['money'] == 0){
        $gen = $model->gen->toArray();
        $he = $model->he->toArray();
        if($he['bd'] > 0){
            $che_dan_data[] = [
                'uid' => $main['user_id'],
                'money' => $he['bd'],
                'type' => 13,
                'explain' => '保底退款'
            ];
        }
        foreach ($gen as $value) {
            $che_dan_data[] = [
                'uid' => $value['user_id'],
                'money' => $value['money'],
                'type' => 6,
                'explain' => '撤单退款'
            ];
        }
    }else{
        $che_dan_data[] = [
            'uid' => $main['user_id'],
            'money' => $main['money'],
            'type' => 6,
            'explain' => '撤单退款'
        ];
    }
    $is_action = moneyAction($che_dan_data);
    $update_data = [
        'id' => $main['id'],
        'explain' => $is_action['msg'],
        'state' => 0
    ];
    $return_data['msg'] = $is_action['msg'];
    if($is_action['code']){
        $return_data['code'] = 1;
        $return_data['msg'] = '撤单成功';
        $update_data['explain'] = '合买进度没有达到,自动撤单';
        $update_data['state'] = 2;
        (new betting)->save($update_data,true);
    }
    return $return_data;
}
function longPolling($callback){
    session_write_close();
    // ignore_user_abort(false);
    set_time_limit(0);

    for($i=0;$i<25;$i++){
        echo str_repeat(' ',4000);
        ob_flush();
        flush();
        if ($callback()) {
            return;
        }
        sleep(3);
    }
    ob_end_flush();
    echo "[]";
}
//获取彩票的type类型对应的数字和开奖时间周期
function getCpType($name)
{
    $arrType = [
        'xysc'=>5,
        'efc'=>7,
        'ffc'=>6,
        'ajc'=>8,
        'wfc'=>9,
        'jslhc'=>11,
        'yfsc'=>36,
        'xypk10'=>37,
        'klpk10'=>38,
        'xjpsm'=>39,
        'jisuks'=>40,
        'xyks'=>41,
        'tjks'=>42,
        'jisusyxw'=>44,
        'xysyxw'=>45,
        'jisuft'=>51,
        'brnn'=>52,
        'dm28'=>57,//泰国28
        'trq28'=>58,//文莱28
    ];
    if(isset($arrType[$name]))
    {
        return $arrType[$name];
    }
    else
    {
        return false;
    }
}
//根据不同类型生成不同的号码
function getTypeCode($type)
{
    if(in_array($type,[3,4,5,36,37,38,39,51]))
    {
        do{
            $arr = ['01','02','03','04','05','06','07','08','09','10'];
            shuffle($arr);
            $content = implode(',',$arr);
            return $content;
        }while(Db::table('lottery_code')->where(['content' => $content,'type'=>$type])->where('create_time','>',time() - 24*3600*365)->find());
    }
    else if(in_array($type,[6,7,8,9,12,13,28,2]))
    {
        do{
            $arr = [];
            for($i=0;$i<5;$i++)
            {
                $arr[$i] = mt_rand(0,9);
            }
            $content = implode(',',$arr);
            return $content;
        }while(Db::table('lottery_code')->where(['content' => $content,'type'=>$type])->where('create_time','>',time() - 24*3600)->find());
    }
    else if(in_array($type,[11,21,73,74,75,76]))
    {
        do{
            $arr = ['01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49'];
            $arrKey = array_rand($arr,7);
            shuffle($arrKey);
            $arrCode = [];
            foreach ($arrKey as $ak)
            {
                $arrCode[] = $arr[$ak];
            }
            $content = implode(',',$arrCode);
            return $content;
        }while(Db::table('lottery_code')->where(['content' => $content,'type'=>$type])->where('create_time','>',time() - 24*3600*365)->find());
    }
    else if(in_array($type,[10,14,15,30,31,32,33,34,40,41,42,43,59]))
    {
        $arr = [];
        for($i=0;$i<3;$i++)
        {
            $arr[$i] = mt_rand(1,6);
        }
        $content = implode(',',$arr);
        return $content;
    }
    else if(in_array($type,[44,45,16,17,18,48,49,77]))
    {
        do{
            $arr = ['01','02','03','04','05','06','07','08','09','10','11'];
            $arrKey = array_rand($arr,5);
            shuffle($arrKey);
            $arrCode = [];
            foreach ($arrKey as $ak)
            {
                $arrCode[] = $arr[$ak];
            }
            $content = implode(',',$arrCode);
            return $content;
        }while(Db::table('lottery_code')->where(['content' => $content,'type'=>$type])->where('create_time','>',time() - 24*3600*10)->find());
    }
    else if($type == 52)
    {
        do{
            $arr = ['0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50','51','52'];
            $arrKey = array_rand($arr,10);
            shuffle($arrKey);
            $arrCode = [];
            foreach ($arrKey as $ak)
            {
                $arrCode[] = $arr[$ak];
            }
            $content = implode(',',$arrCode);
            return $content;
        }while(Db::table('lottery_code')->where(['content' => $content,'type'=>$type])->where('create_time','>',time() - 24*3600*365)->find());
    }
    else if(in_array($type,[24,25,26,27,57,58]))
    {
        do{
            $arr = [];
            for($i=0;$i<3;$i++)
            {
                $arr[$i] = mt_rand(0,9);
            }
            $content = implode(',',$arr);
            return $content;
        }while(Db::table('lottery_code')->where(['content' => $content,'type'=>$type])->where('create_time','>',time() - 10*3600)->find());
    }
    else if(in_array($type,[64,65,66,67,68,69]))
    {
        do{
            $arr = [];
            for($i=0;$i<3;$i++)
            {
                $arr[$i] = mt_rand(0,9);
            }
            $content = implode(',',$arr);
            return $content;
        }while(Db::table('lottery_code')->where(['content' => $content,'type'=>$type])->where('create_time','>',time() - 3*3600)->find());
    }
    else if(in_array($type,[70,71,72]))
    {

    }
    else if(in_array($type,[60,61,62,63]))
    {
        do{
            $arr = ['01','02','03','04','05','06','07','08','09','10','11','12','13','14','15','16','17','18','19','20','21'];
            $arrKey = array_rand($arr,5);
            shuffle($arrKey);
            $arrCode = [];
            foreach ($arrKey as $ak)
            {
                $arrCode[] = $arr[$ak];
            }
            $content = implode(',',$arrCode);
            return $content;
        }while(Db::table('lottery_code')->where(['content' => $content,'type'=>$type])->where('create_time','>',time() - 24*3600*365)->find());
    }
}