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

      if($_SESSION['type']!="Pilotage")
      {
      	 header('Location: index.php'); 
      }
?>

<!DOCTYPE html><html class="no-js" lang="en">
<head>

   <!--- basic page needs
   ================================================== -->
   <meta charset="utf-8">
	<title>eClubs : Plateforme de gestion du parascolaire</title>
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
      
	//traitement d'une demande d'adhésion
			if(isset($_GET['join']))
			{
				if($stmtA = $con->prepare('UPDATE postulation SET statut=? WHERE user_id=? AND club_id=?'))
				{
					if($_GET['join']=='yes'){$statut= "Approuvée";}
					if($_GET['join']=='no'){$statut="Refusée";}
					
					$stmtA->bind_param('sss', $statut, $_GET['user'], $_SESSION['club']);
					$stmtA->execute();
					$stmtA->store_result();
				}
			}

      //récuperer les demandes d'adhésion en attente de traitement
      if ($stmt1 = $con->prepare('SELECT user_id, date FROM postulation WHERE club_id=? AND statut=? ORDER BY date ASC')) {
			$statut="En attente";
			$stmt1->bind_param('ss', $_SESSION['club'], $statut);
			$stmt1->execute();
			$stmt1->store_result();
			$stmt1->bind_result($user_id, $date);
			$stmt1->fetch();
		}

		
   ?>
   
   <!-- end header -->

   
   <!-- content
   ================================================== -->  
   <div id="content-wrap" class="styles">

	   	<div class="row narrow add-bottom text-center">
	   		<div class="col-twelve tab-full">
	   			<h1>Demandes d'adhésion</h1>
	   		</div>
	    </div>

	    <div class="row add-bottom">
		    <div class="col-twelve">
			    <div class="table-responsive demandes">
		      		<table>
						<thead>
						   	<tr>
						      	<th>Date</th>	
						      	<th>Nom</th>
						      	<th>Prénom</th>
						      	<th>Sexe</th>
						      	<th>Filière</th>
						      	<th>Statut</th>
						   	</tr>
						</thead>
						<tbody>
							<?php 
							do{
								if ($stmt2 = $con->prepare('SELECT nom, prenom, sexe, filiere FROM membres WHERE user_id=?')) {
									$stmt2->bind_param('s', $user_id);
									$stmt2->execute();
									$stmt2->store_result();
									$stmt2->bind_result($nom,$prenom,$sexe,$filiere);
									$stmt2->fetch();

								}
							?>
						   	<tr>
						      	<td><?php echo $date ?></td> 
						      	<td><?php echo $nom ?></td>
						      	<td><?php echo $prenom ?></td>
						      	<td><?php echo $sexe ?></td>
						      	<td><?php echo $filiere ?></td>
						      	<td><a href="adhesion.php?join=yes&user=<?php echo $user_id;?>"><div style="margin-left: 2vw;" class="fa fa-check-circle"></div></a>
						      		<a href="adhesion.php?join=no&user=<?php echo $user_id;?>"><div style="margin-left: 2vw;" class="fa fa-times-circle"></div></a></td>
						   	</tr>
						   	<?php }while($row=$stmt1->fetch()); ?>
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