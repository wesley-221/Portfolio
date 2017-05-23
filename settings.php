<?php
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "settings");
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
		<?php $includePageActive = "settings"; include_once 'resources/includes/sidebar.php'; ?>

		<div class="content">
			<div class="container-fluid">
				<table class="table table-striped">
					<tbody>
						<tr>
							<td>Choose what theme you want to use</td>

							<td>
								<select id="pick-theme" class="select-theme form-control">
									<?php
										echo '<option value="light"' . (!strcmp($arrUserData['theme'], "style-light.css") ? "selected" : "") . '>Light Theme</option>
											<option value="dark"' . (!strcmp($arrUserData['theme'], "style-dark.css") ? "selected" : "") . '>Dark Theme</option>';
									?>
								</select>
							</td>
						</tr>

						<tr>
							<td>Profile accessibility</td>

							<td>
								<select id="accessibility" class="select-theme form-control">
									<?php
										echo '<option value="public"' . (!strcmp($arrUserData['visibility'], "public") ? "selected" : "") . '>Available for everyone</option>
											<option value="friends"' . (!strcmp($arrUserData['visibility'], "friends") ? "selected" : "") . '>Available for friends</option>
											<option value="private"' . (!strcmp($arrUserData['visibility'], "private") ? "selected" : "") . '>Available to no one but yourself</option>';
									?>
								</select>
							</td>
						</tr>

						<tr>
							<td>Show uploads on profile</td>
							<td>
								<select id="showuploads" class="select-theme form-control">
									<?php
										echo '<option value="public"' . (!strcmp($arrUserData['showuploads'], "public") ? "selected" : "") . '>Available for everyone</option>
											<option value="friends"' . (!strcmp($arrUserData['showuploads'], "friends") ? "selected" : "") . '>Available for friends</option>
											<option value="private"' . (!strcmp($arrUserData['showuploads'], "private") ? "selected" : "") . '>Available to no one but yourself</option>';
									?>
								</select>
							</td>
						</tr>

						<tr>
							<td>Show blogs on profile</td>
							<td>
								<select id="showblogs" class="select-theme form-control">
									<?php
										echo '<option value="public"' . (!strcmp($arrUserData['showblogs'], "public") ? "selected" : "") . '>Available for everyone</option>
											<option value="friends"' . (!strcmp($arrUserData['showblogs'], "friends") ? "selected" : "") . '>Available for friends</option>
											<option value="private"' . (!strcmp($arrUserData['showblogs'], "private") ? "selected" : "") . '>Available to no one but yourself</option>';
									?>
								</select>
							</td>
						</tr>

						<tr>
							<td>Allow everyone to see my e-mail</td>
							<td>
								<select id="showemail" class ="select-theme form-control">
									<?php
										echo '<option value="yes"' . (!strcmp($arrUserData['showemail'], "yes") ? "selected" : "") . '>Yes</option>
											<option value="no"' . (!strcmp($arrUserData['showemail'], "no") ? "selected" : "") . '>No</option>';
									?>
								</select>
							</td>
						</tr>

						<tr>
							<td>Show all friend request notifications</td>
							<td>
								<button id="friendnotification" class="btn btn-default"><i id="fn_spinner" class="fa fa-spinner fa-spin" style="display: none;"></i> Proccess</button> <span id="showtext"></span>
							</td>
						</tr>
					</tbody>
				</table>

				<div id="test"></div>
			</div>
		</div>

		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/sidebar.js"></script>

		<script>
			$(document).ready(function(){
				$("#pick-theme").on('change', function(){
					$.ajax({
						type: "POST",
						url: "../settings_ajax.php",
						dataType: "html",
						data: { theme : $("#pick-theme").val() },
						success: function(response){
							$("#link-theme").attr("href", "css/style-" + $("#pick-theme").val() + ".css");
						}
					})
				});

				$("#accessibility").on('change', function(){
					$.ajax({
						type: "POST",
						url: "../settings_ajax.php",
						dataType: "html",
						data: { accessibility : $("#accessibility").val() }
					})
				});

				$("#showemail").on('change', function(){
					$.ajax({
						type: "POST",
						url: "../settings_ajax.php",
						dataType: "html",
						data: { showemail : $("#showemail").val() }
					})
				});

				$("#showuploads").on('change', function(){
					$.ajax({
						type: "POST",
						url: "../settings_ajax.php",
						dataType: "html",
						data: { showuploads : $("#showuploads").val() }
					})
				});

				$("#showblogs").on('change', function(){
					$.ajax({
						type: "POST",
						url: "../settings_ajax.php",
						dataType: "html",
						data: { showblogs : $("#showblogs").val() }
					})
				});

				$("#friendnotification").on('click', function(){
					$.ajax({
						type: "POST",
						url: "../settings_ajax.php",
						dataType: "html",
						data: { shownotifications : <?php echo $arrUserData['userid']; ?> },
						beforeSend: function(){
							$("#fn_spinner").show();
						},
						success: function(evt){
							if(evt.length > 0)
							{
								$("#showtext").text(evt);
							}
							else
							{
								$("#showtext").text("Notifications have been re-enabled.");
							}

							$("#showtext").show();
						},
						complete: function(evt){
							$("#fn_spinner").hide();

							setTimeout(function(){
								$("#showtext").hide();
							}, 2000);
						}

					})
				});
			});
		</script>
	</body>
</html>
