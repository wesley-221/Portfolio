<?php
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "message");
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

		<title>Send message &ndash; Portfolio</title>
	</head>

	<body>
		<?php include_once 'resources/includes/navbar.php'; ?>
		<?php $includePageActive = "message"; include_once 'resources/includes/sidebar.php'; ?>

		<div class="content">
			<div class="container-fluid">
				<div class="row">
					<div class="col-xs-12 col-md-6 col-sm-6 col-lg-6 col-md-offset-3 col-sm-offset-3 col-lg-offset-3" style="position: fixed; top: 80; display: none; z-index: 1;" id="errormessage">
						<div class="alert alert-danger alert-dismissable">
							<button type="button" class="close" data-dismiss="alert">×</button>
							<span id="errormessagespan"></span>
						</div>
					</div>
				</div>

				<?php
					if($arrUserData['userid'] != $_GET['u'])
					{
						# check if you aren't sending a message to yourself
						$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($_GET['u']));

						foreach($qUser -> results() as $oUser)
						{
							echo '<div class="panel panel-default">
								<div class="panel-heading">Send a private message</div>

								<div class="panel-body">
									<div class="row">
										<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
											<label for="receiver">Receiver Username: </label>
											<input id="receiver" type="text" class="form-control" placeholder="username" value="' . $oUser -> username . '" />
										</div>

										<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
											<label for="subject">Subject: </label>
											<input id="subject" type="text" class="form-control" placeholder="subject" autofocus />
										</div>
									</div>

									<div class="row">
										<div class="col-xs-12">
											<label for="message">Message: </label> <br />

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

											<textarea id="message" class="form-control textarea-theme" rows="7"></textarea>
										</div>
									</div>

									<div class="row">
										<div class="col-xs-4 col-xs-offset-8">
											<button id="submit" type="button" class="btn btn-success pull-right">Send</button>
										</div>
									</div>
								</div>
							</div>';
						}
					}
					else if($arrUserData['userid'] == $_GET['u'])
					{
						echo '<div class="recentactivity">You can\'t send a message to yourself.</div>';
					}
				?>
			</div>
		</div>

		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/sidebar.js"></script>

		<script>
			$(document).ready(function(){
				$("#submit").on('click', function(){
					var subject = $("#subject").val();
					var message = $("#message").val();

					$.ajax({
						type: "POST",
						url: "../sendmessage_ajax.php",
						dataType: "html",
						data: {
							userid: $("#receiver").val(),
							subject: subject,
							message: message
						},
						success: function(evt){
							$("#errormessagespan").text(evt);
							$("#errormessage").show();
						}
					})
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

					var textarea = document.getElementById("message");

					if($("#message").is(":focus"))
					{
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[b]' + select + '[/b]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$("#italic").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#message").is(":focus"))
					{
						var textarea = document.getElementById("message");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[i]' + select + '[/i]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$("#underline").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#message").is(":focus"))
					{
						var textarea = document.getElementById("message");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[u]' + select + '[/u]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$("#strike").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#message").is(":focus"))
					{
						var textarea = document.getElementById("message");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[strike]' + select + '[/strike]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$(".size").on('mousedown', function(evt){
					evt.preventDefault();

					var textarea = document.getElementById("message");
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

					if($("#message").is(":focus"))
					{
						var textarea = document.getElementById("message");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[spoiler]' + select + '[/spoiler]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});

				$("#spoilerbox").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#message").is(":focus"))
					{
						var textarea = document.getElementById("message");

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
							var textarea = document.getElementById("message");
							var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
							var replace = '[spoilerbox]' + select + '[/spoilerbox]';

							textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
						}
					}
				});

				$("#url").on('mousedown', function(evt){
					evt.preventDefault();

					if($("#message").is(":focus"))
					{
						var textarea = document.getElementById("message");

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

					if($("#message").is(":focus"))
					{
						var textarea = document.getElementById("message");
						var select = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd);
						var replace = '[img]' + select + '[/img]';

						textarea.value = textarea.value.substring(0, textarea.selectionStart) + replace + textarea.value.substring(textarea.selectionEnd, textarea.value.length);
					}
				});
			});
		</script>
	</body>
</html>
