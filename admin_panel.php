<?php
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "adminpanel");
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

		<title>Settings &ndash; Portfolio</title>
	</head>

	<body>
		<?php include_once 'resources/includes/navbar.php'; ?>
		<?php $includePageActive = "admin"; include_once 'resources/includes/sidebar.php'; ?>

		<div class="content">
			<div class="container-fluid">
				<?php
					$Window = isset($_GET['w']) ? $_GET['w'] : '';

					if(!strcmp($Window, ""))
					{
						echo '<ol class="breadcrumb">
							<li><a href="../">Home</a></li>
							<li class="active">Admin Panel</li>
						</ol>';
					}
					else if(!strcmp($Window, "access"))
					{
						echo '<ol class="breadcrumb">
							<li><a href="../">Home</a></li>
							<li><a href="../admin">Admin Panel</a></li>
							<li class="active">Page accessibility</li>
						</ol>';
					}
					else if(!strcmp($Window, "users"))
					{
						echo '<ol class="breadcrumb">
							<li><a href="../">Home</a></li>
							<li><a href="../admin">Admin Panel</a></li>
							<li class="active">Manage Users</li>
						</ol>';
					}
					else if(!strcmp($Window, "groups"))
					{
						echo '<ol class="breadcrumb">
							<li><a href="../">Home</a></li>
							<li><a href="../admin">Admin Panel</a></li>
							<li class="active">Groups</li>
						</ol>';
					}
					else if(!strcmp($Window, "uploadcenter"))
					{
						echo '<ol class="breadcrumb">
							<li><a href="../">Home</a></li>
							<li><a href="../admin">Admin Panel</a></li>
							<li class="active">Upload center</li>
						</ol>';
					}
					else if(!strcmp($Window, "cookies"))
					{
						echo '<ol class="breadcrumb">
							<li><a href="../">Home</a></li>
							<li><a href="../admin">Admin Panel</a></li>
							<li class="active">Cookies</li>
						</ol>';
					}
				?>

				<div class="row">
					<div class="col-xs-4">
						<a href="../admin/access/" class="thumbnail h4" align="center">
							<h1><i class="fa fa-unlock-alt"></i></h1>
							<p>Page accessibility</p>
						</a>
					</div>

					<div class="col-xs-4">
						<a href="../admin/users/" class="thumbnail h4" align="center">
							<h1><i class="fa fa-user-plus"></i></h1>
							<p>Manage users</p>
						</a>
					</div>

					<div class="col-xs-4">
						<a href="../admin/groups/" class="thumbnail h4" align="center">
							<h1><i class="fa fa-users"></i></h1>
							<p>Groups</p>
						</a>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-4">
						<a href="../admin/uploadcenter/" class="thumbnail h4" align="center">
							<h1><i class="fa fa-users"></i></h1>
							<p>Upload center</p>
						</a>
					</div>

					<div class="col-xs-4">
						<a href="../admin/cookies/" class="thumbnail h4" align="center">
							<h1><i class="fa fa-desktop"></i></h1>
							<p>Cookies</p>
						</a>
					</div>
				</div>

				<div class="row" id="errormessage">
					<?php
						$iGroupId = 	isset($_POST['creategroupid']) ? $_POST['creategroupid'] : '';
						$sGroupName = 	isset($_POST['creategroupname']) ? $_POST['creategroupname'] : '';
						$User = isset($_GET['u']) ? $_GET['u'] : '';
						$sError_Message = '';

						if($_SERVER['REQUEST_METHOD'] == 'POST')
						{
							# Show different content: for Groups
							if(!strcmp($Window, "groups"))
							{
								if(strlen($iGroupId) <= 0 || strlen($iGroupId) >= 6 || !is_numeric($iGroupId))
								{
									$sError_Message .= '"Group id" does not meet the requirements. Minimum length: 1. Maximum length: 5 <br />';
								}

								if(strlen($sGroupName) <= 0 || strlen($sGroupName) >= 20)
								{
									$sError_Message .= '"Group name" does not meet the requirements. Minimum value: 1 <br />';
								}

								$qGroup = DB::getInstance() -> query('SELECT groupid FROM groups WHERE groupid = ? OR groupname = ?', array($iGroupId, $sGroupName));
								if($qGroup -> count() > 0)
								{
									$sError_Message .= 'Either the group id or group name already exist. Check below to see what id and names are already in use. <br />';
								}

								# If there are any errors show the error message, otherwise create the group
								if(strlen($sError_Message) > 0)
								{
									echo '<div class="alert alert-danger alert-dismissable col-xs-10 col-xs-offset-1 col-md-7 col-md-offset-3"><button type="button" class="close" data-dismiss="alert">×</button>' . $sError_Message . '</div>';
								}
								else
								{
									DB::getInstance() -> query('INSERT INTO groups VALUES(?, ?)', array($iGroupId, $sGroupName));
									echo '<div class="alert alert-success alert-dismissable col-xs-10 col-xs-offset-1 col-md-7 col-md-offset-3"><button type="button" class="close" data-dismiss="alert">×</button>The group "' . $sGroupName . '" has been succesfully created.</div>';
								}
							}
							else if(!strcmp($Window, "users")) # Show different content: for Users
							{
								$sUsername		= isset($_POST['username']) ? $_POST['username'] : '';
								$sFirstName 	= isset($_POST['firstname']) ? $_POST['firstname'] : '';
								$sLastName		= isset($_POST['lastname']) ? $_POST['lastname'] : '';
								$sEmail			= isset($_POST['email']) ? $_POST['email'] : '';
								$sInterests		= isset($_POST['interests']) ? $_POST['interests'] : '';
								$iGroup			= isset($_POST['group']) ? $_POST['group'] : '';
								$sAboutMe		= isset($_POST['aboutme']) ? $_POST['aboutme'] : '';
								$sErrorMessage 	= "";

								$qValidationUsername = DB::getInstance() -> query('SELECT username FROM users WHERE username = ?', array($sUsername));
								if($qValidationUsername -> count() > 0)
								{
									if(strcmp($sUsername, $qValidationUsername -> first() -> username))
									{
										$sErrorMessage .= "The username \"" . $sUsername . "\" is already in use <br />";
									}
								}

								$qValidationEmail = DB::getInstance() -> query('SELECT email FROM users WHERE email = ?', array($sEmail));
								if($qValidationEmail -> count() > 0)
								{
									if(strcmp($sEmail, $qValidationEmail -> first() -> email))
									{
										$sErrorMessage .= "The email \"" . $sEmail . "\" is already in use <br />";
									}
								}

								if(strlen($sFirstName) < Config::get('config/validation/namemin') || strlen($sFirstName) > Config::get('config/validation/namemax'))
								{
									$sErrorMessage .= "\"First name\" does not meet the requirements. Minimum length: " . Config::get('config/validation/namemin') . ". Maximum length: " . Config::get('config/validation/namemax') . "<br />";
								}

								if(strlen($sLastName) < Config::get('config/validation/namemin') || strlen($sLastName) > Config::get('config/validation/namemax'))
								{
									$sErrorMessage .= "\"Last name\" does not meet the requirements. Minimum length: " . Config::get('config/validation/namemin') . ". Maximum length: " . Config::get('config/validation/namemax') . "<br />";
								}

								if(strlen($sUsername) < Config::get('config/validation/namemin') || strlen($sUsername) > Config::get('config/validation/namemax'))
								{
									$sErrorMessage .= "\"Username\" does not meet the requirements. Minimum length: " . Config::get('config/validation/namemin') . ". Maximum length: " . Config::get('config/validation/namemax') . "<br />";
								}

								if(!filter_var($sEmail, FILTER_VALIDATE_EMAIL))
								{
									$sErrorMessage .= "\"E-mail\" is considered invalid <br />";
								}

								# Check if there is any error, if not update the user
								if(strlen($sErrorMessage) > 0)
								{
									echo '<div class="alert alert-danger alert-dismissable col-md-4 col-md-offset-4"><button type="button" class="close" data-dismiss="alert">×</button>' . $sErrorMessage . '</div>';
								}
								else
								{
									if(isset($_SESSION['edituser']))
									{
										if(is_numeric($_SESSION['edituser']))
										{
											$updateValues = array('username' => $sUsername, 'firstname' => $sFirstName, 'lastname' => $sLastName,
																	'email' => $sEmail, 'interests' => $sInterests, 'groupid' => $iGroup, 'aboutme' => $sAboutMe);
											User::Update($updateValues, $_SESSION['edituser']);

											echo '<div class="alert alert-success alert-dismissable col-xs-10 col-xs-offset-1 col-md-7 col-md-offset-3"><button type="button" class="close" data-dismiss="alert">×</button>You have succesfully changed the data of "' . $sUsername . '"</div>';
										}
										else
										{
											echo '<div class="alert alert-danger alert-dismissable col-md-4 col-md-offset-4"><button type="button" class="close" data-dismiss="alert">×</button>Something went wrong, please try again. </div>';
										}
									}
									else
									{
										echo '<div class="alert alert-danger alert-dismissable col-md-4 col-md-offset-4"><button type="button" class="close" data-dismiss="alert">×</button>Something went wrong, please try again. </div>';
									}
								}
							}
						}
					?>
				</div>

				<div class="row">
					<div class="col-xs-12">
						<div class="panel panel-default">
							<div class="panel-body">
								<?php
									if(!strcmp($Window, "access"))
									{
										User::MiniAuthRedirect("pageaccessibility", $arrUserData, "../admin");

										echo '<table class="table table-striped">
											<thead>
												<tr>
													<td>Access name</td>
													<td>Access permission</td>
													<td>Need to be authenticated</td>
												</tr>
											</thead>

											<tbody>
												<tr>
													<td>Main</td>
													<td>
														<select id="selectgroupmain" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "main"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "main"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogmain">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>

												<tr>
													<td>News</td>
													<td>
														<select id="selectgroupnews" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "news"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "news"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlognews">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>

												<tr>
													<td>Blog</td>
													<td>
														<select id="selectgroupblog" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "blog"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "blog"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogblog">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>

												<tr>
													<td>Edit a blog</td>
													<td>
														<select id="selectgroupeditblog" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "editblog"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "editblog"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogeditblog">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>

												<tr>
													<td>Create a blog entry</td>
													<td>
														<select id="selectgroupcreateblog" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "makeblog"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "makeblog"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogcreateblog">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>

												<tr>
													<td>Documents</td>
													<td>
														<select id="selectgroupdocument" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "document"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "document"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogdocument">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>

												<tr>
													<td>Upload center</td>
													<td>
														<select id="selectgroupuploadcenter" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "uploadcenter"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "uploadcenter"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectloguploadcenter">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>

												<tr>
													<td>Profiles</td>
													<td>
														<select id="selectgroupprofiles" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "profiles"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "profiles"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogprofiles">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>

												<tr>
													<td>Messages</td>
													<td>
														<select id="selectgroupmessages" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "message"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "message"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogmessages">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>
											</tbody>

											<tbody>
												<tr>
													<td>Changelog</td>
													<td>
														<select id="selectgroupchangelog" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "changelog"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "changelog"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogchangelog">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>

												<tr>
													<td>Make changes to the changelog</td>
													<td>
														<select id="selectgroupeditchangelog" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "editchangelog"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "editchangelog"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogeditchangelog">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>
											</tbody>

											<tbody>
												<tr>
													<td>Admin panel</td>
													<td>
														<select id="selectgroupadminpanel" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "adminpanel"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "adminpanel"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogadminpanel">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>

												<tr>
													<td>Page accessibility</td>
													<td>
														<select id="selectgrouppageaccessibility" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "pageaccessibility"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "pageaccessibility"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogpageaccessibility">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>

												<tr>
													<td>Edit users</td>
													<td>
														<select id="selectgroupmanageusers" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "manageusers"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "manageusers"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogmanageusers">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>

												<tr>
													<td>Edit groups</td>
													<td>
														<select id="selectgroupmanagegroups" class="form-control select-theme">';
															$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

															foreach($qGroups -> results() as $oGroup)
															{
																$qPermission = DB::getInstance() -> query('SELECT groupid FROM accessibility WHERE pagename = "managegroups"');

																if(!strcmp($qPermission -> first() -> groupid, $oGroup -> groupid))
																{
																	echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
																}
																else
																{
																	echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
																}
															}
														echo '</select>
													</td>
													<td>';
														$qLoggedIn = DB::getInstance() -> query('SELECT loggedin FROM accessibility WHERE pagename = "managegroups"');

														$iLogNo = ($qLoggedIn -> first() -> loggedin) == 0 ? "selected" : "";
														$iLogYes = ($qLoggedIn -> first() -> loggedin) == 1 ? "selected" : "";
														echo '<select class="form-control select-theme" id="selectlogmanagegroups">
															<option value="0" ' . $iLogNo . '>No</option>
															<option value="1" ' . $iLogYes . '>Yes</option>
														</select>
													</td>
												</tr>
											</tbody>
										</table>';
									}
									else if(!strcmp($Window, "users"))
									{
										# Search for an user so you can edit their settings
										User::MiniAuthRedirect("manageusers", $arrUserData, "../admin");

										echo '<div class="row">
											<div class="col-xs-12 col-md-6 col-md-offset-3">
												<div class="panel panel-default">
													<div class="panel-body">
														<p><b>Please enter an username or userid to search for an user</b></p>
														<input type="text" id="usersearch" class="form-control" name="u" placeholder="username/userid" value="' . $User . '" autofocus /> <br />
													</div>
												</div>
											</div>
										</div>
										<div class="row">
											<div class="col-xs-12">
												<div class="panel panel-default">
													<div class="panel-body" id="UserPage">

													</div>
												</div>
											</div>
										</div>';
									}
									else if(!strcmp($Window, "groups"))
									{
										# Show all the groups so you can edit those settings
										User::MiniAuthRedirect("managegroups", $arrUserData, "../admin");

										echo '<form action="../admin/groups/" method="post" autocomplete="on">
												<table class="table table-striped">
													<thead>
														<td>Group id</td><td>Group name</td>
													</thead>

													<tbody>
														<tr>
															<td><input type="number" name="creategroupid" class="form-control" placeholder="groupid" value="' . $iGroupId . '" /></td>
															<td><input type="text" name="creategroupname" class="form-control" placeholder="groupname" value="' . $sGroupName . '" /></td>
														</tr>

														<tr>
															<td></td><td><button id="submit" type="submit" class="btn btn-default pull-right">Create</button></td>
														</tr>
													</tbody>
												</table>
											</form>';

										$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

										echo '<table class="table table-striped"><thead><td class="col-xs-5 col-md-4">Group id</td><td class="col-xs-5 col-md-4">Group name</td><td class="col-xs-1 col-md-1"> </td></thead><tbody id="groups">';

										foreach($qGroups -> results() as $oGroup)
										{
											echo '<tr>
													<td><input type="number" id="groupid' . $oGroup -> groupid .'" class="form-control" value="' . $oGroup -> groupid . '" placeholder="groupid" disabled /></td>
													<td>
														<input type="text" id="groupname' . $oGroup -> groupid . '" class="form-control" value="' . $oGroup -> groupname . '" placeholder="groupname" disabled />
													</td>

													<td>
														<div class="pull-right">
															<a class="edit" id="edit' . $oGroup -> groupid . '"><i class="fa fa-pencil-square-o"></i></a>
															<a class="edit hidden-xs" id="delete' . $oGroup -> groupid . '"><i class="fa fa-trash-o"></i></a>
														</div>
													</td>
												</tr>';
										}

										echo '</tbody></table>';
									}
									else if(!strcmp($Window, "uploadcenter"))
									{
										# Type an username to search their uploads
										User::MiniAuthRedirect("edituploadcenter", $arrUserData, "../admin");

										$User = isset($_GET['u']) ? $_GET['u'] : $_GET['u'];

										echo '<div class="row">
											<div class="col-xs-12 col-md-6 col-md-offset-3">
												<div class="panel panel-default">
													<div class="panel-body">
														<p><b>Please enter an username the files that the user has uploaded</b></p>

														<form id="searchUserForm" action="../admin/uploadcenter/" method="get">
															<input type="text" id="usersearchupload" class="form-control" name="u" placeholder="username" value="' . $User . '" autofocus />
															<div class="row-buffer-10"></div>
															<button type="submit" class="btn btn-success pull-right">Search</button>
														</form>
													</div>
												</div>
											</div>
										</div>

										<div class="row">
											<div class="col-xs-12">
												<div class="panel panel-default">
													<div class="panel-body" id="UserPageUploads">';
														if(strlen($User) > 0)
														{
															$qUser = DB::getInstance() -> query('SELECT userid, username FROM users WHERE username = ?', array($User));

															if($qUser -> count() > 0)
															{
																foreach($qUser -> results() as $oUser)
																{
																	$folderUserUploads = "resources/uploads/" . $oUser -> username . "/";

																	if(file_exists($folderUserUploads))
																	{
																		echo '<button type="button" class="btn btn-default" id="btnSelect"><i class="fa fa-check-square-o"></i> <span id="btnSelectText">Select</span></button>
																		<button type="button" class="btn btn-danger" id="btnDelete"><i class="fa fa-trash-o"></i> Delete <span id="btnDeleteAmount"></span> <i id="refresher" class="fa fa-refresh fa-spin" style="display: none;"></i></button>

																		<div class="row-buffer-10"></div>';

																		$qFiles = DB::getInstance() -> query('SELECT uploadname, originalname FROM uploads WHERE userid = ?', array($oUser -> userid));
																		foreach($qFiles -> results() as $oFile)
																		{
																			$fileExtension = pathinfo($oFile -> originalname, PATHINFO_EXTENSION);

																			if(in_array($fileExtension, Config::get('config/picture_extension')))
																			{
																				echo '<a href="' . $folderUserUploads . $oFile -> uploadname . '" title="' . $oFile -> originalname . '" class="highlightitem"><img id="' . $oFile -> uploadname . '" style="vertical-align: top;" class="upload-image" src="' . $folderUserUploads . $oFile -> uploadname . '" /></a>';
																			}
																			else if(!strcmp($fileExtension, 'mp3') || !strcmp($fileExtension, 'mp4'))
																			{
																				echo '<a href="' . $folderUserUploads . $oFile -> uploadname . '" download="' . $oFile -> originalname . '" title="' . $oFile -> originalname . '" class="highlightitem"><div id="' . $oFile -> uploadname . '" class="upload-image cutofftextfile" style="padding: 4px; display: inline-block;"><center><i class="fa fa-music uploadfile"></i></center><span style="font-size: 12;">' . $oFile -> originalname . '</span></div></a>';
																			}
																			else
																			{
																				echo '<a href="' . $folderUserUploads . $oFile -> uploadname . '" download="' . $oFile -> originalname . '" title="' . $oFile -> originalname . '" class="highlightitem"><div id="' . $oFile -> uploadname . '" class="upload-image cutofftextfile" style="padding: 4px; display: inline-block;"><center><i class="fa fa-file uploadfile"></i></center><span style="font-size: 12;">' . $oFile -> originalname . '</span></div></a>';
																			}
																		}
																	}
																	else
																	{
																		echo '<div class="alert alert-danger alert-dismissable col-xs-10 col-xs-offset-1 col-md-7 col-md-offset-3"><button type="button" class="close" data-dismiss="alert">×</button> "' . $User . '" has no files uploaded.</div>';
																	}
																}
															}
															else
															{
																echo '<div class="alert alert-danger alert-dismissable col-xs-10 col-xs-offset-1 col-md-7 col-md-offset-3"><button type="button" class="close" data-dismiss="alert">×</button> "' . $User . '" could not be found.</div>';
															}
														}
														else
														{
															if(strlen($User) != 0)
																echo '<div class="alert alert-danger alert-dismissable col-xs-10 col-xs-offset-1 col-md-7 col-md-offset-3"><button type="button" class="close" data-dismiss="alert">×</button> You need to fill in atleast 1 character to be able to find an user.</div>';
														}
													echo '</div>
												</div>
											</div>
										</div>';
									}
									else if(!strcmp($Window, "cookies"))
									{
										# Show ALL cookies so you can manually let them expire
										echo '<table class="table table-striped">
											<thead>
												<th>Cookie id</th>
												<th>Username</th>
												<th>Expire date</th>
												<th>Name of the host device</th>
												<th>Action</th>
											</thead>

											<tbody>';
										$qCookies = DB::getInstance() -> query('SELECT cookieid, userid, date, hostname FROM cookies');

										foreach($qCookies -> results() as $oCookie)
										{
											$qUsername = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($oCookie -> userid));

											echo '<tr class="deletecookie' . $oCookie -> cookieid . '">
												<td>' . $oCookie -> cookieid . '</td>
												<td><a href="../profile/' . $oCookie -> userid . '"><span style="color: yellow !important;">' . $qUsername -> first() -> username . '</span></a></td>
												<td>' . date("d-m-Y", strtotime($oCookie -> date . " +1 month")) . '</td>
												<td>' . $oCookie -> hostname . '</td>
												<td>
													<button id="deletecookie' . $oCookie -> cookieid . '" type="button" class="btn btn-danger">Delete this</button>
													<button id="extendcookie' . $oCookie -> cookieid . '" type="button" class="btn btn-success">Extend this</button>
												</td>
											</tr>';
										}

										echo '</tbody></table>';
									}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/sidebar.js"></script>

		<script>
			$(document).ready(function(){
				$("#searchUserForm").submit(function(evt){
					evt.preventDefault();

					window.location.replace('../admin/uploadcenter/' + $("#usersearchupload").val());
				});

				var $btnSelect = 0;
				var $amountImageSelected = 0;

				$("#btnSelect").on('click', function(){
					$btnSelect = !$btnSelect;

					if($btnSelect)
					{
						$("#btnSelectText").text('Cancel');
					}
					else
					{
						$("#btnSelectText").text('Select');
						$(".upload-image").removeClass("upload-image-selected");
					}
				});

				$(".highlightitem").on('click', function(e){
					if($btnSelect == 1)
					{
						e.preventDefault();
						if($(this).children().hasClass('upload-image-selected'))
						{
							$(this).children().removeClass('upload-image-selected');
							$amountImageSelected --;
							$("#btnDeleteAmount").text('(' + $amountImageSelected + ')');
						}
						else
						{
							$(this).children().addClass('upload-image-selected');

							$amountImageSelected ++;
							$("#btnDeleteAmount").text('(' + $amountImageSelected + ')');
						}
					}
				});

				$("#btnDelete").on('click', function(){
					var $dataArray = [];

					$(".upload-image-selected").each(function(){
						$dataArray.push(this.id);
					});

					$.ajax({
						type: "POST",
						url: "../delete_upload.php",
						dataType: "html",
						data: {
							delete : $dataArray
						},
						beforeSend: function(response){
							$("#refresher").show();
						},
						complete: function(){
							location.reload();
						}
					})
				});

				$("#usersearch").on('input focus', function(){
					if($("#usersearch").val().length > 0)
					{
						$.ajax({
							type: "POST",
							url: "../admin_panel_user_edit.php",
							dataType: "html",
							data: { user : $("#usersearch").val() },
							success: function(response){
								$("#UserPage").html(response);
							}
						})
					}
				});

				$("#selectgroupmain, #selectgroupnews, #selectgroupblog, #selectgroupeditblog, #selectgroupdocument, #selectgroupuploadcenter, #selectgroupprofiles, #selectgroupmessages, #selectgroupchangelog, #selectgroupeditchangelog, #selectgroupadminpanel, #selectgrouppageaccessibility, #selectgroupmanageusers, #selectgroupmanagegroups, #selectgroupcreateblog").on('change', function(){
					$.ajax({
						type: "POST",
						url: "../admin_panel_access_edit.php",
						dataType: "html",
						data: { changeid : $(this).attr('id'), changevalue : $(this).val(), group : 1 },
					})
				});

				$("#selectlogmain, #selectlognews, #selectlogblog, #selectlogeditblog, #selectlogdocument, #selectloguploadcenter, #selectlogprofiles, #selectlogmessages, #selectlogchangelog, #selectlogeditchangelog, #selectlogadminpanel, #selectlogpageaccessibility, #selectlogmanageusers, #selectlogmanagegroups, #selectlogcreateblog").on('change', function(){
					$.ajax({
						type: "POST",
						url: "../admin_panel_access_edit.php",
						dataType: "html",
						data: { changeid : $(this).attr('id'), changevalue : $(this).val(), needlog : 1 },
					})
				});

				<?php
					$qGroups = DB::getInstance() -> query("SELECT groupid, groupname FROM groups");

					foreach($qGroups -> results() as $iGroupId)
					{
						echo 'var $var' . $iGroupId -> groupid . ' = 0;
						$("#edit' . $iGroupId -> groupid . '").on("click", function(){
							if($var' . $iGroupId -> groupid . ' == 1)
							{
								$.ajax({
									type: "POST",
									url: "../admin_panel_group_edit.php",
									dataType: "html",
									data: { edit : 1, oldgroupid : ' . $iGroupId -> groupid . ', groupid : $("#groupid' . $iGroupId -> groupid . '").val(), groupname : $("#groupname' . $iGroupId -> groupid . '").val() },
									success: function(response){
										$("#errormessage").html(response);
									}
								})
							}

							if($var' . $iGroupId -> groupid . ' == 0)
							{
								$("#groupid' . $iGroupId -> groupid . '").removeAttr("disabled");
								$("#groupname' . $iGroupId -> groupid . '").removeAttr("disabled");
								$var' . $iGroupId -> groupid . ' = 1;
							}
							else
							{
								$("#groupid' . $iGroupId -> groupid . '").attr("disabled", "disabled");
								$("#groupname' . $iGroupId -> groupid . '").attr("disabled", "disabled");
								$var' . $iGroupId -> groupid . ' = 0;
							}
						});';

						echo '$("#delete' . $iGroupId -> groupid . '").on("click", function(){
							if(confirm("Are you sure you want to delete the role \"" + $("#groupname' . $iGroupId -> groupid . '").val() + "\"?"))
							{
								$.ajax({
									type: "POST",
									url: "../admin_panel_group_edit.php",
									dataType: "html",
									data: { delete : 1, deletegroupid : ' . $iGroupId -> groupid . ', deletegroupname : "' . $iGroupId -> groupname . '" },
									success: function(response){
										$("#errormessage").html(response);
									}
								})
							}
						});';
					}

					$qCookies = DB::getInstance() -> query("SELECT cookieid FROM cookies");

					foreach($qCookies -> results() as $oCookie)
					{
						echo '$("#deletecookie' . $oCookie -> cookieid . '").on("click", function(){
							$.ajax({
								type: "POST",
								url: "../mycookies_ajax.php",
								dataType: "html",
								data: { userid: ' . $arrUserData['userid'] . ', cookieid: ' . $oCookie -> cookieid . ', action: "delete" },
								success: function(response) {
									$(".deletecookie' . $oCookie -> cookieid . '").fadeOut("fast");
									alert(response);
								}
							})
						});

						$("#extendcookie' . $oCookie -> cookieid . '").on("click", function(){
							$.ajax({
								type: "POST",
								url: "../mycookies_ajax.php",
								dataType: "html",
								data: { userid: ' . $arrUserData['userid'] . ', cookieid: ' . $oCookie -> cookieid . ', action: "extend" },
								success: function(response) {
									alert(response);
									location.reload();
								}
							})
						});';
					}
				?>
			});
		</script>
	</body>
</html>
