<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/5
 * Time: 16:28
 */
$lottery_config = Db::table('lottery_config')->where(['switch'=>1])->column('type,time_config,name') ?? [];

$all_type = [];
$atype = [];

foreach ($lottery_show as $key=>$l)
{
    if($l['name'] == '其他')
    {
        continue;
    }
    else
    {
        $num = 0;
        foreach ($l['data'] as $ld)
        {
            if(isset($lottery_config[$ld]))
            {
                $num += 1;
            }
        }
        if($num > 0)
        {
            $all_type[$key]['name'] = $l['name'];
            $all_type[$key]['info'] = $l['data'];
            $atype = array_merge($atype,$l['data']);
        }
    }
}