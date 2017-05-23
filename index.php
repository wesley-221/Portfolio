<?php
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "main");
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<base href="/">
		<meta name="viewport" content="minimum-scale=1.0, width=device-width, maximum-scale=1.0, user-scalable=no" />
		<link media="all" type="text/css" rel="stylesheet" href="../css/bootstrap.min.css" />
		<?php echo '<link media="all" id = "link-theme" type="text/css" rel="stylesheet" href="../css/' . $arrUserData['theme'] . '" />'; ?>
		<link media="all" type="text/css" rel="stylesheet" href = "../resources/font-awesome/css/font-awesome.min.css" />
		<link rel="shortcut icon" type="image/png" href="../resources/images/favicon.png" />

		<title>Index &ndash; Portfolio</title>
	</head>

	<body>
		<?php include_once 'resources/includes/navbar.php'; ?>
		<?php $includePageActive = "main"; include_once 'resources/includes/sidebar.php'; ?>

		<div class="content">
			<div class="container-fluid">
				<?php
					if($arrUserData['loggedin'] == 1)
					{
				?>
						<div class="row">
							<div class="col-xs-12">
								<div class="panel panel-default">
									<div class="panel-body">
										Welcome back, <?php echo $arrUserData['firstname'] . ' ' . $arrUserData['lastname']; ?>
									</div>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-4">
								<a href="../blogs" class="thumbnail h4" align="center">
									<h1><i class="fa fa-rss"></i></h1>
									<p>Create a blog entry</p>
								</a>
							</div>

							<div class="col-xs-4">
								<a href="../blogs/<?php echo $arrUserData['username']; ?>" class="thumbnail h4" align="center">
									<h1><i class="fa fa-rss"></i></h1>
									<p>My blogs</p>
								</a>
							</div>

							<div class="col-xs-4">
								<a href="../cookies" class="thumbnail h4" align="center">
									<h1><i class="fa fa-desktop"></i></h1>
									<p>My cookies</p>
								</a>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-4">
								<a href="../changelog" class="thumbnail h4" align="center">
									<h1><i class="fa fa-book"></i></h1>
									<p>Changelog</p>
								</a>
							</div>

							<div class="col-xs-4">
								<a href="../changelog/edit" class="thumbnail h4" align="center">
									<h1><i class="fa fa-wrench"></i></h1>
									<p>Manage changelog entries</p>
								</a>
							</div>

							<div class="col-xs-4">
								<a href="../uploadcenter" class="thumbnail h4" align="center">
									<h1><i class="fa fa-upload"></i></h1>
									<p>Upload center</p>
								</a>
							</div>
						</div>
				<?php
					}
					else
					{
				?>
						<div class="row">
							<div class="col-xs-4">
								<a href="../blogs" class="thumbnail h4" align="center">
									<h1><i class="fa fa-rss"></i></h1>
									<p>Show all blog entries</p>
								</a>
							</div>

							<div class="col-xs-4">
								<a href="../changelog" class="thumbnail h4" align="center">
									<h1><i class="fa fa-book"></i></h1>
									<p>Changelog</p>
								</a>
							</div>

							<div class="col-xs-4">
								<a href="../profile" class="thumbnail h4" align="center">
									<h1><i class="fa fa-user"></i></h1>
									<p>Profiles</p>
								</a>
							</div>
						</div>
				<?php
					}
				?>
			</div>
		</div>

		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/sidebar.js"></script>
	</body>
</html>
