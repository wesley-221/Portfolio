<?php
	Header("../"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "profiles");

	$userFriend 		= isset($_POST['userfriend']) ? $_POST['userfriend'] : '';
	$otherUser 			= isset($_POST['otheruser']) ? $_POST['otheruser'] : '';
	$safetyCheck 		= isset($_POST['safetycheck']) ? $_POST['safetycheck'] : '';
	$messageId 			= isset($_POST['messageid']) ? $_POST['messageid'] : '';
	$type 				= isset($_POST['type']) ? $_POST['type'] : '';
	$orderOption 		= isset($_POST['orderoption']) ? $_POST['orderoption'] : '';

	if(!strcmp($type, 'accept')) # User pressed on accept -> will accept friend request
	{
		$qFriendRequest = DB::getInstance() -> query('SELECT userfriend, otheruser, safetycheck FROM friends WHERE userfriend = ? AND otheruser = ? AND safetycheck = ?', array($userFriend, $otherUser, $safetyCheck));

		if($qFriendRequest -> count() > 0)
		{
			DB::getInstance() -> insert('friends', array('userfriend' => $otherUser, 'otheruser' => $userFriend, 'notification' => 0, 'accepted' => 1, 'safetycheck' => Functions::generate_uniqueID(25)));
			DB::getInstance() -> query('UPDATE friends SET notification = 0, accepted = 1 WHERE userfriend = ? AND otheruser = ?', array($userFriend, $otherUser));

			$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($userFriend));
			echo 'You have accepted the friend invite from ' . $qUser -> first() -> username . '.';
		}
	}
	else if(!strcmp($type, 'decline')) # User pressed on decline -> will decline friend request
	{
		$qFriendRequest = DB::getInstance() -> query('SELECT userfriend, otheruser, safetycheck FROM friends WHERE userfriend = ? AND otheruser = ? AND safetycheck = ?', array($userFriend, $otherUser, $safetyCheck));

		if($qFriendRequest -> count() > 0)
		{
			DB::getInstance() -> query('UPDATE friends SET notification = 0, accepted = 0 WHERE userfriend = ? AND otheruser = ?', array($userFriend, $otherUser));

			$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($userFriend));
			echo 'You have declined the friend invite from ' . $qUser -> first() -> username . '.';
		}
	}
	else if(!strcmp($type, 'hide')) # User pressed on hide -> will hide the friend request, no accept or decline
	{
		$qFriendRequest = DB::getInstance() -> query('SELECT userfriend, otheruser, safetycheck FROM friends WHERE userfriend = ? AND otheruser = ? AND safetycheck = ?', array($userFriend, $otherUser, $safetyCheck));

		if($qFriendRequest -> count() > 0)
		{
			DB::getInstance() -> query('UPDATE friends SET notification = 0 WHERE userfriend = ? AND otheruser = ?', array($userFriend, $otherUser));

			$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($userFriend));
			echo 'You hid the friend request from ' . $qUser -> first() -> username . '.';
		}
	}
	else if(!strcmp($type, 'markunread')) # User pressed on mark unread -> will mark the message as unread
	{
		DB::getInstance() -> query('UPDATE messages SET messageread = 0 WHERE messageid = ?', array($messageId));
	}
	else if(!strcmp($type, 'deletemessage')) # User pressed on delete message -> will delete the message
	{
		DB::getInstance() -> query('DELETE FROM messages WHERE messageid = ?', array($messageId));
	}
	else if(!strcmp($type, 'sortingoptions')) # User changed order options -> will change the way the messages are ordered (messageid/messageread/date)
	{
		DB::getInstance() -> query('UPDATE users SET ordermessage = ? WHERE userid = ?', array($orderOption, $arrUserData['userid']));
	}
	else if(!strcmp($type, 'orderoptions')) # ascend/descend sorting option -> will change how the messages are ordered (ascend/descend)
	{
		DB::getInstance() -> query('UPDATE users SET orderoptions = ? WHERE userid = ?', array(strtoupper($orderOption), $arrUserData['userid']));
	}
	else # this should not happen in any circumstance but is there in case it does happen
	{
		echo 'Something went wrong';
	}
