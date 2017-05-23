<?php
	# Log out the user
	require_once 'core/init.php';
	if(isset($_COOKIE[Config::get('config/cookie/cookie_name')]))
	{
		setcookie(Config::get('config/cookie/cookie_name'), '', -7000000);
		Header('location: ../');
	}
	else
	{
		Header('location: ../');
	}
