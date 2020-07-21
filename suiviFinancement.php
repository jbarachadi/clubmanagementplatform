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

      if($_SESSION['type']!="Pilotage" && $_SESSION['type']!="ADEI")
      {
      	 header('Location: index.php'); 
      }
?>

<!DOCTYPE html><html class="no-js" lang="en">
<head>

   <!--- basic page needs
   ================================================== -->
   <meta charset="utf-8">
	<title>eClubs : Liste des demandes de financement</title>
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

      //traitement d'une demande de financement
      if($_SESSION['type']=="ADEI")
      {
		if(isset($_GET['accept']))
		{
			if($stmtA = $con->prepare('UPDATE financement SET statut=? WHERE id=?'))
			{
				if($_GET['accept']=='yes'){$statut= "Approuvée";}
				if($_GET['accept']=='no'){$statut="Refusée";}
				
				$stmtA->bind_param('ss', $statut, $_GET['id']);
				$stmtA->execute();
				$stmtA->store_result();
			}
		}
	   }

      //récuperer les demandes de financement
      if($_SESSION['type']=="Pilotage")
      {
	      if ($stmt = $con->prepare('SELECT montant,motif,date,statut, club_id FROM financement WHERE club_id=? ORDER BY date DESC')) {
			$stmt->bind_param('s', $_SESSION['club']);
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($montant, $motif, $date,$statut, $club);	
			$stmt->fetch();
		  }
	  }
	  else if($_SESSION['type']=="ADEI")
	  {
	  	if ($stmt = $con->prepare('SELECT id,montant,motif,date,statut, club_id FROM financement ORDER BY date DESC')) {
			$stmt->execute();
			$stmt->store_result();
			$stmt->bind_result($id, $montant, $motif, $date,$statut, $club);	
			$stmt->fetch();
		}
	  }
   ?>
   
   <!-- end header -->

   
   <!-- content
   ================================================== -->  
   <div id="content-wrap" class="styles">

	   	<div class="row narrow add-bottom text-center">
	   		<div class="col-twelve tab-full">
	   			<h1>Suivi des demandes de financement</h1>
	   		</div>
	    </div>


	    <div class="row add-bottom">
		    <div class="col-twelve">
	   		<?php if($_SESSION['type']=="Pilotage"){?><a href="demandeFinancement.php" class="fa fa-plus-circle" style="color:GREY;"> Nouvelle demande</a><?php } ?>
			    <div class="table-responsive demandes">
		      		<table>
						<thead>
						   	<tr>
						      	<th>Date</th>
						      	<?php if($_SESSION['type']=="ADEI"){?><th>Club</th><?php } ?>
						      	<th>Montant</th>
						      	<th>Motif</th>
						      	<th>Statut</th>
						   	</tr>
						</thead>
						<tbody>
							<?php do{ 

								//récuperer le nom du club dans le cas de l'ADEI
								if ($stmt1 = $con->prepare('SELECT nom FROM clubs WHERE id=?')) {
									$stmt1->bind_param('s', $club);
									$stmt1->execute();
									$stmt1->store_result();
									$stmt1->bind_result($nom);
									$stmt1->fetch();
								}
							?>
						   	<tr>
						      	<td><?php echo $date ?></td> 
						      	<?php if($_SESSION['type']=="ADEI"){?><td><?php echo $nom ?></td><?php } ?>
						      	<td><?php echo $montant ?></td>
						      	<td><?php echo $motif ?></td>
						      	<td><?php if($_SESSION['type']=="ADEI" && $statut=="En attente"){?> 
						      		<a href="suiviFinancement.php?accept=yes&id=<?php echo $id;?>"><div style="margin-left: 2vw;" class="fa fa-check-circle"></div></a>
						      		<a href="suiviFinancement.php?accept=no&id=<?php echo $id;?>"><div style="margin-left: 2vw;" class="fa fa-times-circle"></div></a>
						      	<?php } else{ ?><?php echo $statut ?></td><?php } ?>
						   	</tr>
						   	<?php }while($row=$stmt->fetch()) ?>
						</tbody>
				   	</table>
		      	</div>     	
	      	</div>	      	
	   	</div> <!-- end row -->

   	</div> <!-- end styles -->

   
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