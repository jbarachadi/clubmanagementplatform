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
	<title>eClubs : Membres du club</title>
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

      //restriction d'accès au pilotage/membres du club uniquement
      if ($stmt1 = $con->prepare('SELECT user_id FROM postulation WHERE club_id=? AND statut=?')) {
         $statut="Approuvé";
         $stmt1->bind_param('ss', $_GET['id'], $statut);
         $stmt1->execute();
         $stmt1->store_result();
         $stmt1->bind_result($user);
         $stmt1->fetch();
      }

      if($_SESSION['club']!= $_GET['id'] && $user!=$_SESSION['id'])
      {
         header('Location: index.php'); 
      }

      //headers
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

   //traitement pour retirer un membre du club
   if(isset($_GET['user']))
   {
      if($stmtB = $con->prepare('DELETE FROM postulation WHERE user_id=? AND club_id=?'))
      {        
         $stmtB->bind_param('ss', $_GET['user'], $_GET['id']);
         $stmtB->execute();
         $stmtB->store_result();
      }
   }

   ?>
   
   <!-- end header -->

   <!-- masonry
   ================================================== -->
   <section id="bricks">

      <div class="row masonry">

         <!-- brick-wrapper -->
         <div class="bricks-wrapper">

            <div class="grid-sizer"></div>

            <?php 
            if ($stmt3 = $con->prepare('SELECT user_id, date FROM postulation WHERE club_id=? AND statut=?')) {
               $statut="Approuvée";
               $stmt3->bind_param('ss', $_GET['id'], $statut);
               $stmt3->execute();
               $stmt3->store_result();
               $stmt3->bind_result($user_id, $date);
               $stmt3->fetch();
            }

            do{
               if ($stmt4 = $con->prepare('SELECT nom,prenom,sexe,filiere,photo FROM membres WHERE user_id=?')) {
                  $stmt4->bind_param('s', $user_id);
                  $stmt4->execute();
                  $stmt4->store_result();
                  $stmt4->bind_result($nom,$prenom,$sexe,$filiere,$photo);
                  $stmt4->fetch();
               }
            ?>
            <article class="brick entry format-standard animate-this membersPage">

               <div class="entry-thumb">
                  <a href="single-standard.html" class="thumb-link">
                     <img src=<?php echo $photo ?>>             
                  </a>
               </div>
               <div class="entry-text2">
                  <div class="entry-header">

                     <div class="entry-meta">
                        <span class="cat-links">
                           <a href="#"><?php echo $nom." ".$prenom ?></a>                      
                        </span>      
                        <?php if($_SESSION['type']=="Pilotage"){  ?>
                          <a href="members.php?id=<?php echo $_GET['id'];?>&user=<?php echo $user_id;?>" class="trash"><i class="fa fa-trash"></i></a>
                        <?php } ?>
                     </div>

                     
                  </div>
                  <div class="entry-excerpt">
                     <ul>
                        <li>
                           <span>Sexe : </span><?php echo $sexe ?>
                        </li>
                        <li>
                           <span>Filière : </span><?php echo $filiere ?>
                        </li>
                        <li>
                           <span>Date d'adhésion : </span><?php echo $date ?>
                        </li>
                     </ul>
                  </div>
               </div>

            </article> <!-- end article -->

         <?php }while($row=$stmt3->fetch()); ?>

           

         </div> <!-- end brick-wrapper --> 

      </div> <!-- end row -->

   </section> <!-- end bricks -->

   
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
   <script src="js/jquery.appear.js"></script>
   <script src="js/main.js"></script>

</body>

</html>