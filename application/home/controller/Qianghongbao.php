<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/28
 * Time: 10:12
 */
namespace app\home\controller;

use think\Controller;
use think\Db;
use think\facade\Request;

class Qianghongbao extends Common
{
    public function bangdan()
    {
        $info = Db::table('user_hongbao_log')
            ->alias('uhl')
            ->join('user u','u.id=uhl.user_id')
            ->field('uhl.*,u.username')
            ->order('uhl.money desc')
            ->limit(50)
            ->select();
        foreach ($info as &$i)
        {
            $len = strlen($i['username']);
            if($len > 6)
            {
                $i['username'] = substr($i['username'],0,3).'***';
            }
            else
            {
                $i['username'] = substr($i['username'],0,2).'***';
            }
        }
        return $info;
    }
    public function getInfo()
    {
        //获取首页红包配置信息
        $info = Db::table('system_config')->where('name','hongbao_flowing_water')->value('value');
        $info = json_decode($info,true);
        return $info;
    }
    public function getRule(){
        $info = Db::table('system_config')->where('name','rule_hongbao')->whereOr('name','title_hongbao')->column('name,value');
        return $info;
    }

    public function qiangAction()
    {
        if(Request::isPost())
        {
            $post = Request::post();
            if(isset($post['type']))
            {
                //获取配置信息
                $data = $this->checkLogin();
                if(!$data['code'])
                {
                    return ['status' => false,'msg' => $data['msg'],'type' => "login"];
                }
                $info = Db::table('system_config')->where('name','hongbao_flowing_water')->value('value');
                $info = json_decode($info,true);
                $user_id = $data['data']['id'];

                $zuo_start = strtotime(date('Y-m-d',strtotime('-1day')).' 00:00:00');
                $zuo_end = strtotime(date('Y-m-d',strtotime('-1day')).' 23:59:59');
                //获取用户总共下注流水
                $allTouzhuMoney = Db::table('capital_detail')->where('type',0)->where('user_id',$user_id)->where('create_time','>=',$zuo_start)->where('create_time','<=',$zuo_end)->sum('money');

                $chongzhi = Db::table('capital_detail')->where('type','in',[2,7])->where('user_id',$user_id)->where('create_time','>=',$zuo_start)->where('create_time','<=',$zuo_end)->sum('money');

                if($chongzhi < $info['recharge'])
                {
                    return ['status' => false,'msg' => '非常抱歉，您暂时不满足参与活动条件！','type' => "error"];
                }
                //获取用户可以抢红包次数
                $config = $info['Robot_config'];
                $canNum = 0;
                $start_money = 0;
                $end_money = 0;
                foreach ($config as $c)
                {
                    if($c['money_start'] >= $allTouzhuMoney && $c['money_end'] <= $allTouzhuMoney)
                    {
                        $canNum = $c['frequency'];
                        $start_money = $c['winning_the_prize_start'];
                        $end_money = $c['winning_the_prize_end'];
                    }
                }

                //获取用户当天参与抢红包次数
                $hasQiang = Db::table('user_hongbao_log')->where('user_id',$user_id)->where('today',strtotime(date('Y-m-d').' 00:00:00'))->count();
                $num = $canNum - $hasQiang;
                if($num <= 0)
                {
                    return ['status' => false,'msg' => '您今天的抢红包次数已经用完了！','type' => "error"];
                }
                //为用户生成红包金额
                $hbMoney = $this->getHBMoney($start_money,$end_money);
                Db::startTrans();
                try{
                    $hasQiang = Db::table('user_hongbao_log')->where('user_id',$user_id)->where('today',strtotime(date('Y-m-d').' 00:00:00'))->count();
                    //获取用户资金
                    $user = Db::table('user')->where('id',$user_id)->find();
                    $num = $canNum - $hasQiang;
                    if($num <= 0)
                    {
                        Db::rollback();
                        return ['status' => false,'msg' => '您今天的抢红包次数已经用完了！','type' => "error"];
                    }

                    $insert = [
                        'user_id' => $user_id,
                        'today' => strtotime(date('Y-m-d').' 00:00:00'),
                        'money' => $hbMoney,
                        'qiang_time'=>time()
                    ];
                    $add = Db::table('user_hongbao_log')->insert($insert);
                    $addMoney = Db::table('user')->where('id',$user_id)->update(['money'=>$user['money'] + $hbMoney,'off_money'=>$user['off_money'] + $hbMoney]);

                    $add1 = Db::table('capital_detail')->insert([
                        'user_id' => $user_id,
                        'money' => $hbMoney,
                        'type' => 22,
                        'explain' => '抽奖红包',
                        'user_money' => $user['money'],
                        'create_time' => time()
                    ]);
                    if($add && $addMoney && $add1)
                    {
                        Db::commit();
                        return ['status' => true,'msg' => '恭喜您，抽中红包<span style="color: white">'.$hbMoney.'</span>元！'];
                    }
                }catch (\Exception $e)
                {
//                    dump($e);
                    Db::rollback();
                    return ['status' => false,'msg' => '系统繁忙，请稍后重试！'];
                }

            }
            else
            {
                return ['status' => false,'msg' => '参数错误，请重试！','type' => "error"];
            }
        }
        else
        {
            return ['status' => false,'msg' => '参数错误，请重试！','type' => "error"];
        }
    }

    private function getHBMoney($s,$e)
    {
       return $suiji = mt_rand($s,$e);

    }

    public function myHongBaoInfo()
    {
        //获取配置信息
        $data = $this->checkLogin();
        if(!$data['code'])
        {
            return ['status' => false,'msg' => $data['msg'],'type' => "login"];
        }
        $user_id = $data['data']['id'];
        $allHongBao = Db::table('user_hongbao_log')->where('user_id',$user_id)->sum('money');
        $info = Db::table('system_config')->where('name','hongbao_flowing_water')->value('value');
        $info = json_decode($info,true);

        $allTouzhuMoney = Db::table('capital_detail')->where('type',0)->where('user_id',$user_id)->sum('money');
        $config = $info['Robot_config'];
        $canNum = 0;
        foreach ($config as $c)
        {
            if($c['money_start'] >= $allTouzhuMoney && $c['money_end'] <= $allTouzhuMoney)
            {
                $canNum = $c['frequency'];
            }
        }

        //获取用户当天参与抢红包次数
        $hasQiang = Db::table('user_hongbao_log')->where('user_id',$user_id)->where('today',strtotime(date('Y-m-d').' 00:00:00'))->count();
        $num = $canNum - $hasQiang;

        $myLog = Db::table('user_hongbao_log')->where('user_id',$user_id)->order('qiang_time desc')->select();

        return [
            'status' => true,
            'allHongBao' => $allHongBao,
            'num' => $num,
            'log' => $myLog
        ];
    }

    public function xinshou()
    {
        $info = Db::table('system_config')->where('name','xinshoujiaocheng')->value('value');
        $info = json_decode($info,true);
        return $info;
    }
}