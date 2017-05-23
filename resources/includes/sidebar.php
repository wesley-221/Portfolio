<?php
	$mainActive 		= (!strcmp($includePageActive, 'main')) ? 'active' : '';
	$newsActive 		= (!strcmp($includePageActive, 'news')) ? 'active' : '';
	$blogActive 		= (!strcmp($includePageActive, 'blog')) ? 'active' : '';
	$cookieActive		= (!strcmp($includePageActive, 'cookie')) ? 'active' : '';
	$changelogActive 	= (!strcmp($includePageActive, 'changelog')) ? 'active' : '';
	$uploadActive 		= (!strcmp($includePageActive, 'upload')) ? 'active' : '';
	$adminActive 		= (!strcmp($includePageActive, 'admin')) ? 'active' : '';
	$profileActive 		= (!strcmp($includePageActive, 'profile')) ? 'active' : '';
	$myProfileActive 	= (!strcmp($includePageActive, 'myprofileactive')) ? 'active' : '';
	$settingsActive 	= (!strcmp($includePageActive, 'settings')) ? 'active' : '';


	if($arrUserData['loggedin'] == 1)
	{
		echo '<aside id="sidebar" class="sidebarshow">
			<div class="user">
				<p>' . $arrUserData['firstname'] . ' ' . $arrUserData['lastname'] . '</p>
				<p>' . $arrUserData['email'] . '</p>
			</div>

			<nav class="list-group">
				<a href="/" class="list-group-item ' . $mainActive . '">Main</a>
				<!--<a href="../news" class="list-group-item ' . $newsActive . '">News</a>-->
				<a href="../blogs" class="list-group-item ' . $blogActive . '">Blog</a>
				<a href="../cookies" class="list-group-item ' . $cookieActive . '">Cookies</a>
				<a href="../changelog" class="list-group-item ' . $changelogActive . '">Changelog</a>
				<a href="../uploadcenter" class="list-group-item ' . $uploadActive . '">Upload center</a>';

				if(User::MiniAuth("adminpanel", $arrUserData)) { echo '<a href="../admin" class="list-group-item ' . $adminActive . '">Admin panel</a>'; }

				echo '<hr>
				<a href="../profile" class="list-group-item ' . $profileActive . '">Profiles</a>
				<a href="../profile/' . $arrUserData['userid'] . '" class="list-group-item ' . $myProfileActive . '">My profile</a>
				<hr>
				<a href="../settings" class="list-group-item ' . $settingsActive . '">Settings</a>
				<a href="../logout" class="list-group-item">Log out</a>
			</nav>
		</aside>';
	}
	else
	{
		echo '<aside id="sidebar" class="sidebarshow">
			<div class="user">
				<p>Welcome guest!</p>
			</div>

			<nav class="list-group">
				<a href="/" class="list-group-item ' . $mainActive . '">Main</a>
				<!--<a href="../news" class="list-group-item ' . $newsActive . '">News</a>-->
				<a href="../blogs/all" class="list-group-item ' . $blogActive . '">Blog</a>
				<a href="../changelog" class="list-group-item ' . $changelogActive . '">Changelog</a>

				<hr>
				<a href="../profile" class="list-group-item ' . $profileActive . '">Profiles</a>
			</nav>
		</aside>';
	}
?>
