<?php
	Header("../"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "edituploadcenter");

	$sUser = isset($_POST['user']) ? $_POST['user'] : '';

	$qUserQuery = DB::getInstance() -> query('SELECT userid, username FROM users WHERE username = ?', array($sUser));

	# check if user exists, if true show user uploads
	if($qUserQuery -> count() > 0)
	{
		foreach($qUserQuery -> results() as $oUser)
		{
			$qUserUploads = DB::getInstance() -> query('SELECT uploadname, originalname FROM uploads WHERE userid = ?', array($oUser -> userid));

			echo '<table><tbody><tr><td><div>';

			$folderUserUploads = "resources/uploads/" . $arrUserData['username'] . "/";

			if(file_exists($folderUserUploads))
			{
				foreach($qUserUploads -> results() as $oFile)
				{
					$fileExtension = pathinfo($oFile -> originalname, PATHINFO_EXTENSION);

					if(in_array($fileExtension, Config::get('config/picture_extension')))
					{
						echo '<a href="' . $folderUserUploads . $oFile -> uploadname . '" title="' . $oFile -> originalname . '" class="highlightitem"><img id="' . $oFile -> uploadname . '" style="vertical-align: top;" class="upload-image" src="' . $folderUserUploads . $oFile -> uploadname . '" /></a>';
					}
					else
					{
						echo '<a href="' . $folderUserUploads . $oFile -> uploadname . '" download="' . $oFile -> originalname . '" title="' . $oFile -> originalname . '" class="highlightitem"><div id="' . $oFile -> uploadname . '" class="upload-image cutofftextfile" style="padding: 4px; display: inline-block;"><center><i class="fa fa-file" style="color: white; font-size: 50"></i></center><span style="font-size: 12;">' . $oFile -> originalname . '</span></div></a>';
					}
				}
			}

			echo '</div></td></tr></tbody></table>';
		}
	}
	else
	{
		echo '<div class="alert alert-danger alert-dismissable col-md-4 col-md-offset-4"><button type="button" class="close" data-dismiss="alert">×</button>The user "' . $sUser . '" could not be found"</div>';
	}
