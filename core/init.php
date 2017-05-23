<?php
	session_start();

	$GLOBALS['config'] = array(
		'firstyear' => "2015-05-01",
		'max_messages' => 1000,
		'max_friend_requests' => 100,
		'default_user' => 'PortfolioBot',
		'mysql' => array(
			'host' 		=> '127.0.0.1',
			'username' 	=> 'root',
			'password' 	=> '',
			'db' 		=> 'portfolio'
		),
		'session' => array(
			'session_name' => 'user'
		),
		'cookie' => array(
			'cookie_name' => 'login_token'
		),
		'validation' => array(
			'namemin' => 3,
			'namemax' => 15,
			'passwordmin' => 4,
			'passwordmax' => 25
		),
		'picture_extension' => array(
			'jpg',
			'jpeg',
			'png',
			'tiff',
			'gif',
			'bmp',
			'bpg'
		)
	);

	spl_autoload_register(function($class) {
		require_once 'classes/' . $class . '.php';
	});
