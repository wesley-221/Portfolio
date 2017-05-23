<?php
	require_once 'core/init.php';
	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';

	$arrUserData = User::Authenticate($sCookie, $arrUserData, 0);

	# redirect if user is already logged in
	if($arrUserData['loggedin'] == 1)
	{
		Header("Location: ../");
	}
?>

<html>
	<head>
		<meta charset="utf-8">
		<base href="/">
		<meta name="viewport" content="minimum-scale=1.0, width=device-width, maximum-scale=1.0, user-scalable=no">
		<link media="all" type="text/css" rel="stylesheet" href="../css/bootstrap.min.css">
		<?php echo '<link media="all" id = "link-theme" type="text/css" rel="stylesheet" href="../css/' . $arrUserData['theme'] . '" />'; ?>
		<link rel="shortcut icon" type="image/png" href="../resources/images/favicon.png" />

		<title>Login &ndash; Portfolio</title>

		<!--[if lt IE 9]>
			<script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6/html5shiv.min.js"></script>
			<script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
		<![endif]-->
	</head>

	<body>
		<?php include_once 'resources/includes/navbar.php'; ?>
		<?php $includePageActive = "none"; include_once 'resources/includes/sidebar.php'; ?>

		<div class = "container">
			<?php
				$sUsername = isset($_POST['username']) ? $_POST['username'] : '';
				$sPassword = isset($_POST['password']) ? $_POST['password'] : '';

				if($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					if(strlen($sUsername) < Config::get('config/validation/namemin') || strlen($sUsername) > Config::get('config/validation/namemax'))
					{
						echo '<div class="alert alert-danger alert-dismissable col-md-4 col-md-offset-4"><button type="button" class="close" data-dismiss="alert">×</button> "Username" does not meet the requirements. Minimum length: ' . Config::get('config/validation/namemin') . '. Maximum length: ' . Config::get('config/validation/namemax') . '.<br /></div>';
					}
					else
					{
						# get data from user registered under $_POST['username']
						$qUser = DB::getInstance() -> query('SELECT userid, username, salt, password FROM users WHERE username = ?', array($sUsername));

						if($qUser -> count() > 0)
						{
							# setting the variables up for comparison
							$iDBUserId = $qUser -> first() -> userid;
							$sDBUsername = $qUser -> first() -> username;

							$sDBSalt = $qUser -> first() -> salt;
							$sDBPassword = $qUser -> first() -> password;
							$sUserPassword = User::HashPassword($sDBSalt, $sPassword);

							# compare password, log in if equal
							if(!strcmp($sDBPassword, $sUserPassword))
							{
								$sCookieValue = User::GenerateCookie($sUsername);
								setcookie(Config::get('config/cookie/cookie_name'), $sCookieValue, strtotime("+1 month"));
								DB::getInstance() -> insert('cookies', array('userid' => $iDBUserId, 'date' => date("Y-m-d"), 'cookie' => $sCookieValue, 'hostname' => gethostname()));

								echo '<div class="alert alert-success alert-dismissable col-xs-12 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3"><button type="button" class="close" data-dismiss="alert">×</button>You have been succesfully logged in. <br /><a href="../">You will be redirected to the homepage in 5 seconds or by clicking here. </a></div>';
								header('Refresh: 5; URL=../');
							}
							else
							{
								echo '<div class="alert alert-warning alert-dismissable col-xs-12 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3"><button type="button" class="close" data-dismiss="alert">×</button>Either the Username or Password was invalid. Please try again</div>';
							}
						}
						else
						{
							echo '<div class="alert alert-warning alert-dismissable col-xs-12 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3"><button type="button" class="close" data-dismiss="alert">×</button>Either the Username or Password was invalid. Please try again</div>';
						}
					}
				}
			?>

			<form action="../login" method="post" autocomplete="on">
				<div class="row">
			        <section class=" col-xs-12 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">
			            <div class="panel panel-default">
			                <div class="panel-heading">
			                    <h1 class="panel-title">Sign in</h1>
			                </div>
			                <div class="panel-body">
			                    <div class="form-group">
									<label for="username">Username</label>
									<input id="username" name="username" type="text" placeholder="username" class="form-control" />
			                    </div>

			                    <div class="form-group">
									<label for="password">Password</label>
									<input id="password" name="password" type="password" placeholder="password" class="form-control" />
			                    </div>

								<b><a href="#" id="cookietooltip" data-toggle="tooltip" data-placement="top" title="The cookie will be active for a month after that it will become inactive">(*)</a></b> <span class="pull-right"><a href="../register">Don't have an account? Click here!</a> <button id="submit" type="submit" class="btn btn-default">Sign in</button></span>
			                </div>
			            </div>
			        </section>
			    </div>
			</form>
		</div>

		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/sidebar.js"></script>

		<script>
			$(document).ready(function(){
				$("#cookietooltip").tooltip();
			});
		</script>
	</body>
</html>
