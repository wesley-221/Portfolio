<?php
	Header("../"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "profiles");

	$iUserid = isset($_POST['userid']) ? $_POST['userid'] : '';
	$sSubject = isset($_POST['subject']) ? $_POST['subject'] : '';
	$sMessage = isset($_POST['message']) ? $_POST['message'] : '';

	$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE username = ?', array($iUserid));
	if($qUser -> count() > 0)
	{
		foreach($qUser -> results() as $oUser)
		{
			if(strlen($sSubject) >= 2 && strlen($sSubject) <= 25)
			{
				if(strlen($sMessage) >= 2)
				{
					// no errors
					$dtDate = date('Y-m-d H:i:s');
					DB::getInstance() -> insert('messages', array('senderid' => $arrUserData['userid'], 'receiverid' => $iUserid, 'subject' => $sSubject, 'message' => $sMessage, 'date' => $dtDate));

					echo 'The message has been send to "' . $oUser -> username . '".';
				}
				else
				{
					echo 'The message has to be atleast 3 characters';
				}
			}
			else
			{
				echo 'The subject has to be atleast 3 characters';
			}
		}
	}
	else
	{
		echo 'User not found. Please try again';
	}
