<?php
	require_once 'core/init.php';

	class User
	{
		/*
			<summary>
				Gets userdata based on cookie
				$sCookie = isset($_COOKIE['cookie']) ? $_COOKIE['cookie'] : '';
				User::Authenticate($sCookie, $arrUserData);
				echo $arrUserData['loggedin'];
			</summary>
		*/

		public static function Authenticate($cookie, &$dataArray, $webpage)
		{
			if(isset($cookie))
			{
				$qCookie = DB::getInstance() -> query('SELECT cookieid, userid, date, cookie FROM cookies WHERE cookie = ? LIMIT 1', array($cookie));

				// check if cookie exists in the database
				if($qCookie -> count() > 0)
				{
					$iCookieID = $qCookie -> first() -> cookieid;
					$curDate = date('Y-m-d', strtotime('+1 months'));

					if($qCookie -> first() -> date > $curDate)
					{
						// cookie expired, delete from database and remove cookie from browser
						// so that the user has to log in again
						DB::getInstance() -> delete('cookies', array('cookieid', '=', $iCookieID));
						unset($_COOKIE[Config::get('config/cookie/cookie_name')]);
						setcookie(Config::get('config/cookie/cookie_name'), null, -1);

						Header("Location: ../");
						return;
					}

					// cookie hasn't expired, continue authenticating
					$iUserId = $qCookie -> first() -> userid;

					$qAccess = DB::getInstance() -> query('SELECT groupid, loggedin FROM accessibility WHERE pagename = ?', array($webpage));
					if($qAccess -> count() > 0)
					{
						$permissionreq 	= $qAccess -> first() -> groupid;
						$needlog 		= $qAccess -> first() -> loggedin;
					}
					else
					{
						$permissionreq = 0;
						$needlog = 0;
					}

					// create query based on permission
					if(isset($permissionreq) && is_numeric($permissionreq) && strlen($permissionreq) > 0)
					{
						$qUserValues = DB::getInstance() -> query('SELECT firstname, lastname, username, email, visibility, orderoptions, showemail, showblogs, showuploads, ordermessage, theme, groupid FROM users WHERE userid = ? AND groupid >= ?', array($iUserId, $permissionreq));
					}
					else
					{
						$qUserValues = DB::getInstance() -> query('SELECT firstname, lastname, username, email, visibility, orderoptions, showemail, showblogs, showuploads, ordermessage, theme, groupid FROM users WHERE userid = ?', array($iUserId));
					}

					if($qUserValues -> count() > 0)
					{
						foreach($qUserValues -> results() as $oUser)
						{
							DB::getInstance() -> update('users', array('lastseen' => date('Y-m-d H:i:s')), array('userid', '=', $iUserId));

							return $dataArray = array('userid' => $iUserId,
													'firstname' => $oUser -> firstname,
													'lastname' => $oUser -> lastname,
													'username' => $oUser -> username,
													'email' => $oUser -> email,
													'theme' => $oUser -> theme,
													'visibility' => $oUser -> visibility,
													'orderoptions' => $oUser -> orderoptions,
													'showemail' => $oUser -> showemail,
													'showblogs' => $oUser -> showblogs,
													'showuploads' => $oUser -> showuploads,
													'ordermessage' => $oUser -> ordermessage,
													'groupid' => $oUser -> groupid,
													'loggedin' => 1);
						}
					}
					else
					{
						unset($_COOKIE[Config::get('config/cookie/cookie_name')]);
						setcookie(Config::get('config/cookie/cookie_name'), null, -1);

						if($needlog == true)
						{
							Header("location: ../");
						}

						return $dataArray = array('loggedin' => 0, 'userid' => '@INVALID@', 'theme' => 'style-light.css');
					}
				}
				else
				{
					$qAccess = DB::getInstance() -> query('SELECT groupid, loggedin FROM accessibility WHERE pagename = ?', array($webpage));
					if($qAccess -> count() > 0)
					{
						$permissionreq 	= $qAccess -> first() -> groupid;
						$needlog 		= $qAccess -> first() -> loggedin;
					}
					else
					{
						$permissionreq = 0;
						$needlog = 0;
					}

					unset($_COOKIE[Config::get('config/cookie/cookie_name')]);
					setcookie(Config::get('config/cookie/cookie_name'), null, -1);

					if($needlog == true)
					{
						Header("location: ../");
					}

					return $dataArray = array('loggedin' => 0, 'userid' => '@INVALID@', 'theme' => 'style-light.css');
				}
			}
		}

		// returns true if user is authenticated
		public static function MiniAuth($webpage, $arrdata = array("loggedin" => 0))
		{
			$qAccess = DB::getInstance() -> query('SELECT groupid, loggedin FROM accessibility WHERE pagename = ?', array($webpage));
			if($qAccess -> count() > 0)
			{
				$permissionreq 	= $qAccess -> first() -> groupid;
				$needlog 		= $qAccess -> first() -> loggedin;
			}
			else
			{
				$permissionreq = 0;
				$needlog = 0;
			}

			if($needlog == 1)
			{
				if(isset($permissionreq) && isset($arrdata['groupid']))
				{
					if($arrdata['groupid'] >= $permissionreq)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
				else
				{
					if($arrdata['loggedin'] == 1)
					{
						return true;
					}
					else
					{
						return false;
					}
				}
			}

			return false;
		}

		// works the same as MiniAuth()
		public static function MiniAuthRedirect($webpage, $arrdata = array("loggedin" => 0), $redirect = "../")
		{
			$qAccess = DB::getInstance() -> query('SELECT groupid, loggedin FROM accessibility WHERE pagename = ?', array($webpage));
			if($qAccess -> count() > 0)
			{
				$permissionreq 	= $qAccess -> first() -> groupid;
				$needlog 		= $qAccess -> first() -> loggedin;
			}
			else
			{
				$permissionreq = 0;
				$needlog = 0;
			}

			if($needlog == 1)
			{
				if(isset($permissionreq))
				{
					if($arrdata['groupid'] >= $permissionreq)
					{
						return true;
					}
					else
					{
						Header("Location: " . $redirect);
					}
				}
				else
				{
					if($arrdata['loggedin'] == 1)
					{
						return true;
					}
					else
					{
						Header("Location: " . $redirect);
					}
				}
			}

			return false;
		}

		public static function Register($username, $firstname, $lastname, $email, $password, $salt, $groupid, $countrycode, $joindate)
		{
			$qCookie = DB::getInstance() -> insert('users', array(
							'firstname'		=> $firstname,
							'lastname'	 	=> $lastname,
							'username' 		=> $username,
							'email' 		=> $email,
							'password' 		=> $password,
							'salt' 			=> $salt,
							'groupid'		=> $groupid,
							'countrycode' 	=> $countrycode,
							'joindate'		=> $joindate
						));
		}

		public static function Update($array, $userid)
		{
			DB::getInstance() -> update('users', $array, array('userid', '=', $userid));
		}

		public static function CreateSalt($length)
		{
			return Functions::generate_uniqueID($length);
		}

		public static function HashPassword($salt, $password)
		{
			return hash("whirlpool", $salt . $password);
		}

		public static function GenerateCookie($username)
		{
			return hash("whirlpool", $username . Functions::generate_uniqueID(5));
		}
	}
