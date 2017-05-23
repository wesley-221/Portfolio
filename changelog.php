<?php
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "changelog");
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

		<title>Changelog &ndash; Portfolio</title>
	</head>

	<body>
		<?php include_once 'resources/includes/navbar.php'; ?>
		<?php $includePageActive = "changelog"; include_once 'resources/includes/sidebar.php'; ?>

		<div class="content">
			<div class="container-fluid">
				<?php
					if(!strcmp($_GET['a'], 'edit'))
					{
						echo '<ol class="breadcrumb">
							<li><a href="../">Home</a></li>
							<li><a href="../changelog">Changelog</a></li>
							<li class="active">Edit changelog</li>
						</ol>';
					}
					else
					{
						echo '<ol class="breadcrumb">
							<li><a href="../">Home</a></li>
							<li class="active">Changelog</li>
						</ol>';
					}
				?>

				<div class="col-xs-12">
					<div id="diverrormessage" style="position: absolute; top: -20; left: 40%; display: none;"><div class="alert alert-danger"><h4>Oops! Something went wrong</h4><span id="spanerrormessage"></span></div></div>
					<div id="divsucceedmessage" style="position: absolute; top: -20; left: 40%; display: none;"><div class="alert alert-success"><h4>Success!</h4><span id="spansucceedmessage"></span></div></div>

					<?php
						if(!strcmp($_GET['a'], 'edit')) # Show the edit page instead of the normal changelog
						{
							if(User::MiniAuthRedirect("editchangelog", $arrUserData, "../changelog"))
							{
								echo '<form action="changelog/edit" method="post">';

								$qVersions = DB::getInstance() -> query('SELECT distinct versionname FROM changelog ORDER BY versionname DESC');

								if($qVersions -> count() > 0)
								{
									# Loop through all versions and show their entries
									foreach($qVersions -> results() as $oVersion)
									{
										echo '<div class="changelog-version">
												<div class="panel panel-default">
													<div class="panel-heading clearfix">
														<button type="button" class="btn btn-default btn-sm pull-right">Delete version</button>

														<h4 class="panel-title" style="padding-top: 7px;">
															Version: ' . $oVersion -> versionname . '
														</h4>
													</div>

													<div class="panel-collapse collapse in">
														<div class="panel-body">
															<div class="row-buffer-10"></div>
															<table class="table">
																<tr>
																	<td>changelogid</td><td>versionname</td><td>userid</td><td>type</td><td>text</td><td>date</td><td></td><td></td>
																</tr>';
															$qVersionChangeLog = DB::getInstance() -> query('SELECT changelogid, versionname, userid, type, text, date FROM changelog WHERE versionname = ? ORDER BY changelogid DESC', array($oVersion -> versionname));

															foreach($qVersionChangeLog -> results() as $oChangeLog)
															{
																$optNew = 		!strcmp($oChangeLog -> type, "new") ? 'selected' : '';
																$optUpdate = 	!strcmp($oChangeLog -> type, "update") ? 'selected' : '';
																$optFix = 		!strcmp($oChangeLog -> type, "fix") ? 'selected' : '';

																echo '<tr id="tr' . $oChangeLog -> changelogid . '">
																	<td style="width: 10%;">
																		<input id="changelogid' . $oChangeLog -> changelogid . '" type="text" class="form-control" value="' . $oChangeLog -> changelogid . '" disabled />
																	</td>

																	<td style="width: 12%;">
																		<input id="versionname' . $oChangeLog -> changelogid . '" type="text" class="form-control" value="' . $oChangeLog -> versionname .'" disabled />
																	</td>

																	<td style="width: 10%;">
																		<input id="userid' . $oChangeLog -> changelogid . '" type="text" class="form-control" value="' . $oChangeLog -> userid . '" disabled />
																	</td>

																	<td>
																		<select id="type' . $oChangeLog -> changelogid . '" class="form-control textarea-theme" disabled>
																			<option value="new" ' . $optNew . '>new</option>
																			<option value="update" ' . $optUpdate . '>update</option>
																			<option value="fix" ' . $optFix . '>fix</option>
																		</select>
																	</td>

																	<td>
																		<input id="text' . $oChangeLog -> changelogid . '" type="text" class="form-control" value="' . htmlentities($oChangeLog -> text) . '" disabled />
																	</td>

																	<td style="width: 20%;">
																		<input id="date' . $oChangeLog -> changelogid . '" type="text" class="form-control" value="' . $oChangeLog -> date . '" disabled />
																	</td>

																	<td>
																		<button id="btn' . $oChangeLog -> changelogid . '" type="button" class="btn btn-default">Edit</button>
																	</td>

																	<td>
																		<button id="btnDelete' . $oChangeLog -> changelogid . '" type="button" class="btn btn-danger">Delete</button>
																	</td>
																</tr>';
															}

														echo '</table>
														</div>
													</div>
												</div>
											</div>
										</form>';
									}
								}
							}
						}
						else # User is not on ../changelog/edit
						{
							if(User::MiniAuth("editchangelog", $arrUserData)) # authenticate user
							{
								# Validation of all data
								$sNewVersion 	= isset($_POST['nversionnumber']) ? $_POST['nversionnumber'] : '';
								$sEditVersion 	= isset($_POST['eversionnumber']) ? $_POST['eversionnumber'] : 'new';
								$sEditor 		= isset($_POST['editor']) ? $_POST['editor'] : '';
								$sTag 			= isset($_POST['tag']) ? $_POST['tag'] : '';
								$sChange 		= isset($_POST['change']) ? $_POST['change'] : '';
								$sError_Message = "";

								if($_SERVER['REQUEST_METHOD'] == 'POST')
								{
									$sFinalVersion = "";
									// Create new version
									if(!strcmp($sEditVersion, "new"))
									{
										if(strlen($sNewVersion) > 0)
										{
											if(!preg_match('/^[0-9]+[.][0-9]+[.][0-9]+$/', $sNewVersion)) // does not use the specified pattern
											{
												$sError_Message .= "The entered version number is invalid. Please use the format ###.###.###, where # is a number <br />";
											}
											else
												$sFinalVersion = $sNewVersion;
										}
										else
										{
											$sError_Message .= "The entered version number is invalid. Please use the format ###.###.###, where # is a number <br />";
										}
									}
									else // Add the change to an existing version
									{
										$qVersionCheck = DB::getInstance() -> query('SELECT versionid FROM versions WHERE versionname = ?', array($sEditVersion));

										if($qVersionCheck -> count() <= 0)
										{
											$sError_Message .= "The entered version number does not exist <br />";
										}
										else
											$sFinalVersion = $sEditVersion;
									}

									// validate changelog applier
									if(strcmp($sEditor, Config::get("default_user")))
									{
										$qEditorCheck = DB::getInstance() -> query('SELECT username FROM users WHERE groupid >= 4 AND username = ?', array($sEditor));

										if($qEditorCheck -> count() <= 0)
										{
											$sError_Message .= "The username of the editor (" . $sEditor . ") has not been found in the database <br />";
										}
									}

									// validate tags
									if(!strcmp($sTag, "fix")) {}
									else if(!strcmp($sTag, "update")) {}
									else if(!strcmp($sTag, "new")) {}
									else
									{
										$sError_Message .= "Invalid tag, only possibilities are \"fix\", \"update\" and \"new\" <br />";
									}

									// validate changes
									if(strlen($sChange) <= 4)
									{
										$sError_Message .= "You have to enter atleast 5 characters on what has been changed <br/>";
									}

									// show error messages
									if(strlen($sError_Message) > 0)
									{
										echo '<div class="row"><div class="col-xs-12 col-md-12 col-lg-8 col-lg-offset-2 alert alert-danger"><h4>Oops! Something went wrong</h3>' . $sError_Message . '</div></div>';
									}
									else // passed all validations, send query
									{
										$qEditorUserid = DB::getInstance() -> query("SELECT userid FROM users WHERE username = ?", array($sEditor));
										$iEditorID = $qEditorUserid -> first() -> userid;
										$dtCurDate = date("Y/m/d G:i:s");

										# handle the user saving
										DB::getInstance() -> insert("changelog", array(
																		"versionname" 	=> $sFinalVersion,
																		"userid" 		=> $iEditorID,
																		"type"			=> $sTag,
																		"text" 			=> $sChange,
																		"date"			=> $dtCurDate));

										# create new version number if it doesn't exist
										if(!strcmp($sEditVersion, "new"))
										{
											$qVersionExist = DB::getInstance() -> query("SELECT versionname FROM versions WHERE versionname = ?", array($sFinalVersion));

											# see if version already exist, create new version if needed
											if($qVersionExist -> count() > 0)
											{
												echo '<div class="row"><div class="col-xs-12 col-md-12 col-lg-8 col-lg-offset-2 alert alert-danger"><h4>Oops! Something went wrong</h3> The new version you wanted to create already exists! I have applied the change to the already existing version!</div></div>';
											}
											else
											{
												DB::getInstance() -> insert("versions", array(
																				"versionname" 	=> $sFinalVersion,
																				"creatorid" 	=> $arrUserData['userid']));
											}
										}
									}
								}

								echo '<form action="changelog" method="post">
								<input type="button" class="btn btn-default" id="minimize" style="margin-bottom: 7px;" value="Expand" />
								<a href="../changelog/edit" class="btn btn-default" id="edit" style="margin-bottom: 7px;">Edit</a>

								<div class="row invis" id="edit-log">
									<div class="col-xs-12 col-md-12 col-lg-10 col-lg-offset-1">
										<div class="panel panel-default">
											<div class="panel-body">
												<div class="row">
													<div class="col-xs-12 col-sm-4">
														<label for="nversionnumber">Enter a new version number</label>
														<input type="text" id="nversionnumber" name="nversionnumber" class="form-control" placeholder="enter version number" value="' . $sNewVersion. '" ' . (!strcmp($sEditVersion, "new")?"":"disabled") . '/>
													</div>

													<div class="col-xs-12 col-sm-8">
														<label for="eversionnumber">Select a version number where you want to add a change to</label>
														<select id="eversionnumber" name="eversionnumber" class="form-control select-theme">
															<option value="new">Create new version</option>';

															# show all versions in database
															$qVersions = DB::getInstance() -> query('SELECT versionname FROM versions ORDER BY versionname DESC');

															foreach($qVersions -> results() as $sVersion)
															{
																if(!strcmp($sEditVersion, $sVersion -> versionname))
																{
																	echo '<option value="' . $sVersion -> versionname . '" selected>' . $sVersion -> versionname . '</option>';
																}
																else
																{
																	echo '<option value="' . $sVersion -> versionname . '">' . $sVersion -> versionname . '</option>';
																}
															}

														echo '</select>
													</div>
												</div>

												<div class="row">
													<div class="col-xs-12">
														<label for="editor">Select as who you want to make the change entry</label>
														<select id="editor" name="editor" class="form-control select-theme">';
															echo '<option value="' . $arrUserData['username'] . '">' . $arrUserData['username'] . '</option>';

															# show all developers
															$qDevelopers = DB::getInstance() -> query('SELECT username FROM users WHERE groupid = 4');

															foreach($qDevelopers -> results() as $sDevelopers)
															{

																echo '<option value="' . $sDevelopers -> username . '"' . (!strcmp($sDevelopers -> username, $sEditor)?"selected":"") . '>' . $sDevelopers -> username . '</option>';
															}

															echo '<option value="' . Config::get("default_user") . '"' . (!strcmp(Config::get("default_user"), $sEditor)?"selected":"") . '>' . Config::get("default_user") . '</option>';
														echo '</select>
													</div>
												</div>

												<div class="row">
													<div class="col-xs-12 col-sm-6 ">
														<label for="tag">Enter a tag for the entry</label>
														<select id="tag" name="tag" class="form-control select-theme">
															<option value="new"' . (!strcmp($sTag, "new") ? "selected" : "") . '>Mention a new feature</option>
															<option value="update"' . (!strcmp($sTag, "update") ? "selected" : "") . '>Mention a new update</option>
															<option value="fix"' . (!strcmp($sTag, "fix") ? "selected" : "") . '>Mention a new fix</option>
														</select>
													</div>

													<div class="col-xs-12 col-sm-6">
														<label for="change">Fill in what changes have been made</label>
														<input type="text" id="change" name="change" class="form-control" placeholder="what has been changed" value="' . $sChange . '" />
													</div>
												</div>

												<div class="row">
													<div class="col-xs-12">
														<button type="submit" id="submit" name="submit" class="btn btn-default pull-right">Create new entry</button>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								</form>

								<div class="row invis" id="live-preview-row">
									<div class="col-xs-12 col-md-12 col-lg-8 col-lg-offset-2">
										<div class="live-preview" id="live-preview" name="live-preview">
											<p class="live-preview-version h4" id="live-preview-version"></p>

											<span class="live-preview" id="live-preview-tag"></span> <span class="live-preview-username" id="live-preview-editor"></span> <span class="live-preview-comment" id="live-preview-comment"></span>
										</div>
									</div>
								</div>';
							}

							echo '<div class="row">';
							# query to get all versions
							$qAllVersions = DB::getInstance() -> query("SELECT versionname FROM versions ORDER BY versionname DESC");

							$iFirstVersion = 0; # expand first version, so updates are visible by default

							# loop through all versions, show seperate collapse for each version
							foreach($qAllVersions -> results() as $sVersion)
							{
								$sPanelId = Functions::generate_uniqueID(7); # generate an id for the collapse

								echo '<div class="changelog-version">
										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title">
													<a role="button" data-toggle="collapse" href="#' . $sPanelId . '">Version: ' . $sVersion -> versionname . '<span class="pull-right"><i>Click on the text to expand</i></span></a>
												</h4>
											</div>

											<div id="' . $sPanelId . '" class="panel-collapse collapse ' . ($iFirstVersion == 0 ? "in" : "") . '">
												<div class="panel-body">';
													# get all changelog entries for current version
													$qChangeLogEntries = DB::getInstance() -> query("SELECT userid, type, text, date FROM changelog WHERE versionname = ? ORDER BY changelogid  DESC", array($sVersion->versionname));

													# loop through all changelog entries
													foreach($qChangeLogEntries -> results() as $oChangeLog)
													{
														# get the username from the creator
														$qEditorName = DB::getInstance() -> query("SELECT username FROM users WHERE userid = ?", array($oChangeLog -> userid));
														$sEditorName = $qEditorName -> first() -> username;

														echo '<div class="changelog">
															<span class="changelog">' . $oChangeLog -> type . '</span> <span class="changelog-username">' . $sEditorName . '</span> : <span class="changelog-comment">' . $oChangeLog -> text . '</span>
														</div>';
													}
												echo '</div>
											</div>
										</div>
									</div>';

								$iFirstVersion ++;
							}
							echo '</div>';
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
				var $bMinimized = true;

				$("#live-preview-version").text("Version: " + $("#eversionnumber").find(":selected").val());
				$("#live-preview-comment").text($("#change").val());
				$("#live-preview-editor").text($("#editor").find(":selected").val());

				$("#live-preview-tag").text($("#tag").find(":selected").val());

				$("#nversionnumber").on('input', function(){
					if($("#eversionnumber").val().trim() == "new")
					{
						$("#live-preview-version").text("Version: " + $("#nversionnumber").val());
					}
					else
					{
						$("#live-preview-version").text("Version: " + $("#eversionnumber").find(":selected").val());
					}
				});

				$("#eversionnumber").on('change', function(){
					if($("#eversionnumber").find(":selected").val() != "new")
					{
						$("#nversionnumber").prop("disabled", true);
						$("#live-preview-version").text("Version: " + $("#eversionnumber").find(":selected").val());
					}
					else
					{
						$("#nversionnumber").prop("disabled", false);

						if($("#nversionnumber").val().length > 0)
						{
							$("#live-preview-version").text("Version: " + $("#nversionnumber").val());
						}
						else
						{
							$("#live-preview-version").text("Version: " + $("#eversionnumber").find(":selected").val());
						}
					}
				});

				$("#editor").on('change', function(){
					$("#live-preview-editor").text($("#editor").find(":selected").text());
				});

				$("#tag").on('change', function(){
					$("#live-preview-tag").text($("#tag").find(":selected").val());
				});

				$("#change").on('input', function(){
					$("#live-preview-comment").text($("#change").val());
				});

				$("#minimize").on('click', function(){
					if($bMinimized == true)
					{
						$("#edit-log").slideDown(400);
						setTimeout(function(){$("#live-preview-row").slideDown(200);}, 250);
						$bMinimized = false;
						$("#minimize").val("Minimize");
					}
					else
					{
						$("#live-preview-row").slideUp(200);
						setTimeout(function(){$("#edit-log").slideUp(400);}, 150);
						$bMinimized = true;
						$("#minimize").val("Expand");
					}
				});

				function adjustErrorMessage(event)
				{
					if($ErrorMessageShow == 0)
					{
						$("#spanerrormessage").html(event);
						$("#diverrormessage").show();
						$ErrorMessageShow = 1;
					}
					else
					{
						clearTimeout($errTimer);
						$ErrorMessageShow = 1;
						$("#spanerrormessage").append(event);
					}

					$errTimer = setTimeout(function(){
						$("#diverrormessage").hide();
						$ErrorMessageShow = 0;
					}, 3000);
				}

				function adjustSucceedMessage(event)
				{
					if($SucceedMessageShow == 0)
					{
						$("#spansucceedmessage").html(event);
						$("#divsucceedmessage").show();
						$SucceedMessageShow = 1;
					}
					else
					{
						clearTimeout($sucTimer);
						$SucceedMessageShow = 1;
						$("#spansucceedmessage").append(event);
					}

					$sucTimer = setTimeout(function(){
						$("#divsucceedmessage").hide();
						$SucceedMessageShow = 0;
					}, 3000);
				}

				<?php
					$qChangeLog = DB::getInstance() -> query('SELECT changelogid FROM changelog');

					foreach($qChangeLog -> results() as $oChangeLog)
					{
						echo '$btn' . $oChangeLog -> changelogid . ' = 1;
						$("#btn' . $oChangeLog -> changelogid . '").on("click", function(){
							if($btn' . $oChangeLog -> changelogid . ')
							{
								$("#versionname' . $oChangeLog -> changelogid . '").attr("disabled", false);
								$("#userid' . $oChangeLog -> changelogid . '").attr("disabled", false);
								$("#type' . $oChangeLog -> changelogid . '").attr("disabled", false);
								$("#text' . $oChangeLog -> changelogid . '").attr("disabled", false);
								$("#date' . $oChangeLog -> changelogid . '").attr("disabled", false);
								$btn' . $oChangeLog -> changelogid . ' = !$btn' . $oChangeLog -> changelogid . '
							}
							else
							{
								$("#versionname' . $oChangeLog -> changelogid . '").attr("disabled", true);
								$("#userid' . $oChangeLog -> changelogid . '").attr("disabled", true);
								$("#type' . $oChangeLog -> changelogid . '").attr("disabled", true);
								$("#text' . $oChangeLog -> changelogid . '").attr("disabled", true);
								$("#date' . $oChangeLog -> changelogid . '").attr("disabled", true);
								$btn' . $oChangeLog -> changelogid . ' = !$btn' . $oChangeLog -> changelogid . '
							}
						});';

						echo 'var $ErrorMessageShow = 0;
						var $SucceedMessageShow = 0;
						var $errTimer;
						var $sucTimer;

						$("#versionname' . $oChangeLog -> changelogid . '").on("blur", function(){
							$.ajax({
								type: "POST",
								url: "../changelog_ajax.php",
								dataType: "json",
								data: {
									versionname : $("#versionname' . $oChangeLog -> changelogid . '").val(), changelogid : ' . $oChangeLog -> changelogid . '
								},
								before: function(evt){
									console.log("evt");
								},
								success: function(evt){
									if(typeof evt.success != "undefined")
									{
										adjustSucceedMessage(evt.success, ' . $oChangeLog -> changelogid . ');
									}
									else if(typeof evt.error != "undefined")
									{
										adjustErrorMessage(evt.error, ' . $oChangeLog -> changelogid . ');
									}
								}
							})
						});

						$("#userid' . $oChangeLog -> changelogid . '").on("blur", function(){
							$.ajax({
								type: "POST",
								url: "../changelog_ajax.php",
								dataType: "json",
								data: {
									userid : $("#userid' . $oChangeLog -> changelogid . '").val(), changelogid : ' . $oChangeLog -> changelogid . '
								},
								success: function(evt){
									if(typeof evt.success != "undefined")
									{
										adjustSucceedMessage(evt.success, ' . $oChangeLog -> changelogid . ');
									}
									else if(typeof evt.error != "undefined")
									{
										adjustErrorMessage(evt.error, ' . $oChangeLog -> changelogid . ');
									}
								}
							})
						});

						$("#type' . $oChangeLog -> changelogid . '").on("change", function(){
							$.ajax({
								type: "POST",
								url: "../changelog_ajax.php",
								dataType: "json",
								data: {
									type : $("#type' . $oChangeLog -> changelogid . '").val(), changelogid : ' . $oChangeLog -> changelogid . '
								},
								success: function(evt){
									if(typeof evt.success != "undefined")
									{
										adjustSucceedMessage(evt.success, ' . $oChangeLog -> changelogid . ');
									}
									else if(typeof evt.error != "undefined")
									{
										adjustErrorMessage(evt.error, ' . $oChangeLog -> changelogid . ');
									}
								}
							})
						});

						$("#text' . $oChangeLog -> changelogid . '").on("blur", function(){
							$.ajax({
								type: "POST",
								url: "../changelog_ajax.php",
								dataType: "json",
								data: {
									text : $("#text' . $oChangeLog -> changelogid . '").val(), changelogid : ' . $oChangeLog -> changelogid . '
								},
								success: function(evt){
									if(typeof evt.success != "undefined")
									{
										adjustSucceedMessage(evt.success, ' . $oChangeLog -> changelogid . ');
									}
									else if(typeof evt.error != "undefined")
									{
										adjustErrorMessage(evt.error, ' . $oChangeLog -> changelogid . ');
									}
								}
							})
						});

						$("#date' . $oChangeLog -> changelogid . '").on("blur", function(){
							$.ajax({
								type: "POST",
								url: "../changelog_ajax.php",
								dataType: "json",
								data: {
									date : $("#date' . $oChangeLog -> changelogid . '").val(), changelogid : ' . $oChangeLog -> changelogid . '
								},
								success: function(evt){
									if(typeof evt.success != "undefined")
									{
										adjustSucceedMessage(evt.success, ' . $oChangeLog -> changelogid . ');
									}
									else if(typeof evt.error != "undefined")
									{
										adjustErrorMessage(evt.error, ' . $oChangeLog -> changelogid . ');
									}
								}
							})
						});

						$("#btnDelete' . $oChangeLog -> changelogid . '").on("click", function(){
							if(confirm("Are you sure you want to delete this entry?"))
							{
								$.ajax({
									type: "POST",
									url: "../changelog_ajax.php",
									dataType: "html",
									data: {
										changelogid : ' . $oChangeLog -> changelogid . ', delete : 1
									}
								})

								$("#tr' . $oChangeLog -> changelogid . '").fadeToggle("fast");
							}
						});';
					}
				?>
			});
		</script>
	</body>
</html>
