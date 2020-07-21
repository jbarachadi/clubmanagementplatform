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


    
	if ($stmt = $con->prepare('SELECT intitule, descriptif, date, categorie, cover, public FROM evenements WHERE id=? AND statut=?')) {
		$statut="Approuvée";
		$stmt->bind_param('ss',$_GET['id'],$statut);
		$stmt->execute();
		$stmt->store_result();
		$stmt->bind_result($intitule, $descriptif, $date, $categorie_id, $cover, $public);	
		$stmt->fetch();
	}

	if ($stmt2 = $con->prepare('SELECT intitule FROM categorie WHERE id=?')) {
		$stmt2->bind_param('s', $categorie_id);
		$stmt2->execute();
		$stmt2->store_result();
		$stmt2->bind_result($categorie);
		$stmt2->fetch();
	}

	//restriction d'accès dans le cas d'un évenement privé
	if(!isset($_SESSION['loggedin']) && $public=="ENSIAS")
	{
		header('Location: index.php');
	}

	if(isset($_GET['join']))
	{
		if($_GET['join']=="true")
		{
			//participer à un évenement
			if($stmtp = $con->prepare("INSERT INTO participation (user_id,evt_id) VALUES (?,?)")) {
				mysqli_stmt_bind_param($stmtp,'ss', $_SESSION['id'], $_GET['id']);
				$stmtp->execute();
			}
		}
		else if($_GET['join']=="false")
		{
			//retirer sa participation d'un évenement
			if($stmtp = $con->prepare("DELETE FROM participation WHERE user_id=? AND evt_id=?")) {
				mysqli_stmt_bind_param($stmtp,'ss', $_SESSION['id'], $_GET['id']);
				$stmtp->execute();
			}
		}

	}

   ?>
   
   <!-- end header -->
   

   <!-- content
   ================================================== -->
   <section id="content-wrap" class="blog-single">
   	<div class="row">
   		<div class="col-twelve">

   			<article class="format-standard">  

   				<div class="content-media">
					<div class="post-thumb">
						<img src="<?php echo $cover ?>"> 
					</div>  
				</div>

				<div class="primary-content">

					<h1 class="page-title"><?php echo $intitule ?> 
					<?php  
						//check la participation si le membre est connecté
						if(isset($_SESSION['type']) && $_SESSION['type']=="Membre"){
							if ($stmtA = $con->prepare('SELECT * FROM participation WHERE user_id=? AND evt_id=?')) {
								$stmtA->bind_param('ss', $_SESSION['id'], $_GET['id']);
								$stmtA->execute();
								$stmtA->store_result();
								$stmtA->fetch();
							}

							if ($stmtA->num_rows > 0)
							{
								$class="fa fa-minus-circle";
								$join="false";
							}
							else
							{
								$class="fa fa-plus-circle";
								$join="true";
							}				
							?>
							<a href="eventPage.php?id=<?php echo $_GET['id'];?>&join=<?php echo $join;?>" class="<?php echo $class;?>"></a>
						<?php } ?> 
					</h1>	

					<ul class="entry-meta">
						<li class="date"><?php echo date("d M Y", strtotime($date)) ?></li>					
						<li class="cat"><a href=""><?php echo $categorie ?></a></li>
					</ul>			

					<p style="padding-bottom: 50px;"><?php echo $descriptif ?></p>

				</div>
			</article>  		
		</div> <!-- end col-twelve -->
   	</div> <!-- end row -->

		<div class="comments-wrap">
			<div id="comments" class="row">
				<div class="col-full">

				<?php 
				//traitement de l'envoi d'un témoignage
				if(isset($_POST['submit']))
				{
					if($stmt_c = $con->prepare("INSERT INTO commentaires (user_id,event_id,corps,date) VALUES (?,?,?,?)")) {
						$date_c = date("Y-m-d @ H:i");
						mysqli_stmt_bind_param($stmt_c,'ssss', $_SESSION['id'], $_GET['id'], $_POST['corps'],$date_c);
						$stmt_c->execute();
					}
				}


				//récuperer les témoignages
					if ($stmtC = $con->prepare('SELECT user_id, corps, date FROM commentaires WHERE event_id=? ORDER BY date')) {
						$stmtC->bind_param('s',$_GET['id']);
						$stmtC->execute();
						$stmtC->store_result();
						$stmtC->bind_result($user_id, $corps, $date_c);	
						$stmtC->fetch();
					}
				?>	

               <h3><?php echo $stmtC->num_rows; ?> Témoignages</h3>

               <!-- commentlist -->
               <ol class="commentlist">

				<?php do{
					if ($stmtU = $con->prepare('SELECT nom, prenom, photo FROM membres WHERE user_id=?')) {
						$stmtU->bind_param('s',$user_id);
						$stmtU->execute();
						$stmtU->store_result();
						$stmtU->bind_result($nom, $prenom, $photo);	
						$stmtU->fetch();
					}
				 ?>

                  <li class="depth-1">

                     <div class="avatar">
                        <img width="50" height="50" class="avatar" src="<?php echo $photo; ?>" alt="">
                     </div>

                     <div class="comment-content">

	                     <div class="comment-info">
	                        <cite><?php echo $nom." ".$prenom; ?></cite>

	                        <div class="comment-meta">
	                           <time class="comment-time" datetime="2014-07-12T23:05"><?php echo $date_c; ?></time>
	                           
	                        </div>
	                     </div>

	                     <div class="comment-text">
	                        <p><?php echo $corps; ?></p>
	                     </div>

	                  </div>

                  </li>

                  <?php }while($row=$stmtC->fetch())?>

                  

               </ol> <!-- Commentlist End -->					

               <!-- respond -->
               <?php if(isset($_SESSION['loggedin'])){?>
               <div class="respond">

               	<h3>Envoyer un témoignage</h3>

                  <form id="contactForm" method="POST" action="eventPage.php?id=<?php echo $_GET['id'] ?>">
  					<fieldset>
                     	<div class="message form-field">
                        	<textarea name="corps" id="cMessage" class="full-width" placeholder="Votre témoignage (Limite de 5000 caractères)" ></textarea>
                     	</div>

                     	<input name="submit" type="submit" class="submit button-primary" value="Envoyer">
  					</fieldset>
  				  </form> <!-- Form End -->

               </div> 
               <?php
           		}	
               	else
               	{
               	  echo "Vous devez être connectés pour pouvoir laisser un témoignane.";
               	} ?>
               <!-- Respond End -->

         	</div> <!-- end col-full -->
         </div> <!-- end row comments -->
		</div> <!-- end comments-wrap -->

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
   <script>
   		var ic = document.getElementsByClassName('respond');
   		var d = document.getElementsByClassName('date');

   		var q = new Date();

		var eventDate = new Date(d[0].textContent);

		if (q<eventDate) {
			ic[0].style.display = "none";
		}
   </script>
   

</body>

</html>