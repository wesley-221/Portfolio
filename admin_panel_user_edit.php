<?php
	Header("../"); # don't allow anyone on this webpage
	require_once 'core/init.php';

	$sCookie = isset($_COOKIE[Config::get('config/cookie/cookie_name')]) ? $_COOKIE[Config::get('config/cookie/cookie_name')] : '';
	User::Authenticate($sCookie, $arrUserData, "manageusers");

	$sUser = isset($_POST['user']) ? $_POST['user'] : '';

	# make the query with userid or username
	if(is_numeric($sUser))
	{
		$qUserQuery = DB::getInstance() -> query('SELECT userid, username, firstname, lastname, email, interests, aboutme, groupid FROM users WHERE userid = ?', array($sUser));
	}
	else
	{
		$qUserQuery = DB::getInstance() -> query('SELECT userid, username, firstname, lastname, email, interests, aboutme, groupid FROM users WHERE username = ?', array($sUser));
	}

	# check if the user exists, if true show user settings
	if($qUserQuery -> count() > 0)
	{
		$iGroupId = $qUserQuery -> first() -> groupid;

		$_SESSION['edituser'] = $qUserQuery -> first() -> userid;

		echo '<div class="row">
			<div class="col-xs-12">
				<form action="admin/users/' . $qUserQuery -> first() -> userid . '" method="post" autocomplete="on">
					<h3>Userdata of ' . $qUserQuery -> first() -> username . '</h3>
					<table class="table table-striped">
						<thead>
							<tr>
								<td style="width: 10%;"></td>
								<td style="width: 90%;"></td>
							</tr>
						</thead>

						<tbody>
							<tr>
								<td>Username: </td>
								<td><input type="text" name="username" class="form-control" value="' . $qUserQuery -> first() -> username . '" /></td>
							</tr>

							<tr>
								<td>First name: </td>
								<td><input type="text" name="firstname" class="form-control" value="' . $qUserQuery -> first() -> firstname . '" /></td>
							</tr>

							<tr>
								<td>Last name: </td>
								<td><input type="text" name="lastname" class="form-control" value="' . $qUserQuery -> first() -> lastname . '" /></td>
							</tr>

							<tr>
								<td>Email: </td>
								<td><input type="email" name="email" class="form-control" value="' . $qUserQuery -> first() -> email . '" /></td>
							</tr>

							<tr>
								<td>Interests: </td>
								<td><textarea name="interests" class="form-control textarea-theme" rows="6">' . $qUserQuery -> first() -> interests . '</textarea></td>
							</tr>

							<tr>
								<td>About me: </td>
								<td><textarea name="aboutme" class="form-control textarea-theme" rows="6">' . $qUserQuery -> first() -> aboutme . '</textarea></td>
							</tr>

							<tr>
								<td>Group: </td>

								<td>
									<select name="group" class="select-theme form-control">';
										$qGroups = DB::getInstance() -> query('SELECT groupid, groupname FROM groups');

										foreach($qGroups -> results() as $oGroup)
										{
											if($iGroupId == $oGroup -> groupid)
											{
												echo '<option value="' . $oGroup -> groupid . '" selected>' . $oGroup -> groupname . '</option>';
											}
											else
											{
												echo '<option value="' . $oGroup -> groupid . '">' . $oGroup -> groupname . '</option>';
											}

										}
									echo '</select>
								</td>
							</tr>

							<tr>
						</tbody>

						<tfoot>
							<tr>
								<td></td>
								<td><button type="sumbit" class="btn btn-default pull-right">Save</button></td>
							</tr>
						</tfoot>
					</table>
				</form>
			</div>
		</div>';
	}
	else
	{
		echo '<div class="alert alert-danger alert-dismissable col-md-4 col-md-offset-4"><button type="button" class="close" data-dismiss="alert">×</button>The user "' . $sUser . '" could not be found"</div>';
	}
