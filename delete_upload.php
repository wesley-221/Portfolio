<?php
	Header("index.php"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "upload");

	if(isset($_POST['delete']))
	{
		if(count($_POST['delete']) > 0)
		{
			foreach($_POST['delete'] as $file)
			{
				$folderUserUploads = 'resources/uploads/' . $arrUserData['username'] . '/' . $file;

				if(file_exists($folderUserUploads))
				{
					unlink($folderUserUploads);
					DB::getInstance() -> delete('uploads', array('uploadname', '=', $file));
				}
			}
		}
	}
