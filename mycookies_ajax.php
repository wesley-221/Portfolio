<?php
	Header("../"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "profiles");

	$iUserID 	= isset($_POST['userid']) ? $_POST['userid'] : '';
	$iCookieID 	= isset($_POST['cookieid']) ? $_POST['cookieid'] : '';
	$sAction 	= isset($_POST['action']) ? $_POST['action'] : '';

	$qUserId = DB::getInstance() -> query('SELECT userid FROM cookies WHERE cookieid = ?', array($iCookieID));

	# Check if the logged user is actually you to prevent deleting someone else's cookies
	if($arrUserData['userid'] == $iUserID)
	{
		if($qUserId -> count() > 0)
		{
			if($qUserId -> first() -> userid == $iUserID || $arrUserData['groupid'] >= 3)
			{

				if(!strcmp($sAction, "delete")) # delete a cookie
				{
					DB::getInstance() -> delete('cookies', array('cookieid', '=', $iCookieID));
					echo 'Succesfully deleted the cookie with the id "' . $iCookieID . '"';
				}
				else if(!strcmp($sAction, "extend")) # extend a cookie
				{
					$expireDate = date('Y-m-d', strtotime('+1 months'));
					$newDate = date('Y-m-d');
					DB::getInstance() -> update('cookies', array('date' => $newDate), array('cookieid', '=', $iCookieID));
					echo 'Succesfully extended the cookie with the id "' . $iCookieID . '". The expire date now is at ' . $expireDate . '.';
				}
				else if(!strcmp($sAction, "deleteall")) # delete all cookies
				{
					$qSelect = DB::getInstance() -> query('SELECT cookieid FROM cookies WHERE cookieid <> ?', array($iCookieID));

					if($qSelect -> count() > 0) # prevent from deleting the active cookie so you don't get logged out
					{
						DB::getInstance() -> query('DELETE FROM cookies WHERE cookieid <> ?', array($iCookieID));
						echo 'Succesfully deleted all the cookies that are not active';
					}
					else
					{
						echo 'No inactive cookies found';
					}
				}
				else
				{
					echo 'Something went wrong, try again';
				}
			}
			else
			{
				echo 'Insufficient permission';
			}
		}
		else
		{
			echo 'Invalid cookieid given';
		}
	}
	else
	{
		echo 'Insufficient permission';
	}
