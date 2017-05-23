<?php
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "blog");

	if($arrUserData['loggedin'] == 0)
		Header('Location: ../blogs/all');
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

		<!--[if lt IE 9]>
			<script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6/html5shiv.min.js"></script>
			<script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
		<![endif]-->
	</head>

	<body>
		<?php include_once 'resources/includes/navbar.php'; ?>
		<?php $includePageActive = "blog"; include_once 'resources/includes/sidebar.php'; ?>

		<div class="content">
			<div class="container-fluid">
				<ol class="breadcrumb">
					<li><a href="../">Home</a></li>
					<li class="active">Blog</li>
				</ol>

				<div class="row">
					<?php
						if($arrUserData['loggedin'] == 1)
						{
					?>
					<div class="col-xs-6">
						<?php echo '<a href="../blogs/' . $arrUserData['username'] . '" class="thumbnail h4" align="center">'; ?>
							<h1><i class="fa fa-user"></i></h1>
							<p>Show my blog entries</p>
						</a>
					</div>

					<?php
						}
					?>

					<div class="col-xs-6">
						<a href="../blogs/all" class="thumbnail h4" align="center">
							<h1><i class="fa fa-rss"></i></h1>
							<p>Show all blog entries</p>
						</a>
					</div>
				</div>

				<?php
					if(User::MiniAuth("makeblog", $arrUserData))
					{
				?>

				<form action="../blogs" method="post">
					<?php
						# creating variables
						$sBlogName			= isset($_POST['blogname']) ? $_POST['blogname'] : '';
						$sBlogDescription	= isset($_POST['blogdescription']) ? $_POST['blogdescription'] : '';
						$sBlogMessage		= isset($_POST['blogmessage']) ? $_POST['blogmessage'] : '';
						$iBlogSticky		= isset($_POST['blogsticky']) ? 'checked' : '';
						$dtBlogDate 		= date('Y-m-d');
						$sTempId			= Functions::generate_uniqueID(15);

						if($_SERVER['REQUEST_METHOD'] == 'POST')
						{
							# error messages depending on the situation
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

							# check if there are any errors, if not update the blog
							if(strlen($sError_Message) > 0)
							{
								echo '<div class="row"><div class="col-xs-12 col-md-12 col-lg-8 col-lg-offset-2 alert alert-danger"><h4>Oops! Something went wrong</h4>' . $sError_Message . '</div></div>';
							}
							else
							{
								# no errors have been found, insert blog
								DB::getInstance() -> insert('blog', array(
									'userid' => $arrUserData['userid'],
									'blogname' => $sBlogName,
									'blogdescription' => $sBlogDescription,
									'message' => $sBlogMessage,
									'sticky' => $iBlogSticky,
									'postdate' => $dtBlogDate,
									'temp_id' => $sTempId
								));

								$dbBlogId = DB::getInstance() -> query('SELECT blogid FROM blog WHERE temp_id = ?', array($sTempId));

								echo '<div class="row"><div class="col-xs-12 col-md-12 col-lg-8 col-lg-offset-2 alert alert-success"><h4>Success!</h4> Your blog has been created! <br><a href="../blogs/' . $dbBlogId -> first() -> blogid . '">You will be redirected to the blog entry in 5 seconds or by clicking here. </a></div></div>';
								header('Refresh: 5; URL=../blogs/' . $dbBlogId -> first() -> blogid);

								$dbBlogId = DB::getInstance() -> update('blog', array('temp_id' => 'UNDEFINED'), array('temp_id', '=', $sTempId));
							}
						}
					?>

					<div class="panel panel-default">
						<div class="panel-body">
							<div class="row">
								<div class="col-md-4 col-sm-6 col-xs-12">
									<label for="name">Blog name</label>
									<input type="text" id="blogname" class="form-control" name="blogname" placeholder="blog entry name" value="<?php echo $sBlogName; ?>" />
								</div>

								<div class="col-md-8 col-sm-6 col-xs-12">
									<label for="description">Description</label>
									<input type="text" id="blogdescription" class="form-control" name="blogdescription" placeholder="blog entry description" value="<?php echo $sBlogDescription; ?>" />
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

									<textarea class="form-control textarea-theme" id="blogmessage" name="blogmessage" rows="10"><?php echo $sBlogMessage; ?></textarea>
								</div>
							</div>

							<div class="row" style="padding-top: 5px;">
								<div class="col-xs-12">
									<div class="form-group">
							            <div class="checkbox">
							                <label>
							                    <input id="blocksticky" name="blogsticky" type="checkbox" <?php echo $iBlogSticky; ?>> Sticky this post
							                </label>
							            </div>
							        </div>
								</div>
							</div>

							<div class="row">
								<div class="col-xs-offset-9 col-xs-3" style="padding-top: 5px;">
									<button type="submit" class="btn btn-default pull-right">Create</button>
								</div>
							</div>
						</div>
					</div>
				</form>
				<?php
					}
				?>

			</div>
		</div>

		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/sidebar.js"></script>

		<script>
			$(document).ready(function(){
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

					var textarea = document.getElementById("blogmessage");

					if($("#blogmessage").is(":focus"))
					{
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[b]' + select + '[/b]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$("#italic").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#blogmessage").is(":focus"))
					{
						var textarea = document.getElementById("blogmessage");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[i]' + select + '[/i]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$("#underline").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#blogmessage").is(":focus"))
					{
						var textarea = document.getElementById("blogmessage");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[u]' + select + '[/u]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$("#strike").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#blogmessage").is(":focus"))
					{
						var textarea = document.getElementById("blogmessage");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[strike]' + select + '[/strike]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$(".size").on('mousedown', function(evt){
					evt.preventDefault();

					var textarea = document.getElementById("blogmessage");
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

					if($("#blogmessage").is(":focus"))
					{
						var textarea = document.getElementById("blogmessage");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[spoiler]' + select + '[/spoiler]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$("#spoilerbox").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#blogmessage").is(":focus"))
					{
						var textarea = document.getElementById("blogmessage");

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
							var textarea = document.getElementById("blogmessage");
							var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
							var replace = '[spoilerbox]' + select + '[/spoilerbox]';

							textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
						}
					}
				});

				$("#url").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#blogmessage").is(":focus"))
					{
						var textarea = document.getElementById("blogmessage");

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

					if($("#blogmessage").is(":focus"))
					{
						var textarea = document.getElementById("blogmessage");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[img]' + select + '[/img]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});
			});
		</script>
	</body>
</html>
