<?php
	if (count(get_included_files()) == 1) {
		die();
	}

	session_start();

	if (!(isset($_SESSION['admin']) && $_SESSION['admin'] == 'true')) {
		if (!(isset($_POST['password']) && $_POST['password'] == getenv('POPCORN_PASSWORD'))) {
			?>

<html>
	<head>
		<title>Popcorn</title>
	</head>
	<body>
		<form method="POST">
			Admin password: <input name="password" type="password" />
			<input type="submit" value="Login" />
		</form>
	</body>
</html>

			<?php
			die();
		} else {
			$_SESSION['admin'] = 'true';
		}
	}

