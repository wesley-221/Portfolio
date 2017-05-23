<?php
	Header("../"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "makeblog");

	$sAction = isset($_POST['action']) ? $_POST['action'] : '';
	$iBlogId = isset($_POST['blogid']) ? $_POST['blogid'] : '';

	$qBlog = DB::getInstance() -> query('SELECT blogid FROM blog WHERE blogid = ?', array($iBlogId));

	if($qBlog -> count() > 0)
	{
		# Blog exists
		$dtCurDate = date('Y-m-d');
		$qResponse = DB::getInstance() -> query('SELECT reblog, bloglike FROM blogresponse WHERE blogid = ? AND userid = ?', array($iBlogId, $arrUserData['userid']));

		# User has already liked/reblogged
		if($qResponse -> count() > 0)
		{
			# Blog Response query
			foreach($qResponse -> results() as $oResponse)
			{
				# User pressed Like button
				if(!strcmp($sAction, 'like'))
				{
					# User has not liked this yet
					if($oResponse -> bloglike == 0)
					{
						DB::getInstance() -> query('UPDATE blogresponse SET bloglike = 1, date = ? WHERE blogid = ? AND userid = ?', array($dtCurDate, $iBlogId, $arrUserData['userid']));
						echo 'You liked this blog.';
					}
					# User has already liked this post
					else if($oResponse -> bloglike == 1)
					{
						DB::getInstance() -> query('UPDATE blogresponse SET bloglike = 0, date = ? WHERE blogid = ? AND userid = ?', array($dtCurDate, $iBlogId, $arrUserData['userid']));
						echo 'You revoked your like on this blog.';
					}
				}
				# User pressed Reblog button
				else if(!strcmp($sAction, 'reblog'))
				{
					# User has not reblogged this yet
					if($oResponse -> reblog == 0)
					{
						DB::getInstance() -> query('UPDATE blogresponse SET reblog = 1, date = ? WHERE blogid = ? AND userid = ?', array($dtCurDate, $iBlogId, $arrUserData['userid']));
						echo 'You reblogged this blog.';
					}
					# User has already reblogged this
					else if($oResponse -> reblog == 1)
					{
						DB::getInstance() -> query('UPDATE blogresponse SET reblog = 0, date = ? WHERE blogid = ? AND userid = ?', array($dtCurDate, $iBlogId, $arrUserData['userid']));
						echo 'You revoked your reblog on this blog.';
					}
				}
			}
		}
		# User has not liked/reblogged yet
		else
		{
			# User has pressed Like
			if(!strcmp($sAction, 'like'))
			{
				DB::getInstance() -> insert('blogresponse', array('blogid' => $iBlogId, 'userid' => $arrUserData['userid'], 'bloglike' => 1, 'reblog' => 0, 'date' => $dtCurDate));
				echo 'You liked this blog.';
			}
			# User has pressed reblog
			else if(!strcmp($sAction, 'reblog'))
			{
				DB::getInstance() -> insert('blogresponse', array('blogid' => $iBlogId, 'userid' => $arrUserData['userid'], 'bloglike' => 0, 'reblog' => 1, 'date' => $dtCurDate));
				echo 'You reblogged this blog.';
			}
		}
	}
	else # Blog was not found
	{
		echo "Something went wrong. Please try again.";
	}
