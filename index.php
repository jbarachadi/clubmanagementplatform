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
   <link rel="stylesheet" href="css/main.css?ver=1">
        

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


      include("auth.php");

      shell_exec('ps4test.py');

      $row = 2;
      $recomm_event = [];
      if (($handle = fopen("data.csv", "r")) !== FALSE) {
          while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
              $num = count($data);
              $row++;
              for ($c=1; $c < $num; $c++) {
                  array_push($recomm_event, $data[$c]);
              }
          }
          fclose($handle);
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

      //traitement de l'inscription
      if(isset($_POST['submiti']) &&!isset($_SESSION['type']))
   {
      //vérification de l'unicité de l'email
      if ($stmt2 = $con->prepare('SELECT email FROM users WHERE email = ?')) {
         $stmt2->bind_param('s', $_POST['email']);
         $stmt2->execute();
         $stmt2->store_result();
         
         if ($stmt2->num_rows > 0) {

            $message= '<div class="alert-box ss-error hideit"><p>L\'email que vous avez choisi a déjà été utilisé. Veuillez réessayer</p><i class="fa fa-times close"></i></div>'; 
         }
         else
         { 
            //ajout de l'utilisateur à la base de données et envoi d'un e-mail de confirmation
            if ($stmt3 = $con->prepare("INSERT INTO users (email,password,type) VALUES (?,?,?)")) {
               $pw=$_POST['pw'];
               $password = password_hash($pw,PASSWORD_DEFAULT);
               $email=$_POST['email'];
               $type="Membre";
                              
               mysqli_stmt_bind_param($stmt3,'sss', $email, $password, $type);
               $stmt3->execute();

               //récupération du user_id
               if ($stmt4 = $con->prepare('SELECT id FROM users WHERE email = ?')) {
                  $stmt4->bind_param('s', $email);
                  $stmt4->execute();
                  $stmt4->store_result();
                  $stmt4->bind_result($user_id);
                  $stmt4->fetch();
               }

               if ($stmt5 = $con->prepare("INSERT INTO membres (user_id,nom,prenom,sexe,filiere,photo) VALUES (?,?,?,?,?,?)")) {
                  $nom=$_POST['nom'];
                  $prenom=$_POST['prenom'];
                  $sexe=$_POST['sexe'];
                  $filiere=$_POST['filiere'];

                  $file_type = $_FILES['photo']['type'];
                      $file_tmp_name = $_FILES['photo']['tmp_name'];
                      $file_name = $user_id;
                      $target_dir = "images/avatars/";
                      move_uploaded_file($file_tmp_name, $target_dir.$file_name.".png");
                      $photo=$target_dir.$file_name.".png";
                                 
                  mysqli_stmt_bind_param($stmt5,'ssssss', $user_id, $nom, $prenom, $sexe, $filiere, $photo);
                  $stmt5->execute();

                  /*$recipient=$email;
                  $subject="Inscription réussie à la plateforme Parascolaire ENSIAS!";
                  $content="Chèr(e) ".$nom. " ". $prenom. "\n\nNous sommes ravis de vous compter parmi nous!\n\nCordialement.";

                  mail($recipient, $subject, $content);*/

                   $message= '<div class="alert-box ss-success hideit"><p>Inscription réussie! Un email a été envoyé à votre adresse</p><i class="fa fa-times close"></i></div>';
               }
            }
               else{
                  $message= '<div class="alert-box ss-error hideit"><p>Il semblerait qu\'une erreur soit survenue... Veuillez réessayer plus tard</p><i class="fa fa-times close"></i></div>'; 
               }
            }  
         }        
   }

   ?>
   
   <!-- end header -->


   <!-- masonry
   ================================================== -->
   <section id="bricks">

      <div class="row masonry">

         <div class="row add-bottom"><div class="col-twelve"><?php if(isset($message)){echo $message;} ?></div></div>


         <!-- brick-wrapper -->
         <div class="bricks-wrapper">

            <div class="grid-sizer"></div>
            <?php
               //si c'est un compte tout les évenements, si c'est un visiteur sélection des évenements grand public uniquement
                  if(isset($_SESSION['loggedin']))
                  {
                     //si c'est un compte de membre afficher les recommendations d'évenements
                     if($_SESSION['type']=="Membre")
                     {
                         if ($stmt = $con->prepare("SELECT id,club_id, intitule, descriptif, date, categorie, cover FROM evenements WHERE statut=? AND id IN (".implode(',',$recomm_event).")")) {
                              $statut="Approuvée";
                              $stmt->bind_param('s',$statut);
                              $stmt->execute();
                              $stmt->store_result();
                              $stmt->bind_result($evt_id, $club, $intitule, $descriptif, $date, $categorie_id, $cover); 
                              $stmt->fetch();
                           }
                     }
                     else
                     {
                        if ($stmt = $con->prepare('SELECT club_id, intitule, descriptif, date, categorie, cover FROM evenements WHERE statut=? ORDER BY date DESC LIMIT 3')) {
                        $statut="Approuvée";
                        $stmt->bind_param('s',$statut);
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($club, $intitule, $descriptif, $date, $categorie_id, $cover); 
                        $stmt->fetch();
                        }
                     }
                  }
                  else
                  {
                     if ($stmt = $con->prepare('SELECT club_id, intitule, descriptif, date, categorie, cover FROM evenements WHERE statut=? AND public=? ORDER BY date DESC LIMIT 3')) {
                        $public="Grand Public";
                        $statut="Approuvée";
                        $stmt->bind_param('ss',$statut, $public);
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($club, $intitule, $descriptif, $date, $categorie_id, $cover); 
                        $stmt->fetch();
                     }
                  }
               ?>


            <div class="brick entry featured-grid animate-this">
               <div class="entry-content">
                  <div id="featured-post-slider" class="flexslider">
                     <ul class="slides">
                        <?php if(isset($_SESSION['type']) && $_SESSION['type']=="Membre"){?><div class="forYou">Recommandés pour vous</div><?php } ?>
                        <?php do{ 

                        //vérifier que le membre n'a pas déjà participé aux évènements recommandés 
                        if(isset($_SESSION['type']) && $_SESSION['type']=="Membre"){
                            if ($stmtv = $con->prepare('SELECT * FROM participation WHERE evt_id=? AND user_id=?')) {
                                    $stmtv->bind_param('ss', $evt_id, $_SESSION['id']);
                                    $stmtv->execute();
                                    $stmtv->store_result();
                                    $stmtv->fetch();
                                 }

                              if($stmtv->num_rows != 0)
                              {
                                 continue; 
                              }
                        }


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

                        <li>
                           <div class="featured-post-slide">

                              <div class="post-background" style="background-image:url('<?php echo $cover ?>');"></div>

                              <div class="overlay"></div>                  

                              <div class="post-content">
                                 <ul class="entry-meta">
                                    <li><?php echo date("d M Y", strtotime($date)) ?></li>            
                                 </ul> 

                                 <h1 class="slide-title"><a href="single-standard.html" title=""><?php echo $intitule ?></a></h1> 
                              </div>                                
                        
                           </div>
                        </li> <!-- /slide -->

                        <?php 
                           }while($row=$stmt->fetch())
                        ?>
                     </ul> <!-- end slides -->
                  </div> <!-- end featured-post-slider -->                 
               </div> <!-- end entry content -->               
            </div>


            <?php 
            if(isset($_SESSION['loggedin']))
            {
               //ADEI peut voir ses annonces uniquement
               if($_SESSION['type']=="ADEI"){
                  if ($stmtF = $con->prepare('SELECT date, titre,corps,image,club_id FROM annonces WHERE club_id=? ORDER BY date DESC')) {
                     $id_adei="0";
                     $stmtF->bind_param('s', $id_adei);
                     $stmtF->execute();
                     $stmtF->store_result();
                     $stmtF->bind_result($date,$titre,$corps,$image, $club_id);   
                     $stmtF->fetch();
                  }
               }

               //Pilotage peut voir ses annonces + celles de l'ADEI
               if($_SESSION['type']=="Pilotage"){
                  if ($stmtF = $con->prepare('SELECT date, titre,corps,image,club_id FROM annonces WHERE club_id=? OR club_id=? ORDER BY date DESC')) {
                     $id_adei="0";
                     $stmtF->bind_param('ss', $_SESSION['club'], $id_adei);
                     $stmtF->execute();
                     $stmtF->store_result();
                     $stmtF->bind_result($date,$titre,$corps,$image, $club_id);   
                     $stmtF->fetch();
                  }
               }

               //Membre peut voir les annonces des clubs auxquels il appartient + celles de l'ADEI
               if($_SESSION['type']=="Membre"){
                  if ($stmtF = $con->prepare('SELECT date, titre,corps,image,club_id FROM annonces ORDER BY date DESC')) {
                     $stmtF->execute();
                     $stmtF->store_result();
                     $stmtF->bind_result($date,$titre,$corps,$image, $club_id);   
                     $stmtF->fetch();
                  }
               }


               do{
                  //vérifier pour un membre qu'il fait partie du club dont il veut visualiser l'annonce
                  if($_SESSION['type']=="Membre"){
                     if ($stmt = $con->prepare('SELECT id FROM postulation WHERE club_id=? AND user_id=? AND statut=?')) {
                           $stat="Approuvée";
                           $stmt->bind_param('sss', $club_id, $_SESSION['id'], $stat);
                           $stmt->execute();
                           $stmt->store_result();
                           $stmt->bind_result($id); 
                           $stmt->fetch();
                        }

                     if($stmt->num_rows == 0 && $club_id!=0)
                     {
                        continue; 
                     }
                  }   

                  if($club_id=="0")
                  {
                     $nom="ADEI";
                  }
                  else
                  {
                     if ($stmt = $con->prepare('SELECT nom FROM clubs WHERE id=?')) {
                        $stmt->bind_param('s', $club_id);
                        $stmt->execute();
                        $stmt->store_result();
                        $stmt->bind_result($nom); 
                        $stmt->fetch();
                     }
                  }
            ?>


            <article class="brick entry format-standard animate-this">
               <?php if($image!=""){ ?>
                  <div class="entry-thumb">
                     <a href="single-standard.html" class="thumb-link">
                        <img src='<?php echo $image; ?>'>           
                     </a>
                  </div>
               <?php } ?>  

               <div class="entry-text">
                  <div class="entry-header">

                     <div class="entry-meta">
                        <span class="cat-links">
                           <a href="#"><?php echo $nom; ?></a>                            
                        </span>        
                     </div>
                     
                     <h1 class="entry-title"><a href="single-standard.html"><?php echo $titre; ?></a></h1>

                  </div>
                  <div class="entry-excerpt">
                     <?php echo $corps; ?>
                  </div>
               </div>

            </article> <!-- end article -->

            <?php 
                }while($row=$stmtF->fetch());
            }    
            else
            {

            }
            ?>

      <?php if(!isset($_SESSION['type'])){?>      
      <section id="content-wrap" class="site-page" style="position: relative; float: right; width: 50vw; top: -4vw; margin: 0;">
      <div class="row">
         <div class="col-twelve">

            <section>  
               <h1 style="position: relative; left: 50%; transform: translate(-10%, 1vw);">Inscription</h1>
                  <div class="primary-content">

                  <form name="cForm" id="cForm" method="post" action="index.php" enctype="multipart/form-data">
                     <fieldset>

                        <div class="form-field">
                           <input name="nom" type="text" id="iName" class="half-width left" placeholder="Nom" value="" required>
                           <input name="prenom" type="text" id="iPrenom" class="half-width right" placeholder="Prénom" value="" required>
                        </div>

                        <div class="form-field selectClass genderClass">
                          <select class="full-width" id="sampleRecipientInput" name="sexe">
                             <option value="">Sexe</option>
                             <option value="M">Homme</option>
                             <option value="F">Femme</option>
                          </select>
                   </div>  

                        <div class="form-field selectClass filiereClass">
                          <select class="full-width" id="sampleRecipientInput" name="filiere">
                             <option value="">Filière</option>
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
                           <input name="email" type="text" id="iEmail" class="full-width" placeholder="Email" value="" required>
                        </div>

                        <div class="form-field">
                           <input name="pw" type="password" id="iPassword" class="half-width left" placeholder="Mot de passe"  value="" required autocomplete="false" autosave="false">
                        </div> 

                        <div class="form-field">
                           <input name="pw" type="password" id="iPassword" class="half-width right" placeholder="Vérifiez votre mot de passe"  value="" required autocomplete="false" autosave="false"> 
                        </div>

                        <div class="form-field">
                           <input name="photo" type="file" class="full-width" required="">
                        </div>

                        <input type="submit" name="submiti" class="submit button-primary full-width-on-mobile" value="S'enregistrer">

                     </fieldset>
                  </form> <!-- end form -->

            </section>
         <?php } ?>
         

         </div> <!-- end col-twelve -->
      </div> <!-- end row -->    
   </section>


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