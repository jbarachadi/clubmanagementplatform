<?php
	session_start();

	if (isset($_COOKIE['session']))
	{
		$_SESSION['id'] = $_COOKIE['id'];
		$_SESSION['type'] = $_COOKIE['type'];

		if($_SESSION['type']=="Pilotage") {
			$_SESSION['club'] = $_COOKIE['club'];
		}

		$_SESSION['loggedin'] = TRUE;
		$_SESSION['remember'] = TRUE;
	  }

	if(isset($_SESSION['loggedin'])== FALSE)
	{
		header('Location: index.php'); 
	} 
	else
	{
		$DATABASE_HOST = 'localhost';
		$DATABASE_USER = 'root';
		$DATABASE_PASS = '';
		$DATABASE_NAME = 'parascolaire';

		$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
		if ( mysqli_connect_errno() ) {

			die ('Failed to connect to MySQL: ' . mysqli_connect_error());
		}

		setcookie('session');
		setcookie('state');
		setcookie('id');
		setcookie('type');
		setcookie('club');
	  	unset($_COOKIE['session']);
	  	unset($_COOKIE['state']);
	  	unset($_COOKIE['id']);
	  	unset($_COOKIE['type']);
	  	unset($_COOKIE['club']);

		session_destroy();
		$_SESSION['loggedin'] = FALSE;

		header('Location: index.php');
	}
?>