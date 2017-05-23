<?php
	Header("../"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "settings");

	// succesfully authenticated
	if($arrUserData['loggedin'] == 1)
	{
		# update settings depending on what you want to change
		$sTheme = isset($_POST['theme']) ? $_POST['theme'] : '';
		$sVisibility = isset($_POST['accessibility']) ? $_POST['accessibility'] : '';
		$bShowEmail = isset($_POST['showemail']) ? $_POST['showemail'] : '';
		$sShowBlogs = isset($_POST['showblogs']) ? $_POST['showblogs'] : '';
		$sShowUploads = isset($_POST['showuploads']) ? $_POST['showuploads'] : '';
		$sShowNotifications = isset($_POST['shownotifications']) ? $_POST['shownotifications'] : '';

		if(strcmp($sTheme, ""))
		{
			if(!strcmp($sTheme, "dark") || !strcmp($sTheme, "light"))
			{
				$sTheme = 'style-' . $_POST['theme'] . '.css';
				DB::getInstance() -> query("UPDATE users SET theme = ? WHERE userid = ?", array($sTheme, $arrUserData['userid']));
			}
		}
		else if(strcmp($sVisibility, ""))
		{
			if(!strcmp($sVisibility, "public") || !strcmp($sVisibility, "friends") || !strcmp($sVisibility, "private"))
			{
				DB::getInstance() -> query("UPDATE users SET visibility = ? WHERE userid = ?", array($sVisibility, $arrUserData['userid']));
			}
		}
		else if(strcmp($bShowEmail, ""))
		{
			if(!strcmp($bShowEmail, "yes") || !strcmp($bShowEmail, "no"))
			{
				DB::getInstance() -> query("UPDATE users SET showemail = ? WHERE userid = ?", array($bShowEmail, $arrUserData['userid']));
			}
		}
		else if(strcmp($sShowUploads, ""))
		{
			if(!strcmp($sShowUploads, "public") || !strcmp($sShowUploads, "friends") || !strcmp($sShowUploads, "private"))
			{
				DB::getInstance() -> query("UPDATE users SET showuploads = ? WHERE userid = ?", array($sShowUploads, $arrUserData['userid']));
			}
		}
		else if(strcmp($sShowBlogs, ""))
		{
			if(!strcmp($sShowBlogs, "public") || !strcmp($sShowBlogs, "friends") || !strcmp($sShowBlogs, "private"))
			{
				DB::getInstance() -> query("UPDATE users SET showblogs = ? WHERE userid = ?", array($sShowBlogs, $arrUserData['userid']));
			}
		}
		else if(strcmp($sShowNotifications, ""))
		{
			if($sShowNotifications == $arrUserData['userid'])
			{
				DB::getInstance() -> query("UPDATE friends SET notification = 1 WHERE otheruser = ? AND accepted = 0", array($sShowNotifications));
			}
			else
			{
				echo 'Invalid user given';
			}
		}
		else
		{
			echo 'Unknown error happened';
		}
	}
	else
	{
		echo 'User is not logged in';
	}
