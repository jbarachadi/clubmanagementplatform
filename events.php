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
?>
<!DOCTYPE html>
<!--[if IE 8 ]><html class="no-js oldie ie8" lang="en"> <![endif]-->
<!--[if IE 9 ]><html class="no-js oldie ie9" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html class="no-js" lang="en"> <!--<![endif]-->
<head>

   <!--- basic page needs
   ================================================== -->
   <meta charset="utf-8">
	<title>eClubs - Évènements</title>
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

	//si c'est un compte tout les évenements, si c'est un visiteur sélection des évenements grand public uniquement
	if(isset($_SESSION['loggedin']))
	{
		if ($stmt = $con->prepare('SELECT id,club_id, intitule, descriptif, date, categorie, cover FROM evenements WHERE statut=? ORDER BY date DESC')) {
		$statut="Approuvée";
		$stmt->bind_param('s',$statut);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($id,$club, $intitule, $descriptif, $date, $categorie_id, $cover);	
		$stmt->fetch();
		}
	}
	else
	{
		if ($stmt = $con->prepare('SELECT id,club_id, intitule, descriptif, date, categorie, cover FROM evenements WHERE statut=? AND public=? ORDER BY date DESC')) {
			$public="Grand Public";
			$statut="Approuvée";
			$stmt->bind_param('ss',$statut, $public);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($id,$club, $intitule, $descriptif, $date, $categorie_id, $cover);	
			$stmt->fetch();
		}
	}
   ?>
   
   <!-- end header -->


   <!-- page header
   ================================================== -->
   <section id="page-header">
   	<div class="row current-cat">
   		<div class="col-full">
   			<h1>Évènements</h1>
   		</div>   		
   	</div>
   </section>

   
   <!-- masonry
   ================================================== -->
   <section id="bricks" class="with-top-sep">

   	<div class="row masonry"> 

   		<!-- brick-wrapper -->
         <div class="bricks-wrapper">

         	<div class="grid-sizer"></div>

         	<?php do{ 

			if ($stmt1 = $con->prepare('SELECT icon FROM clubs WHERE id=?')) {
				$stmt1->bind_param('s', $club);
				$stmt1->execute();
				$stmt1->store_result();
				$stmt1->bind_result($icon);
				$stmt1->fetch();
			}

			if ($stmt2 = $con->prepare('SELECT intitule FROM categorie WHERE id=?')) {
				$stmt2->bind_param('s', $categorie_id);
				$stmt2->execute();
				$stmt2->store_result();
				$stmt2->bind_result($categorie);
				$stmt2->fetch();
			}
			?>
            
            <article class="brick2 entry format-standard animate-this">

               <div class="eventDateContainer">
                  <div class="eventDate"><?php echo date("d M", strtotime($date)) ?></div>
               </div>

               <div class="entry-thumb">
                  <a href="eventPage.php?id=<?php echo $id ?>" class="thumb-link">
                     <img src="<?php echo $cover ?>" alt="<?php echo $intitule ?>">                   
                  </a>
               </div>

               <div class="entry-text">
               	<div class="entry-header">

               		<div class="entry-meta">
               			<span class="cat-links">
               				<a href="#"><?php echo $categorie ?></a>
               			</span>			
               		</div>

               		<h1 class="entry-title"><a href="eventPage.php?id=<?php echo $id ?>"><?php echo $intitule ?></a></h1>
               		
               	</div>
						<div class="entry-excerpt">
							<?php echo $descriptif ?>
						</div>
               </div>

        		</article> <!-- end article -->

        		<?php	
					}while($row=$stmt->fetch())
				?>


         </div> <!-- end brick-wrapper --> 

   	</div> <!-- end row -->

   	<!--<div class="row">
   		
   		<nav class="pagination">
		      <span class="page-numbers prev inactive">Prev</span>
		   	<span class="page-numbers current">1</span>
		   	<a href="#" class="page-numbers">2</a>
		      <a href="#" class="page-numbers">3</a>
		      <a href="#" class="page-numbers">4</a>
		      <a href="#" class="page-numbers">5</a>
		      <a href="#" class="page-numbers">6</a>
		      <a href="#" class="page-numbers">7</a>
		      <a href="#" class="page-numbers">8</a>
		      <a href="#" class="page-numbers">9</a>
		   	<a href="#" class="page-numbers next">Next</a>
	      </nav>

   	</div>-->

   </section> <!-- bricks -->

   
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