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

      //restriction d'accès au pilotage/ADEI uniquement
      if($_SESSION['type']=="Membre" || isset($_SESSION['loggedin'])==FALSE)
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
	<title>eClubs : Nouveau Post</title>
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

      //traitement de l'envoi de la demande
      if(isset($_POST['submit']))
      {
         if($stmt = $con->prepare('INSERT INTO annonces(club_id, titre, corps, date, image) values (?,?,?,?,?)'))
         {
            $date=date("Y-m-d H:i:s"); 
            if(file_exists($_FILES['image']['tmp_name']) || is_uploaded_file($_FILES['image']['tmp_name']))
            {
               //traitement d'image
               $file_type = $_FILES['image']['type'];
               $file_tmp_name = $_FILES['image']['tmp_name'];
               $file_name = $_SESSION['club']."-".date("YmdHis");
               $target_dir = "images/posts/";
                  move_uploaded_file($file_tmp_name, $target_dir.$file_name.".png");
               $image=$target_dir.$file_name.".png";
            }
            else
            {
               $image="";
            }

            if(isset($_SESSION['club']))
            {
               $club_id=$_SESSION['club'];
            }
            else
            {
               //l'ADEI
               $club_id=0;
            }
            

            $stmt->bind_param('sssss', $club_id, $_POST['titre'], $_POST['corps'], $date, $image);
            $stmt->execute();
            $stmt->store_result();
            $message='<div class="alert-box ss-success hideit"><p>Votre annonce a bien été publié sur le fil d\'actualité !</p><i class="fa fa-times close"></i></div>';
         }
      }
   ?>
   
   <!-- end header -->

   <!-- page header
   ================================================== -->
   <section id="page-header">
   	<div class="row current-cat">
   		<div class="col-full">
   			<h1>Nouveau post sur le fil d'actualité</h1>
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

						<form name="cForm" id="cForm" method="post" action="nouveauPost.php" enctype="multipart/form-data">
	  					   <fieldset>

	                     <div class="form-field">
	  						      <input name="titre" type="text" class="full-width" placeholder="Titre" value="" required>
	                     </div>

	                     <div class="form-field">
									<textarea name="corps" class="full-width" placeholder="Corps (limite de 1000 caractères)" required=""></textarea><br/>
	                     </div>

                        <div class="form-field">
                           <input name="image" type="file" class="full-width" >
                        </div>

	                     <input type="submit" name="submit" class="submit button-primary full-width-on-mobile" value="Publier">

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