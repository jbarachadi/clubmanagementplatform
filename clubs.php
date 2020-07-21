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
	<title>eClubs : Liste des clubs</title>
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

               //traitement pour retirer un club
               if(isset($_GET['id']))
               {
                  if($stmtB = $con->prepare('DELETE FROM clubs WHERE id=?'))
                  {        
                     $stmtB->bind_param('s', $_GET['id']);
                     $stmtB->execute();
                     $stmtB->store_result();
                  }
               }
         }
         else {
            include('headerVisiteur.php');
         }
      }
      else {
         include('headerVisiteur.php');
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
            if ($stmt3 = $con->prepare('SELECT id,nom,icon FROM clubs ORDER BY nom')) {
               $stmt3->execute();
               $stmt3->store_result();
               $stmt3->bind_result($id, $nom, $icon);
               $stmt3->fetch();
            }

            do{
            ?>
            <article class="brick entry format-standard animate-this membersPage">

               <div class="entry-thumb">
                  <a href="clubProfile.php?id=<?php echo $id ?>" class="thumb-link">
                     <img src=<?php echo $icon ?>>             
                  </a>
               </div>
               <div class="entry-text2">
                  <div class="entry-header">

                     <div class="entry-meta">
                        <span class="cat-links">
                           <a href="clubProfile.php?id=<?php echo $id ?>"><?php echo $nom ?></a>                      
                        </span>      
                        <?php if(isset($_SESSION['type']) && $_SESSION['type']=="ADEI"){  ?>
                          <a href="clubs.php?id=<?php echo $id;?>" class="trash"><i class="fa fa-trash"></i></a>
                        <?php } ?>
                     </div>                     
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