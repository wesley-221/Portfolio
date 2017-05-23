<?php
	class Functions
	{
		/*
			<summary>
				generate_uniqueID($length)
				Generates a string with a given length

				$length: the length of the string
			</summary>
		*/
		public function generate_uniqueID($length)
		{
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randomString = '';

			for ($i = 0; $i < $length; $i++)
			{
				$randomString .= $characters[rand(0, strlen($characters) - 1)];
			}
			return $randomString;
		}

		/*
			<summary>
				formatSizeUnits($bytes)
				converts $bytes to a more suitable number that is easily readable

				$bytes: the byte you want to convert
			</summary>
		*/
		function formatSizeUnits($bytes)
	    {
	        if ($bytes >= 1073741824)
	        {
	            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
	        }
	        elseif ($bytes >= 1048576)
	        {
	            $bytes = number_format($bytes / 1048576, 2) . ' MB';
	        }
	        elseif ($bytes >= 1024)
	        {
	            $bytes = number_format($bytes / 1024, 2) . ' KB';
	        }
	        elseif ($bytes > 1)
	        {
	            $bytes = $bytes . ' bytes';
	        }
	        elseif ($bytes == 1)
	        {
	            $bytes = $bytes . ' byte';
	        }
	        else
	        {
	            $bytes = '0 bytes';
	        }

	        return $bytes;
		}

		/*
			<summary>
				reArrayFiles(&$file_post)
				Rearranges an array so that is easier to read

				&$file_post: The array you want to rearrange
				You don't have to create a new array to use this
			</summary>
		*/
		function reArrayFiles(&$file_post)
		{
		    $file_ary 	= array();
		    $file_count = count($file_post['name']);
		    $file_keys 	= array_keys($file_post);

		    for ($i = 0; $i < $file_count; $i++)
			{
		        foreach ($file_keys as $key)
				{
		            $file_ary[$i][$key] = $file_post[$key][$i];
		        }
		    }

		    return $file_ary;
		}

		/*
			<summary>
				bb_parse($string)
				Parses bb code ([b][/b], [i][/i], etc.) to HTML code (<b></b>, <i></i>, etc.)

				$string: the string that *MIGHT* contain bb code that you want to parse
			</summary>1
		*/
		function bb_parse($string) {
	        $tags = 'b|i|u|size|url|img|video|spoiler|spoilerbox|color|hl|strike|quote';

	        while(preg_match_all('`\[(' . $tags . ')=?(.*?)\](.+?)\[/\1\]`', $string, $matches))
	    	foreach($matches[0] as $key => $match)
	    	{
	            list($tag, $param, $innertext) = array($matches[1][$key], $matches[2][$key], $matches[3][$key]);
	            switch($tag)
	            {
	                case 'b': 		$replacement = '<strong>' . $innertext . '</strong>'; break;
	                case 'i': 		$replacement = '<em>' . $innertext . '</em>'; break;
	                case 'u': 		$replacement = '<u>' . $innertext . '</u>'; break;
					case 'strike':	$replacement = '<strike>' . $innertext . '</strike>'; break;
	                case 'size': 	$replacement = '<span style = "font-size:' . $param . 'px;">' . $innertext . '</span>'; break;
	                case 'color': 	$replacement = '<span style = "color: ' . $param . '">' . $innertext . '</span>'; break;
	                case 'center': 	$replacement = '<span style = "text-align: center;">' . $innertext . '</div>'; break;
					case 'img': 	$replacement = '<img src = "' . $innertext . '" class="img-responsive" />'; break;
					case 'spoiler': $replacement = '<span class="spoiler">' . $innertext . '</span>'; break;
					case 'hl': 		$replacement = '<span style = "background-color: red; color: white;">' . $innertext . '</span>'; break;
	                case 'quote':
						if($param)
						{
							$replacement = '<div class="quote-box">
								<div class="quote-by"><i class="fa fa-quote-right"></i> Originally posted by ' . $param . ' <i class="fa fa-quote-right"></i></div>

								<div class="quote-quote">
									' . $innertext . '
								</div>
							</div>';
						}
						else
						{
							$replacement = '<div class="quote-box">
								<div class="quote-by"><i class="fa fa-quote-right"></i> Originally posted by N/A <i class="fa fa-quote-right"></i></div>

								<div class="quote-quote">
									' . $innertext . '
								</div>
							</div>';
						}
					break;
	                case 'url':
	                    if($param)
	                    {
	                        if(strpos($param, 'http://') == false)
	                        {
	                            $param = 'http://' . $param;
	                        }
	                    }
	                    else
	                    {
	                        if(strpos($innertext, 'http://') == false)
	                        {
	                            $innertext = 'http://' . $innertext;
	                        }
	                    }

	                    $replacement = '<b><a class = "blue-text" href="' . ($param ? $param : $innertext) . '">' . $innertext . '</a></b>';
	                break;
					case 'spoilerbox':
						if($param)
						{
							$sRandomString = Functions::generate_uniqueID(5);
							$replacement = '<div class="panel">
											<div class = "panel-header">
		                                        <a class = "no-underline" data-toggle = "collapse" href = "#' . $sRandomString . '">
		                                            <div class = "panel-heading panel-border">
		                                                <h4 class = "panel-title" align = "center">
		                                                    <i>' . $param . '</i>
		                                                </h4>
		                                            </div>
		                                        </a>
											</div>

	                                        <div id = "' . $sRandomString . '" class = "panel-collapse collapse panel-text">
	                                            <div class = "panel-body">
	                                                ' . $innertext . '
	                                            </div>
	                                        </div>
	                                    </div>';
						}
						else
						{
							$sRandomString = Functions::generate_uniqueID(5);
							$replacement = '<div class = "panel-header">
		                                        <a class = "no-underline" data-toggle = "collapse" href = "#' . $sRandomString . '">
		                                            <div class = "panel-heading panel-border">
		                                                <h4 class = "panel-title" align = "center">
		                                                    <i>- Spoiler -</i>
		                                                </h4>
		                                            </div>
		                                        </a>

		                                        <div id = "' . $sRandomString . '" class = "panel-collapse collapse panel-text">
		                                            <div class = "panel-body">
		                                                ' . $innertext . '
		                                            </div>
		                                        </div>
		                                    </div>';
						}
					break;
	                case 'video':
	                    $videourl = parse_url($innertext);
	                    parse_str($videourl['query'], $videoquery);
	                    if(strpos($videourl['host'], 'youtube.com') !== FALSE) $replacement = '<embed src="http://www.youtube.com/v/' . $videoquery['v'] . '" type="application/x-shockwave-flash" width="425" height="344"></embed>';
	                    if(strpos($videourl['host'], 'google.com') !== FALSE) $replacement = '<embed src="http://video.google.com/googleplayer.swf?docid=' . $videoquery['docid'] . '" width="400" height="326" type="application/x-shockwave-flash"></embed>';
	                break;
	            }

	            $string = str_replace($match, $replacement, $string);
	        }
	        return $string;
	    }
	}
