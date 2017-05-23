<?php
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "uploadcenter");
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

		<title>Upload center &ndash; Portfolio</title>
	</head>

	<body>
		<?php include_once 'resources/includes/navbar.php'; ?>
		<?php $includePageActive = "upload"; include_once 'resources/includes/sidebar.php'; ?>

		<div class="content">
			<div class="container-fluid">
				<?php
					$Window = isset($_GET['w']) ? $_GET['w'] : '';

					if(!strcmp($Window, 'upload') || !strcmp($Window, ''))
					{
						echo '<ol class="breadcrumb">
							<li><a href="../">Home</a></li>
							<li><a href="../uploadcenter">Upload center</a></li>
							<li class="active">Upload a file</li>
						</ol>';
					}
					else if(!strcmp($Window, 'gallery'))
					{
						echo '<ol class="breadcrumb">
							<li><a href="../">Home</a></li>
							<li><a href="../uploadcenter">Upload center</a></li>
							<li class="active">Galleries</li>
						</ol>';
					}
					else if(!strcmp($Window, 'manage'))
					{
						echo '<ol class="breadcrumb">
							<li><a href="../">Home</a></li>
							<li><a href="../uploadcenter">Upload center</a></li>
							<li class="active">Manage entries</li>
						</ol>';
					}
				?>

				<div class="row">
					<div class="col-xs-4">
						<a href="../uploadcenter/upload/" class="thumbnail h4" align="center">
							<h1><i class="fa fa-upload"></i></h1>
							<p>Upload a file</p>
						</a>
					</div>

					<div class="col-xs-4">
						<a href="../uploadcenter/gallery/" class="thumbnail h4" align="center">
							<h1><i class="fa fa-archive"></i></h1>
							<p>Galleries</p>
						</a>
					</div>

					<div class="col-xs-4">
						<a href="../uploadcenter/manage/" class="thumbnail h4" align="center">
							<h1><i class="fa fa-wrench"></i></h1>
							<p>Manage entries</p>
						</a>
					</div>
				</div>

				<div class="row">
					<div class="col-xs-12">
						<?php
							if(!strcmp($Window, "upload") || !strcmp($Window, ""))
							{
								echo '<div class="col-xs-12">
									<div class="panel panel-default">
										<div class="panel-body">
											<div class="uploadfield">
												<h4>Click to upload a file</h4>

												<form action="../uploadcenter/upload/" method="post" enctype="multipart/form-data" id="formsubmit">
													<label class="btn btn-success btn-upload">
														<div><i class="fa fa-plus-circle"></i> Add files <i id="refresher" class="fa fa-refresh fa-spin" style="display: none;"></i>
														<input id="files" type="file" name="files[]" multiple></div>
										            </label>
												</form>
											</div>

											<div class="result-table">
												<div class="row-buffer-10"></div>
												<h3>The following files have been uploaded: </h3>
												<table class="table table-striped">
													<thead>
														<tr>
															<td>File</td><td>Name</td><td>Size</td>
														</tr>
													</thead>

													<tbody>';
														if(isset($_FILES['files']))
														{
															if(count($_FILES['files']))
															{
																$arrSorted = Functions::reArrayFiles($_FILES['files']);

																foreach($arrSorted as $file)
																{
																	$newNameUnique = 0;
																	$newName = Functions::generate_uniqueID(10);

																	do
																	{
																		$qNewName = DB::getInstance() -> query('SELECT uploadname FROM uploads WHERE uploadname = ?', array($newName));

																		if($qNewName -> count() > 0)
																		{
																			if(!strcmp($qNewName -> first() -> uploadname, $newName))
																			{
																				$newName = Functions::generate_uniqueID(10);
																			}
																			else
																			{
																				$newNameUnique = 1;
																			}
																		}
																		else
																		{
																			$newNameUnique = 1;
																		}
																	}
																	while($newNameUnique == 0);

																	$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
																	$newName = $newName . '.' . $extension;

																	if(!file_exists('resources/uploads/' . $arrUserData['username'] . '/'))
																	{
																		mkdir('resources/uploads/' . $arrUserData['username'], 0777, true);
																	}

																	if($file['error'] == UPLOAD_ERR_OK)
																	{
																		move_uploaded_file($file['tmp_name'], 'resources/uploads/' . $arrUserData['username'] . '/' . $newName);

																		DB::getInstance() -> insert('uploads', array(
																			'userid' => $arrUserData['userid'],
																			'uploadname' => $newName,
																			'originalname' => $file['name'],
																			'passwordprotected' => 0,
																			'postdate' => date('Y-m-d'),
																			'password' => 'UNDEFINED'));
																	}
																	else
																	{
																		echo 'Something went wrong';
																	}

																	$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

																	if(in_array($fileExtension, Config::get('config/picture_extension')))
																	{
																		echo '<tr>
																			<td><img src="resources/uploads/' . $arrUserData['username'] . '/' . $newName . '" width="200px" /></td>
																			<td>' . $file['name'] . '</td>
																			<td>' . Functions::formatSizeUnits($file['size']) . '</td>
																		</tr>';
																	}
																	else if(!strcmp($fileExtension, 'mp3') || !strcmp($fileExtension, 'mp4'))
																	{
																		echo '<tr>
																			<td><span class="cutofftextfile" style="padding: 4px; display: inline-block;"><center><i class="fa fa-music uploadfile" style=" font-size: 50"></i></center>' . $newName . '</span></td>
																			<td>' . $file['name'] . '</td>
																			<td>' . Functions::formatSizeUnits($file['size']) . '</td>
																		</tr>';
																	}
																	else
																	{
																		echo '<tr>
																			<td><span class="cutofftextfile" style="padding: 4px; display: inline-block;"><center><i class="fa fa-file uploadfile" style=" font-size: 50"></i></center>' . $newName . '</span></td>
																			<td>' . $file['name'] . '</td>
																			<td>' . Functions::formatSizeUnits($file['size']) . '</td>
																		</tr>';
																	}
																}
															}
														}

													echo '</tbody>
												</table>
											</div>
										</div>
									</div>
								</div>';
							}
							else if(!strcmp($Window, "gallery"))
							{
								echo '<table class="table table-striped">
									<thead>
										<tr>
											<td>File</td><td>Name</td><td>Size</td>
										</tr>
									</thead>

									<tbody>';

								$folderUserUploads = "resources/uploads/" . $arrUserData['username'] . "/";

								if(file_exists($folderUserUploads))
								{
									$qFiles = DB::getInstance() -> query('SELECT userid, uploadname, originalname FROM uploads WHERE userid = ? AND softdelete = 0', array($arrUserData['userid']));

									foreach($qFiles -> results() as $oFile)
									{
										$fileExtension = pathinfo($oFile -> uploadname, PATHINFO_EXTENSION);

										if(in_array($fileExtension, Config::get('config/picture_extension')))
										{
											echo '<tr>
												<td><a href="' . $folderUserUploads . $oFile -> uploadname . '" title="' . $oFile -> originalname . '"><img src="' . $folderUserUploads . $oFile -> uploadname . '" width="150" /></a></td>
												<td>' . $oFile -> uploadname . '</td>
												<td>' . Functions::formatSizeUnits(filesize($folderUserUploads . $oFile -> uploadname)) . '</td>
											</tr>';
										}
										else if(!strcmp($fileExtension, 'mp3') || !strcmp($fileExtension, 'mp4'))
										{
											echo '<tr>
												<td><a href="' . $folderUserUploads . $oFile -> uploadname . '" download="' . $oFile -> originalname . '" title="' . $oFile -> originalname . '"><span class="cutofftextfile" style="padding: 4px; display: inline-block;"><i class="fa fa-music uploadfile"></i><div class="row-buffer-10"></div>' . $oFile -> uploadname . '</span></a></td>
												<td>' . $oFile -> originalname . '</td>
												<td>' . Functions::formatSizeUnits(filesize($folderUserUploads . $oFile -> uploadname)) . '</td>
											</tr>';
										}
										else
										{
											echo '<tr>
												<td><a href="' . $folderUserUploads . $oFile -> uploadname . '" download="' . $oFile -> originalname . '" title="' . $oFile -> originalname . '"><span class="cutofftextfile" style="padding: 4px; display: inline-block;"><i class="fa fa-file uploadfile"></i><div class="row-buffer-10"></div>' . $oFile -> uploadname . '</span></a></td>
												<td>' . $oFile -> originalname . '</td>
												<td>' . Functions::formatSizeUnits(filesize($folderUserUploads . $oFile -> uploadname)) . '</td>
											</tr>';
										}
									}
								}
								echo '</tbody></table>';
							}
							else if(!strcmp($Window, "manage"))
							{
								echo '<button type="button" class="btn btn-default" id="btnSelect"><i class="fa fa-check-square-o"></i> <span id="btnSelectText">Select</span></button>
									<button type="button" class="btn btn-danger" id="btnDelete"><i class="fa fa-trash-o"></i> Delete <span id="btnDeleteAmount"></span> <i id="refresher" class="fa fa-refresh fa-spin" style="display: none;"></i></button>

									<div class="row-buffer-10"></div>

									<div id="blogtable" class = "scrolltable">';

								echo '<table>
									<tbody>
										<tr>
											<td>
												<div>';

													$folderUserUploads = "resources/uploads/" . $arrUserData['username'] . "/";

													if(file_exists($folderUserUploads))
													{
														$qFiles = DB::getInstance() -> query('SELECT userid, uploadname, originalname FROM uploads WHERE userid = ? AND softdelete = 0', array($arrUserData['userid']));

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
												echo '</div>
											</td>
										</tr>
									</tbody>
								</table>
								</div>
								<div id="test"></div>';
							}
						?>
					</div>
				</div>
			</div>
		</div>

		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/sidebar.js"></script>

		<script>
			$(document).ready(function(){
				var $btnSelect = 0;
				var $amountImageSelected = 0;
				$("#files").on('change', function(){
					$("#formsubmit").submit();
				});

				$("#formsubmit").submit(function(){
					$("#refresher").show();
				});

				$("#btnSelect").on('click', function(e){
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
			});
		</script>
	</body>
</html>
