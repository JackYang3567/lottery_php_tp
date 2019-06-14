<?php
namespace app\djycpgk\validate;

use think\Validate;

class Lottery2 extends Validate{	


    protected $regex = [ 'fanshui_v' => "/^[1-9][0-9]*$/"];

    protected $rule = [
        'fanshui_s'  => 'require',
        'fanshui_v'  => '>=:0|number|require|regex:fanshui_v',
        'kongzhi_s'  => 'require',
        // 'kongzhi_v'  => '>=:0|number|require',

    ];
     protected $message  =   [
        'regex' =>'必须为正数',
        'egt' => '大于等于0',
        'number' =>'必须是数字',
        'require' =>'不能为空',
    ];

}
