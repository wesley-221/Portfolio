<?php
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "profiles");
?>

<html>
	<head>
		<meta charset="utf-8">
		<base href="/">
		<meta name="viewport" content="minimum-scale=1.0, width=device-width, maximum-scale=1.0, user-scalable=no" />
		<link media="all" type="text/css" rel="stylesheet" href="../css/bootstrap.min.css" />
		<?php echo '<link media="all" id = "link-theme" type="text/css" rel="stylesheet" href="../css/' . $arrUserData['theme'] . '" />'; ?>
		<link media="all" type="text/css" rel="stylesheet" href = "../resources/font-awesome/css/font-awesome.min.css" />
		<link rel="shortcut icon" type="image/png" href="../resources/images/favicon.png" />

		<title>Profile &ndash; Portfolio</title>

		<!--[if lt IE 9]>
			<script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6/html5shiv.min.js"></script>
			<script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
		<![endif]-->
	</head>

	<body>
		<?php include_once 'resources/includes/navbar.php'; ?>
		<?php
			$User = strlen($_GET['u']) != 0 ? $_GET['u'] : '@EMPTY@';

			if($User == $arrUserData['userid'])
			{
				$includePageActive = "myprofileactive"; include_once 'resources/includes/sidebar.php';
			}
			else
			{
				$includePageActive = "profile"; include_once 'resources/includes/sidebar.php';
			}
		?>

		<div class="content">
			<div class="container-fluid">
				<?php
					if(!strcmp($User, '@EMPTY@')) # you aren't on any profile, show search bar and all users
					{
				?>
						<div class="row" style="height: 10%;">
							<div class="col-xs-6 col-xs-offset-3">
								<div class="panel panel-default">
									<div class="panel-body">
										<p><b>Please enter an username or userid to search for an user</b></p>
										<input type="text" id="usersearch" class="form-control" name="u" placeholder="username/firstname/lastname" autofocus /> <br />
									</div>
								</div>
							</div>
						</div>

						<div class="row" style="height: 10%;">
							<center><i id="refresher" class="fa fa-refresh fa-spin" style="font-size: 35px; display: none;"></i></center>
						</div>

						<div class="row" style="height: 80%;">
							<div class="col-xs-12">
								<div class="blogtable">
									<div class="panel-body" id="UserPage">
										<div id="blogtable">
											<?php
												$qUsers = DB::getInstance() -> query('SELECT userid, username, firstname, lastname, countrycode, groupid FROM users');

												foreach($qUsers -> results() as $oUser)
												{
													$qGroup = DB::getInstance() -> query('SELECT groupname FROM groups WHERE groupid = ?', array($oUser -> groupid));
													echo '<a href="../profile/' . $oUser -> userid . '"><div class="usersearch"><span class="searchusername">' . $oUser -> username . '</span> (<span class="searchusername">' . $oUser -> firstname . '</span> <span class="searchusername">' . $oUser -> lastname . '</span>) <span class="pull-right"><span style="margin-right: 10px;">' . $qGroup -> first() -> groupname . '</span> <img src="../resources/flags/' .  $oUser -> countrycode . '.png" style="border: 1px solid black;" /></span></div></a>';
												}
											?>
										</div>
									</div>
								</div>
							</div>
						</div>
				<?php
					}
					else # show user profile
					{
						$qUserQuery = DB::getInstance() -> query('SELECT userid, username, firstname, lastname, interests, groupid, email, showemail, visibility, showblogs, showuploads, pageviews, joindate, lastseen, aboutme, countrycode FROM users WHERE userid = ?', array($User));

						if($qUserQuery -> count() > 0)
						{
							foreach($qUserQuery -> results() as $oUser)
							{
								$arrVisible = array('public', 'friends', 'private');

								# show profile depending on visibility, set by user
								if(in_array($oUser -> visibility, $arrVisible))
								{
									if(!strcmp($oUser -> visibility, $arrVisible[2]))
									{
										if($oUser -> userid != $arrUserData['userid'])
										{
											echo '<div class="recentactivity">Welcome to the profile of ' . $oUser -> username . '<br>This user has their profile set to private so you are unable to visit their profile at this time. </div>';
											break;
										}
									}
									else if(!strcmp($oUser -> visibility, $arrVisible[1])) # show profile depnding on visibility, have to be a friend
									{
										$qFriends = DB::getInstance() -> query('SELECT accepted FROM friends WHERE userfriend = ? AND otheruser = ?', array($arrUserData['userid'], $oUser -> userid));
										if($qFriends -> first() -> accepted == 0)
										{
											echo '<div class="recentactivity">Welcome to the profile of ' . $oUser -> username . '<br>This user has their profile set to friends only so you are unable to visit their profile at this time. </div>';
											break;
										}
									}
								}

								if($_SERVER['REQUEST_METHOD'] == 'POST')
								{
									# user uploaded a file
									if($User == $arrUserData['userid'])
									{
										if(isset($_FILES['files']))
										{
											if(count($_FILES['files']))
											{
												# check if upload folder exists
												if(!file_exists('resources/uploads/' . $oUser -> username . '/'))
												{
													mkdir('resources/uploads/' . $oUser -> username, 0777, true);
												}

												# rearrange the files so that is properly readable
												Functions::reArrayFiles($_FILES['files']);

												# loop through the uploaded files
												foreach($_FILES as $file)
												{
													$extension = pathinfo($file['name'], PATHINFO_EXTENSION);

													# check if the extension is allowed
													if(in_array($extension, Config::get('config/picture_extension')))
													{
														# check if the size isn't too large
														if($file['size'] < 1048576)
														{
															if($file['error'] == UPLOAD_ERR_OK)
															{
																foreach(Config::get('config/picture_extension') as $deleteFileExtension)
																{
																	if(file_exists('resources/uploads/' . $oUser -> username . '/ava.' . $deleteFileExtension))
																	{
																		unlink('resources/uploads/' . $oUser -> username . '/ava.' . $deleteFileExtension);
																	}
																}

																move_uploaded_file($file['tmp_name'], 'resources/uploads/' . $oUser -> username . '/ava.' . $extension);
															}
															else
															{
																echo '<div class="alert alert-danger"><h4>Oops! Something went wrong</h4> Please try again</div>';
															}
														}
														else
														{
															echo '<div class="alert alert-danger"><h4>Oops! Something went wrong</h4> File exceeded the maximum file size. </div>';
														}
													}
													else
													{
														echo '<div class="alert alert-danger"><h4>Oops! Something went wrong</h4> This is not a valid picture. The allowed extensions are: <br> ';
															foreach(Config::get('config/picture_extension') as $extension)
															{
																echo ' - ' . $extension . '<br>';
															}

														echo '</div>';
													}
												}
											}
										}
									}
								}

								# give the user a pageview and a 5 minute cooldown on the pageview
								if($oUser -> userid != $arrUserData['userid'] && !isset($_COOKIE['pageview' . $oUser -> userid]))
								{
									$iPageViews = $oUser -> pageviews += 1;
									DB::getInstance() -> update('users', array('pageviews' => $iPageViews), array('userid', '=', $oUser -> userid));
									setcookie('pageview' . $oUser -> userid, $oUser -> userid, strtotime('+5 minutes'));
								}

								echo '<h3>Welcome to the userpage of ' . $oUser -> username . '!</h3>

								<ul class="nav nav-tabs" role="tablist">
									<li class="active">
										<a href="#aboutme" role="tab" data-toggle="tab">
											<i class="fa fa-user"></i> About me
										</a>
									</li>

									<li>
										<a href="#blogs" role="tab" data-toggle="tab">
											<i class="fa fa-rss"></i> Blogs
										</a>
									</li>

									<li>
										<a href="#reblogs" role="tab" data-toggle="tab">
											<i class="fa fa-rss"></i> Reblogs
										</a>
									</li>

									<li>
										<a href="#uploads" role="tab" data-toggle="tab">
											<i class="fa fa-upload"></i> Uploads
										</a>
									</li>

									<li>
										<a href="#friends" role="tab" data-toggle="tab">
											<i class="fa fa-users"></i> Friends
										</a>
									</li>';

									if($arrUserData['userid'] != $User && $arrUserData['loggedin'] == 1)
									{
										# establish the friendship between the two users and see if it's mutual
										$qFriends = DB::getInstance() -> query('SELECT userfriend, otheruser FROM friends WHERE userfriend = ? AND otheruser = ?', array($arrUserData['userid'], $oUser -> userid));

										if($qFriends -> count() > 0)
										{
											$qFriendsMutual = DB::getInstance() -> query('SELECT userfriend, otheruser FROM friends WHERE userfriend = ? AND otheruser = ?', array($oUser -> userid, $arrUserData['userid']));

											if($qFriendsMutual -> count() > 0)
											{
												// mutual friends
												echo '<li class="pull-right mutualfriend">
													<a id="addfriend">
														<span class="mutualfriend">
															<i class="fa fa-heart"></i> Friends
														</span>
													</a>
												</li>';
											}
											else
											{
												// not mutual friends
												echo '<li class="pull-right notmutualfriend">
													<a id="addfriend">
														<span class="notmutualfriend">
															<i class="fa fa-star"></i> Friends
														</span>
													</a>
												</li>';
											}
										}
										else
										{
											// not friended, show add friend button
											echo '<li class="pull-right addfriend">
												<a id="addfriend">
													<span class="addfriend">
														<i class="fa fa-plus"></i> Add as friend
													</span>
												</a>
											</li>';
										}
									}

								# if the user is logged in show a send message button
								if($arrUserData['loggedin'] == 1)
								{
									echo '<li class="pull-right" style="cursor: pointer;">
											<a href="../sendmessage/' . $oUser -> userid . '">
												<i class="fa fa-envelope-o"></i> Send message
											</a>
										</li>';
								}

								echo '</ul>

								<div class="tab-content">
									<div class="tab-pane fade active in" id="aboutme">
										<div class="row">
											<div class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
												<div class="avatar">';

												$folderUserUploads = "resources/uploads/" . $oUser -> username . "/";

												# show avatar if you have one
												if(file_exists($folderUserUploads))
												{
													$sAvatar = '@NOAVA@';

													foreach(Config::get('config/picture_extension') as $extension)
													{
														if(file_exists($folderUserUploads . 'ava.' . $extension))
														{
															$sAvatar = $folderUserUploads . 'ava.' . $extension;
														}
													}

													if(strcmp($sAvatar, '@NOAVA@'))
													{
														echo '<img src="' . $sAvatar . '" class="avatar-width" />';
													}
													else
													{
														echo '<img src="resources/images/ava.png" />';
													}
												}
												else
												{
													echo '<img src="resources/images/ava.png" />';
												}

												# button to change avatar
												if($arrUserData['userid'] == $User)
												{
													echo '<center style="padding-top: 4px;">
														<form action="../profile/' . $oUser -> userid . '" method="post" enctype="multipart/form-data" id="formsubmit">
															<label class="btn btn-default btn-upload">
																<div>Change avatar
																<input id="files" type="file" name="files"></div>
															</label>
														</form>
													</center>';
												}

											echo '<div style="padding-top: 4px;"><img src="../resources/flags/' . $oUser -> countrycode . '.png" style="border: 1px solid black;" /></div>
													<span style="color: black; font-size: 17px;">' . $oUser -> firstname . ' ' . $oUser -> lastname . '</span>';

													# show email if you have this enabled and always show for yourself
													if(!strcmp($oUser -> showemail, 'yes') || $oUser -> userid == $arrUserData['userid'])
													{
														echo '<div style="white-space: initial; word-wrap: break-word;">' . $oUser -> email . '</div>';
													}

												echo '</div>
											</div>

											<div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
												<div class="profile" style="margin-left: 20px;">
													<div class="recentactivity">
														Recent Acitvity
													</div>';

													$arrRecentActivity = array();

													$qRecentBlog = DB::getInstance() -> query('SELECT blogid, blogname, postdate FROM blog WHERE DATE_ADD(postdate, INTERVAL 30 DAY) AND userid = ?', array($oUser -> userid));

													foreach($qRecentBlog -> results() as $recent)
													{
														$arrRecentActivity[] = array('type' => 'blog','blogid' => $recent -> blogid, 'blogname' => $recent -> blogname, 'date' => $recent -> postdate);
													}

													$qRecentUploads = DB::getInstance() -> query('SELECT uploadname, originalname, postdate FROM uploads WHERE DATE_ADD(postdate, INTERVAL 30 day) AND softdelete = 0 AND userid = ?', array($oUser -> userid));

													foreach($qRecentUploads -> results() as $recent)
													{
														$arrRecentActivity[] = array('type' => 'file', 'uploadname' => $recent -> uploadname, 'originalname' => $recent -> originalname, 'date' => $recent -> postdate);
													}

													# re-sort the array so that most recent items are on top
													function sortFunction($a, $b) {
														return strtotime($b["date"]) - strtotime($a["date"]);
													}
													usort($arrRecentActivity, 'sortFunction');

													echo '<ul>';

													$arrCount = 0;
													foreach($arrRecentActivity as $entry)
													{
														if($arrCount == 9)
															break;

														if(!strcmp($entry['type'], 'file'))
														{
															echo '<li><a href="../profile/' . $oUser -> userid . '">' . $oUser -> username . '</a> has uploaded a new file: "<a href = "../resources/uploads/' . $oUser -> username . '/' . $entry['uploadname'] . '">' . $entry['originalname'] . '</a>".</li>';
														}
														else if(!strcmp($entry['type'], 'blog'))
														{
															echo '<li><a href="../profile/' . $oUser -> userid . '">' . $oUser -> username . '</a> has made a new blog: "<a href="../blogs/' . $entry['blogid'] . '">' . $entry['blogname'] . '</a>".</li>';
														}

														$arrCount ++;
													}

													echo '</ul>';

													echo '<div class="aboutme" id="aboutme">
														<div class="recentactivity" style="margin-top: 25px; margin-bottom: 5px;">
															About me <span id="saved" class="pull-right" style="background-color: rgba(0, 255, 0, 0.5); padding: 2px; display: none;">Changes have been saved.</span>
														</div>

														<div id="aboutme_edit">
															<button class="btn btn-bb" type="button" id="bold" style="display: none;"><b>bold</b></button> <button class="btn btn-bb" type="button" id="italic" style="display: none;"><i>italic</i></button> <button class="btn btn-bb" type="button" id="underline" style="display: none;"><u>underline</u></button> <button class="btn btn-bb" type="button" id="strike" style="display: none;"><s>strike</s></button>

															<span class="dropdown">
																<button class="btn btn-bb dropdown-toggle" style="display: none;" type="button" data-toggle="dropdown" id="size">size <span class="caret"></span></button>
																<ul class="dropdown-menu">
																	<li><a style="font-size: 8px; cursor: pointer" class="size">small</a></li>
																	<li><a style="font-size: 15px; cursor: pointer" class="size">normal</a></li>
																	<li><a style="font-size: 20px; cursor: pointer" class="size">medium</a></li>
																	<li><a style="font-size: 25px; cursor: pointer" class="size">large</a></li>
																</ul>
															</span>
															<button class="btn btn-bb" type="button" id="spoiler" style="display: none;">spoiler</button> <button class="btn btn-bb" type="button" id="spoilerbox" style="display: none;">spoilerbox</button> <button class="btn btn-bb" type="button" id="url" style="display: none;">url</button> <button class="btn btn-bb" type="button" id="img" style="display: none;">img</button>

															<textarea style="width: 100%; resize: vertical; margin-bottom: 5px;" class="invis" id="textarea" rows="10"></textarea>
															<div id="aboutmetext">' . Functions::bb_parse($oUser -> aboutme) . '</div>

															<button id="btnSave" type="button" class="btn btn-success invis"><i class="fa fa-check"></i> Save</button>
															<button id="btnDiscard" type="button" class="btn btn-danger invis"><i class="fa fa-times"></i> Discard</button>
														</div>
													</div>
												</div>
											</div>

											<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
												<div class="userinfo pull-right">
													<ul style="list-style-type: none;">
														<li>
															<i class="fa fa-calendar"></i> Member since ';

															# Show since when you are a member
															function pluralize($count, $text) {
																// if $count is higher than 1 give the word an 's' (year -> years)
																return (($count == 1) ? (" $text") : (" ${text}s"));
															}

															$interval = date_create('now') -> diff(new DateTime($oUser -> joindate));

															if($interval -> y >= 1)	{
																echo $interval -> y . pluralize($interval -> y, 'year');
															}
															else {
																if($interval -> m >= 1)	{
																	echo $interval -> m . pluralize($interval -> m, 'month');
																}
																else {
																	if($interval -> d >= 1)	{
																		echo $interval -> d . pluralize($interval -> d, 'day');
																	}
																	else {
																		echo 'just now.';
																	}
																}
															}

														echo '</li>

														<li>
															<i class="fa fa-eye"></i> ' . $oUser -> pageviews . ' profile views
														</li>

														<li>
															<i class="fa fa-clock-o"></i> Last seen ';

															# Show since when you were last seen online
															$interval = date_create('now') -> diff(new DateTime($oUser -> lastseen));

															if($interval -> y >= 1)	{
																echo $interval -> y . pluralize($interval -> y, 'year') . ' ago';
															}
															else {
																if($interval -> m >= 1)	{
																	echo $interval -> m . pluralize($interval -> m, 'month') . ' ago';
																}
																else {
																	if($interval -> d >= 1)	{
																		echo $interval -> d . pluralize($interval -> d, 'day') . ' ago';
																	}
																	else {
																		if($interval -> h >= 1) {
																			echo $interval -> h . pluralize($interval -> h, 'hour') . ' ago';
																		}
																		else {
																			if($interval -> i >= 1)	{
																				echo $interval -> i . pluralize($interval -> i, 'minute') . ' ago';
																			}
																			else {
																				if($interval -> s >= 1) {
																					echo $interval -> s . pluralize($interval -> s, 'second') . ' ago';
																				}
																				else {
																					echo 'just now';
																				}
																			}
																		}
																	}
																}
															}


														echo '</li>
													</ul>';

													# if it is your userpage show the edit button
													if($arrUserData['userid'] == $User)
													{
														echo '<button id="btnEditAbout" type="button" class="btn btn-default pull-right">Edit about me</button>';
													}

												echo '</div>
											</div>
										</div>
									</div>

									<div class="tab-pane fade" id="blogs">';
										$varShowBlog = 0;
										/*
											varShowBlog == 0: Do not show
											varShowBlog == 1: Do not show > Not Mutual Friends
											varShowBlog == 2: Do not show > Profile set to Private
											varShowBlog == 3: Show the blogs
										*/

										# set the variables to know whether or not to show blogs
										if(!strcmp($oUser -> showblogs, 'friends'))
										{
											# check if both users are friends
											$qFriends = DB::getInstance() -> query('SELECT userfriend, otheruser FROM friends WHERE userfriend = ? AND otheruser = ?', array($arrUserData['userid'], $oUser -> userid));

											if($qFriends -> count() > 0)
											{
												$qFriendsMutual = DB::getInstance() -> query('SELECT userfriend, otheruser FROM friends WHERE userfriend = ? AND otheruser = ?', array($oUser -> userid, $arrUserData['userid']));

												if($qFriendsMutual -> count() > 0)
												{
													# mutual > show blogs
													$varShowBlog = 3;
												}
												else
												{
													# not mutual > don't show blogs
													$varShowBlog = 1;
												}
											}
											else
											{
												# not mutual > don't show blogs
												$varShowBlog = 1;
											}
										}
										else if(!strcmp($oUser -> showblogs, 'private'))
										{
											# Do not show the blogs
											$varShowBlog = 2;
										}
										else
										{
											# Show to everyone
											$varShowBlog = 3;
										}

										if($varShowBlog == 3 || $arrUserData['userid'] == $oUser -> userid) # Show blog
										{
											echo '<h4>All blogs from ' . $oUser -> firstname . ' ' . $oUser -> lastname . '</h4>';

											$qStickyBlogs = DB::getInstance() -> query('SELECT blogid, blogname, blogdescription FROM blog WHERE userid = ? AND sticky = 1 ORDER BY postdate DESC', array($oUser -> userid));
											$countAllBlogs = $qStickyBlogs -> count();

											foreach($qStickyBlogs -> results() as $oBlog)
											{
												echo '<a href="../blogs/' . $oBlog -> blogid . '/"><span class="blogblock"><i class="fa fa-thumb-tack pull-right" style="padding: 2px;"></i><h4>' . $oBlog -> blogname . '</h4><h6>' . $oBlog -> blogdescription . '</h6></span></a>';
											}

											$qNoStickyBlogs = DB::getInstance() -> query('SELECT blogid, blogname, blogdescription FROM blog WHERE userid = ? AND sticky = 0 ORDER BY postdate DESC', array($oUser -> userid));
											$countAllBlogs += $qNoStickyBlogs -> count();

											foreach($qStickyBlogs -> results() as $oBlog)
											{
												echo '<a href="../blogs/' . $oBlog -> blogid . '/"><span class="blogblock"><h4>' . $oBlog -> blogname . '</h4><h6>' . $oBlog -> blogdescription . '</h6></span></a>';
											}

											if($countAllBlogs == 0)
											{
												echo '<div class="recentactivity">This user has no blogs</div>';
											}
										}
										else if($varShowBlog == 2) # Do not show > Profile set to Private
										{
											echo 'The blogs on this profile are set to private and therefore you are unable to access this section. ';
										}
										else if($varShowBlog == 1) # Do not show > Not Mutual Friends
										{
											echo 'Your friendship is not mutual and therefore you are unable to access this section.';
										}
										else if($varShowBlog == 0) # Do not show
										{
											echo 'Something went wrong. ';
										}

									echo '</div>

									<div class="tab-pane fade" id="reblogs">
										<div class="recentactivity"><b>Reblogs</b></div>
											<ul>';

									# show all the reblogs and likes
									$qReblogs = DB::getInstance() -> query('SELECT blogresponse.blogid, blogname, reblog, bloglike, date FROM blogresponse, blog WHERE blogresponse.blogid = blog.blogid AND blogresponse.userid = ?', array($oUser -> userid));

									foreach($qReblogs -> results() as $oReblogs)
									{
										if($oReblogs -> reblog == 1)
										{
											echo '<li><a href="../profile/' . $oUser -> userid . '">' . $oUser -> username . '</a> reblogged the following blog: <a href="../blogs/' . $oReblogs -> blogid . '/">' . $oReblogs -> blogname . '</a> on <i>' . $oReblogs -> date . '</i></li>';
										}
									}

									echo '</ul>
										<div class="recentactivity"><b>Likes</b></div>
											<ul>';

									foreach($qReblogs -> results() as $oReblogs)
									{
										if($oReblogs -> bloglike == 1)
										{
											echo '<li><a href="../profile/' . $oUser -> userid . '">' . $oUser -> username . '</a> liked the following blog: <a href="../blogs/' . $oReblogs -> blogid . '/">' . $oReblogs -> blogname . '</a> on <i>' . $oReblogs -> date . '</i></li>';
										}
									}

									echo '</ul>
									</div>

									<div class="tab-pane fade" id="uploads">';
										$varShowUploads = 0;
										/*
											varShowUploads == 0: Do not show
											varShowUploads == 1: Do not show > Not Mutual Friends
											varShowUploads == 2: Do not show > Profile set to Private
											varShowUploads == 3: Show the blogs
										*/

										if(!strcmp($oUser -> showuploads, 'friends'))
										{
											# check if both users are friends
											$qFriends = DB::getInstance() -> query('SELECT userfriend, otheruser FROM friends WHERE userfriend = ? AND otheruser = ?', array($arrUserData['userid'], $oUser -> userid));

											if($qFriends -> count() > 0)
											{
												$qFriendsMutual = DB::getInstance() -> query('SELECT userfriend, otheruser FROM friends WHERE userfriend = ? AND otheruser = ?', array($oUser -> userid, $arrUserData['userid']));

												if($qFriendsMutual -> count() > 0)
												{
													# mutual > show blogs
													$varShowUploads = 3;
												}
												else
												{
													# not mutual > don't show blogs
													$varShowUploads = 1;
												}
											}
											else
											{
												# not mutual > don't show blogs
												$varShowUploads = 1;
											}
										}
										else if(!strcmp($oUser -> showuploads, 'private'))
										{
											# Do not show the blogs
											$varShowUploads = 2;
										}
										else
										{
											# Show to everyone
											$varShowUploads = 3;
										}

										if($varShowUploads == 3 || $arrUserData['userid'] == $oUser -> userid) # Show uploads
										{
											echo '<h4>All uploads from ' . $oUser -> firstname . ' ' . $oUser -> lastname . '</h4>';

											$folderUserUploads = "resources/uploads/" . $oUser -> username . "/";

											if(file_exists($folderUserUploads))
											{
												$qFiles = DB::getInstance() -> query('SELECT userid, uploadname, originalname FROM uploads WHERE userid = ? AND softdelete = 0', array($oUser -> userid));

												foreach($qFiles -> results() as $oFile)
												{
													$fileExtension = pathinfo($oFile -> originalname, PATHINFO_EXTENSION);

													if(in_array($fileExtension, Config::get('config/picture_extension')))
													{
														echo '<a href="' . $folderUserUploads . $oFile -> uploadname . '" title="' . $oFile -> originalname . '" class="highlightitem"><img id="' . $oFile -> uploadname . '" style="vertical-align: top;" class="upload-image highlightitem-yellow" src="' . $folderUserUploads . $oFile -> uploadname . '" /></a>';
													}
													else
													{
														echo '<a href="' . $folderUserUploads . $oFile -> uploadname . '" download="' . $oFile -> originalname . '" title="' . $oFile -> originalname . '" class="highlightitem"><div id="' . $oFile -> uploadname . '" class="upload-image highlightitem-yellow cutofftextfile" style="padding: 4px; display: inline-block;"><center><i class="fa fa-file uploadfile"></i></center><span style="font-size: 12;">' . $oFile -> originalname . '</span></div></a>';
													}
												}

												if ($qFiles -> count() <= 0) {
													echo '<div class="recentactivity">This user has no uploads</div>';
												}
											}
											else
											{
												echo '<div class="recentactivity">This user has no uploads</div>';
											}
										}
										else if($varShowUploads == 2) # Do not show > Profile set to Private
										{
											echo 'The uploads on this profile are set to private and therefore you are unable to access this section. ';
										}
										else if($varShowUploads == 1) # Do not show > Not Mutual Friends
										{
											echo 'Your friendship is not mutual and therefore you are unable to access this section.';
										}
										else if($varShowUploads == 0) # Do not show
										{
											echo 'Something went wrong. ';
										}

									echo '</div>

									<div class="tab-pane fade active" id="friends">
										<div class="row" style="margin-left: 1px;">
										<div class="mutual recentactivity" style="margin-right: 20px;"><center><b>All your friends at a quick glance</b></center></div> <div class="row-buffer-10"></div>';

										$qFriends = DB::getInstance() -> query('SELECT otheruser FROM friends WHERE userfriend = ?', array($oUser -> userid));

										foreach($qFriends -> results() as $oFriend)
										{
											$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($oFriend -> otheruser));
											$folderUserUploads = "resources/uploads/" . $qUser -> first() -> username . "/";
											$avaString = '<img src="resources/images/ava.png" />';

											if(file_exists($folderUserUploads))
											{
												$sAvatar = '@NOAVA@';

												foreach(Config::get('config/picture_extension') as $extension)
												{
													if(file_exists($folderUserUploads . 'ava.' . $extension))
													{
														$sAvatar = $folderUserUploads . 'ava.' . $extension;
													}
												}

												if(strcmp($sAvatar, '@NOAVA@'))
												{
													$avaString = '<img src="' . $sAvatar . '" style="height: 70px; padding-top: 7px;" />';
												}
												else
												{
													$avaString = '<img src="resources/images/ava.png" style="height: 70px; padding-top: 7px;" />';
												}
											}
											else
											{
												$avaString = '<img src="resources/images/ava.png" style="height: 70px; padding-top: 7px;" />';
											}
											// col-xs-12 col-sm-4 col-md-4 col-lg-4
											echo '<a href="../profile/' . $oFriend -> otheruser . '"><div class="friendtile">' . $avaString . ' ' . $qUser -> first() -> username . '<span class="pull-right" style="padding-top: 33px;"><i class="fa fa-heart" style="color: #FF5600;"></i></span></div></a>';
										}

										echo '<!--<div class="row-buffer-10"></div><div class="mutual recentactivity" style="margin-right: 20px;">Non-Mutual friends</div> <div class="row-buffer-10"></div>-->';
										$qFriends = DB::getInstance() -> query('SELECT userfriend FROM friends WHERE otheruser = ?', array($oUser -> userid));

										foreach($qFriends -> results() as $oFriend)
										{
											$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($oFriend -> userfriend));
											$folderUserUploads = "resources/uploads/" . $qUser -> first() -> username . "/";
											$avaString = '<img src="resources/images/ava.png" />';

											if(file_exists($folderUserUploads))
											{
												$sAvatar = '@NOAVA@';

												foreach(Config::get('config/picture_extension') as $extension)
												{
													if(file_exists($folderUserUploads . 'ava.' . $extension))
													{
														$sAvatar = $folderUserUploads . 'ava.' . $extension;
													}
												}

												if(strcmp($sAvatar, '@NOAVA@'))
												{
													$avaString = '<img src="' . $sAvatar . '" style="height: 70px; padding-top: 7px;" />';
												}
												else
												{
													$avaString = '<img src="resources/images/ava.png" style="height: 70px; padding-top: 7px;" />';
												}
											}
											else
											{
												$avaString = '<img src="resources/images/ava.png" style="height: 70px; padding-top: 7px;" />';
											}

											echo '<a href="../profile/' . $oFriend -> userfriend . '"><div class="friendtile">' . $avaString . ' ' . $qUser -> first() -> username . '<span class="pull-right" style="padding-top: 33px;"><i class="fa fa-heart" style="color: green;"></i></span></div></a>';
										}

										echo '</div>
									</div>
								</div>';
							}
						}
					}
				?>
			</div>
		</div>

		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/sidebar.js"></script>

		<script>
			$(document).ready(function(){
				function showBB(bbstate){
					if(bbstate == true)
					{
						$("#bold").show();
						$("#italic").show();
						$("#underline").show();
						$("#strike").show();
						$("#size").show();
						$("#spoiler").show();
						$("#spoilerbox").show();
						$("#url").show();
						$("#img").show();
					}
					else
					{
						$("#bold").hide();
						$("#italic").hide();
						$("#underline").hide();
						$("#strike").hide();
						$("#size").hide();
						$("#spoiler").hide();
						$("#spoilerbox").hide();
						$("#url").hide();
						$("#img").hide();
					}
				}

				$("#files").on('change', function(){
					$("#formsubmit").submit();
				});

				$("#addfriend").on('click', function(){
					$.ajax({
						type: "POST",
						url: "../profile_ajax_friend.php",
						dataType: "html",
						data: {
							userid : <?php echo isset($_GET['u']) && $_GET['u'] != 0 ? json_encode($_GET['u']) : 'N/A'; ?>
						},
						success: function(evt) {
							alert(evt);
						},
						complete: function(){
							window.location.reload(true);
						}
					})
				});

				var $state = 0;
				$("#btnEditAbout").on('click', function(){
					if($state == 0)
					{
						$("#btnSave").show();
						$("#btnDiscard").show();
						$("#textarea").show();
						showBB(true);

						$("#aboutmetext").hide();

						$.ajax({
							type: "POST",
							url: "../profile_ajax.php",
							dataType: "html",
							data: {
								user : <?php echo json_encode($arrUserData['userid']); ?>, type : "retrievetext"
							},
							success: function(evt){
								$("#textarea").text(evt);
							}
						})
					}
					else
					{
						$("#btnSave").hide();
						$("#btnDiscard").hide();
						$("#textarea").hide();
						showBB(false);
						$("#aboutmetext").show();
					}

					$state = !$state;
				});

				$("#btnSave").on('click', function(){
					$.ajax({
						type: "POST",
						url: "../profile_ajax.php",
						dataType: "html",
						data: {
							user: <?php echo json_encode($arrUserData['userid']); ?>, aboutmetext : $("#textarea").val(), type: "save"
						},
						complete: function(evt){
							$("#btnSave").hide();
							$("#btnDiscard").hide();
							$("#textarea").hide();
							showBB(false);
							$("#aboutmetext").html(evt.responseText);
							$("#aboutmetext").show();

							$state = !$state;
						},
						success: function()
						{
							$("#saved").show();
							setTimeout(function(){
								$("#saved").hide();
							}, 3000);
						}
					})
				});

				$("#btnDiscard").on('click', function(){
					$("#btnSave").hide();
					$("#btnDiscard").hide();
					$("#textarea").hide();
					showBB(false);
					$("#aboutmetext").show();

					$state = !$state;
				});

				$("#usersearch").on('input', function(){
					if($("#usersearch").length > 0)
					{
						var matcher = new RegExp($(this).val(), 'gi');

						$('.usersearch').show().not(function(){
							return matcher.test($(this).find('.searchusername').text())
						}).hide();
					}
				});

				function getSelectionText() {
				    var text = "";
				    if (window.getSelection) {
				        text = window.getSelection().toString();
				    } else if (document.selection && document.selection.type != "Control") {
				        text = document.selection.createRange().text;
				    }
				    return text;
				}

				String.prototype.replaceBetween = function(start, end, what) {
				    return this.substring(0, start) + what + this.substring(end);
				};

				$("#bold").on('mousedown', function(evt){
					evt.preventDefault();

					var textarea = document.getElementById("textarea");

					if($("#textarea").is(":focus"))
					{
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[b]' + select + '[/b]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$("#italic").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#textarea").is(":focus"))
					{
						var textarea = document.getElementById("textarea");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[i]' + select + '[/i]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$("#underline").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#textarea").is(":focus"))
					{
						var textarea = document.getElementById("textarea");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[u]' + select + '[/u]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$("#strike").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#textarea").is(":focus"))
					{
						var textarea = document.getElementById("textarea");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[strike]' + select + '[/strike]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$(".size").on('mousedown', function(evt){
					evt.preventDefault();

					var textarea = document.getElementById("textarea");
					var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
					var fontsize = 8;

					switch($(evt.target).text())
					{
						case 'small':
							fontsize = 8;
							break;

						case 'normal':
							fontsize = 15;
							break;

						case 'medium':
							fontsize = 20;
							break;

						case 'large':
							fontsize = 25;
							break;

						default:
							fontsize = 8;
							break;
					}

					var replace = '[size=' + fontsize + ']' + select + '[/size]';

					textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
				});

				$("#spoiler").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#textarea").is(":focus"))
					{
						var textarea = document.getElementById("textarea");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[spoiler]' + select + '[/spoiler]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$("#spoilerbox").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#textarea").is(":focus"))
					{
						var textarea = document.getElementById("textarea");

						if(textarea.selectionStart == textarea.selectionEnd)
						{
							var caption = prompt('Please enter the spoilerbox caption', 'caption');

							if(caption != null)
							{
								var data = prompt('Please enter the data you want to show', 'data');

								if(data != null)
								{
									var replace = '[spoilerbox=' + caption + ']' + data + '[/spoilerbox]';

									textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
								}
							}
						}
						else
						{
							var textarea = document.getElementById("textarea");
							var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
							var replace = '[spoilerbox]' + select + '[/spoilerbox]';

							textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
						}
					}
				});

				$("#url").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#textarea").is(":focus"))
					{
						var textarea = document.getElementById("textarea");

						if(textarea.selectionStart == textarea.selectionEnd)
						{
							var url = prompt('Please enter the URL', 'url');

							if(url != null)
							{
								var description = prompt('Please enter a description', 'description');

								if(description != null)
								{
									var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
									var replace = '[url=' + url + ']' + description + '[/url]';

									textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
								}
							}
						}
						else
						{
							var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
							var replace = '[url]' + select + '[/url]';

							textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
						}
					}
				});

				$("#img").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#textarea").is(":focus"))
					{
						var textarea = document.getElementById("textarea");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[img]' + select + '[/img]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});
			});
		</script>
	</body>
</html>
