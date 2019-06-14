<?php
namespace app\djycpgk\model;

use think\Model;
use think\Db;

class Proxy extends Model {


    public function getXiaJiAttr($value,$data)//获取下级代理数量
    {
        return self::field('COUNT(*) as xiaji2')->where('proxy_id', $data['uid'])->find()['xiaji2'];
    }

    public function getYongHunAttr($value,$data)//获取下级用户数量
    {
         $ss =   User::field('COUNT(*) as yonghu2')->where('type',0)->where('proxy_id', $data['uid'])->find()['yonghu2'];

        return $ss;
    }
    public function getSuperiorAttr($value,$data)//获取上级用户名称
    {
        $ss =   self::field('username,proxy_id')->where('uid', $data['proxy_id'])->find();
        return $ss;
    }
    public function getUserIdAttr($value,$data)//获取代理下面所有的用户id
    {
        $id = [];
        $user_id = User::where('proxy_id',$data['uid'])->field('id')->select();
        foreach ($user_id as $k =>&$valux){
            $id[] = $valux['id'];
        }

        return $id;
    }

    /**
     * 添加代理
     * @param $superior_id 上级id
     * @param $uid  添加的id
     */
    public static function  proxyAdd($superior_id,$uid){
        if (!$superior_id && !$uid){
            return '数据异常';
        }
        $superior_rgt = self::field('rgt')->where('uid',$superior_id)->find()['rgt'];
        //代理表改变左右值
        self::where('lft','>',$superior_rgt)->setInc('lft',2);
        self::where('rgt','>=',$superior_rgt)->setInc('rgt',2);
        //用户表改变左右值
        User::where('lft','>',$superior_rgt)->setInc('lft',2);
        User::where('rgt','>=',$superior_rgt)->setInc('rgt',2);
        //更新添加的用户的左右值
        $ss = self::where('uid',$uid)->update(['lft'=>$superior_rgt,'rgt'=>$superior_rgt+1]);
        return $ss;
    }
    //添加顶级代理
    public static function  proxyTopDelete($uid){
        if (!$uid){
            return '数据异常';
        }
        $max_rgt = self::field('MAX(rgt) as rgt')->where('proxy_id',0)->find()['rgt'];
        $ss =  self::where('uid',$uid)->update(['lft'=>$max_rgt+1,'rgt'=>$max_rgt+2]);
        return $ss;
    }

    /**
     * 代理线删除
     * @param $uid 代理id
     * @return string
     */
    public  static function proxyDelete($uid){
        if (!$uid){
            return '数据异常';
        }

        $ss = self::field('lft,rgt')->where('uid',$uid)->find();
//        dump($ss);die();
        self::where([['lft','>=',$ss['lft']],['rgt','<=',$ss['rgt']]])->update(['node_status'=>1]);
        User::where([['lft','>',$ss['lft']],['rgt','<',$ss['rgt']]])->update(['node_status'=>1]);
        $shuliang = ($ss['rgt']-$ss['lft']+1);
//        代理表 大于被删除的左值的全部 减去 $shuliang
        self::where('lft','>',$ss['rgt'])->setDec('lft',$shuliang);
        self::where('rgt','>',$ss['rgt'])->setDec('rgt',$shuliang);

        //        用户表 大于被删除的左值的全部 减去 $shuliang
        User::where('lft','>',$ss['rgt'])->setDec('lft',$shuliang);
        User::where('rgt','>',$ss['rgt'])->setDec('rgt',$shuliang);

        //改变代理下面的用户的左右值和上级
          User::where('lft','>',$ss['lft'])->where('rgt','<',$ss['rgt'])->where('node_status',1)->update(['lft'=>0,'rgt'=>0,'proxy_id'=>0,'node_status'=>0]);
        //删除代理
        $sc = self::where([['lft','>=',$ss['lft']],['rgt','<=',$ss['rgt']],['node_status','=',1]])->delete();

        return $sc;

    }

}