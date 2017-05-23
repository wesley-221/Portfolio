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

		<title>Messages &ndash; Portfolio</title>
	</head>

	<body>
		<?php include_once 'resources/includes/navbar.php'; ?>
		<?php $includePageActive = "message"; include_once 'resources/includes/sidebar.php'; ?>

		<div class="content">
			<div class="container-fluid">
				<div class="blogtable">
					<?php
						$ajaxUserid = '@INVALID@';

						if(isset($_GET['frq']) && strlen($_GET['frq']) > 0) # frq = friend request url see below for different arguments
						{
							$explFriendRequest = explode('/', $_GET['frq']);

							// check if it is a friend request
							if(!strcmp($explFriendRequest[0], 'friendrequest'))
							{
								// data what is inside "$_GET['frq']"
								// 0 = "friendrequest"
								// 1 = "userfriend"
								// 2 = "otheruser"
								// 3 = "safetycheck"

								$qFriendCheck = DB::getInstance() -> query('SELECT safetycheck FROM friends WHERE userfriend = ? AND otheruser = ? AND safetycheck = ?', array($explFriendRequest[1], $explFriendRequest[2], $explFriendRequest[3]));

								if($qFriendCheck -> count() > 0) # Confirm if you actually do have a friendrequest
								{
									$qUser = DB::getInstance() -> query('SELECT userid, username FROM users WHERE userid = ?', array($explFriendRequest[1]));

									echo '<div class="panel panel-default">
											<div class="panel-heading">Add a friend</div>
												<div class="panel-body">
													Do you want to add "<a href="../profile/' . $qUser -> first() -> userid . '">' . $qUser -> first() -> username . '</a>" to your friends?

													<div class="row-buffer-10"></div>
													<button id="accept" type="button" class="btn btn-success pull-right" style="margin-left: 2px;">Accept</button>
													<button id="decline" type="button" class="btn btn-danger pull-right" style="margin-left: 2px;">Decline</button>
													<button id="hide" type="button" class="btn btn-warning pull-right">Hide notification</button>
												</div>
											</div>';
								}
								else # Values don't match database values, give error
								{
									echo 'Invalid friend request';
								}
							}
						}
						else # Don't show the friendrequests, instead show private messages
						{
							if(isset($_GET['m']) && strlen($_GET['m']) > 0)
							{
								if(isset($_GET['action']) && !strcmp($_GET['action'], 'reply')) # Reply to a message
								{
									$qMessage = DB::getInstance() -> query('SELECT senderid, receiverid, subject, message, date FROM messages WHERE messageid = ?', array($_GET['m']));
									$ajaxUserid = $qMessage -> first() -> senderid;

									# get the data from the message you are replying to
									foreach($qMessage -> results() as $oMessage)
									{
										$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($oMessage -> senderid));

										# get the username from userid and show the message form
										foreach($qUser -> results() as $oUser)
										{
											echo '<div class="row">
												<div class="col-xs-12 col-md-6 col-sm-6 col-lg-6 col-md-offset-3 col-sm-offset-3 col-lg-offset-3" style="display: none;" id="errormessage">
													<div class="alert alert-danger alert-dismissable">
														<button type="button" class="close" data-dismiss="alert">×</button>
														<span id="errormessagespan"></span>
													</div>
												</div>
											</div>

											<div class="panel panel-default">
												<div class="panel-heading">Send a private message</div>

												<div class="panel-body">
													<div class="row">
														<div class="col-xs-6">
															<label for="receiver">Receiver Username: </label>
															<input id="receiver" type="text" class="form-control" placeholder="username" value="' . $oUser -> username . '" />
														</div>

														<div class="col-xs-6">
															<label for="subject">Subject: </label>
															<input id="subject" type="text" class="form-control" placeholder="subject" value="' . $oMessage -> subject . '" />
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

															<textarea id="message" class="form-control textarea-theme" rows="7" autofocus>[quote="' . $oUser -> username . '"]' . $oMessage -> message . '[/quote]&#10;</textarea>
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
								}
								else # Show the private message from X user
								{
									$qMessage = DB::getInstance() -> query('SELECT senderid, receiverid, subject, message, messageread, date FROM messages WHERE messageid = ? AND receiverid = ?', array($_GET['m'], $arrUserData['userid']));

									if($qMessage -> count() > 0)
									{
										# show the message
										foreach($qMessage -> results() as $oMessage)
										{
											# set the message as read
											if($oMessage -> messageread == 0)
											{
												DB::getInstance() -> query('UPDATE messages SET messageread = 1 WHERE messageid = ?', array($_GET['m']));
											}

											$qUser = DB::getInstance() -> query('SELECT userid, username FROM users WHERE userid = ?', array($oMessage -> senderid));

											$dtMessageDate = date('d F Y, H:i:s', strtotime($oMessage -> date));

											echo '<div class="panel panel-default">
												<div class="panel-heading">Private message from "' . $qUser -> first() -> username . '"</div>

												<div class="panel-body">
													<div class="row">
														<div class="col-xs-12 col-sm-5 col-md-5">
															<label for="username">Username Sender</label>
															<div id="username"><a href="../profile/' . $qUser -> first() -> userid . '">' . $qUser -> first() -> username . '</a></div>
														</div>

														<div class="col-xs-12 col-sm-5 col-md-5">
															<label for="subject">Subject</label>
															<div id="subject">' . $oMessage -> subject . '</div>
														</div>

														<div class="col-xs-12 col-sm-2 col-md-2">
															<button id="markunread" type="button" class="btn btn-default pull-right">Mark this message as unread</button>
														</div>
													</div>

													<div class="row">
														<div class="col-xs-12">
														<label for="messagedate">Sent at date:</label>
														<div id="messagedate">' . $dtMessageDate . '</div>
														</div>
													</div>

													<div class="row" style="margin-top: 7px;">
														<div class="col-xs-12">
															<label for="message">Message</label>
															<div id="message" class="showmessage">' . Functions::bb_parse($oMessage -> message) . '</div>
														</div>
													</div>

													<div class="row" style="margin-top: 7px;">
														<div class="col-xs-12">
															<a href="../messages/' . $_GET['m'] . '/reply" class="btn btn-success pull-right" style="margin-left: 7px;">Reply</a>
															<button type="button" id="deletemessage" class="btn btn-danger pull-right">Delete this message</button>
														</div>
													</div>
												</div>
											</div>';
										}
									}
									else # You don't have the permission to see this message
									{
										echo '<div class="recentactivity">This message has not been send to you.</div>';
									}
								}
							}
							else # Show all the messages received
							{
								echo '<div class="inbox">';
									# show how full your inbox is with friend requests in percentages
									$qMessages = DB::getInstance() -> query('SELECT userfriend, otheruser, safetycheck FROM friends WHERE otheruser = ? AND notification = 1', array($arrUserData['userid']));

									if($qMessages -> count() != 0)
									{
										if($qMessages -> count() >= 100)
										{
											$iPercentage = 100;
										}
										else
										{
											$iPercentage = $qMessages -> count();
										}

										echo '<div class="row">
											<div class="messagebox col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3">
												<div class = "progress">';
												if($iPercentage <= 50)
												{
													echo '<div class = "progress-bar progress-bar-success" style = "width: ' . $iPercentage . '%"></div>';
												}
												else if($iPercentage >= 51 && $iPercentage <= 80)
												{
													echo '<div class = "progress-bar progress-bar-success" style = "width: 50%"></div>
														<div class = "progress-bar progress-bar-warning progress-bar-striped" style = "width: ' . ($iPercentage - 50) . '%"></div>';
												}
												else if($iPercentage >= 81 && $iPercentage <= 100)
												{
													echo '<div class = "progress-bar progress-bar-success" style = "width: 50%"></div>
														<div class = "progress-bar progress-bar-warning progress-bar-striped" style = "width: 30%"></div>
														<div class = "progress-bar progress-bar-danger" style = "width: ' . ($iPercentage - 80) . '%"></div>';
												}

												echo '</div>
												You have ' . $qMessages -> count() . '/' . Config::get('config/max_friend_requests') . ' friend request(s).
											</div>
										</div>';
									}

								echo '</div>

								<div class="panel">
									<div class="panel-header">
                                        <a class="no-underline" data-toggle="collapse" href="#friendrequests">
                                            <div class="panel-heading panel-border">
                                                <h4 class="panel-title" align="center">
                                                    <i>Friend Requests<span class="pull-right">Click here to show/hide</span></i>
                                                </h4>
                                            </div>
                                        </a>
									</div>

                                    <div id="friendrequests" class="panel-collapse collapse panel-text">
                                        <div class="panel-body">';

										# loop through all friend requests
										foreach ($qMessages -> results() as $oMessage)
										{
											$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($oMessage -> userfriend));
											echo '<div class="col-xs-6" style="display: inline-block;">
													<a href="../messages/friendrequest/' . $oMessage -> userfriend . '/' . $oMessage -> otheruser . '/' . $oMessage -> safetycheck . '">
														<div class="message cutofftextfile">
															<i class="fa fa-envelope-o"></i> Friend request from <b>' . $qUser -> first() -> username . '</b>
														</div>
													</a>
												</div>';
										}

                                        echo '</div>
                                    </div>
                                </div>';

								# show how full your inbox is with messages in percentages
								$qMessages = DB::getInstance() -> query('SELECT messageid, senderid, receiverid, subject, message, messageread, date FROM messages WHERE receiverid = ? ORDER BY ' . $arrUserData['ordermessage'] . ' ' . $arrUserData['orderoptions'], array($arrUserData['userid']));

								if($qMessages -> count() != 0)
								{
									$iPercentage = ($qMessages -> count() / Config::get('config/max_messages')) * 100; // calculate how far the bar should be filled

									$selMessageId 	= !strcmp($arrUserData['ordermessage'], "messageid") ? "selected" : "";
									$selMessageRead = !strcmp($arrUserData['ordermessage'], "messageread") ? "selected" : "";
									$selDate 		= !strcmp($arrUserData['ordermessage'], "date") ? "selected" : "";
									$selAscend		= !strcmp(strtolower($arrUserData['orderoptions']), "asc") ? "selected" : "";
									$selDescend		= !strcmp(strtolower($arrUserData['orderoptions']), "desc") ? "selected" : "";

									echo '<div class="inbox">
										<div class="row">
											<div class="col-xs-10 col-xs-offset-1 col-sm-offset-0 col-sm-3">
												<select id="sortoptions" class=" form-control select-theme" style="width: 200px; margin-left: 5px;">
													<option value="messageid" ' . $selMessageId . '>Sort by messageid</option>
													<option value="messageread" ' . $selMessageRead . '>Sort by messageread</option>
													<option value="date" ' . $selDate . '>Sort by date</option>
												</select>

												<div style="padding-top: 5px;"></div>

												<select id="orderoptions" class=" form-control select-theme" style="width: 200px; margin-left: 5px;">
													<option value="asc" ' . $selAscend . '>Ascend</option>
													<option value="desc" ' . $selDescend . '>Descend</option>
												</select>
											</div>

											<div class="messagebox col-xs-10 col-xs-offset-1 col-sm-offset-0 col-sm-6">
												<div class = "progress">';
												if($iPercentage <= 50)
												{
													echo '<div class = "progress-bar progress-bar-success" style = "width: ' . $iPercentage . '%"></div>';
												}
												else if($iPercentage >= 51 && $iPercentage <= 80)
												{
													echo '<div class = "progress-bar progress-bar-success" style = "width: 50%"></div>
														<div class = "progress-bar progress-bar-warning progress-bar-striped" style = "width: ' . ($iPercentage - 50) . '%"></div>';
												}
												else if($iPercentage >= 81 && $iPercentage <= 100)
												{
													echo '<div class = "progress-bar progress-bar-success" style = "width: 50%"></div>
														<div class = "progress-bar progress-bar-warning progress-bar-striped" style = "width: 30%"></div>
														<div class = "progress-bar progress-bar-danger" style = "width: ' . ($iPercentage - 80) . '%"></div>';
												}

												echo '</div>

												You have ' . $qMessages -> count() . '/' . Config::get('config/max_messages') . ' message(s) in your inbox.
											</div>
										</div>
									</div>';

									# loop through all messages and show if they're read or unread
									foreach($qMessages -> results() as $oMessage)
									{
										$qUser = DB::getInstance() -> query('SELECT username FROM users WHERE userid = ?', array($oMessage -> senderid));

										$dtMessageDate = explode(' ', $oMessage -> date);

										if($oMessage -> messageread == 0)
										{
											echo '<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="inline-block"><a href="../messages/' . $oMessage -> messageid . '"><div class="message cutofftextfile"><img src="../resources/images/file_unread.png" /> (' . $oMessage -> messageid . ') <b>' . $qUser -> first() -> username . '</b>: ' . $oMessage -> subject . ' <span class="pull-right">(' . date('d-m-Y', strtotime($dtMessageDate[0])) . ')</span></div></a></div>';
										}
										else
										{
											echo '<div class="col-xs-12 col-sm-12 col-md-6 col-lg-6" style="inline-block"><a href="../messages/' . $oMessage -> messageid . '"><div class="message cutofftextfile"><img src="../resources/images/file_read.png" /> (' . $oMessage -> messageid . ') <b>' . $qUser -> first() -> username . '</b>: ' . $oMessage -> subject . ' <span class="pull-right">(' . date('d-m-Y', strtotime($dtMessageDate[0])) . ')</span></div></a></div>';
										}
									}
								}
								else
								{
									# no messages have been found
									echo '<div class="messagebox col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3">
										You don\'t have any messages.
									</div>';
								}
							}
						}
					?>
				</div>
			</div>
		</div>

		<script src="../js/jquery-1.11.1.min.js"></script>
		<script src="../js/bootstrap.min.js"></script>
		<script src="../js/sidebar.js"></script>

		<script>
			$(document).ready(function(){
				$("#deletemessage").on('click', function(){
					$.ajax({
						type: "POST",
						url: "../messages_ajax.php",
						dataType: "html",
						data: {
							type : "deletemessage",
							messageid : <?php echo isset($_GET['m']) ? json_encode($_GET['m']) : 'N/A'; ?>
						},
						success: function(evt){
							alert('This message has been deleted');
							window.location.href = "../messages/";
						}
					})
				});

				$("#sortoptions").on('change', function(){
					$.ajax({
						type: "POST",
						url: "../messages_ajax.php",
						dataType: "html",
						data: {
							type : "sortingoptions",
							orderoption : $("#sortoptions").val()
						},
						success: function(){
							location.reload(true);
						}
					})
				});

				$("#orderoptions").on('change', function(){
					$.ajax({
						type: "POST",
						url: "../messages_ajax.php",
						dataType: "html",
						data: {
							type : "orderoptions",
							orderoption : $("#orderoptions").val()
						},
						success: function(){
							location.reload(true);
						}
					})
				});

				$("#markunread").on('click', function(){
					$.ajax({
						type: "POST",
						url: "../messages_ajax.php",
						dataType: "html",
						data: {
							type : "markunread",
							messageid : <?php echo isset($_GET['m']) ? json_encode($_GET['m']) : 'N/A'; ?>
						},
						success: function(){
							alert('This message has been marked as unread.');
							window.location.href = "../messages/";
						}
					})
				});

				$("#accept").on('click', function(){
					var friendRequest = <?php echo isset($_GET['frq']) ? json_encode($_GET['frq']) : 'N/A'; ?>;

					if(urlSplit != "N/A")
					{
						var urlSplit = friendRequest.split('/');

						$.ajax({
							type: "POST",
							url: "../messages_ajax.php",
							dataType: "html",
							data: {
								userfriend : urlSplit[1],
								otheruser : urlSplit[2],
								safetycheck : urlSplit[3],
								type : "accept"
							},
							success: function(evt){
								alert(evt);
								window.location.href = '../profile/' + urlSplit[1];
							}
						})
					}
				});

				$("#decline").on('click', function(){
					var friendRequest = <?php echo isset($_GET['frq']) ? json_encode($_GET['frq']) : 'N/A'; ?>;

					if(urlSplit != "N/A")
					{
						var urlSplit = friendRequest.split('/');

						$.ajax({
							type: "POST",
							url: "../messages_ajax.php",
							dataType: "html",
							data: {
								userfriend : urlSplit[1],
								otheruser : urlSplit[2],
								safetycheck : urlSplit[3],
								type : "decline"
							},
							success: function(evt){
								alert(evt);
								window.location.href = '../profile/' + urlSplit[1];
							}
						})
					}
				});

				$("#hide").on('click', function(){
					var friendRequest = <?php echo isset($_GET['frq']) ? json_encode($_GET['frq']) : 'N/A'; ?>;

					if(urlSplit != "N/A")
					{
						var urlSplit = friendRequest.split('/');

						$.ajax({
							type: "POST",
							url: "../messages_ajax.php",
							dataType: "html",
							data: {
								userfriend : urlSplit[1],
								otheruser : urlSplit[2],
								safetycheck : urlSplit[3],
								type : "hide"
							},
							success: function(evt){
								alert(evt);
								window.location.href = '../profile/' + urlSplit[1];
							}
						})
					}
				});

				$("#submit").on('click', function(){
					var subject = $("#subject").val();
					var message = $("#message").val();

					$.ajax({
						type: "POST",
						url: "../sendmessage_ajax.php",
						dataType: "html",
						data: {
							userid: <?php echo json_encode($ajaxUserid); ?>,
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
