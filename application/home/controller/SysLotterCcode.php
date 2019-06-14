<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Request;
use app\prize\controller\Klsf;
use app\prize\controller\Ks;
use app\prize\controller\Lhc;
use app\prize\controller\P3d;
use app\prize\controller\Pc28;
use app\prize\controller\Pk10;
use app\prize\controller\Ssc;
use app\prize\controller\Syxw;
use app\prize\controller\Xync;
use app\prize\controller\Brnn;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/25
 * Time: 12:42
 */
class SysLotterCcode extends Controller
{
    private function lottery_code($typekey)
    {

        //记录最新一期号码，方便传入计划表
        $type = $typekey;
        if (Db::table('lottery_code')->where('type', $type)->where('create_time', '>', time())->find()) {
            Db::table('lottery_code')->where('type', $type)->where('create_time', '>', time())->delete();
        }
        $the_newest_data = [];
        //获取彩种的配置信息
        $lottery_config = Db::table('lottery_config')->where('type', $type)->find();
        $config = json_decode($lottery_config['time_config'], true);
        if ($config['num'] < 100) {
            $str = '%02d';
        } else if ($config['num'] >= 100 && $config['num'] < 1000) {
            $str = '%03d';
        } else {
            $str = '%04d';
        }
        //获取当前彩种当天第一期开奖的时间错
        $start_time = strtotime(date('Y-m-d ') . $config['start_time']);
        //获取当前彩种开奖间隔时间
        $jiange = $config['cha'] * 60;

        //数组，用以暂时储存要写入lottery_code的期号
        $expectArr = [];
        //获取最新一期开奖时间
        $now_qi = Db::table('lottery_code')->where('type', $type)->order('expect DESC')->find();
        //计算该彩种当前时间应该的期号
        //获取当前时间为当前开奖期数
        $now = time();
        $qishu = intval(($now - $start_time) / $jiange);
        $qishu = $qishu <= $config['num'] ? $qishu : $config['num'];
        if ($qishu < 1) {
            //获取前一天最后一期期号
            $last_expect = date('Ymd', strtotime('-1day')) . sprintf($str, $config['num']);
            $the_last = Db::table('lottery_code')->where('type', $type)->where('expect', $last_expect)->find();
            if (!$the_last) {
                //查看是否有预测号码
                $has = Db::table('preset_lottery_code')->where('expect', '=', $last_expect)->where('type', $type)->find();
                if ($has) {
                    $code = $has['content'];
                } else {
                    $code = getTypeCode($type);
                }
                $last_insert_arr = [
                    'expect' => $last_expect,
                    'content' => $code,
                    'type' => $type,
                    'create_time' => time()
                ];
                $last_insert = Db::table('lottery_code')->insert($last_insert_arr);
                if ($last_insert) {
                    $msg = date('Y-m-d H:i:s') . '-------【' . $lottery_config['name'] . '】 ' . $lottery_config['note'] . '采集成功。' . PHP_EOL;
                    echo $msg;
                    $this->forPaiJiang($last_expect, $type);
                }
            } else {
                return 2;
            }
        }
        if ($now_qi && date('Ymd') . sprintf($str, $qishu) == $now_qi['expect']) {
            return 2;
        }
        $insert = [];
        if ($now_qi) {
            //获取库中最新一期的开奖日期与当前一期的间隔天数
            if (strtotime(date('Y-m-d', $now_qi['create_time'])) == strtotime(date('Y-m-d'))) {
                $shengyu_qi = $qishu - (int)substr($now_qi['expect'], 8);
                if ((int)substr($now_qi['expect'], 8) == $config['num']) {
                    $shengyu_qi = $qishu;
                    $now_qi['expect'] = date('Ymd') . sprintf($str, 0);

                }
                for ($m = 1; $m <= $shengyu_qi; $m++) {
                    // $kai_time = $now_qi['create_time'] + $m * $jiange;

                    $new_expect = $now_qi['expect'] + $m;
                    $kai_time = $start_time + $jiange * substr($new_expect, 8);
                    $code = getTypeCode($type);
                    $insert[] = [
                        'expect' => $new_expect,
                        'content' => $code,
                        'type' => $type,
                        'create_time' => $kai_time
                    ];
                    $expectArr[] = $new_expect;
                    if ($m == $shengyu_qi) {
                        $the_newest_data['type'] = $type;
                        $the_newest_data['expect'] = $new_expect;
                        $the_newest_data['content'] = $code;
                    }
                }
            } else {
                //计算当前这一期开奖时间
                for ($i = 1; $i <= $qishu; $i++) {
                    $kai_time = $start_time + $i * $jiange;

                    $new_expect = date('Ymd') . sprintf($str, $i);
                    $code = getTypeCode($type);
                    $insert[] = [
                        'expect' => $new_expect,
                        'content' => $code,
                        'type' => $type,
                        'create_time' => $kai_time
                    ];
                    $expectArr[] = $new_expect;
                    if ($i == $qishu) {
                        $the_newest_data['type'] = $type;
                        $the_newest_data['expect'] = $new_expect;
                    }
                }
            }
        } else {

            //计算当前这一期开奖时间
            for ($i = 1; $i <= $qishu; $i++) {
                $kai_time = $start_time + $i * $jiange;

                $new_expect = date('Ymd') . sprintf($str, $i);
                $code = getTypeCode($type);
                $insert[] = [
                    'expect' => $new_expect,
                    'content' => $code,
                    'type' => $type,
                    'create_time' => $kai_time
                ];
                $expectArr[] = $new_expect;
                if ($i == $qishu) {
                    $the_newest_data['type'] = $type;
                    $the_newest_data['expect'] = $new_expect;
                    $the_newest_data['content'] = $code;
                }
            }
        }
        //查询对应类型和对应期号下是否有预测记录
        $hasForecast = Db::table('preset_lottery_code')->where('expect', 'in', $expectArr)->where('type', $type)->select();
        if ($hasForecast) {
            foreach ($hasForecast as $h) {
                foreach ($insert as &$ins) {
                    if ($h['expect'] == $ins['expect']) {
                        $ins['content'] = $h['content'];
                    } else {
                        continue;
                    }
                }
            }
        }
        //获取系统生成的code
        $insert = Db::table('lottery_code')->insertAll($insert);
        if ($insert) {
            $msg = date('Y-m-d H:i:s') . '-------【' . $lottery_config['name'] . '】 ' . $lottery_config['note'] . '采集成功。' . PHP_EOL;
//            file_put_contents('sysCaiJiLog.txt', $msg, FILE_APPEND);
            echo $msg;
            $this->forPaiJiang($the_newest_data['expect'], $the_newest_data['type']);
//            $plan = new \app\home\controller\Plan();
//            $plan->index($the_newest_data['type'],$the_newest_data['expect'],explode(',',$the_newest_data['content']));
            return 1;
        } else {
            return 2;
        }
    }

    public function judgeLottery()
    {
        $timeArr = [time(),time()-1,time()-2,time()-3,time()-4,time()-5,time()-6,time()-7,time()-8,time()-9,time()-10];

        $type = [5, 6, 8, 9, 11, 36, 37, 38, 39, 40, 41, 42, 44, 45, 51, 57, 58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77];
        foreach ($type as $t)
        {
            $lottery_config = Db::table('lottery_config')->where('type',$t)->find();
            $config = json_decode($lottery_config['time_config'],true);
            //判断当前时间是否有指定类型的彩种开奖
            $start_time = strtotime(date('Y-m-d ').$config['start_time']);
            foreach ($timeArr as $now_time)
            {
                $cha = $config['cha']*60;
                $cha = (int) $cha;
                $now_expect = ($now_time - $start_time)/$cha;
                if(gettype($now_expect) == 'integer')
                {
                    $this->lottery_code($t);
                }
            }
        }
    }

    public function beforeJudgeLottery()
    {
        $type = [5, 6, 8, 9, 11, 36, 37, 38, 39, 40, 41, 42, 44, 45, 51, 57, 58,59,60,61,62,63,64,65,66,67,68,69,70,71,72,73,74,75,76,77];
        foreach ($type as $t)
        {
            Db::table('lottery_code')->where('type',$t)->where('create_time','>',time())->delete();
            $this->lottery_code($t);
        }
    }

    public function forPaiJiang($expect,$type)
    {
        $caishu = 0;
        $curl = curl_init();
        while($caishu < 5)
        {
            $caishu += 1;
            //设置抓取的url
            curl_setopt($curl, CURLOPT_URL, config('web_host').'/'.'SysCodePaiJiang');
            //设置头文件的信息作为数据流输出
            curl_setopt($curl, CURLOPT_HEADER, 0);
            //设置获取的信息以文件流的形式返回，而不是直接输出。
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
            //设置post方式提交
            curl_setopt($curl, CURLOPT_POST, 1);
            //设置post数据
            $post_data = array(
                "type" => $type,
                "expect" => $expect
            );
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            //执行命令
            $data = curl_exec($curl);
            $data = json_decode($data,true);
            $msg = date('Y-m-d H:i:s').'-------'.$data['msg'].PHP_EOL;
            file_put_contents('paijianglog.txt',$msg,FILE_APPEND);
            if($data['code'] == 1 || $caishu == 4)
            {
                echo $msg;
                curl_close($curl);
                break;
            }
        }
    }

    public function paijiang(Request $r)
    {
        $post = $r->post();
        $Klsf_array = [20, 50,60,61,62,63];
        $Ks_array = [10, 14, 15, 30, 31, 32, 33, 34, 40, 41, 42, 43,59];
        $Lhc_array = [11, 21,73,74,75,76];
        $P3d_array = [19, 22,64,65,66,67,68,69];
        $Pc28_array = [24, 25, 26, 27,57,58];
        $Pk10_array = [3, 4, 5, 36, 37, 38, 39, 51];
        $Ssc_array = [2, 6, 7, 8, 9, 12, 13, 28 ];
        $Syxw_array = [16, 17, 18, 44, 45, 46, 47, 48, 49,77];
        $Xync_array = [23];
        $bhd_bjl = [0, 1]; //龙虎斗 ，百家乐
        $niuniu = [52];

        if (in_array($post['type'], $Klsf_array)) {
            $rs = new Klsf();
        } elseif (in_array($post['type'], $Ks_array)) {
            $rs = new Ks();
        } elseif (in_array($post['type'], $Lhc_array)) {
            $rs = new Lhc();
        } elseif (in_array($post['type'], $P3d_array)) {
            $rs = new P3d();
        } elseif (in_array($post['type'], $Pc28_array)) {
            $rs = new Pc28();
        } elseif (in_array($post['type'], $Pk10_array)) {
            $rs = new Pk10();
        } elseif (in_array($post['type'], $Ssc_array)) {
            $rs = new Ssc();
        } elseif (in_array($post['type'], $Syxw_array)) {
            $rs = new Syxw();
        } elseif (in_array($post['type'], $Xync_array)) {
            $rs = new Xync();
        } elseif (in_array($post['type'], $bhd_bjl)) {//龙虎 百家
            $rs = new Game();
        } else if (in_array($post['type'], $niuniu)){ //牛牛
            $rs = new Brnn();
        }else {
            return '该彩种不存在';
        }

        // die;
        $re = $rs->prize($post['expect'], $post['type']);
        echo $re['msg'];
    }
}