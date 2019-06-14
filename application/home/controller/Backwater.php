<?php
namespace app\home\controller;

use think\Controller;
use think\Db;
use think\facade\Request;

class Backwater extends controller {

  public function returnMoney() {
    $t = time();
    // $start_time = strtotime(date("Y-m-d", strtotime("-1 day")));
    // $end_time = $start_time + 3600 * 24 - 1;
    $start_time = mktime(0, 0, 0, date("m", $t), date("d", $t), date("Y", $t));
    $end_time = mktime(23, 59, 59, date("m", $t), date("d", $t), date("Y", $t));
    $map = [];
    $lotterys = Db::table('lottery_config')->where(['type' => ['type', 'in', [24, 25, 26, 27]]])->field('type,return')->select();
    foreach ($lotterys as $key => $value) {
      $lotterys[$key]['return'] = json_decode($value['return'],true);
      $range = array_reverse($lotterys[$key]['return']['range'], true);
      $map['type'] = $value['type'];
      //
      $map['create_time'] = ['create_time', 'between', [$start_time, $end_time]];
      //找普通普通用户
      // dump($value['type']);
      $mop['be.type'] =  ['be.type', '=', $value['type']];
      $mop['u.type'] = ['u.type','=','0'];
      $mop['u.group'] = ['u.group','=','0'];
      $mop['be.create_time'] = ['be.create_time', 'between', [$start_time, $end_time]];
      $ptyh = DB::table('betting')->alias('be')->where($mop)->join('user u','be.user_id = u.id')->field('be.user_id')->group('be.user_id')->select();
      $uid = [];
      foreach ($ptyh as $ss) {
        $uid[] = $ss['user_id'];
      }
      

      $map['user_id'] = ['user_id','in',$uid];
      $total_datas = Db::table('betting')->field('id,user_id,content,money,money,type,win')->where($map)->select();
      // dump($total_datas);die;
      if (count($total_datas) != 0) {//判断今天是否有用户下注
        //计算大小单双所占比例
        $dxds = 0;
        foreach ($total_datas as $k => $v) {
          $content = json_decode($v['content'],true);
          foreach ($content as $k1 => $v1) {
            if (in_array($content[$k1]['code'], ['a', 'b', 'c', 'd'])) {
              //dump($content[$k1]['money']);
              $dxds += $content[$k1]['money'];
            }
          }
        }
        // dump($dxds);
        //总投注额度
        $totalbetting = array_sum(array_map(function ($val) {return $val['money'];}, $total_datas));

        $totalwin = array_sum(array_map(function ($val) {return $val['win'];}, $total_datas));
        $percent = 0;
        $dxds_rate = $dxds / $totalbetting * 100;


        if ($dxds_rate < floatval($lotterys[$key]['return']['condition'])) { //判断用户的 下注 是否占 总金额的 75%（后台设置比例）
          // dump($dxds_rate);die;
          //echo floatval($lotterys[$key]['return']['condition']);die;
          $sql = "select user_id,(sum(money)-sum(win)) as profit  from betting  where type=" . $value['type'] . " and create_time between " . $start_time . " and " . $end_time . " group by user_id";
          // dump($sql);die;
          $profit_datas = Db::table('betting')->query($sql);
          //dump($prox)
          //dump($profit_datas);die;
          // dump('ss');exit();
          if (count($profit_datas) !== 0) {

            foreach ($profit_datas as $kk => $vv) {
              $percent = 0;

              if ($profit_datas[$kk]['profit'] > end($range)[0]) {

                foreach ($range as $k2 => $v2) {
                  if (($totalbetting - $totalwin) >= $v2[0]) {
                    $percent = $v2[1];
                    break;
                  }
                }
                // dump($vv);die;
                $data['uid'] = $vv['user_id'];
                $data['money'] = round($vv['profit'] * $percent / 100, 2);
                $data['type'] = 11;
                dump($data['uid']);die;
                // moneyAction($data);
              }
            }
          }

        }else{
          echo "下注金额 除以 总额 大于数据库设置的比例--默认75%".'<br>';
           // return json(['error' => 1, 'msg' => '下注金额 除以 总额 大于数据库设置的比例']);
        }

      }else{
        echo "今天28没有人下注".'<br>';
          // return json(['error' => 1, 'msg' => '今天28没有人下注']);
      }
    }

  }



}