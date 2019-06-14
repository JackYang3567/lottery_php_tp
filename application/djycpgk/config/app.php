<?php

return [
	// 是否强制使用路由
	'url_route_must' => true,

	'AUTH_CONFIG' => array(
		// 用户组数据表名
		'AUTH_GROUP' => 'think_auth_group',
		// 用户-用户组关系表
		'AUTH_GROUP_ACCESS' => 'think_auth_group_access',
		// 权限规则表
		'AUTH_RULE' => 'think_auth_rule',
		// 用户信息表
		'AUTH_USER' => 'admin',
	),
	'dispatch_success_tmpl' => 'public/success',
	'dispatch_error_tmpl' => 'public/error',
	//'exception_tmpl'         => Env::get('think_path') . 'tpl/error_404.tpl',
	//'exception_handle'       => 'app\lib\exception\ExceptionHandler',
	'default_filter' => 'trim',

];