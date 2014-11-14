<?php

return array(
	'default' => 'mysql',
	'connections' => array(
		'sqlite' => array(
			'driver'   => 'sqlite',
			'database' => __DIR__.'/../../database/local.sqlite',
			'prefix'   => '',
		),
		'mysql' => array(
			'driver'    => 'mysql',
			'host'      => 'localhost',
			'database'  => 'newsdiff',
			'username'  => 'root',
			'password'  => '',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
		),
	),
);
