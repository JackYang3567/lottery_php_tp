<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/30
 * Time: 10:02
 */
namespace app\djycpgk\controller;

use think\facade\Request;
use think\Db;

class XinshoujiaochengUpload extends Rbac
{
    public function index()
    {
        $info = Db::table('system_config')->where('name','xinshoujiaocheng')->value('value');
        $info = json_decode($info,true);
        $this->assign('info',$info);
        return view();
    }

    public function addPage()
    {
        return view();
    }

    public function upload()
    {
        $type = Request::post('type_info');
        $file = $_FILES['upfile'];

        $saveInfo = [];

        for($i=0;$i<count($file['name']);$i++)
        {
            $extArr = explode('.',$file['name'][$i]);
            $ext = $extArr[count($extArr) -1];

            $public_path = config('public_path');
            $upload = $public_path.'upload';
            if(!file_exists($upload))
            {
                mkdir($upload);
            }
            $gonggao = $upload.DIRECTORY_SEPARATOR.$type;
            if(!file_exists($gonggao))
            {
                mkdir($gonggao);
            }

            $filename = $i.'.'.$ext;
            $newFile = $gonggao.DIRECTORY_SEPARATOR.$filename;
            $savePath = explode('public',$newFile);
            $savePath = $savePath[1];

            $save = move_uploaded_file($file['tmp_name'][$i],$newFile);
            if($save)
            {
                $saveInfo[$type][] = $savePath;
            }
        }

        $info = Db::table('system_config')->where('name','xinshoujiaocheng')->value('value');
        $info = json_decode($info,true);
        $info[$type] = $saveInfo[$type];

        $upload = Db::table('system_config')->where('name','xinshoujiaocheng')->update(['value'=>json_encode($info)]);
        if($upload)
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }
}