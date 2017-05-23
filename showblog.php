<?php
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "blog");
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

		<title>Blog &ndash; Portfolio</title>
	</head>

	<body>
		<?php include_once 'resources/includes/navbar.php'; ?>
		<?php $includePageActive = "blog"; include_once 'resources/includes/sidebar.php'; ?>

		<div class="content">
			<div class="container-fluid">
				<?php
					$sId = isset($_GET['b']) ? $_GET['b'] : '';
					$sAction = isset($_GET['action']) ? $_GET['action'] : '';

					if(!strcmp($sAction, 'edit')) # Edit a blog instead of showing it
					{
						if(is_numeric($sId)) # See if id is numeric
						{
							echo '<div id="blogtable" class = "blogtable" style="padding: 4px;">';

							$qBlog = DB::getInstance() -> query('SELECT blogid, userid, blogname, blogdescription, message, sticky, postdate FROM blog WHERE blogid = ?', array($sId));

							if($qBlog -> count() > 0) # Does the blog exist
							{
								foreach($qBlog -> results() as $oBlog)
								{
									# get the information from the blog
									if($oBlog -> userid == $arrUserData['userid'] || User::MiniAuth('editblog', $arrUserData))
									{
										if($_SERVER['REQUEST_METHOD'] == 'POST')
										{
											$sBlogName			= isset($_POST['blogname']) ? $_POST['blogname'] : '';
											$sBlogDescription	= isset($_POST['blogdescription']) ? $_POST['blogdescription'] : '';
											$sBlogMessage		= isset($_POST['blogmessage']) ? $_POST['blogmessage'] : '';
											$iBlogSticky		= isset($_POST['blogsticky']) ? '1' : '';
											$iSticky 			= ($oBlog -> sticky == 1) ? 'checked' : '';

											# validations
											$sError_Message = '';
											if(strlen($sBlogName) <= 4 || strlen($sBlogName) >= 51)
											{
												$sError_Message .= '"Blog name" does not meet the requirements. Minimum length: 5, Maximum length: 50 <br />';
											}

											if(strlen($sBlogDescription) <= 4 || strlen($sBlogDescription) >= 51)
											{
												$sError_Message .= '"Blog description" does not meet the requirements. Minimum length: 5, maximum length: 50 <br />';
											}

											if(strlen($sBlogMessage) <= 4)
											{
												$sError_Message .= '"Message" does not meet the requirements. Minimum length: 5';
											}

											if(strlen($sError_Message) > 0)
											{
												echo '<div class="row"><div class="col-xs-12 col-md-12 col-lg-8 col-lg-offset-2 alert alert-danger"><h4>Oops! Something went wrong</h4>' . $sError_Message . '</div></div>';
											}
											else
											{
												DB::getInstance() -> update('blog', array(
													'userid' => $arrUserData['userid'],
													'blogname' => $sBlogName,
													'blogdescription' => $sBlogDescription,
													'message' => $sBlogMessage,
													'sticky' => $iBlogSticky
												), array('blogid', '=', $sId));

												echo '<div class="row"><div class="col-xs-12 col-md-12 col-lg-8 col-lg-offset-2 alert alert-success"><h4>Success!</h4> The blog entry has been succesfully changed! <br><a href="../blogs/' . $oBlog -> blogid . '">Click here to view the blog entry</a></div></div>';
											}
										}

										$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($oBlog -> userid));

										$iSticky = ($oBlog -> sticky == 1) ? 'checked' : '';

										echo '<form action="../blogs/' . $oBlog -> blogid . '/edit" method="post">
											<div class="panel panel-default">
												<div class="panel-header">
													<div class = "panel-heading panel-border">
														<h4 class = "panel-title">
															You are now editing "' . $oBlog -> blogname . '" by <a href="../profile/' . $qUser -> first() -> username . '">' . $qUser -> first() -> username . '</a>
														</h4>
													</div>
												</div>
												<div class="panel-body">
													<div class="row">
														<div class="col-md-4 col-xs-6">
															<label for="name">Blog name</label>
															<input type="text" id="blogname" class="form-control" name="blogname" placeholder="blog entry name" value="' . $oBlog -> blogname . '" />
														</div>

														<div class="col-md-8 col-xs-6">
															<label for="description">Description</label>
															<input type="text" id="blogdescription" class="form-control" name="blogdescription" placeholder="blog entry description" value="' . $oBlog -> blogdescription . '" />
														</div>
													</div>

													<div class="row" style="padding-top: 5px;">
														<div class="col-md-12 col-xs-12">
															<label for="message">Message</label>
															<div class="row-buffer-10"></div>
															<button class="btn btn-bb" type="button" id="bold"><b>bold</b></button> <button class="btn btn-bb" type="button" id="italic"><i>italic</i></button> <button class="btn btn-bb" type="button" id="underline"><u>underline</u></button> <button class="btn btn-bb" type="button" id="strike"><s>strike</s></button>

															<span class="dropdown">
																<button class="btn btn-bb dropdown-toggle" type="button" data-toggle="dropdown" id="size">size <span class="caret"></span></button>

																<ul class="dropdown-menu">
																	<li><a style="font-size: 8px; cursor: pointer" class="size">small</a></li>
																	<li><a style="font-size: 15px; cursor: pointer" class="size">normal</a></li>
																	<li><a style="font-size: 20px; cursor: pointer" class="size">medium</a></li>
																	<li><a style="font-size: 25px; cursor: pointer" class="size">large</a></li>
																</ul>
															</span>

															<button class="btn btn-bb" type="button" id="spoiler">spoiler</button> <button class="btn btn-bb" type="button" id="spoilerbox">spoilerbox</button> <button class="btn btn-bb" type="button" id="url">url</button> <button class="btn btn-bb" type="button"id="img">img</button>

															<textarea class="form-control textarea-theme" id="blogmessage" name="blogmessage" rows="10">' . $oBlog -> message . '</textarea>
														</div>
													</div>

													<div class="row" style="padding-top: 5px;">
														<div class="col-xs-12">
															<div class="form-group">
													            <div class="checkbox">
													                <label>
													                    <input id="blocksticky" name="blogsticky" ' . $iSticky . ' type="checkbox"> Sticky this post
													                </label>
													            </div>
													        </div>
														</div>
													</div>

													<div class="row">
														<div class="col-xs-offset-9 col-xs-3" style="padding-top: 5px;">
															<button type="submit" class="btn btn-default pull-right">Save</button>
														</div>
													</div>
												</div>
											</div>
										</form>';
									}
									else
									{
										echo '<div class="alert alert-danger col-xs-offset-3 col-xs-6"><h4>Something went wrong!</h4> You do not have the required access to edit this blog entry.</div>';
									}
								}
							}
							else
							{
								echo '<div class="alert alert-warning col-xs-offset-3 col-xs-6"><h4>Something went wrong!</h4> The blog you are trying to access could not be found</div>';
							}
						}
						echo '</div>';
					}
					else # User is not trying to edit anything
					{
						if(!strcmp($sId, 'all')) # Show all blogs to the user
						{
							echo '<div class="height: 10%">
								<ol class="breadcrumb">
									<li><a href="../">Home</a></li>
									<li><a href="../blogs">Blog</a></li>
									<li class="active">All blogs</li>
								</ol>

								<label for="searchuser">Search for an user.</label>
								<input id="searchuser" type="text" class="form-control" placeholder="search for an user..." style="margin-bottom: 16px;" />
							</div>

							<div id="blogtable" class = "blogtable" style="padding: 4px; height: 80% !important;">';

							# show all the usernames and their blogs in a dropdown
							$qUsers = DB::getInstance() -> query('SELECT userid, username FROM users');
							foreach($qUsers -> results() as $oUser)
							{
								$qBlogCount = DB::getInstance() -> query('SELECT count(blogid) AS blogcount FROM blog WHERE userid = ?', array($oUser -> userid));

								echo '<div class="panel panel-default searchuser">
										<div class="panel-heading">
											<h4 class="panel-title">
												<a role="button" data-toggle="collapse" href="#' . $oUser -> userid . '" class="blogusername">
													<div class="smallertext">
														<span style="text-align: left;">User: <span class="searchusername">' . $oUser -> username . '</span></span>
														<span style="text-align: center;">Blog entries: <b>(' . $qBlogCount -> first() -> blogcount . ')</b></span>
														<span style="text-align: right;"><i>Click to expand</i></span>
													</div>
												</a>
											</h4>
										</div>

										<div id="' . $oUser -> userid . '" class="panel-collapse collapse">
											<div class="panel-body customblog">';
												$qStickyBlogs = DB::getInstance() -> query('SELECT blogid, blogname, blogdescription FROM blog WHERE userid = ? AND sticky = 1 ORDER BY postdate DESC', array($oUser -> userid));

												foreach($qStickyBlogs -> results() as $oBlog)
												{
													echo '<a href="../blogs/' . $oBlog -> blogid . '/"><span class="blogblock"><i class="fa fa-thumb-tack pull-right" style="padding: 2px;"></i><h4>' . $oBlog -> blogname . '</h4><h6>' . $oBlog -> blogdescription . '</h6></span></a>';
												}

												$qNoStickyBlogs = DB::getInstance() -> query('SELECT blogid, blogname, blogdescription FROM blog WHERE userid = ? AND sticky = 0 ORDER BY postdate DESC', array($oUser -> userid));

												foreach($qStickyBlogs -> results() as $oBlog)
												{
													echo '<a href="../blogs/' . $oBlog -> blogid . '/"><span class="blogblock"><h4>' . $oBlog -> blogname . '</h4><h6>' . $oBlog -> blogdescription . '</h6></span></a>';
												}

											echo '</div>
										</div>
									</div>';
							}
						}
						else if(is_numeric($sId)) # Show the actual blog by blogid
						{
							$qBlog = DB::getInstance() -> query('SELECT userid, blogname, blogdescription, message, postdate FROM blog WHERE blogid = ? LIMIT 1', array($sId));

							if($qBlog -> count() > 0) # Does the blog exist
							{
								foreach($qBlog -> results() as $oBlog)
								{
									$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($oBlog -> userid));

									$sUsername = $qUser -> first() -> username;
									echo '<ol class="breadcrumb">
										<li><a href="../">Home</a></li>
										<li><a href="../blogs">Blog</a></li>
										<li><a href="../blogs/' . $sUsername . '">' . $qUser -> first() -> username . '\'s blogs</a></li>
										<li class="active">' . $oBlog -> blogname . '</li>
									</ol>';

									$dtDate = strtotime($oBlog -> postdate);

									echo '<h3 class="blogdate">' . date('jS F Y', $dtDate) . '</h3>';
									if($oBlog -> userid == $arrUserData['userid']) { echo '<span class="pull-right" style="padding-right: 4px;"><a href="../blogs/' . $sId . '/edit" class="btn btn-default">Edit this blog</a></span>'; }

									$qBlogResponse = DB::getInstance() -> query('SELECT reblog, bloglike FROM blogresponse WHERE blogid = ? AND userid = ?', array($sId, $arrUserData['userid']));

									echo '<h2 class="blogname">' . $oBlog -> blogname . ' </h2> by ' . $sUsername .
									'<span class="pull-right">';

									# record exists, show (revoke)like/(revoke)reblog depending on what the user already did
									if($arrUserData['loggedin'] == 1)
									{
										if($qBlogResponse -> count() > 0)
										{
											if($qBlogResponse -> first() -> reblog == 0)
											{
												echo '<a id="reblog" class="btn btn-success">Reblog</a>';
											}
											else if($qBlogResponse -> first() -> reblog == 1)
											{
												echo '<a id="reblog" class="btn btn-danger">Revoke Reblog</a>';
											}

											if($qBlogResponse -> first() -> bloglike == 0)
											{
												echo '<a id="like" class="btn btn-success">Like</a>';
											}
											else if($qBlogResponse -> first() -> bloglike == 1)
											{
												echo '<a id="like" class="btn btn-danger">Revoke Like</a>';
											}
										}
										else
										{
											# no record exists, show both like/reblog
											echo '<a id="reblog" class="btn btn-success">Reblog</a>	<a id="like" class="btn btn-success">Like</a>';
										}
									}

									echo '</span>
										<hr class="blogline" />
										<div class="blogmessage">' . Functions::bb_parse(nl2br($oBlog -> message)) . '</div>

										<a id="top" class="gotop"><i class="fa fa-chevron-up"></i></a>

										<table class="table bloglikes">';

											$qBlogResponse = DB::getInstance() -> query('SELECT userid, reblog, bloglike, date FROM blogresponse WHERE blogid = ? ORDER BY blogresponseid DESC', array($sId));

											foreach($qBlogResponse -> results() as $oBlogResponse)
											{
												$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($oBlogResponse -> userid));
												$BlogResponseUser = $qUser -> first() -> username;

												if($oBlogResponse -> bloglike == 1)
												{
													echo '<tr>
														<td><span><a href="../profile/' . $oBlogResponse -> userid . '">' . $BlogResponseUser . '</a> liked this (' . date('d-m-Y', strtotime($oBlogResponse -> date)) . ')</span></td>
													</tr>';
												}

												if($oBlogResponse -> reblog == 1)
												{
													$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($oBlog -> userid));

													echo '<tr>
														<td><span><a href="../profile/' . $oBlogResponse -> userid . '">' . $BlogResponseUser . '</a> reblogged this from <a href="../profile/' . $oBlog -> userid . '">' . $qUser -> first() -> username . '</a> (' . date('d-m-Y', strtotime($oBlogResponse -> date)) . ')</span></td>
													</tr>';
												}
											}

										echo '</table>';
								}
							}
							else
							{
								echo '<div class="alert alert-warning col-xs-offset-3 col-xs-6"><h4>Something went wrong!</h4> The blog you are trying to access could not be found</div>';
							}
						}
						else # Show the user the blogs from the other user
						{
							$qUser = DB::getInstance() -> query('SELECT userid, username FROM users WHERE username = ? LIMIT 1', array($sId));

							if($qUser -> count() > 0) # Does the user exist
							{
								echo '<ol class="breadcrumb">
									<li><a href="../">Home</a></li>
									<li><a href="../blogs">Blog</a></li>
									<li class="active">' . $qUser -> first() -> username . '\'s blogs</li>
								</ol>';

								foreach($qUser -> results() as $oUser)
								{
									$qBlogCount = DB::getInstance() -> query('SELECT count(blogid) AS blogcount FROM blog WHERE userid = ?', array($oUser -> userid));

									echo '<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title">
													User: ' . $oUser -> username . ' | Blog entries: ' . $qBlogCount -> first() -> blogcount . '
												</h4>
											</div>

											<div class="panel-body customblog">';
												# show sticky blogs first
												$qStickyBlogs = DB::getInstance() -> query('SELECT blogid, blogname, blogdescription FROM blog WHERE userid = ? AND sticky = 1 ORDER BY postdate DESC', array($oUser -> userid));

												foreach($qStickyBlogs -> results() as $oBlog)
												{
													echo '<a href="../blogs/' . $oBlog -> blogid . '/"><span class="blogblock"><i class="fa fa-thumb-tack pull-right" style="padding: 2px;"></i><h4>' . $oBlog -> blogname . '</h4><h6>' . $oBlog -> blogdescription . '</h6></span></a>';
												}

												$qNoStickyBlogs = DB::getInstance() -> query('SELECT blogid, blogname, blogdescription FROM blog WHERE userid = ?  AND sticky = 0 ORDER BY postdate DESC', array($oUser -> userid));

												foreach($qStickyBlogs -> results() as $oBlog)
												{
													echo '<a href="../blogs/' . $oBlog -> blogid . '/"><span class="blogblock"><h4>' . $oBlog -> blogname . '</h4><h6>' . $oBlog -> blogdescription . '</h6></span></a>';
												}
											echo '</div>
										</div>';
								}
							}
							else
							{
								echo '<div class="alert alert-warning col-xs-offset-3 col-xs-6"><h4>Something went wrong!</h4> The user could not be found</div>';
							}
						}
					}
				?>
				</div>
			</div>
		</div>

		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/sidebar.js"></script>

		<script>
			$(document).ready(function(){
				$("#top").click(function() {
					$("html, body, #blogtable").animate({ scrollTop: 0 }, 500);
					return false;
				});

				$("#searchuser").on('keyup', function(){
					var matcher = new RegExp($(this).val(), 'gi');

					$('.searchuser').show().not(function(){
						return matcher.test($(this).find('.searchusername').text())
					}).hide();
				});

				$("#like").on('click', function(){
					$.ajax({
						type: "POST",
						url: "../blog_ajax.php",
						dataType: "html",
						data: {
							blogid : <?php echo json_encode($_GET['b']); ?>,
							action : "like"
						},
						success: function(evt){
							alert(evt);
							window.location.href = "../blogs/<?php echo $_GET['b']; ?>";
						}
					})
				});

				$("#reblog").on('click', function(){
					$.ajax({
						type: "POST",
						url: "../blog_ajax.php",
						dataType: "html",
						data: {
							blogid : <?php echo json_encode($_GET['b']); ?>,
							action : "reblog"
						},
						success: function(evt){
							alert(evt);
							window.location.href = "../blogs/<?php echo $_GET['b']; ?>";
						}
					})
				});
			});
		</script>
	</body>
</html>
