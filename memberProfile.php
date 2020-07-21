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

      //restriction d'accès aux membres
       if($_SESSION['type']!="Membre")
	   {
	      header('Location: index.php'); 
	   }
?>

<!DOCTYPE html><html class="no-js" lang="en">
<head>

   <!--- basic page needs
   ================================================== -->
   <meta charset="utf-8">
	<title>eClubs : Profil</title>
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

      //informations du membre
       if ($stmt = $con->prepare('SELECT nom, prenom, filiere, photo FROM membres WHERE user_id=?')) {
	         $stmt->bind_param('s', $_SESSION['id']);
	         $stmt->execute();
	         $stmt->store_result();
	         $stmt->bind_result($nom, $prenom, $filiere, $photo); 
	         $stmt->fetch();
	      }

	      if ($stmtE = $con->prepare('SELECT email FROM users WHERE id=?')) {
	         $stmtE->bind_param('s', $_SESSION['id']);
	         $stmtE->execute();
	         $stmtE->store_result();
	         $stmtE->bind_result($email); 
	         $stmtE->fetch();
	      }
   ?>
   
   <!-- end header -->

   
   <!-- content
   ================================================== -->  
   <div id="content-wrap" class="styles">

   	<div class="row narrow add-bottom text-center">

   		<div class="col-twelve tab-full">

   			<h1>Profil</h1>		

   		</div>

     	</div>

     	<div class="row">

     		<?php if(isset($_SESSION['type'])=="Membre"){?><a href="editProfile.php" style="color:GREY; font-weight: bold;">Editer</a><?php } ?>

     		<div class="col-six tab-full profile-pic"><div class="image-zone"><img src="<?php echo $photo ?>"></div></div>

	      	<div class="col-six tab-full">

	         	<h3>Infos</h3>

	         	<ul class="disc">
				   	<li><span>Nom :</span> <?php echo $nom ?> </li>
				   	<li><span>Prénom :</span> <?php echo $prenom ?> </li>
				   	<li><span>Filière :</span> <?php echo $filiere ?> </li> 
				   	<li><span>Contact :</span> <?php echo $email ?> </li>
				</ul>			      	

			</div>	         

		</div> <!-- end row -->

		<div class="row half-bottom">

		   <div class="col-twelve">
			<?php 

			if ($stmtC = $con->prepare('SELECT * FROM postulation WHERE user_id=? AND statut=?')) {
				$statut="Approuvée";
				$stmtC->bind_param('ss', $_SESSION['id'],$statut);
				$stmtC->execute();
				$stmtC->store_result();
				$stmtC->fetch();
			}

			if ($stmtE = $con->prepare('SELECT DISTINCT user_id, evt_id FROM participation WHERE user_id=?')) {
				 $stmtE->bind_param('s', $_SESSION['id']);
				 $stmtE->execute();
				 $stmtE->store_result();
				 $stmtE->fetch();
			}

			if ($stmtT = $con->prepare('SELECT * FROM commentaires WHERE user_id=?')) {
				 $stmtT->bind_param('s', $_SESSION['id']);
				 $stmtT->execute();
				 $stmtT->store_result();
				 $stmtT->fetch();
			}

			?>


		      <ul class="stats-tabs">
				   <li><a href="#"> <?php echo $stmtC->num_rows; ?> <em>Clubs</em></a></li>
				   <li><a href="#"> <?php echo $stmtE->num_rows; ?> <em>Evenements</em></a></li>
				   <li><a href="#"> <?php echo $stmtT->num_rows; ?> <em>Témoignages</em></a></li>
				</ul>	      		

		   </div>	      	

		</div> <!-- end row -->

		<div class="row half-bottom">

			<h3>Clubs</h3>

			<div class="table-responsive">

	      		<table>
						<thead>
						   	<tr>
						 		<th>Club</th>
						      	<th>Date d'adhésion</th>
						      	<th>Statut de la postulation</th>		    
						  	 </tr>
						</thead>
						<tbody>
							<?php 
								if ($stmtC = $con->prepare('SELECT club_id, statut, date FROM postulation WHERE user_id=?')) {
									$stmtC->bind_param('s', $_SESSION['id']);
									$stmtC->execute();
									$stmtC->store_result();
									$stmtC->bind_result($club_id, $statut, $date);
									$stmtC->fetch();
								}

								do{
									if ($stmtn = $con->prepare('SELECT nom FROM clubs WHERE id=?')) {
										$stmtn->bind_param('s', $club_id);
										$stmtn->execute();
										$stmtn->store_result();
										$stmtn->bind_result($club);
										$stmtn->fetch();
									}
							?>	
						  	 <tr>
						   	   <td><a href="clubProfile.php?id=<?php echo $club_id?>"><?php echo $club ?></a></td>
						   	   <td><?php echo $date ?></td>
						   	   <td><?php echo $statut ?></td>		        
						   	</tr>
						    <?php }while($row=$stmtC->fetch()) ?>
						</tbody>
				   </table>

				<h3>Evènements</h3>

				   <div class="table-responsive">

		      		<table>
							<thead>
							   	<tr>
							 		<th>Nom</th>
							 		<th>Club</th>
							      	<th>Date</th>
							      	<th>Catégorie</th>			    
							  	 </tr>
							</thead>
							<tbody>
								<?php
								if ($stmtE = $con->prepare('SELECT evt_id FROM participation WHERE user_id=?')) {
									$stmtE->bind_param('s', $_SESSION['id']);
									$stmtE->execute();
									$stmtE->store_result();
									$stmtE->bind_result($event_id);
									$stmtE->fetch();
								}

								do{

								if ($stmtl = $con->prepare('SELECT club_id, intitule, date, categorie, public FROM evenements WHERE id=? ORDER BY date DESC')) {
									$stmtl->bind_param('s', $event_id);
									$stmtl->execute();
									$stmtl->store_result();
									$stmtl->bind_result($club_id, $intitule, $date, $categorie_id, $public);
									$stmtl->fetch();
								}

								if ($stmtn = $con->prepare('SELECT nom FROM clubs WHERE id=?')) {
									$stmtn->bind_param('s', $club_id);
									$stmtn->execute();
									$stmtn->store_result();
									$stmtn->bind_result($club);
									$stmtn->fetch();
								}


								if ($stmt2 = $con->prepare('SELECT intitule FROM categorie WHERE id=?')) {
									$stmt2->bind_param('s', $categorie_id);
									$stmt2->execute();
									$stmt2->store_result();
									$stmt2->bind_result($categorie);
									$stmt2->fetch();
								}
								?>

							  	 <tr>
							   	   <td><a href="eventPage.php?id=<?php echo $event_id ?>"><?php echo $intitule ?></a></td>
							   	   <td><a href="clubProfile.php?id=<?php echo $club_id ?>"><?php echo $club ?></a></td>
							   	   <td><?php echo $date ?></td>
							   	   <td><?php echo $categorie?></td>				    
							   	</tr>

							   <?php }while($row=$stmtE->fetch()) ?>
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