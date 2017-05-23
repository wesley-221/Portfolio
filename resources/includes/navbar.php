<nav class = "navbar navbar-default navbar-fixed-top" role = "navigation">
	<div class = "container navbar-inner">
		<button type="button" class="navbar-toggle pull-left" id="showmenu">
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>

		<a class="navbar-brand" href="/">Portfolio</a>
		<ul class="nav navbar-nav pull-right">
			<?php
				if($arrUserData['loggedin'] == 0)
				{
					echo '<li><a href="../login">Log in</a></li>';
				}

				if($arrUserData['loggedin'] == 1)
				{
					$qMessages = DB::getInstance() -> query('SELECT userfriend FROM friends WHERE otheruser = ? AND notification = 1', array($arrUserData['userid']));
					$totalMessages = $qMessages -> count();
					$qMessages = DB::getInstance() -> query('SELECT messageid FROM messages WHERE receiverid = ? AND messageread = 0', array($arrUserData['userid']));
					$totalMessages += $qMessages -> count();

					echo '<li><a href="../messages"><span class="badge">' . $totalMessages . '</span> message(s)</a></li>';
				}
			?>
		</ul>
	</div>
</nav>
