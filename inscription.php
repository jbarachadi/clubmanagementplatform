<?php
  	session_start();
	//récupération de cookies
	if (isset($_COOKIE['session']))
	{
	    $_SESSION['id'] = $_COOKIE['id'];
	    $_SESSION['type'] = $_COOKIE['type'];
	    if($_SESSION['type']=="Pilotage"){$_SESSION['club'] = $_COOKIE['club'];}
	    $_SESSION['loggedin'] = TRUE;
	    $_SESSION['remember'] = TRUE;
	}

	//restriction d'accès
	if(isset($_SESSION['loggedin'])== TRUE)
	{
		header('Location: index.php'); 
	}  

      $DATABASE_HOST = 'localhost';
      $DATABASE_USER = 'root';
      $DATABASE_PASS = '';
      $DATABASE_NAME = 'parascolaire';

      $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
      if ( mysqli_connect_errno() ) {

         die ('Failed to connect to MySQL: ' . mysqli_connect_error());
      }

    //traitement de l'inscription
    if(isset($_POST['submit']))
	{
		//vérification de l'unicité de l'email
		if ($stmt2 = $con->prepare('SELECT email FROM users WHERE email = ?')) {
			$stmt2->bind_param('s', $_POST['email']);
			$stmt2->execute();
			$stmt2->store_result();
			
			if ($stmt2->num_rows > 0) {

				$message= '<div class="alert-box ss-error hideit"><p>L\'email que vous avez choisi a déjà été utilisé. Veuillez réessayer</p><i class="fa fa-times close"></i></div>'; 
			}
			else
			{ 
				//ajout de l'utilisateur à la base de données et envoi d'un e-mail de confirmation
				if ($stmt3 = $con->prepare("INSERT INTO users (email,password,type) VALUES (?,?,?)")) {
					$pw=$_POST['pw'];
					$password = password_hash($pw,PASSWORD_DEFAULT);
					$email=$_POST['email'];
					$type="Membre";
										
					mysqli_stmt_bind_param($stmt3,'sss', $email, $password, $type);
					$stmt3->execute();

					//récupération du user_id
					if ($stmt4 = $con->prepare('SELECT id FROM users WHERE email = ?')) {
						$stmt4->bind_param('s', $email);
						$stmt4->execute();
						$stmt4->store_result();
						$stmt4->bind_result($user_id);
						$stmt4->fetch();
					}

					if ($stmt5 = $con->prepare("INSERT INTO membres (user_id,nom,prenom,sexe,filiere,photo) VALUES (?,?,?,?,?,?)")) {
						$nom=$_POST['nom'];
						$prenom=$_POST['prenom'];
						$sexe=$_POST['sexe'];
						$filiere=$_POST['filiere'];

						$file_type = $_FILES['photo']['type'];
		                $file_tmp_name = $_FILES['photo']['tmp_name'];
		                $file_name = $user_id;
		                $target_dir = "images/avatars/";
		                move_uploaded_file($file_tmp_name, $target_dir.$file_name.".png");
		                $photo=$target_dir.$file_name.".png";
											
						mysqli_stmt_bind_param($stmt5,'ssssss', $user_id, $nom, $prenom, $sexe, $filiere, $photo);
						$stmt5->execute();

						/*$recipient=$email;
						$subject="Inscription réussie à la plateforme Parascolaire ENSIAS!";
						$content="Chèr(e) ".$nom. " ". $prenom. "\n\nNous sommes ravis de vous compter parmi nous!\n\nCordialement.";

						mail($recipient, $subject, $content);*/

						 $message= '<div class="alert-box ss-success hideit"><p>Inscription réussie! Un email a été envoyé à votre adresse</p><i class="fa fa-times close"></i></div>';
					}
				}
					else{
						$message= '<div class="alert-box ss-error hideit"><p>Il semblerait qu\'une erreur soit survenue... Veuillez réessayer plus tard</p><i class="fa fa-times close"></i></div>'; 
					}
				}	
			}			
	}

   ?>