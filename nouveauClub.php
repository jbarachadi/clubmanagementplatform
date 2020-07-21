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

      //restriction d'accès à l'ADEI uniquement
      if($_SESSION['type']!="ADEI")
      {
         header('Location: index.php'); 
      } 
?>


<!DOCTYPE html>
<!--[if IE 8 ]><html class="no-js oldie ie8" lang="en"> <![endif]-->
<!--[if IE 9 ]><html class="no-js oldie ie9" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html class="no-js" lang="en"> <!--<![endif]-->
<head>

   <!--- basic page needs
   ================================================== -->
   <meta charset="utf-8">
	<title>eClubs : Ajouter un nouveau club</title>
	<meta name="description" content="">  
	<meta name="author" content="">

   <!-- mobile specific metas
   ================================================== -->
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

 	<!-- CSS
   ================================================== -->
   <link rel="stylesheet" href="css/base.css">
   <link rel="stylesheet" href="css/vendor.css">  
   <link rel="stylesheet" href="css/main.css">        

   <!-- script
   ================================================== -->
	<script src="js/modernizr.js"></script>
	<script src="js/pace.min.js"></script>

   <!-- favicons
	================================================== -->
   <link rel="shortcut icon" href="images/logo.png" type="image/x-icon">
   <link rel="icon" href="images/logo.png" type="image/x-icon">


</head>

<body id="top">

	<!-- header 
   ================================================== -->
   <?php 

      $DATABASE_HOST = 'localhost';
      $DATABASE_USER = 'root';
      $DATABASE_PASS = '';
      $DATABASE_NAME = 'parascolaire';

      $con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
      if ( mysqli_connect_errno() ) {

         die ('Failed to connect to MySQL: ' . mysqli_connect_error());
      }

      if ( isset($_SESSION['type']) ) {
         if($_SESSION['type'] == "Pilotage") {
               include('headerPilotage.php');
         }
         else if($_SESSION['type'] == "Membre") {
               include('headerMembre.php');
         }
         else if($_SESSION['type'] == "ADEI") {
               include('headerAdei.php');
         }
         else {
            include('headerVisiteur.php');
         }
      }
      else {
         include('headerVisiteur.php');
      }

      //traitement de la création de club
      if(isset($_POST['submit']))
      {
         //vérification de l'unicité du nom du club
         if ($stmt2 = $con->prepare('SELECT nom FROM clubs WHERE nom = ?')) {
            $stmt2->bind_param('s', $_POST['nom']);
            $stmt2->execute();
            $stmt2->store_result();
            
            if ($stmt2->num_rows > 0) {

               $message='<div class="alert-box ss-error hideit"><p>Attention! Ce nom de club existe déjà...</p><i class="fa fa-times close"></i></div>';
            }
            else
            { 
               //ajout du club & compte pilotage
               if ($stmt3 = $con->prepare("INSERT INTO users (email,password,type) VALUES (?,?,?)")) {
                  $pw=$_POST['pw'];
                  $password = password_hash($pw,PASSWORD_DEFAULT);
                  $email=$_POST['email'];
                  $type="Pilotage";
                                 
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

                  //création du club
                  if ($stmt5 = $con->prepare("INSERT INTO clubs (nom) VALUES (?)")) {
                     $nom=$_POST['nom'];
                                    
                     mysqli_stmt_bind_param($stmt5,'s', $nom);
                     $stmt5->execute();
                  }

                  //récupération du club_id
                  if ($stmt6 = $con->prepare('SELECT id FROM club WHERE nom = ?')) {
                     $stmt6->bind_param('s', $nom);
                     $stmt6->execute();
                     $stmt6->store_result();
                     $stmt6->bind_result($club_id);
                     $stmt6->fetch();
                  }

                  //insertion dans la table pilotages
                  if ($stmt7 = $con->prepare("INSERT INTO pilotages (user_id, club_id) VALUES (?,?)")) {
                     mysqli_stmt_bind_param($stmt7,'ss', $user_id, $club_id);
                     $stmt7->execute();
                                 
                     /*$recipient=$email;
                     $subject="Création de votre compte pilotage sur la plateforme Parascolaire ENSIAS!";
                     $content="Cher ".$nom. "\n\nNous sommes ravis de vous compter parmi nous!\n\nCordialement.";

                     mail($recipient, $subject, $content);*/

                     $message= '<div class="alert-box ss-success hideit"><p>Création de club réussie!</p><i class="fa fa-times close"></i></div>';
                  }
               }
                  else{
                     $message= '<div class="alert-box ss-error hideit"><p>Il semblerait qu\'une erreur soit survenue... Veuillez réessayer plus tard</p><i class="fa fa-times close"></i></div>'; 
                  }
               }           
            }
         }      
   ?>
   
   <!-- end header -->

   <!-- page header
   ================================================== -->
   <section id="page-header">
   	<div class="row current-cat">
   		<div class="col-full">
   			<h1>Ajouter un nouveau club</h1>
   		</div>   		
   	</div>
   </section>


   <!-- content
   ================================================== -->
   <section id="content-wrap" class="site-page">
   	<div class="row">
   		<div class="col-twelve">

   			<section>  

   					<div class="primary-content">

   						<div class="row add-bottom"><div class="col-twelve"><?php if(isset($message)){echo $message;} ?></div></div>


						<form name="cForm" id="cForm" method="post" action="nouveauClub.php">
	  					   <fieldset>

	                     <div class="form-field">
	  						      <input name="nom" type="text" class="full-width" placeholder="Nom" value="" required>
	                     </div>

	                     <div class="form-field">
                           <input name="email" type="email" class="full-width" placeholder="Adresse email" value="" required>
                        </div>

                        <div class="form-field">
                           <input name="pw" type="password" class="full-width" placeholder="Mot de passe" value="" required>
                        </div>

	                     <input type="submit" name="submit" class="submit button-primary full-width-on-mobile" value="Créer">

	  					   </fieldset>
  				      </form> <!-- end form -->

				</section>
   		

			</div> <!-- end col-twelve -->
   	</div> <!-- end row -->		
   </section> <!-- end content -->

   
   <!-- footer
   ================================================== -->
   <footer>

   	<div class="footer-main">

   		<div class="row">  

	      	<div class="col-four tab-full mob-full footer-info">            

	            <h4>About Our Site</h4>

	               <p>
		          	Lorem ipsum Ut velit dolor Ut labore id fugiat in ut fugiat nostrud qui in dolore commodo eu magna Duis cillum dolor officia esse mollit proident Excepteur exercitation nulla. Lorem ipsum In reprehenderit commodo aliqua irure labore.
		          	</p>

		      </div> <!-- end footer-info -->

	      	<div class="col-two tab-1-3 mob-1-2 site-links">

	      		<h4>Site Links</h4>

	      		<ul>
	      			<li><a href="#">About Us</a></li>
						<li><a href="#">Blog</a></li>
						<li><a href="#">FAQ</a></li>
						<li><a href="#">Terms</a></li>
						<li><a href="#">Privacy Policy</a></li>
					</ul>

	      	</div> <!-- end site-links -->  

	      	<div class="col-two tab-1-3 mob-1-2 social-links">

	      		<h4>Social</h4>

	      		<ul>
	      			<li><a href="#">Twitter</a></li>
						<li><a href="#">Facebook</a></li>
						<li><a href="#">Dribbble</a></li>
						<li><a href="#">Google+</a></li>
						<li><a href="#">Instagram</a></li>
					</ul>
	      	           	
	      	</div> <!-- end social links --> 

	      	<div class="col-four tab-1-3 mob-full footer-subscribe">

	      		<h4>Subscribe</h4>

	      		<p>Keep yourself updated. Subscribe to our newsletter.</p>

	      		<div class="subscribe-form">
	      	
	      			<form id="mc-form" class="group" novalidate="true">

							<input type="email" value="" name="dEmail" class="email" id="mc-email" placeholder="Type &amp; press enter" required=""> 
	   		
			   			<input type="submit" name="subscribe" >
		   	
		   				<label for="mc-email" class="subscribe-message"></label>
			
						</form>

	      		</div>	      		
	      	           	
	      	</div> <!-- end subscribe -->         

	      </div> <!-- end row -->

   	</div> <!-- end footer-main -->

      <div class="footer-bottom">
      	<div class="row">

      		<div class="col-twelve">
	      		<div class="copyright">
		         	<span>© Copyright Abstract 2016</span> 
		         	<span>Design by <a href="http://www.styleshout.com/">styleshout</a></span>		         	
		         </div>

		         <div id="go-top">
		            <a class="smoothscroll" title="Back to Top" href="#top"><i class="icon icon-arrow-up"></i></a>
		         </div>         
	      	</div>

      	</div> 
      </div> <!-- end footer-bottom -->  

   </footer>  

   <div id="preloader"> 
    	<div id="loader"></div>
   </div> 

   <!-- Java Script
   ================================================== --> 
   <script src="js/jquery-2.1.3.min.js"></script>
   <script src="js/plugins.js"></script>
   <script src="js/main.js"></script>  

</body>

</html>