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
      if($_SESSION['type']!="Pilotage")
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
	<title>eClubs - Demande d'évenement</title>
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
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<link rel="icon" href="favicon.ico" type="image/x-icon">

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


      //traitement de l'ajout d'évenement
      if(isset($_POST['submit']))
      {
         if($stmt1 = $con->prepare("SELECT * FROM evenements WHERE date=?"))
         {
            mysqli_stmt_bind_param($stmt1,'s', $_POST['date']);
            $stmt1->execute();
            $stmt1->store_result();
         }

         if ($stmt1->num_rows > 0){
            $message= '<div class="alert-box ss-error hideit"><p>Il existe déjà un évenement prévu pour cette date, veuillez choisir un autre jour.</p><i class="fa fa-times close"></i></div>';
         }
         else
         {

            if ($stmt = $con->prepare("INSERT INTO evenements (club_id, intitule, descriptif, logistique, horaire, date, public, categorie, cover) VALUES (?,?,?,?,?,?,?,?,?)"))
            {
               //traitement d'image
               $file_type = $_FILES['image']['type'];
               $file_tmp_name = $_FILES['image']['tmp_name'];
               $file_name = $_SESSION['club'].$_POST['date'];
               $target_dir = "images/events/";
               move_uploaded_file($file_tmp_name, $target_dir.$file_name.".png");
               $image=$target_dir.$file_name.".png";

               $horaire=$_POST['debut']." à ".$_POST['fin'];
               mysqli_stmt_bind_param($stmt,'sssssssss', $_SESSION['club'], $_POST['intitule'], $_POST['descriptif'], $_POST['logistique'], $horaire, $_POST['date'], $_POST['public'], $_POST['categorie'], $image);
               $stmt->execute();
               $message='<div class="alert-box ss-success hideit"><p>Demande bien envoyée!</p><i class="fa fa-times close"></i></div>';
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
   			<h1>Nouvelle demande d'évenement</h1>
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

						<form name="cForm" id="cForm" method="post" action="demandeEvenement.php" enctype="multipart/form-data">
	  					   <fieldset>

	                     <div class="form-field">
	  						      <input name="intitule" type="text" class="full-width" placeholder="Intitulé de l'évenement" value="" required>
	                     </div>

                        <div class="form-field">
                           <input name="date" type="date" class="full-width" placeholder="" value="" required>
                        </div>

                         <div class="form-field">
                           <input name="debut" type="text" class="half-width left" placeholder="Heure de début" value="" required>
                           <input name="fin" type="text" class="half-width right" placeholder="Heure de fin" value="" required>
                        </div>

                        <div class="form-field selectClass categorieClass">
                          <select class="full-width" id="sampleRecipientInput" name="categorie">
                           <option value="">Catégorie</option>
                             <?php
                                 if ($stmtA = $con->prepare('SELECT id, intitule FROM categorie')) {
                                    $stmtA->execute();
                                    $stmtA->store_result();
                                    $stmtA->bind_result($id, $categorie);
                                    $stmtA->fetch();
                                 }

                                 do{
                                  ?>
                                    <option value="<?php echo $id?>"><?php echo $categorie?></option>
                                 <?php
                                    }while($row=$stmtA->fetch());
                              ?>
                          </select>
                       </div>  

                       <div class="form-field selectClass publicClass">
                          <select class="full-width" id="sampleRecipientInput" name="public">
                             <option value="">Public visé</option>
                             <option value="ENSIAS">ENSIAS</option>
                             <option value="Grand Public">Grand Public</option>
                          </select>
                        </div>  

	                     <div class="form-field">
									<textarea name="descriptif" class="full-width" placeholder="Descriptif de l'évenement" required=""></textarea>
	                     </div>

                        <div class="form-field">
                           Photo de couverture:
                           <input name="image" type="file" class="full-width" required="">
                        </div>

                        <div class="form-field">
                           <input name="logistique" type="text" class="full-width" placeholder="Logistique de l'évenement (amphithéâtres, équipements de sonorisation...)" required="">
                        </div>


	                     <input type="submit" name="submit" class="submit button-primary full-width-on-mobile" value="Envoyer">

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