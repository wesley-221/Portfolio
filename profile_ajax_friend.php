<?php
	Header("../"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "profiles");

	$iUserid = isset($_POST['userid']) ? $_POST['userid'] : '';

	$qFriendship = DB::getInstance() -> query('SELECT userfriend, otheruser FROM friends WHERE userfriend = ? AND otheruser = ?', array($arrUserData['userid'], $iUserid));

	if ($qFriendship -> count() > 0) {
		# delete line userfriend arruserdata and otheruser iUserid
		# update line otheruser iUserid notification = 1

		DB::getInstance() -> query('DELETE FROM friends WHERE userfriend = ? AND otheruser = ?', array($arrUserData['userid'], $iUserid));
		DB::getInstance() -> query('UPDATE friends SET notification = 1 WHERE userfriend = ? AND otheruser = ?', array($iUserid, $arrUserData['userid']));

		$qOtherUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($iUserid));

		echo 'You have unfriended ' . $qOtherUser -> first() -> username . '. ';
	}
	else {
		// notification == 0: otheruser accepted invite
		// notification == 1: otheruser has a pending invite

		// userfriend: the user that is friends with another user
		// otheruser: the friend from the user

		$qPendingInvite = DB::getinstance() -> query('SELECT accepted FROM friends WHERE userfriend = ? AND otheruser = ?', array($iUserid, $arrUserData['userid']));

		if($qPendingInvite -> count() > 0)
		{
			foreach($qPendingInvite -> results() as $oPending)
			{
				if($oPending -> accepted == 1) // other has pending invite for user
				{
					# send friend request to user
					# update friend request from otheruser

					DB::getInstance() -> insert('friends', array('userfriend' => $arrUserData['userid'], 'otheruser' => $iUserid, 'notification' => 0, 'accepted' => 1, 'safetycheck' => Functions::generate_uniqueID(25)));
					DB::getInstance() -> query('UPDATE friends SET notification = 0 WHERE userfriend = ? AND otheruser = ?', array($iUserid, $arrUserData['userid']));

					$qOtherUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($iUserid));
					echo 'You have added the user ' . $qOtherUser -> first() -> username . ' to your friends. Your friendship is now mutual!';
				}
				else
				{
					# $arruserdata declined invite from otheruser
					# $arruserdata send invite to otheruser

					DB::getInstance() -> insert('friends', array('userfriend' => $arrUserData['userid'], 'otheruser' => $iUserid, 'notification' => 0, 'accepted' => 1, 'safetycheck' => Functions::generate_uniqueID(25)));
					DB::getInstance() -> query('UPDATE friends SET notification = 0, accepted = 1 WHERE userfriend = ? AND otheruser = ?', array($iUserid, $arrUserData['userid']));

					$qOtherUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($iUserid));
					echo 'You have added the user ' . $qOtherUser -> first() -> username . ' to your friends. Your friendship is now mutual!';
				}
			}
		}
		else
		{
			# send friend request to otheruser
			# check if user doesn't have too many friend requests: Config::get('config/max_friend_requests')
			$qFriendRequests = DB::getInstance() -> query('SELECT count(otheruser) AS amount FROM friends WHERE otheruser = ? AND (notification = 1 OR notification = 0)', array($iUserid));

			if($qFriendRequests -> count() > 0)
			{
				# user has too many friendrequests
				if($qFriendRequests -> first() -> amount > Config::get('config/max_friend_requests'))
				{
					echo 'This user has too many friend requests. In order to add this user he has to accept/decline some of their friend requests.';
				}
				else
				{
					# user doesn't have too many friendrequests
					DB::getInstance() -> insert('friends', array('userfriend' => $arrUserData['userid'], 'otheruser' => $iUserid, 'notification' => 1, 'accepted' => 1, 'safetycheck' => Functions::generate_uniqueID(25)));

					$qOtherUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($iUserid));
					echo 'You have added the user ' . $qOtherUser -> first() -> username . ' to your friends. They will get a notification in their inbox.';
				}
			}
			else
			{
				# user doesn't have too many friendrequests
				DB::getInstance() -> insert('friends', array('userfriend' => $arrUserData['userid'], 'otheruser' => $iUserid, 'notification' => 1, 'accepted' => 1, 'safetycheck' => Functions::generate_uniqueID(25)));

				$qOtherUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($iUserid));
				echo 'You have added the user ' . $qOtherUser -> first() -> username . ' to your friends. They will get a notification in their inbox.';
			}
		}
	}
