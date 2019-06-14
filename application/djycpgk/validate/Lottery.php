<?php
namespace app\djycpgk\validate;

use think\Validate;

class Lottery extends Validate{	


    protected $regex = [ 'chazhi' => "/^[1-9][0-9]*$/"];

    protected $rule = [
        'chazhi'  => '>=:0|number|require|regex:chazhi',
        'switch'  => 'require',

    ];
     protected $message  =   [
        'chazhi.regex' =>'必须为正数',
        'chazhi.egt' => '大于等于0',
        'chazhi.number' =>'必须是数字',
        'chazhi.require' =>'不能为空',
        'switch.require' =>'不能为空',
    ];

}
