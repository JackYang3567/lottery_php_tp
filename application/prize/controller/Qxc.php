<?php
namespace app\prize\controller;
use think\Db;
class Qxc extends Lottery{
    public function prize(){

        $this->actionPrize();

        $arr = [];
        // 未开奖期数
        $not = Db::table('lottery_code')
            ->where('type',29)//玩法：七星彩
            ->where('state',0)//未开奖
            ->select();
        if($not){
            //下注信息
            $data = Db::table('betting')
                ->where('expect',$not[0]['expect'])
                ->where('type',29)
                ->select();

            if($data){
                $Result = $this->GetLotteryNumber($data[0]['expect'],$data[0]['type']);   //开奖号码，以数组的形式返回   参数1 = 期数   参数2 = 开奖游戏编号
                if($Result){
                    $Result=explode(",",$Result);
                    $Result = array_reverse($Result); // 开奖号码倒叙      1,2,3  ->  3,2,1   方便定位比较
                }else{
                    return false;
                }

                foreach($data as $key => $val ){
                    $content = $val['content'];
                    $content = json_decode($content,true);

                    // 定位判断
                    if(!(empty($content['dw'])) || isset($content['dw'])){
                        $dw =  $content['dw'];

                        // 一定位判断
                        if(!(empty($dw['yidw'])) || isset($dw['yidw'])){
                            $yidw = $dw['yidw']['code'];
                            foreach ($yidw as $k => $v){
                                if(!empty($v) || isset($v)){
                                    $a = in_array($Result[$k],$v);
                                    if($a){
                                        $this->DistributePrizes($val['user_id'],$val['money'],$val['type'],"dw","yidw");
                                    }
                                }
                            }
                        }

                        //二定位判断
                        if(!(empty($dw['erdw'])) || isset($dw['erdw'])){
                            $erdw = $dw['erdw']['code'];
                            $num = 0;
                            foreach ($erdw as $k => $v){
                                if(!empty($v) || isset($v)){
                                    $a = in_array($Result[$k],$v);
                                    if($a){
                                        $num++;
                                        if($num == 2){
                                            $this->DistributePrizes($val['user_id'],$val['money'],$val['type'],"dw","erdw");
                                            $num = 0;
                                        }
                                    }
                                }
                            }
                        }

                        //三定位判断
                        if(!(empty($dw['sandw'])) || isset($dw['sandw'])){
                            $sandw = $dw['sandw']['code'];
                            $num = 0;
                            foreach ($sandw as $k => $v){
                                if(!empty($v) || isset($v)){
                                    $a = in_array($Result[$k],$v);
                                    if($a){
                                        $num++;
                                        if($num == 3){
                                            $this->DistributePrizes($val['user_id'],$val['money'],$val['type'],"dw","sandw");
                                            $num = 0;
                                        }
                                    }
                                }
                            }
                        }

                        //四定位判断
                        if(!(empty($dw['sidw'])) || isset($dw['sidw'])){
                            $sidw = $dw['sidw']['code'];
                            $num = 0;
                            foreach ($sidw as $k => $v){
                                if(!empty($v) || isset($v)){
                                    $a = in_array($Result[$k],$v);
                                    if($a){
                                        $num++;
                                        if($num == 4){
                                            $this->DistributePrizes($val['user_id'],$val['money'],$val['type'],"dw","sidw");
                                            $num = 0;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    //字现判断
                    if(!(empty($content['zx'])) || isset($content['zx'])){
                        $zx =  $content['zx'];

                        //二字现判断
                        if(!(empty($zx['erzx'])) || isset($zx['erzx'])){
                            $erzx = $zx['erzx']['code'][0];
                            $num = 0;
                            for($i = 0;$i<count($erzx);$i++){
                                if(in_array($erzx[$i],$Result)){
                                    $num++;
                                    if($num == 2){
                                        $this->DistributePrizes($val['user_id'],$val['money'],$val['type'],"zx","erzx");
                                        $num = 0;
                                    }
                                }
                            }
                        }

                        //三字现判断
                        if(!(empty($zx['sanzx'])) || isset($zx['sanzx'])){
                            $sanzx = $zx['sanzx']['code'][0];
                            $num = 0;
                            for($i = 0;$i<count($sanzx);$i++){
                                if(in_array($sanzx[$i],$Result)){
                                    $num++;
                                    if($num == 3){
                                        $this->DistributePrizes($val['user_id'],$val['money'],$val['type'],"zx","erzx");
                                        $num = 0;
                                    }
                                }
                            }
                        }


                    }
                }

                //更改开奖状态。
                 Db::table('lottery_code')
                    ->where('type',29)//玩法：七星彩
                    ->where('expect',$not[0]['expect'])//期数
                    ->update(['state' => '1']);




            }else{
                $arr['msg'] = "没有人在本期下注";
            }
        }else{
            $arr['msg'] = "不存在未开奖的期数";
        }
    }


    public function GetLotteryNumber($expect,$type){   //获取开奖号码    $expect = 期数    $type = 游戏编码
        $data = Db::table('lottery_code')
            ->where('type',$type)
            ->where('expect',$expect)
            ->column('content');
        if($data){
            return $data[0];
        }else{
            return false;
        }
    }



    //$user_id;  用户ID
    //$money;    下注金额
    //$type;     彩种编号
    //$class     大类玩法          如：字现  or   定位
    //$ification 具体玩法          如：一字定位（yzdw）
    public function DistributePrizes($user_id,$money,$type,$class,$ification){  //派奖方法
        $odds = (float)json_decode(Db::table('lottery_config')->where('type',$type)->column('basic_config')[0],true)[$class]['items'][$ification]['odds'];//赔率
        $user_money = ((float)Db::table('user')->where('id',$user_id)->column('money')[0]) + ($odds * $money);//结算过后的金额
        $a = Db::table('user')->where('id',$user_id)->update(['money'=>$user_money]);
        if($a){
            echo "派奖成功";
            return true;
        }else{
            echo "派奖失败";
            return false;
        }
    }
}