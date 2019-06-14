<?php
namespace app\djycpgk\validate;

use think\Validate;

class Footballss extends Validate{


    protected $rule = [
        'name'  => 'require',
    ];
    protected $message  =   [
        'name.require' => '名称必须',

    ];

}
