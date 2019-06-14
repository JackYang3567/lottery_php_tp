<?php
namespace app\home\controller;

use think\Db;
use app\home\model\User;
use app\home\model\Betting;
use app\home\model\LotteryConfig;

class LotteryLh extends Lottery
{

    private $user = [];
    public function _initialize()
    {
      $data = $this->checkLogin();
      if($data['code']){
        $this->user = $data['data'];
      }else{
        $this->error('您还没有登陆');
      }
    }
    private $li = ['1', '1', '1', '2', '2', '2', '3', '3', '3', '4', '4', '4', '5', '5', '5', '6', '6', '7', '7', '8', '8', '9', '10', '11'];

    private $gameType = 53;
    private $gameName = '';

    /**
     * 连线规则
     * @var array
     */
    private $lineType = [
        '1' => [0, 3, 6, 9, 12],
        '2' => [1, 4, 7, 10, 13],
        '3' => [2, 5, 8, 11, 14],
        '4' => [0, 4, 8, 10, 12],
        '5' => [2, 4, 6, 10, 14],
        '6' => [0, 4, 6, 10, 12],
        '7' => [1, 3, 7, 9, 13],
        '8' => [1, 5, 7, 11, 13],
        '9' => [2, 4, 8, 10, 14],
        '10' => [1, 3, 6, 9, 13],
        '11' => [0, 4, 7, 10, 12],
        '12' => [2, 4, 7, 10, 14],
        '13' => [1, 5, 8, 11, 13],
        '14' => [0, 3, 8, 9, 12],
        '15' => [2, 5, 6, 11, 14],
        '16' => [1, 4, 6, 10, 13],
        '17' => [1, 4, 8, 10, 13],
        '18' => [0, 5, 6, 11, 12],
        '19' => [2, 3, 8, 9, 14],
        '20' => [1, 3, 7, 11, 13]
    ];

    /**
     * 奖金格式
     * @var array
     */
    private $bonus = [
        '1' => ['3' => 5, '4' => 20, '5' => 100],
        '2' => ['3' => 5, '4' => 20, '5' => 100],
        '3' => ['3' => 5, '4' => 20, '5' => 100],
        '4' => ['3' => 5, '4' => 20, '5' => 100],
        '5' => ['3' => 10, '4' => 40, '5' => 200],
        '6' => ['3' => 20, '4' => 100, '5' => 500],
        '7' => ['3' => 30, '4' => 150, '5' => 800],
        '8' => ['3' => 50, '4' => 200, '5' => 1500],
        '9' => ['3' => 75, '4' => 300, '5' => 2000],
        '10' => ['3' => 100, '4' => 400, '5' => 3000],
        '11' => ['3' => 150, '4' => 600, '5' => 4500]
    ];

    /**
     * 房间列表格式（等级=>金额）
     * @var array
     */
    private $levelMoney = [5, 20, 100];

    /**
     * 根据类型生成对应配置
     * @param mixed $type 0是水果拉霸 1是沉鱼落雁 2是忍者神龟
     * @return LotteryConfig
     */
    private function createConfig($type = 0)
    {
        if ($type == 1) {
            $this->gameType = 54;
        }
        if ($type == 2) {
            $this->gameType = 55;
        }

        $lottery_config = LotteryConfig::get($this->gameType);
        $config = $lottery_config->getConfig();
        $this->bonus = $config['bonus'];
        $this->levelMoney = $config['room'];
        $this->gameName = $lottery_config['name'];
        $this->li = $this->parseLi($config['li']);
        return $lottery_config;
    }

    private function parseLi($li_config)
    {
        $li = [];
        foreach ($li_config as $key => $value) {
            for ($i = 0; $i < $value; $i ++) {
                $li[] = $key;
            }
        }
        return $li;
    }

    private $allGame = [53, 54, 55];

    /**
     * 获取房间信息
     * @param integer $type 0是水果拉霸 1是沉鱼落雁 2是忍者神龟
     * @return array
     */
    public function getRoomList($type = 0)
    {
        $this->createConfig($type);
        return $this->levelMoney;
    }

    /**
     * 游戏初始化
     * @param integer $level 房间等级
     * @param integer $type  0是水果拉霸 1是沉鱼落雁 2是忍者神龟
     */
    public function init($level = 0, $type = 0)
    {
        $this->user = $this->checkLogin()['data'];

        $this->createConfig($type);

        if ($level >= count($this->levelMoney)) {
            return ['err' => 1];
        }

        $li = $this->li;
        $show = [];
        $res = [];
        for ($i = 1; $i <= 5; $i ++) {
            shuffle($li);
            $res['l' . $i] = implode(',', $li);
            $show = array_merge($show,  array_slice($li, 0, 3));
        }
        $count = Betting::where([
            'user_id' => $this->user['id'],
            'type' => $this->gameType
        ])->count();
        $res['show'] = implode(',', $show);
        $res['jackpot'] = 0;
        $res['todayBet'] = 0;
        $res['todayWin'] = 0;
        $res['todayCount'] = $count;
        $res['coin'] = $this->user['money'];
        $res['level'] = $level;
        $res['levelMoney'] = $this->levelMoney[$level];

        return $res;
    }

    /**
     * 开始游戏/投注
     * @param string $l1 - $l5 投注号码
     * @param string $money    游戏金额
     * @param string $type     0是水果拉霸 1是沉鱼落雁 2是忍者神龟
     * @return array
     */
    public function bet($l1 = '', $l2 = '', $l3 = '', $l4 = '', $l5 = '', $money = '', $type = 0)
    {
        $this->user = $this->checkLogin()['data'];
        $this->createConfig($type);

        $l_rand = [rand(0, 23), rand(0, 23), rand(0, 23), rand(0, 23), rand(0, 23)];

        $res = [];
        $show = [];

        for ($i = 1; $i <= 5; $i ++) {
            // 分割成数组，方便下面操作
            $ln = explode(',', ${'l' . $i});
            // 复制数组，应为排序后原数组会改变
            $li = $ln;
            // 序列化数组，对比是否为合法投注号码
            $_ln = sort($ln);

            // 如果不合法，返回错误信息
            if ($_ln != $this->li) {
                return ['err' => 1];
            }
            $new_li = $this->randLi($li);
            // 生成新的列表
            $res['l' . $i] = implode(',', $new_li);
            $show = array_merge($show,  array_slice($new_li, 0, 3));
        }

        $lines = [];
        $winMoney = 0;
        $tt = [];
        // 遍历规则
        foreach ($this->lineType as $key => $rule) {
            $passNum = 0;
            $lastNum = '';

            $t = [];
            // 匹配规则
            foreach ($rule as $item) {
                $num = $show[$item];
                if ($lastNum == '' || $num == $lastNum) {
                    $t[$item] = $num;
                    $passNum ++;
                } elseif($passNum < 3) {
                    $passNum = 1;
                }
                $lastNum = $num;
            }
            $t['pass'] = $passNum;
            $t['num'] = $lastNum;
            $tt[] = $t;

            if ($passNum >= 3) {
                $lines[] = $key;
                $winMoney += $this->bonus[$lastNum][$passNum];
            }
        }

        $useMoney = $money * 20;
        if ($this->user['money'] < $useMoney) {
            return ['code' => 0, 'msg' => '余额不足'];
        }
        $count = Betting::where([
            'user_id' => $this->user['id'],
            'type' => $this->gameType
        ])->count();
        $res['show'] = implode(',', $show);
        $res['winMoney'] = $winMoney * $money;
        $res['lines'] = implode(',', $lines);
        $res['winJackPot'] = 0.00;
        $res['jackpot'] = 0; // 奖池
        $res['todayBet'] = 0;
        $res['todayWin'] = 0;
        $res['todayCount'] = $count + 1;
        $res['success'] = 1;
        $res['test'] = $tt;

        moneyAction([
            'uid' => $this->user['id'],
            'money' => $useMoney,
            'type' => 0,
            'explain' => $this->gameName .' 开始'
        ]);

        if ($res['winMoney'] > 0) {
            moneyAction([
                'uid' =>$this->user['id'],
                'money' => $res['winMoney'],
                'type' => 3,
                'explain' => $this->gameName . ' 中奖'
            ]);
        }

        Betting::create([
            'user_id' => $this->user['id'],
            'content' => $res['show'],
            'money' => $useMoney,
            'win' => $res['winMoney'],
            'expect' => '',
            'type' => $this->gameType,
            'state' => 1,
            'create_time' => time()
        ]);

        $user = User::get($this->user['id']);
        $res['coin'] = $user->money;

        return $res;
    }

    /**
     * 随机滚动号码
     * @param array $li 原号码
     * @return array    滚动后的号码
     */
    private function randLi($li)
    {
        $length = count($li);
        $num = rand(1, $length);
        $_li = [array_slice($li, 0, $num), array_slice($li, $num)];

        return array_merge($_li[1], $_li[0]);
    }

    /**
     * 生成0~1随机小数
     * @param Int  $min
     * @param Int  $max
     * @return Float
     */
    function randFloat($min=0, $max=1){
      return $min + mt_rand()/mt_getrandmax() * ($max-$min);
    }

    /**
     * 老虎机开奖号
     * @param array 投注内容号码
     * @return array 号码奖金等
     */
    public function tiger()
    {
       $this->gameType = 56;

       //1.类型倍率一个编号 8种*2 + 1个头luck0倍 + 1个尾部luck500倍
       //2.投注内容一个编号 0-7
       //3.开奖号码一个编号 0-23 [10,10,100,50,5,3]
       //4.根据 投注内容 和 回报率 和 类型倍率 决定开奖号

       //获取全部0-23 的对应 投注|赔率
       $odds = [
                [6,10],[4,10],[0,100],[0,50],[7,5],[7,3],[5,10],
                [3,20],[3,3],[8,500],[7,5],[6,3],
                [6,10],[4,10],[1,3],[1,20],[7,5],[5,3],[5,10],
                [2,20],[2,3],[8,0],[7,5],[4,3]
               ];
       $random = rand(0,100000);
       // print_r($random);die;
       // 默认一个中奖号key21 是 未中奖
       $winK = 21;
       $bingo = [
                  [2,3],
                  [14,15],
                  [19,20],
                  [7,8],
                  [1,13,23],
                  [6,17,18],
                  [0,11,12],
                  [4,5,10,16,22]
                ];
       //在未中奖情况下,将选择比下注总金额小的的中奖号或未中奖
       $sel_win = [21];
       //获取回报率
       $ror = (int)($this->gameTigerConfig(2)['basic_config']['percent'])/100;
        //print_r($ror);die;
       //临时变量
       $list = 0;
       //获取投注内容
       $data = input('post.');
       // $allp = array_sum(array_map(function($val){return $val[1];}, $odds));
       // $allp = floor($allp/$ror);
       $allp = max($data['money']) * 500;
       foreach ($data['money'] as $k1 => $v1) {
         foreach ($bingo[$k1] as $k2 => $v2) {
           $allp += $odds[$v2][1] * ($v1 <= 0 ? 1 : $v1);
         }
       }
       // $allp = floor($allp/$ror);
       // print_r($allp);die;
       $data_sum = array_sum($data['money']);
       if($data_sum == 0){
         return ['code' => -1,'msg' => '金额错误'];
       }
       $bl = 0;
       //循环
       foreach ($odds as $k => $vo) {
         if( $k == 2 || $k == 3) {
           $percent = ((1-(($data['money'][$vo[0]] * $vo[1])/$allp))/24)*$ror;
           $percent = $percent * $ror;
         } elseif( $k == 9 ) {
           $percent = ((1-(500*max($data['money'])/$allp))/24)*$ror;
           if(($percent * 100) > 0.2){
             $percent = 0.0001;
           }
           // echo '--';
           // print_r(500*max($data['money']));
           // echo "\n";
         } elseif( $k == 21 ) {
           $percent = (1-$vo[1]/$allp)/24;
         } else {
           // $percent = ((($data['money'][$vo[0]] <= 0 ? 1 : $data['money'][$vo[0]] ) * $vo[1])/$allp);
           $percent = ((1-(($data['money'][$vo[0]] * $vo[1])/$allp))/24)*$ror;
           // $percent = (1-($vo[1]/$allp)/24)*$ror;
           // print_r( 1-(($data['money'][$vo[0]] == 0 ? 1 : $data['money'][$vo[0]]) * $vo[1])/$allp );
           // echo "\n";
         }
         // $percent = ((1-$vo[1]/$allp)/24)*$ror;
         // echo $vo[1].'->'.round($percent*100,2) . '%'."\n";
         $list += $percent*100000;
         //print_r($list);
         if($bl == 0 && $random <= $list){
           $bl = 1;
           $winK = $k;
         }
         // echo $data['money'][$vo[0]].'--';
         if(isset($data['money'][$vo[0]]) && (($vo[1]*$data['money'][$vo[0]]) < $data_sum) ){
           $sel_win[] = $k;
         }
       }
       // die;
       //如果开奖号为21且可开号码大于1的情况下 随机一波
       if($winK == 21 && count($sel_win) >= 2){
         $winK = $sel_win[array_rand($sel_win,1)];
       }
       //是否中奖
       $win_money = 0;
       if( isset($data['money'][$odds[$winK][0]]) ){
         $win_money = round($data['money'][$odds[$winK][0]] * $odds[$winK][1],2);
       }else{
         $win_money = round(max($data['money']) * $odds[$winK][1],2);
       }

       $lottery_config = LotteryConfig::get($this->gameType)->toArray();

       moneyAction([
           'uid' => $this->user['id'],
           'money' => $data_sum,
           'type' => 0,
           // 'explain' => $lottery_config['name'] .' 投注'
       ]);
       if ($win_money > 0) {
           moneyAction([
               'uid' =>$this->user['id'],
               'money' => $win_money,
               'type' => 3,
               'explain' => $lottery_config['name'] . ' 中奖'
           ]);
       }
       Betting::create([
           'user_id' => $this->user['id'],
           'content' => $winK,
           'money' => $data_sum,
           'win' => $win_money,
           'expect' => '',
           'type' => $this->gameType,
           'state' => 1,
           'create_time' => time()
       ]);
       $user = User::get($this->user['id'])->toArray();
       $return_data = [
         'win' => $win_money,
         'wink' => $winK,
         'money' => $user['money'],
         'code' => 1,
       ];
       return $return_data;
    }

    /**
     * 老虎机游戏的次数
     * @return number
     */
    public function gameTigerNum()
    {
      // $return_data = [
      //   'num' => 0,
      //   'room' => [],
      // ]
      $user = $this->checkLogin();

      if( $user['code'] == 1 ){
        // print_r(123);
        $return_data['money'] = $user['data']['money'];
      }else{
        // print_r(321);
        $return_data['money'] = 0;
      }
      $return_data['num'] = Betting::where([
          'user_id' => $this->user['id'],
          'type' => 56,
          'create_time' => ['create_time','>',strtotime(date('Y-m-d 00:00:00'))]
      ])->count();
      $return_data['room'] = $this->gameTigerConfig()['basic_config']['room'];
      // print_r($return_data);die;
      return $return_data;
    }

    /**
     * 获取老虎机配置
     * @return array
     */
     public function gameTigerConfig($val = 1)
     {
       $rs = LotteryConfig::get(56)->toArray();
       $rs['basic_config'] = json_decode($rs['basic_config'],true);
       if($val == 1){
         $rs['basic_config']['percent'] = '';
       }
       return $rs;
     }
}
