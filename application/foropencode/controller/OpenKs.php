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

class OpenKs extends Controller
{
    public function generatCode($type,$expect)
    {
        $config = Db::table('lottery_config')->where('type', $type)->value('basic_config');
        $config = json_decode($config, true);
//        dump($config);
        $pool = Db::table('lottery_pool')->where(['expect' => $expect, 'type' => $type])->select();
//dump($pool);
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
        return $content = $this->getCode($moneyInfo);
    }

    public function getCode($info)
    {
        if($info)
        {
            $n = 0;
            do{
                $arr = [];
                for($i=0;$i<3;$i++)
                {
                    $arr[$i] = mt_rand(1,6);
                }
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
                $n++;

                if(array_sum($is_rule) == 0)
                {
                    $content = implode(',',$arr);
                    return $content;
                }
                if($n == 300 && !isset($content))
                {
                    for($i=0;$i<3;$i++)
                    {
                        $arr[$i] = mt_rand(1,6);
                    }
                    $content = implode(',',$arr);
                    return $content;
                }
            }while($n <= 300 && !isset($content));
        }
        else
        {
            do{
                $arr = [];
                for($i=0;$i<3;$i++)
                {
                    $arr[$i] = mt_rand(1,6);
                }
                $content = implode(',',$arr);
                return $content;
            }while(Db::table('lottery_code')->where(['content' => $content,'type'=>$type])->where('create_time','>',time() - 3*3600)->find());
        }
    }

    public function getRule($code)
    {
        $rule = [];
        $rule['zh']['code_'.array_sum($code)] = 1;

        sort($code);
        if($code[0] == $code[1] && $code[1] == $code[2])
        {
            $bz = 1;
        }

        if(($code[0] + 1) == $code[1] && ($code[1] + 1) == $code[2])
        {
            $sz = 1;
        }
        if($code[0] == $code[1] || $code[1] == $code[2] || $code[2] == $code[0])
        {
            $dz = 1;
        }
        if(($code[0] + 1) == $code[1] || ($code[1] + 1) == $code[2])
        {
            $bs = 1;
        }
        if(isset($bz))
        {
            $rule['xt']['bz'] = 1;
        }
        if(isset($sz))
        {
            $rule['xt']['sz'] = 1;
        }
        if(!isset($sz) && isset($bs) && !isset($dz))
        {
            $rule['xt']['bs'] = 1;
        }
        if(!isset($bz) && isset($dz))
        {
            $rule['xt']['dz'] = 1;
        }
        if(!isset($bz) && !isset($sz) && !isset($bs) && !isset($dz))
        {
            $rule['xt']['zl'] = 1;
        }
        if(array_sum($code)%2)
        {
            $rule['zh']['dan'] = 1;
        }
        else
        {
            $rule['zh']['s'] = 1;
        }
        if(array_sum($code) > 10)
        {
            $rule['zh']['da'] = 1;
        }
        else
        {
            $rule['zh']['x'] = 1;
        }
        return $rule;
    }
}