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
				</ul>
			</nav> <!-- end main-nav-wrap -->

			<div class="search-wrap">
				
				<form role="search" method="post" class="search-form" action="index.php">
					<label>
						<span class="hide-content">Connexion</span>
						<input type="email" class="search-field" placeholder="E-mail" value="" name="email" title="Search for:" autocomplete="off">
						<input type="password" class="search-field" placeholder="Password" value="" name="pw" title="Search for:" autocomplete="off" required="true">

				     	<label class="add-bottom">
				        	<input type="checkbox" style="position: static;">			            
				        	<span class="label-text">Se souvenir de moi</span>
				     	</label>
					
					</label>
					<input type="submit" class="search-submit" value="Se connecter" name="submit">
					<a href="#" class="forgotten">Mot de passe oublié?</a>
				</form>

				<a href="#" id="close-search" class="close-btn">Close</a>

			</div> <!-- end search wrap -->	

			<div class="triggers">
				<a class="search-trigger" href="#"><i class="fa fa-search"></i></a>
				<a class="menu-toggle" href="#"><span>Menu</span></a>
			</div> <!-- end triggers -->	
   		
   	</div>     		
   	
   </header> <!-- end header -->