<?php
namespace app\gk\controller;
use app\djycpgk\model\User;
use app\djycpgk\model\ApiBetting;
use app\djycpgk\model\ApiConfig;
use app\djycpgk\model\ApiGame;
use app\djycpgk\model\Proxy;
use app\home\model\SystemConfig;
use think\Controller;
use think\Db;
use think\facade\Request;
use think\facade\Session;

class Chessgame extends Controller {
    public function apiconfig(){
        $config = ApiConfig::all();
        return json($config);
    }
    public function couponList($type=1){
        $game = ApiGame::where('api_id',$type)->select();
        return json($game);
    }
    public function reportForm($search=0,$start_time='',$end_time='',$name='',$agent='',$type=-1,$type_yx=-1,$number='') //输赢报表
    {
        $proxy_id = Session::get('proxy.uid');//获取代理的id
        $paginate = 15;
        $pageParam = [];
        $where = [];
        $map = [];

        if (Request::method() == 'POST'){
            if (Session::get('proxy.type') == 2) {//判断为 二级代理
                $map[] = ['user_id', 'in', function($query) use($proxy_id) {
                    $query->table('relationship')->field('userid')->where(['prev' => $proxy_id, 'floor' => 3])->select();
                }];
            } else {	//登录的是顶级代理
                $map[] = ['user_id', 'in', function($query) use($proxy_id) {
                    $query->table('relationship')->field('userid')->where(['top' => $proxy_id, 'floor' => 3])->select();
                }];
            }
            if ($search ==1){
                if ($start_time){
                    $where[] = ['game_start_time','>',$start_time];
                }
                if ($end_time){
                    $where[] = ['game_end_time','<',$end_time.'23:59:59'];
                }
                if ($name){
                    $where[] = ['username','=',$name];
                }
                if ($type !=-1)
                {
                    $where[] = ['api_id','=',$type];
                    if ($type_yx !=-1){
                        $where[] = ['kind_id','=',$type_yx];
                    }
                }
                if ($number){
                    $where[] = ['game_id','=',$number];
                }
                if ($agent){
                    $proxy = Proxy::where('username',$agent)->where('type',2)->field('uid')->find()['uid'];
                    $where[] = ['user_id','in',function($query) use($proxy) {
                        $query->table('user')->where('proxy_id',$proxy)->field('id')->select();
                    }];
                }
            }
            $zhancheng = SystemConfig::where('name','zhancheng')->find()['value'];
            $data = ApiBetting::order('id', 'desc')->where($where)->where($map)->paginate($paginate,false,$pageParam);
            $data->append(['Operator','GameType']);
            $tj = ['cell_score'=>0,'all_bet'=>0,'profit'=>0];
            foreach ($data as $k=>$v){
                $tj['cell_score'] +=$v['cell_score'];
                $tj['all_bet'] +=$v['all_bet'];
                $tj['profit'] +=  $v['profit']? $v['profit'] :0;
            }
            if($tj['profit']<0){
                $tj['profit'] -= $tj['profit']*$zhancheng/100;
            }
            $tj = '本页总投注统计：'.$tj['cell_score'].',本页有效投注统计：'.$tj['all_bet'].',本页输赢统计：'.$tj['profit'];
            return json([$data,$tj]);
        }else{

            return $this->fetch();
        }
    }
    //二级代理
    public function secondlevel($search=0,$agent='',$start_time='',$end_time=''){
        $uid = Session::get('proxy.uid');//获取代理的id
        if (Request::method() == 'POST'){
            $paginate = 15;
            $pageParam = [];
            $where =[];
            $map = [];
            if ($search){
                if ($agent){
                    $where[] = ['username','=',$agent];
                }
                if ($start_time){
                    $map[] = ['game_start_time','>',$start_time];
                }
                if ($end_time){
                    $map[] = ['game_end_time','<',$end_time.'23:59:59'];
                }
            }
            $where[] = ['uid', 'in', function($query) use($uid) {
                $query->table('relationship')->field('userid')->where('prev', $uid)->select();
            }];
            $zhancheng = SystemConfig::where('name','zhancheng')->find()['value'];
            $e_proxy =  Proxy::where($where)->paginate($paginate,false,$pageParam)->each(function ($item, $key) use($map,$zhancheng){
                $id = $item['uid'];
                $map[] = ['user_id', 'in', function($query) use($id) {
                    $query->table('relationship')->field('userid')->where(['prev' => $id, 'floor' => 3])->select();
                }];
                $betting = ApiBetting::where($map)->field('sum(cell_score) as cell_score,sum(all_bet) as all_bet,sum(profit) as profit')->find();
                $item['cell_score'] = $betting['cell_score']? $betting['cell_score']: 0;
                $item['all_bet'] = $betting['all_bet']? $betting['all_bet']: 0;
                $item['profit'] = 0;
                $profit= $betting['profit']? $betting['profit']: 0;
                if ($profit<0){
                    $item['profit'] = $profit-$profit*$zhancheng/100;
                }else{
                    $item['profit'] =$profit;
                }
            });
            return json($e_proxy);
        }else{
            $this->assign('uid',$uid);
            return $this->fetch();
        }
    }
    //用户
    public function chess_users($sp_id=0,$uid=0,$search=0,$agent='',$start_time='',$end_time=''){
        if (Request::method() == 'POST'){
            $paginate = 15;
            $pageParam = [];
            $where =[];
            $map = [];
            if($search){
                if ($agent){
                    $where[] = ['username','=',$agent];
                }
                if ($start_time){
                    $map[] = ['game_start_time','>',$start_time];
                }
                if ($end_time){
                    $map[] = ['game_end_time','<',$end_time.'23:59:59'];
                }
            }
            $where[] = ['id','in',function($query) use($uid) {
                $query->table('relationship')->field('userid')->where(['prev' => $uid, 'floor' => 3])->select();
            }];
            $zhancheng = SystemConfig::where('name','zhancheng')->find()['value'];
            $user = User::where($where)->paginate($paginate,false,$pageParam)->each(function ($item, $key) use($map,$zhancheng) {
                $betting = ApiBetting::where('user_id',$item['id'])->where($map)->field('sum(cell_score) as cell_score,sum(all_bet) as all_bet,sum(profit) as profit')->find();
                $item['cell_score'] = $betting['cell_score']? $betting['cell_score']: 0;
                $item['all_bet'] = $betting['all_bet']? $betting['all_bet']: 0;
                $item['profit'] =0;
                $profit= $betting['profit']? $betting['profit']: 0;
                if ($profit<0){
                    $item['profit'] =$profit- $profit*$zhancheng/100;
                }else{
                    $item['profit'] = $profit;
                }

            });
            return json($user);
        }else{
            $this->assign('sp_id',$sp_id);
            $this->assign('uid',$uid);
            return $this->fetch();
        }
    }
    //棋牌二级代理用户统计
    public function chessawm($search=0,$agent='',$start_time='',$end_time=''){
        $uid = Session::get('proxy.uid');//获取代理的id
        if (Request::method() == 'POST'){

            $paginate = 15;
            $pageParam = [];
            $where =[];
            $map = [];
            if($search){
                if ($agent){
                    $where[] = ['username','=',$agent];
                }
                if ($start_time){
                    $map[] = ['game_start_time','>',$start_time];
                }
                if ($end_time){
                    $map[] = ['game_end_time','<',$end_time.'23:59:59'];
                }
            }
            $where[] = ['id','in',function($query) use($uid) {
                $query->table('relationship')->field('userid')->where(['prev' => $uid, 'floor' => 3])->select();
            }];

            $user = User::where($where)->paginate($paginate,false,$pageParam)->each(function ($item, $key) use($map) {
                $betting = ApiBetting::where('user_id',$item['id'])->where($map)->field('sum(cell_score) as cell_score,sum(all_bet) as all_bet,sum(profit) as profit')->find();
                $item['cell_score'] = $betting['cell_score']? $betting['cell_score']: 0;
                $item['all_bet'] = $betting['all_bet']? $betting['all_bet']: 0;
                $item['profit'] = $betting['profit']? $betting['profit']: 0;
            });
            return json($user);
        }else{
            $this->assign('uid',$uid);
            return $this->fetch();
        }
    }

}