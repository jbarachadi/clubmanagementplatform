<?php
	
		if(isset($_POST['submit']))
		{
			$DATABASE_HOST = 'localhost';
			$DATABASE_USER = 'root';
			$DATABASE_PASS = '';
			$DATABASE_NAME = 'parascolaire';

			$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
			if ( mysqli_connect_errno() ) {

				die ('Failed to connect to MySQL: ' . mysqli_connect_error());
			}

			if ($stmt = $con->prepare('SELECT id, password, type FROM users WHERE email = ?')) {

				$stmt->bind_param('s', $_POST['email']);
				$stmt->execute();
				$stmt->store_result();
			}

			if ($stmt->num_rows > 0) {
				$stmt->bind_result($id, $pw, $type);
				$stmt->fetch();

				if (password_verify($_POST['pw'], $pw)) {
			
					$_SESSION['id'] = $id;
					$_SESSION['type'] = $type;
					$_SESSION['loggedin'] = TRUE;
					$_SESSION['remember'] = FALSE;

					if($type=="Pilotage")
					{
						if ($stmtB = $con->prepare('SELECT club_id FROM pilotages WHERE user_id=?')) {
							$stmtB->bind_param('s', $id);
							$stmtB->execute();
							$stmtB->store_result();
							$stmtB->bind_result($club_id);
							$stmtB->fetch();
						}

						$_SESSION['club'] = $club_id;
						setcookie( 'club', $_SESSION['club'], time() + 7*24*3600, null, null, false, true);
					}

					//Remember me
					if(isset($_POST['remember']))
					{
						setcookie( 'id', $_SESSION['id'], time() + 7*24*3600, null, null, false, true);
						setcookie( 'type', $_SESSION['type'], time() + 7*24*3600, null, null, false, true);
						setcookie( 'state', $_SESSION['loggedin'], time() + 7*24*3600, null, null, false, true);

						$_SESSION['remember'] = TRUE;
					}
					$message= '<div class="alert-box ss-success hideit"><p>Connexion réussie!</p><i class="fa fa-times close"></i></div>';

				    $fp = fopen('curr_user.txt', 'w');
					fwrite($fp, $id);
					fclose($fp);

				} else {
					$message = '<div class="alert-box ss-error hideit"><p>Il semblerait que le mot de passe que vous avez saisi est incorrect. Veuillez réessayer</p><i class="fa fa-times close"></i></div>';
				}
			} else {
				$message = '<div class="alert-box ss-error hideit"><p>Il semblerait que l\'identifiant que vous avez saisi est incorrect. Veuillez réessayer</p><i class="fa fa-times close"></i></div>';;
			}
			$stmt->close();
		}
	 
	?>