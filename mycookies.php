<?php
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "cookies");

	// The cookie can't be empty, so put something in the variable for the check
	$activeCookie = "N/A";
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
		<?php $includePageActive = "cookie"; include_once 'resources/includes/sidebar.php'; ?>

		<div class="content">
			<div class="container-fluid">
				<ol class="breadcrumb">
					<li><a href="../">Home</a></li>
					<li class="active">My cookies</li>
				</ol>

				<table class="table table-striped">
					<thead>
						<th colspan="5"><center>Your current active cookie is</center></th>
					</thead>

					<thead>
						<th>Cookie id</th>
						<th>Username</th>
						<th>Expire date</th>
						<th>Name of the host device</th>
						<th></th>
					</thead>

					<?php
						# loop through all cookies and look for the currenct active one
						$qCookie = DB::getInstance() -> query('SELECT cookieid, userid, date, hostname FROM cookies WHERE cookie = ?', array($sCookie));

						foreach($qCookie -> results() as $oCookie)
						{
							#show the cookies with a button to move to the cookie
							$activeCookie = $oCookie -> cookieid;
							$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($oCookie -> userid));
							echo '<tr>
									<td>' . $oCookie -> cookieid . '</td>
									<td><a href="../profile/' . $oCookie -> userid . '"><span style="color: yellow;">' . $qUser -> first() -> username . '</span></span></td>
									<td>' . date("d-m-Y", strtotime($oCookie -> date . " +1 month")) . '</td>
									<td>' . $oCookie -> hostname . '</td>
									<td><button class="btn btn-default" onClick="moveHere(' . $oCookie -> cookieid . ');">Move here</button></td>
								</tr>';
						}
					?>
				</table>

				<button id="deleteall" class="btn btn-default">Delete all inactive cookies</button>

				<table class="table table-striped">
					<thead>
						<th colspan="5"><center>All cookies connected to this account</center></th>
					</thead>

					<thead>
						<th>Cookie id</th>
						<th>Username</th>
						<th>Expire date</th>
						<th>Name of the host device</th>
						<th>Action</th>
					</thead>

					<tbody>
						<?php
							# loop through all cookies
							$qCookies = DB::getInstance() -> query('SELECT cookieid, userid, date, hostname FROM cookies WHERE userid = ?', array($arrUserData['userid']));

							foreach($qCookies -> results() as $oCookie)
							{
								# show the cookie with a button to delete or extend
								$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($oCookie -> userid));

								if($oCookie -> cookieid == $activeCookie)
								{
									# highlight this cookie as active cookie
									echo '<tr id="row' . $oCookie -> cookieid . '" class="delete' . $oCookie -> cookieid . '" style="border: 2px solid #787bde;">
											<td>' . $oCookie -> cookieid . '</td>
											<td><a href="../profile/' . $oCookie -> userid . '"><span style="color: yellow;">' . $qUser -> first() -> username . '</span></a></td>										<td>' . date("d-m-Y", strtotime($oCookie -> date . " +1 month")) . '</td>
											<td>' . $oCookie -> hostname . '</td>
											<td>
												<button id="delete' . $oCookie -> cookieid . '" type="button" class="btn btn-danger">Delete this</button>
												<button id="extend' . $oCookie -> cookieid . '" type="button" class="btn btn-success">Extend this</button>
											</td>
										</tr>';
								}
								else
								{
									echo '<tr id="row' . $oCookie -> cookieid . '" class="delete' . $oCookie -> cookieid . '">
											<td>' . $oCookie -> cookieid . '</td>
											<td><a href="../profile/' . $oCookie -> userid . '"><span style="color: yellow;">' . $qUser -> first() -> username . '</span></a></td>										<td>' . date("d-m-Y", strtotime($oCookie -> date . " +1 month")) . '</td>
											<td>' . $oCookie -> hostname . '</td>
											<td>
												<button id="delete' . $oCookie -> cookieid . '" type="button" class="btn btn-danger">Delete this</button>
												<button id="extend' . $oCookie -> cookieid . '" type="button" class="btn btn-success">Extend this</button>
											</td>
										</tr>';
								}
							}
						?>
					</tbody>
				</table>
			</div>
		</div>

		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/sidebar.js"></script>

		<script>
			$(document).ready(function(){
				<?php
					$qCookies = DB::getInstance() -> query("SELECT cookieid FROM cookies");

					foreach($qCookies -> results() as $oCookie)
					{
						echo '$("#delete' . $oCookie -> cookieid . '").on("click", function(){
							$.ajax({
								type: "POST",
								url: "../mycookies_ajax.php",
								dataType: "html",
								data: { userid: ' . $arrUserData['userid'] . ', cookieid: ' . $oCookie -> cookieid . ', action: "delete" },
								success: function(response) {
									$(".delete' . $oCookie -> cookieid . '").fadeOut("fast");
									alert(response);
								}
							})
						});

						$("#extend' . $oCookie -> cookieid . '").on("click", function(){
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

					echo '$("#deleteall").on("click", function(){
						$.ajax({
							type: "POST",
							url: "../mycookies_ajax.php",
							dataType: "html",
							data: { userid: ' . $arrUserData['userid'] . ', cookieid: ' . $activeCookie . ', action: "deleteall" },
							success: function(response) {
								alert(response);
								location.reload();
							}
						})
					});';
				?>
			});

			function moveHere(cookieid) {
				$('html, body').animate({ scrollTop: $("#row" + cookieid).offset().top - 100 }, 600);
			}
		</script>
	</body>
</html>
