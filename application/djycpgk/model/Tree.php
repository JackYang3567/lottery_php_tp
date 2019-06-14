<?php
namespace app\djycpgk\model;

use think\Model;

class Tree extends Model {

    public function getUsernameAttr($value,$data)//获取器
    {
        if (!$user = User::field('username')->where('id', $data['user_id'])->find()) {
            return '未知用户';
        }
        return $user->username;
    }
    public function getShangjiAttr($value,$data)//获取器
    {
        if ($data['parent_id'] == 0){
            if (!$user_parent =  User::field('proxy_id')->where('id',$data['user_id'])->find()){
                    return '无代理';
            }
            return  Proxy::field('username')->where('uid',$user_parent['proxy_id'])->find()['username'].'(代理)';
        }elseif  (!$parent = self::where('id', $data['parent_id'])->find()) {
            return '用户异常';
        }
        return $parent->username;
    }
    public function getShangjiIdAttr($value,$data)//获取器
    {
        if  (!$parent_id = self::where('id', $data['parent_id'])->find()) {
            return  0;
        }
        return $parent_id->parent_id;
    }
    public static function  Dama($parent_id=0,$pagina=20,$pageParam=[],$start_time='1970-01-01 08:00:00',$end_time='',$keywords=''){
            $max  =[];
            if ($end_time == ''){
                $end_time=time();
            }else{
                $end_time = strtotime($end_time)+86400;
            }
            if ($keywords){
               $name_id =  User::field('id')->where('username',$keywords)->find()['id'];
               $max['user_id'] = ['user_id','=',$name_id];
            }else{
                $max['parent_id'] = ['parent_id','=',$parent_id];
            }
            $map['create_time'] = ['create_time','between',[strtotime($start_time),$end_time]];
            $Dama = self::where($max)->order('lft')->paginate($pagina,false, $pageParam);
            foreach ($Dama as &$vo){
                $vo['xiaji'] = ($vo['rgt']-$vo['lft']-1)/2;
                $id = [];
                $user_id = self::field('user_id')->where('lft','>',$vo['lft'])->where('rgt','<',$vo['rgt'])->select();
                foreach ($user_id as $value){
                    $id[] = $value['user_id'];
                }
                $dama = CapitalDetail::field('sum(money) as v')->where($map)->where('user_id','in',$id)->where('type',0)->find()['v'];
                $vo['dama'] = $dama*-1;
                $fanyong = CapitalDetail::field('sum(money) as v')->where($map)->where('user_id',$vo['user_id'])->where('type',8)->find()['v'];
                $vo['fanyong'] = $fanyong?$fanyong:0;
            }
            return $Dama;
    }
}