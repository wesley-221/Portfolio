<?php
	Header("../"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "pageaccessibility");

	$iChangeGroup 	= isset($_POST['group']) ? $_POST['group'] : '';
	$iNeedLog		= isset($_POST['needlog']) ? $_POST['needlog'] : '';

	if($iChangeGroup == 1)
	{
		$sId = isset($_POST['changeid']) ? $_POST['changeid'] : '';
		$iGroup = isset($_POST['changevalue']) ? $_POST['changevalue'] : '';

		$sId = str_replace('selectgroup', '', $sId);

		// Add pages here which you want to add to the accessibility list
		$AllowedPages = array('main', 'news', 'blog', 'document', 'uploadcenter', 'profiles', 'changelog', 'editchangelog',
								'adminpanel', 'pageaccessibility', 'manageusers', 'managegroups');

		if(in_array($sId, $AllowedPages))
		{
			$qGroup = DB::getInstance() -> query('SELECT groupid from GROUPS WHERE groupid = ?', array($iGroup));
			if($qGroup -> count() > 0)
			{
				DB::getInstance() -> update('accessibility', array('groupid' => $iGroup), array('pagename', '=', $sId));
			}
		}
	}
	else if($iNeedLog == 1)
	{
		$sId = isset($_POST['changeid']) ? $_POST['changeid'] : '';
		$iGroup = isset($_POST['changevalue']) ? $_POST['changevalue'] : '';

		$sId = str_replace('selectlog', '', $sId);

		$AllowedPages = array('main', 'news', 'blog', 'uploadcenter', 'profiles', 'changelog', 'editchangelog',
								'adminpanel', 'pageaccessibility', 'manageusers', 'managegroups');

		if(in_array($sId, $AllowedPages))
		{
			if(is_numeric($iGroup) && ($iGroup == 1 || $iGroup == 0))
			{
				DB::getInstance() -> update('accessibility', array('loggedin' => $iGroup), array('pagename', '=', $sId));
			}
		}
	}
