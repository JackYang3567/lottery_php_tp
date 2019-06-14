<?php
namespace app\home\controller;
use think\Db;
use app\home\model\SystemConfig;
class Chartroom extends Common
{
    public $user = [];
    public $page = [];

    //构造函数
    public function _initialize()
    {
        $data = $this->checkLogin();
        if ($data['code']) {
            $this->user = $data['data'];
        } else {
            $this->error('您还没有登陆');
        }
    }

    //获取聊天室信息
    public function getChartRoom()
    {
        $return = [
            'type' => false,
            'data' => []
        ];
        $user = [
            'id' => $this->user['id'],//角色ID
            'photo' => $this->user['photo'],//角色头像
            'chart_id' => $this->user['chart_id']//角色所属聊天室ID  格式：string
        ];
        $map['chart_id'] = ['chart_id', 'in', $user['chart_id']];
        $data = Db::table('chart')->where($map)->select();
        if ($data != "" || $data != null || !empty($data) || isset($data)) {
            $return['type'] = true;
            //聊天室去重
            $a = [];
            foreach ($data as $key => $value) {
                $a [] = $value['chart_id'];
            }
            $a = array_unique($a);
            $a = array_flip($a);
            foreach ($a as $v) {
                $return['data'] [] = $data[$v];
            }
        }

        //获取私聊信息
        foreach ($return['data'] as $k=>$v){
            if($v['type'] < 0){
                $arr = explode(',',$v['member_id']);
                for($i = 0;$i < count($arr);$i++){
                    if($arr[$i]  == $this->user['id']){
                        unset($arr[$i]);

                    }
                    $arr = array_values($arr);
                }
                $information = Db::table('user')->field('username,photo')->where('id',$arr[0])->find();
                if(strlen($information['username']) >= 3){
                    $return['data'][$k]['name']= substr($information['username'],0,2)."*";
                }
                $return['data'][$k]['photo'] = $information['photo'];
            }
        }
        return $return;
    }

    //根据 聊天室号获取聊天室的聊天信息 和 聊天室是否开启
    public function getChartcontent()
    {
        $return_data = [
            'code' => 0,
            'msg' => 'nothing'
        ];
        $a = input('param.');
        $this->page = $a['page'];
        $content = Db::table('chart_content')->where(['chart_id' => $this->page])->limit(20)->order('Publication DESC')->select(); //查询最近的20条聊天记录
        $set = Db::table('system_config')->field('value')->where(['name' => 'chat_config'])->find();//查询聊天室是否开启
        $return_data['set'] = $set ? json_decode($set['value'], true) : null;

        if ($content) {
            foreach ($content as $k => $v){
                if(strlen($v['name']) > 3){
                    $content[$k]['name']= substr($v['name'],0,3)."*";
                }
            }

            $return_data['code'] = 1;
            $return_data['msg'] = 'success';
            $return_data['data'] = [
                'list' => array_reverse($content),
            ];
        }
        return $return_data;
    }

    //加入聊天室 and 加入私聊
    public function join_chart()
    {
        $return = [
            'type' => false,
            'Tips' => "",
            'chart_id' => ""
        ];

        $a = input('param.');

        $this->page = $a['page'];

        if(strlen($this->page) >= 10){
            $data = Db::table('chart')->where('chart_id', $this->page)->select();//查出所属聊天室存不存在

            $chart_information = Db::table('chart')->field('Number_of_members,Grade,member_id')->where('chart_id', $this->page)->find();//此群的等级和人数上限

            $UpperLimit = Db::table('chart_config')->field('UpperLimit')->where('vip', $chart_information['Grade'])->find();//根据群等级查询出的人数上限

            if ($data == "" || $data == null || empty($data) || !isset($data)) {
                $return['Tips'] = "此聊天室不存在";
            } else {
                if ($chart_information['Number_of_members'] > $UpperLimit['UpperLimit']) {
                    $return['Tips'] = "此聊天室人数已达上限";
                } else {
                    $arr = explode(",", $this->user['chart_id']);//此ID所属聊天室
                    $member = explode(',',$chart_information['member_id']); //此聊天室所属ID
                    $num = 0;
                    if ((!in_array($this->page, $arr))){
                        $arr [] = $this->page;
                        $arr = implode(',', $arr);
                        $a = Db::table('user')->where('id', $this->user['id'])->update(['chart_id' => $arr]);
                        if($a){
                            $num++;
                        }
                    }
                    if((!in_array($this->user['id'], $member))){
                        $member []=  $this->user['id'];
                        $member = implode(',', $member);
                        $b = Db::table('chart')->where('chart_id', $this->page)->update(
                            ['member_id' => $member,'Number_of_members'=>($chart_information['Number_of_members']+1)]
                        );
                        if($b){
                            $num++;
                        }
                    }
                    if ($num > 0) {
                        $return['type'] = true;
                        $return['Tips'] = "已加入此聊天室";
                        $return['chart_id'] = $this->page;
                    }
                }
            }
        }else{
            $data = Db::table('user')->field('id,username,photo,chart_id')->where('id', $this->page)->find();//查出此用户在不在

            if($data){
                $max = $this->user['id'];
                $min = $data['id'];
                if($max < $min){
                    $switch = $max;
                    $max = $min;
                    $min = $switch;
                }
                $p = $min.$max; // 生成群聊ID。
                $chart_id = Db::table('chart')->where('chart_id',$p)->select();

                if(!$chart_id){
                    if($data['id'] != $this->user['id']){
                        $num = 0;
                        $arr = [
                            'chart_id'=>$p,
                            'name'=>$data['username']."与".$this->user['username']."的私人聊天室",
                            'Administrator_ID'=>null,
                            'Creation_time' => date("Y-m-d H:i:s"),
                            'photo' =>$data['photo'],
                            'Spokesman_id' => null,
                            'Number_of_members' => 2,
                            'Grade' => null,
                            'member_id'=>$data['id'].",".$this->user['id']
                        ];
                            $a = Db::table('chart')->insert($arr);
                            if($a){
                                $num = 1;
                        }

                        $m = explode(",", $data['chart_id']);
                        if(!in_array($arr['chart_id'],$m)){
                            $m []=$arr['chart_id'];
                            $m = implode(',', $m);
                            $b = Db::table('user')->where('id',$data['id'])->update(['chart_id' => $m]);
                            if($b){
                                $num = 2;
                            }
                        }
                        $n =  explode(",", $this->user['chart_id']);
                        if(!in_array($arr['chart_id'],$n)){
                            $n []=$arr['chart_id'];
                            $n = implode(',', $n);
                            $c = Db::table('user')->where('id',$this->user['id'])->update(['chart_id' => $n]);
                            if($c){
                                $num = 3;
                            }
                        }
                        if($num > 0){
                            $return['type'] = true;
                            $return['Tips'] = "已开启私人聊天";
                            $return['chart_id'] = $arr['chart_id'];
                        }
                    }else{
                        $return['Tips'] = "不可与自己建立私聊";
                    }
                }else{
                    $return['Tips'] = "";
                    $return['type'] = true;
                    $return['chart_id'] = $p;
                }
            }else{
                $return['Tips'] = "此ID号不存在";
            }
        }
        return $return;
    }

    //创建群聊
    public function initiate_chat()
    {
        $return = [
            'type' => false,
            'Tips' => "不知名错误",
            'chart_id' => '',
        ];
        $user_id = $this->user['id'];
        $vip = gradeJudgement($user_id);//发起人等级
        $chart_config = Db::table('chart_config')->where('Group_hierarchy', $vip)->select();//根据发起人的等级获取聊天室配置
        if ($chart_config) {
            $rooms = Db::table('chart')->field('Spokesman_id,chart_id')->where('Spokesman_id', $user_id)->select();//此ID创建的聊天室数量
            if (count($rooms) >= $chart_config[0]['rooms']) {
                $return['Tips'] = "已达创建聊天室上限";
            } else {
                $add = [
                    'chart_id' => strtotime(date("Y-m-d"), time()) + rand(1000, 9999),
                    'name' => $this->user['username'] . "的聊天室",
                    'Administrator_ID' => $this->user['id'],
                    'Creation_time' => date("Y-m-d H:i:s"),
                    'photo' => $chart_config[0]['default_img'],
                    'Spokesman_id' => $this->user['id'],
                    'Number_of_members' => 1,
                    'Grade' => $vip,
                    'member_id'=>$this->user['id'],
                    'type'=>1,
                    'Notice'=>"长按可进入群规置"
                ];
                $data = Db::table('chart')->insert($add);
                if ($data) {
                    $a = Db::table('user')->field('chart_id')->where('id', $this->user['id'])->find();
                    $a['chart_id'] .= "," . $add['chart_id'];
                    Db::table('user')->where('id', $this->user['id'])->update(['chart_id' => $a['chart_id']]);
                    $return['Tips'] = "创建成功";
                    $return['type'] = true;
                    $return['chart_id'] = $add['chart_id'];
                } else {
                    $return['Tips'] = "创建失败，请重试";
                }
            }
        } else {
            $return['Tips'] = "你的等级不够创建群聊";
        }


        return $return;
    }

    //发送聊天信息
    public function Send_chat_messages()
    {
        $return_data = [
            'code' => 0,
            'msg' => '发送失败'
        ];
        $information = input('param.');
        $content = $information['content']['content']; //聊天内容
        $chart_id = $information['chart_id'];//聊天室号


        $chart_config = SystemConfig::get(32);

        $chart_config->value = json_decode($chart_config->value, true);

        if ($chart_config->value['is_open'] == 1) {
            if (empty($content)) {
                $return_data['msg'] = '请输入您要发送的信息';
                return $return_data;
            } else {
                $um = Db::table('accumulation')->where([['user_id', '=', $this->user['id'], ['in_money|online_money', '>', 0]]])->find();
                if ($this->user['money'] == 0 || empty($um)) {
                    $return_data['code'] = -1;
                    $return_data['msg'] = '只有充值的会员才能发送信息,请先充值';
                } else {
                    $arr = [
                        'chart_id' => $chart_id,
                        'content' => $content,
                        'Spokesman' => $this->user['id'],
                        'Publication' => date("Y-m-d H:i:s"),
                        'photo' => $this->user['photo'],
                        'name' => $this->user['username']
                    ];
                    $data = Db::table('chart_content')->insert($arr);
                    if($data){
                        $return_data['msg']="发送成功";
                        $return_data['code']= "1";
                    }
                }
            }
        } else{
            $return_data['msg'] = '聊天室禁止聊天';
        }
        return $return_data;
    }

    //聊天室发红包
    public function send_envelopes(){
        $data = input('param.');

        $red_number = (int)$data['red_number']; //单个红包领取人数
        $red_monney = (float)$data['red_money']; //红包金额
        $red_type = $data['red_type'];//红包类型：1（普通红包） 0 （随机红包）
        $red_Blessings = $data['red_Blessings']; //红包祝福语
        $chart_id = $data['chart_id'];//聊天室ID
        $user_id = $this->user['id'];//红包发送人ID
        $user_photo = $this->user['photo'];//红包发送人头像
        $user_name = $this->user['username'];//红包发送人名称
        
        $return = [
            'Tips'=>["红包发送失败"],
            'type'=>false,
        ];
        $arr = [];

        $num = 1;
        //红包配置
        $red_config = Db::table('red_config')->field('max_money,min_money,max_num,min_num')->where('id',1)->find();
        //自身金额
        $user_money = Db::table('user')->field('money')->where('id',$user_id)->find();

        //对红包的判断
        if($red_number > $red_config['max_num']){
            $return['Tips'] []= "单个红包最大领取数量为".$red_config['max_num'];
            $num++;
        }else if($red_number < $red_config['min_num']){
            $return['Tips'] []= "单个红包最小领取数量为".$red_config['max_num'];
            $num++;
        }
        if($red_monney >$red_config['max_money']){
            $return['Tips'] []= "单个红包最大金额为".$red_config['max_money'];
            $num++;
        }else if($red_monney < $red_config['min_money']){
            $return['Tips'] []= "单个红包最小金额为".$red_config['min_money'];
            $num++;
        }
        if($red_monney > $user_money['money']){
            $return['Tips'] []= "你的余额为".$user_money;
            $num++;
        }

        if($num == 1){
            $arr = [
                'red_id'=>time()+rand(1000,9999),
                'money'=>$red_monney,
                'num'=>$red_number,
                'surplus_money'=>$red_monney,
                'surplus_num'=>$red_number,
                'type'=>$red_type,
                'Sending_time'=>date("Y-m-d H:i:s",time()),
                'end_time'=>date("Y-m-d H:i:s",strtotime("+1 day")),
                'user_id'=>$user_id,
                'user_photo'=>$user_photo,
                'user_name'=>$user_name,
                'Blessings'=>$red_Blessings,
            ];
            $a = Db::table('red_envelopes')->insert($arr);//存入红包信息表
            if($a){
                $chart_content = [
                    'chart_id'=>$chart_id,
                    'content'=>$this->red_style($red_Blessings,$arr['red_id'],$data['red_type']),
                    'Spokesman'=>$user_id,
                    'Publication'=>date("Y-m-d H:i:s",time()),
                    'photo'=>$user_photo,
                    'name'=>$user_name,
                    'type'=>3,
                    'address'=>$arr['red_id']
                ];
                $b = Db::table('chart_content')->insert($chart_content);
                if($b){
                    $action = moneyAction([
                        'uid' => $user_id,
                        'type' =>21,
                        'money' =>$red_monney,
                        'explain'=>"发送红包",
                    ]);
                    if ($action['code'] == 1){
                        $return['Tips'] = "红包发送成功";
                        $return['type'] = true;
                    }
                }
            }
        }
        return $return;
    }

    //红包样式与地址
    //$red_Blessings => 红包祝福语
    //$red_id => 红包地址
    public function red_style($red_Blessings,$type){
        if($type){
            $a = "普通红包";
        }else{
            $a = "拼手气红包";
        }
//        $content  = "<a href = '#/my/chart/receivRed/$red_id'>";
//        $content  = "<div >";
        $content = "<div class = 'bag'>";
        $content .=     "<div class='header'><img src = './api/static/images/hb.png'>$red_Blessings</div>";
        $content .=     "<div class='stick'>$a</div>";
        $content .= "</div>";
//        $content .='</div>';
        return $content;
    }


    //获取红包信息 并且查询自己是否领取过，并且查询红包已过期,并且领取红包
    public function Get_red_envelopes(){
        $red_id  = input('red_id'); //红包ID
        $return = [
            'data'=> "",
            'type'=>false,
            'Tips'=>"查询错误"
        ];

        $data =Db::table('red_envelopes')->where('red_id',$red_id)->select()[0]; //红包信息

        if($data){

            if(!($data['end_time']  <  date("Y-m-d H:i:s"))){
                $user_id = $this->user['id'];
                $red_Record = Db::table('red_record')
                                ->field('Receive_name,Receive_photo,Receive_money,Receive_time')
                                ->where('red_id',$red_id)
                                ->where('Receive_id',$user_id)
                                ->find();
                if(!$red_Record){
                    if($data['surplus_money'] > 0 && $data['surplus_num'] >0){
                        if($data['type']){
                            $return['money'] = (float)round($data['surplus_money']/$data['surplus_num'],2);//普通红包领取方式
                        }else{
                            //随机红包领取方式
                            if($data['surplus_num'] != 1){
                                $m = rand(0, $data['surplus_money']);
                                $return['money'] = (float)round($m,2);
                            }else{
                                $return['money'] = $data['surplus_money'];
                            }
                        }
                        $ar = [
                            'surplus_money'=>((float)$data['surplus_money'] -$return['money']),
                            'surplus_num'=> ($data['surplus_num']-1)
                        ];
                        $arr = [
                            'red_id'=>$red_id,
                            'Receive_id'=>$this->user['id'],
                            'Receive_name'=>$this->user['username'],
                            'Receive_photo'=>$this->user['photo'],
                            'Receive_money'=>$return['money'],
                            'Receive_time'=>date("Y-m-d H:i:s",time())
                        ];

                        $a = Db::table('red_envelopes')->where('red_id',$red_id)->update($ar);
                        $b = Db::table('red_record')->insert($arr);

                        if($a && $b){
                            $action = moneyAction([
                                'uid' => $user_id,
                                'type' =>16,
                                'money' =>$return['money'],
                                'explain'=>"领取红包",
                            ]);

                            if($action){
                                $return['data'] = [
                                    'Receive_money'=>$return['money'],
                                    'Receive_time'=>date("Y-m-d H:i:s",time()),
                                    'Receive_name'=>$this->user['username'],
                                    'Receive_photo'=>$this->user['photo']
                                ];
                                $return['type'] = true;
                                $return['Tips'] = "成功领取：".$return['money']."元";
                            }
                        }else{
                            $return['Tips']="出现不知名错误";
                        }
                    }else{
                        $return['Tips'] = "手慢了，此红包已经被抢光了";
                    }
                }else{
                    $return['Tips'] = "这个红包你已经领取过";
                }
            }else{
                $return['Tips'] = "已过红包领取时间";
            }
        }else{
            $return['Tips'] = "没有这个红包";
        }
        return $return;
    }


    //预测->判断是否为群主
    public function Heel_throw(){
        $return = [
            'Tips'=>"",
            'type'=>false,
            'data'=>""
        ];

        $user_id = $this->user['id'];

        //查出所建立的聊天室
        $a = Db::table('chart')->where('Spokesman_id',$user_id)->select();

        if($a){
            $return['type'] = true;
            $return['data'] = $a;
        }else{
            $return['Tips'] = "您还没有建立聊天室";
        }
        return $return;
    }


    //发布跟投信息
    public function release_Heel_throw(){

        $return = [
            'Tips'=>"发布失败",
            'type'=>false,
            'data'=>""
        ];
        $data = input('param.');
        $basic =json_decode($data['basic'],true);

        foreach ($basic as $k =>$v){
            $expect = $v["expect_s"][0];
            break;
        }

       $str[0] = [
           'id'=>0,
           'user_id'=>$this->user['id'],
           "content"=>$data['betting'],
           "money"=>$basic['money'],
           "expect"=>$expect,
           "type"=>$data['type'],
           "win"=>0,
           "state"=>0,
           "other"=>null,
           "category"=>0,
           "explain"=>null,
           "create_time"=>time(),

       ];

       $str = bettingFormat($str, true)[0];

        $content = $this->Heel_throw_style($str['lottery_name'],$str['content'],$str['expect'],$str['money']);

        $arr = [
            'chart_id'=>$data['chart_id'],
            'content'=>$content,
            'Spokesman'=>$str['user_id'],
            'Publication'=>date("Y-m-d H:i:s",time()),
            'photo'=>$this->user['photo'],
            'name'=>$this->user['username'],
            'type'=>2,
            'address'=>$str['type'].'|'.$data['betting'].'|'.$data['basic']
        ];
        $a = Db::table('chart_content')->insert($arr);
        if($a){
            $return['type'] = true;
            $return['Tips'] = "发布成功";
        }

        return $return;
    }

    //跟投发言样式以及内容的拼写
    //$lottery_name => 彩种名称
    //$content => 具体下注内容
    //$expect = 期号
    //$type =>玩法
    //$contents => 下注编码
    public function Heel_throw_style($lottery_name,$content,$expect,$money){
//        $str = "<a href = '#/my/chart/throw/$type/$contents/$basic'>";
        $str = "<div class='message'>";
        $str .=     "<div class = 'info'> <p>$lottery_name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;第$expect 期</p>";
//        $str .=     "<p></p>";
        $str .=     "<p>$content</p> </div>";
        $str .=     "<p>每注金额：￥$money</p>";
        $str .= "</div>";
//        $str .= "</a>";
        return $str;
    }



    //删除,退出，解除聊天
    public function unset_chart(){
        //聊天室号
        $chart_id = input('param.')['chart_id'];
        $admin_id = input('param.')['admin_id'];
        $return = [
            'type'=>false,
            'Tips'=>"",
            'data'=>""
        ];
        //判定是私聊还是群聊
        if((strlen($chart_id) >= 10)){ //群聊
            $Spokesman_id = Db::table('chart')->field('Spokesman_id')->where('chart_id',$chart_id)->find();
            if($this->user['id'] == $Spokesman_id['Spokesman_id'] || $admin_id == 0){//群主    0 是管理员
                $c_m_id = Db::table('chart')->field('member_id')->where('chart_id',$chart_id)->find()['member_id'];
                if($c_m_id != "" || $c_m_id != null){
                    $c_m_id = $c_m_id = explode(',', $c_m_id);
                    $num = 0;
                    foreach ($c_m_id as $v){
                        $u_c_id = Db::table('user')->field('chart_id')->where('id',$v)->find()['chart_id']; //user表内的chart_id 字段
                        $u_c_id = explode(',', $u_c_id);
                        $counter = count($u_c_id);
                        for ($i = 0;$i<$counter;$i++){
                            if($u_c_id[$i] == "" || $u_c_id[$i] == null || $u_c_id[$i] == $chart_id){
                                unset($u_c_id[$i]);
                            }
                        }
                        $u_c_id = implode(',', $u_c_id);
                        $u_c_id = Db::table('user')->where('id',$v)->update(['chart_id'=>$u_c_id]);
                        if($u_c_id){
                            $num++;
                        }
                    }
                    if($num > 0){
                        Db::table('chart')->where('chart_id',$chart_id)->delete();
                        $return['Tips']="群已解散";
                        $return['type']=true;
                    }
                }else{
                    $a = Db::table('chart')->where('chart_id',$chart_id)->delete();
                    if($a){
                        $return['type'] = true;
                        $return['Tips'] = "本群已解散";
                    }
                }
            }else{//群员
                $u_c_id = Db::table('user')->field('chart_id')->where('id',$this->user['id'])->find()['chart_id'];//此ID所属聊天室
                $u_c_id = explode(',', $u_c_id);
                $num =count($u_c_id);
                for($i = 0;$i<$num;$i++){
                    if($u_c_id[$i] == "" || $u_c_id[$i] == null || $u_c_id[$i] == $chart_id){
                        unset($u_c_id[$i]);
                    }
                }
                $u_c_id = implode(',', $u_c_id);

                $y = Db::table('user')->where('id',$this->user['id'])->update(['chart_id' => $u_c_id]);

//                if($y){ dump('个人chart_id 删除成功');}

                $c_m_id = Db::table('chart')->field('member_id,Number_of_members')->where('chart_id',$chart_id)->find();//此聊天室所属ID
                $c_m_id['member_id'] = explode(',', $c_m_id['member_id']);
                $n =count($c_m_id['member_id']);
                for($i = 0;$i<$n;$i++){
                    if($c_m_id['member_id'][$i] == "" || $c_m_id['member_id'][$i] == null || $c_m_id['member_id'][$i] == $this->user['id']){
                        unset($c_m_id['member_id'][$i]);
                    }
                }
                $c_m_id['member_id'] = implode(',', $c_m_id['member_id']);
                $x = Db::table('chart')->where('chart_id',$chart_id)->update(['member_id' => $c_m_id['member_id'],'Number_of_members'=>($c_m_id['Number_of_members']-1)]);

//                if($x){ dump('聊天室 member_id 删除成功');}


                if($y){
                    $return['type']=true;
                    $return['Tips']="群聊退出成功";
                }
            }
        }else{ //私聊
            $member_id = Db::table('chart')->field('member_id')->where('chart_id',$chart_id)->find()['member_id'];
            $member_id = explode(",",$member_id);
            $num = 0;
            foreach ($member_id as $v){
                $u_c_id = Db::table('user')->field('chart_id')->where('id',$v)->find()['chart_id']; //用户表内的chart_id 字段
                $u_c_id = explode(',', $u_c_id);
                $counter = count($u_c_id);
                for ($i = 0;$i<$counter;$i++){
                    if($u_c_id[$i] == "" || $u_c_id[$i] == null || $u_c_id[$i] == $chart_id){
                        unset($u_c_id[$i]);
                    }
                }
                $u_c_id = implode(',', $u_c_id);
                $u_c_id = Db::table('user')->where('id',$v)->update(['chart_id'=>$u_c_id]);
                if($u_c_id){
                    $num++;
                }
            }
            if($num > 0){
                Db::table('chart')->where('chart_id',$chart_id)->delete();
                $return['Tips']="私聊删除成功";
                $return['type']=true;
            }
        }

        return $return;
    }

    //图片上传
    public function up_img(){
        $base_img = input('param.')['imgdata'];//图片文件
        $chart_id = input('param.')['chart_id'];

        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base_img, $result)){
            $type = $result[2];
            $new_file = "./static/upload/";
            if(!file_exists($new_file)){
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0700);
            }
            $new_file = $new_file.time().".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base_img)))){
                $arr = [
                    'chart_id'=>$chart_id,
                    'content'=>"<img src = './api$new_file'>",
                    'Spokesman'=>$this->user['id'],
                    'Publication'=>date("Y-m-d H:i:s",time()),
                    'photo'=>$this->user['photo'],
                    'name'=>$this->user['username']
                ];
                Db::table('chart_content')->insert($arr);
            }
        }else{
            return false;
        }






    }

    //添加好友
    public function add_friend(){
        $return = [
            'Tips'=>'添加失败',
            'type'=>false,
            'data'=>''
        ];
        $friend_id =input('param.')['friend_id'];//前面传进来的好友ID
        if($friend_id != $this->user['id']){
            $friend_data = Db::table('user')->field('id')->where('id',$friend_id)->find(); //查询此ID是否存在
            if($friend_data){
                //我添加好友↓ *****************************************************************
                    $num = 0;
                    $me_arr = explode(",", $this->user['friend_id']);
                    if(!in_array($friend_id,$me_arr)){
                        $me_arr [] = $friend_id;
                        $me_arr = implode(',',$me_arr);
                        $me_add = Db::table('user')->where('id',$this->user['id'])->update(['friend_id'=>$me_arr]);
                        if($me_add){$num ++;}
                    }
                 //我添加好友↑ *****************************************************************

                 //好友添加我↓ *****************************************************************
                    $friend_arr = Db::table('user')->field('friend_id')->where('id',$friend_id)->find();
                    $friend_arr = explode(",", $friend_arr['friend_id']);
                    if(!in_array($this->user['id'],$friend_arr)){
                        $friend_arr [] = $this->user['id'];
                        $friend_arr = implode(',',$friend_arr);
                        $friend_arr = Db::table('user')->where('id',$friend_id)->update(['friend_id'=>$friend_arr]);
                        if($friend_arr){$num ++;}
                    }
                 //好友添加我↑ *****************************************************************
                if($num > 0){
                    $return['Tips']= "添加成功";
                }else{
                    $return['Tips']= "添加失败，可能你们已经是好友了。";
                }
            }else{
                $return['Tips']= "没有此用户";
            }
        }else{
            $return['Tips']= "不能添加自己为好友";
        }
        return $return;
    }

    //查询好友 属性
    public function query_friend(){
        $return = [
            'data'=>"",
            'type'=>false,
            'Tips'=>"你还没有好友，在上方输入ID添加好友吧"
        ];
        $friend_id = explode(",", $this->user['friend_id']);
        $ar = [];
        for ($i = 0; $i< count($friend_id);$i++){
            if($friend_id[$i] != null || $friend_id[$i] != ""){
                $arr []= Db::table('user')->field('id,username,photo')->where('id',$friend_id[$i])->find();
                $max = $this->user['id'];
                $min = $friend_id[$i];
                if($max < $min){
                    $switch = $max;
                    $max = $min;
                    $min = $switch;
                }
                $chart = $min."0".$max; // 生成私聊ID。
                $ar []= Db::table('chart_content')
                    ->field('content,name,Publication')
                    ->where('chart_id',$chart)
                    ->limit(1)
                    ->order('Publication DESC')
                    ->find();

                if($ar[$i-1] != null){
                    $arr[$i-1]['content'] = $ar[$i-1]['content'];
                    $arr[$i-1]['Publication'] = substr($ar[$i-1]['Publication'],strpos($ar[$i-1]['Publication'],' ')+1);
                    $arr[$i-1]['Spoke_name'] = $ar[$i-1]['name'];
                }else{
                    $arr[$i-1]['content'] = "";
                    $arr[$i-1]['Publication'] = "";
                    $arr[$i-1]['Spoke_name'] = "";
                }
            }
        }
        foreach ($arr as $k => $v){
            if(strlen($v['username']) >= 3){
                $arr[$k]['username']= substr($v['username'],0,2)."*";
            }
            if(strlen($v['Spoke_name']) >= 3){
                $arr[$k]['Spoke_name']= substr($v['Spoke_name'],0,2)."*";
            }
        }


        if($arr != null){
            $return['data']=$arr;
            $return['type']=true;
            $return['Tips']="查询完毕";
        }

        return $return;
    }

    // 好友私聊
    public function private_chat(){

        $friend_id = input('param.')['friend_id'];
        $return = [
            'Tips'=>"发生不知名错误",
            'type'=>false,
            'data'=>""
        ];

        $max = $this->user['id'];
        $min = $friend_id;
        if($max < $min){
            $switch = $max;
            $max = $min;
            $min = $switch;
        }
        $chart = $min."0".$max; // 生成私聊ID。
        $chart_id = Db::table('chart')->where('chart_id',$chart)->select();
        if(!$chart_id){
            $arr = [
                'chart_id'=>$chart,
                'name'=>"",
                'Administrator_ID'=>null,
                'Creation_time' => date("Y-m-d H:i:s"),
                'photo' =>"",
                'Spokesman_id' => null,
                'Number_of_members' => 2,
                'Grade' => null,
                'member_id'=>$friend_id.",".$this->user['id'],
                'type'=>-1,
            ];

            $data = Db::table('chart')->insert($arr);
            if($data){
                $return['type']= true;
                $return['data']= $chart;
                $return['Tips']= "";
            }


            $a = [$this->user['id'],$friend_id];
            foreach ($a as $v) {
                $u_c_id = Db::table('user')->field('chart_id')->where('id', $v)->find()['chart_id'];
                $u_c_id = explode(',', $u_c_id);
                if(!in_array($chart,$u_c_id)){
                    $u_c_id [] = $chart;
                    $u_c_id = implode(',', $u_c_id);
                    Db::table('user')->where('id',$v)->update(['chart_id'=>$u_c_id]);
                }
            }
        }else{
            $a = [$this->user['id'],$friend_id];
            foreach ($a as $v) {
                $u_c_id = Db::table('user')->field('chart_id')->where('id', $v)->find()['chart_id'];
                $u_c_id = explode(',', $u_c_id);
                if(!in_array($chart,$u_c_id)){
                    $u_c_id [] = $chart;
                    $u_c_id = implode(',', $u_c_id);
                    Db::table('user')->where('id',$v)->update(['chart_id'=>$u_c_id]);
                }
            }
            $return['type']= true;
            $return['data']= $chart;
            $return['Tips']= "";
        }
        return $return;
    }

    // 查询群设置
    public function query_Spokesman_id(){
        $chart_id = input('param.')['chart_id'];

        $return = [
            'type'=>false,
            'Tips'=>"你不是群主，将自动返回上衣页面",
            'data'=>""
        ];

        $data = Db::table('chart')->field('Spokesman_id,name,Notice')->where('chart_id',$chart_id)->find();

        if($data['Spokesman_id'] == $this->user['id']){
            $return['data'] = $data;
            $return['type'] = true;
            $return['Tips'] = "";
        }
        return $return;
    }
    //更改群昵称
    public function change_chart_name(){
        $chart_id = input('param.')['chart_id'];
        $chart_name = input('param.')['chart_name'];

        $return = [
            'Tips'=>"修改失败",
            'type'=>false,
        ];
        if($chart_name != null || $chart_name != "" ){
            $data = Db::table('chart')->where('chart_id',$chart_id)->update(['name'=>$chart_name]);
            if($data){
                $return['Tips']= "修改成功";
                $return['type']= true;
            }
        }
        return $return;
    }
    //更改群公告
    public function change_chart_Notice(){
        $chart_id = input('param.')['chart_id'];
        $chart_Notice = input('param.')['chart_Notice'];

        $return = [
            'Tips'=>"修改失败",
            'type'=>false,
        ];
        if($chart_Notice != null || $chart_Notice != "" ){
            $data = Db::table('chart')->where('chart_id',$chart_id)->update(['Notice'=>$chart_Notice]);
            if($data){
                $return['Tips']= "修改成功";
                $return['type']= true;
            }
        }
        return $return;
    }
    //邀请好友
    public function Invitation_friend(){
        $friend_id = input('param.')['friend_id'];
        $chart_id = input('param.')['chart_id'];
        $return = [
            'Tips'=>"邀请失败",
            'type'=>false,
            'data'=>""
        ];
        $u_c_id = Db::table('user')->field('id,chart_id')->where('id',$friend_id)->find();
        if($u_c_id){
            $num = 0;
            $u_c_id = explode(',',$u_c_id['chart_id']);
            if(!in_array($chart_id,$u_c_id)){
                $u_c_id [] = $chart_id;
                $u_c_id = implode(',',$u_c_id);
                $a = Db::table('user')->where('id',$friend_id)->update(['chart_id'=>$u_c_id]);
                if($a){
                    $num++;
                }
            }
            $c_m_id = Db::table('chart')->field('id,member_id')->where('chart_id',$chart_id)->find();
            $c_m_id = explode(',',$c_m_id['member_id']);
            if(!in_array($friend_id,$c_m_id)){
                $c_m_id [] = $friend_id;
                $c_m_id = implode(',',$c_m_id);
                $b = Db::table('chart')->where('chart_id',$chart_id)->update(['member_id'=>$c_m_id]);
                if($b){
                    $num++;
                }
            }
            if($num > 0){
                $return['Tips'] = "邀请成功";
                $return['type'] = true;
            }
        }else{
            $return['Tips'] = "没有此用户";
        }

        return $return;
    }

    //获取聊天室历史纪录
    public function get_chart_record(){
        $chart_id = input('param.')['chart_id'];
        $return_data = [
        'code' => 0,
        'msg' => '没有数据'
        ];
        $data = Db::table('chart_content')
            ->field('name,content,Publication')
            ->where('chart_id',$chart_id)
            ->order('id DESC')
            ->paginate(20)
            ->toArray();
        if($data){
            $return_data['code'] = 1;
            $return_data['msg'] = 'success';
            $return_data['data'] = $data;
        }

        return $return_data;
    }

    //联系客服
    public function Contact_Customer_Service(){

        $return = [
            'type'=>0,
            'tips'=>'不知名错误，请重试'
        ];
        $user_id = $this->user['id'];

        $Customer_service_config  = json_decode(SystemConfig::get(67)['value'],true);

        if($Customer_service_config['type']){
            $chart_id = '1'.$user_id.$Customer_service_config['id'].'0';

            $data = Db::table('chart')
                ->field('id')
                ->where('type',2)
                ->where('chart_id',$chart_id)
                ->select();

            if(count($data) < 1){
                $inert = [
                    'chart_id'=>$chart_id,
                    'Creation_time'=>date('Y-m-d H:i:s',time()),
                    'Spokesman_id' => $Customer_service_config['id'],
                    'Administrator_ID'=>$user_id,
                    'member_id' => $user_id.','.$Customer_service_config['id'],
                    'type'=>2,
                ];

                $a = Db::table('chart')->insert($inert);
                if($a){
                    $return['type'] = 1;
                    $return['tips'] = $chart_id;
                }
            }else{
                $return['type'] = 1;
                $return['tips'] = $chart_id;
            }
        }else{
            $return['tips'] = '尚未开启客服功能，青耐心等待';
        }
        return $return;


    }
}













































