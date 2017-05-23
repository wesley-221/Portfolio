<?php
	Header("../"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "managegroups");

	$iOldGroupId 		= isset($_POST['oldgroupid']) 		? $_POST['oldgroupid'] : ''; # "oldgroupid" is retrieved from admin_panel.php
	$iGroupId 			= isset($_POST['groupid']) 			? $_POST['groupid'] : ''; # "groupid" is retrieved from admin_panel.php
	$sGroupName 		= isset($_POST['groupname']) 		? $_POST['groupname'] : ''; # "groupname" is retrieved from admin_panel.php
	$iEdit 				= isset($_POST['edit'])				? $_POST['edit'] : ''; # "edit" is retrieved from admin_panel.php
	$iDelete			= isset($_POST['delete']) 			? $_POST['delete'] : ''; # "delete" is retrieved from admin_panel.php
	$iDeleteGroupId		= isset($_POST['deletegroupid'])	? $_POST['deletegroupid'] : ''; # "deletegroupid" is retrieved from admin_panel.php
	$sDeleteGroupName	= isset($_POST['deletegroupname'])	? $_POST['deletegroupname'] : ''; # "deletegroupname" is retrieved from admin_panel.php

	$sError_Message = "";
	$sWarning_Message = "";

	if($iDelete == 1)
	{
		// group has been deleted
		DB::getInstance() -> query('DELETE FROM groups WHERE groupid = ?', array($iDeleteGroupId));

		echo '<div class="alert alert-success alert-dismissable col-xs-10 col-xs-offset-1 col-md-7 col-md-offset-3"><button type="button" class="close" data-dismiss="alert">×</button>The group "' . $sDeleteGroupName . '" has been succesfully deleted.</div>';
	}
	else if($iEdit == 1)
	{
		if(strlen($iOldGroupId) <= 0 || strlen($iOldGroupId) >= 6 || !is_numeric($iOldGroupId))
		{
			$sError_Message .= '"Old Group id" does not meet the requirements. Minimum length: 1. Maximum length: 5 <br />';
		}

		if(strlen($iGroupId) <= 0 || strlen($iGroupId) >= 6 || !is_numeric($iGroupId))
		{
			$sError_Message .= '"Group id" does not meet the requirements. Minimum length: 1. Maximum length: 5 <br />';
		}

		if(strlen($sGroupName) <= 0 || strlen($sGroupName) >= 20)
		{
			$sError_Message .= '"Group name" does not meet the requirements. Minimum value: 1 <br />';
		}

		$qGroup = DB::getInstance() -> query('SELECT groupid FROM groups WHERE groupid = ?', array($iGroupId));
		if($qGroup -> count() > 0)
		{
			$sWarning_Message .= '<b>WARNING:</b> The group id "' . $iGroupId . '" already exists. If you have multiple roles with the same id something <u>could</u> go wrong. Use unique id\'s to prevent this <br />';
		}

		$qGroup = DB::getInstance() -> query('SELECT groupname FROM groups WHERE groupname = ?', array($sGroupName));
		if($qGroup -> count() > 0)
		{
			$sWarning_Message .= '<b>WARNING:</b> The group name "' . $sGroupName . '" already exists. If you have multiple roles with the same name something <u>could</u> go wrong. Use unique names to prevent this <br />';
		}

		// check if there was an error if not update the group
		if(strlen($sError_Message) > 0)
		{
			echo '<div class="alert alert-danger alert-dismissable col-xs-10 col-xs-offset-1 col-md-7 col-md-offset-3"><button type="button" class="close" data-dismiss="alert">×</button>' . $sError_Message . '</div>';
		}
		else
		{
			DB::getInstance() -> query('UPDATE groups SET groupid = ?, groupname = ? WHERE groupid = ?', array($iGroupId, $sGroupName, $iOldGroupId));
			echo '<div class="alert alert-success alert-dismissable col-xs-10 col-xs-offset-1 col-md-7 col-md-offset-3"><button type="button" class="close" data-dismiss="alert">×</button>The group "' . $sGroupName . '" has been succesfully edited.</div>';
			if(strlen($sWarning_Message) > 0)
			{
				echo '<div class="alert alert-warning alert-dismissable col-xs-10 col-xs-offset-1 col-md-7 col-md-offset-3"><button type="button" class="close" data-dismiss="alert">×</button>' . $sWarning_Message . '</div>';
			}
		}
	}
	else
	{
		echo 'Something went wrong';
	}
