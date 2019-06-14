<?php
namespace app\djycpgk\controller;
use app\djycpgk\model\User;
use app\djycpgk\model\ApiBetting;
use app\djycpgk\model\ApiConfig;
use app\djycpgk\model\ApiGame;
use app\djycpgk\model\Proxy;
use app\djycpgk\model\CapitalAudit;
use app\home\model\SystemConfig;
use Illuminate\Support\Debug\Dumper;
use think\Controller;
use think\Db;
use think\facade\Request;

class ChessGame extends Rbac {
    //德州扑克  0709292a0000000000000000252b0000211104281d181a   type 1
    //二八杠  5326814a3  type 2
    //抢庄牛牛  360c2c14180000000000360c2c141800000000001  type 3
    //炸金花  161c1d000000262c2d000000363c3d5  type 4
    //三公  161c1d000000262c2d000000363c3d5  type 5
    //押庄龙虎  161c0112 type 6
    //21点  02d1317,13d062a,2032703,323253d-333b|41c29|5393b type 7
    //通比牛牛  360c2c1418000000000000000000000000000000360c2c141800000000001 type 8
    //二人麻将  0203040506070203040506071111,0102030607081212121315151528,0   type 740
    //红黑大战  37241409390413   type 750
    //十三水  3b2c110,04352607384,323d2d1d0d7,4;342b0b1,2336062a1a2,22123929096,1;083a3c0,33252731011,131516171c5,2;1112131415161718191a1b1c1d263;0   type 630
    //通比牛牛 360c2c1418000000000000000000000000000000360c2c141800000000001 type 870
    public function cardvalue($type=870,$val='360c2c1418000000000000000000000000000000360c2c141800000000001'){ //牌读取规则
        $hua = ['0'=>'♦','1'=>'♣','2'=>'♥','3'=>'♠','4'=>'王']; //花色
        $pai = ['1'=>'A','a'=>'10','b'=>'J','c'=>'Q','d'=>'K']; //牌
        if ($type==620){ //  德州扑克
            $gon= substr($val,-10);
            $rs  = str_split($val,2); //手牌
            $gon = str_split($gon,2); //共牌
            for ($i=0;$i<5;$i++){
                array_pop($rs);
            }
            $rs = implode('', $rs);
            $rs  = str_split($rs,4); //手牌
            foreach ($rs as $k=> &$value ){ //玩家手牌
                $value = str_split($value,2);
                foreach ($value as $k => &$v) {
                    if($v == '00'){continue;}
                    $num = $v[1];
                    $v = $hua[$v[0]].(isset($pai[$num[0][0]]) ? $pai[$num[0][0]] : $num[0][0]);
                }
            }
            foreach ($gon as $k => &$v) { //共牌
                if($v == '00'){continue;}
                $num = $v[1];
                $v = $hua[$v[0]].(isset($pai[$num[0][0]]) ? $pai[$num[0][0]] : $num[0][0]);
            }

            foreach ($rs as $k=>&$v){
                if ($v[0]=='00' || $v[1] =='00'){
                    $v = '第'.($k+1).'座位无玩家';
                }else{
                    $v = '第'.($k+1).'座位玩家手牌:('.implode('_', $v).')';
                }
            }
            $rs = implode('_',$rs);
            $gon = '公共牌:'.implode('_', $gon);
            $arr = $gon.$rs;
            return json($arr);
        }
        elseif ($type == 720){//二八杠
            $pai = ['1'=>'一筒','2'=>'二筒','3'=>'三筒','4'=>'四筒','5'=>'五筒','6'=>'六筒','7'=>'七筒','8'=>'八筒','9'=>'九筒','a'=>'白板']; //牌
            $zuang= '庄家是'.substr($val,-1).'号玩家'; //获取庄家
            $wj = substr($val, 0, -1);//去除
            $wj = str_split($wj,2);
            foreach ($wj as $k=>&$v){
                $v = str_split($v,1);
                foreach ($v as $ks=>&$va){
                    $va = $pai[$va];
                }
            }
            foreach ($wj as $k =>&$vq){
                $vq = '第'.($k+1).'号玩家手牌'.implode('_', $vq);
            }
            $arr = [$wj,$zuang];
            return json($arr);
        }
        elseif ($type == 3){ //抢庄牛牛
            $zuang= '庄家是'.substr($val,-1).'号玩家'; //获取庄家
            $yh = str_split($val,10); //用户手牌
            array_pop($yh); //删除庄
            foreach ($yh as $k=> &$value ){ //玩家手牌
                $value = str_split($value,2);
                foreach ($value as $k => &$v) {
                    if($v == '00'){continue;}
                    $num = $v[1];
                    $v = $hua[$v[0]].(isset($pai[$num[0][0]]) ? $pai[$num[0][0]] : $num[0][0]);
                }
            }
            foreach ($yh as $k=>&$v){
                if ($v[0]=='00' || $v[1] =='00' ){
                    $v = '第'.($k+1).'座位无玩家';
                }else{
                    $v = '第'.($k+1).'座位玩家手牌:('.implode('_', $v).')';
                }
            }
            $arr = [$yh,$zuang];
            return json($arr);
        }
        elseif ($type ==220) {//炸金花
            $zuang= substr($val,-1).'号位的玩家是赢家'; //获取赢家
            $yh = str_split($val,6); //用户手牌
            array_pop($yh); //删除赢家
            foreach ($yh as $k=> &$value ){ //玩家手牌
                $value = str_split($value,2);
                foreach ($value as $k => &$v) {
                    if($v == '00'){continue;}
                    $num = $v[1];
                    $v = $hua[$v[0]].(isset($pai[$num[0][0]]) ? $pai[$num[0][0]] : $num[0][0]);
                }
            }
            foreach ($yh as $k=>&$v){
                if ($v[0]=='00' || $v[1] =='00' ){
                    $v = '第'.($k+1).'座位无玩家';
                }else{
                    $v = '第'.($k+1).'座位玩家手牌:('.implode('_', $v).')';
                }
            }
            $arr = [$yh , $zuang];
            return json($arr);
        }
        elseif ($type ==5) {//三公
            $zuang= substr($val,-1).'号位的玩家是庄家'; //获取赢家
            $yh = str_split($val,6); //用户手牌
            array_pop($yh); //删除赢家
            foreach ($yh as $k=> &$value ){ //玩家手牌
                $value = str_split($value,2);
                foreach ($value as $k => &$v) {
                    if($v == '00'){continue;}
                    $num = $v[1];
                    $v = $hua[$v[0]].(isset($pai[$num[0][0]]) ? $pai[$num[0][0]] : $num[0][0]);
                }
            }
            foreach ($yh as $k=>&$v){
                if ($v[0]=='00' || $v[1] =='00' ){
                    $v = '第'.($k+1).'座位无玩家';
                }else{
                    $v = '第'.($k+1).'座位玩家手牌:('.implode('_', $v).')';
                }
            }
            $arr = [$yh , $zuang];
            return json($arr);
        }
        elseif ($type ==6){ //押庄龙虎
            $sz = ['01'=>'龙', '02'=>'虎', '03'=>'和', '04'=>'龙-黑桃', '05'=>'龙-红桃', '06'=>'龙-梅花', '07'=>'龙-方块', '08'=>'虎-黑桃', '09'=>'虎-红桃', '10'=>'虎-梅花', '11'=>'虎-方块', '12'=>'押庄赢', '13'=>'押庄输',];
            $hm= substr($val,0,4); //龙虎牌
            $kj = str_split($val,2); //开奖号码
            for ($i=0;$i<2;$i++){
                 array_shift($kj);
            }
            $hm = str_split($hm,2);
            foreach ($hm as $k => &$v) { //转换龙虎 手牌
                if($v == '00'){continue;}
                $num = $v[1];
                $v = $hua[$v[0]].(isset($pai[$num[0][0]]) ? $pai[$num[0][0]] : $num[0][0]);
            }
            foreach ($kj as $k=>&$v){ //转换开奖
                $v = $sz[$v];
            }
            $hm = '龙虎开牌为：'.implode('，', $hm);
            $kj = '本局：'.implode('，', $kj).'获胜';
            return json([$hm,$kj]);
        }
        elseif ($type ==600){//21点
            $val = explode(",",$val);
            foreach ($val as $k =>&$v) {
                if (strpos($v,'|')){//判断是否在其他位置 下注


                    if (strpos($v,'-')){ //判断是否分牌
                        $v = explode("|",$v);
                        foreach ($v as $kwc => &$vwc){
                            if (strpos($vwc,'-')){
                                $zw= substr($vwc,0,1); //获取字符串第一位  第一位代表位置
                                $vwc = substr($vwc,1);//删除  字符串第一位
                                $rus = $zw.'号位进行了分牌:';
                                $vwc = explode("-",$vwc);
                                foreach ($vwc as $ka=>&$valz){
                                    $valz =  $this->zhuanhuan($valz);
                                    $rus .= '第'.($ka+1).'墩的牌:'.implode(',', $valz).' -- ';
                                }
                                $vwc = $rus;
                            }else{
                                $zw= substr($vwc,0,1); //获取字符串第一位  第一位代表位置
                                $vwc = substr($vwc,1);//删除  字符串第一位
                                $vwc =  $this->zhuanhuan($vwc);
                                $vwc ='也在'. $zw.'空位上进行了下注:'.implode(',', $vwc);
                            }
                        }
                        $v = implode('', $v);

                    }else{//没有分牌
                        $v = explode("|",$v);
                        foreach ($v as $kwm => &$vws){
                            $zw= substr($vws,0,1); //获取字符串第一位  第一位代表位置
                            $vws = substr($vws,1);//删除  字符串第一位
                            $vws =  $this->zhuanhuan($vws);
                            if ($kwm == 0){
                                $vws = $zw.'号位牌:'.implode(',', $vws);
                            }else{
                                $vws ='也在'. $zw.'号空位上进行了下注:'.implode(',', $vws);
                            }

                        }
                        $v = implode('__', $v);

                    }
                }
                else{ //没有在其他位置下注
                    $zw= substr($v,0,1); //获取字符串第一位  第一位代表位置
                    $v = substr($v,1);//删除  字符串第一位


                    if (strpos($v,'-')){ //判断是否分牌

                        $rus = $zw.'号位进行了分牌:';
                        $v = explode("-",$v);
                        foreach ($v as $ka=>&$valz){
                            $valz =  $this->zhuanhuan($valz);
                            $rus .= '第'.($ka+1).'墩的牌:'.implode(',', $valz).'--';

                        }
                        $v = $rus;

                    }else{ //未分牌
                        $v =  $this->zhuanhuan($v);
                        if ($zw == 0){
                            $v = '庄 家 牌:'.implode(',', $v);
                        }else{
                            $v = $zw.'号位牌:'.implode(',', $v);
                        }
                    }
                }
            }
            return json($val);
        }
        elseif ($type == 740){//二人麻将
            $val = explode(",",$val);
            $zhuang  =end($val); //庄家
            array_pop($val);// 玩家手牌
            $rww = ['01'=>'一万','02'=>'二万','03'=>'三万','04'=>'四万','05'=>'五万','06'=>'六万','07'=>'七万'
                ,'08'=>'八万','09'=>'九万','11'=>'东风','12'=>'南风','13'=>'西风','14'=>'北风','15'=>'中','16'=>'發'
                ,'17'=>'白','21'=>'春','22'=>'夏','23'=>'秋','24'=>'冬','25'=>'梅','26'=>'兰','27'=>'竹','28'=>'菊'];
            foreach ($val as $k =>&$value){
                $value = str_split($value,2);
                foreach ($value as $ke =>&$valz){
                    $valz = $rww[$valz];
                }
                $value =($k+1).'号位玩家牌为'.implode('，', $value);
            }
            $json=[];
            $json[] = implode('__', $val);
            $json[] = $zhuang !=0?$zhuang.'庄家' :'没有庄家';
            return json(implode('，', $json));
        }
        elseif ($type==750){  // 红黑大战
            $val = str_split($val,6);
            $kaijiang = end($val);
            array_pop ($val);
            foreach ($val as $k=>&$value){
                $value = $this->zhuanhuan($value);
                $pai = $k==0? '红方牌:':'黑方牌:';
                $value = $pai.implode('，', $value);
            }
            $val = implode('__', $val);
            $arww = ['1'=>'黑','2'=>'红','3'=>'幸运一击'];
            $kaijiang = str_split($kaijiang,1);
            foreach ($kaijiang as $kw =>&$vwm){
                $vwm = $arww[$vwm];
            }
            $kaijiang = '胜利方：'.implode(',', $kaijiang);
            $json = [];
            $json[] = $val;
            $json[] = $kaijiang;
            return json(implode('，', $json));
        }elseif ($type==630){
            $val = explode(";",$val);
            array_pop ( $val );
            $paixing =['0'=>'乌龙','1'=>'一对','2'=>'两对','3'=>'三条','4'=>'顺子','5'=>'同花','6'=>'葫芦','7'=>'铁支'
                ,'8'=>'同花顺','14'=>'三同花','15'=>'三顺子','16'=>'六对半','17'=>'五对三条','18'=>'四套三条','19'=>'凑一色','20'=>'全小'
                ,'21'=>'全大','22'=>'三分天下','23'=>'三同花顺','24'=>'十二皇族','25'=>'十三水','26'=>'至尊青龙'];
            foreach ($val as $k =>&$value){
                if (strpos($value,',') !== false){ //包含
                    $value = explode(",",$value);
                    $wz = end($value);//桌位
                    array_pop ($value);//删除最后一位
                    foreach ($value as $kew =>&$valuex){
                        $valuex = str_split($valuex,2);
                        $px = end($valuex);//牌型
                        array_pop ($valuex);//删除最后一位
                        foreach ($valuex as $k=> &$value3 )  {
                            $num = $value3[1];
                            $value3 = $hua[$value3[0]].(isset($pai[$num[0][0]]) ? $pai[$num[0][0]] : $num[0][0]);
                        }
                        $valuex = implode('', $valuex).'_'.$paixing[$px];
                    }
                    $value = $wz.'号位'.implode(',', $value);
                }else{ //不包含
                    $value = str_split($value,2);
                    $wz = end($value);//桌位
                    array_pop ($value);//删除最后一位
                    $px = end($value);//牌型
                    array_pop ($value);//在删除最后一位
                    $value = implode('', $value);
                    $value = $this->zhuanhuan($value);
                    $value = $wz.'号位'.implode(' ', $value).'_'.$paixing[$px];;
                }
            }
            return json(implode('--', $val));
        }elseif ($type ==870){ //通比牛牛
            dump($val);
        }
        else{
            return json(['aa'=>0]);
        }
   }
    public function zhuanhuan($value){ //扑克牌装换
        $hua = ['0'=>'♦','1'=>'♣','2'=>'♥','3'=>'♠','4'=>'王']; //花色
        $pai = ['1'=>'A','a'=>'10','b'=>'J','c'=>'Q','d'=>'K']; //牌

        $value = str_split($value,2);
        foreach ($value as $k=> &$value3 )  {
            $num = $value3[1];
            $value3 = $hua[$value3[0]].(isset($pai[$num[0][0]]) ? $pai[$num[0][0]] : $num[0][0]);
        }
        return $value;
    }


    public function apiconfig(){
        $config = ApiConfig::all();
        return json($config);
    }
    public function couponList($type=1){
        $game = ApiGame::where('api_id',$type)->select();
        return json($game);
    }
    //棋牌顶级 统计
    public function agent($search=0,$agent='',$start_time='',$end_time=''){
        $paginate = 15;
        $pageParam = [];
        if (Request::method() == 'POST'){
            $where = [];
            $map = [];
            if ($start_time==''){
                $map[] = ['game_start_time','>',date("Y/m/d")];
            }
            if ($end_time==''){
                $map[] = ['game_end_time','<',date("Y/m/d").' 23:59:59'];
            }
            if ($search==1){
                if ($agent){
                    $where[] = ['username','=',$agent];
                }
                if ($start_time){
                    $map[] = ['game_start_time','>',$start_time];
                }
                if ($end_time){
                    $map[] = ['game_end_time','<',$end_time.' 23:59:59'];
                }
            }
            $proxy = Proxy::where('type',1)->paginate($paginate,false,$pageParam)->each(function ($item, $key) use($map) {
                $uid = $item['uid'];
                $map[] = ['user_id', 'in', function($query) use($uid) {
                    $query->table('relationship')->field('userid')->where(['top' => $uid, 'floor' => 3])->select();
                }];
                $zhancheng = SystemConfig::where('name','zhancheng')->find()['value'];
                $betting = ApiBetting::where($map)->field('sum(cell_score) as cell_score,sum(all_bet) as all_bet,sum(profit) as profit')->find();
                $item['cell_score'] = $betting['cell_score']? $betting['cell_score']: 0;
                $item['all_bet'] = $betting['all_bet']? $betting['all_bet']: 0;
                $profit= $betting['profit']? $betting['profit']: 0;
                $item['profit'] = 0;
                if ($profit < 0){
                    $item['profit'] =$profit-$profit*$zhancheng/100;
                }else{
                    $item['profit'] =$profit;
                }
                return $item;
            });
            return json($proxy);
        }else{
            return $this->fetch();
        }
    }
    //棋牌二级代理统计
    public function secondlevel($uid=0,$search=0,$agent='',$start_time='',$end_time=''){
        if (Request::method() == 'POST'){
            $paginate = 15;
            $pageParam = [];
            $where =[];
            $map = [];
            if ($start_time==''){
                $map[] = ['game_start_time','>',date("Y/m/d")];
            }
            if ($end_time==''){
                $map[] = ['game_end_time','<',date("Y/m/d").' 23:59:59'];
            }
            if ($start_time==''){
                $map[] = ['game_start_time','>',date("Y/m/d")];
            }
            if ($end_time==''){
                $map[] = ['game_end_time','<',date("Y/m/d").' 23:59:59'];
            }
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
    //棋牌用户统计
    public function chess_users($sp_id=0,$uid=0,$search=0,$agent='',$start_time='',$end_time=''){
        if (Request::method() == 'POST'){
            $paginate = 15;
            $pageParam = [];
            $where =[];
            $map = [];
            if ($start_time==''){
                $map[] = ['game_start_time','>',date("Y/m/d")];
            }
            if ($end_time==''){
                $map[] = ['game_end_time','<',date("Y/m/d").' 23:59:59'];
            }
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
                $item['profit'] = 0;
                $profit= $betting['profit']? $betting['profit']: 0;
                if ($profit<0){
                    $item['profit'] =$profit- $profit*$zhancheng/100;
                }else{
                    $item['profit'] =$profit;
                }
            });

            return json($user);
        }else{
            $this->assign('sp_id',$sp_id);
            $this->assign('uid',$uid);
            return $this->fetch();
        }
    }
    /**
     * @param int $search  判断是否点击搜索
     * @param string $start_time  开始时间
     * @param string $end_time 结束时间
     * @param string $name 用户名
     * @param string $agent  代理名称
     * @param int $type 运营商
     * @param int $type_yx 游戏
     * @param string $number 局号
     * @return mixed|\think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function reportForm($search=0,$start_time='',$end_time='',$name='',$agent='',$type=-1,$type_yx=-1,$number='') //输赢报表
    {
        $paginate = 15;
        $pageParam = [];

        if (Request::method() == 'POST'){
            $where = [];
            $whm  =[];
            $whm[0] = ['game_start_time','>',date("Y-m-d")];
            $whm[1] = ['game_end_time','<',date("Y-m-d").' 23:59:59'];
            if ($search ==1){
                if ($start_time){
                    $where[] = ['game_start_time','>',$start_time];
                    $whm[0] = ['game_start_time','>',$start_time];
                }
                if ($end_time){
                    $where[] = ['game_end_time','<',$end_time.' 23:59:59'];
                    $whm[1] = ['game_end_time','<',$end_time.' 23:59:59'];
                }
                if ($name){
                    $where[] = ['username','=',$name];
                    $whm[] = ['username','=',$name];
                }
                if ($type !=-1)
                {
                    $where[] = ['api_id','=',$type];
                    if ($type_yx !=-1){
                        $where[] = ['kind_id','=',$type_yx];
                        $whm[] = ['kind_id','=',$type_yx];
                    }
                }
                if ($number){
                    $where[] = ['game_id','=',$number];
                    $whm[] = ['game_id','=',$number];
                }
                if ($agent){
                    $proxy = Proxy::where('username',$agent)->field('uid,type')->find();
                    if ($proxy['type'] == 1){
                        $where[] = ['user_id','in',function($query) use($proxy) {
                            $query->table('relationship')->where('top',$proxy['uid'])->where('floor',3)->field('userid')->select();
                        }];
                        $whm[] = ['user_id','in',function($query) use($proxy) {
                            $query->table('relationship')->where('top',$proxy['uid'])->where('floor',3)->field('userid')->select();
                        }];
                    }else{
                        $where[] = ['user_id','in',function($query) use($proxy) {
                            $query->table('relationship')->where('prev',$proxy['uid'])->where('floor',3)->field('userid')->select();
                        }];
                        $whm[] = ['user_id','in',function($query) use($proxy) {
                            $query->table('relationship')->where('top',$proxy['uid'])->where('floor',3)->field('userid')->select();
                        }];
                    }
                }
            }
         //   dump($whm);
            $zhancheng = SystemConfig::where('name','zhancheng')->find()['value'];
             $data = ApiBetting::order('id', 'desc')->where($where)->paginate($paginate,false,$pageParam);
            $tj = ['cell_score'=>0,'all_bet'=>0,'profit'=>0];
            foreach ($data as $k=>$v){
                $tj['cell_score'] +=$v['cell_score'];
                $tj['all_bet'] +=$v['all_bet'];
                $tj['profit'] +=  $v['profit']? $v['profit'] :0;;
            }
            $api_cong =ApiBetting::where($whm)->field('sum(profit) as money')->find()['money'];
//            dump($api_cong);
            if($api_cong<0){
                $api_cong =$api_cong- $api_cong*$zhancheng/100;
            }
            $qb =',输赢统计（默认今天）:'. ($api_cong?$api_cong:0);
            $tj = '本页总投注统计：'.$tj['cell_score'].',本页有效投注统计：'.$tj['all_bet'].$qb;
            $data->append(['Operator','GameType']);
            return json([$data,$tj]);
        }else{
            return $this->fetch();
        }
    }

    /**
     * @param string $name 用户名
     * @param int $type 运行商
     * @return mixed
     */
    public function upAndDown($search=0,$name='',$type_fen=-1,$start_time='',$end_time='') //现金网上下分
    {
        $paginate = 15;
        $pageParam = [];
        if (Request::method() == 'POST'){
            $where[] = ['type','in',[3,4]];
            if ($search ==1){
                if ($start_time){
                    $where[] = ['create_time','>',strtotime($start_time)];
                }
                if ($end_time){
                    $where[] = ['create_time','<',strtotime($end_time)+24*60*60];
                }
                if ($name){
                    $id = User::where('username',$name)->find()['id'];
                    $where[] = ['user_id','=',$id];
                }
                if ($type_fen!=-1){
                    $where[] = ['type','=',$type_fen];
                }
            }
            $audit = CapitalAudit::where($where)->order('state ASC,id DESC')->paginate($paginate,false,$pageParam);
            $audit->append(['UserName','Nomoney']);
            return json($audit);
        }else{
            return $this->fetch();
        }
    }
    public function index(){ //代理商显示
        $tai = db::table('api_config')->field('id,name,switch,sort')->select();
        $this->assign('tai',$tai);
        return $this->fetch();
    }
    public function huifu($data_id,$money,$user_id){  //棋牌上分下分失败 恢复
        $ss = User::where('id',$user_id)->find()['money'];
        $rs = User::where('id',$user_id)->update(['money' => $ss+$money]);
        if ($rs){
            Db::table('capital_audit')->where('id',$data_id)->update(['state'=>1]);
            $give_config['uid'] =$user_id;
            $give_config['type'] = 20;
            $give_config['money'] = $money;
            $give_config['explain'] = '上分或下分失败，金额恢复';
            $rsw  = moneyAction($give_config);
            if ($rsw){
                return json(['error' => 1, 'msg' => '操作成功']);
            }else{
                return json(['error' => -1, 'msg' => '操作失败']);
            }
        }else{
            return json(['error' => -1, 'msg' => '操作失败']);
        }
    }

}