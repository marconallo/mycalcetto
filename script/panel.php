<div id="toppanel">
	<div id="panel">
		<div class="content clearfix">
			<div class="left">
				<h1>MyCalcetto</h1>
				<img alt="Oliver Hutton" src="img/pallone.png">
				<p class="grey"></p>
			</div>

            <?php if(!isset($_SESSION['id'])){ ?>
            
			<div class="left">
				<!-- Login Form -->
				<form class="clearfix" action="" method="post">
					<h2>Accedi</h2>
                    
                    <?php
						if($_SESSION['msg']['login-err']) {
							echo '<div class="error-small">'.$_SESSION['msg']['login-err'].'</div>';
							unset($_SESSION['msg']['login-err']);
						}
					?>
					
					<label class="grey" for="username">Username:</label>
					<input class="field" type="text" name="username" id="username" value="" size="23" />
					<label class="grey" for="password">Password:</label>
					<input class="field" type="password" name="password" id="password" size="23" />
	            	<label><input name="rememberMe" id="rememberMe" type="checkbox" checked="checked" value="1" /> &nbsp;Ricorda</label>
        			<div class="clear"></div>
					<input type="submit" name="submit" value="Login" class="bt_login" />
				</form>
			</div>
			<div class="left right">			
				<!-- Register Form -->
				<form action="" method="post">
					<h2>Registrati!</h2>		
                    
                    <?php
						if($_SESSION['msg']['reg-err'])	{
							echo '<div class="error-small">'.$_SESSION['msg']['reg-err'].'</div>';
							unset($_SESSION['msg']['reg-err']);
						}
						if($_SESSION['msg']['reg-success']) {
							echo '<div class="success-small">'.$_SESSION['msg']['reg-success'].'</div>';
							unset($_SESSION['msg']['reg-success']);
						}
					?>
                    		
					<label class="grey" for="name">Nome:</label>
					<input class="field" type="text" name="name" id="name" value="" size="23" />
					<label class="grey" for="surname">Cognome:</label>
					<input class="field" type="text" name="surname" id="surname" value="" size="23" />
					<label class="grey" for="username">Username:</label>
					<input class="field" type="text" name="username" id="reg_username" value="" size="23" />
					<label class="grey" for="email">Email:</label>
					<input class="field" type="text" name="email" id="email" size="23" />
					<label>Ti verr&agrave; inviata una password.</label>
					<input type="submit" name="submit" value="Invia" class="bt_register" />
				</form>
			</div>
            
            <?php }else { ?>
            
            <div class="left">
            <h2>Opzioni</h2>
            ---------------------------<br />
            <a href="userData.php">&nbsp;Modifica dati personali</a><br />
            ---------------------------<br />
            <?php if (isset($_SESSION['level']) && $_SESSION['level']==9) { ?>
            	<a href="visualizzaUtenti.php">&nbsp;Gestione utenti</a><br />
            ---------------------------<br />
            <a href="log/debug.log" target="_blank">&nbsp;Visualizza log</a><br />
            ---------------------------<br />
            <?php } ?>
            <a href="?logoff">&nbsp;Logout</a><br />
            ---------------------------<br />
            </div>
            
            <div class="left right">
            </div>
            
            <?php } ?>
		</div>
	</div> 
<!-- /login -->	

<!-- The tab on top -->	
	<div class="tab">
		<ul class="login">
	    	<li class="left">&nbsp;</li>
	        <li>user: <?php echo isset($_SESSION['usr']) ? $_SESSION['usr'] : 'Guest';?></li>
			<li class="sep">|</li>
			<li id="toggle">
				<a id="open" class="open" href="#"><?php echo isset($_SESSION['id'])?'Apri pannello':'Accedi | Registrati';?></a>
				<a id="close" style="display: none;" class="close" href="#">Chiudi pannello</a>			
			</li>
	    	<li class="right">&nbsp;</li>
		</ul> 
	</div> 
<!-- / top -->
	
</div>