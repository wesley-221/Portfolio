<?php
	Header("../"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "editchangelog");

	$iChangelogId 	= isset($_POST['changelogid']) ? $_POST['changelogid'] : '';
	$sVersionName 	= isset($_POST['versionname']) ? $_POST['versionname'] : '';
	$iUserid 		= isset($_POST['userid']) ? $_POST['userid'] : '';
	$sType 			= isset($_POST['type']) ? $_POST['type'] : '';
	$sText 			= isset($_POST['text']) ? $_POST['text'] : '@EMPTY@';
	$dtDate 		= isset($_POST['date']) ? $_POST['date'] : '';
	$iDelete		= isset($_POST['delete']) ? $_POST['delete'] : '@EMPTY@';

	$eventArray = array();

	if(!strcmp($iDelete, "@EMPTY@")) # Don't delete the changelog entry
	{
		if(strlen($sVersionName) > 0) # Change version name
		{
			if(!preg_match('/^[0-9]+[.][0-9]+[.][0-9]+$/', $sVersionName))
			{
				$eventArray['error'] = 'The entered version number is invalid. Please use the format ###.###.###, where # is a number <br />';
			}
			else
			{
				DB::getInstance() -> update('changelog', array('versionname' => $sVersionName), array('changelogid', '=', $iChangelogId));
				$eventArray['success'] = 'Changelog Id "' . $iChangelogId . '" Version Name has been changed to "' . $sVersionName . '". <br />';
			}

			echo json_encode($eventArray);
		}
		else if(strlen($iUserid) > 0) # Change user id
		{
			$qUser = DB::getInstance() -> query('SELECT userid FROM users WHERE userid = ?', array($iUserid));

			if($qUser -> count() > 0)
			{
				if(is_numeric($iUserid))
				{
					DB::getInstance() -> update('changelog', array('userid' => $iUserid), array('changelogid', '=', $iChangelogId));
					$eventArray['success'] = 'Changelog Id "' . $iChangelogId . '" User Id has been changed to "' . $iUserid . '". <br />';
				}
				else
				{
					$eventArray['error'] = 'The userid has to be numeric. <br />';
				}
			}
			else
			{
				$eventArray['error'] = 'The entered userid is invalid. <br />';
			}

			echo json_encode($eventArray);
		}
		else if(strlen($sType) > 0) # Change the type
		{
			if(!strcmp($sType, "fix") || !strcmp($sType, "update") || !strcmp($sType, "new"))
			{
				DB::getInstance() -> update('changelog', array('type' => $sType), array('changelogid', '=', $iChangelogId));
				$eventArray['success'] = 'Changelog Id "' . $iChangelogId . '" Type has been changed to "' . $sType . '". <br />';
			}
			else
			{
				$eventArray['error'] = "Invalid tag, only possibilities are \"fix\", \"update\" and \"new\". <br />";
			}

			echo json_encode($eventArray);
		}
		else if(strcmp($sText, "@EMPTY@")) # Change the text
		{
			if(strlen($sText) > 0)
			{
				DB::getInstance() -> update('changelog', array('text' => $sText), array('changelogid', '=', $iChangelogId));
				$eventArray['success'] = 'Changelog Id "' . $iChangelogId . '" Text has been changed to "' . $sText . '". <br />';
			}
			else
			{
				$eventArray['error'] = "You need to fill in at least 1 character. <br />";
			}

			echo json_encode($eventArray);
		}
		else if(strlen($dtDate) > 0) # Change the date
		{
			if(preg_match("/^\d{1,4}-\d{1,2}-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}$/", $dtDate))
			{
				DB::getInstance() -> update('changelog', array('date' => $dtDate), array('changelogid', '=', $iChangelogId));
				$eventArray['success'] = 'Changelog Id "' . $iChangelogId . '" Date has been changed to "' . $dtDate . '". <br />';
			}
			else
			{
				$eventArray['error'] = "Incorrect date format, please use: yyyy-mm-dd hh-mm-ss. <br />";
			}

			echo json_encode($eventArray);
		}
	}
	else # Delete the changelog entry
	{
		DB::getInstance() -> DELETE('changelog', array('changelogid', '=', $iChangelogId));
	}
