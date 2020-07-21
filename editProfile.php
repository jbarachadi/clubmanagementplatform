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

<!DOCTYPE html>
<!--[if IE 8 ]><html class="no-js oldie ie8" lang="en"> <![endif]-->
<!--[if IE 9 ]><html class="no-js oldie ie9" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html class="no-js" lang="en"> <!--<![endif]-->
<head>

   <!--- basic page needs
   ================================================== -->
   <meta charset="utf-8">
   <title>eClubs : Modifier le profil</title>
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

      //traitement de la modification
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

      if(isset($_POST['submit']))
      {
         if($stmtE = $con->prepare('UPDATE users SET email=? WHERE id=?'))
         {
            if($_POST['email']!=""){$email=$_POST['email'];}
            $stmtE->bind_param('ss', $email, $_SESSION['id']);
            $stmtE->execute();
            $stmtE->store_result();
         }

         if($stmt = $con->prepare('UPDATE membres SET nom=?, prenom=? , filiere=? , photo=? WHERE user_id=?'))
         {
            if($_POST['nom']!=""){$nom=$_POST['nom'];}
            if($_POST['prenom']!=""){$prenom=$_POST['prenom'];}
            if($_POST['filiere']!=""){$filiere=$_POST['filiere'];}
            if(file_exists($_FILES['photo']['tmp_name']) || is_uploaded_file($_FILES['photo']['tmp_name']))
            {
               //traitement d'image
               $file_type = $_FILES['photo']['type'];
               $file_tmp_name = $_FILES['photo']['tmp_name'];
               $file_name = $_SESSION['id'];
               $target_dir = "images/avatars/";
               move_uploaded_file($file_tmp_name, $target_dir.$file_name.".png");
               $photo=$target_dir.$file_name.".png";
            }

            $stmt->bind_param('sssss', $nom, $prenom, $filiere, $photo, $_SESSION['id']);
            $stmt->execute();
            $stmt->store_result();
            $message='<div class="alert-box ss-success hideit"><p>Modifications enregistrées !</p><i class="fa fa-times close"></i></div>';
         }
      }
   ?>
   
   <!-- end header -->

   <!-- page header
   ================================================== -->
   <section id="page-header">
      <div class="row current-cat">
         <div class="col-full">
            <h1>Modifier le profil</h1>
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

                  <form name="cForm" id="cForm" method="post" action="editProfile.php" enctype="multipart/form-data">
                     <fieldset>

                        <div class="form-field">
                           <input name="nom" type="text" id="iName" class="half-width left" placeholder="<?php echo $nom ?>">
                        </div>

                        <div class="form-field">
                           <input name="prenom" type="text" id="iPrenom" class="half-width right" placeholder="<?php echo $prenom ?>">
                        </div>

                        <div class="form-field selectClass">
                          <select class="full-width" id="sampleRecipientInput" name="filiere">
                             <option value="<?php echo $filiere?>"><?php echo $filiere?></option>
                             <option value="IWIM">Ingénierie du Web et Informatique Mobile (IWIM)</option>
                             <option value="IeL">Ingénierie e-Logistique (IeL)</option>
                             <option value="GL">Génie Logiciel (GL)</option>
                             <option value="ISEM">Ingénierie des Systèmes Embarqués et Mobiles (ISEM)</option>
                             <option value="eMBI">e-Management et Business Intelligence (eMBI)</option>
                             <option value="SSI">Sécurité des Systèmes d'Information (SSI)</option>
                             <option value="IIA">Ingénierie Intelligence Artificielle (2IA)</option>
                             <option value="IDF">Ingénierie Digitale pour la Finance (IDF)</option>
                          </select>
                        </div>  

                        <div class="form-field">
                           <input name="email" type="text" id="iEmail" class="full-width" placeholder="<?php echo $email?>">
                        </div>

                        <div class="form-field">
                           <input name="pw" type="password" id="iPassword" class="half-width left" placeholder="Mot de passe"  autocomplete="false" autosave="false">
                        </div> 

                        <div class="form-field">
                           <input name="pw" type="password" id="iPassword" class="half-width right" placeholder="Vérifiez votre mot de passe"  autocomplete="false" autosave="false"> 
                        </div>

                        <div class="form-field">
                           <input name="photo" type="file" class="full-width" >
                        </div>

                        <input type="submit" name="submit" class="submit button-primary full-width-on-mobile" value="Enregistrer">

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