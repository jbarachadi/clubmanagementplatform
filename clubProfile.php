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

<!DOCTYPE html><html class="no-js" lang="en">
<head>

   <!--- basic page needs
   ================================================== -->
   <meta charset="utf-8">
	<title>eClubs : Profil du Club</title>
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

      //récuperation des informations du club
	   if ($stmt = $con->prepare('SELECT nom, descriptif, creation, icon FROM clubs WHERE id=?')) {
	     $stmt->bind_param('s', $_GET['id']);
	     $stmt->execute();
	     $stmt->store_result();
	     $stmt->bind_result( $nom, $descriptif, $creation, $icon); 
	     $stmt->fetch();
	  }

	  if ($stmtE = $con->prepare('SELECT user_id FROM pilotages WHERE club_id=?')) {
         $stmtE->bind_param('s', $_GET['id']);
         $stmtE->execute();
         $stmtE->store_result();
         $stmtE->bind_result($_id); 
         $stmtE->fetch();
      }

	  if ($stmtE = $con->prepare('SELECT email FROM users WHERE id=?')) {
         $stmtE->bind_param('s', $_id);
         $stmtE->execute();
         $stmtE->store_result();
         $stmtE->bind_result($email); 
         $stmtE->fetch();
      }

      //adhésion d'un membre
		if(isset($_POST['submit']))
		{
			if($stmt1 = $con->prepare("INSERT INTO postulation (user_id,club_id,date) VALUES (?,?,?)")) {
				$sys_date=date('Y-m-d');
				mysqli_stmt_bind_param($stmt1,'sss', $_SESSION['id'], $_GET['id'],$sys_date);
				$stmt1->execute();
			}
		}
   ?>
   
   <!-- end header -->

   
   <!-- content
   ================================================== -->  
   <div id="content-wrap" class="styles">

   	<div class="row narrow add-bottom text-center">

   		<div class="col-twelve tab-full">

   			<h1><?php echo $nom ?></h1>		

   		</div>

     	</div>

     	<div class="row">


     		<?php if(isset($_SESSION['type'])=="Pilotage" && isset($_SESSION['club']) && $_SESSION['club']==$_GET['id']){?><a href="editClub.php?id=<?php echo $_GET['id'];?>" style="color:GREY; font-weight: bold;">Modifier</a><?php } ?>

     		<div class="col-six tab-full profile-pic"><div class="image-zone"><img src="<?php echo $icon ?>"></div></div>

	      	<div class="col-six tab-full">

	         	<?php 
					if(isset($_SESSION['type']) && $_SESSION['type']=="Membre")
					{
						if ($stmtA = $con->prepare('SELECT statut FROM postulation WHERE user_id=? AND club_id=?')) {
							$stmtA->bind_param('ss', $_SESSION['id'],$_GET['id']);
							$stmtA->execute();
							$stmtA->store_result();
							$stmtA->bind_result($statut);	
							$stmtA->fetch();

							if ($stmtA->num_rows > 0) {
								if($statut=="Approuvée")
								{
									echo "<div style='color:grey;'>Vous êtes membre de ce club</div>";
								}
								if($statut=="En attente")
								{
									echo "<div style='color:grey;'>Votre demande d'adhésion est en attente de traitement</div>";
								}
							}
							else
							{
				?>

				<form method="POST" action="clubProfile.php?id=<?php echo $_GET['id']?>">
					<input type="submit" name="submit" value="Rejoindre">
				</form>

				<?php
							}
						}
					}
				?>

	         	<ul class="disc">
				   	<li><span>Date de création :</span> <?php echo $creation ?></li> 
				   	<li><span>Email :</span> <?php echo $email ?></li>
				   	<li><span>Descriptif :</span><br/>
				   	 <?php echo $descriptif ?></li> 
				</ul>			      	

			</div>	         

		</div> <!-- end row -->

		<div class="row half-bottom">

		   <div class="col-twelve">

		   	<?php 

			if ($stmtM = $con->prepare('SELECT * FROM postulation WHERE club_id=? AND statut=?')) {
				$statut="Approuvée";
				$stmtM->bind_param('ss', $_GET['id'],$statut);
				$stmtM->execute();
				$stmtM->store_result();
				$stmtM->fetch();
			}

			if ($stmtE = $con->prepare('SELECT * FROM evenements WHERE club_id=? AND statut=?')) {
				$statut="Approuvée";
				 $stmtE->bind_param('ss', $_GET['id'], $statut);
				 $stmtE->execute();
				 $stmtE->store_result();
				 $stmtE->fetch();
			}

			?>

		      <ul class="stats-tabs">
				   <li><a href="#"><?php echo $stmtM->num_rows; ?><em>Membres</em></a></li>
				   <li><a href="#"><?php echo $stmtE->num_rows; ?><em>Évènements</em></a></li>
				</ul>	      		

		   </div>	      	

		</div> <!-- end row -->

		<div class="row half-bottom">

			<h3>Events</h3>

			<?php
				//evenements approuvés et en attente pour le pilotage
				if(isset($_SESSION['club']) && $_SESSION['club']==$_GET['id'])
				{
					if ($stmt = $con->prepare('SELECT id,club_id, intitule, descriptif, date, categorie, cover, public FROM evenements WHERE club_id=? ORDER BY date DESC')) {
						$stmt->bind_param('s', $_GET['id']);
						$stmt->execute();
						$stmt->store_result();
						$stmt->bind_result($evt_id, $club, $intitule, $descriptif, $date, $categorie_id, $cover, $public);	
						$stmt->fetch();
					}
				}
				else if(!isset($_SESSION['club']))
				{
					//evenements grand public si non connecté
					if(!isset($_SESSION['loggedin'])){
						if ($stmt = $con->prepare('SELECT id, club_id, intitule, descriptif, date, categorie, cover, public FROM evenements WHERE club_id=? AND statut=? AND public=? ORDER BY date DESC')) {
							$public="Grand Public";
							$statut="Approuvée";
							$stmt->bind_param('sss', $_GET['id'],$statut, $public);
							$stmt->execute();
							$stmt->store_result();
							$stmt->bind_result($evt_id, $club, $intitule, $descriptif, $date, $categorie_id, $cover, $public);	
							$stmt->fetch();
						}
					}
					else
					{
						//evenements ensias et grand public si connecté
						if ($stmt = $con->prepare('SELECT id, club_id, intitule, descriptif, date, categorie, cover, public FROM evenements WHERE club_id=? AND statut=? ORDER BY date DESC')) {
							$statut="Approuvée";
							$stmt->bind_param('ss', $_GET['id'], $statut);
							$stmt->execute();
							$stmt->store_result();
							$stmt->bind_result($evt_id, $club, $intitule, $descriptif, $date, $categorie_id, $cover, $public);	
							$stmt->fetch();
						}
					}
				}

				?>
			<div class="table-responsive">

	      		<table>
						<thead>
						   	<tr>
						 		<th>Nom</th>
						      	<th>Date</th>
						      	<th>Catégorie</th>
						      	<th>Public</th>
						      	<?php if(isset($_SESSION['club']) && $_SESSION['club']==$_GET['id']){ ?><th>Statut de la demande</th><?php } ?>				    
						  	 </tr>
						</thead>
						<tbody>
							<?php
								do{
									if ($stmt2 = $con->prepare('SELECT intitule FROM categorie WHERE id=?')) {
										$stmt2->bind_param('s', $categorie_id);
										$stmt2->execute();
										$stmt2->store_result();
										$stmt2->bind_result($categorie);
										$stmt2->fetch();
									}
							?>

						  	 <tr>
						   	   <td><a href="eventPage.php?id=<?php echo $evt_id ?>"><?php echo $intitule ?></a></td>
						   	   <td><?php echo $date ?></td>
						   	   <td><?php echo $categorie?></td>
						   	   <td><?php echo $public ?></td>
						   	   <?php if(isset($_SESSION['club']) && $_SESSION['club']==$_GET['id']){ ?><td><?php echo $statut ?></td><?php } ?>					    
						   	</tr>

						   <?php }while($row=$stmt->fetch()) ?>
						</tbody>
				   </table>

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