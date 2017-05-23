<?php
	Header("../"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "profiles");

	$iUserid 		= isset($_POST['user']) ? $_POST['user'] : '';
	$type 			= isset($_POST['type']) ? $_POST['type'] : '';
	$aboutmeText 	= isset($_POST['aboutmetext']) ? $_POST['aboutmetext'] : '';

	$qUser = DB::getInstance() -> query('SELECT aboutme FROM users WHERE userid = ?', array($iUserid));

	# check if the logged in user is you to prevent editing data that isn't from you
	if($arrUserData['userid'] == $iUserid)
	{
		if($qUser -> count() > 0)
		{
			if(!strcmp($type, "retrievetext")) # User pressed the edit button
			{
				if($arrUserData['groupid'] == 1) # Check permission
				{
					if($arrUserData['userid'] == $iUserid)
					{
						echo $qUser -> first() -> aboutme;
					}
					else
					{
						echo 'Invalid userid';
					}
				}
				else
				{
					echo $qUser -> first() -> aboutme;
				}
			}
			else if(!strcmp($type, "save")) # User pressed save button
			{
				DB::getInstance() -> update('users', array('aboutme' => $aboutmeText), array('userid', '=', $iUserid));

				echo Functions::bb_parse($aboutmeText);
			}
			else
			{
				echo 'Invalid type';
			}
		}
		else
		{
			echo 'No user found';
		}
	}
	else
	{
		echo 'Invalid user';
	}
