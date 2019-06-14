<?php
namespace app\prize\controller;
use app\home\controller\Lottery28;
use app\home\model\Betting;
use app\home\model\ChatRoom;
use think\Db;
function pr($var)
{
    $template = PHP_SAPI !== 'cli' ? '<pre>%s</pre>' : "\n%s\n";
    printf($template, print_r($var, true));
}
class Hlsb extends Lottery
{
    public function prize(){

        // //post数据
        // public $post_data;
        // //彩种配置
        // public $lottery_config;
        // //开奖号（数组）
        // public $prize_code;
        $return_data = [
            'code' => -1,
            'msg' => $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 派奖失败'
        ];
        //获取所有开奖类型
        $bingo = $this->winType();
        // pr($bingo);die;
        //查询本期所有投注内容
        $betting = Db::table('betting')
            ->field('id,user_id,money,content,explain,win')
            ->where('type','=',$this->post_data['type'])
            ->where('expect','=',$this->post_data['expect'])
            ->where('state','=',0)
            ->select();
        //print_r($betting);
        foreach ($betting as $key => &$value) {
            $demo = json_decode($value['content'],true);
            //每一注的金额
            $sig_money = round($value['money']/$this->betNum($demo),2);
            $win_all = 0;
            $win_sig = 0;
            foreach ($demo as $k => $v) {
                foreach ($v as $k1 => $v1) {
                        if(in_array($k1,$bingo[$k])){
                            $win_sig = round($sig_money * $this->lottery_config['basic_config'][$k]['items'][$k1]['odds'],2);
                            $win_all += $win_sig;
                            $value['explain'] .=  ( $this->lottery_config['basic_config'][$k]['name'].'-'. $this->lottery_config['basic_config'][$k]['items'][$k1]['name'].'中奖:'.$win_sig.'元;');
                            $win_sig = 0;
                        }
                }
            }
            $is_ok = moneyAction([
                'uid' => $value['user_id'],
                'money' => $win_all,
                'type' => 3,
                'explain' => $this->lottery_config['name']
            ]);
            if($is_ok == 0){
                $value['explain'] = '派奖出错,未处理这个单子';
            } elseif ($win_all <= 0){
                $value['state'] = 1;
                $value['explain'] = '已结算';
            } else {
                $value['state'] = 1;
                $value['win'] = $win_all;
                if(!isset($u_all[$value['user_id']])){
                    $u_all[$value['user_id']] = Db::table('user')->field('username,type')->where(['id'=>$value['user_id']])->find();
                }
                $strl = mb_strlen($u_all[$value['user_id']]['username']);
                $s_n = $u_all[$value['user_id']]['type'] == 1 ? '试玩用户' :mb_substr($u_all[$value['user_id']]['username'],0,floor($strl/2) ).'***';
                $chat_r[] = [
                    'user_id' => 0,
                    'content' => '恭喜玩家'.$s_n.'在游戏'.$this->lottery_config['name'].'欢乐骰子中,投注'.$value['explain'],
                    'create_time' => time(),
                ];
            }
        }

        if((new Betting)->saveAll($betting)){
            if(isset($chat_r)){
                (new ChatRoom)->insertAll($chat_r);
            }
            $return_data['code'] = 1;
            $return_data['msg'] = $this->lottery_config['name'] . ' ' . $this->post_data['expect'] . ' 期已经全部派奖';
        }
        print_r(json_encode($return_data));
        return;
    }
    //计算注数
    public function betNum($val){
        $count = 0;
        foreach ($val as $key => $value) {
            // print_r($val);die;
            $count += count($value);
        }
        return $count;
    }
    //给与开奖号码,返回所有中奖类型
    private function winType(){

        //开奖号循环获取格式化牌组
        $changePoker = [];
        $tai = [];
        $ni = [];
        foreach( $this->prize_code as $key=> $item ) {
            $changePoker[] = $item;
            $tai[] = $item;
        }
        $ku = array_sum($tai);
        //判断玩法
        if ($tai) {
            //猜数字
            $ni['szyi'] = ['code_'.$tai[0]];
            $ni['szer'] = ['code_'.$tai[1]];
            $ni['szsan'] = ['code_'.$tai[2]];
            //猜双面判断
            //第一骰子
            if ($tai[0] <=  3 && ($tai[0]%2) != 0) {
                $ni['smyi'] = [ 'code_2','code_3'];
            }elseif ($tai[0] >=  4 && ($tai[0]%2) == 0){
                $ni['smyi'] = [ 'code_1','code_4'];
            }elseif ($tai[0] >=  4 && ($tai[0]%2) != 0) {
                $ni['smyi'] = [ 'code_1','code_3'];
            }elseif ($tai[0] <=  3 && ($tai[0]%2) == 0) {
                $ni['smyi'] = [ 'code_2','code_4'];
            }
            //第二骰子
            if ($tai[1] <=  3 && ($tai[1]%2) !=0){
                $ni['smer'] = [ 'code_2','code_3'];
            }elseif ($tai[1] >=  4 && ($tai[1]%2) == 0){
                $ni['smer'] = [ 'code_1','code_4'];
            }elseif ($tai[1] >=  4 && ($tai[1]%2) != 0){
                $ni['smer'] = [ 'code_1','code_3'];
            }elseif ($tai[1] <=  3 && ($tai[1]%2) == 0){
                $ni['smer'] = [ 'code_2','code_4'];
            }
            //第三骰子
            if ($tai[2] >=  4 && ($tai[2]%2) == 0){
                $ni['smsan'] = [ 'code_1','code_4'];
            }elseif ($tai[2] <=  3 && ($tai[2]%2) != 0){
                $ni['smsan'] = [ 'code_2','code_3'];
            }elseif ($tai[2] <=  3 && ($tai[2]%2) == 0){
                $ni['smsan'] = [ 'code_2','code_4'];
            }elseif ($tai[2] >=  4 && ($tai[2]%2) != 0){
                $ni['smsan'] = [ 'code_1','code_3'];
            }
            //猜总和
            if ($ku >= 11 && ($ku%2) != 0) {
                $ni['zh'] = ['code_1','code_3'];
            }else{
                $ni['zh'] = ['code_2','code_3'];
            }
            if ($ku <= 11 && ($ku%2) == 0) {
                $ni['zh'] = ['code_2','code_4'];
            }else{
                $ni['zh'] = ['code_2','code_3'];
            }
            /*if ($ku <= 11 ){
                $ni['zh'] = ['code_2'];
            }
            if (($ku%2) != 0) {
                $ni['zh'] =['code_3'];
            }
            if (($ku%2) == 0) {
                $ni['zh'] = ['code_4'];
            }*/
            if ( $ku== 4) {
                $ni['zh'] = ['code_5'];
            }
            if ( $ku== 5) {
                $ni['zh'] = ['code_6'];
            }
            if ( $ku== 6) {
                $ni['zh'] = ['code_7'];
            }
            if ( $ku== 7) {
                $ni['zh'] = ['code_8'];
            }
            if ( $ku== 8) {
                $ni['zh'] = ['code_9'];
            }
            if ( $ku== 9) {
                $ni['zh'] = ['code_10'];
            }
            if ( $ku== 10) {
                $ni['zh'] = ['code_11'];
            }
            if ( $ku== 11) {
                $ni['zh'] = ['code_12'];
            }
            if ( $ku== 12) {
                $ni['zh'] = ['code_13'];
            }
            if ( $ku== 13) {
                $ni['zh'] = ['code_14'];
            }
            if ( $ku== 14) {
                $ni['zh'] =['code_15'];
            }
            if ( $ku== 15) {
                $ni['zh'] = ['code_16'];
            }
            if ( $ku== 16) {
                $ni['zh'] = ['code_17'];
            }
            if ( $ku== 17) {
                $ni['zh']['code_18'] = 'code_18';
            }
            //猜对子
            if ($tai[0] == $tai[1] || $tai[0] == $tai[2] || $tai[1] == $tai[2]) {
                if ($tai[0] == $tai[1]) {
                    $ni['dz'] = [ 'code_'.$tai[0]];
                }elseif ($tai[0] == $tai[2]) {
                    $ni['dz'] = ['code_'.$tai[0]];
                }elseif ($tai[1] == $tai[2]) {
                    $ni['dz'] = ['code_'.$tai[2]];
                }
            }else{
                $ni['dz'] =['1'];
            }
            //猜围骰
            if ($tai[0] == $tai[1] && $tai[0] == $tai[2] && $tai[1] == $tai[2]) {
                $ni['ws'] = ['code_'.$tai[1],'code_7'];
            }else{
                $ni['ws'] = ['1'];
            }
            //猜单骰
            if ($tai){
                $ni['ds'] = ['code_'.$tai[0],'code_'.$tai[1],'code_'.$tai[2],];
            }
            //猜双骰
            if ($tai[0] == $tai[1] && $tai[0] == $tai[2] && $tai[1] == $tai[2]) {
                $ni['ss'] =['1'];
            }else{
                if(in_array(1,$tai) && in_array(2,$tai)) {
                    $ni['ss'] = [ 'code_1'];
                }
                if(in_array(1,$tai) && in_array(3,$tai)) {
                    $ni['ss'] = [ 'code_2'];
                }
                if(in_array(1,$tai) && in_array(4,$tai)) {
                    $ni['ss'] = [ 'code_3'];
                }
                if(in_array(1,$tai) && in_array(5,$tai)) {
                    $ni['ss'] = [ 'code_4'];
                }
                if(in_array(1,$tai) && in_array(6,$tai)) {
                    $ni['ss'] = [ 'code_5'];
                }
                if(in_array(2,$tai) && in_array(3,$tai)) {
                    $ni['ss'] = [ 'code_6'];
                }
                if(in_array(2,$tai) && in_array(4,$tai)) {
                    $ni['ss'] = [ 'code_7'];
                }
                if(in_array(2,$tai) && in_array(5,$tai)) {
                    $ni['ss'] = [ 'code_8'];
                }
                if(in_array(2,$tai) && in_array(6,$tai)) {
                    $ni['ss'] = [ 'code_9'];
                }
                if(in_array(3,$tai) && in_array(4,$tai)) {
                    $ni['ss'] = [ 'code_10'];
                }
                if(in_array(3,$tai) && in_array(5,$tai)) {
                    $ni['ss'] = [ 'code_11'];
                }
                if(in_array(3,$tai) && in_array(6,$tai)) {
                    $ni['ss'] = [ 'code_12'];
                }
                if(in_array(4,$tai) && in_array(5,$tai)) {
                    $ni['ss'] = [ 'code_13'];
                }
                if(in_array(4,$tai) && in_array(6,$tai)) {
                    $ni['ss'] = [ 'code_14'];
                }
                if(in_array(5,$tai) && in_array(6,$tai)) {
                    $ni['ss'] = [ 'code_15'];
                }
            }
        }
        return $ni;
    }
}
