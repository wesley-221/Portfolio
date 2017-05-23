<?php
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, 0);

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

		<title>Register &ndash; Portfolio</title>

		<!--[if lt IE 9]>
			<script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6/html5shiv.min.js"></script>
			<script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
		<![endif]-->
	</head>

	<body>
		<?php include_once 'resources/includes/navbar.php'; ?>
		<?php $includePageActive = "none"; include_once 'resources/includes/sidebar.php'; ?>

		<div class = "container-fluid">
			<?php
				// user posted the form, handle register part
				$sFirstName 	= isset($_POST['firstname']) ? $_POST['firstname'] : '';
				$sLastName		= isset($_POST['lastname']) ? $_POST['lastname'] : '';
				$sUsername		= isset($_POST['username']) ? $_POST['username'] : '';
				$sEmail			= isset($_POST['email']) ? $_POST['email'] : '';
				$sPassword		= isset($_POST['password']) ? $_POST['password'] : '';
				$sPasswordConf	= isset($_POST['passwordconf']) ? $_POST['passwordconf'] : '';

				if($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					$sErrorMessage = "";

					// validation
					$qValidationUsername = DB::getInstance() -> query('SELECT userid FROM users WHERE username = ?', array($sUsername));
					if($qValidationUsername -> count() > 0)
					{
						$sErrorMessage .= "The username \"" . $sUsername . "\" is already in use <br />";
					}

					$qValidationEmail = DB::getInstance() -> query('SELECT userid FROM users WHERE email = ?', array($sEmail));
					if($qValidationEmail -> count() > 0)
					{
						$sErrorMessage .= "The email \"" . $sEmail . "\" is already in use <br />";
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

					if(strlen($sPassword) < Config::get('config/validation/passwordmin') || strlen($sPassword) > Config::get('config/validation/passwordmax'))
					{
						$sErrorMessage .= "\"Password\" does not meet the requirements. Minimum length: " . Config::get('config/validation/passwordmin') . ". Maximum length: " . Config::get('config/validation/passwordmax') . "<br />";
					}
					else
					{
						if(strlen($sPasswordConf) < Config::get('config/validation/passwordmin') || strlen($sPasswordConf) > Config::get('config/validation/passwordmax'))
						{
							$sErrorMessage .= "\"Password (confirmation)\" does not meet the requirements. Minimum length: " . Config::get('config/validation/passwordmin') . ". Maximum length: " . Config::get('config/validation/passwordmax') . "<br />";
						}
						else
						{
							if(strcmp($sPassword, $sPasswordConf))
							{
								$sErrorMessage .= "The two passwords you entered do not match <br />";
							}
						}
					}

					if(strlen($sErrorMessage) > 0)
					{
						# error found
						echo '<div class="alert alert-danger alert-dismissable col-md-4 col-md-offset-4"><button type="button" class="close" data-dismiss="alert">×</button>' . $sErrorMessage . '</div>';
					}
					else
					{
						# register user
						$sSalt = User::CreateSalt(50);
						$dtJoinDate = date("Y-m-d");
						$country = json_decode(file_get_contents('http://ipinfo.io/' . $_SERVER['REMOTE_ADDR']));

						if(!strcmp($country -> hostname, "localhost.localdomain"))
						{
							$sCountry = 'NL';
						}
						else
						{
							$sCountry = $country -> country;
						}

						User::Register($sUsername, $sFirstName, $sLastName, $sEmail, User::HashPassword($sSalt, $sPassword), $sSalt, 1, $sCountry, $dtJoinDate);

						echo '<div class="alert alert-success alert-dismissable col-md-4 col-md-offset-4"><button type="button" class="close" data-dismiss="alert">×</button>Your account has been succesfully created. <br />You can log in with the username "' . $sUsername . '". <br /><a href="../login">You will be redirected to the log in page in 5 seconds or by clicking on here. </a></div>';
						header('Refresh: 5; URL=login');
					}
				}
			?>

			<form action="../register" method="post" autocomplete="on">
				<div class="row">
			        <section class="col-md-6 col-md-offset-3">
			            <div class="panel panel-default">
			                <div class="panel-heading">
			                    <h1 class="panel-title">Sign in</h1>
			                </div>
			                <div class="panel-body">
			                    <div class="form-group">
									<div class = "row">
										<div class = "col-xs-6">
											<label for="firstname">First name</label>
											<input id="firstname" name="firstname" type="text" placeholder="first name" class="form-control" value="<?php echo $sFirstName; ?>" />
										</div>

										<div class = "col-xs-6">
											<label for="lastname">Last name</label>
											<input id="lastname" name="lastname" type="text" placeholder="last name" class="form-control" value="<?php echo $sLastName; ?>" />
										</div>
									</div>
			                    </div>

			                    <div class="form-group">
									<label for="username">Username</label>
									<input id="username" name="username" type="text" placeholder="username" class="form-control" value="<?php echo $sUsername; ?>" />
			                    </div>

								<div class="form-group">
									<label for="email">E-mail</label>
									<input id="email" name="email" type="email" placeholder="e-mail" class="form-control" value="<?php echo $sEmail; ?>" />
			                    </div>

								<div class="form-group">
									<label for="password">Password</label>
									<input id="password" name="password" type="password" placeholder="password" class="form-control" />
			                    </div>

								<div class="form-group">
									<label for="passwordconf">Password (confirmation)</label>
									<input id="passwordconf" name="passwordconf" type="password" placeholder="password" class="form-control" />
			                    </div>

			                    <div class="pull-right">
									<p><button id="submit" type="submit" class="btn btn-default">Register</button></p>
			                    </div>
			                </div>
			            </div>
			        </section>
			    </div>
			</form>
		</div>

		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/sidebar.js"></script>
	</body>
</html>
