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

      if($_SESSION['type']!="Pilotage" && $_SESSION['type']!="Membre")
      {
      	 header('Location: index.php'); 
      }
?>

<!DOCTYPE html><html class="no-js" lang="en">
<head>

   <!--- basic page needs
   ================================================== -->
   <meta charset="utf-8">
	<title>eClubs : Liste des réunions</title>
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


      //traitement de l'annulation d'une réunion
		if(isset($_SESSION['club']))
		{
			if(isset($_GET['id']))
			{
				if($stmtC = $con->prepare('UPDATE reservation SET statut=? WHERE id=?'))
				{
					$stat="Cancelled";
					$stmtC->bind_param('ss', $stat, $_GET['id']);
					$stmtC->execute();
					$stmtC->store_result();
				}
			}
		}

      //si pilotage récuperer les réunions de son club uniquement
      if($_SESSION['type'] == "Pilotage") 
      {
	   		if ($stmtL = $con->prepare('SELECT id,salle_id, date, duree_id FROM reservation WHERE club_id=? AND statut=? ORDER BY date ASC')) {
				$statut="Planned";
				$stmtL->bind_param('ss', $_SESSION['club'], $statut);
				$stmtL->execute();
				$stmtL->store_result();
				$stmtL->bind_result($id_r,$salle_id,$date_r,$duree_id);
				$stmtL->fetch();
			}
	  }

	  //si membre récuperer les réunions des clubs dont il fait partie
	  if($_SESSION['type'] == "Membre") 
	  {
	   		if ($stmtL = $con->prepare('SELECT id,club_id,salle_id, date, duree_id FROM reservation WHERE statut=? ORDER BY date ASC')) {
	   			$stat="Planned";
				$stmtL->bind_param('s', $stat);
				$stmtL->execute();
				$stmtL->store_result();
				$stmtL->bind_result($id_r,$club_id,$salle_id,$date_r,$duree_id);
				$stmtL->fetch();
			}            
	  }

      
   ?>
   
   <!-- end header -->

   
   <!-- content
   ================================================== -->  
   <div id="content-wrap" class="styles">

	   	<div class="row narrow add-bottom text-center">
	   		<div class="col-twelve tab-full">
	   			<h1>Réunions de la semaine</h1>
	   		</div>
	    </div>

	    <div class="row add-bottom">
		    <div class="col-twelve">
			    <div class="table-responsive demandes">
		      		<table>
						<thead>
						   	<tr>
						      	<th>Date</th>
						   		<?php if($_SESSION['type']=="Membre"){?><th>Club</th><?php } ?>
						      	<th>Salle</th>
						      	<th>Durée</th>
						      	<?php if($_SESSION['type']=="Pilotage"){?><th> </th><?php } ?>	    
						  	 </tr>
						</thead>
						<tbody>
							<?php 
							if($_SESSION['type']=="Membre"){
								do{
									//vérifier que le membre appartient au club
									 if ($stmtv = $con->prepare('SELECT id FROM postulation WHERE club_id=? AND user_id=? AND statut=?')) {
				                           $stat="Approuvée";
				                           $stmtv->bind_param('sss', $club_id, $_SESSION['id'], $stat);
				                           $stmtv->execute();
				                           $stmtv->store_result();
				                           $stmtv->bind_result($id); 
				                           $stmtv->fetch();
				                        }

				                     if($stmtv->num_rows == 0)
				                     {
				                        continue; 
				                     }

									if ($stmtn = $con->prepare('SELECT nom FROM clubs WHERE id=?')) {
										$stmtn->bind_param('s', $club_id);
										$stmtn->execute();
										$stmtn->store_result();
										$stmtn->bind_result($club);
										$stmtn->fetch();
									}

										if ($stmtD = $con->prepare('SELECT duree FROM duree WHERE id=?')) {
											$stmtD->bind_param('s', $duree_id);
											$stmtD->execute();
											$stmtD->store_result();
											$stmtD->bind_result($duree);
											$stmtD->fetch();
										}

										if ($stmtS = $con->prepare('SELECT salle FROM salles WHERE id=?')) {
											$stmtS->bind_param('s', $salle_id);
											$stmtS->execute();
											$stmtS->store_result();
											$stmtS->bind_result($salle);
											$stmtS->fetch();
										}?>
									<tr>
										<td><?php echo $date_r; ?></td>
										<td><?php echo $club; ?></td>
										<td><?php echo $salle; ?></td>
										<td><?php echo $duree; ?></td>
									</tr>
							<?php 
								}while($row=$stmtL->fetch());
							}
							?>



							<?php 
							if($_SESSION['type']=="Pilotage"){
								do{
									if ($stmtD = $con->prepare('SELECT duree FROM duree WHERE id=?')) {
										$stmtD->bind_param('s', $duree_id);
										$stmtD->execute();
										$stmtD->store_result();
										$stmtD->bind_result($duree);
										$stmtD->fetch();
									}

									if ($stmtS = $con->prepare('SELECT salle FROM salles WHERE id=?')) {
										$stmtS->bind_param('s', $salle_id);
										$stmtS->execute();
										$stmtS->store_result();
										$stmtS->bind_result($salle);
										$stmtS->fetch();
									}?>
								<tr>
									<td><?php echo $date_r; ?></td>
									<td><?php echo $salle; ?></td>
									<td><?php echo $duree; ?></td>
									<td><a href="reunions.php?id=<?php echo $id_r?>">Annuler</a></td>
								</tr>
							<?php 
								}while($row=$stmtL->fetch());
							}
							?>
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