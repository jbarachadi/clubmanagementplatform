<!-- header 
   ================================================== -->
   <header class="short-header">   

   	<div class="gradient-block"></div>	

   	<div class="row header-content">

   		<div class="logo">
	         <a href="index.php">eClubs - Plateforme de gestion du parascolaire</a>
	      </div>

	   	<nav id="main-nav-wrap">
				<ul class="main-navigation sf-menu">
					<li class="current"><a href="index.php" title="">Accueil</a></li>		
					<li><a href="events.php" title="">Évènements</a></li>
					<li class="has-children">
						<a href="clubs.php" title="">Clubs</a>
						<ul class="sub-menu">
							<?php 
							if ($stmt = $con->prepare('SELECT id,nom FROM clubs')) {
								$stmt->execute();
								$stmt->store_result();
								$stmt->bind_result($club_id, $nom);	
								$stmt->fetch();
							}
							do{
						?>
							<li><a href="clubProfile.php?id=<?php echo $club_id; ?>"><?php echo $nom; ?></a></li>
						<?php
							}while($row=$stmt->fetch());
						?>
			         </ul>
					</li>							
					<li><a href="reunions.php" title="">Mes réunions</a></li>
					<li><a href="memberProfile.php" title="">Profil</a></li>										
				</ul>
			</nav> <!-- end main-nav-wrap -->

			<div class="triggers">
				<a class="search-triggers" href="logout.php"><i class="fa fa-sign-out"></i></a>
				<a class="menu-toggle" href="#"><span>Menu</span></a>
			</div> <!-- end triggers -->	
   		
   	</div>     		
   	
   </header> <!-- end header -->