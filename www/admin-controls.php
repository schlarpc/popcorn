<?php
	if (count(get_included_files()) == 1) {
		die();
	}

	function kosher($path) {
		$path = '/var/videos/' . $path;
		return (dirname(realpath($path)) === '/var/videos' && file_exists($path));
	}

	if (isset($_POST['command'])) {
		set_time_limit(0);
		
		$url = escapeshellarg(isset($_POST['url']) ? $_POST['url'] : '');
		$file = escapeshellarg(isset($_POST['file']) ? $_POST['file'] : '');
		$offset = escapeshellarg(isset($_POST['offset']) ? $_POST['offset'] : '');
	
		if ($_POST['command'] == 'download' && $_POST['type'] == 'site') {
			chdir("/var/videos");
			exec("youtube-dl $url");
			
		} elseif ($_POST['command'] == 'download' && $_POST['type'] == 'direct') {
			chdir("/var/videos");
			exec("wget $url");
		
		} elseif ($_POST['command'] == 'play' && kosher($_POST['file'])) {
			exec('killall ffmpeg 2>&1');
			file_put_contents('nowplaying', $_POST['file']);
			chdir("/var/videos");
			exec("/var/www/bin/stream $file $offset");
		
		} elseif ($_POST['command'] == 'delete' && kosher($_POST['file'])) {
			chdir("/var/videos");
			unlink($_POST['file']);
		
		} elseif ($_POST['command'] == 'restart') {
			file_put_contents('nowplaying', '');
			exec('killall ffmpeg');
			//exec('/var/www/bin/server');
			//exec('youtube-dl -U');
		
		} elseif ($_POST['command'] == 'pause') {
			exec('/var/www/bin/pause');
		
		} elseif ($_POST['command'] == 'resume') {
			exec('/var/www/bin/resume');
		
		} elseif ($_POST['command'] == 'stop') {
			file_put_contents('nowplaying', '');
			exec('killall ffmpeg');
		
		}

		exit();
	}

