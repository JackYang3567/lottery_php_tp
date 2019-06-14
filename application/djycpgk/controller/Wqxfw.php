<?php
namespace app\djycpgk\controller;
use app\djycpgk\model\User;
use app\home\model\SystemConfig;
use think\Controller;
use think\Db;
use think\facade\Request;
function pr($var)
{
    $template = PHP_SAPI !== 'cli' ? '<pre>%s</pre>' : "\n%s\n";
    printf($template, print_r($var, true));
}
class Wqxfw extends Controller {
    //德州扑克  0709292a0000000000000000252b0000211104281d181a   type 1
    //二八杠  5326814a3  type 2
    //抢庄牛牛  360c2c14180000000000360c2c141800000000001  type 830
    //炸金花  161c1d000000262c2d000000363c3d5  type 4
    //三公  161c1d000000262c2d000000363c3d5  type 5
    //押庄龙虎  161c0112 type 6
    //21点  02d1317,13d062a,2032703,323253d-333b|41c29|5393b type 7
    //通比牛牛  360c2c1418000000000000000000000000000000360c2c141800000000001 type 8
    //二人麻将  0203040506070203040506071111,0102030607081212121315151528,0   type 740
    //红黑大战  37241409390413   type 750
    //十三水  3b2c110,04352607384,323d2d1d0d7,4;342b0b1,2336062a1a2,22123929096,1;083a3c0,33252731011,131516171c5,2;1112131415161718191a1b1c1d263;0   type 630
    //通比牛牛 360c2c1418000000000000000000000000000000360c2c141800000000001 type 870
    //极速炸金花 161c1d000000262c2d000000363c3d161c1d000000161c1d0000005 type 230
    //抢庄牌九 161c1c000000262c2c000000363c3c5 type 730
    //百人牛牛 12a3a09181522528273d3431b2b1a0706435083739125110d140322020304 type 930
    //森林舞会 5RB08YC16 type 920
    //射龙门  3611320 type 390
    //百家乐  2a23332a1c0441 type 910
    //幸运五张 3611323d093611323d090 type 380
    //斗地主 3611323d092505041c0b222d2414390c29420a16061901151a313a0d2b08272a02073537341726182c38231333033b431d3c1b2112281  type 610
    public function cardvalue($type=830,$val='360c2c14180000000000360c2c141800000000001'){ //牌读取规则
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
        elseif ($type == 830 || $type == 890){ //抢庄牛牛
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
        elseif ($type ==220 || $type == 230) {//炸金花  和 极速炸金花
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
        elseif ($type ==860) {//三公
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
        elseif ($type ==900){ //押庄龙虎
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
        }elseif ($type == 930) {//百人牛牛
            $wq = str_split(substr($val,0,11));
            $wqq = str_split(substr($val,11,11));
            $wqw = str_split(substr($val,22,11));
            $wqe = str_split(substr($val,33,11));
            $wqr = str_split(substr($val,44,11));
            $wqt = str_split(substr($val,55));
            //pr($wqt);
            $tgbs = [$wq,$wqq,$wqw,$wqe,$wqr,$wqt];
            $kq = ['0'=>'♦','1'=>'♣','2'=>'♥','3'=>'♠'];
            //转牌的大小
            $pq = ['a'=>'10','b'=>'J','c'=>'Q','d'=>'K','1'=>'A','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9'];
            //位置定义
            $bq = ['0'=>'1','1'=>'2','2'=>'3','3'=>'4','4'=>'5','5'=>'6','6'=>'7','7'=>'8','8'=>'9','9'=>'10',];
            //天地玄黄
            $lio = ['01'=>'天','02'=>'地','03'=>'玄','04'=>'黄'];
            //
            $oop = ['1'=>'天','2'=>'地','3'=>'玄','4'=>'黄','5'=>'庄家'];
            $mmn = [];
            foreach ($tgbs as $k=>$v) {
                if ($k == '5') {
                    $ook = count($v);
                    if ($ook == 6) {
                        $ti = $v[0].$v[1];
                        $tis = $v[2].$v[3];
                        $tiw = $v[4].$v[5];
                        //$tie = $v[6].$v[7];
                        $ty = [$ti,$tis,$tiw];
                        $mmn[] = $lio[$ty[0]] . $lio[$ty[1]] . $lio[$ty[2]].'赢了';
                    }elseif ( $ook == 8) {
                        $ti = $v[0].$v[1];
                        $tis = $v[2].$v[3];
                        $tiw = $v[4].$v[5];
                        $tie = $v[6].$v[7];
                        $ty = [$ti,$tis,$tiw,$tie];
                        $mmn[] = $lio[$ty[0]] . $lio[$ty[1]] . $lio[$ty[2]] .$lio[$ty[3]].'赢了';
                    }
                }elseif ($k == '4'){
                    //pr($v);
                    $mmn[] = $oop[$v[0]]. '牌为:'. $kq[$v[1]].$pq[$v[2]].$kq[$v[3]].$pq[$v[4]].$kq[$v[5]].$pq[$v[6]].$kq[$v[7]].$pq[$v[8]].$kq[$v[9]].$pq[$v[10]];
                }else{
                    $mmn[] = $oop[$v[0]]. '牌为:'. $kq[$v[1]].$pq[$v[2]].$kq[$v[3]].$pq[$v[4]].$kq[$v[5]].$pq[$v[6]].$kq[$v[7]].$pq[$v[8]].$kq[$v[9]].$pq[$v[10]];
                }
            }
            $kkl = implode(",",$mmn);
            return json($kkl);
        }elseif ($type == 920) {//森林舞会
            $llk = ['1' => '无事件', '2' => '大三元', '3' => '大四喜', '4' => '霹雳闪电', '5' => '送灯'];
            $ppl = ['R' => '红色', 'G' => '绿色', 'Y' => '黄色'];
            $rf = ['A' => '狮子', 'B' => '熊猫', 'C' => '猴子', 'D' => '兔子'];
            $wqq = substr($val, 0, 1);
            $mmn = [];
            if ($wqq == '2') {//大三元
                $wqqs = str_split(substr($val,0,1));
                $wqw = str_split(substr($val,1,1));
                $wqe = str_split(substr($val,2,1));
                $wqr = str_split(substr($val,3,2));
                $wqt = str_split(substr($val,5,1));
                $wqy = str_split(substr($val,6,1));
                $wqu = str_split(substr($val,7,2));
                $wqi = str_split(substr($val,9,1));
                $wqo = str_split(substr($val,10,1));
                $wqp = str_split(substr($val,11,2));
                $iot = [$wqqs,$wqw,$wqe,$wqr,$wqt,$wqy,$wqu,$wqi,$wqo,$wqp];
                foreach ($iot as $k =>$v) {
                    if ($k == 0) {
                        $mmn[] = $llk[$v[0]];
                    }elseif ($k == 1) {
                        $mmn[] = $ppl[$v[0]];
                    }elseif ($k == 2) {
                        $mmn[] = $rf[$v[0]];
                    }elseif ($k == 3) {
                        if ($v[0] == 0) {
                            $mmn[] = $v[1].'倍';
                        }else{
                            $mmn[] = ($v[0].$v[1]).'倍';
                        }
                    }elseif ($k == 4) {
                        $mmn[] = $ppl[$v[0]];
                    }elseif ($k == 5) {
                        $mmn[] = $rf[$v[0]];
                    }elseif ($k == 6) {
                        if ($v[0] == 0) {
                            $mmn[] = $v[1].'倍';
                        }else{
                            $mmn[] = ($v[0].$v[1]).'倍';
                        }
                    }elseif ($k == 7) {
                        $mmn[] = $ppl[$v[0]];
                    }elseif ($k == 8) {
                        $mmn[] = $rf[$v[0]];
                    }elseif ($k == 9) {
                        if ($v[0] == 0) {
                            $mmn[] = $v[1].'倍';
                        }else{
                            $mmn[] = ($v[0].$v[1]).'倍';
                        }
                    }
                }
                $kkl = implode(",",$mmn);
                return json($kkl);
            }elseif ($wqq == '3') {//大四喜
                $wqqs = str_split(substr($val,0,1));
                $wqw = str_split(substr($val,1,1));
                $wqe = str_split(substr($val,2,1));
                $wqr = str_split(substr($val,3,2));
                $wqt = str_split(substr($val,5,1));
                $wqy = str_split(substr($val,6,1));
                $wqu = str_split(substr($val,7,2));
                $wqi = str_split(substr($val,9,1));
                $wqo = str_split(substr($val,10,1));
                $wqp = str_split(substr($val,11,2));
                $wqa = str_split(substr($val,13,1));
                $wqs = str_split(substr($val,14,1));
                $wqd = str_split(substr($val,15,2));
                $iot = [$wqqs,$wqw,$wqe,$wqr,$wqt,$wqy,$wqu,$wqi,$wqo,$wqp,$wqa,$wqs,$wqd];
                foreach ($iot as $k =>$v) {
                    if ($k == 0) {
                        $mmn[] = $llk[$v[0]];
                    }elseif ($k == 1) {
                        $mmn[] = $ppl[$v[0]];
                    }elseif ($k == 2) {
                        $mmn[] = $rf[$v[0]];
                    }elseif ($k == 3) {
                        if ($v[0] == 0) {
                            $mmn[] = $v[1].'倍';
                        }else{
                            $mmn[] = ($v[0].$v[1]).'倍';
                        }
                    }elseif ($k == 4) {
                        $mmn[] = $ppl[$v[0]];
                    }elseif ($k == 5) {
                        $mmn[] = $rf[$v[0]];
                    }elseif ($k == 6) {
                        if ($v[0] == 0) {
                            $mmn[] = $v[1].'倍';
                        }else{
                            $mmn[] = ($v[0].$v[1]).'倍';
                        }
                    }elseif ($k == 7) {
                        $mmn[] = $ppl[$v[0]];
                    }elseif ($k == 8) {
                        $mmn[] = $rf[$v[0]];
                    }elseif ($k == 9) {
                        if ($v[0] == 0) {
                            $mmn[] = $v[1].'倍';
                        }else{
                            $mmn[] = ($v[0].$v[1]).'倍';
                        }
                    }elseif ($k == 10) {
                        $mmn[] = $ppl[$v[0]];
                    }elseif ($k == 11) {
                        $mmn[] = $rf[$v[0]];
                    }elseif ($k == 12) {
                        if ($v[0] == 0) {
                            $mmn[] = $v[1].'倍';
                        }else{
                            $mmn[] = ($v[0].$v[1]).'倍';
                        }
                    }
                }
                $kkl = implode(",",$mmn);
                return json($kkl);
            }elseif ($wqq == '4') {//送灯霹雳闪电

                $wqqs = str_split(substr($val,0,1));
                $wqw = str_split(substr($val,1,1));
                $wqe = str_split(substr($val,2,1));
                $wqr = str_split(substr($val,3,2));
                $iot = [$wqqs,$wqw,$wqe,$wqr];
                foreach ($iot as $k =>$v) {
                    if ($k == 0) {
                        $mmn[] = $llk[$v[0]];
                    }elseif ($k == 1) {
                        $mmn[] = $ppl[$v[0]];
                    }elseif ($k == 2) {
                        $mmn[] = $rf[$v[0]];
                    }elseif ($k == 3) {
                        if ($v[0] == 0) {
                            $mmn[] = $v[1]*'2'.'倍';
                        }else{
                            $mmn[] = ($v[0].$v[1])*'2'.'倍';
                        }
                    }
                }
                $kkl = implode(",",$mmn);
                return json($kkl);
            }elseif ($wqq == '5') {//送灯
                $wqqs = str_split(substr($val,0,1));
                $wqw = str_split(substr($val,1,1));
                $wqe = str_split(substr($val,2,1));
                $wqr = str_split(substr($val,3,2));
                $wqt = str_split(substr($val,5,1));
                $wqy = str_split(substr($val,6,1));
                $wqu = str_split(substr($val,7,2));
                $iot = [$wqqs,$wqw,$wqe,$wqr,$wqt,$wqy,$wqu];
                foreach ($iot as $k =>$v) {
                    if ($k == 0) {
                        $mmn[] = $llk[$v[0]];
                    }elseif ($k == 1) {
                        $mmn[] = $ppl[$v[0]];
                    }elseif ($k == 2) {
                        $mmn[] = $rf[$v[0]];
                    }elseif ($k == 3) {
                        if ($v[0] == 0) {
                            $mmn[] = $v[1].'倍';
                        }else{
                            $mmn[] = ($v[0].$v[1]).'倍';
                        }

                    }elseif ($k == 4) {
                        $mmn[] = $ppl[$v[0]];
                    }elseif ($k == 5) {
                        $mmn[] = $rf[$v[0]];
                    }elseif ($k == 6) {
                        if ($v[0] == 0) {
                            $mmn[] = $v[1].'倍';
                        }else{
                            $mmn[] = ($v[0].$v[1]).'倍';
                        }
                    }
                }
                $kkl = implode(",",$mmn);
                return json($kkl);
            }elseif ($wqq == '1') {//无事件

                $wqqs = str_split(substr($val,0,1));
                $wqw = str_split(substr($val,1,1));
                $wqe = str_split(substr($val,2,1));
                $wqr = str_split(substr($val,3,2));
                $iot = [$wqqs,$wqw,$wqe,$wqr];
                foreach ($iot as $k =>$v) {
                    if ($k == 0) {
                        $mmn[] = $llk[$v[0]];
                    }elseif ($k == 1) {
                        $mmn[] = $ppl[$v[0]];
                    }elseif ($k == 2) {
                        $mmn[] = $rf[$v[0]];
                    }elseif ($k == 3) {
                        if ($v[0] == 0) {
                            $mmn[] = $v[1].'倍';
                        }else{
                            $mmn[] = ($v[0].$v[1]).'倍';
                        }

                    }
                }
                $kkl = implode(",",$mmn);
                return json($kkl);
            }
        }elseif ($type == 390) {//射龙门
            $kuiq = ['0'=>'♦','1'=>'♣','2'=>'♥','3'=>'♠'];
            $popq = ['a'=>'10','b'=>'J','c'=>'Q','d'=>'K','1'=>'A','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9'];
            $ggh  = count(str_split($val));
            $mmn = [];
            if ($ggh == '7') {
                $wrss = str_split(substr($val,0,6));
                $mmn[] = '手牌为:'.$kuiq[$wrss[0]] .$popq[$wrss[1]].$kuiq[$wrss[2]] .$popq[$wrss[3]].$kuiq[$wrss[4]] .$popq[$wrss[5]];
            }elseif ($ggh == '4') {
                $wrss = str_split(substr($val,0,4));
                $mmn[] = '手牌为:'.$kuiq[$wrss[0]] .$popq[$wrss[1]].$kuiq[$wrss[2]] .$popq[$wrss[3]];
            }elseif ($ggh == '5') {
                $wrss = str_split(substr($val,0,4));
                $mmn[] = '手牌为:'.$kuiq[$wrss[0]] .$popq[$wrss[1]].$kuiq[$wrss[2]] .$popq[$wrss[3]];
            }

            return json($mmn[0]);
        }elseif ($type == 910) {//百家乐
            $kuiq = ['0'=>'♦','1'=>'♣','2'=>'♥','3'=>'♠'];
            //转牌的大小
            $popq = ['a'=>'10','b'=>'J','c'=>'Q','d'=>'K','1'=>'A','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9'];
            $uio = ['1'=>'闲胜','2'=>'庄胜','3'=>'和赢','4'=>'上庄赢','5'=>'上庄输','6'=>'庄对','7'=>'闲对','8'=>'大','9'=>'小'];
            $wrsq = str_split(substr($val,0,6));
            $wtsq = str_split(substr($val,6,6));
            $wtsrs = str_split(substr($val,12));
            $iot = [$wrsq,$wtsq,$wtsrs];
            $mmn = [];
            foreach ($iot as $k => $v) {
                if ($k == '2') {
                    $mmn[] = $uio[$v[0]] .','. $uio[$v[1]];
                }elseif ($k == '0') {
                    $mmn[] = '闲牌:'.$kuiq[$v[0]] . $popq[$v[1]].$kuiq[$v[2]] . $popq[$v[3]].$kuiq[$v[4]] . $popq[$v[5]];
                }else{
                    $mmn[] = '庄牌:'.$kuiq[$v[0]] . $popq[$v[1]].$kuiq[$v[2]] . $popq[$v[3]].$kuiq[$v[4]] . $popq[$v[5]];
                }

            }
            $kkl = implode(",",$mmn);
            return json($kkl);
        }elseif ($type == 380) {//幸运五张
            $yu = count(str_split($val));
            $kuiq = ['0'=>'♦','1'=>'♣','2'=>'♥','3'=>'♠'];
            //转牌的大小
            $popq = ['a'=>'10','b'=>'J','c'=>'Q','d'=>'K','1'=>'A','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9'];
            $wrsqs = str_split(substr($val,0,10));
            $wtsqse = str_split(substr($val,10,10));
            $iot = [$wrsqs,$wtsqse];
            $mmn = [];
            if ($yu == '21') {
                foreach ($iot as $k => $v ) {
                    if ($k == '0') {
                        $mmn[] = '换牌前:'.$kuiq[$v[0]] . $popq[$v[1]]. $kuiq[$v[2]] . $popq[$v[3]].$kuiq[$v[4]] . $popq[$v[5]].$kuiq[$v[6]] . $popq[$v[7]].$kuiq[$v[8]] . $popq[$v[9]];
                    }elseif ($k == '1'){
                        $mmn[] = '换牌后:'.$kuiq[$v[0]] . $popq[$v[1]]. $kuiq[$v[2]] . $popq[$v[3]].$kuiq[$v[4]] . $popq[$v[5]].$kuiq[$v[6]] . $popq[$v[7]].$kuiq[$v[8]] . $popq[$v[9]];

                    }
                }
            }elseif ($yu == '11') {
                foreach ($iot as $k => $v ) {
                    if ($k == '0') {
                        $mmn[] = '没有换牌:'.$kuiq[$v[0]] . $popq[$v[1]]. $kuiq[$v[2]] . $popq[$v[3]].$kuiq[$v[4]] . $popq[$v[5]].$kuiq[$v[6]] . $popq[$v[7]].$kuiq[$v[8]] . $popq[$v[9]];
                    }
                }
            }
            $kkl = implode(",",$mmn);
            return json($kkl);
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
        }
        elseif ($type==630){//十三水
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
            return json(implode('__', $val));
        }
        elseif ($type ==870){ //通比牛牛
            $zuang= '赢家是'.substr($val,-1).'号玩家'; //获取庄家
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
        elseif ($type==730){ //抢庄牌九
            //14120000151611331
            $awwrr =['12'=>'丁三','24'=>'二四','23'=>'杂五','14'=>'杂五','25'=>'杂七','34'=>'杂七','26'=>'杂八','35'=>'杂八','36'=>'杂九'
                ,'45'=>'杂九','15'=>'零霖六','16'=>'高脚七','46'=>'红头十','56'=>'斧头','22'=>'板凳','33'=>'长三','55'=>'梅牌','13'=>'鹅牌'
                ,'44'=>'人牌','11'=>'地牌','66'=>'天牌'];
            $val = str_split($val,4);
            $kaijiang = end($val); //获取最后一位
            array_pop ($val);//删除最后一位
            $mmn= [];
            foreach ($val as $k=>$v){
                $value = str_split($v,2);
                if ($value[0] == '00' && $value[1] == '00') {
                    $mmn[] = ($k+1).'位没有玩家';
                }else{
                    $mmn[] = ($k+1).'位玩家手牌位:'.$awwrr[$value[0]] . $awwrr[$value[1]];
                }
            }
            $mmn[] = $kaijiang .'号位玩家是庄';
            //pr($mmn);
            return json(implode(',', $mmn));
        }elseif ($type == 610) {//斗地主
            //3611323d092505041c0b222d2414390c29420a16061901151a313a0d2b08272a02073537341726182c38231333033b431d3c1b2112281
            $kuis = ['0'=>'♦','1'=>'♣','2'=>'♥','3'=>'♠','4'=>'王'];
            $val = str_split($val,34);
            $kaijiang = end($val);//获取最后一位
            $kk = substr($kaijiang,-1);//获取地主

            array_pop ($val);//删除最后一位
            $mmn = [];
            foreach ($val as $k=>$v) {
                $value = str_split($v,2);
                foreach ($value as $k => &$v) {
                    if($v == '00'){continue;}
                    if ($v[0] == 4){
                        if ($v[1]==2){
                            $v = '小王';
                        }else{
                            $v = "大王";
                        }
                    }else{
                        $num = $v[1];
                        $v = $hua[$v[0]].(isset($pai[$num[0][0]]) ? $pai[$num[0][0]] : $num[0][0]);
                    }
                }
                $mmn[] = implode(',',$value);
            }
            $yui = str_split(substr($kaijiang,0,8));
            $pops = ['a'=>'10','b'=>'J','c'=>'Q','d'=>'K','1'=>'A','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7','8'=>'8','9'=>'9'];
            array_pop ($yui);//删除最后一位
            $mmn[] = '地主牌为:'.$kuis[$yui[0]].$pops[$yui[1]].$kuis[$yui[2]]. $pops[$yui[3]].$kuis[$yui[4]]. $pops[$yui[5]];
            $mmn[] = $kk .'号位玩家是地主';
            return json($mmn);
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

    public function zhancheng($zhancheng){
        $ss = SystemConfig::where('name','zhancheng')->update(['value' => $zhancheng]);
        if ($ss > 0) {
            $this->success('配置成功');
        } else {
            $this->error('配置失败');
        }
    }
    public function huifu($data_id,$money,$user_id){

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
    public function yxtx($uid){
        $model = User::get($uid);
        $model->off_money = 0;
        if($model->save()){
            return json(['error' => 1, 'msg' => '操作成功']);
        }else{
            return json(['error' => -1, 'msg' => '操作失败']);
        }
    }
    public function yhtj(){ //银行添加
        $data = input('post.');
        if (!isset($data['number']) && !isset($data['user_name'])){
            return json(['error' => -1, 'msg' =>"数据不能为空"]);
        }
        if (($data['name'] == 'payalipay' || $data['name'] == 'payweixin')){
            if (count($data['qr_code'])==0){
                return json(['error' => -1, 'msg' =>"图片不能为空"]);
            }
        }else{
            if (count($data['number'])==0 && count($data['user_name'])==0){
                return json(['error' => -1, 'msg' =>"账户号或账户姓名不能为空"]);
            }
            if (count($data['number']) != count($data['user_name'])){
                return json(['error' => -1, 'msg' =>"账户号或账户姓名对不上"]);
            }
        }
        if (!empty($data['qr_code'])){
            foreach ($data['qr_code'] as $k =>&$value){
                if (strstr($value,",")){
                    $image = explode(',',$value);
                    $image = $image[1];
                }else{
                    continue;
                }
                $imageName = "25220_".date("His",time())."_".rand(1111,9999).'.png';
                $path = "./uploads/erweima";
                if (!is_dir($path)){ //判断目录是否存在 不存在就创建
                    mkdir($path,0777,true);
                }
                $valuea= $path."/". $imageName; //图片名字
                $value = "/uploads/erweima/".$imageName;

                $r = file_put_contents($valuea, base64_decode($image));//返回的是字节数
                if (!$r){
                    return json(['error' => -1, 'msg' =>"图片上传失败"]);
                }
            }

            $data['qr_code'] = json_encode( $data['qr_code']);
            $data['user_name'] = [];
        }else{
            $data['number'] = json_encode($data['number']);
            $data['user_name'] = json_encode($data['user_name']);
        }
        $rs = Db::table('bank_pay')->insert($data);
        if($rs){
            return json(['error' => 1, 'msg' => '操作成功']);
        }else{
            return json(['error' => -1, 'msg' => '操作失败']);
        }
    }
    public function yhxg(){ //银行修改
        $data = input('post.');

        if (!isset($data['number']) && !isset($data['user_name'])){
            return json(['error' => -1, 'msg' =>"数据不能为空"]);
        }
        if (($data['name'] == 'payalipay' || $data['name'] == 'payweixin')){
            if (count($data['qr_code'])==0){
                return json(['error' => -1, 'msg' =>"图片不能为空"]);
            }
        }else{
            if (count($data['number'])==0 && count($data['user_name'])==0){
                return json(['error' => -1, 'msg' =>"账户号或账户姓名不能为空"]);
            }
            if (count($data['number']) != count($data['user_name'])){
                return json(['error' => -1, 'msg' =>"账户号或账户姓名对不上"]);
            }
        }
        if (!empty($data['qr_code'])){
            foreach ($data['qr_code'] as $k =>&$value){
                if (!strstr($value,",")){
                    continue;
                }else{
                    $image = explode(',',$value);
                    $image = $image[1];
                }
                $imageName = "25220_".date("His",time())."_".rand(1111,9999).'.png';

                $path = "./uploads/erweima";
                if (!is_dir($path)){ //判断目录是否存在 不存在就创建
                    mkdir($path,0777,true);
                }
                $valuea= $path."/". $imageName; //图片名字
                $value = "/uploads/erweima/".$imageName;
                $r = file_put_contents($valuea, base64_decode($image));//返回的是字节数
                if (!$r){
                    return json(['error' => -1, 'msg' =>"图片上传失败"]);
                }
            }
            $data['qr_code'] = json_encode( $data['qr_code']);
            $data['user_name'] = [];

        }else{
            $data['number'] = json_encode($data['number']);
            $data['user_name'] = json_encode($data['user_name']);
        }
        $rs = Db::table('bank_pay')->where('id',$data['id'])->update($data);
        if($rs){
            return json(['error' => 1, 'msg' => '操作成功']);
        }else{
            return json(['error' => -1, 'msg' => '操作失败']);
        }
    }
    public function text(){
        dump(date(DATE_RFC3339)); //获取rfc3339格式时间
    }
}