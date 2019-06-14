<?php
namespace app\djycpgk\controller;
use think\Controller;
use think\Request;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/3
 * Time: 16:05
 */
class Extension extends Controller
{
    public function forExtension()
    {
        $ex = config('extension');
        $this->assign('ex',$ex);
        return view();
    }

    public function getCodeForExtension(Request $r)
    {
        $info = $r->get('info');
        $info = base64_decode($info);
        $info = json_decode($info,true);
        switch ($info)
        {
            case 'zc':
                $title = '众筹';
                break;
            case 'xy':
                $title = '幸运';
                break;
            case 'cdw':
                $title = '大彩网';
                break;
        }
        $this->assign('title',$title);
        $this->assign('jumpUrl',config('extension_url').'?type='.$info);
        return view();
    }
}