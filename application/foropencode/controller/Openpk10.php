<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/10
 * Time: 10:31
 */
namespace app\foropencode\controller;

use think\Controller;
use think\Db;

class Openpk10 extends Controller
{
    public function generatCode($type,$expect)
    {
        $config = Db::table('lottery_config')->where('type', $type)->value('basic_config');
        $config = json_decode($config, true);
//        dump($config);die;
        $pool = Db::table('lottery_pool')->where(['expect' => $expect, 'type' => $type])->select();
        //获取总下注金额
        $allMoney = Db::table('lottery_pool')->where(['expect' => $expect, 'type' => $type])->value('sum(money) as allmoney');
        //获取资金池中各个玩法中奖后的资金
        $moneyInfo = [];
        for ($i=0;$i<count($pool);$i++)
        {
            if(isset($moneyInfo[$pool[$i]['bet_type']][$pool[$i]['bet_content']]))
            {
                $moneyInfo[$pool[$i]['bet_type']][$pool[$i]['bet_content']] += $config[$pool[$i]['bet_type']]['items'][$pool[$i]['bet_content']]['odds'] * $pool[$i]['money'];
            }
            else
            {
                $moneyInfo[$pool[$i]['bet_type']][$pool[$i]['bet_content']] = $config[$pool[$i]['bet_type']]['items'][$pool[$i]['bet_content']]['odds'] * $pool[$i]['money'];
            }
        }
        //暂定奖金比例为总下注金额的20%
        foreach ($moneyInfo as $key=>$value)
        {
            foreach ($value as $k=>$v)
            {
                if($v >= $allMoney * 0.3)
                {
                    continue;
                }
                else
                {
                    unset($moneyInfo[$key][$k]);
                }
            }
        }
//        dump($moneyInfo);die;
        return $content = $this->getCode($moneyInfo);
    }

    public function getCode($info)
    {
        if($info)
        {
            do{
                $arr = ['01','02','03','04','05','06','07','08','09','10'];
                shuffle($arr);

                $rule = $this->getRule($arr);

                $is_rule = [];
                foreach ($rule as $key=>$value)
                {
                    foreach ($value as $k=>$v)
                    {
                        if(isset($info[$key][$k]))
                        {
                            $is_rule[] = 1;
                        }
                        else
                        {
                            $is_rule[] = 0;
                        }
                    }
                }
                if(array_sum($is_rule) == 0)
                {
                    $content = implode(',',$arr);
                    return $content;
                }
            }while(!isset($content));
        }
        else
        {
            do{
                $arr = ['01','02','03','04','05','06','07','08','09','10'];
                shuffle($arr);
                $content = implode(',',$arr);
                return $content;
            }while(Db::table('lottery_code')->where(['content' => $content,'type'=>$type])->where('create_time','>',time() - 3*3600)->find());
        }
    }

    public function getRule($code)
    {
        $rule = [];
        $rule['gyzh']['code_'.($code[0] + $code[1])] = 1;
        if(($code[0] + $code[1]) < 11)
        {
            $rule['gyzh']['x'] = 1;
        }
        else
        {
            $rule['gyzh']['da'] = 1;
        }
        if(($code[0] + $code[1])%2)
        {
            $rule['gyzh']['dan'] = 1;
        }
        else
        {
            $rule['gyzh']['s'] = 1;
        }
        //冠军
        if($code[0] >= 6)
        {
            $rule['gj']['da'] = 1;
        }
        else
        {
            $rule['gj']['x'] = 1;
        }
        if($code[0]%2)
        {
            $rule['gj']['dan'] = 1;
        }
        else
        {
            $rule['gj']['s'] = 1;
        }
        $rule['gj']['code_'.($code[0]+0)] = 1;

        if($code[0] > $code[9])
        {
            $rule['gj']['l'] = 1;
        }
        else
        {
            $rule['gj']['h'] = 1;
        }

        //亚军
        if($code[1] >= 6)
        {
            $rule['yj']['da'] = 1;
        }
        else
        {
            $rule['yj']['x'] = 1;
        }
        if($code[1]%2)
        {
            $rule['yj']['dan'] = 1;
        }
        else
        {
            $rule['yj']['s'] = 1;
        }
        $rule['yj']['code_'.($code[1]+0)] = 1;

        if($code[1] > $code[8])
        {
            $rule['yj']['l'] = 1;
        }
        else
        {
            $rule['yj']['h'] = 1;
        }

        //第三名
        if($code[2] >= 6)
        {
            $rule['d3m']['da'] = 1;
        }
        else
        {
            $rule['d3m']['x'] = 1;
        }
        if($code[2]%2)
        {
            $rule['d3m']['dan'] = 1;
        }
        else
        {
            $rule['d3m']['s'] = 1;
        }
        $rule['d3m']['code_'.($code[2]+0)] = 1;

        if($code[2] > $code[7])
        {
            $rule['d3m']['l'] = 1;
        }
        else
        {
            $rule['d3m']['h'] = 1;
        }

        //第四名
        if($code[3] >= 6)
        {
            $rule['d4m']['da'] = 1;
        }
        else
        {
            $rule['d4m']['x'] = 1;
        }
        if($code[3]%2)
        {
            $rule['d4m']['dan'] = 1;
        }
        else
        {
            $rule['d4m']['s'] = 1;
        }
        $rule['d4m']['code_'.($code[3]+0)] = 1;

        if($code[3] > $code[6])
        {
            $rule['d4m']['l'] = 1;
        }
        else
        {
            $rule['d4m']['h'] = 1;
        }

        //第五名
        if($code[4] >= 6)
        {
            $rule['d5m']['da'] = 1;
        }
        else
        {
            $rule['d5m']['x'] = 1;
        }
        if($code[4]%2)
        {
            $rule['d5m']['dan'] = 1;
        }
        else
        {
            $rule['d5m']['s'] = 1;
        }
        $rule['d5m']['code_'.($code[4]+0)] = 1;

        if($code[4] > $code[5])
        {
            $rule['d5m']['l'] = 1;
        }
        else
        {
            $rule['d5m']['h'] = 1;
        }

        //第六名
        if($code[5] >= 6)
        {
            $rule['d6m']['da'] = 1;
        }
        else
        {
            $rule['d6m']['x'] = 1;
        }
        if($code[5]%2)
        {
            $rule['d6m']['dan'] = 1;
        }
        else
        {
            $rule['d6m']['s'] = 1;
        }
        $rule['d6m']['code_'.($code[5]+0)] = 1;

        //第七名
        if($code[6] >= 6)
        {
            $rule['d7m']['da'] = 1;
        }
        else
        {
            $rule['d7m']['x'] = 1;
        }
        if($code[6]%2)
        {
            $rule['d7m']['dan'] = 1;
        }
        else
        {
            $rule['d7m']['s'] = 1;
        }
        $rule['d7m']['code_'.($code[6]+0)] = 1;

        //第八名
        if($code[7] >= 6)
        {
            $rule['d8m']['da'] = 1;
        }
        else
        {
            $rule['d8m']['x'] = 1;
        }
        if($code[7]%2)
        {
            $rule['d8m']['dan'] = 1;
        }
        else
        {
            $rule['d8m']['s'] = 1;
        }
        $rule['d8m']['code_'.($code[7]+0)] = 1;

        //第九名
        if($code[8] >= 6)
        {
            $rule['d9m']['da'] = 1;
        }
        else
        {
            $rule['d9m']['x'] = 1;
        }
        if($code[8]%2)
        {
            $rule['d9m']['dan'] = 1;
        }
        else
        {
            $rule['d9m']['s'] = 1;
        }
        $rule['d9m']['code_'.($code[8]+0)] = 1;

        //第十名
        if($code[9] >= 6)
        {
            $rule['d10m']['da'] = 1;
        }
        else
        {
            $rule['d10m']['x'] = 1;
        }
        if($code[9]%2)
        {
            $rule['d10m']['dan'] = 1;
        }
        else
        {
            $rule['d10m']['s'] = 1;
        }
        $rule['d10m']['code_'.($code[9]+0)] = 1;
        return $rule;
    }
}