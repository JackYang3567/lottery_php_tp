<?php
namespace app\djycpgk\validate;

use think\Validate;

class System extends Validate{	


    protected $rule = [
        'name'  => 'require',
        'number'  => 'require',
        'user_name'  => 'require',
        'explain'  => 'require',
        'slogan'  => 'require',

    ];
     protected $message  =   [
        'name.require' => '银行名称不能为空',
        'number.require' => '账户号不能为空',
        'user_name.require' => '标题不能为空',
        'explain.require' => '入款说明不能为空',
        'slogan.require' => '充值标语不能为空',
        

    ];

}
