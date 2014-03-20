<?php
	set_time_limit(0);
	
	function kosher($path) {
		$path = '/var/videos/' . $path;
		return (dirname(realpath($path)) === '/var/videos' && file_exists($path));
	}
	
	$clean_url = escapeshellarg($_GET['url']);
	$clean_file = escapeshellarg($_GET['file']);
	$clean_offset = escapeshellarg($_GET['offset']);
	
	if ($_GET['act'] == 'download' && $_GET['type'] == 'site') {
		chdir("/var/videos");
		exec("youtube-dl $clean_url");
			
	} elseif ($_GET['act'] == 'download' && $_GET['type'] == 'direct') {
		chdir("/var/videos");
		exec("wget $clean_url");
		
	} elseif ($_GET['act'] == 'play' && kosher($_GET['file'])) {
		passthru('killall ffmpeg');
		file_put_contents('nowplaying', $_GET['file']);
		chdir("/var/videos");
		exec("/var/www/bin/stream $clean_file $clean_offset");
		
	} elseif ($_GET['act'] == 'delete' && kosher($_GET['file'])) {
		chdir("/var/videos");
		unlink($_GET['file']);
		
	} elseif ($_GET['act'] == 'restart') {
		file_put_contents('nowplaying', '');
		exec('killall ffmpeg');
		//exec('/var/www/bin/server');
		//exec('youtube-dl -U');
		
	} elseif ($_GET['act'] == 'pause') {
		exec('/var/www/bin/pause');
		
	} elseif ($_GET['act'] == 'resume') {
		exec('/var/www/bin/resume');
		
	} elseif ($_GET['act'] == 'stop') {
		file_put_contents('nowplaying', '');
		exec('killall ffmpeg');
		
	}
	
		
